<?php

namespace App\Console\Commands;

use App\Jobs\ProcessCitationsJob;
use App\Models\Citation;
use App\Models\Journal;
use App\Models\Publication;
use App\Models\Submission;
use Illuminate\Console\Command;

/**
 * Bulk-process existing publications' references into structured citations
 * and recalculate internal cross-journal citation counts.
 *
 * Usage:
 *   php artisan citations:process                    # All published publications without citations
 *   php artisan citations:process --journal=tawazun  # Specific journal by slug
 *   php artisan citations:process --force             # Re-process all (overwrite existing)
 *   php artisan citations:process --sync              # Process synchronously without queue worker
 *
 * The publications.references column is NEVER modified.
 */
class ProcessExistingCitations extends Command
{
    protected $signature = 'citations:process 
                            {--journal= : Process only publications from this journal slug}
                            {--force : Re-process even if citations already exist}
                            {--sync : Run jobs synchronously in the current process}';

    protected $description = 'Parse existing publication references into structured citations, link cross-journal citations, and recalculate citation counts';

    public function handle(): int
    {
        $this->info('🔍 Starting citation processing and backfill...');

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

        $runSync = $this->option('sync');

        if ($total > 0) {
            $this->info("📋 Found {$total} publications to process." . ($runSync ? ' (running synchronously)' : ''));

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $processed = 0;

            foreach ($publications as $publication) {
                try {
                    if ($runSync) {
                        ProcessCitationsJob::dispatchSync($publication->id);
                    } else {
                        ProcessCitationsJob::dispatch($publication->id);
                    }
                    $processed++;
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->warn("⚠️  Failed for publication {$publication->id}: {$e->getMessage()}");
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            $this->info("✅ " . ($runSync ? 'Processed' : 'Dispatched') . " {$processed}/{$total} publications.");
        } else {
            $this->info('ℹ️  No new publications needed parsing.');
        }

        // Recalculate citation counts across published submissions
        $this->info('🔄 Recalculating citation counts for published submissions...');
        $submissionQuery = Submission::where('status', Submission::STATUS_PUBLISHED);
        if ($journalSlug = $this->option('journal')) {
            if ($journal = Journal::where('slug', $journalSlug)->first()) {
                $submissionQuery->where('journal_id', $journal->id);
            }
        }

        $submissions = $submissionQuery->get();
        $recalculated = 0;
        $citedCount = 0;

        foreach ($submissions as $submission) {
            $count = $submission->recalculateCitationCount();
            $recalculated++;
            if ($count > 0) {
                $citedCount++;
            }
        }

        $totalCitationsInDb = Citation::count();
        $totalLinkedCitations = Citation::whereNotNull('target_submission_id')->count();

        $this->info("📊 Recalculated citation counts for {$recalculated} submissions.");
        $this->info("📈 Total citations in database: {$totalCitationsInDb}");
        $this->info("🔗 Internal cross-journal links: {$totalLinkedCitations}");
        $this->info("🌟 Submissions with internal citations: {$citedCount}");

        return self::SUCCESS;
    }
}
