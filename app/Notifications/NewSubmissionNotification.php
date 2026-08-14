<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent to Journal Managers and Editors when a new submission is created.
 */
class NewSubmissionNotification extends Notification
{
    use Queueable;

    protected Submission $submission;

    /**
     * Create a new notification instance.
     */
    public function __construct(Submission $submission)
    {
        $this->submission = $submission;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $isAnonymous = $notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable;
        return $isAnonymous ? ['mail'] : ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $journal = $this->submission->journal;
        $url = route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $this->submission->url_slug]);

        $submitter = $this->submission->author;
        $submitterName = $submitter ? $submitter->name : ($this->submission->authors->first()->name ?? 'Author');
        $submitterEmail = $submitter ? $submitter->email : ($this->submission->authors->first()->email ?? null);

        $principalName = $journal->getSetting('contact.principal.name') ?? $journal->name;
        $principalEmail = $journal->getSetting('contact.principal.email');

        $recipientName = $notifiable->name ?? 'Editor';

        $mailMessage = (new MailMessage)
            ->subject('[' . ($journal->abbreviation ?? 'JOURNAL') . '] New notification from ' . $journal->name)
            ->greeting('Dear ' . $recipientName . ',')
            ->line('You have a new notification from ' . $journal->name . ':')
            ->line('A new submission titled "' . $this->submission->title . '" has been submitted by ' . $submitterName . '.')
            ->line('**Submission Details:**')
            ->line('- **Title:** ' . $this->submission->title)
            ->line('- **Section:** ' . ($this->submission->section->title ?? $this->submission->section->name ?? 'Not specified'))
            ->line('- **Submitted:** ' . ($this->submission->submitted_at?->format('F j, Y') ?? date('F j, Y')))
            ->line('- **Username:** ' . ($notifiable->username ?? 'N/A'))
            ->action('View Submission', $url)
            ->line('Link: ' . $url)
            ->salutation("Best regards,\nEditorial Team\n________________________________\n" . $journal->name);

        $systemEmail = config('mail.from.address');
        $fromName = $submitterName . ' via ' . $journal->name;
        $mailMessage->from($systemEmail, $fromName);

        if ($submitterEmail) {
            $mailMessage->replyTo($submitterEmail, $submitterName);
        } elseif ($principalEmail) {
            $mailMessage->replyTo($principalEmail, $principalName);
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toArray(object $notifiable): array
    {
        $journal = $this->submission->journal;

        return [
            'type' => 'new_submission',
            'title' => 'New Submission Received',
            'message' => "A new submission has been submitted: \"{$this->submission->title}\".",
            'url' => route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $this->submission->url_slug], false),
            'notification_type' => 'info',
            'icon' => 'fa-file-circle-plus',
            'submission_id' => $this->submission->id,
            'submission_title' => $this->submission->title,
            'journal_id' => $journal->id,
            'journal_slug' => $journal->slug,
            'author' => $this->submission->authors->first()->name ?? 'Unknown',
        ];
    }
}
