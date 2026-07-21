<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionDecision extends Notification
{
    use Queueable;

    protected Submission $submission;
    protected string $decision;
    protected ?string $comments;
    protected array $attachments;

    /**
     * Create a new notification instance.
     */
    public function __construct(Submission $submission, string $decision, ?string $comments = null, array $attachments = [])
    {
        $this->submission = $submission;
        $this->decision = $decision;
        $this->comments = $comments;
        $this->attachments = $attachments;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $journal = $this->submission->journal;
        $url = route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $this->submission->url_slug]);

        $principalName = $journal->getSetting('contact.principal.name') ?? $journal->name;
        $principalEmail = $journal->getSetting('contact.principal.email');

        $mail = (new MailMessage)
            ->subject('[' . ($journal->abbreviation ?? 'JOURNAL') . '] New notification from ' . $journal->name)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('You have a new notification from ' . $journal->name . ':');

        foreach ($this->attachments as $file) {
            $filePath = storage_path('app/' . $file['path']);
            if (!file_exists($filePath)) {
                // Fallback jika path absolut
                $filePath = $file['path'];
            }
            if (file_exists($filePath)) {
                $mail->attach($filePath, [
                    'as' => $file['name'] ?? null,
                    'mime' => $file['mime'] ?? null,
                ]);
            }
        }

        switch ($this->decision) {
            case 'accepted':
                $mail->line('We are pleased to inform you that your submission "' . $this->submission->title . '" has been accepted for publication.')
                    ->line('**Title:** ' . $this->submission->title);
                break;

            case 'rejected':
                $mail->line('After careful review, we regret to inform you that your submission "' . $this->submission->title . '" has not been accepted for publication.')
                    ->line('**Title:** ' . $this->submission->title);
                break;

            case 'revision_required':
                $mail->line('Your submission "' . $this->submission->title . '" has been reviewed and requires revisions.')
                    ->line('**Title:** ' . $this->submission->title);
                break;

            default:
                $mail->line('There is an update on your submission "' . $this->submission->title . '".')
                    ->line('**Title:** ' . $this->submission->title)
                    ->line('**Status:** ' . ucfirst($this->decision));
        }

        if ($this->comments) {
            $mail->line('')
                ->line('**Editor\'s Comments:**')
                ->line($this->comments);
        }

        $mail->line('- **Username:** ' . ($notifiable->username ?? 'N/A'))
            ->action('View Submission', $url)
            ->line('Link: ' . $url)
            ->salutation("Best regards,\nEditorial Team\n________________________________\n" . $journal->name);

        $systemEmail = config('mail.from.address');
        $fromName = $principalName . ' via ' . $journal->name;
        $mail->from($systemEmail, $fromName);

        if ($principalEmail) {
            $mail->replyTo($principalEmail, $principalName);
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toArray(object $notifiable): array
    {
        $journal = $this->submission->journal;

        $titles = [
            'accepted' => 'Submission Accepted',
            'rejected' => 'Submission Declined',
            'revision_required' => 'Revision Required',
        ];

        $types = [
            'accepted' => 'success',
            'rejected' => 'danger',
            'revision_required' => 'warning',
        ];

        $icons = [
            'accepted' => 'fa-check-circle',
            'rejected' => 'fa-times-circle',
            'revision_required' => 'fa-edit',
        ];

        $messages = [
            'accepted' => 'Congratulations! Your submission has been accepted.',
            'rejected' => 'Your submission has been declined.',
            'revision_required' => 'Revision required for your submission.',
        ];

        return [
            'type' => 'submission_decision',
            'title' => $titles[$this->decision] ?? 'Submission Update',
            'message' => $messages[$this->decision] ?? 'There is an update on your submission.',
            'url' => route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $this->submission->url_slug], false),
            'notification_type' => $types[$this->decision] ?? 'info',
            'icon' => $icons[$this->decision] ?? 'fa-gavel',
            'submission_id' => $this->submission->id,
            'submission_title' => $this->submission->title,
            'journal_id' => $journal->id,
            'journal_slug' => $journal->slug,
            'decision' => $this->decision,
        ];
    }
}
