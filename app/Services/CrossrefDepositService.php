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
            ->with(['authors', 'issue', 'currentPublication.authors', 'galleys.file'])
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

        $isTestMode = (bool) $journal->getSetting('crossref_test_mode');
        
        // API Endpoints — loaded from system settings (database-driven, not hardcoded)
        $defaultUrl = $isTestMode ? 'https://test.crossref.org/servlet/deposit' : 'https://doi.crossref.org/servlet/deposit';
        $settingUrl = $isTestMode
            ? Settings::system('crossref_deposit_url_test', $defaultUrl)
            : Settings::system('crossref_deposit_url_live', $defaultUrl);
        $url = !empty($settingUrl) ? $settingUrl : $defaultUrl;
        
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
            Log::channel('single')->info('[CrossrefDeposit] Starting deposit', [
                'batch_id' => $batchId,
                'endpoint' => $url,
                'xml_size' => strlen($xmlString),
                'username' => $username,
            ]);

            $response = Http::timeout(30)
                ->attach('fname', $xmlString, $filename)
                ->post($url, [
                    'operation' => 'doMDUpload',
                    'login_id' => $username,
                    'login_passwd' => $password,
                ]);

            $body = (string) $response->body();
            $isAccepted = $response->successful()
                && (stripos($body, 'SUCCESS') !== false || stripos($body, 'successfully received') !== false)
                && stripos($body, 'FAILURE') === false;

            if ($isAccepted) {
                $status = 'Success';
                $message = 'Deposit successful. Crossref Response: ' . $body;
                Log::channel('single')->info('[CrossrefDeposit] Request successful', ['batch_id' => $batchId, 'status' => $response->status()]);
            } else {
                $status = 'Failed';
                $message = $response->successful()
                    ? 'Crossref rejected deposit: ' . $body
                    : 'HTTP Error ' . $response->status() . ': ' . $body;
                Log::channel('single')->error('[CrossrefDeposit] HTTP Error / Deposit rejected', ['batch_id' => $batchId, 'status' => $response->status(), 'response' => $body]);
            }
        } catch (\Exception $e) {
            $status = 'Failed';
            $message = 'Request failed: ' . $e->getMessage();
            Log::channel('single')->error('[CrossrefDeposit] Exception', ['batch_id' => $batchId, 'error' => $e->getMessage()]);
            // No need to re-throw since we run synchronously and return the status directly to the controller
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
            $pub = $submission->currentPublication;
            if ($pub) {
                if ($status === 'Success') {
                    $pub->update([
                        'doi_status' => 'submitted',
                        'crossref_batch_id' => $batchId
                    ]);
                } elseif ($status === 'Failed') {
                    $pub->update([
                        'doi_status' => 'failed',
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

        $isTestMode = (bool) $journal->getSetting('crossref_test_mode');
        
        $defaultUrl = $isTestMode ? 'https://test.crossref.org/servlet/deposit' : 'https://doi.crossref.org/servlet/deposit';
        $settingUrl = $isTestMode
            ? Settings::system('crossref_deposit_url_test', $defaultUrl)
            : Settings::system('crossref_deposit_url_live', $defaultUrl);
        $url = !empty($settingUrl) ? $settingUrl : $defaultUrl;
        
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
            Log::channel('single')->info('[CrossrefDeposit-Issue] Starting deposit', [
                'batch_id' => $batchId,
                'endpoint' => $url,
                'xml_size' => strlen($xmlString),
                'username' => $username,
            ]);

            $response = Http::timeout(30)
                ->attach('fname', $xmlString, $filename)
                ->post($url, [
                    'operation' => 'doMDUpload',
                    'login_id' => $username,
                    'login_passwd' => $password,
                ]);

            $body = (string) $response->body();
            $isAccepted = $response->successful()
                && (stripos($body, 'SUCCESS') !== false || stripos($body, 'successfully received') !== false)
                && stripos($body, 'FAILURE') === false;

            if ($isAccepted) {
                $status = 'Success';
                $message = 'Deposit successful. Crossref Response: ' . $body;
                Log::channel('single')->info('[CrossrefDeposit-Issue] Request successful', ['batch_id' => $batchId, 'status' => $response->status()]);
            } else {
                $status = 'Failed';
                $message = $response->successful()
                    ? 'Crossref rejected deposit: ' . $body
                    : 'HTTP Error ' . $response->status() . ': ' . $body;
                Log::channel('single')->error('[CrossrefDeposit-Issue] HTTP Error / Deposit rejected', ['batch_id' => $batchId, 'status' => $response->status(), 'response' => $body]);
            }
        } catch (\Exception $e) {
            $status = 'Failed';
            $message = 'Request failed: ' . $e->getMessage();
            Log::channel('single')->error('[CrossrefDeposit-Issue] Exception', ['batch_id' => $batchId, 'error' => $e->getMessage()]);
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
            } elseif ($status === 'Failed') {
                $issue->update([
                    'doi_status' => 'failed',
                    'crossref_batch_id' => $batchId
                ]);
            }
        }

        return ['status' => $status, 'message' => $message];
    }

    /**
     * Dual-Strategy DOI Status Check (Identical to PKP OJS)
     *
     * Strategy 1 (Primary): Works API — checks if DOI is resolving in the global Crossref registry.
     *   Endpoint: GET https://api.crossref.org/works/{DOI} (DOIs retain their slashes)
     *
     * Strategy 2 (Secondary): Diagnostic Servlet — PKP OJS official diagnostic endpoint
     *   Endpoint: GET {baseUrl}/servlet/submissionDownload?usr={username}&pwd={password}&doi_batch_id={batchId}&type=result
     *
     * @return array{status: string, source: string, message: string, detail: string}
     */
    public function checkDoiStatus(
        string $doi,
        ?string $batchId,
        ?string $username,
        ?string $password,
        string $depositorEmail,
        bool $isTestMode = false
    ): array {
        // =====================================================
        // STRATEGY 1: Works API (Primary — OJS-style)
        // Fix: Do not replace '/' with '%2F' when querying works API
        // =====================================================
        $apiBaseUrl = rtrim(Settings::system('crossref_api_base_url', 'https://api.crossref.org/works/'), '/') . '/';
        $cleanDoi = trim($doi);
        $cleanDoi = preg_replace('/^https?:\/\/(?:dx\.)?doi\.org\//i', '', $cleanDoi);
        $cleanDoi = preg_replace('/^doi:\s*/i', '', $cleanDoi);
        $cleanDoi = trim($cleanDoi);
        $worksUrl = $apiBaseUrl . $cleanDoi;

        try {
            $worksResponse = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'IamJOS/1.0 (mailto:' . $depositorEmail . ')',
                ])
                ->get($worksUrl);

            if ($worksResponse->successful()) {
                return [
                    'status' => 'active',
                    'source' => 'Works API',
                    'message' => 'DOI is resolving successfully.',
                    'detail' => 'confirmed',
                ];
            }
        } catch (\Exception $e) {
            Log::warning("Works API check failed for DOI {$doi}: " . $e->getMessage());
        }

        // =====================================================
        // STRATEGY 2: PKP OJS Diagnostic Servlet (Secondary)
        // =====================================================
        if ($batchId && !empty($username) && !empty($password)) {
            try {
                $baseUrl = $isTestMode ? 'https://test.crossref.org' : 'https://doi.crossref.org';
                $diagUrl = "{$baseUrl}/servlet/submissionDownload?usr=" . urlencode($username)
                    . "&pwd=" . urlencode($password)
                    . "&doi_batch_id=" . urlencode($batchId)
                    . "&type=result";

                $diagResponse = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'IamJOS/1.0 (mailto:' . $depositorEmail . ')',
                    ])
                    ->get($diagUrl);

                if ($diagResponse->successful()) {
                    $body = trim($diagResponse->body());

                    if (!empty($body) && str_starts_with($body, '<')) {
                        $prevSetting = libxml_use_internal_errors(true);
                        $xml = simplexml_load_string($body);
                        libxml_use_internal_errors($prevSetting);

                        if ($xml !== false) {
                            $rootStatus = strtolower((string) ($xml['status'] ?? ''));
                            $failureCount = (int) ($xml->batch_data->failure_count ?? 0);
                            $successCount = (int) ($xml->batch_data->success_count ?? 0);

                            // 1. Check for record_diagnostic matching this specific DOI
                            if (isset($xml->record_diagnostic)) {
                                foreach ($xml->record_diagnostic as $rec) {
                                    $recDoi = trim((string) ($rec->doi ?? ''));
                                    $recDoiClean = preg_replace('/^https?:\/\/(?:dx\.)?doi\.org\//i', '', $recDoi);
                                    $recDoiClean = preg_replace('/^doi:\s*/i', '', $recDoiClean);
                                    $recDoiClean = trim($recDoiClean);

                                    $isMatch = (!empty($recDoiClean) && strcasecmp($recDoiClean, $cleanDoi) === 0);

                                    if ($isMatch) {
                                        $recStatus = strtolower((string) ($rec['status'] ?? ''));
                                        $msg = trim((string) ($rec->msg ?? ''));

                                        if ($recStatus === 'failure') {
                                            return [
                                                'status' => 'failed',
                                                'source' => 'Diagnostic Servlet',
                                                'message' => $msg ?: 'Deposit rejected for this DOI.',
                                                'detail' => 'record_failure',
                                            ];
                                        }

                                        if ($recStatus === 'success') {
                                            return [
                                                'status' => 'active',
                                                'source' => 'Diagnostic Servlet',
                                                'message' => $msg ?: 'Deposit completed and registered in Crossref.',
                                                'detail' => 'completed',
                                            ];
                                        }

                                        if ($recStatus === 'warning') {
                                            return [
                                                'status' => 'active',
                                                'source' => 'Diagnostic Servlet',
                                                'message' => $msg ?: 'Deposit accepted with warnings.',
                                                'detail' => 'completed_with_warning',
                                            ];
                                        }
                                    }
                                }
                            }

                            // 2. If no specific record_diagnostic matched, evaluate overall batch status
                            $batchErrorMessages = [];
                            if (isset($xml->record_diagnostic)) {
                                foreach ($xml->record_diagnostic as $rec) {
                                    $recStatus = strtolower((string) ($rec['status'] ?? ''));
                                    $msg = trim((string) ($rec->msg ?? ''));
                                    if ($recStatus === 'failure' && $msg) {
                                        $batchErrorMessages[] = $msg;
                                    }
                                }
                            }

                            if ($rootStatus === 'completed') {
                                if ($failureCount > 0) {
                                    return [
                                        'status' => 'failed',
                                        'source' => 'Diagnostic Servlet',
                                        'message' => !empty($batchErrorMessages) ? implode('; ', $batchErrorMessages) : 'Deposit rejected by Crossref diagnostic check.',
                                        'detail' => 'failure_reported',
                                    ];
                                }

                                if ($successCount > 0) {
                                    return [
                                        'status' => 'active',
                                        'source' => 'Diagnostic Servlet',
                                        'message' => 'Deposit completed and registered in Crossref.',
                                        'detail' => 'completed',
                                    ];
                                }
                            } elseif ($rootStatus === 'failed') {
                                return [
                                    'status' => 'failed',
                                    'source' => 'Diagnostic Servlet',
                                    'message' => !empty($batchErrorMessages) ? implode('; ', $batchErrorMessages) : 'Batch diagnostic reported failed status.',
                                    'detail' => 'failed',
                                ];
                            }

                            return [
                                'status' => 'pending',
                                'source' => 'Diagnostic Servlet',
                                'message' => '',
                                'detail' => "diagnostic status: {$rootStatus}",
                            ];
                        }
                    } elseif (!empty($body)) {
                        Log::warning("Diagnostic Servlet returned non-XML response for batch {$batchId}: {$body}");
                        if (stripos($body, 'invalid') !== false || stripos($body, 'error') !== false || stripos($body, 'failed') !== false) {
                            return [
                                'status' => 'pending',
                                'source' => 'Diagnostic Servlet',
                                'message' => $body,
                                'detail' => 'diagnostic servlet: ' . substr($body, 0, 100),
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Diagnostic Servlet check failed for batch {$batchId}: " . $e->getMessage());
            }
        }

        return [
            'status' => 'pending',
            'source' => 'None',
            'message' => '',
            'detail' => 'waiting for Crossref processing',
        ];
    }
}
