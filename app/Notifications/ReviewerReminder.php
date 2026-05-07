<?php

namespace App\Notifications;

use App\Models\ReviewAssignment;
use App\Services\WaGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewerReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected ReviewAssignment $review;
    protected string $type; // 'upcoming' or 'overdue'

    /**
     * Create a new notification instance.
     */
    public function __construct(ReviewAssignment $review, string $type = 'upcoming')
    {
        $this->review = $review;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];
        
        // Add WhatsApp if configured and enabled
        if (!empty($notifiable->phone)) {
             $channels[] = \App\Notifications\Channels\WhatsappChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $submission = $this->review->submission;
        $dueDate = $this->review->due_date?->format('F j, Y') ?? 'Not specified';
        
        $subject = ($this->type === 'overdue' ? 'URGENT: ' : '') . 'Review Reminder - ' . $submission->title;
        
        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Dear ' . $notifiable->name . ',');

        if ($this->type === 'overdue') {
            $message->line('This is a reminder that your review for the manuscript "' . $submission->title . '" was due on ' . $dueDate . '.')
                   ->line('Please submit your review as soon as possible.');
        } else {
            $message->line('This is a friendly reminder that your review for the manuscript "' . $submission->title . '" is due soon on ' . $dueDate . '.');
        }

        return $message
            ->action('View Submission', route('journal.reviewer.show', ['journal' => $submission->journal->slug, 'identifier' => $this->review->slug]))
            ->line('Thank you for your contribution to the peer review process.')
            ->salutation('Best regards, Editorial Team');
    }

    /**
     * Send WhatsApp notification.
     */
    public function toWhatsapp(object $notifiable): void
    {
        $submission = $this->review->submission;
        $dueDate = $this->review->due_date?->format('d M Y') ?? '-';
        
        $template = $this->type === 'overdue' ? 'reviewer_reminder_overdue' : 'reviewer_reminder_upcoming';
        
        WaGateway::sendTemplate($notifiable, $template, [
            'name' => $notifiable->name,
            'title' => $submission->title,
            'due_date' => $dueDate,
        ], $submission->journal_id);
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toArray(object $notifiable): array
    {
        $submission = $this->review->submission;
        $journal = $submission->journal;

        return [
            'type' => 'reviewer_reminder',
            'reminder_type' => $this->type,
            'title' => $this->type === 'overdue' ? 'Overdue Review Reminder' : 'Upcoming Review Reminder',
            'message' => "Reminder: Review for \"{$submission->title}\" is " . ($this->type === 'overdue' ? 'overdue' : 'due soon') . ".",
            'url' => route('journal.reviewer.show', ['journal' => $journal->slug, 'identifier' => $this->review->slug], false),
            'notification_type' => $this->type === 'overdue' ? 'warning' : 'info',
            'icon' => 'fa-clock',
            'review_id' => $this->review->id,
            'submission_id' => $submission->id,
            'journal_id' => $journal->id,
            'due_date' => $this->review->due_date?->format('Y-m-d'),
        ];
    }
}
