<?php

namespace App\Notifications;

use App\Models\Issue;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArticlePublished extends Notification
{
    use Queueable;

    protected Submission $submission;
    protected Issue $issue;

    /**
     * Create a new notification instance.
     */
    public function __construct(Submission $submission, Issue $issue)
    {
        $this->submission = $submission;
        $this->issue = $issue;
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
        $url = route('journal.public.article', ['journal' => $journal->slug, 'submission' => $this->submission]);

        $principalName = $journal->getSetting('contact.principal.name') ?? $journal->name;
        $principalEmail = $journal->getSetting('contact.principal.email');

        $mailMessage = (new MailMessage)
            ->subject('[' . ($journal->abbreviation ?? 'JOURNAL') . '] New notification from ' . $journal->name)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('You have a new notification from ' . $journal->name . ':')
            ->line('Congratulations! Your article "' . $this->submission->title . '" has been published in ' . $this->issue->identifier . '.')
            ->line('**Article Details:**')
            ->line('- **Title:** ' . $this->submission->title)
            ->line('- **Issue:** ' . $this->issue->identifier)
            ->line('- **Published:** ' . ($this->submission->published_at?->format('F j, Y') ?? date('F j, Y')))
            ->line('- **Username:** ' . ($notifiable->username ?? 'N/A'))
            ->action('View Published Article', $url)
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
        return [
            'type' => 'article_published',
            'title' => 'Article Published',
            'submission_id' => $this->submission->id,
            'issue_id' => $this->issue->id,
            'title' => $this->submission->title,
            'issue' => $this->issue->identifier,
            'message' => 'Your article "' . $this->submission->title . '" has been published!',
            'url' => route('journal.public.article', ['journal' => $this->submission->journal->slug, 'submission' => $this->submission], false),
            'notification_type' => 'success',
            'icon' => 'fa-check-circle',
        ];
    }
}
