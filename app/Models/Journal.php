<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'path',
        'slug',
        'abbreviation',
        'description',
        'publisher',
        'issn_print',
        'issn_online',
        'enabled',
        'visible',
        'logo_path',
        'thumbnail_path',
        'settings',
        'license_terms',
        'license_url',
        'copyright_holder_type',
        'copyright_holder_other',
        'copyright_year_basis',
        'search_description',
        'custom_headers',
        'open_access_policy',
        'enable_oai',
        'enable_lockss',
        'enable_clockss',
        'archiving_policy',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'visible' => 'boolean',
            'settings' => 'array', // JSONB to array
            'enable_oai' => 'boolean',
            'enable_lockss' => 'boolean',
            'enable_clockss' => 'boolean',
        ];
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get sections for this journal
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'journal_id');
    }

    /**
     * Get issues for this journal
     */
    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class, 'journal_id');
    }

    /**
     * Get submissions for this journal
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'journal_id');
    }

    /**
     * Get categories for this journal
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'journal_id');
    }

    /**
     * Get submission checklists for this journal
     */
    public function submissionChecklists(): HasMany
    {
        return $this->hasMany(SubmissionChecklist::class, 'journal_id');
    }

    /**
     * Get review forms for this journal
     */
    public function reviewForms(): HasMany
    {
        return $this->hasMany(ReviewForm::class, 'journal_id');
    }

    /**
     * Get library files for this journal
     */
    public function libraryFiles(): HasMany
    {
        return $this->hasMany(LibraryFile::class, 'journal_id');
    }

    /**
     * Get email templates for this journal
     */
    public function emailTemplates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class, 'journal_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope to only include enabled journals
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope to only include visible journals
     */
    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    // =====================================================
    // ACCESSORS & HELPERS
    // =====================================================

    /**
     * Get a specific setting value
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set a specific setting value
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
    }
}
