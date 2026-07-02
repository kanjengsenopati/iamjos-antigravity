<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkflowEventNotification extends Notification
{
    use Queueable;

    protected Submission $submission;
    protected string $subject;
    protected string $messageBody;
    protected string $actionUrl;
    protected string $actionText;
    protected array $dbData;

    /**
     * Create a new notification instance.
     *
     * @param Submission $submission
     * @param string $subject
     * @param string $messageBody
     * @param string $actionUrl
     * @param string $actionText
     * @param array $dbData
     */
    public function __construct(
        Submission $submission,
        string $subject,
        string $messageBody,
        string $actionUrl,
        string $actionText = 'View Submission',
        array $dbData = []
    ) {
        $this->submission = $submission;
        $this->subject = $subject;
        $this->messageBody = $messageBody;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
        $this->dbData = $dbData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
        $bodyLines = explode("\n", $this->messageBody);

        $principalName = $journal ? ($journal->getSetting('contact.principal.name') ?? $journal->name) : 'Journal';
        $principalEmail = $journal ? $journal->getSetting('contact.principal.email') : null;

        $mailMessage = (new MailMessage)
            ->subject('[' . ($journal?->abbreviation ?? 'JOURNAL') . '] New notification from ' . ($journal?->name ?? 'Journal'))
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('You have a new notification from ' . ($journal?->name ?? 'Journal') . ':');

        foreach ($bodyLines as $line) {
            if (trim($line) !== '') {
                $mailMessage->line($line);
            }
        }

        $mailMessage->line('- **Username:** ' . ($notifiable->username ?? 'N/A'))
            ->action($this->actionText, $this->actionUrl)
            ->line('Link: ' . $this->actionUrl)
            ->salutation("Best regards,\nEditorial Team\n________________________________\n" . ($journal?->name ?? 'IAMJOS'));

        $systemEmail = config('mail.from.address');
        $fromName = $principalName . ' via ' . ($journal?->name ?? 'Journal');
        $mailMessage->from($systemEmail, $fromName);

        if ($principalEmail) {
            $mailMessage->replyTo($principalEmail, $principalName);
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification for database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $journal = $this->submission->journal;

        return array_merge([
            'type' => 'workflow_event',
            'title' => $this->subject,
            'message' => strip_tags(str_replace('<br>', "\n", $this->messageBody)),
            'url' => str_replace(url('/'), '', $this->actionUrl),
            'notification_type' => 'info',
            'icon' => 'fa-bell',
            'submission_id' => $this->submission->id,
            'submission_title' => $this->submission->title,
            'journal_id' => $journal?->id,
            'journal_slug' => $journal?->slug,
        ], $this->dbData);
    }
}
