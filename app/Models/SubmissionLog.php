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
    public const EVENT_REVIEWER_UNASSIGNED   = 'reviewer_unassigned';
    public const EVENT_REVIEW_SUBMITTED      = 'review_submitted';
    public const EVENT_DECISION_MADE         = 'decision_made';
    public const EVENT_STAGE_CHANGED         = 'stage_changed';
    public const EVENT_DISCUSSION_CREATED    = 'discussion_created';
    public const EVENT_DISCUSSION_MESSAGE    = 'discussion_message_sent';
    public const EVENT_FILE_UPLOADED         = 'file_uploaded';
    public const EVENT_METADATA_UPDATED      = 'metadata_updated';
    public const EVENT_PUBLISHED             = 'published';
    public const EVENT_UNPUBLISHED           = 'unpublished';

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
            self::EVENT_REVIEWER_UNASSIGNED => 'fa-user-xmark',
            self::EVENT_REVIEW_SUBMITTED    => 'fa-check-circle',
            self::EVENT_DECISION_MADE       => 'fa-gavel',
            self::EVENT_STAGE_CHANGED       => 'fa-arrow-right-arrow-left',
            self::EVENT_DISCUSSION_CREATED  => 'fa-comments',
            self::EVENT_DISCUSSION_MESSAGE  => 'fa-message',
            self::EVENT_FILE_UPLOADED       => 'fa-file-arrow-up',
            self::EVENT_METADATA_UPDATED    => 'fa-pen-to-square',
            self::EVENT_PUBLISHED           => 'fa-globe',
            self::EVENT_UNPUBLISHED         => 'fa-globe-slash',
            default                         => 'fa-circle',
        };
    }

    public function getColorAttribute(): string
    {
        return match ($this->event_type) {
            self::EVENT_SUBMITTED           => 'indigo',
            self::EVENT_EDITOR_ASSIGNED     => 'purple',
            self::EVENT_EDITOR_UNASSIGNED   => 'amber',
            self::EVENT_REVIEWER_ASSIGNED   => 'blue',
            self::EVENT_REVIEWER_UNASSIGNED => 'red',
            self::EVENT_REVIEW_SUBMITTED    => 'emerald',
            self::EVENT_DECISION_MADE       => 'amber',
            self::EVENT_DISCUSSION_CREATED,
            self::EVENT_DISCUSSION_MESSAGE  => 'sky',
            self::EVENT_FILE_UPLOADED       => 'teal',
            self::EVENT_METADATA_UPDATED    => 'orange',
            self::EVENT_PUBLISHED           => 'green',
            self::EVENT_UNPUBLISHED         => 'amber',
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
                'submission' => $submission->url_slug,
            ]);

            // Ambil data author target (Primary Contact -> Corresponding -> First Author)
            $authorRecipient = null;
            if ($submission->authors()->exists()) {
                $authorRecipient = $submission->authors()->where('is_primary_contact', true)->first()
                    ?? $submission->authors()->where('is_corresponding', true)->first()
                    ?? $submission->authors()->first();
            }

            // Dapatkan user account jika terdaftar (atau fallback ke submitter $submission->author)
            $authorUser = $authorRecipient?->user ?? $submission->author;
            $authorEmail = $authorRecipient?->email ?? $submission->author?->email;

            // 1. Ambil Editor & Manager Jurnal (Aktif & Global) berdasarkan permission_level
            // OJS 3.3 default: Super Admin (0), Admin/Manager (1), Editor/Section Editor (2)
            $allJournalEditors = User::whereHas('journalRoles', function ($q) use ($journal) {
                $q->where('journal_id', $journal->id)
                  ->whereHas('role', function ($rq) {
                      $rq->whereIn('permission_level', [
                          Role::LEVEL_SUPER_ADMIN,
                          Role::LEVEL_MANAGER,
                          Role::LEVEL_ADMIN,
                          Role::LEVEL_EDITOR,
                          Role::LEVEL_SECTION_EDITOR,
                      ]);
                  });
            })->get();

            // Jika belum ada editor/manager khusus di jurnal, sertakan Super Admin sistem
            if ($allJournalEditors->isEmpty()) {
                $allJournalEditors = User::whereHas('roles', function ($q) {
                    $q->where('name', Role::ROLE_SUPERADMIN);
                })->get();
            }

            // Filter: Jika artikel sudah memiliki editor yang ditugaskan,
            // maka semua notifikasi selanjutnya hanya dikirim ke editor tersebut.
            $assignedEditorIds = $submission->editorialAssignments()
                ->where('is_active', true)
                ->pluck('user_id')
                ->toArray();

            if (!empty($assignedEditorIds)) {
                $allJournalEditors = $allJournalEditors->filter(function ($editor) use ($assignedEditorIds) {
                    return in_array($editor->id, $assignedEditorIds);
                });
            }

            $globalEmailsSent = [];

            // Logika Distribusi Berdasarkan Tipe Event
            switch ($log->event_type) {
                case self::EVENT_SUBMITTED:
                    // Handled directly and synchronously by SendSubmissionNotifications
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
                                        $globalEmailsSent[] = strtolower($participant->email);
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
                                    $globalEmailsSent[] = strtolower($editor->email);
                                }
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error("Failed to notify editor on review submit: " . $e->getMessage());
                            }
                        }
                    }
                    break;

                case self::EVENT_DECISION_MADE:
                    // Notify Author (User Terdaftar atau On-Demand Email)
                    $isSender = $authorUser && $authorUser->id === $triggerUserId;
                    if (!$isSender && ($authorUser || $authorEmail)) {
                        try {
                            $log->load('files');
                            $attachments = [];
                            foreach ($log->files as $file) {
                                $attachments[] = [
                                    'path' => $file->file_path,
                                    'name' => $file->file_name,
                                    'mime' => $file->mime_type,
                                ];
                            }

                            $decisionInfo = $submission->metadata['decisions'] ?? [];
                            $lastDecision = end($decisionInfo);
                            $decision = $lastDecision['decision'] ?? $lastDecision['type'] ?? 'accept';
                            if ($decision === 'revision_request') {
                                $decision = 'revision_required';
                            }
                            $comments = $lastDecision['comments'] ?? '';

                            $notifyAuthor = $lastDecision['notify_author'] ?? $log->metadata['notify_author'] ?? true;
                            if ($notifyAuthor) {
                                if ($authorUser) {
                                    $authorUser->notify(new \App\Notifications\SubmissionDecision($submission, $decision, $comments, $attachments));
                                } else {
                                    \Illuminate\Support\Facades\Notification::route('mail', $authorEmail)
                                        ->notify(new \App\Notifications\SubmissionDecision($submission, $decision, $comments, $attachments));
                                }
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to notify author on decision: " . $e->getMessage());
                        }
                    }
                    break;

                case self::EVENT_PUBLISHED:
                    // Notify Author (User Terdaftar atau On-Demand Email)
                    $isSender = $authorUser && $authorUser->id === $triggerUserId;
                    if (!$isSender && ($authorUser || $authorEmail)) {
                        try {
                            if ($authorUser) {
                                $authorUser->notify(new \App\Notifications\ArticlePublished($submission, $submission->issue));
                            } else {
                                \Illuminate\Support\Facades\Notification::route('mail', $authorEmail)
                                    ->notify(new \App\Notifications\ArticlePublished($submission, $submission->issue));
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to notify author on publish: " . $e->getMessage());
                        }
                    }
                    break;

                default:
                    break;
            }

            // Jangan kirim notifikasi generik jika event tersebut sudah memiliki alur notifikasi terdedikasi
            $skipGenericBroadcast = in_array($log->event_type, [
                self::EVENT_SUBMITTED,
                self::EVENT_EDITOR_ASSIGNED,
                self::EVENT_EDITOR_REMOVED,
                self::EVENT_STAGE_CHANGED,
                self::EVENT_METADATA_UPDATED,
                self::EVENT_FILE_UPLOADED,
                self::EVENT_FILE_DELETED,
                self::EVENT_PUBLISHED,
                self::EVENT_DECISION_MADE,
            ]);

            if ($skipGenericBroadcast) {
                return;
            }

            // Kirim notifikasi generik ke semua Journal Editor, Journal Manager, dan Principal Contact
            // untuk SETIAP log workflow naskah (kecuali yang sudah dikirimi notifikasi spesifik di atas)
            foreach ($allJournalEditors as $editor) {
                if ($editor->id === $triggerUserId) {
                    continue;
                }

                $editorEmailLower = strtolower($editor->email);
                if (!in_array($editorEmailLower, $globalEmailsSent)) {
                    try {
                        $editor->notify(new \App\Notifications\WorkflowEventNotification(
                            $submission,
                            $log->title,
                            $log->description ?? $log->title,
                            $actionUrl
                        ));
                        $globalEmailsSent[] = $editorEmailLower;
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to notify global editor on workflow event: " . $e->getMessage());
                    }
                }
            }

            // Kirim ke Principal Contact (jika ada, dan emailnya belum dikirimi notifikasi)
            $principalEmail = $journal->getSetting('contact.principal.email') ?? ($journal->settings['contact']['principal']['email'] ?? null);
            if ($principalEmail && filter_var($principalEmail, FILTER_VALIDATE_EMAIL)) {
                $principalEmailLower = strtolower($principalEmail);
                
                // Cari apakah ada user dengan email ini agar bisa dikirimi notifikasi lewat DB + Mail
                $principalUser = User::where('email', $principalEmail)->first();
                
                $isTrigger = false;
                if ($triggerUser && strtolower($triggerUser->email) === $principalEmailLower) {
                    $isTrigger = true;
                } elseif (auth()->check() && strtolower(auth()->user()->email) === $principalEmailLower) {
                    $isTrigger = true;
                }

                if (!$isTrigger && !in_array($principalEmailLower, $globalEmailsSent)) {
                    try {
                        $notification = null;
                        if ($log->event_type === self::EVENT_SUBMITTED) {
                            $notification = new \App\Notifications\NewSubmissionNotification($submission);
                        } elseif ($log->event_type === self::EVENT_DECISION_MADE) {
                            $decisionInfo = $submission->metadata['decisions'] ?? [];
                            $lastDecision = end($decisionInfo);
                            $decision = $lastDecision['decision'] ?? $lastDecision['type'] ?? 'accept';
                            if ($decision === 'revision_request') {
                                $decision = 'revision_required';
                            }
                            $comments = $lastDecision['comments'] ?? '';
                            $notification = new \App\Notifications\SubmissionDecision($submission, $decision, $comments);
                        } else {
                            $notification = new \App\Notifications\WorkflowEventNotification(
                                $submission,
                                $log->title,
                                $log->description ?? $log->title,
                                $actionUrl
                            );
                        }

                        if ($principalUser) {
                            $principalUser->notify($notification);
                        } else {
                            \Illuminate\Support\Facades\Notification::route('mail', $principalEmail)
                                ->notify($notification);
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to notify principal contact on workflow event: " . $e->getMessage());
                    }
                }
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

    /**
     * Ensure a published log entry exists for a published submission.
     */
    public static function ensurePublishedLog(Submission $submission): void
    {
        $isPublished = $submission->status === Submission::STATUS_PUBLISHED
            || $submission->published_at !== null
            || ($submission->currentPublication && $submission->currentPublication->status === \App\Models\Publication::STATUS_PUBLISHED);

        if ($isPublished) {
            $hasLog = self::where('submission_id', $submission->id)
                ->where('event_type', self::EVENT_PUBLISHED)
                ->exists();

            if (!$hasLog) {
                $publishedAt = $submission->published_at 
                    ?? $submission->currentPublication?->date_published 
                    ?? $submission->updated_at 
                    ?? now();

                $issueId = $submission->issue_id ?? $submission->currentPublication?->issue_id;
                $issue = $submission->issue ?? ($issueId ? \App\Models\Issue::find($issueId) : null);
                $issueIdentifier = $issue?->identifier ?? 'the journal';

                self::create([
                    'submission_id' => $submission->id,
                    'user_id'       => $submission->user_id ?? auth()->id(),
                    'event_type'    => self::EVENT_PUBLISHED,
                    'title'         => 'Article Published',
                    'description'   => "This submission was published in {$issueIdentifier}.",
                    'stage'         => 'production',
                    'created_at'    => $publishedAt,
                    'updated_at'    => $publishedAt,
                ]);
            }
        }
    }
}
