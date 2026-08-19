<?php

namespace App\Notifications;

use App\Models\Submission;
use App\Models\User;
use App\Models\CopyeditingAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent to Copyeditors when they are assigned to copyedit a submission.
 * Triggered when Manager/Editor assigns a Copyeditor to a submission in the Copyediting stage.
 */
class CopyeditorAssignmentNotification extends Notification
{
    use Queueable;

    protected CopyeditingAssignment $assignment;
    protected Submission $submission;
    protected User $assignedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(CopyeditingAssignment $assignment, User $assignedBy)
    {
        $this->assignment = $assignment;
        $this->submission = $assignment->submission;
        $this->assignedBy = $assignedBy;
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
        $assignedBy = $this->assignedBy;

        $mailMessage = (new MailMessage)
            ->subject('[' . ($journal->abbreviation ?? 'JOURNAL') . '] Copyediting Assignment from ' . $journal->name)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('You have a new notification from ' . $journal->name . ':')
            ->line('You have been assigned as copyeditor for the submission "' . $this->submission->title . '" by ' . $assignedBy->name . '.')
            ->line('**Submission Details:**')
            ->line('- **Title:** ' . $this->submission->title)
            ->line('- **Author:** ' . ($this->submission->authors->first()->name ?? 'Unknown'))
            ->line('- **Section:** ' . ($this->submission->section->title ?? $this->submission->section->name ?? 'Not specified'));

        if ($this->assignment->due_date) {
            $mailMessage->line('- **Due Date:** ' . $this->assignment->due_date->format('F d, Y'));
        }

        $mailMessage->action('View Submission', $url)
            ->line('Link: ' . $url)
            ->line('Please log in to the system to access the submission files and begin copyediting.')
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
            'type' => 'copyeditor_assignment',
            'title' => 'Copyediting Assignment',
            'message' => "You have been assigned as copyeditor for \"{$this->submission->title}\".",
            'url' => route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $this->submission->url_slug], false),
            'notification_type' => 'info',
            'icon' => 'fa-pen-to-square',
            'submission_id' => $this->submission->id,
            'journal_id' => $journal->id,
            'journal_slug' => $journal->slug,
            'assigned_by_id' => $this->assignedBy->id,
            'assigned_by_name' => $this->assignedBy->name,
            'assignment_id' => $this->assignment->id,
            'due_date' => $this->assignment->due_date?->toISOString(),
        ];
    }
}

