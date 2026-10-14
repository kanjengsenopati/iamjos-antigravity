<?php

namespace App\Console\Commands;

use App\Facades\Settings;
use App\Services\CrossrefDepositService;
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
     * 2. Diagnostic Servlet (Secondary) — PKP OJS official diagnostic endpoint via batch_id.
     *    Mendeteksi status completed, failed, dan pesan penolakan eksplisit.
     */
    public function handle(CrossrefDepositService $crossrefService)
    {
        $this->info('Starting Crossref Deposit Status check...');
        $journals = Journal::all();
        $processedCount = 0;

        foreach ($journals as $journal) {
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
            $isTestMode = (bool) $journal->getSetting('crossref_test_mode');

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
                        $result = $crossrefService->checkDoiStatus(
                            $pub->doi,
                            $pub->crossref_batch_id,
                            $username,
                            $password,
                            $depositorEmail,
                            $isTestMode
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
                        $result = $crossrefService->checkDoiStatus(
                            $issue->doi,
                            $issue->crossref_batch_id,
                            $username,
                            $password,
                            $depositorEmail,
                            $isTestMode
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
     * Dual-Strategy DOI Status Check (delegated to CrossrefDepositService)
     *
     * @return array{status: string, source: string, message: string, detail: string}
     */
    protected function checkDoiStatus(
        string $doi,
        ?string $batchId,
        ?string $username,
        ?string $password,
        string $depositorEmail,
        bool $isTestMode = false
    ): array {
        return app(CrossrefDepositService::class)->checkDoiStatus(
            $doi,
            $batchId,
            $username,
            $password,
            $depositorEmail,
            $isTestMode
        );
    }
}
