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

    // =====================================================
    // ACCESSORS
    // =====================================================

    /**
     * Generate a Google Scholar-compatible citation_reference key=value string
     * from the structured fields stored in this model.
     *
     * Falls back to raw_citation if not enough structured data is available.
     */
    public function toScholarMetaContent(): string
    {
        $fields = [];

        if ($this->doi) {
            $fields[] = 'citation_doi=' . $this->doi;
        }
        if ($this->title) {
            $fields[] = 'citation_title=' . $this->title;
        }
        if (!empty($this->authors)) {
            foreach ($this->authors as $author) {
                $fields[] = 'citation_author=' . $author;
            }
        }
        if ($this->year) {
            $fields[] = 'citation_publication_date=' . $this->year;
        }
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

        // Fallback: if less than 2 structured fields, return raw text
        if (count($fields) < 2) {
            return $this->raw_citation;
        }

        return implode('; ', $fields) . ';';
    }
}
