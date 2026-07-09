<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionAssignment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Production assignment status constants
     */
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'submission_id',
        'production_user_id',
        'assigned_by',
        'role',
        'status',
        'date_assigned',
        'date_notified',
        'date_completed',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_assigned' => 'datetime',
        'date_notified' => 'datetime',
        'date_completed' => 'datetime',
    ];

    /**
     * Get the submission this assignment belongs to.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Get the assigned production staff (user).
     */
    public function productionUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'production_user_id');
    }

    /**
     * Get the user who made the assignment.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope for active assignments (not cancelled).
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    /**
     * Scope for assignments with a specific status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ASSIGNED => 'Assigned',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
