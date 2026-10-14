<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Structured citation (bibliographic reference) extracted from Publication.references.
 *
 * Each row represents a single parsed reference with structured fields (DOI, title,
 * authors, year, etc.) for precise Google Scholar and CrossRef citation matching.
 *
 * This model is SUPPLEMENTARY to publications.references — the raw text column
 * remains the source of truth. If structured citations are missing, the system
 * falls back to runtime parsing via CitationService::parseReferenceToScholarMeta().
 */
class Citation extends Model
{
    use HasUuids;

    protected $fillable = [
        'publication_id',
        'target_submission_id',
        'matched_by',
        'seq',
        'raw_citation',
        'doi',
        'title',
        'authors',
        'year',
        'source',
        'volume',
        'issue',
        'first_page',
        'last_page',
        'url',
        'is_structured',
        'processing_status',
    ];

    protected $casts = [
        'authors'        => 'array',
        'year'           => 'integer',
        'seq'            => 'integer',
        'is_structured'  => 'boolean',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * The publication this citation belongs to.
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

    /**
     * The internal submission this citation points to (if matched).
     */
    public function targetSubmission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'target_submission_id');
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    /**
     * Generate a Google Scholar-compatible citation_reference key=value string
     * from the structured fields stored in this model.
     *
     * In accordance with PKP OJS GoogleScholarPlugin:
     * Emit key-value format only when Title, Author, and Year exist;
     * otherwise emit the raw bibliographic text string.
     */
    public function toScholarMetaContent(): string
    {
        if (empty($this->title) || empty($this->authors) || empty($this->year)) {
            return $this->raw_citation ?? '';
        }

        $fields = [];

        if ($this->doi) {
            $fields[] = 'citation_doi=' . $this->doi;
        }
        $fields[] = 'citation_title=' . $this->title;
        foreach ($this->authors as $author) {
            $fields[] = 'citation_author=' . $author;
        }
        $fields[] = 'citation_publication_date=' . $this->year;
        if ($this->source) {
            $fields[] = 'citation_journal_title=' . $this->source;
        }
        if ($this->volume) {
            $fields[] = 'citation_volume=' . $this->volume;
        }
        if ($this->issue) {
            $fields[] = 'citation_issue=' . $this->issue;
        }
        if ($this->first_page) {
            $fields[] = 'citation_firstpage=' . $this->first_page;
        }
        if ($this->last_page) {
            $fields[] = 'citation_lastpage=' . $this->last_page;
        }

        return implode('; ', $fields) . ';';
    }
}
