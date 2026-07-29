<?php

namespace App\Console\Commands;

use App\Models\Submission;
use App\Models\SubmissionLog;
use Illuminate\Console\Command;

class BackfillPublishedLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'submissions:backfill-published-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill missing published activity logs for published submissions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Scanning for published submissions with missing activity logs...');

        $submissions = Submission::where('status', Submission::STATUS_PUBLISHED)
            ->orWhereNotNull('published_at')
            ->get();

        $count = 0;
        foreach ($submissions as $submission) {
            $hasLog = SubmissionLog::where('submission_id', $submission->id)
                ->where('event_type', SubmissionLog::EVENT_PUBLISHED)
                ->exists();

            if (!$hasLog) {
                SubmissionLog::ensurePublishedLog($submission);
                $count++;
            }
        }

        $this->info("✅ Successfully backfilled {$count} published activity log entries.");

        return Command::SUCCESS;
    }
}
