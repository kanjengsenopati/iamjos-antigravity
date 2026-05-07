<?php

namespace App\Jobs;

use App\Models\ReviewAssignment;
use App\Notifications\ReviewerReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReviewerReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Running ReviewerReminderJob...');

        // 1. Get assignments that are NOT completed/declined/cancelled
        $assignments = ReviewAssignment::whereIn('status', [
            ReviewAssignment::STATUS_PENDING,
            ReviewAssignment::STATUS_ACCEPTED,
        ])
        ->whereNotNull('due_date')
        ->with(['reviewer', 'submission.journal'])
        ->get();

        $reminderCount = 0;

        foreach ($assignments as $assignment) {
            $reviewer = $assignment->reviewer;
            if (!$reviewer) continue;

            $dueDate = $assignment->due_date;
            $daysLeft = now()->diffInDays($dueDate, false);

            $shouldRemind = false;
            $type = 'upcoming';

            // Reminder Logic:
            // - 7 days before due date
            // - 3 days before due date
            // - 1 day before due date
            // - On due date
            // - Every 3 days after due date (overdue)

            if ($daysLeft == 7 || $daysLeft == 3 || $daysLeft == 1 || $daysLeft == 0) {
                $shouldRemind = true;
                $type = 'upcoming';
            } elseif ($daysLeft < 0 && abs($daysLeft) % 3 == 0) {
                $shouldRemind = true;
                $type = 'overdue';
            }

            if ($shouldRemind) {
                try {
                    $reviewer->notify(new ReviewerReminder($assignment, $type));
                    $reminderCount++;
                    
                    Log::info("Reminder sent to {$reviewer->name} for assignment {$assignment->slug} ({$type})");
                } catch (\Exception $e) {
                    Log::error("Failed to send reminder to {$reviewer->name}: " . $e->getMessage());
                }
            }
        }

        Log::info("ReviewerReminderJob finished. Total reminders sent: {$reminderCount}");
    }
}
