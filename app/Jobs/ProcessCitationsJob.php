<?php

namespace App\Jobs;

use App\Models\Citation;
use App\Models\Publication;
use App\Services\CitationParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Process a publication's raw references into structured citations.
 *
 * This job is dispatched by PublicationObserver when references change,
 * or by the citations:process artisan command for bulk processing.
 *
 * The job is idempotent — it deletes existing citations for the publication
 * and re-creates them from scratch based on current references text.
 *
 * publications.references (source of truth) is NEVER modified by this job.
 */
class ProcessCitationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $publicationId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $publication = Publication::find($this->publicationId);

        if (!$publication) {
            Log::warning("[ProcessCitationsJob] Publication not found: {$this->publicationId}");
            return;
        }

        if (empty(trim($publication->references ?? ''))) {
            // No references — clean up any stale citations and exit
            Citation::where('publication_id', $publication->id)->delete();
            Log::info("[ProcessCitationsJob] No references for publication {$publication->id}, cleared citations.");
            return;
        }

        // Parse all references into structured data
        $parsedResults = CitationParserService::parseAll($publication->references);

        if (empty($parsedResults)) {
            return;
        }

        // Idempotent: delete existing citations, then re-create
        Citation::where('publication_id', $publication->id)->delete();

        $structuredCount = 0;
        $totalCount = 0;

        foreach ($parsedResults as $parsed) {
            try {
                Citation::create([
                    'publication_id'    => $publication->id,
                    'seq'               => $parsed['seq'],
                    'raw_citation'      => $parsed['raw_citation'],
                    'doi'               => $parsed['doi'],
                    'title'             => $parsed['title'],
                    'authors'           => $parsed['authors'],
                    'year'              => $parsed['year'],
                    'source'            => $parsed['source'],
                    'volume'            => $parsed['volume'],
                    'issue'             => $parsed['issue'],
                    'first_page'        => $parsed['first_page'],
                    'last_page'         => $parsed['last_page'],
                    'url'               => $parsed['url'],
                    'is_structured'     => $parsed['is_structured'],
                    'processing_status' => 'processed',
                ]);

                $totalCount++;
                if ($parsed['is_structured']) {
                    $structuredCount++;
                }
            } catch (\Exception $e) {
                Log::error("[ProcessCitationsJob] Failed to create citation seq {$parsed['seq']} for pub {$publication->id}: {$e->getMessage()}");
            }
        }

        Log::info("[ProcessCitationsJob] Processed {$totalCount} citations for publication {$publication->id} ({$structuredCount} structured).");
    }
}
