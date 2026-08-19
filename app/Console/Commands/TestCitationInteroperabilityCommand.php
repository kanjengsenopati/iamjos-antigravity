<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Submission;
use App\Services\CitationService;

class TestCitationInteroperabilityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:citation-interop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Citation Interoperability and Google Scholar compliance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $submission = Submission::with(['journal', 'issue', 'currentPublication', 'authors'])->where('status', 'published')->first();

        if (!$submission) {
            $this->error('No published submissions found to test.');
            return;
        }

        $citationService = app(CitationService::class);
        $this->info("Testing Citation Interoperability for Submission ID: {$submission->id} ({$submission->title})");

        // 1. Generate APA Citation
        $this->info("\n--- APA Citation ---");
        $apa = $citationService->generateAPA($submission);
        $this->line($apa);
        
        if (str_contains($apa, 'http')) {
            $this->info("SUCCESS: URL found in APA citation.");
        } else {
            $this->warn("WARNING: No URL found in APA citation.");
        }

        // 2. Generate COinS
        $this->info("\n--- COinS ---");
        $coins = $citationService->generateCOinS($submission, $submission->journal);
        $this->line($coins);
        
        if (str_contains($coins, 'Z3988')) {
            $this->info("SUCCESS: COinS span generated.");
        } else {
            $this->warn("WARNING: COinS span missing.");
        }

        // 3. RIS endpoint route generation
        $this->info("\n--- RIS Endpoint ---");
        try {
            $risRoute = route('citation.ris', ['journal' => $submission->journal->slug, 'article' => $submission->seq_id]);
            $this->line($risRoute);
            $this->info("SUCCESS: RIS route generated successfully.");
        } catch (\Exception $e) {
            $this->error("FAILED: Could not generate RIS route: " . $e->getMessage());
        }
        
        $this->info("\nTest completed.");
    }
}
