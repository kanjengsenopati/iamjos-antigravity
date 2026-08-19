<?php

namespace App\Notifications;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent to author when their submission is declined.
 */
class SubmissionDeclinedNotification extends Notification
{
    use Queueable;

    protected Submission $submission;
    protected User $declinedBy;
    protected string $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Submission $submission, User $declinedBy, string $reason)
    {
        $this->submission = $submission;
        $this->declinedBy = $declinedBy;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $journal = $this->submission->journal;
        $url = route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $this->submission->url_slug]);
        $declinedBy = $this->declinedBy;

        $mailMessage = (new MailMessage)
            ->subject('[' . ($journal->abbreviation ?? 'JOURNAL') . '] New notification from ' . $journal->name)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('You have a new notification from ' . $journal->name . ':')
            ->line('We regret to inform you that your submission "' . $this->submission->title . '" has been declined by the editorial team.')
            ->line('**Reason for Declining:**')
            ->line($this->reason)
            ->line('- **Username:** ' . ($notifiable->username ?? 'N/A'))
            ->action('View Submission', $url)
            ->line('Link: ' . $url)
            ->salutation("Best regards,\nEditorial Team\n________________________________\n" . $journal->name);

        $systemEmail = config('mail.from.address');
        $fromName = ($declinedBy ? $declinedBy->name : 'Editor') . ' via ' . $journal->name;
        $mailMessage->from($systemEmail, $fromName);

        if ($declinedBy && $declinedBy->email) {
            $mailMessage->replyTo($declinedBy->email, $declinedBy->name);
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
            'type' => 'submission_declined',
            'submission_id' => $this->submission->id,
            'journal_id' => $journal->id,
            'journal_slug' => $journal->slug,
            'declined_by_id' => $this->declinedBy->id,
            'declined_by_name' => $this->declinedBy->name,
            'title' => $this->submission->title,
            'message' => 'Your submission "' . $this->submission->title . '" has been declined.',
            'url' => route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $this->submission->url_slug], false),
        ];
    }
}

