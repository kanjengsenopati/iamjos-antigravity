<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationGalley extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'submission_id',
        'file_id',
        'label',
        'locale',
        'url_remote',
        'seq',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'seq' => 'integer',
        ];
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the submission this galley belongs to
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    /**
     * Get the file associated with this galley
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(SubmissionFile::class, 'file_id');
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    /**
     * Get the download URL for this galley
     */
    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->url_remote) {
            return $this->url_remote;
        }

        if ($this->file) {
            return route('files.download', $this->file);
        }

        return null;
    }

    /**
     * Get formatted label with icon
     */
    public function getLabelIconAttribute(): string
    {
        return match (strtolower($this->label)) {
            'pdf' => 'fa-file-pdf text-red-500',
            'html' => 'fa-file-code text-orange-500',
            'epub' => 'fa-book text-purple-500',
            'xml' => 'fa-file-code text-blue-500',
            default => 'fa-file text-gray-500',
        };
    }

    /**
     * Get locale display name
     */
    public function getLocaleNameAttribute(): string
    {
        $locales = [
            'en' => 'English',
            'id' => 'Indonesian',
            'ar' => 'Arabic',
            'zh' => 'Chinese',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
        ];

        return $locales[$this->locale] ?? ucfirst($this->locale);
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Order by sequence
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('seq')->orderBy('created_at');
    }
}
