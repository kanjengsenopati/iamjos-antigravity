<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewAssignment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Review status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Recommendation constants
     */
    const RECOMMEND_ACCEPT = 'accept';
    const RECOMMEND_MINOR_REVISION = 'minor_revision';
    const RECOMMEND_MAJOR_REVISION = 'major_revision';
    const RECOMMEND_REJECT = 'reject';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'submission_id',
        'review_round_id',
        'reviewer_id',
        'status',
        'recommendation',
        'comments_for_author',
        'comments_for_editor',
        'quality_rating',
        'assigned_at',
        'due_date',
        'response_due_date',
        'responded_at',
        'completed_at',
        'round',
        'review_method',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'quality_rating' => 'integer',
            'assigned_at' => 'datetime',
            'due_date' => 'datetime',
            'response_due_date' => 'datetime',
            'responded_at' => 'datetime',
            'completed_at' => 'datetime',
            'round' => 'integer',
            'metadata' => 'array',
        ];
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the submission being reviewed
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    /**
     * Get the reviewer (user)
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for pending reviews
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
        ]);
    }

    /**
     * Scope for completed reviews
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for overdue reviews
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', [
                self::STATUS_COMPLETED,
                self::STATUS_DECLINED,
                self::STATUS_CANCELLED,
            ]);
    }

    /**
     * Scope by review round
     */
    public function scopeRound($query, int $round)
    {
        return $query->where('round', $round);
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_DECLINED => 'Declined',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get recommendation label
     */
    public function getRecommendationLabelAttribute(): ?string
    {
        if (!$this->recommendation) {
            return null;
        }

        return match ($this->recommendation) {
            self::RECOMMEND_ACCEPT => 'Accept',
            self::RECOMMEND_MINOR_REVISION => 'Minor Revision',
            self::RECOMMEND_MAJOR_REVISION => 'Major Revision',
            self::RECOMMEND_REJECT => 'Reject',
            default => ucfirst($this->recommendation),
        };
    }

    /**
     * Get recommendation color
     */
    public function getRecommendationColorAttribute(): string
    {
        return match ($this->recommendation) {
            self::RECOMMEND_ACCEPT => 'green',
            self::RECOMMEND_MINOR_REVISION => 'yellow',
            self::RECOMMEND_MAJOR_REVISION => 'orange',
            self::RECOMMEND_REJECT => 'red',
            default => 'gray',
        };
    }

    /**
     * Check if review is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        return $this->due_date->isPast() &&
            !in_array($this->status, [
                self::STATUS_COMPLETED,
                self::STATUS_DECLINED,
                self::STATUS_CANCELLED,
            ]);
    }

    /**
     * Get days until due (negative if overdue)
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }
}
