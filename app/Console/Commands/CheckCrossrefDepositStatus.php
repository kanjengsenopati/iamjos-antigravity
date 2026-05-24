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
                    // We check if the DOI resolves in the works API.
                    // If it returns 200, it is fully registered and active.
                    // If it returns 404, it is not yet registered (still processing or failed).
                    // For a more advanced implementation, checking the exact batch ID status could be done via
                    // https://api.crossref.org/deposits?filter=batch-id:{batch_id} (requires authentication).
                    // Using the /works/{doi} endpoint is simpler and does not require auth.
                    
                    $apiBaseUrl = rtrim(Settings::system('crossref_api_base_url', 'https://api.crossref.org/works/'), '/') . '/';
                    $url = $apiBaseUrl . urlencode($pub->doi);
                    $response = Http::timeout(10)->get($url);

                    if ($response->successful()) {
                        $pub->update(['doi_status' => 'active']);
                        $this->info("DOI {$pub->doi} is now ACTIVE.");
                        $processedCount++;
                    } elseif ($response->status() === 404) {
                        // Still not found. Could be processing or failed.
                        // We will keep it as 'submitted' so it checks again next time.
                        // If it has been 'submitted' for a very long time (e.g., > 3 days), we could mark it as failed.
                        // For now, we leave it as submitted.
                        $this->line("DOI {$pub->doi} is still pending (404).");
                    } else {
                        // Other errors (rate limiting, server errors)
                        $this->error("Error checking DOI {$pub->doi}: HTTP {$response->status()}");
                    }
                    
                    // Sleep to avoid rate limiting from Crossref API
                    usleep(200000); // 200ms
                    
                } catch (\Exception $e) {
                    $this->error("Failed to check DOI {$pub->doi}: " . $e->getMessage());
                    Log::error("Crossref Auto-Poll Error for DOI {$pub->doi}: " . $e->getMessage());
                }
            }
        }

        $this->info("Finished checking statuses. {$processedCount} DOIs activated.");
        return Command::SUCCESS;
    }
}
