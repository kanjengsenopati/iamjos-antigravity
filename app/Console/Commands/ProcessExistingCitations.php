<?php

namespace App\Console\Commands;

use App\Jobs\ProcessCitationsJob;
use App\Models\Journal;
use App\Models\Publication;
use Illuminate\Console\Command;

/**
 * Bulk-process existing publications' references into structured citations.
 *
 * Usage:
 *   php artisan citations:process                    # All published publications without citations
 *   php artisan citations:process --journal=tawazun  # Specific journal by slug
 *   php artisan citations:process --force             # Re-process all (overwrite existing)
 *
 * This command dispatches ProcessCitationsJob for each qualifying publication.
 * The publications.references column is NEVER modified.
 */
class ProcessExistingCitations extends Command
{
    protected $signature = 'citations:process 
                            {--journal= : Process only publications from this journal slug}
                            {--force : Re-process even if citations already exist}';

    protected $description = 'Parse existing publication references into structured citations for better Google Scholar indexing';

    public function handle(): int
    {
        $this->info('🔍 Starting citation processing...');

        $query = Publication::query()
            ->where('status', Publication::STATUS_PUBLISHED)
            ->whereNotNull('references')
            ->where('references', '!=', '');

        // Filter by journal if specified
        if ($journalSlug = $this->option('journal')) {
            $journal = Journal::where('slug', $journalSlug)->first();

            if (!$journal) {
                $this->error("❌ Journal '{$journalSlug}' not found.");
                return self::FAILURE;
            }

            $query->whereHas('submission', function ($q) use ($journal) {
                $q->where('journal_id', $journal->id);
            });

            $this->info("📚 Filtering by journal: {$journal->name}");
        }

        // Skip already-processed unless --force
        if (!$this->option('force')) {
            $query->whereDoesntHave('citations');
            $this->info('⏭️  Skipping publications that already have citations (use --force to override)');
        }

        $publications = $query->get();
        $total = $publications->count();

        if ($total === 0) {
            $this->info('✅ No publications to process.');
            return self::SUCCESS;
        }

        $this->info("📋 Found {$total} publications to process.");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $dispatched = 0;

        foreach ($publications as $publication) {
            try {
                ProcessCitationsJob::dispatch($publication->id);
                $dispatched++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->warn("⚠️  Failed to dispatch for publication {$publication->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Dispatched {$dispatched}/{$total} citation processing jobs.");
        $this->info('💡 Jobs will be processed by the queue worker. Run `php artisan queue:work` if not already running.');

        return self::SUCCESS;
    }
}
