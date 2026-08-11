<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\GeneralNotificationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendIssuePublishedEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $recipientEmail,
        public string $recipientName,
        public string $issueTitle,
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
                Log::warning("SendIssuePublishedEmailJob: SMTP not configured. Skipping email for {$this->recipientEmail}.");
                return;
            }

            if (empty($this->recipientEmail) || !filter_var($this->recipientEmail, FILTER_VALIDATE_EMAIL)) {
                Log::warning("SendIssuePublishedEmailJob: Invalid recipient email: {$this->recipientEmail}");
                return;
            }

            $subject = "Issue Published: {$this->issueTitle}";
            $body = "We are pleased to inform you that the issue **{$this->issueTitle}** containing your article has been published in **{$this->journalName}**.";

            // Send using sendNow so it executes inline inside this isolated job execution
            Mail::to($this->recipientEmail)->sendNow(
                new GeneralNotificationMail(
                    $subject,
                    $body,
                    $this->recipientName,
                    $this->journalName
                )
            );

            Log::info("Issue published notification sent successfully to {$this->recipientEmail}");
        } catch (\Throwable $e) {
            Log::error("Failed to send Issue Published email to {$this->recipientEmail}: " . $e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendIssuePublishedEmailJob failed for {$this->recipientEmail}: " . $exception->getMessage());
    }
}
