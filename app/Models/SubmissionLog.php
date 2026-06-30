<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'submission_id',
        'user_id',
        'event_type',
        'title',
        'description',
        'metadata',
        'stage',
        'email_subject',
        'email_body',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // =====================================================
    // EVENT TYPE CONSTANTS
    // =====================================================

    public const EVENT_SUBMITTED             = 'submission_created';
    public const EVENT_EDITOR_ASSIGNED       = 'editor_assigned';
    public const EVENT_EDITOR_UNASSIGNED     = 'editor_unassigned';
    public const EVENT_REVIEWER_ASSIGNED     = 'reviewer_assigned';
    public const EVENT_REVIEW_SUBMITTED      = 'review_submitted';
    public const EVENT_DECISION_MADE         = 'decision_made';
    public const EVENT_STAGE_CHANGED         = 'stage_changed';
    public const EVENT_DISCUSSION_CREATED    = 'discussion_created';
    public const EVENT_DISCUSSION_MESSAGE    = 'discussion_message_sent';
    public const EVENT_FILE_UPLOADED         = 'file_uploaded';
    public const EVENT_METADATA_UPDATED      = 'metadata_updated';
    public const EVENT_PUBLISHED             = 'published';

    // =====================================================
    // STAGE NAME MAP (integer stage_id → string)
    // =====================================================

    public const STAGE_MAP = [
        1 => 'submission',
        2 => 'review',
        3 => 'copyediting',
        4 => 'production',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The submission files attached to this log entry.
     */
    public function files()
    {
        return $this->belongsToMany(SubmissionFile::class, 'submission_log_files')
                    ->withTimestamps();
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getIconAttribute(): string
    {
        return match ($this->event_type) {
            self::EVENT_SUBMITTED           => 'fa-file-circle-plus',
            self::EVENT_EDITOR_ASSIGNED     => 'fa-user-tie',
            self::EVENT_EDITOR_UNASSIGNED   => 'fa-user-minus',
            self::EVENT_REVIEWER_ASSIGNED   => 'fa-clipboard-check',
            self::EVENT_REVIEW_SUBMITTED    => 'fa-check-circle',
            self::EVENT_DECISION_MADE       => 'fa-gavel',
            self::EVENT_STAGE_CHANGED       => 'fa-arrow-right-arrow-left',
            self::EVENT_DISCUSSION_CREATED  => 'fa-comments',
            self::EVENT_DISCUSSION_MESSAGE  => 'fa-message',
            self::EVENT_FILE_UPLOADED       => 'fa-file-arrow-up',
            self::EVENT_METADATA_UPDATED    => 'fa-pen-to-square',
            self::EVENT_PUBLISHED           => 'fa-globe',
            default                         => 'fa-circle',
        };
    }

    public function getColorAttribute(): string
    {
        return match ($this->event_type) {
            self::EVENT_SUBMITTED           => 'indigo',
            self::EVENT_EDITOR_ASSIGNED     => 'purple',
            self::EVENT_REVIEWER_ASSIGNED   => 'blue',
            self::EVENT_REVIEW_SUBMITTED    => 'emerald',
            self::EVENT_DECISION_MADE       => 'amber',
            self::EVENT_DISCUSSION_CREATED,
            self::EVENT_DISCUSSION_MESSAGE  => 'sky',
            self::EVENT_FILE_UPLOADED       => 'teal',
            self::EVENT_METADATA_UPDATED    => 'orange',
            self::EVENT_PUBLISHED           => 'green',
            default                         => 'gray',
        };
    }

    /**
     * Human-readable stage label.
     */
    public function getStageLabelAttribute(): string
    {
        return match ($this->stage) {
            'submission'   => 'Submission',
            'review'       => 'Review',
            'copyediting'  => 'Copyediting',
            'production'   => 'Production',
            default        => ucfirst($this->stage ?? ''),
        };
    }

    // =====================================================
    // STATIC HELPERS
    // =====================================================

    /**
     * Create a log entry.
     *
     * @param Submission  $submission
     * @param string      $eventType  One of the EVENT_* constants
     * @param string      $title
     * @param string|null $description
     * @param array|null  $metadata
     * @param User|null   $user       Defaults to the authenticated user
     * @param array       $fileIds    Array of submission_files.id to attach
     * @param string|null $stage      Workflow stage string (submission|review|copyediting|production)
     * @param string|null $emailSubject
     * @param string|null $emailBody
     */
    public static function log(
        Submission $submission,
        string $eventType,
        string $title,
        ?string $description = null,
        ?array $metadata = null,
        ?User $user = null,
        array $fileIds = [],
        ?string $stage = null,
        ?string $emailSubject = null,
        ?string $emailBody = null
    ): self {
        $log = self::create([
            'submission_id' => $submission->id,
            'user_id'       => $user?->id ?? auth()->id(),
            'event_type'    => $eventType,
            'title'         => $title,
            'description'   => $description,
            'metadata'      => $metadata,
            'stage'         => $stage ?? $submission->stage,
            'email_subject' => $emailSubject,
            'email_body'    => $emailBody,
        ]);

        if (!empty($fileIds)) {
            $log->files()->attach($fileIds);
        }

        // Pemicu Notifikasi Terpusat
        self::sendWorkflowNotificationsToRelatedParties($submission, $log, $user);

        return $log;
    }

    /**
     * Memproses dan mengirimkan notifikasi email ke pihak terkait berdasarkan tipe event log.
     */
    protected static function sendWorkflowNotificationsToRelatedParties(Submission $submission, SubmissionLog $log, ?User $triggerUser): void
    {
        try {
            $journal = $submission->journal;
            if (!$journal) return;

            $triggerUserId = $triggerUser?->id ?? auth()->id();
            $actionUrl = route('journal.submissions.show', [
                'journal' => $journal->slug,
                'submission' => $submission->seq_id
            ]);

            // 1. Ambil Editor & Manager Jurnal (Aktif & Global)
            $editorRoles = Role::withoutGlobalScope('journal')
                ->whereIn('name', ['Journal manager', 'Journal editor', 'Section editor', 'Guest editor'])
                ->where('journal_id', $journal->id)
                ->pluck('id');

            $allJournalEditors = User::whereHas('journalRoles', function ($q) use ($journal, $editorRoles) {
                $q->where('journal_id', $journal->id)
                  ->whereIn('role_id', $editorRoles);
            })->get();

            // 2. Ambil Editor Aktif yang ditugaskan ke artikel ini
            $assignedEditors = $submission->activeEditors()
                ->with('user')->get()
                ->map(fn($a) => $a->user)
                ->filter()
                ->reject(fn($u) => $u->id === $triggerUserId);

            // Logika Distribusi Berdasarkan Tipe Event
            switch ($log->event_type) {
                case self::EVENT_SUBMITTED:
                    // Notify Author
                    if ($submission->author && $submission->author->id !== $triggerUserId) {
                        try {
                            $submission->author->notify(new \App\Notifications\SubmissionReceived($submission));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to notify author on submission: " . $e->getMessage());
                        }
                    }
                    // Notify All Journal Editors
                    foreach ($allJournalEditors as $editor) {
                        if ($editor->id !== $triggerUserId) {
                            try {
                                $editor->notify(new \App\Notifications\NewSubmissionNotification($submission));
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error("Failed to notify editor on submission: " . $e->getMessage());
                            }
                        }
                    }
                    break;

                case self::EVENT_DISCUSSION_CREATED:
                case self::EVENT_DISCUSSION_MESSAGE:
                    // Ambil partisipan diskusi untuk log diskusi
                    $discussionId = $log->metadata['discussion_id'] ?? null;
                    if ($discussionId) {
                        $discussion = \App\Models\Discussion::with('participants')->find($discussionId);
                        if ($discussion) {
                            $message = null;
                            $messageId = $log->metadata['message_id'] ?? $log->metadata['discussion_message_id'] ?? null;
                            if ($messageId) {
                                $message = \App\Models\DiscussionMessage::find($messageId);
                            }

                            foreach ($discussion->participants as $participant) {
                                if ($participant->id !== $triggerUserId) {
                                    try {
                                        if ($message) {
                                            $participant->notify(new \App\Notifications\NewDiscussionMessageNotification($discussion, $message, $triggerUser ?? auth()->user() ?? $message->user));
                                        } else {
                                            $participant->notify(new \App\Notifications\WorkflowEventNotification(
                                                $submission,
                                                $log->title,
                                                $log->description ?? $log->title,
                                                $actionUrl . '?tab=discussion'
                                            ));
                                        }
                                    } catch (\Exception $e) {
                                        \Illuminate\Support\Facades\Log::error("Failed to notify participant on discussion event: " . $e->getMessage());
                                    }
                                }
                            }
                        }
                    }
                    break;

                case self::EVENT_REVIEW_SUBMITTED:
                    // Notify All Journal Editors
                    foreach ($allJournalEditors as $editor) {
                        if ($editor->id !== $triggerUserId) {
                            try {
                                $assignment = $submission->reviewAssignments()->latest()->first();
                                if ($assignment) {
                                    $editor->notify(new \App\Notifications\ReviewCompleted($assignment));
                                }
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error("Failed to notify editor on review submit: " . $e->getMessage());
                            }
                        }
                    }
                    break;

                case self::EVENT_DECISION_MADE:
                    // Notify Author
                    if ($submission->author && $submission->author->id !== $triggerUserId) {
                        try {
                            $decisionInfo = $submission->metadata['decisions'] ?? [];
                            $lastDecision = end($decisionInfo);
                            $decision = $lastDecision['decision'] ?? 'accept';
                            $comments = $lastDecision['comments'] ?? '';
                            $submission->author->notify(new \App\Notifications\SubmissionDecision($submission, $decision, $comments));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to notify author on decision: " . $e->getMessage());
                        }
                    }
                    // Notify Assigned Editors
                    foreach ($assignedEditors as $editor) {
                        try {
                            $editor->notify(new \App\Notifications\WorkflowEventNotification(
                                $submission,
                                'Editorial Decision Recorded',
                                $log->description ?? $log->title,
                                $actionUrl
                            ));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to notify editor on decision: " . $e->getMessage());
                        }
                    }
                    break;

                case self::EVENT_PUBLISHED:
                    // Notify Author
                    if ($submission->author && $submission->author->id !== $triggerUserId) {
                        try {
                            $submission->author->notify(new \App\Notifications\ArticlePublished($submission, $submission->issue));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to notify author on publish: " . $e->getMessage());
                        }
                    }
                    // Notify Assigned Editors
                    foreach ($assignedEditors as $editor) {
                        try {
                            $editor->notify(new \App\Notifications\WorkflowEventNotification(
                                $submission,
                                'Submission Published',
                                $log->description ?? $log->title,
                                $actionUrl
                            ));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to notify editor on publish: " . $e->getMessage());
                        }
                    }
                    break;

                case self::EVENT_EDITOR_ASSIGNED:
                case self::EVENT_EDITOR_UNASSIGNED:
                case self::EVENT_REVIEWER_ASSIGNED:
                    // Event-event ini memiliki notifikasi bawaan di controller mereka, tetapi mari kirim notifikasi generik ke editor lain jika dipicu
                    foreach ($assignedEditors as $editor) {
                        try {
                            $editor->notify(new \App\Notifications\WorkflowEventNotification(
                                $submission,
                                $log->title,
                                $log->description ?? $log->title,
                                $actionUrl
                            ));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to notify editor on workflow event: " . $e->getMessage());
                        }
                    }
                    break;

                default:
                    // Event lainnya (Stage Changed, File Uploaded, Metadata Updated, dll.)
                    // Kirim ke semua Assigned Editors
                    foreach ($assignedEditors as $editor) {
                        try {
                            $editor->notify(new \App\Notifications\WorkflowEventNotification(
                                $submission,
                                $log->title,
                                $log->description ?? $log->title,
                                $actionUrl
                            ));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to notify editor on general workflow event: " . $e->getMessage());
                        }
                    }
                    break;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mengirimkan notifikasi alur kerja terpusat: " . $e->getMessage());
        }
    }

    /**
     * Log a metadata diff (before vs after) when submission fields are updated.
     *
     * @param Submission  $submission
     * @param array       $before     Key-value of original values
     * @param array       $after      Key-value of new values
     * @param User|null   $user
     */
    public static function logMetadataDiff(
        Submission $submission,
        array $before,
        array $after,
        ?User $user = null
    ): self {
        // Only keep keys that actually changed
        $changed = array_keys(array_diff_assoc($after, $before));
        $filteredBefore = array_intersect_key($before, array_flip($changed));
        $filteredAfter  = array_intersect_key($after,  array_flip($changed));

        $fields = implode(', ', $changed);

        return self::log(
            submission:  $submission,
            eventType:   self::EVENT_METADATA_UPDATED,
            title:       'Metadata updated: ' . $fields,
            description: 'The following fields were changed: ' . $fields,
            metadata:    ['before' => $filteredBefore, 'after' => $filteredAfter],
            user:        $user,
            stage:       $submission->stage,
        );
    }

    /**
     * Resolve the stage string from an integer stage_id (Discussion.stage_id).
     */
    public static function stageFromId(?int $stageId): ?string
    {
        return self::STAGE_MAP[$stageId] ?? null;
    }
}
