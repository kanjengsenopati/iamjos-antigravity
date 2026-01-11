<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Submission status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_REVISION_REQUIRED = 'revision_required';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PUBLISHED = 'published';

    /**
     * Submission stage constants
     */
    const STAGE_SUBMISSION = 'submission';
    const STAGE_REVIEW = 'review';
    const STAGE_REVISION = 'revision';
    const STAGE_COPYEDITING = 'copyediting';
    const STAGE_PRODUCTION = 'production';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'journal_id',
        'user_id',
        'section_id',
        'issue_id',
        'title',
        'subtitle',
        'abstract',
        'keywords',
        'submission_file_path',
        'status',
        'stage',
        'stage_id',
        'submitted_at',
        'accepted_at',
        'published_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'accepted_at' => 'datetime',
            'published_at' => 'datetime',
            'metadata' => 'array', // JSONB to array
        ];
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the journal this submission belongs to
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    /**
     * Get the author (user) who submitted this
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the section this submission belongs to
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Get the issue this submission is assigned to
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class, 'issue_id');
    }

    /**
     * Get all files for this submission
     */
    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class, 'submission_id');
    }

    /**
     * Get all authors for this submission
     */
    public function authors(): HasMany
    {
        return $this->hasMany(SubmissionAuthor::class, 'submission_id')->orderBy('sort_order');
    }

    /**
     * Get review assignments for this submission
     */
    public function reviewAssignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class, 'submission_id');
    }

    /**
     * Get review rounds for this submission
     */
    public function reviewRounds(): HasMany
    {
        return $this->hasMany(ReviewRound::class, 'submission_id')->orderBy('round');
    }

    /**
     * Get current (latest) review round
     */
    public function currentReviewRound()
    {
        return $this->reviewRounds()->latest('round')->first();
    }

    /**
     * Get editorial assignments for this submission
     */
    public function editorialAssignments(): HasMany
    {
        return $this->hasMany(EditorialAssignment::class, 'submission_id');
    }

    /**
     * Get active editorial assignments
     */
    public function activeEditors(): HasMany
    {
        return $this->editorialAssignments()->where('is_active', true);
    }

    /**
     * Get discussions for this submission
     */
    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class, 'submission_id');
    }

    /**
     * Check if submission has an assigned editor
     */
    public function hasEditor(): bool
    {
        return $this->editorialAssignments()->where('is_active', true)->exists();
    }

    // =====================================================
    // STAGE CONSTANTS (OJS 3.3 Style)
    // =====================================================

    const STAGE_ID_SUBMISSION = 1;
    const STAGE_ID_REVIEW = 2;
    const STAGE_ID_COPYEDITING = 3;
    const STAGE_ID_PRODUCTION = 4;
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
     * Scope to filter by stage
     */
    public function scopeStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }

    /**
     * Scope for submissions awaiting editorial decision (all active)
     */
    public function scopeInQueue($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SUBMITTED,
            self::STATUS_IN_REVIEW,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_REVISION_REQUIRED,
        ]);
    }

    /**
     * Scope for UNASSIGNED submissions (OJS 3.3 style)
     * Submissions that are submitted but not yet picked up by an editor
     */
    public function scopeUnassigned($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED)
            ->where('stage_id', 1); // Stage 1 = Submission
    }

    /**
     * Scope for ASSIGNED/ACTIVE submissions (editor has picked it up)
     */
    public function scopeAssigned($query)
    {
        return $query->whereIn('status', [
            self::STATUS_IN_REVIEW,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_REVISION_REQUIRED,
        ])->orWhere(function ($q) {
            // Also include submitted but moved past stage 1
            $q->where('status', self::STATUS_SUBMITTED)
                ->where('stage_id', '>', 1);
        });
    }

    /**
     * Scope for archived submissions
     */
    public function scopeArchived($query)
    {
        return $query->whereIn('status', [
            self::STATUS_ACCEPTED,
            self::STATUS_REJECTED,
            self::STATUS_PUBLISHED,
        ]);
    }

    /**
     * Scope for published submissions
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    // =====================================================
    // ACCESSORS & HELPERS
    // =====================================================

    /**
     * Get status label with proper formatting
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_IN_REVIEW => 'In Review',
            self::STATUS_REVISION_REQUIRED => 'Revision Required',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PUBLISHED => 'Published',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status color for badges
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_SUBMITTED => 'blue',
            self::STATUS_IN_REVIEW => 'yellow',
            self::STATUS_REVISION_REQUIRED => 'orange',
            self::STATUS_ACCEPTED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_PUBLISHED => 'emerald',
            default => 'gray',
        };
    }

    /**
     * Get keywords as array
     */
    public function getKeywordsArrayAttribute(): array
    {
        if (empty($this->keywords)) {
            return [];
        }

        return array_map('trim', explode(',', $this->keywords));
    }

    /**
     * Check if submission can be edited
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_REVISION_REQUIRED,
        ]);
    }

    /**
     * Get corresponding author
     */
    public function getCorrespondingAuthor()
    {
        return $this->authors()->where('is_corresponding', true)->first();
    }
}
