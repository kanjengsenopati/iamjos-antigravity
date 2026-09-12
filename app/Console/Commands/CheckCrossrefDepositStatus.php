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

            // Find publications that are submitted but not yet active
            $pendingPublications = Publication::where('doi_status', 'submitted')
                ->whereNotNull('doi')
                ->get();

            if ($pendingPublications->isEmpty()) {
                continue;
            }

            $this->info("Found {$pendingPublications->count()} pending DOIs for Journal: {$journal->name}");

            foreach ($pendingPublications as $pub) {
                try {
                    // Gunakan Advanced Polling (Deposit API) via batch_id
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

                    // Jika tidak ada batch_id atau kredensial, fallback ke Works API (Legacy)
                    if (!$pub->crossref_batch_id || empty($username) || empty($password)) {
                        $apiBaseUrl = rtrim(Settings::system('crossref_api_base_url', 'https://api.crossref.org/works/'), '/') . '/';
                        $url = $apiBaseUrl . urlencode($pub->doi);
                        $response = Http::timeout(10)->get($url);

                        if ($response->successful()) {
                            $pub->update(['doi_status' => 'active']);
                            $this->info("DOI {$pub->doi} is now ACTIVE (via Legacy Works API).");
                            $processedCount++;
                        } elseif ($response->status() === 404) {
                            $this->line("DOI {$pub->doi} is still pending/404 (via Legacy).");
                        } else {
                            $this->error("Error checking DOI {$pub->doi}: HTTP {$response->status()}");
                        }
                    } else {
                        // Advanced Deposit API Polling
                        $url = "https://api.crossref.org/deposits?filter=submission-id:" . urlencode($pub->crossref_batch_id);
                        $response = Http::withBasicAuth($username, $password)->timeout(10)->get($url);

                        if ($response->successful()) {
                            $items = $response->json('message.items');
                            if (is_array($items) && count($items) > 0) {
                                $item = $items[0];
                                $crossrefStatus = $item['status'] ?? 'submitted';
                                
                                if ($crossrefStatus === 'completed') {
                                    $pub->update(['doi_status' => 'active']);
                                    $this->info("DOI {$pub->doi} is now ACTIVE (Deposit API).");
                                    $processedCount++;
                                } elseif ($crossrefStatus === 'failed') {
                                    $pub->update(['doi_status' => 'failed']);
                                    $messages = $item['messages'] ?? [];
                                    $errorText = "Crossref rejected deposit.";
                                    if (!empty($messages)) {
                                        $errorTexts = array_map(function($msg) {
                                            return $msg['message'] ?? '';
                                        }, $messages);
                                        $errorText = implode(' | ', $errorTexts);
                                    }
                                    
                                    // Log the explicit error back to crossref_logs
                                    \App\Models\CrossrefLog::create([
                                        'id' => (string) \Illuminate\Support\Str::uuid(),
                                        'journal_id' => $journal->id,
                                        'submission_id' => $pub->submission_id,
                                        'status' => 'Failed',
                                        'crossref_batch_id' => $pub->crossref_batch_id,
                                        'message' => 'Crossref Error: ' . substr($errorText, 0, 480),
                                    ]);
                                    
                                    $this->error("DOI {$pub->doi} FAILED: $errorText");
                                } else {
                                    $this->line("DOI {$pub->doi} is still pending (status: {$crossrefStatus}).");
                                }
                            } else {
                                $this->line("DOI {$pub->doi} deposit is not yet available in Crossref API (might take a few minutes).");
                            }
                        } else {
                            $this->error("Error connecting to Deposit API for DOI {$pub->doi}: HTTP {$response->status()}");
                        }
                    }
                    
                    // Sleep to avoid rate limiting from Crossref API
                    usleep(300000); // 300ms
                    
                } catch (\Exception $e) {
                    $this->error("Failed to check DOI {$pub->doi}: " . $e->getMessage());
                    Log::error("Crossref Auto-Poll Error for DOI {$pub->doi}: " . $e->getMessage());
                }
            }

            // ---------------------------------------------------------
            // [IAMJOS-CROSSREF-ISSUE] Poll pending Issues
            // ---------------------------------------------------------
            $pendingIssues = \App\Models\Issue::where('journal_id', $journal->id)
                ->where('doi_status', 'submitted')
                ->whereNotNull('doi')
                ->get();

            if ($pendingIssues->isNotEmpty()) {
                $this->info("Found {$pendingIssues->count()} pending Issue DOIs for Journal: {$journal->name}");

                foreach ($pendingIssues as $issue) {
                    try {
                        $username = $journal->getSetting('crossref_username');
                        $rawPassword = $journal->getSetting('crossref_password');
                        $password = '';
                        if ($rawPassword) {
                            try { $password = decrypt($rawPassword); } 
                            catch (\Exception $e) { $password = $rawPassword; }
                        }

                        if (!$issue->crossref_batch_id || empty($username) || empty($password)) {
                            $apiBaseUrl = rtrim(Settings::system('crossref_api_base_url', 'https://api.crossref.org/works/'), '/') . '/';
                            $url = $apiBaseUrl . urlencode($issue->doi);
                            $response = Http::timeout(10)->get($url);

                            if ($response->successful()) {
                                $issue->update(['doi_status' => 'active']);
                                $this->info("Issue DOI {$issue->doi} is now ACTIVE (Legacy).");
                                $processedCount++;
                            } elseif ($response->status() === 404) {
                                $this->line("Issue DOI {$issue->doi} is still pending (Legacy).");
                            }
                        } else {
                            $url = "https://api.crossref.org/deposits?filter=submission-id:" . urlencode($issue->crossref_batch_id);
                            $response = Http::withBasicAuth($username, $password)->timeout(10)->get($url);

                            if ($response->successful()) {
                                $items = $response->json('message.items');
                                if (is_array($items) && count($items) > 0) {
                                    $item = $items[0];
                                    $crossrefStatus = $item['status'] ?? 'submitted';
                                    
                                    if ($crossrefStatus === 'completed') {
                                        $issue->update(['doi_status' => 'active']);
                                        $this->info("Issue DOI {$issue->doi} is now ACTIVE (Deposit API).");
                                        $processedCount++;
                                    } elseif ($crossrefStatus === 'failed') {
                                        $issue->update(['doi_status' => 'failed']);
                                        $messages = $item['messages'] ?? [];
                                        
                                        $errorLines = [];
                                        foreach ($messages as $msg) {
                                            $msgType = $msg['msg_type'] ?? 'Error';
                                            $msgText = $msg['msg'] ?? '';
                                            $errorLines[] = "[$msgType] $msgText";
                                        }
                                        $finalErrorMessage = !empty($errorLines) ? implode("\n", $errorLines) : 'Unknown Crossref Rejection';
                                        
                                        \App\Models\CrossrefLog::create([
                                            'id' => (string) \Illuminate\Support\Str::uuid(),
                                            'journal_id' => $journal->id,
                                            'submission_id' => null,
                                            'status' => 'Failed',
                                            'crossref_batch_id' => $issue->crossref_batch_id,
                                            'message' => substr($finalErrorMessage, 0, 500),
                                        ]);
                                        
                                        $this->error("Issue DOI {$issue->doi} FAILED. Logged rejection reason.");
                                    } else {
                                        $this->line("Issue DOI {$issue->doi} is still processing ({$crossrefStatus}).");
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $this->error("Exception while checking Issue DOI {$issue->doi}: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Finished checking statuses. {$processedCount} DOIs activated.");
        return Command::SUCCESS;
    }
}
