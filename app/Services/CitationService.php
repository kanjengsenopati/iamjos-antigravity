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
        $journal = e($submission->journal->name);
        $volume = $submission->issue->volume ?? null;
        $number = $submission->issue->number ?? null;
        $pages = $submission->currentPublication->pages ?? $submission->pages;
        $doi = $submission->currentPublication->doi ?? $submission->doi;
        $title = e($submission->currentPublication->title ?? $submission->title);

        $citation = "{$authors} ({$year}). {$title}. <em>{$journal}</em>";
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
        $pub = $submission->currentPublication;
        $authorCollection = $pub?->authors ?? $submission->authors;
        $authors = $authorCollection->map(function ($author) {
            $last = trim($author->last_name ?? '');
            $first = trim($author->first_name ?? '');
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
        $journal = e($submission->journal->name);
        $issue = $submission->issue;
        $volume = $issue->volume ?? null;
        $number = $issue->number ?? null;
        $pages = $submission->currentPublication->pages ?? $submission->pages;
        $doi = $submission->currentPublication->doi ?? $submission->doi;
        $doiUrl = $doi ? "https://doi.org/{$doi}" : route('journal.public.article', ['journal' => $submission->journal->slug, 'article' => $submission->seq_id]);

        // Use author names as-is from database (preserve intentional casing like "van der Berg")
        $cleanName = fn($s) => trim($s);

        // Title from currentPublication with fallback, HTML-escaped
        $title = e($submission->currentPublication->title ?? $submission->title);

        $pub = $submission->currentPublication;
        $authorCollection = $pub?->authors ?? $submission->authors;

        // Full names for some formats
        $authorsFull = $authorCollection->map(fn($a) => $cleanName($a->first_name . ' ' . $a->last_name))->implode(', ');
        
        // IEEE format: F. Last
        $authorsIEEE = $authorCollection->map(function($a) use ($cleanName) {
            $first = $cleanName($a->first_name);
            $last = $cleanName($a->last_name);
            return ($first ? mb_substr($first, 0, 1) . '. ' : '') . $last;
        })->implode(', ');

        $authorsAPA = $this->getAuthorsAPA($submission);

        return [
            'APA' => "{$authorsAPA} ({$year}). {$title}. <em>{$journal}</em>" . ($volume ? ", {$volume}" : '') . ($number ? "({$number})" : '') . ($pages ? ", {$pages}" : '') . ". {$doiUrl}",
            'ACM' => "{$authorsFull}. {$year}. {$title}. <em>{$journal}</em>" . ($volume ? ", {$volume}" : '') . ($number ? ", {$number}" : '') . ($pages ? ", {$pages}" : '') . ". DOI: {$doiUrl}",
            'ACS' => "{$authorsFull}. {$title}. <em>{$journal}</em> {$year}" . ($volume ? ", {$volume}" : '') . ($number ? "({$number})" : '') . ($pages ? ", {$pages}" : '') . ". {$doiUrl}",
            'ABNT' => mb_strtoupper($authorsFull) . ". {$title}. {$journal}, {$year}." . ($volume ? " v. {$volume}" : '') . ($number ? ", n. {$number}" : '') . ($pages ? ", p. {$pages}" : '') . ". Disponível em: {$doiUrl}",
            'Chicago' => "{$authorsFull}. {$year}. \"{$title}.\" <em>{$journal}</em>" . ($volume ? " {$volume}" : '') . ($number ? ", no. {$number}" : '') . ($pages ? ": {$pages}" : '') . ". {$doiUrl}",
            'Harvard' => "{$authorsFull} ({$year}) '{$title}', <em>{$journal}</em>" . ($volume ? ", vol. {$volume}" : '') . ($number ? ", no. {$number}" : '') . ($pages ? ", pp. {$pages}" : '') . ". Available at: {$doiUrl}",
            'IEEE' => "{$authorsIEEE}, \"{$title},\" <em>{$journal}</em>" . ($volume ? ", vol. {$volume}" : '') . ($number ? ", no. {$number}" : '') . ($pages ? ", pp. {$pages}" : '') . ", {$year}. {$doiUrl}",
            'MLA' => "{$authorsFull}. \"{$title}.\" <em>{$journal}</em>" . ($volume ? ", vol. {$volume}" : '') . ($number ? ", no. {$number}" : '') . ", {$year}" . ($pages ? ", pp. {$pages}" : '') . ". {$doiUrl}",
            'Turabian' => "{$authorsFull}. \"{$title}.\" {$journal}" . ($volume ? " {$volume}" : '') . ($number ? ", no. {$number}" : '') . " ({$year})" . ($pages ? ": {$pages}" : '') . ". {$doiUrl}",
            'Vancouver' => "{$authorsIEEE}. {$title}. {$journal}. {$year}" . ($volume ? ";{$volume}" : '') . ($number ? "({$number})" : '') . ($pages ? ":{$pages}" : '') . ". {$doiUrl}",
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
        $ctx['rft.title'] = $submission->currentPublication->title ?? $submission->title;
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

        foreach ($submission->currentPublication?->authors ?? $submission->authors as $author) {
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
     * @deprecated Use CitationParserService::toScholarMeta() instead.
     * Kept as proxy for backwards compatibility.
     */
    public static function parseReferenceToScholarMeta(string $rawRef): string
    {
        return \App\Services\CitationParserService::toScholarMeta($rawRef);
    }
}

