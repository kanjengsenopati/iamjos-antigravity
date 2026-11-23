<?php

namespace App\Jobs;

use App\Models\Submission;
use App\Models\SubmissionIndexStat;
use App\Services\ScholarCheckerService;
use App\Services\WaGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckArticleIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $submissionId;

    /**
     * Create a new job instance.
     */
    public function __construct($submissionId)
    {
        $this->submissionId = $submissionId;
    }

    /**
     * Execute the job.
     */
    public function handle(ScholarCheckerService $checker): void
    {
        $submission = Submission::with(['journal', 'currentPublication', 'indexStat'])->find($this->submissionId);

        if (!$submission) {
            Log::error("CheckArticleIndexJob: Submission not found: {$this->submissionId}");
            return;
        }

        try {
            // 1. Perform Check (ScholarCheckerService throws on CAPTCHA / blocking so we never false-alert)
            $isIndexed = $checker->isIndexed($submission);

            // 2. Get or Create Stat Record
            $stat = SubmissionIndexStat::firstOrCreate(
                ['submission_id' => $submission->id],
                ['journal_id' => $submission->journal_id]
            );

            // 3. Check for De-indexing (Previous was true, current is false)
            if ($stat->is_indexed === true && $isIndexed === false) {
                $this->notifyJournalManager($submission);
            }

            // 4. Update Stats
            $stat->is_indexed = $isIndexed;
            $stat->last_check_status = $isIndexed ? 'found' : 'not_found';
            $stat->last_checked_at = now();
            $stat->save();

        } catch (\Throwable $e) {
            Log::error('CheckArticleIndexJob Error', ['error' => $e->getMessage()]);

            $stat = SubmissionIndexStat::firstOrCreate(
                ['submission_id' => $submission->id],
                ['journal_id' => $submission->journal_id]
            );
            $stat->last_check_status = 'error';
            $stat->last_checked_at = now();
            $stat->save();
        }
    }

    protected function notifyJournalManager(Submission $submission)
    {
        $journal = $submission->journal;
        if (!$journal) {
            return;
        }

        $manager = $journal->usersWithRole('Journal Manager')->first()
            ?? $journal->usersWithRole('journal_manager')->first();

        if ($manager && $manager->phone) {
            $title = $submission->currentPublication?->title ?? $submission->title;
            $message = "⚠️ Warning: Article '{$title}' appears to have dropped from Google Scholar index.";
            WaGateway::send($manager, $message, $journal);
            Log::info("ScholarWatchdog: Alert sent to manager for submission {$submission->id}");
        } else {
            Log::warning("ScholarWatchdog: No journal manager with phone found for journal {$journal->id}");
        }
    }
}
