<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Http\Exceptions\HttpResponseException;

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
    protected $casts = [
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

    // =====================================================
    // ROUTE MODEL BINDING & REDIRECTION
    // =====================================================

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'url_path';
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // If the value is a valid UUID, handle backward compatibility (301 Redirect)
        if (Str::isUuid($value)) {
            $issue = $this->where('id', $value)->first();
            
            if ($issue && $issue->url_path) {
                // Generate the correct URL by replacing the UUID with the new url_path
                $currentUrl = request()->url();
                $newUrl = str_replace($value, $issue->url_path, $currentUrl);
                
                // Preserve query strings if any
                if (request()->getQueryString()) {
                    $newUrl .= '?' . request()->getQueryString();
                }

                throw new HttpResponseException(redirect($newUrl, 301));
            }
            
            if ($issue) {
               return $issue;
            }
        }

        return $this->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }

    // =====================================================
    // MODEL EVENTS (Auto-generate url_path)
    // =====================================================

    protected static function booted(): void
    {
        static::creating(function (Issue $issue) {
            if (empty($issue->url_path)) {
                $issue->url_path = static::generateUniqueUrlPath($issue);
            }
        });

        static::updating(function (Issue $issue) {
            if (empty($issue->url_path)) {
                $issue->url_path = static::generateUniqueUrlPath($issue);
            }
        });
    }

    public static function generateUniqueUrlPath(Issue $issue): string
    {
        if ($issue->show_title && !empty($issue->title)) {
            $baseSlug = Str::slug($issue->title);
        } else {
            $baseSlug = "v{$issue->volume}-n{$issue->number}-{$issue->year}";
        }

        $slug = $baseSlug;
        $counter = 1;

        while (static::where('journal_id', $issue->journal_id)
            ->where('url_path', $slug)
            ->where('id', '!=', $issue->id)
            ->exists()) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        return $slug;
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
