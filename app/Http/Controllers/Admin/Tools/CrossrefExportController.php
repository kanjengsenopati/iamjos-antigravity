<?php

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Submission;
use Illuminate\Http\Request;

class CrossrefExportController extends Controller
{
    /**
     * Display the OJS 3.3 Style Interface
     */
    public function index(Request $request)
    {
        $journal = current_journal();
        
        // 1. Filter Logic (OJS Style)
        $status = $request->input('status', 'not_deposited'); // Default OJS usually shows 'not_deposited'
        $tab = $request->input('tab', 'settings');

        // 2. Base Query & Filter
        if ($tab === 'issues') {
            $query = \App\Models\Issue::where('journal_id', $journal->id)
                ->where('is_published', true);

            if ($status == 'not_deposited') {
                $query->where(function ($q) {
                    $q->where('doi_status', 'not_deposited')->orWhereNull('doi_status');
                });
            } elseif (in_array($status, ['active', 'failed', 'submitted', 'marked'])) {
                $query->where('doi_status', $status);
            }

            $items = $query->orderByDesc('published_at')->paginate(20);
        } else {
            $query = Submission::where('journal_id', $journal->id)
                ->where('status', Submission::STATUS_PUBLISHED)
                ->with(['authors', 'issue', 'currentPublication']);

            if ($status == 'not_deposited') {
                $query->whereHas('currentPublication', function ($q) {
                    $q->where('doi_status', 'not_deposited')->orWhereNull('doi_status');
                });
            } elseif (in_array($status, ['active', 'failed', 'submitted', 'marked'])) {
                $query->whereHas('currentPublication', function ($q) use ($status) {
                    $q->where('doi_status', $status);
                });
            }

            $items = $query->latest('published_at')->paginate(20);
        }

        return view('journal.tools.crossref_index', compact('journal', 'items', 'status', 'tab'));
    }

    // 2. Save Settings Logic
    public function saveSettings(Request $request)
    {
        $journal = current_journal();

        $validated = $request->validate([
            'depositor_name' => 'required|string|max:255',
            'depositor_email' => 'required|email|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'automatic_deposit' => 'sometimes|boolean',
            'auto_poll_status' => 'sometimes|boolean',
            'test_mode' => 'sometimes|boolean',
        ]);

        $journal->setSetting('crossref_depositor_name', $validated['depositor_name']);
        $journal->setSetting('crossref_depositor_email', $validated['depositor_email']);
        $journal->setSetting('crossref_username', $validated['username']);
        
        if (!empty($validated['password'])) {
             $journal->setSetting('crossref_password', encrypt($validated['password']));
        }

        $journal->setSetting('crossref_automatic_deposit', $request->boolean('automatic_deposit'));
        $journal->setSetting('crossref_auto_poll_status', $request->boolean('auto_poll_status'));
        $journal->setSetting('crossref_test_mode', $request->boolean('test_mode'));
        $journal->save();

        return redirect()->back()->with('success', 'Crossref settings saved successfully.');
    }

    // 4. Mark Active Logic (Manual Override — OJS 3.3 "Marked active")
    public function markActive(Request $request)
    {
        $journal = current_journal();
        $ids = $request->input('submission_ids', []);
        $type = $request->input('type', 'article');

        if (empty($ids)) {
            return back()->with('error', 'Please select at least one item to mark as active.');
        }

        if ($type === 'issue') {
            $issues = \App\Models\Issue::where('journal_id', $journal->id)->whereIn('id', $ids)->get();
            foreach ($issues as $issue) {
                $issue->doi_status = 'marked';
                $issue->save();
            }
        } else {
            $submissions = \App\Models\Submission::where('journal_id', $journal->id)
                ->whereIn('id', $ids)->with(['currentPublication'])->get();
            foreach ($submissions as $submission) {
                $pub = $submission->currentPublication;
                if ($pub) {
                    $pub->doi_status = 'marked';
                    $pub->save();
                }
            }
        }

        return back()->with('success', 'DOI status marked as active successfully.');
    }

    // 2. XML Export Logic
    public function export(Request $request)
    {
        $journal = current_journal();
        $ids = $request->input('submission_ids', []);
        $type = $request->input('type', 'article');

        if (empty($ids)) {
            return back()->with('error', 'Please select at least one item.');
        }

        $batchId = (string) \Illuminate\Support\Str::uuid();
        $filename = 'crossref-' . $journal->path . '-' . now()->format('YmdHis') . '.xml';

        if ($type === 'issue') {
            $issues = \App\Models\Issue::whereIn('id', $ids)->where('journal_id', $journal->id)->get();
            $content = view('journal.tools.crossref_issue_xml', compact('issues', 'journal', 'batchId'))->render();
        } else {
            $submissions = \App\Models\Submission::whereIn('id', $ids)
                ->where('journal_id', $journal->id)
                ->with(['authors', 'issue', 'currentPublication.citations', 'currentPublication', 'galleys.file'])
                ->get();
            $content = view('journal.tools.crossref_xml', compact('submissions', 'journal', 'batchId'))->render();
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $content = trim($content);
        $xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . $content;

        if (ob_get_length()) ob_clean();

        return response($xml, 200, [
            'Content-Type' => 'text/xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // 4. API Deposit Logic (Asynchronous Queue)
    public function deposit(Request $request)
    {
        $journal = current_journal();
        $ids = $request->input('submission_ids', []);
        $type = $request->input('type', 'article');

        if (empty($ids)) {
            return back()->with('error', 'Please select at least one item for deposit.');
        }

        $hasDepositorInfo = $journal->getSetting('crossref_depositor_name') 
            && $journal->getSetting('crossref_depositor_email') 
            && $journal->getSetting('crossref_username');

        if (!$hasDepositorInfo) {
            return back()->with('error', 'Crossref username and depositor information must be configured first.');
        }

        $invalidCount = 0;

        if ($type === 'issue') {
            $issues = \App\Models\Issue::whereIn('id', $ids)->where('journal_id', $journal->id)->get();
            foreach ($issues as $issue) {
                if (empty($issue->doi)) {
                    $invalidCount++;
                }
            }
            if ($invalidCount > 0 && $invalidCount == $issues->count()) {
                return back()->with('error', 'None of the selected issues have DOIs assigned.');
            }
            \App\Jobs\DepositCrossrefJob::dispatch($ids, $journal, 'issue');
        } else {
            $submissions = \App\Models\Submission::whereIn('id', $ids)
                ->where('journal_id', $journal->id)->with(['currentPublication'])->get();
            foreach ($submissions as $sub) {
                if (!$sub->currentPublication || empty($sub->currentPublication->doi)) {
                    $invalidCount++;
                }
            }
            if ($invalidCount > 0 && $invalidCount == $submissions->count()) {
                return back()->with('error', 'None of the selected articles have DOIs assigned.');
            }
            \App\Jobs\DepositCrossrefJob::dispatch($ids, $journal, 'article');
        }

        return back()->with('success', 'Selected items have been queued for Crossref deposit. The background worker will process them shortly.');
    }

    // 5. Manual Sync / Check DOI Status (OJS 3.3 Status Polling)
    public function syncStatus(Request $request, \App\Services\CrossrefDepositService $service)
    {
        $journal = current_journal();
        $ids = $request->input('submission_ids', []);
        $type = $request->input('type', 'article');

        $username = $journal->getSetting('crossref_username');
        $rawPassword = $journal->getSetting('crossref_password');
        $password = '';
        if ($rawPassword) {
            try {
                $password = decrypt($rawPassword);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                $password = $rawPassword;
            }
        }
        $depositorEmail = $journal->getSetting('crossref_depositor_email') ?? 'admin@iamjos.id';
        $isTestMode = (bool) $journal->getSetting('crossref_test_mode');

        $activatedCount = 0;
        $failedCount = 0;
        $pendingCount = 0;

        if ($type === 'issue') {
            $query = \App\Models\Issue::where('journal_id', $journal->id)
                ->whereNotNull('doi');

            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            } else {
                $query->where('doi_status', 'submitted');
            }

            $issues = $query->get();

            if ($issues->isEmpty()) {
                return back()->with('info', 'Tidak ada issue yang perlu diperiksa statusnya.');
            }

            foreach ($issues as $issue) {
                $result = $service->checkDoiStatus(
                    $issue->doi,
                    $issue->crossref_batch_id,
                    $username,
                    $password,
                    $depositorEmail,
                    $isTestMode
                );

                if ($result['status'] === 'active') {
                    $issue->update(['doi_status' => 'active']);
                    $activatedCount++;
                } elseif ($result['status'] === 'failed') {
                    $issue->update(['doi_status' => 'failed']);
                    \App\Models\CrossrefLog::create([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'journal_id' => $journal->id,
                        'submission_id' => null,
                        'status' => 'Failed',
                        'crossref_batch_id' => $issue->crossref_batch_id,
                        'message' => substr($result['message'], 0, 500),
                    ]);
                    $failedCount++;
                } else {
                    $pendingCount++;
                }
            }
        } else {
            $query = \App\Models\Submission::where('journal_id', $journal->id)
                ->whereHas('currentPublication', function ($q) use ($ids) {
                    $q->whereNotNull('doi');
                    if (empty($ids)) {
                        $q->where('doi_status', 'submitted');
                    }
                })
                ->with(['currentPublication']);

            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }

            $submissions = $query->get();

            if ($submissions->isEmpty()) {
                return back()->with('info', 'Tidak ada artikel yang perlu diperiksa statusnya.');
            }

            foreach ($submissions as $sub) {
                $pub = $sub->currentPublication;
                if (!$pub || !$pub->doi) continue;

                $result = $service->checkDoiStatus(
                    $pub->doi,
                    $pub->crossref_batch_id,
                    $username,
                    $password,
                    $depositorEmail,
                    $isTestMode
                );

                if ($result['status'] === 'active') {
                    $pub->update(['doi_status' => 'active']);
                    $activatedCount++;
                } elseif ($result['status'] === 'failed') {
                    $pub->update(['doi_status' => 'failed']);
                    \App\Models\CrossrefLog::create([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'journal_id' => $journal->id,
                        'submission_id' => $sub->id,
                        'status' => 'Failed',
                        'crossref_batch_id' => $pub->crossref_batch_id,
                        'message' => substr('Crossref Error: ' . $result['message'], 0, 500),
                    ]);
                    $failedCount++;
                } else {
                    $pendingCount++;
                }
            }
        }

        return back()->with('success', "Status DOI berhasil diperiksa: {$activatedCount} aktif, {$failedCount} gagal, {$pendingCount} masih diproses.");
    }
}
