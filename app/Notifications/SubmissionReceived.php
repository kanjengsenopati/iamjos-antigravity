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
        // Get the author list nicely formatted
        $authorList = $this->submission->authors->map(function ($author) {
            return $author->first_name . ' ' . $author->last_name;
        })->implode(', ');

        return (new MailMessage)
            ->subject('[' . ($this->submission->journal->abbreviation ?? 'JOURNAL') . '] Submission Acknowledgement')
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Thank you for submitting the manuscript, "' . $this->submission->title . '" to ' . $this->submission->journal->name . '.')
            ->line('With the online journal management system that we are using, you will be able to track its progress through the editorial process by logging in to the journal web site:')
            ->line('**Submission Details:**')
            ->line('- **Title:** ' . $this->submission->title)
            ->line('- **Authors:** ' . ($authorList ?: $notifiable->name))
            ->line('- **Submission ID:** ' . ($this->submission->seq_id ?? 'Pending'))
            ->line('- **Submitted:** ' . $this->submission->submitted_at?->format('F j, Y'))
            ->action('Track Submission Progress', route('journal.submissions.workflow', ['journal' => $this->submission->journal->slug, 'submission' => $this->submission]))
            ->line('If you have any questions, please contact me. Thank you for considering this journal as a venue for your work.')
            ->salutation('Best regards,' . "\n" . 'Editorial Team' . "\n" . $this->submission->journal->name);
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
            'message' => "Your submission \"{$this->submission->title}\" has been received and is under review.",
            'url' => route('journal.submissions.workflow', ['journal' => $journal->slug, 'submission' => $this->submission], false),
            'notification_type' => 'success',
            'icon' => 'fa-check-circle',
            'submission_id' => $this->submission->id,
            'submission_title' => $this->submission->title,
        ];
    }
}
