<?php

namespace App\Notifications;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent to Editors when they are assigned to handle a submission.
 * Triggered when Manager/Journal Manager assigns an Editor to a submission.
 */
class EditorAssignmentNotification extends Notification
{
    use Queueable;

    protected Submission $submission;
    protected User $assignedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Submission $submission, User $assignedBy)
    {
        $this->submission = $submission;
        $this->assignedBy = $assignedBy;
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
        $url = route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $this->submission->slug]);
        $assignedBy = $this->assignedBy;

        $mailMessage = (new MailMessage)
            ->subject('[' . ($journal->abbreviation ?? 'JOURNAL') . '] New notification from ' . $journal->name)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('You have a new notification from ' . $journal->name . ':')
            ->line('You have been assigned as editor for the submission "' . $this->submission->title . '" by ' . $assignedBy->name . '.')
            ->line('**Submission Details:**')
            ->line('- **Title:** ' . $this->submission->title)
            ->line('- **Author:** ' . ($this->submission->authors->first()->name ?? 'Unknown'))
            ->line('- **Section:** ' . ($this->submission->section->title ?? $this->submission->section->name ?? 'Not specified'))
            ->line('- **Username:** ' . ($notifiable->username ?? 'N/A'))
            ->action('View Submission', $url)
            ->line('Link: ' . $url)
            ->salutation("Best regards,\nEditorial Team\n________________________________\n" . $journal->name);

        $systemEmail = config('mail.from.address');
        $fromName = ($assignedBy ? $assignedBy->name : 'Editor') . ' via ' . $journal->name;
        $mailMessage->from($systemEmail, $fromName);

        if ($assignedBy && $assignedBy->email) {
            $mailMessage->replyTo($assignedBy->email, $assignedBy->name);
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
            'type' => 'editor_assignment',
            'title' => 'Editor Assignment',
            'message' => "You have been assigned as editor for \"{$this->submission->title}\".",
            'url' => route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $this->submission->slug], false),
            'notification_type' => 'info',
            'icon' => 'fa-user-tie',
            'submission_id' => $this->submission->id,
            'journal_id' => $journal->id,
            'journal_slug' => $journal->slug,
            'assigned_by_id' => $this->assignedBy->id,
            'assigned_by_name' => $this->assignedBy->name,
        ];
    }
}
