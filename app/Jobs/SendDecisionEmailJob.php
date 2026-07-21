<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDecisionEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $submission;
    public $emailBody;
    public $decisionType;
    public $madeBy;

    /**
     * Create a new job instance.
     */
    public function __construct($submission, $emailBody, $decisionType, $madeBy = null)
    {
        $this->submission = $submission;
        $this->emailBody = $emailBody;
        $this->decisionType = $decisionType;
        $this->madeBy = $madeBy;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Basic SMTP configuration check
            if (!config('mail.mailers.smtp.host') || !config('mail.mailers.smtp.username')) {
                Log::warning("SendDecisionEmailJob: SMTP not configured. Skipping email for submission {$this->submission->id}.");
                return;
            }

            $recipient = $this->submission->author;
            if (!$recipient) {
                Log::warning("SendDecisionEmailJob: No author found for submission {$this->submission->id}.");
                return;
            }

            $journal = $this->submission->journal;
            if (!$journal) {
                Log::warning("SendDecisionEmailJob: Journal not found for submission {$this->submission->id}.");
                return;
            }

            // Map decision types to database template keys
            $key = match ($this->decisionType) {
                'accepted' => 'EDITOR_DECISION_ACCEPT',
                'declined' => 'EDITOR_DECISION_DECLINE',
                'revisions' => 'EDITOR_DECISION_REVISIONS',
                'send_to_production' => 'LAYOUT_REQUEST',
                default => null,
            };

            if ($key) {
                $customSubject = match ($this->decisionType) {
                    'accepted' => 'Editor Decision: Submission Accepted',
                    'declined' => 'Editor Decision: Submission Declined',
                    'revisions' => 'Editor Decision: Revisions Required',
                    'send_to_production' => 'Editor Decision: Sent to Production',
                    default => 'Editor Decision Update',
                };

                // Use centralized dynamic email service which checks is_enabled and resolves the template
                $sent = \App\Services\JournalEmailService::sendNotification($journal, $recipient, $key, [
                    'customSubject' => $customSubject,
                    'customBody' => $this->emailBody,
                ]);

                if ($sent) {
                    Log::info("Decision email sent successfully via JournalEmailService: " . $this->decisionType);
                } else {
                    Log::info("Decision email skipped or failed to send via JournalEmailService: " . $this->decisionType);
                }
            } else {
                Log::warning("SendDecisionEmailJob: Unmapped decision type: " . $this->decisionType);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send decision email for submission {$this->submission->id}: " . $e->getMessage());
        }
    }
}
