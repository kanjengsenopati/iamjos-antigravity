<?php

namespace App\Jobs;

use App\Facades\Settings;
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

        // Load reminder configuration from system settings (database-driven)
        $reminderDaysRaw = Settings::system('reviewer_reminder_days_before', '7,3,1,0');
        $reminderDays = array_map('intval', array_filter(array_map('trim', explode(',', $reminderDaysRaw))));

        $overdueInterval = (int) Settings::system('reviewer_reminder_overdue_interval_days', 3);

        foreach ($assignments as $assignment) {
            $reviewer = $assignment->reviewer;
            if (!$reviewer) continue;

            $dueDate = $assignment->due_date;
            $daysLeft = now()->diffInDays($dueDate, false);

            $shouldRemind = false;
            $type = 'upcoming';

            // Reminder Logic (days configured via system settings):
            // - N days before due date (configurable via reviewer_reminder_days_before)
            // - Every N days after due date (configurable via reviewer_reminder_overdue_interval_days)

            if (in_array((int) $daysLeft, $reminderDays, true)) {
                $shouldRemind = true;
                $type = 'upcoming';
            } elseif ($daysLeft < 0 && $overdueInterval > 0 && abs($daysLeft) % $overdueInterval == 0) {
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
