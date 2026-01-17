<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\GeneralNotificationMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBroadcastNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 60, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $recipient,
        public string $subject,
        public string $body,
        public string $journalName
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Basic SMTP configuration check
            if (!config('mail.mailers.smtp.host') || !config('mail.mailers.smtp.username')) {
                Log::warning("SendBroadcastNotificationJob: SMTP not configured. Skipping email for user {$this->recipient->id}.");
                return;
            }

            // Validate recipient email
            if (empty($this->recipient->email)) {
                Log::warning("SendBroadcastNotificationJob: No email found for user {$this->recipient->id}.");
                return;
            }

            // Send the email
            Mail::to($this->recipient->email)->send(
                new GeneralNotificationMail(
                    $this->subject,
                    $this->body,
                    $this->recipient->name,
                    $this->journalName
                )
            );

            Log::info("Broadcast notification sent successfully to {$this->recipient->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send broadcast notification to user {$this->recipient->id}: " . $e->getMessage());
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendBroadcastNotificationJob permanently failed for user {$this->recipient->id}: " . $exception->getMessage());
    }
}
