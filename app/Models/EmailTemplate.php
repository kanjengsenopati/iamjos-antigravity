<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'journal_id',
        'key',
        'name',
        'subject',
        'body',
        'description',
        'from_name',
        'from_email',
        'is_enabled',
        'is_custom',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_custom' => 'boolean',
        'can_edit' => 'boolean',
        'can_disable' => 'boolean',
        'is_active' => 'boolean',
        'variables' => 'array',
    ];

    protected $appends = [
        'sent_from',
        'sent_to',
        'stage',
    ];

    public function getSentFromAttribute(): string
    {
        return match ($this->key) {
            'REVIEW_CONFIRM', 'REVIEW_DECLINE', 'REVIEW_COMPLETE' => 'Reviewer',
            default => 'Editor',
        };
    }

    public function getSentToAttribute(): string
    {
        return match ($this->key) {
            'SUBMISSION_ACK', 'SUBMISSION_ACK_NOT_USER', 'SUBMISSION_UNDER_REVIEW',
            'EDITOR_DECISION_ACCEPT', 'EDITOR_DECISION_REVISIONS', 'EDITOR_DECISION_DECLINE',
            'PUBLISH_NOTIFY' => 'Author',

            'NOTIFICATION', 'EDITOR_ASSIGN',
            'REVIEW_CONFIRM', 'REVIEW_DECLINE', 'REVIEW_COMPLETE' => 'Editor',

            'REVIEW_REQUEST', 'REVIEW_REQUEST_SUBSEQUENT', 'REVIEW_REMIND', 'REVIEW_ACK' => 'Reviewer',

            'COPYEDIT_REQUEST', 'LAYOUT_REQUEST' => 'Assistant',

            default => 'Author',
        };
    }

    public function getStageAttribute(): string
    {
        return match ($this->key) {
            'SUBMISSION_ACK', 'SUBMISSION_ACK_NOT_USER', 'NOTIFICATION', 'EDITOR_ASSIGN' => 'Submission',
            'SUBMISSION_UNDER_REVIEW', 'REVIEW_REQUEST', 'REVIEW_REQUEST_SUBSEQUENT',
            'REVIEW_CONFIRM', 'REVIEW_DECLINE', 'REVIEW_REMIND', 'REVIEW_COMPLETE',
            'REVIEW_ACK', 'EDITOR_DECISION_ACCEPT', 'EDITOR_DECISION_REVISIONS',
            'EDITOR_DECISION_DECLINE' => 'Review',
            'COPYEDIT_REQUEST' => 'Copyediting',
            'LAYOUT_REQUEST', 'PUBLISH_NOTIFY' => 'Production',
            default => 'Other',
        };
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Accessor to ensure body is always returned in HTML format with proper paragraph tags.
     */
    public function getBodyAttribute(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return self::formatPlainToHtml($value);
    }

    /**
     * Convert plain text with newlines into clean HTML paragraphs.
     */
    public static function formatPlainToHtml(string $text): string
    {
        $hasBlockTags = preg_match('/<(p|div|table|ul|ol|h[1-6]|blockquote)\b[^>]*>/i', $text);
        if ($hasBlockTags) {
            return $text;
        }

        // Normalize line endings
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $paragraphs = array_filter(array_map('trim', explode("\n\n", $text)), function ($p) {
            return $p !== '';
        });

        if (empty($paragraphs)) {
            return '';
        }

        $html = '';
        foreach ($paragraphs as $p) {
            $formatted = nl2br($p);
            $html .= "<p>{$formatted}</p>\n";
        }

        return trim($html);
    }

    // =====================================================
    // STATIC: Default Templates
    // =====================================================

    /**
     * Get default OJS email templates (HTML formatted)
     */
    public static function getDefaultTemplates(): array
    {
        return [
            [
                'key' => 'SUBMISSION_ACK',
                'name' => 'Submission Acknowledgement',
                'subject' => 'Submission Acknowledgement',
                'body' => "<p>Dear {\$authorName},</p>\n<p>Thank you for submitting the manuscript, &quot;{\$submissionTitle}&quot; to {\$journalName}. With the online journal management system that we are using, you will be able to track its progress through the editorial process by logging in to the journal web site:</p>\n<p>Submission URL: <a href=\"{\$submissionUrl}\">{\$submissionUrl}</a></p>\n<p>If you have any questions, please contact me. Thank you for considering this journal as a venue for your work.</p>\n<p>{\$signature}</p>",
                'description' => 'Sent to the author when a new submission is received.',
            ],
            [
                'key' => 'SUBMISSION_ACK_NOT_USER',
                'name' => 'Submission Acknowledgement (Co-Author)',
                'subject' => 'Submission Acknowledgement',
                'body' => "<p>Dear {\$recipientName},</p>\n<p>You have been named as a co-author on a manuscript submission to {\$journalName}.</p>\n<p>The submitting author, {\$authorName}, has provided the following message:</p>\n<p>Title: {\$submissionTitle}</p>\n<p>If you have any questions, please contact the submitting author.</p>\n<p>{\$signature}</p>",
                'description' => 'Sent to co-authors when a new submission is received.',
            ],
            [
                'key' => 'SUBMISSION_UNDER_REVIEW',
                'name' => 'Submission Sent to Review',
                'subject' => 'Update on Your Submission: {$submissionTitle}',
                'body' => "<p>Dear {\$authorName},</p>\n<p>We are pleased to inform you that your manuscript, &quot;{\$submissionTitle},&quot; has passed the initial desk review and has been sent to our reviewers for the peer review process.</p>\n<p>You can track the progress of your submission by logging into the journal website:</p>\n<p>Submission URL: <a href=\"{\$submissionUrl}\">{\$submissionUrl}</a></p>\n<p>We will notify you once the reviewers have submitted their feedback and an editorial decision has been made.</p>\n<p>Thank you for considering {\$journalName} as a venue for your work.</p>\n<p>{\$signature}</p>",
                'description' => 'Sent to the author when their submission is promoted to the Review stage.',
            ],
            [
                'key' => 'NOTIFICATION',
                'name' => 'New Submission Notification (Manager / Editor)',
                'subject' => 'New Submission Received: {$submissionTitle}',
                'body' => "<p>Dear {\$recipientName},</p>\n<p>A new submission titled &quot;{\$submissionTitle}&quot; has been submitted to {\$journalName} by {\$submitterName}.</p>\n<p>Submission Details:<br />\n- Title: {\$submissionTitle}<br />\n- Section: {\$sectionName}<br />\n- Submitted: {\$submittedDate}</p>\n<p>Submission URL: <a href=\"{\$submissionUrl}\">{\$submissionUrl}</a></p>\n<p>{\$signature}</p>",
                'description' => 'Sent to journal managers and editors when a new submission is received.',
            ],
            [
                'key' => 'EDITOR_ASSIGN',
                'name' => 'Editor Assignment',
                'subject' => 'Editor Assignment: {$submissionTitle}',
                'body' => "<p>Dear {\$editorName},</p>\n<p>You have been assigned as an editor to oversee the submission, &quot;{\$submissionTitle},&quot; for {\$journalName}.</p>\n<p>Submission Details:<br />\n- Title: {\$submissionTitle}<br />\n- Section: {\$sectionName}<br />\n- Assigned By: {\$assignedByName}</p>\n<p>Submission URL: <a href=\"{\$submissionUrl}\">{\$submissionUrl}</a></p>\n<p>Please log in to the journal system to begin the editorial process.</p>\n<p>{\$signature}</p>",
                'description' => 'Sent to an editor when they are assigned to handle a submission.',
            ],
            [
                'key' => 'REVIEW_REQUEST',
                'name' => 'Review Request',
                'subject' => 'Article Review Request',
                'body' => "<p>Dear {\$reviewerName},</p>\n<p>I believe that you would serve as an excellent reviewer of the manuscript, &quot;{\$submissionTitle},&quot; which has been submitted to {\$journalName}.</p>\n<p>Please log into the journal website to indicate whether you will undertake the review or not, as well as to access the submission and guidelines.</p>\n<p>Review URL: <a href=\"{\$reviewUrl}\">{\$reviewUrl}</a></p>\n<p>The review is due {\$reviewDueDate}.</p>\n<p>Thank you for considering this request.</p>\n<p>{\$signature}</p>",
                'description' => 'Sent to a reviewer when they are assigned to review a submission.',
            ],
            [
                'key' => 'REVIEW_REQUEST_SUBSEQUENT',
                'name' => 'Review Request (Resubmission)',
                'subject' => 'Article Review Request (Revised)',
                'body' => "<p>Dear {\$reviewerName},</p>\n<p>This regards the manuscript &quot;{\$submissionTitle},&quot; which has been resubmitted to {\$journalName}.</p>\n<p>As you reviewed the original submission, we would appreciate if you could review this revised version as well.</p>\n<p>Review URL: <a href=\"{\$reviewUrl}\">{\$reviewUrl}</a></p>\n<p>The review is due {\$reviewDueDate}.</p>\n<p>{\$signature}</p>",
                'description' => 'Sent to a reviewer for resubmitted manuscripts.',
            ],
            [
                'key' => 'REVIEW_CONFIRM',
                'name' => 'Review Confirmed',
                'subject' => 'Review Confirmed',
                'body' => "<p>Dear {\$reviewerName},</p>\n<p>Thank you for agreeing to review the submission, &quot;{\$submissionTitle},&quot; for {\$journalName}.</p>\n<p>Please make sure to complete the review by {\$reviewDueDate}.</p>\n<p>Review URL: <a href=\"{\$reviewUrl}\">{\$reviewUrl}</a></p>\n<p>{\$signature}</p>",
                'description' => 'Sent to a reviewer when they accept a review request.',
            ],
            [
                'key' => 'REVIEW_DECLINE',
                'name' => 'Review Declined',
                'subject' => 'Unable to Review',
                'body' => "<p>Dear {\$editorName},</p>\n<p>I am afraid that I am unable to review the submission, &quot;{\$submissionTitle},&quot; for {\$journalName} at this time.</p>\n<p>Thank you for thinking of me, and please feel free to contact me in the future.</p>\n<p>{\$reviewerName}</p>",
                'description' => 'Sent when a reviewer declines a review request.',
            ],
            [
                'key' => 'REVIEW_REMIND',
                'name' => 'Review Reminder',
                'subject' => 'Reminder: Review Due',
                'body' => "<p>Dear {\$reviewerName},</p>\n<p>This is a reminder that your review for &quot;{\$submissionTitle}&quot; is due on {\$reviewDueDate}.</p>\n<p>Please log in to complete your review at your earliest convenience.</p>\n<p>Review URL: <a href=\"{\$reviewUrl}\">{\$reviewUrl}</a></p>\n<p>{\$signature}</p>",
                'description' => 'Reminder sent to a reviewer for pending reviews.',
            ],
            [
                'key' => 'REVIEW_COMPLETE',
                'name' => 'Review Completed',
                'subject' => 'Review Completed',
                'body' => "<p>Dear {\$editorName},</p>\n<p>{\$reviewerName} has completed the review of &quot;{\$submissionTitle}&quot; for {\$journalName}.</p>\n<p>Please log in to view the review comments and make an editorial decision.</p>\n<p>Submission URL: <a href=\"{\$submissionUrl}\">{\$submissionUrl}</a></p>\n<p>{\$signature}</p>",
                'description' => 'Sent to editor when a reviewer completes their review.',
            ],
            [
                'key' => 'REVIEW_ACK',
                'name' => 'Review Acknowledgement',
                'subject' => 'Article Review Acknowledgement',
                'body' => "<p>Dear {\$reviewerName},</p>\n<p>Thank you for completing the review of the submission, &quot;{\$submissionTitle},&quot; for {\$journalName}. We appreciate your contribution to the quality of the work that we publish.</p>\n<p>{\$signature}</p>",
                'description' => 'Sent to a reviewer to thank them for completing a review.',
            ],
            [
                'key' => 'EDITOR_DECISION_ACCEPT',
                'name' => 'Editorial Decision: Accept',
                'subject' => 'Editor Decision: Accept',
                'body' => "<p>Dear {\$authorName},</p>\n<p>We have reached a decision regarding your submission to {\$journalName}, &quot;{\$submissionTitle}&quot;.</p>\n<p>Our decision is to: Accept Submission</p>\n<p>{\$editorComments}</p>\n<p>{\$signature}</p>",
                'description' => 'Sent to the author when their submission is accepted.',
            ],
            [
                'key' => 'EDITOR_DECISION_REVISIONS',
                'name' => 'Editorial Decision: Revisions Required',
                'subject' => 'Editor Decision: Revisions Required',
                'body' => "<p>Dear {\$authorName},</p>\n<p>We have reached a decision regarding your submission to {\$journalName}, &quot;{\$submissionTitle}&quot;.</p>\n<p>Our decision is to: Request Revisions</p>\n<p>Please address the following concerns and resubmit your revised manuscript:</p>\n<p>{\$editorComments}</p>\n<p>{\$signature}</p>",
                'description' => 'Sent to the author when revisions are required.',
            ],
            [
                'key' => 'EDITOR_DECISION_DECLINE',
                'name' => 'Editorial Decision: Decline',
                'subject' => 'Editor Decision: Decline',
                'body' => "<p>Dear {\$authorName},</p>\n<p>We have reached a decision regarding your submission to {\$journalName}, &quot;{\$submissionTitle}&quot;.</p>\n<p>Our decision is to: Decline Submission</p>\n<p>{\$editorComments}</p>\n<p>Thank you for considering {\$journalName} as a venue for your work.</p>\n<p>{\$signature}</p>",
                'description' => 'Sent to the author when their submission is declined.',
            ],
            [
                'key' => 'COPYEDIT_REQUEST',
                'name' => 'Copyediting Request',
                'subject' => 'Copyediting Assignment',
                'body' => "<p>Dear {\$copyeditorName},</p>\n<p>You have been assigned to copyedit the submission &quot;{\$submissionTitle}&quot; for {\$journalName}.</p>\n<p>Please log in to access the submission and begin copyediting.</p>\n<p>Submission URL: <a href=\"{\$submissionUrl}\">{\$submissionUrl}</a></p>\n<p>{\$signature}</p>",
                'description' => 'Sent to a copyeditor when they are assigned.',
            ],
            [
                'key' => 'LAYOUT_REQUEST',
                'name' => 'Layout Request',
                'subject' => 'Layout Assignment',
                'body' => "<p>Dear {\$layoutEditorName},</p>\n<p>You have been assigned to create galleys for the submission &quot;{\$submissionTitle}&quot; for {\$journalName}.</p>\n<p>Please log in to access the submission files.</p>\n<p>Submission URL: <a href=\"{\$submissionUrl}\">{\$submissionUrl}</a></p>\n<p>{\$signature}</p>",
                'description' => 'Sent to a layout editor when they are assigned.',
            ],
            [
                'key' => 'PUBLISH_NOTIFY',
                'name' => 'Publication Notification',
                'subject' => 'Your Article Has Been Published',
                'body' => "<p>Dear {\$authorName},</p>\n<p>We are pleased to inform you that your article &quot;{\$submissionTitle}&quot; has been published in {\$journalName}, {\$issueTitle}.</p>\n<p>You can view your published article at:<br />\n<a href=\"{\$articleUrl}\">{\$articleUrl}</a></p>\n<p>Thank you for your contribution.</p>\n<p>{\$signature}</p>",
                'description' => 'Sent to authors when their article is published.',
            ],
        ];
    }

    /**
     * Seed default templates for a journal
     */
    public static function seedForJournal(string $journalId): void
    {
        foreach (self::getDefaultTemplates() as $template) {
            $existing = self::where('journal_id', $journalId)
                ->where('key', $template['key'])
                ->first();

            if (!$existing) {
                self::create([
                    'journal_id' => $journalId,
                    'key' => $template['key'],
                    'name' => $template['name'],
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'description' => $template['description'],
                    'is_enabled' => true,
                    'is_custom' => false,
                ]);
            } elseif (!$existing->is_custom) {
                // If it's a default template that hasn't been customized, upgrade its body to HTML if needed
                $hasBlockTags = preg_match('/<(p|div|table|ul|ol|h[1-6]|blockquote)\b[^>]*>/i', $existing->getRawOriginal('body') ?? '');
                if (!$hasBlockTags) {
                    $existing->update([
                        'body' => $template['body'],
                    ]);
                }
            }
        }
    }

    /**
     * Reset template to default
     */
    public function resetToDefault(): bool
    {
        $defaults = collect(self::getDefaultTemplates());
        $default = $defaults->firstWhere('key', $this->key);

        if ($default) {
            $this->update([
                'subject' => $default['subject'],
                'body' => $default['body'],
                'is_custom' => false,
            ]);
            return true;
        }

        return false;
    }
}
