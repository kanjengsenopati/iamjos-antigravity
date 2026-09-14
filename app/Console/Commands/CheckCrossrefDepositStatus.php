<?php

namespace App\Console\Commands;

use App\Facades\Settings;
use Illuminate\Console\Command;
use App\Models\Journal;
use App\Models\Publication;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckCrossrefDepositStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crossref:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Crossref for the status of pending DOI deposits';

    /**
     * Execute the console command.
     * 
     * Dual-Strategy Polling (Identik OJS):
     * 1. Works API (Primary) — Cek apakah DOI sudah resolving di registry global.
     *    Tidak butuh autentikasi, universal, dan paling reliable.
     * 2. Deposit API (Secondary) — Cek detail status deposit via batch_id.
     *    Butuh autentikasi, digunakan untuk mendeteksi FAILURE secara eksplisit.
     */
    public function handle()
    {
        $this->info('Starting Crossref Deposit Status check...');
        $journals = Journal::all();
        $processedCount = 0;

        foreach ($journals as $journal) {
            // Check if auto polling is enabled for this journal
            if (!$journal->getSetting('crossref_auto_poll_status')) {
                continue;
            }

            // Ambil kredensial sekali per journal
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

            // ---------------------------------------------------------
            // [PUBLICATIONS] Poll pending Article DOIs
            // ---------------------------------------------------------
            // FIX: Scope by journal_id to prevent cross-journal processing
            $pendingPublications = Publication::where('doi_status', 'submitted')
                ->whereNotNull('doi')
                ->whereHas('submission', function ($q) use ($journal) {
                    $q->where('journal_id', $journal->id);
                })
                ->get();

            if ($pendingPublications->isNotEmpty()) {
                $this->info("Found {$pendingPublications->count()} pending DOIs for Journal: {$journal->name}");

                foreach ($pendingPublications as $pub) {
                    try {
                        $result = $this->checkDoiStatus(
                            $pub->doi,
                            $pub->crossref_batch_id,
                            $username,
                            $password,
                            $depositorEmail
                        );

                        if ($result['status'] === 'active') {
                            $pub->update(['doi_status' => 'active']);
                            $this->info("DOI {$pub->doi} is now ACTIVE ({$result['source']}).");
                            $processedCount++;
                        } elseif ($result['status'] === 'failed') {
                            $pub->update(['doi_status' => 'failed']);
                            
                            \App\Models\CrossrefLog::create([
                                'id' => (string) \Illuminate\Support\Str::uuid(),
                                'journal_id' => $journal->id,
                                'submission_id' => $pub->submission_id,
                                'status' => 'Failed',
                                'crossref_batch_id' => $pub->crossref_batch_id,
                                'message' => substr('Crossref Error: ' . $result['message'], 0, 500),
                            ]);
                            
                            $this->error("DOI {$pub->doi} FAILED: {$result['message']}");
                        } else {
                            $this->line("DOI {$pub->doi} is still pending ({$result['detail']}).");
                        }

                        // Rate limiting: 300ms between API calls (Crossref etiquette)
                        usleep(300000);

                    } catch (\Exception $e) {
                        $this->error("Failed to check DOI {$pub->doi}: " . $e->getMessage());
                        Log::error("Crossref Auto-Poll Error for DOI {$pub->doi}: " . $e->getMessage());
                    }
                }
            }

            // ---------------------------------------------------------
            // [ISSUES] Poll pending Issue DOIs
            // ---------------------------------------------------------
            $pendingIssues = \App\Models\Issue::where('journal_id', $journal->id)
                ->where('doi_status', 'submitted')
                ->whereNotNull('doi')
                ->get();

            if ($pendingIssues->isNotEmpty()) {
                $this->info("Found {$pendingIssues->count()} pending Issue DOIs for Journal: {$journal->name}");

                foreach ($pendingIssues as $issue) {
                    try {
                        $result = $this->checkDoiStatus(
                            $issue->doi,
                            $issue->crossref_batch_id,
                            $username,
                            $password,
                            $depositorEmail
                        );

                        if ($result['status'] === 'active') {
                            $issue->update(['doi_status' => 'active']);
                            $this->info("Issue DOI {$issue->doi} is now ACTIVE ({$result['source']}).");
                            $processedCount++;
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

                            $this->error("Issue DOI {$issue->doi} FAILED. Logged rejection reason.");
                        } else {
                            $this->line("Issue DOI {$issue->doi} is still processing ({$result['detail']}).");
                        }

                        usleep(300000);

                    } catch (\Exception $e) {
                        $this->error("Exception while checking Issue DOI {$issue->doi}: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Finished checking statuses. {$processedCount} DOIs activated.");
        return Command::SUCCESS;
    }

    /**
     * Dual-Strategy DOI Status Check
     * 
     * Strategy 1 (Primary): Works API — cek apakah DOI sudah terdaftar di registry global.
     *   Endpoint: GET https://api.crossref.org/works/{DOI}
     *   Tidak butuh autentikasi. Jika 200 = DOI aktif. Jika 404 = belum aktif.
     *   Ini adalah metode yang sama digunakan oleh OJS.
     * 
     * Strategy 2 (Secondary): Deposit API — cek status batch deposit untuk mendeteksi kegagalan.
     *   Endpoint: GET https://api.crossref.org/deposits?filter=submission-id:{BATCH_ID}
     *   Butuh Basic Auth. Bisa mendeteksi status 'completed', 'failed', atau 'submitted'.
     *   Digunakan untuk menangkap pesan error eksplisit dari Crossref.
     * 
     * @return array{status: string, source: string, message: string, detail: string}
     */
    private function checkDoiStatus(
        string $doi,
        ?string $batchId,
        ?string $username,
        ?string $password,
        string $depositorEmail
    ): array {
        // =====================================================
        // STRATEGY 1: Works API (Primary — OJS-style)
        // =====================================================
        $apiBaseUrl = rtrim(Settings::system('crossref_api_base_url', 'https://api.crossref.org/works/'), '/') . '/';
        $worksUrl = $apiBaseUrl . urlencode($doi);

        try {
            // Crossref Polite Pool: sertakan mailto di User-Agent untuk rate limit lebih baik
            $worksResponse = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'IamJOS/1.0 (mailto:' . $depositorEmail . ')',
                ])
                ->get($worksUrl);

            if ($worksResponse->successful()) {
                // DOI sudah terdaftar dan resolving di registry global Crossref
                return [
                    'status' => 'active',
                    'source' => 'Works API',
                    'message' => 'DOI is resolving successfully.',
                    'detail' => 'confirmed',
                ];
            }
        } catch (\Exception $e) {
            // Works API gagal (timeout/network), lanjut ke Strategy 2
            Log::warning("Works API check failed for DOI {$doi}: " . $e->getMessage());
        }

        // =====================================================
        // STRATEGY 2: Deposit API (Secondary — Failure Detection)
        // =====================================================
        if ($batchId && !empty($username) && !empty($password)) {
            try {
                $depositUrl = "https://api.crossref.org/deposits?filter=submission-id:" . urlencode($batchId);
                $depositResponse = Http::withBasicAuth($username, $password)
                    ->timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'IamJOS/1.0 (mailto:' . $depositorEmail . ')',
                    ])
                    ->get($depositUrl);

                if ($depositResponse->successful()) {
                    $items = $depositResponse->json('message.items');

                    if (is_array($items) && count($items) > 0) {
                        $item = $items[0];
                        $crossrefStatus = $item['status'] ?? 'submitted';

                        if ($crossrefStatus === 'completed') {
                            return [
                                'status' => 'active',
                                'source' => 'Deposit API',
                                'message' => 'Deposit completed successfully.',
                                'detail' => 'completed',
                            ];
                        }

                        if ($crossrefStatus === 'failed') {
                            // Ekstrak pesan error detail dari Crossref
                            $errorLines = [];
                            $messages = $item['messages'] ?? [];
                            foreach ($messages as $msg) {
                                $msgType = $msg['msg_type'] ?? $msg['type'] ?? 'Error';
                                $msgText = $msg['msg'] ?? $msg['message'] ?? '';
                                if ($msgText) {
                                    $errorLines[] = "[{$msgType}] {$msgText}";
                                }
                            }
                            $finalError = !empty($errorLines) 
                                ? implode("\n", $errorLines) 
                                : 'Unknown Crossref Rejection';

                            return [
                                'status' => 'failed',
                                'source' => 'Deposit API',
                                'message' => $finalError,
                                'detail' => 'failed',
                            ];
                        }

                        // Masih dalam antrean
                        return [
                            'status' => 'pending',
                            'source' => 'Deposit API',
                            'message' => '',
                            'detail' => "deposit status: {$crossrefStatus}",
                        ];
                    }

                    // Batch belum muncul di API (masih sangat awal)
                    return [
                        'status' => 'pending',
                        'source' => 'Deposit API',
                        'message' => '',
                        'detail' => 'batch not yet visible in Crossref API',
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("Deposit API check failed for batch {$batchId}: " . $e->getMessage());
            }
        }

        // Kedua strategy tidak menghasilkan jawaban definitif
        return [
            'status' => 'pending',
            'source' => 'None',
            'message' => '',
            'detail' => 'waiting for Crossref processing (Works API 404, no Deposit API data)',
        ];
    }
}
