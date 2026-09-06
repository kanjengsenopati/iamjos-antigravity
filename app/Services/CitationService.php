<?php

namespace App\Services;

use App\Models\Submission;
use Illuminate\Support\Str;

class CitationService
{
    /**
     * Generate APA Citation
     */
    public function generateAPA(Submission $submission): string
    {
        $year = $this->getYear($submission);
        $authors = $this->getAuthorsAPA($submission);
        $journal = $submission->journal->name;
        $volume = $submission->issue->volume ?? null;
        $number = $submission->issue->number ?? null;
        $pages = $submission->currentPublication->pages ?? $submission->pages;
        $doi = $submission->currentPublication->doi ?? $submission->doi;

        $citation = "{$authors} ({$year}). {$submission->title}. <em>{$journal}</em>";
        if ($volume) $citation .= ", {$volume}";
        if ($number) $citation .= "({$number})";
        if ($pages) $citation .= ", {$pages}";
        if ($doi) $citation .= ". https://doi.org/{$doi}";

        return $citation;
    }

    /**
     * Parse references into an array
     */
    public function parseReferences(?string $references): array
    {
        if (empty($references)) {
            return [];
        }

        // Split by newline and filter empty lines
        return array_values(array_filter(array_map('trim', explode("\n", $references))));
    }

    /**
     * Helper to get publication year
     */
    protected function getYear(Submission $submission): int
    {
        return $submission->issue->year ?? 
               ($submission->published_at ? $submission->published_at->year : now()->year);
    }

    /**
     * Format authors for APA: Last, F.
     */
    protected function getAuthorsAPA(Submission $submission): string
    {
        $authors = $submission->authors->map(function ($author) {
            $last = Str::title(trim($author->last_name ?? ''));
            $first = Str::title(trim($author->first_name ?? ''));
            return $last ? "{$last}, " . mb_substr($first, 0, 1) . "." : $first;
        });

        return $authors->implode(', ');
    }

    /**
     * Generate all citation formats for UI
     */
    public function getAllFormats(Submission $submission): array
    {
        $year = $this->getYear($submission);
        $journal = $submission->journal->name;
        $issue = $submission->issue;
        $volume = $issue->volume ?? null;
        $number = $issue->number ?? null;
        $pages = $submission->currentPublication->pages ?? $submission->pages;
        $doi = $submission->currentPublication->doi ?? $submission->doi;
        $doiUrl = $doi ? "https://doi.org/{$doi}" : route('journal.public.article', ['journal' => $submission->journal->slug, 'article' => $submission->seq_id]);

        // Title case for consistent display
        $titleCase = fn($s) => Str::title(trim($s));

        // Full names for some formats
        $authorsFull = $submission->authors->map(fn($a) => $titleCase($a->first_name . ' ' . $a->last_name))->implode(', ');
        
        // IEEE format: F. Last
        $authorsIEEE = $submission->authors->map(function($a) use ($titleCase) {
            $first = $titleCase($a->first_name);
            $last = $titleCase($a->last_name);
            return ($first ? mb_substr($first, 0, 1) . '. ' : '') . $last;
        })->implode(', ');

        $authorsAPA = $this->getAuthorsAPA($submission);

        return [
            'APA' => "{$authorsAPA} ({$year}). {$submission->title}. <em>{$journal}</em>" . ($volume ? ", {$volume}" : '') . ($number ? "({$number})" : '') . ($pages ? ", {$pages}" : '') . ". {$doiUrl}",
            'ACM' => "{$authorsFull}. {$year}. {$submission->title}. <em>{$journal}</em>" . ($volume ? ", {$volume}" : '') . ($number ? ", {$number}" : '') . ($pages ? ", {$pages}" : '') . ". DOI: {$doiUrl}",
            'ACS' => "{$authorsFull}. {$submission->title}. <em>{$journal}</em> {$year}" . ($volume ? ", {$volume}" : '') . ($number ? "({$number})" : '') . ($pages ? ", {$pages}" : '') . ". {$doiUrl}",
            'ABNT' => mb_strtoupper($authorsFull) . ". {$submission->title}. {$journal}, {$year}." . ($volume ? " v. {$volume}" : '') . ($number ? ", n. {$number}" : '') . ($pages ? ", p. {$pages}" : '') . ". Disponível em: {$doiUrl}",
            'Chicago' => "{$authorsFull}. {$year}. \"{$submission->title}.\" <em>{$journal}</em>" . ($volume ? " {$volume}" : '') . ($number ? ", no. {$number}" : '') . ($pages ? ": {$pages}" : '') . ". {$doiUrl}",
            'Harvard' => "{$authorsFull} ({$year}) '{$submission->title}', <em>{$journal}</em>" . ($volume ? ", vol. {$volume}" : '') . ($number ? ", no. {$number}" : '') . ($pages ? ", pp. {$pages}" : '') . ". Available at: {$doiUrl}",
            'IEEE' => "{$authorsIEEE}, \"{$submission->title},\" <em>{$journal}</em>" . ($volume ? ", vol. {$volume}" : '') . ($number ? ", no. {$number}" : '') . ($pages ? ", pp. {$pages}" : '') . ", {$year}. {$doiUrl}",
            'MLA' => "{$authorsFull}. \"{$submission->title}.\" <em>{$journal}</em>" . ($volume ? ", vol. {$volume}" : '') . ($number ? ", no. {$number}" : '') . ", {$year}" . ($pages ? ", pp. {$pages}" : '') . ". {$doiUrl}",
            'Turabian' => "{$authorsFull}. \"{$submission->title}.\" {$journal}" . ($volume ? " {$volume}" : '') . ($number ? ", no. {$number}" : '') . " ({$year})" . ($pages ? ": {$pages}" : '') . ". {$doiUrl}",
            'Vancouver' => "{$authorsIEEE}. {$submission->title}. {$journal}. {$year}" . ($volume ? ";{$volume}" : '') . ($number ? "({$number})" : '') . ($pages ? ":{$pages}" : '') . ". {$doiUrl}",
        ];
    }

    /**
     * Generate OpenURL COinS span for the submission
     */
    public function generateCOinS(Submission $submission, $journal): string
    {
        $year = $this->getYear($submission);
        $issue = $submission->issue;
        $volume = $issue->volume ?? null;
        $number = $issue->number ?? null;
        $pages = $submission->currentPublication->pages ?? $submission->pages;
        $doi = $submission->currentPublication->doi ?? $submission->doi;

        $ctx = [];
        $ctx['ctx_ver'] = 'Z39.88-2004';
        $ctx['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:journal';
        $ctx['rft.type'] = 'article';
        $ctx['rft.title'] = $submission->title;
        $ctx['rft.jtitle'] = $journal->name;
        if ($submission->journal->issn_online) {
            $ctx['rft.issn'] = $submission->journal->issn_online;
        } elseif ($submission->journal->issn_print) {
            $ctx['rft.issn'] = $submission->journal->issn_print;
        }
        $ctx['rft.date'] = $year;
        if ($volume) $ctx['rft.volume'] = $volume;
        if ($number) $ctx['rft.issue'] = $number;
        if ($pages) {
            $pageParts = explode('-', $pages, 2);
            $ctx['rft.spage'] = trim($pageParts[0]);
            if (isset($pageParts[1])) {
                $ctx['rft.epage'] = trim($pageParts[1]);
            }
        }
        
        if ($doi) {
            $ctx['rft_id'] = 'info:doi/' . $doi;
        } else {
            $ctx['rft_id'] = route('journal.public.article', ['journal' => $submission->journal->slug, 'article' => $submission->seq_id]);
        }

        foreach ($submission->authors as $author) {
            $name = trim(($author->family_name ?? $author->last_name ?? '') . ', ' . ($author->given_name ?? $author->first_name ?? ''));
            if ($name !== ',') {
                $ctx['rft.au'][] = $name;
            }
        }
        
        $query = http_build_query($ctx, '', '&', PHP_QUERY_RFC3986);
        // COinS often needs unindexed array params like rft.au=A&rft.au=B
        $query = preg_replace('/%5B[0-9]+%5D/i', '', $query);

        return '<span class="Z3988" title="' . $query . '"></span>';
    }

    /**
     * Parse a plain-text reference string into a Google Scholar-compatible
     * citation_reference key=value meta content string.
     *
     * Priority order:
     *   1. citation_doi        — highest-fidelity match for Scholar & CrossRef
     *   2. citation_title      — fuzzy match fallback
     *   3. citation_author     — disambiguation
     *   4. citation_publication_date — year
     *   5. citation_volume / citation_issue / citation_firstpage / citation_lastpage
     *
     * If parsing yields fewer than 2 structured fields, the raw text is returned
     * unchanged (safe fallback — no regression).
     *
     * @param  string $rawRef  A single reference line (APA or similar format)
     * @return string          key=value string OR original plain text as fallback
     */
    public static function parseReferenceToScholarMeta(string $rawRef): string
    {
        $rawRef = trim($rawRef);
        if (empty($rawRef)) {
            return '';
        }

        $fields = [];

        // ── 1. DOI ─────────────────────────────────────────────────────────
        // Match bare DOI (10.XXXX/...) or inside doi.org URL
        $doi = null;
        if (preg_match('/\bhttps?:\/\/(?:dx\.)?doi\.org\/(10\.\d{4,}\/[^\s\]>,;]+)/i', $rawRef, $m)) {
            $doi = rtrim($m[1], '.,;)"\'');
        } elseif (preg_match('/\b(10\.\d{4,}\/[^\s\]>,;]+)/i', $rawRef, $m)) {
            $doi = rtrim($m[1], '.,;)"\'');
        }
        if ($doi) {
            $fields[] = 'citation_doi=' . $doi;
        }

        // ── 2. Year ────────────────────────────────────────────────────────
        $year = null;
        if (preg_match('/\((\d{4}[a-z]?)\)/', $rawRef, $m)) {
            $year = $m[1];
            $fields[] = 'citation_publication_date=' . $year;
        }

        // ── 3. Authors ─────────────────────────────────────────────────────
        // Everything before the first "(YEAR)" — APA pattern
        if ($year && preg_match('/^(.+?)\s*\(\d{4}[a-z]?\)/', $rawRef, $m)) {
            $rawAuthors = trim($m[1], " ,.\t");
            if ($rawAuthors) {
                // Split on "; " first (Vancouver/numbered style)
                $authorList = preg_split('/;\s+/', $rawAuthors, -1, PREG_SPLIT_NO_EMPTY);
                // If only one element, try APA comma-split: "Surname, I., Surname2, I2."
                if (count($authorList) === 1) {
                    preg_match_all(
                        '/[^\s,][^,]+,\s+[A-Z](?:\.[A-Z])*\.(?=\s|,|$)/u',
                        $rawAuthors,
                        $am
                    );
                    if (!empty($am[0])) {
                        $authorList = $am[0];
                    }
                }
                foreach ($authorList as $author) {
                    $author = trim($author, " ,.\t");
                    if (mb_strlen($author) > 3) {
                        $fields[] = 'citation_author=' . $author;
                    }
                }
            }
        }

        // ── 4. Title ───────────────────────────────────────────────────────
        // APA: text immediately after "(YEAR). " and before the journal name.
        if ($year) {
            $afterYear = preg_replace('/^.*?\(\d{4}[a-z]?\)\.\s*/u', '', $rawRef, 1);
            if ($afterYear && $afterYear !== $rawRef) {
                if (preg_match('/^(.+?)(?:\.\s+[A-Z\p{Lu}]|\.$)/u', $afterYear, $tm)) {
                    $title = trim($tm[1]);
                    // Remove trailing DOI URL that may have been appended
                    $title = preg_replace('/\s+https?:\/\/\S+/i', '', $title);
                    $title = trim($title, " .\t");
                    if (mb_strlen($title) > 8) {
                        $fields[] = 'citation_title=' . $title;
                    }
                }
            }
        }

        // ── 5. Volume & Issue ──────────────────────────────────────────────
        // Pattern: "N(N)" e.g. "3(2)" or "Vol. 3 No. 2"
        if (preg_match('/[,\s](\d+)\((\d+)\)/', $rawRef, $m)) {
            $fields[] = 'citation_volume=' . $m[1];
            $fields[] = 'citation_issue=' . $m[2];
        } elseif (preg_match('/[Vv]ol\.?\s*(\d+)[,;\s]+[Nn]o\.?\s*(\d+)/', $rawRef, $m)) {
            $fields[] = 'citation_volume=' . $m[1];
            $fields[] = 'citation_issue=' . $m[2];
        }

        // ── 6. Pages ───────────────────────────────────────────────────────
        // Pattern: "20-29" or "20–29" (en-dash)
        if (preg_match('/[,\s](\d+)\s*[–\-]\s*(\d+)(?:[.,\s]|$)/', $rawRef, $m)) {
            $fields[] = 'citation_firstpage=' . $m[1];
            $fields[] = 'citation_lastpage=' . $m[2];
        }

        // ── Safety fallback ────────────────────────────────────────────────
        // If we didn't extract at least 2 fields, return the raw string
        // so Scholar can still attempt its own NLP parsing (no regression).
        if (count($fields) < 2) {
            return $rawRef;
        }

        return implode('; ', $fields) . ';';
    }
}

