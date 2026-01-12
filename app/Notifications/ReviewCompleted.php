<?php

namespace App\Notifications;

use App\Models\ReviewAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected ReviewAssignment $review;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReviewAssignment $review)
    {
        $this->review = $review;
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
        $submission = $this->review->submission;
        $recommendation = $this->review->recommendation_label;

        return (new MailMessage)
            ->subject('Review Completed - ' . $submission->title)
            ->greeting('Dear Editor,')
            ->line('A review has been completed for the following submission:')
            ->line('**Title:** ' . $submission->title)
            ->line('**Reviewer:** ' . $this->review->reviewer->name)
            ->line('**Recommendation:** ' . $recommendation)
            ->line('**Round:** ' . $this->review->round)
            ->action('View Review', url('/editorial/queue'))
            ->line('Please log in to view the complete review and make an editorial decision.')
            ->salutation('Best regards, IAMJOS System');
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'review_completed',
            'review_id' => $this->review->id,
            'submission_id' => $this->review->submission_id,
            'title' => $this->review->submission->title,
            'reviewer_name' => $this->review->reviewer->name,
            'recommendation' => $this->review->recommendation,
            'message' => 'Review completed for "' . $this->review->submission->title . '".',
        ];
    }
}
