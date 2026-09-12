<?php

namespace App\Services;

use App\Facades\Settings;
use App\Models\Journal;
use App\Models\Submission;
use App\Models\CrossrefLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CrossrefDepositService
{
    /**
     * Generate Crossref XML content.
     */
    public function generateXml($submissions, Journal $journal, $batchId = null)
    {
        $batchId = $batchId ?? (string) Str::uuid();
        $content = view('journal.tools.crossref_xml', compact('submissions', 'journal', 'batchId'))->render();

        // Cleaning (Remove BOM & leading/trailing whitespace)
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // Remove BOM if present
        $content = trim($content);
        
        // CR-08 FIX: Only collapse whitespace between structural tags (not inside text content).
        // This preserves whitespace within <jats:p>, <unstructured_citation>, <title>, etc.
        $content = preg_replace('/>\s+</', '><', $content);

        return '<?xml version="1.0" encoding="utf-8"?>' . "\n" . $content;
    }

    /**
     * Send XML to Crossref.
     */
    public function deposit($submissionIds, Journal $journal)
    {
        $hasDepositorInfo = $journal->getSetting('crossref_depositor_name') 
            && $journal->getSetting('crossref_depositor_email') 
            && $journal->getSetting('crossref_username');

        if (!$hasDepositorInfo) {
            return [
                'status' => 'Failed',
                'message' => 'Crossref depositor info or username is missing.'
            ];
        }

        $submissions = Submission::whereIn('id', (array) $submissionIds)
            ->where('journal_id', $journal->id)
            ->with(['authors', 'issue', 'currentPublication'])
            ->get();

        if ($submissions->isEmpty()) {
            return [
                'status' => 'Failed',
                'message' => 'No valid submissions found.'
            ];
        }

        // Fix: Generate batch ID once and pass it to both XML generator and database
        $batchId = (string) Str::uuid();
        $xmlString = $this->generateXml($submissions, $journal, $batchId);
        $filename = 'crossref-' . $journal->path . '-' . date('YmdHis') . '.xml';

        $isTestMode = $journal->getSetting('crossref_test_mode');
        
        // API Endpoints — loaded from system settings (database-driven, not hardcoded)
        $url = $isTestMode
            ? Settings::system('crossref_deposit_url_test', 'https://test.crossref.org/servlet/deposit')
            : Settings::system('crossref_deposit_url_live', 'https://doi.crossref.org/servlet/deposit');
        
        $username = $journal->getSetting('crossref_username');
        $password = '';
        $rawPassword = $journal->getSetting('crossref_password');
        if ($rawPassword) {
            try {
                $password = decrypt($rawPassword);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                // Fallback for legacy plaintext passwords not yet encrypted
                $password = $rawPassword;
            }
        }

        try {
            $response = Http::attach('fname', $xmlString, $filename)
                ->post($url, [
                    'operation' => 'doMDataUpload',
                    'login_id' => $username,
                    'login_passwd' => $password,
                ]);

            if ($response->successful()) {
                $status = 'Success';
                $message = 'Deposit successful. Crossref Response: ' . $response->body();
                // Depending on Crossref response, you might extract batch_id from the XML response string.
                // It usually returns a generic HTML/XML response stating queued processing.
            } else {
                $status = 'Failed';
                $message = 'HTTP Error ' . $response->status() . ': ' . $response->body();
            }
        } catch (\Exception $e) {
            $status = 'Failed';
            $message = 'Request failed: ' . $e->getMessage();
        }

        // Log the result
        foreach ($submissions as $submission) {
            CrossrefLog::create([
                'id' => (string) Str::uuid(),
                'journal_id' => $journal->id,
                'submission_id' => $submission->id,
                'status' => $status,
                'crossref_batch_id' => $batchId,
                'message' => substr($message, 0, 500),
            ]);

            // Update publication doi_status
            if ($status === 'Success') {
                $pub = $submission->currentPublication;
                if ($pub) {
                    $pub->update([
                        'doi_status' => 'submitted',
                        'crossref_batch_id' => $batchId
                    ]);
                }
            }
        }

        return [
            'status' => $status,
            'message' => $message
        ];
    }

    /**
     * Generate Crossref XML content for Issues.
     */
    public function generateIssueXml($issues, Journal $journal, $batchId = null)
    {
        $batchId = $batchId ?? (string) Str::uuid();
        // Menggunakan blade khusus untuk schema XML issue
        $content = view('journal.tools.crossref_issue_xml', compact('issues', 'journal', 'batchId'))->render();

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $content = trim($content);
        $content = preg_replace('/>\s+</', '><', $content);

        return '<?xml version="1.0" encoding="utf-8"?>' . "\n" . $content;
    }

    /**
     * Send Issue XML to Crossref.
     */
    public function depositIssues($issueIds, Journal $journal)
    {
        $hasDepositorInfo = $journal->getSetting('crossref_depositor_name') 
            && $journal->getSetting('crossref_depositor_email') 
            && $journal->getSetting('crossref_username');

        if (!$hasDepositorInfo) {
            return ['status' => 'Failed', 'message' => 'Crossref depositor info or username is missing.'];
        }

        $issues = \App\Models\Issue::whereIn('id', (array) $issueIds)
            ->where('journal_id', $journal->id)
            ->get();

        if ($issues->isEmpty()) {
            return ['status' => 'Failed', 'message' => 'No valid issues found.'];
        }

        // Fix: Generate batch ID once and pass it to both XML generator and database
        $batchId = (string) Str::uuid();
        $xmlString = $this->generateIssueXml($issues, $journal, $batchId);
        $filename = 'crossref-issue-' . $journal->path . '-' . date('YmdHis') . '.xml';

        $isTestMode = $journal->getSetting('crossref_test_mode');
        
        $url = $isTestMode
            ? Settings::system('crossref_deposit_url_test', 'https://test.crossref.org/servlet/deposit')
            : Settings::system('crossref_deposit_url_live', 'https://doi.crossref.org/servlet/deposit');
        
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

        try {
            $response = Http::attach('fname', $xmlString, $filename)
                ->post($url, [
                    'operation' => 'doMDataUpload',
                    'login_id' => $username,
                    'login_passwd' => $password,
                ]);

            if ($response->successful()) {
                $status = 'Success';
                $message = 'Deposit successful. Crossref Response: ' . $response->body();
            } else {
                $status = 'Failed';
                $message = 'HTTP Error ' . $response->status() . ': ' . $response->body();
            }
        } catch (\Exception $e) {
            $status = 'Failed';
            $message = 'Request failed: ' . $e->getMessage();
        }

        // Log the result and update issue status
        foreach ($issues as $issue) {
            CrossrefLog::create([
                'id' => (string) Str::uuid(),
                'journal_id' => $journal->id,
                'submission_id' => null, // null for issues
                'status' => $status,
                'crossref_batch_id' => $batchId,
                'message' => substr($message, 0, 500),
            ]);

            if ($status === 'Success') {
                $issue->update([
                    'doi_status' => 'submitted',
                    'crossref_batch_id' => $batchId
                ]);
            }
        }

        return ['status' => $status, 'message' => $message];
    }
}
