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
        $doiUrl = $doi ? "https://doi.org/{$doi}" : "";

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
}
