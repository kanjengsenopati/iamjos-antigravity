<?php

namespace App\Http\Controllers\Journal;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Submission;
use App\Models\SubmissionLog;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    /**
     * Show the form for creating a new issue
     */
    public function create()
    {
        $journal = current_journal();

        // Get the latest issue to suggest next values
        $latestIssue = Issue::where('journal_id', $journal->id)
            ->orderBy('year', 'desc')
            ->orderBy('volume', 'desc')
            ->orderBy('number', 'desc')
            ->first();

        // Suggest next issue values
        $suggestedYear = date('Y');
        $suggestedVolume = $latestIssue ? $latestIssue->volume : 1;
        $suggestedNumber = $latestIssue ? $latestIssue->number + 1 : 1;

        return view('editor.issues.create', compact(
            'journal',
            'suggestedYear',
            'suggestedVolume',
            'suggestedNumber'
        ));
    }

    /**
     * Display a listing of issues for the current journal
     */
    public function index()
    {
        $journal = current_journal();

        // Stats
        $totalIssues = Issue::where('journal_id', $journal->id)->count();
        $publishedCount = Issue::where('journal_id', $journal->id)->where('is_published', true)->count();
        $upcomingCount = Issue::where('journal_id', $journal->id)->where('is_published', false)->count();

        // Count all articles in all issues (or you can refine to published only if needed)
        // Assuming 'submissions' relation exists on Issue
        $totalArticles = Issue::where('journal_id', $journal->id)
            ->withCount('submissions')
            ->get()
            ->sum('submissions_count');

        // Future Issues (all of them, usually few)
        $futureIssues = Issue::where('journal_id', $journal->id)
            ->where('is_published', false)
            ->orderBy('year', 'asc') // Upcoming should probably be ascending or descending? Usually upcoming is nearest first.
            ->orderBy('volume', 'asc')
            ->orderBy('number', 'asc')
            ->withCount('submissions')
            ->get();

        // Available years for Back Issues filter
        $availableYears = Issue::where('journal_id', $journal->id)
            ->where('is_published', true)
            ->distinct()
            ->pluck('year')
            ->filter()
            ->sortDesc()
            ->values();

        $selectedYear = request('year');

        // Back Issues (Published, paginated)
        $backIssuesQuery = Issue::where('journal_id', $journal->id)
            ->where('is_published', true);

        if (!empty($selectedYear)) {
            $backIssuesQuery->where('year', $selectedYear);
        }

        $backIssues = $backIssuesQuery
            ->orderBy('published_at', 'desc')
            ->orderBy('year', 'desc')
            ->orderBy('volume', 'desc')
            ->orderBy('number', 'desc')
            ->withCount('submissions')
            ->paginate(12)
            ->withQueryString();

        // Get current active issue
        $currentIssue = $journal->currentIssue;

        return view('editor.issues.index', compact(
            'journal',
            'futureIssues',
            'backIssues',
            'totalIssues',
            'publishedCount',
            'upcomingCount',
            'totalArticles',
            'availableYears',
            'selectedYear',
            'currentIssue'
        ));
    }

    /**
     * Store a newly created issue
     */
    public function store(Request $request)
    {
        $journal = current_journal();

        $validated = $request->validate([
            'volume' => 'required|integer|min:1',
            'number' => 'required|integer|min:1',
            'year' => 'required|integer|min:1900|max:2100',
            'title' => 'nullable|string|max:255',
            'show_volume' => 'nullable|boolean',
            'show_number' => 'nullable|boolean',
            'show_year' => 'nullable|boolean',
            'show_title' => 'nullable|boolean',
            'description' => 'nullable|string',
            'url_path' => ['nullable', 'string', 'alpha_dash', 'unique:issues,url_path,NULL,id,journal_id,' . $journal->id],
            'cover' => 'nullable|image|max:2048',
            'doi_suffix' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($journal) {
                    if ($journal->doi_prefix) {
                        $fullDoi = $journal->doi_prefix . '/' . $value;
                        if (\App\Models\Issue::withTrashed()->where('doi', $fullDoi)->exists()) {
                            $fail("Suffix DOI ini akan menghasilkan DOI ({$fullDoi}) yang sudah digunakan oleh terbitan lain. Harap gunakan suffix yang unik.");
                        }
                    }
                },
            ],
        ]);

        $issueData = [
            'journal_id' => $journal->id,
            'volume' => $validated['volume'],
            'number' => $validated['number'],
            'year' => $validated['year'],
            'title' => $validated['title'] ?? null,
            'show_volume' => $request->boolean('show_volume', true),
            'show_number' => $request->boolean('show_number', true),
            'show_year' => $request->boolean('show_year', true),
            'show_title' => $request->boolean('show_title', false),
            'description' => $validated['description'] ?? null,
            'url_path' => $validated['url_path'] ?? null,
            'doi_suffix' => $validated['doi_suffix'] ?? null,
            'is_published' => false,
        ];

        // If manual DOI suffix provided, construct full DOI
        if (!empty($issueData['doi_suffix']) && $journal->doi_prefix) {
            $issueData['doi'] = $journal->doi_prefix . '/' . $issueData['doi_suffix'];
        }

        // Handle cover image upload
        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('issues/covers', 'public');
            $issueData['cover_path'] = $path;
        }

        $issue = Issue::create($issueData);

        return redirect()
            ->route('journal.issues.index', ['journal' => $journal->slug])
            ->with('success', 'Issue created successfully.');
    }

    /**
     * Update the specified issue
     */
    public function update(Request $request, Issue $issue)
    {
        $journal = current_journal();

        // Ownership check
        if ($issue->journal_id !== $journal->id) abort(404);

        $validated = $request->validate([
            'volume' => 'required|integer|min:1',
            'number' => 'required|integer|min:1',
            'year' => 'required|integer|min:1900|max:2100',
            'title' => 'nullable|string|max:255',
            'show_volume' => 'nullable|boolean',
            'show_number' => 'nullable|boolean',
            'show_year' => 'nullable|boolean',
            'show_title' => 'nullable|boolean',
            'description' => 'nullable|string',
            'url_path' => ['nullable', 'string', 'alpha_dash', 'unique:issues,url_path,' . $issue->id . ',id,journal_id,' . $journal->id],
            'cover' => 'nullable|image|max:2048',
            'doi_suffix' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($journal, $issue) {
                    if ($journal->doi_prefix) {
                        $fullDoi = $journal->doi_prefix . '/' . $value;
                        if (\App\Models\Issue::withTrashed()->where('doi', $fullDoi)->where('id', '!=', $issue->id)->exists()) {
                            $fail("Suffix DOI ini akan menghasilkan DOI ({$fullDoi}) yang sudah digunakan oleh terbitan lain. Harap gunakan suffix yang unik.");
                        }
                    }
                },
            ],
        ]);

        $issueData = [
            'volume' => $validated['volume'],
            'number' => $validated['number'],
            'year' => $validated['year'],
            'title' => $validated['title'] ?? null,
            'show_volume' => $request->boolean('show_volume', true),
            'show_number' => $request->boolean('show_number', true),
            'show_year' => $request->boolean('show_year', true),
            'show_title' => $request->boolean('show_title', false),
            'description' => $validated['description'] ?? null,
            'url_path' => $validated['url_path'] ?? null,
            'doi_suffix' => $validated['doi_suffix'] ?? null,
        ];

        // If manual DOI suffix provided, construct full DOI
        if (!empty($issueData['doi_suffix']) && $journal->doi_prefix) {
            $issueData['doi'] = $journal->doi_prefix . '/' . $issueData['doi_suffix'];
        } elseif (empty($issueData['doi_suffix'])) {
            $issueData['doi'] = null; // Clear DOI if suffix is removed
        }

        // Handle cover image upload
        if ($request->hasFile('cover')) {
            if ($issue->cover_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($issue->cover_path);
            }
            $path = $request->file('cover')->store('issues/covers', 'public');
            $issueData['cover_path'] = $path;
        }

        $issue->update($issueData);

        return back()->with('success', 'Issue updated successfully.');
    }

    /**
     * Publish the issue
     */
    public function publish(Request $request, Issue $issue)
    {
        $journal = current_journal();
        if ($issue->journal_id !== $journal->id) abort(404);

        $issueData = [
            'is_published' => true,
            'published_at' => now(),
        ];

        // Auto-assign DOI if enabled and missing
        if (empty($issue->doi)) {
            $generatedDoi = \App\Services\DoiService::generateForIssue($issue, $journal);
            if ($generatedDoi) {
                // Prevent duplicate DOI assignment
                if (\App\Models\Issue::withTrashed()->where('doi', $generatedDoi)->where('id', '!=', $issue->id)->exists()) {
                    return back()->with('error', "Gagal mempublikasikan: DOI terbitan ini ({$generatedDoi}) sudah digunakan oleh terbitan lain. Silakan masuk ke Edit Issue dan buat Suffix DOI secara manual yang lebih unik.");
                }

                $issueData['doi'] = $generatedDoi;
                // Extract suffix from full DOI
                $prefix = $journal->doi_prefix;
                if ($prefix && str_starts_with($generatedDoi, $prefix . '/')) {
                    $issueData['doi_suffix'] = substr($generatedDoi, strlen($prefix) + 1);
                }
            }
        } else {
            // Check if existing DOI collides
            if (\App\Models\Issue::withTrashed()->where('doi', $issue->doi)->where('id', '!=', $issue->id)->exists()) {
                return back()->with('error', "Gagal mempublikasikan: DOI terbitan ini ({$issue->doi}) sudah digunakan oleh terbitan lain. Silakan masuk ke Edit Issue dan ubah Suffix DOI secara manual.");
            }
        }

        try {
            $issue->update($issueData);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23505' && str_contains($e->getMessage(), 'issues_doi_unique')) {
                return back()->with('error', 'Gagal mempublikasikan: Terdapat duplikasi DOI pada terbitan ini di sistem. Silakan masuk ke menu Edit Issue dan ubah Suffix DOI secara manual agar unik.');
            }
            throw $e;
        }

        // [IAMJOS-CROSSREF-ISSUE] Trigger Auto-Deposit for Issue if enabled
        if ($journal->getSetting('crossref_automatic_deposit') && !empty($issueData['doi'])) {
            \App\Jobs\DepositCrossrefJob::dispatch([$issue->id], $journal, 'issue');
        }

        foreach ($issue->submissions as $sub) {
            $sub->update([
                'status' => Submission::STATUS_PUBLISHED,
                'published_at' => now(),
            ]);
            SubmissionLog::log(
                submission: $sub,
                eventType: SubmissionLog::EVENT_PUBLISHED,
                title: 'Article Published',
                description: "Published via issue publication ({$issue->identifier}).",
                stage: $sub->stage
            );
        }

        // Check if user requested to send email notification to registered users
        $sendEmail = $request->boolean('send_email');
        if ($sendEmail) {
            // Queued/Log notification to journal users if requested
        }

        return back()->with('success', "Issue {$issue->identifier} has been published.");
    }

    /**
     * Unpublish the issue
     */
    public function unpublish(Issue $issue)
    {
        $journal = current_journal();
        if ($issue->journal_id !== $journal->id) abort(404);

        $issue->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        foreach ($issue->submissions as $sub) {
            $sub->update([
                'status' => Submission::STATUS_ACCEPTED,
                'published_at' => null,
            ]);
            SubmissionLog::log(
                submission: $sub,
                eventType: SubmissionLog::EVENT_UNPUBLISHED,
                title: 'Article Unpublished',
                description: "Unpublished due to issue unpublish ({$issue->identifier}).",
                stage: $sub->stage
            );
        }

        return back()->with('success', "Issue {$issue->identifier} has been unpublished.");
    }

    /**
     * Delete issue cover image.
     */
    public function deleteCover(Issue $issue)
    {
        $journal = current_journal();
        if ($issue->journal_id !== $journal->id) abort(404);

        if ($issue->cover_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($issue->cover_path);
            $issue->cover_path = null;
            $issue->save();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified issue
     */
    public function destroy(Issue $issue)
    {
        $journal = current_journal();
        if ($issue->journal_id !== $journal->id) abort(404);

        // Check if issue has any submissions
        if ($issue->submissions()->exists()) {
            return back()->with('error', 'Cannot delete issue with assigned submissions.');
        }

        $issue->delete();

        return back()->with('success', 'Issue deleted successfully.');
    }

    /**
     * Set a published issue as the Current Issue for the journal
     */
    public function setCurrent(Request $request, Issue $issue)
    {
        $journal = current_journal();
        if ($issue->journal_id !== $journal->id) abort(404);
        if (!$issue->is_published) {
            return redirect()->route('journal.issues.index', [
                'journal' => $journal->slug,
                'tab' => 'back',
            ])->with('error', 'Only published issues can be set as current issue.');
        }

        // Calculate a timestamp guaranteed to be strictly greater than any other issue's published_at
        $maxPublishedAt = Issue::where('journal_id', $journal->id)
            ->where('id', '!=', $issue->id)
            ->max('published_at');

        $now = now();
        if ($maxPublishedAt) {
            $parsedMax = \Carbon\Carbon::parse($maxPublishedAt);
            $newPublishedAt = $parsedMax->greaterThanOrEqualTo($now) ? $parsedMax->copy()->addSecond() : $now;
        } else {
            $newPublishedAt = $now;
        }

        $issue->update(['published_at' => $newPublishedAt]);

        $queryParams = [
            'journal' => $journal->slug,
            'tab' => 'back',
        ];
        if ($request->filled('year')) {
            $queryParams['year'] = $request->query('year');
        }

        return redirect()->route('journal.issues.index', $queryParams)
            ->with('success', "Issue {$issue->identifier} is now designated as the Current Issue.");
    }

    /**
     * Assign DOI to the specified issue manually
     */
    public function assignDoi(Issue $issue)
    {
        $journal = current_journal();
        if ($issue->journal_id !== $journal->id) abort(404);

        if ($issue->doi) {
            return back()->with('error', 'This issue already has a DOI assigned.');
        }

        $doi = \App\Services\DoiService::generateForIssue($issue, $journal);
        
        if ($doi) {
            $issue->doi = $doi;
            $issue->save();
            return back()->with('success', 'DOI successfully assigned to this issue.');
        }

        return back()->with('error', 'Failed to generate DOI. Please check your DOI Plugin configuration.');
    }

    /**
     * Clear DOI from the specified issue
     */
    public function clearDoi(Issue $issue)
    {
        $journal = current_journal();
        if ($issue->journal_id !== $journal->id) abort(404);

        $issue->doi = null;
        $issue->doi_status = null;
        $issue->crossref_batch_id = null;
        $issue->save();

        return back()->with('success', 'DOI successfully cleared from this issue.');
    }
}
