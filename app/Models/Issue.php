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
        'seq_id',
        'doi',
        'doi_suffix',
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
        return 'seq_id';
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // 1. If explicit field specified or value is a valid UUID, search by ID directly
        if ($field === 'id' || \Illuminate\Support\Str::isUuid($value)) {
            return $this->where('id', $value)->firstOrFail();
        }

        // 2. If value is not numeric, check url_path
        if (!is_numeric($value)) {
            $query = $this->where('url_path', $value);
            $issue = $query->first();
            
            if ($issue && $issue->seq_id && request()->isMethod('GET')) {
                $currentUrl = request()->url();
                $newUrl = preg_replace('/\/'.preg_quote($value, '/').'(?=\/|$)/', '/' . $issue->seq_id, $currentUrl, 1);
                if ($newUrl === $currentUrl) {
                    $newUrl = str_replace($value, $issue->seq_id, $currentUrl);
                }
                if (request()->getQueryString()) {
                    $newUrl .= '?' . request()->getQueryString();
                }

                throw new HttpResponseException(redirect($newUrl, 301));
            }
            
            if ($issue) {
                return $issue;
            }
        }

        // 3. For numeric seq_id values, scope by current journal if available
        $query = $this->where($field ?? $this->getRouteKeyName(), $value);
        if ($journal = current_journal()) {
            $query->where('journal_id', $journal->id);
        }

        return $query->firstOrFail();
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

    /**
     * Get galleys for this issue
     */
    public function issueGalleys(): HasMany
    {
        return $this->hasMany(IssueGalley::class, 'issue_id');
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
     * Get formatted issue volume/number/year identifier (e.g. "Vol. 4 No. 2 (2026)")
     */
    public function getIdentifierAttribute(): string
    {
        $parts = [];
        if ($this->volume && ($this->show_volume ?? true)) {
            $parts[] = "Vol. {$this->volume}";
        }
        if ($this->number && ($this->show_number ?? true)) {
            $parts[] = "No. {$this->number}";
        }
        if ($this->year && ($this->show_year ?? true)) {
            $parts[] = "({$this->year})";
        }
        return implode(' ', $parts);
    }

    /**
     * Get full issue identification string in OJS 3 format:
     * e.g. "Vol. 4 No. 2 (2026): Mel: Riset Ilmu Manajemen Bisnis dan Akuntansi"
     * or "Vol. 1 No. 2 (2026): TAWAZUN: Journal of Islamic Finance and Digital Innovation"
     */
    public function getIssueIdentificationAttribute(): string
    {
        $identifier = $this->identifier;
        $title = trim($this->title ?? '');

        // Fallback to journal name if title is empty
        if (empty($title)) {
            if ($this->relationLoaded('journal') && $this->journal) {
                $title = $this->journal->name;
            } elseif ($this->journal_id) {
                $title = Journal::where('id', $this->journal_id)->value('name') ?? '';
            }
        }

        if (!empty($identifier) && !empty($title)) {
            // Check if title already starts with identifier to prevent duplication
            if (Str::startsWith($title, $identifier)) {
                return $title;
            }
            return "{$identifier}: {$title}";
        }

        return !empty($identifier) ? $identifier : $title;
    }

    /**
     * Get display title (uses full OJS 3 issue identification)
     */
    public function getDisplayTitleAttribute(): string
    {
        return $this->issue_identification;
    }

    /**
     * Get a short version of the UUID for UI display fallback.
     */
    public function getIdShortAttribute(): string
    {
        return substr($this->id, 0, 8);
    }
}
