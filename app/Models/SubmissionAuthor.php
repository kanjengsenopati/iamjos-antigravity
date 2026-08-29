<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionAuthor extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'submission_id',
        'publication_id',
        'user_id',
        'name',
        'given_name',
        'family_name',
        'preferred_public_name',
        'first_name',
        'last_name',
        'email',
        'affiliation',
        'country',
        'orcid',
        'url',
        'biography',
        'is_corresponding',
        'is_primary_contact',
        'include_in_browse',
        'user_group_id',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_corresponding' => 'boolean',
        'is_primary_contact' => 'boolean',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the submission this author belongs to
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    /**
     * Get the user account (if author is registered)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the publication this author belongs to
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope to get corresponding author
     */
    public function scopeCorresponding($query)
    {
        return $query->where('is_corresponding', true);
    }

    /**
     * Ensure exactly one author per submission/publication is marked as primary contact.
     * Queries by BOTH submission_id and publication_id to cover all related authors.
     */
    public static function ensureSinglePrimaryAuthor(string $submissionId, ?string $publicationId = null): void
    {
        // Query by publication_id first (single source of truth), fallback to submission_id
        $authors = collect();
        if ($publicationId) {
            $authors = self::where('publication_id', $publicationId)
                ->orderBy('sort_order')->orderBy('created_at')->get();
        }
        if ($authors->isEmpty()) {
            $authors = self::where('submission_id', $submissionId)
                ->orderBy('sort_order')->orderBy('created_at')->get();
        }

        if ($authors->isEmpty()) {
            return;
        }

        $allIds = $authors->pluck('id')->toArray();
        $primaryAuthors = $authors->filter(fn($a) => $a->is_corresponding || $a->is_primary_contact);

        // Pick exactly one primary author
        $primary = null;
        if ($primaryAuthors->count() === 1) {
            $primary = $primaryAuthors->first();
        } else {
            // 0 or >1 primary: pick the first primary, or fallback to first author
            $primary = $primaryAuthors->first() ?? $authors->first();
        }

        // Set the chosen primary to true
        self::where('id', $primary->id)->update([
            'is_corresponding' => true,
            'is_primary_contact' => true,
        ]);

        // Reset ALL others to false (using individual IDs for accuracy)
        self::whereIn('id', $allIds)
            ->where('id', '!=', $primary->id)
            ->update([
                'is_corresponding' => false,
                'is_primary_contact' => false,
            ]);
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    /**
     * Get formatted ORCID URL
     */
    public function getOrcidUrlAttribute(): ?string
    {
        if (empty($this->orcid)) {
            return null;
        }

        // Remove any existing URL prefix to normalize
        $orcid = preg_replace('/^https?:\/\/orcid\.org\//', '', $this->orcid);

        return "https://orcid.org/{$orcid}";
    }

    /**
     * Get given_name with fallbacks to first_name or first word of name
     */
    public function getGivenNameAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }
        if (!empty($this->attributes['first_name'])) {
            return $this->attributes['first_name'];
        }
        if (!empty($this->attributes['name'])) {
            $parts = explode(' ', trim($this->attributes['name']), 2);
            return $parts[0] ?? null;
        }
        return null;
    }

    /**
     * Get family_name with fallbacks to last_name or remaining words of name
     */
    public function getFamilyNameAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }
        if (!empty($this->attributes['last_name'])) {
            return $this->attributes['last_name'];
        }
        if (!empty($this->attributes['name'])) {
            $parts = explode(' ', trim($this->attributes['name']), 2);
            return $parts[1] ?? null;
        }
        return null;
    }

    /**
     * Get first_name with fallbacks to given_name or first word of name
     */
    public function getFirstNameAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }
        if (!empty($this->attributes['given_name'])) {
            return $this->attributes['given_name'];
        }
        if (!empty($this->attributes['name'])) {
            $parts = explode(' ', trim($this->attributes['name']), 2);
            return $parts[0] ?? null;
        }
        return null;
    }

    /**
     * Get last_name with fallbacks to family_name or remaining words of name
     */
    public function getLastNameAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }
        if (!empty($this->attributes['family_name'])) {
            return $this->attributes['family_name'];
        }
        if (!empty($this->attributes['name'])) {
            $parts = explode(' ', trim($this->attributes['name']), 2);
            return $parts[1] ?? null;
        }
        return null;
    }

    /**
     * Get display name with affiliation
     */
    public function getDisplayNameAttribute(): string
    {
        $display = $this->name;

        if ($this->affiliation) {
            $display .= " ({$this->affiliation})";
        }

        return $display;
    }
}
