<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionReceived extends Notification
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

    public function via(object $notifiable): array
    {
        $isAnonymous = $notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable;
        $journal = $this->submission->journal;
        if ($journal) {
            $disabled = \App\Models\EmailTemplate::where('journal_id', $journal->id)
                ->where('key', 'SUBMISSION_ACK')
                ->where('is_enabled', false)
                ->exists();
            if ($disabled) {
                return $isAnonymous ? [] : ['database'];
            }
        }

        return $isAnonymous ? ['mail'] : ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $journal = $this->submission->journal;
        $authorList = $this->submission->authors->map(function ($author) {
            return $author->first_name . ' ' . $author->last_name;
        })->implode(', ');

        $url = route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $this->submission->url_slug]);

        $principalName = $journal->getSetting('contact.principal.name') ?? $journal->name;
        $principalEmail = $journal->getSetting('contact.principal.email');

        $recipientName = $notifiable->name ?? ($this->submission->authors->first()->name ?? 'Author');

        $mailMessage = (new MailMessage)
            ->subject('[' . ($journal->abbreviation ?? 'JOURNAL') . '] New notification from ' . $journal->name)
            ->greeting('Dear ' . $recipientName . ',')
            ->line('You have a new notification from ' . $journal->name . ':')
            ->line('Thank you for submitting the manuscript, "' . $this->submission->title . '".')
            ->line('**Submission Details:**')
            ->line('- **Title:** ' . $this->submission->title)
            ->line('- **Authors:** ' . ($authorList ?: $notifiable->name))
            ->line('- **Submission ID:** ' . ($this->submission->seq_id ?? 'Pending'))
            ->line('- **Submitted:** ' . ($this->submission->submitted_at?->format('F j, Y') ?? date('F j, Y')))
            ->line('- **Username:** ' . ($notifiable->username ?? 'N/A'))
            ->action('View Submission', $url)
            ->line('Link: ' . $url)
            ->salutation("Best regards,\nEditorial Team\n________________________________\n" . $journal->name);

        $systemEmail = config('mail.from.address');
        $fromName = $principalName . ' via ' . $journal->name;
        $mailMessage->from($systemEmail, $fromName);

        if ($principalEmail) {
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
            'type' => 'submission_received',
            'title' => 'Submission Received',
            'message' => "Your submission \"{$this->submission->title}\" has been received.",
            'url' => route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $this->submission->url_slug], false),
            'notification_type' => 'success',
            'icon' => 'fa-check-circle',
            'submission_id' => $this->submission->id,
            'submission_title' => $this->submission->title,
        ];
    }
}
