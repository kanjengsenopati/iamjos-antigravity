<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Issue extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'journal_id',
        'volume',
        'number',
        'year',
        'title',
        'show_volume',
        'show_number',
        'show_year',
        'show_title',
        'description',
        'url_path',
        'is_published',
        'published_at',
        'cover_path',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'volume' => 'integer',
            'number' => 'integer',
            'year' => 'integer',
            'show_volume' => 'boolean',
            'show_number' => 'boolean',
            'show_year' => 'boolean',
            'show_title' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'metadata' => 'array', // JSONB to array
        ];
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the journal that owns this issue
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    /**
     * Get submissions/articles in this issue
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'issue_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope to only include published issues
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope to order by newest first
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('year', 'desc')
            ->orderBy('volume', 'desc')
            ->orderBy('number', 'desc');
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    /**
     * Get formatted issue identifier (Vol. X No. Y, Year)
     */
    public function getIdentifierAttribute(): string
    {
        return "Vol. {$this->volume} No. {$this->number}, {$this->year}";
    }

    /**
     * Get display title (custom title or identifier)
     */
    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?: $this->identifier;
    }
}
