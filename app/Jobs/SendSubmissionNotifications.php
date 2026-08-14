<?php

namespace App\Jobs;

use App\Models\Role;
use App\Models\User;
use App\Models\Submission;
use App\Services\WaGateway;
use App\Services\JournalEmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SubmissionReceived;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewSubmissionNotification;

class SendSubmissionNotifications
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $submission;
    public $author;

    /**
     * Create a new job instance.
     *
     * @param Submission $submission
     * @param User $author
     */
    public function __construct(Submission $submission, User $author)
    {
        $this->submission = $submission;
        $this->author = $author;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $journal = $this->submission->journal;
            if (!$journal) {
                Log::warning("SendSubmissionNotifications: Journal not found for submission {$this->submission->id}");
                return;
            }

            $sentEmails = [];

            // =========================================================
            // 1. EMAIL NOTIFICATION: SUBMITTING AUTHOR & CO-AUTHORS
            // =========================================================
            $submitterEmail = strtolower(trim($this->author->email));

            // Notify Submitting Author (Submission Acknowledgement)
            try {
                $this->author->notify(new SubmissionReceived($this->submission));
                $sentEmails[] = $submitterEmail;
                Log::info("Submission notification email sent to submitting author: {$submitterEmail}");
            } catch (\Exception $e) {
                Log::error("Failed to send submission email to author ({$submitterEmail}): " . $e->getMessage());
            }

            // Notify Co-authors if any
            if ($this->submission->authors()->exists()) {
                foreach ($this->submission->authors as $subAuthor) {
                    $coAuthorEmail = strtolower(trim($subAuthor->email ?? ''));
                    if (empty($coAuthorEmail) || in_array($coAuthorEmail, $sentEmails)) {
                        continue;
                    }

                    try {
                        $coAuthorUser = $subAuthor->user ?? User::where('email', $coAuthorEmail)->first();
                        if ($coAuthorUser) {
                            $coAuthorUser->notify(new SubmissionReceived($this->submission));
                        } else {
                            // On-demand notification or Template fallback
                            Notification::route('mail', $coAuthorEmail)
                                ->notify(new SubmissionReceived($this->submission));
                        }
                        $sentEmails[] = $coAuthorEmail;
                        Log::info("Submission notification email sent to co-author: {$coAuthorEmail}");
                    } catch (\Exception $e) {
                        Log::error("Failed to send submission email to co-author ({$coAuthorEmail}): " . $e->getMessage());
                    }
                }
            }

            // =========================================================
            // 2. EMAIL NOTIFICATION: JOURNAL MANAGERS & EDITORS
            // =========================================================
            // Find all Editors and Managers in this journal
            $editorsAndManagers = User::whereHas('journalRoles', function ($q) use ($journal) {
                $q->where('journal_id', $journal->id)
                  ->whereHas('role', function ($rq) {
                      $rq->whereIn('permission_level', [
                          Role::LEVEL_SUPER_ADMIN,
                          Role::LEVEL_MANAGER,
                          Role::LEVEL_ADMIN,
                          Role::LEVEL_EDITOR,
                          Role::LEVEL_SECTION_EDITOR,
                      ]);
                  });
            })->get();

            // Fallback: If no journal-specific roles, notify Super Admins
            if ($editorsAndManagers->isEmpty()) {
                $editorsAndManagers = User::whereHas('roles', function ($q) {
                    $q->where('name', Role::ROLE_SUPERADMIN);
                })->get();
            }

            foreach ($editorsAndManagers as $editor) {
                $editorEmail = strtolower(trim($editor->email));
                if (in_array($editorEmail, $sentEmails) || $editor->id === $this->author->id) {
                    continue;
                }

                try {
                    $editor->notify(new NewSubmissionNotification($this->submission));
                    $sentEmails[] = $editorEmail;
                    Log::info("New submission email sent to editor/manager: {$editorEmail}");
                } catch (\Exception $e) {
                    Log::error("Failed to send new submission email to editor ({$editorEmail}): " . $e->getMessage());
                }
            }

            // Fallback: Notify Journal Principal Contact if not in sent list
            $principalEmail = $journal->getSetting('contact.principal.email');
            if ($principalEmail) {
                $principalEmailClean = strtolower(trim($principalEmail));
                if (!in_array($principalEmailClean, $sentEmails) && $principalEmailClean !== $submitterEmail) {
                    try {
                        $principalUser = User::where('email', $principalEmailClean)->first();
                        if ($principalUser) {
                            $principalUser->notify(new NewSubmissionNotification($this->submission));
                        } else {
                            Notification::route('mail', $principalEmailClean)
                                ->notify(new NewSubmissionNotification($this->submission));
                        }
                        $sentEmails[] = $principalEmailClean;
                        Log::info("New submission email sent to journal principal contact: {$principalEmailClean}");
                    } catch (\Exception $e) {
                        Log::error("Failed to notify principal contact ({$principalEmailClean}): " . $e->getMessage());
                    }
                }
            }

            // =========================================================
            // 3. WHATSAPP NOTIFICATIONS
            // =========================================================
            // 3.1 Send WhatsApp to all registered authors
            $authorUsers = collect([$this->author]);
            if ($this->submission->authors()->exists()) {
                foreach ($this->submission->authors as $subAuthor) {
                    $u = $subAuthor->user;
                    if (!$u && $subAuthor->email) {
                        $u = User::where('email', strtolower(trim($subAuthor->email)))->first();
                    }
                    if ($u) {
                        $authorUsers->push($u);
                    }
                }
            }

            foreach ($authorUsers->unique('id') as $authorUser) {
                try {
                    WaGateway::sendTemplate($authorUser, 'submission_received', [
                        'name'  => $authorUser->name,
                        'title' => $this->submission->title,
                    ], $journal->id);
                } catch (\Exception $e) {
                    Log::warning("Failed to send WhatsApp to author ({$authorUser->email}): " . $e->getMessage());
                }
            }

            // 3.2 Send WhatsApp to Journal Managers and Editors
            foreach ($editorsAndManagers as $editor) {
                if ($editor->id === $this->author->id) {
                    continue;
                }
                try {
                    WaGateway::sendTemplate($editor, 'new_submission_notification', [
                        'name' => $editor->name,
                        'title' => $this->submission->title,
                        'author' => $this->author->name,
                    ], $journal->id);
                } catch (\Exception $e) {
                    Log::warning("Failed to send WhatsApp to editor ({$editor->email}): " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            Log::error('SendSubmissionNotifications job failed', [
                'submission_id' => $this->submission->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
