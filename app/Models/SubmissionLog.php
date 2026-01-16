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
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Event type constants
    public const EVENT_SUBMITTED = 'submission_created';
    public const EVENT_EDITOR_ASSIGNED = 'editor_assigned';
    public const EVENT_EDITOR_UNASSIGNED = 'editor_unassigned';
    public const EVENT_REVIEWER_ASSIGNED = 'reviewer_assigned';
    public const EVENT_REVIEW_SUBMITTED = 'review_submitted';
    public const EVENT_DECISION_MADE = 'decision_made';
    public const EVENT_STAGE_CHANGED = 'stage_changed';
    public const EVENT_DISCUSSION_CREATED = 'discussion_created';
    public const EVENT_FILE_UPLOADED = 'file_uploaded';
    public const EVENT_PUBLISHED = 'published';

    /**
     * Get the submission this log belongs to.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Get the user who triggered this event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get icon for event type.
     */
    public function getIconAttribute(): string
    {
        return match ($this->event_type) {
            self::EVENT_SUBMITTED => 'fa-file-circle-plus',
            self::EVENT_EDITOR_ASSIGNED => 'fa-user-tie',
            self::EVENT_EDITOR_UNASSIGNED => 'fa-user-minus',
            self::EVENT_REVIEWER_ASSIGNED => 'fa-clipboard-check',
            self::EVENT_REVIEW_SUBMITTED => 'fa-check-circle',
            self::EVENT_DECISION_MADE => 'fa-gavel',
            self::EVENT_STAGE_CHANGED => 'fa-arrow-right',
            self::EVENT_DISCUSSION_CREATED => 'fa-comments',
            self::EVENT_FILE_UPLOADED => 'fa-file-upload',
            self::EVENT_PUBLISHED => 'fa-globe',
            default => 'fa-circle',
        };
    }

    /**
     * Get color for event type.
     */
    public function getColorAttribute(): string
    {
        return match ($this->event_type) {
            self::EVENT_SUBMITTED => 'indigo',
            self::EVENT_EDITOR_ASSIGNED => 'purple',
            self::EVENT_REVIEWER_ASSIGNED => 'blue',
            self::EVENT_REVIEW_SUBMITTED => 'emerald',
            self::EVENT_DECISION_MADE => 'amber',
            self::EVENT_PUBLISHED => 'green',
            default => 'gray',
        };
    }

    /**
     * Static helper to create a log entry.
     */
    public static function log(
        Submission $submission,
        string $eventType,
        string $title,
        ?string $description = null,
        ?array $metadata = null,
        ?User $user = null
    ): self {
        return self::create([
            'submission_id' => $submission->id,
            'user_id' => $user?->id ?? auth()->id(),
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
