<?php

namespace App\Services;

use App\Models\Submission;
use Illuminate\Support\Str;

class CitationService
{
    /**
     * Generate APA Citation (HTML-safe, only static <em> tags unescaped)
     */
    public function generateAPA(Submission $submission): string
    {
        $year = e((string) $this->getYear($submission));
        $authors = $this->getAuthorsAPA($submission);
        $journal = e((string) ($submission->journal?->name ?? ''));
        $volume = $submission->issue?->volume ? e((string) $submission->issue->volume) : null;
        $number = $submission->issue?->number ? e((string) $submission->issue->number) : null;
        $rawPages = $submission->currentPublication?->pages ?? $submission->pages;
        $pages = $rawPages ? e((string) $rawPages) : null;
        $rawDoi = trim((string) ($submission->currentPublication?->doi ?? $submission->doi ?? ''));
        $cleanDoi = trim((string) preg_replace('#^https?://(dx\.)?doi\.org/#i', '', $rawDoi));
        $doi = $cleanDoi !== '' ? e($cleanDoi) : null;
        $title = e((string) ($submission->currentPublication?->title ?? $submission->title ?? ''));

        $prefix = $authors !== '' ? "{$authors} ({$year}). " : "({$year}). ";
        $citation = "{$prefix}{$title}. <em>{$journal}</em>";
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

        $lines = preg_split('/\R/u', $references) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $line): bool => $line !== ''));
    }

    /**
     * Helper to get publication year
     */
    protected function getYear(Submission $submission): int
    {
        if ($submission->issue?->year) {
            return (int) $submission->issue->year;
        }
        if ($submission->currentPublication?->date_published) {
            return (int) $submission->currentPublication->date_published->year;
        }
        if ($submission->published_at) {
            return (int) $submission->published_at->year;
        }

        return (int) now()->year;
    }

    /**
     * Format authors for APA (HTML-escaped): Last, F. M., &amp; Last2, F.
     */
    protected function getAuthorsAPA(Submission $submission): string
    {
        $pub = $submission->currentPublication;
        $authorCollection = ($pub && $pub->authors && $pub->authors->isNotEmpty())
            ? $pub->authors
            : ($submission->authors ?? collect());

        $authors = $authorCollection->map(function ($author): string {
            $last = trim((string) ($author->family_name ?: ($author->last_name ?? '')));
            $first = trim((string) ($author->given_name ?: ($author->first_name ?? '')));
            $initials = $this->formatInitials($first);
            if ($last !== '' && $initials !== '') {
                return e("{$last}, {$initials}");
            }
            if ($last !== '') {
                return e($last);
            }
            if ($first !== '') {
                return e($first);
            }
            $fallback = trim((string) ($author->preferred_public_name ?: ($author->name ?? '')));

            return $fallback !== '' ? e($fallback) : '';
        })->filter()->values();

        $count = $authors->count();
        if ($count === 0) {
            return '';
        }
        if ($count === 1) {
            return $authors[0];
        }
        if ($count === 2) {
            return $authors[0] . ', &amp; ' . $authors[1];
        }
        $lastAuthor = $authors->pop();

        return $authors->implode(', ') . ', &amp; ' . $lastAuthor;
    }

    /**
     * Format UTF-8 given-name initials (e.g., "John Michael" -> "J. M.").
     */
    protected function formatInitials(string $givenName): string
    {
        if ($givenName === '') {
            return '';
        }
        $tokens = preg_split('/\s+/u', $givenName) ?: [];
        $initials = [];
        foreach ($tokens as $token) {
            $clean = trim($token, " \t\n\r\0\x0B.");
            if ($clean !== '') {
                $initials[] = mb_substr($clean, 0, 1, 'UTF-8') . '.';
            }
        }

        return implode(' ', $initials);
    }

    /**
     * Generate all citation formats for UI (HTML-safe)
     */
    public function getAllFormats(Submission $submission): array
    {
        $year = e((string) $this->getYear($submission));
        $journal = e((string) ($submission->journal?->name ?? ''));
        $issue = $submission->issue;
        $volume = $issue?->volume ? e((string) $issue->volume) : null;
        $number = $issue?->number ? e((string) $issue->number) : null;
        $rawPages = $submission->currentPublication?->pages ?? $submission->pages;
        $pages = $rawPages ? e((string) $rawPages) : null;
        $rawDoi = trim((string) ($submission->currentPublication?->doi ?? $submission->doi ?? ''));
        $cleanDoi = trim((string) preg_replace('#^https?://(dx\.)?doi\.org/#i', '', $rawDoi));
        $rawDoiUrl = $cleanDoi !== ''
            ? "https://doi.org/{$cleanDoi}"
            : ($submission->journal
                ? route('journal.public.article', ['journal' => $submission->journal->slug, 'article' => $submission->seq_id ?? $submission->id])
                : '');
        $doiUrl = e($rawDoiUrl);

        // Title from currentPublication with fallback, HTML-escaped
        $title = e((string) ($submission->currentPublication?->title ?? $submission->title ?? ''));

        $pub = $submission->currentPublication;
        $authorCollection = ($pub && $pub->authors && $pub->authors->isNotEmpty())
            ? $pub->authors
            : ($submission->authors ?? collect());

        // Full names (HTML-escaped, preserving intentional casing like "van der Berg")
        $authorsFullRaw = $authorCollection->map(function ($a): string {
            $first = trim((string) ($a->given_name ?: ($a->first_name ?? '')));
            $last = trim((string) ($a->family_name ?: ($a->last_name ?? '')));
            $full = trim("{$first} {$last}");

            return $full !== '' ? $full : trim((string) ($a->preferred_public_name ?: ($a->name ?? '')));
        })->filter()->values();

        $authorsFull = $authorsFullRaw->map(static fn (string $n): string => e($n))->implode(', ');
        $authorsAbnt = $authorsFullRaw->map(static fn (string $n): string => e(mb_strtoupper($n, 'UTF-8')))->implode(', ');

        // IEEE format: F. M. Last (HTML-escaped)
        $authorsIEEE = $authorCollection->map(function ($a): string {
            $first = trim((string) ($a->given_name ?: ($a->first_name ?? '')));
            $last = trim((string) ($a->family_name ?: ($a->last_name ?? '')));
            $initials = $this->formatInitials($first);
            $formatted = trim(($initials !== '' ? "{$initials} " : '') . $last);

            return $formatted !== '' ? e($formatted) : e(trim((string) ($a->preferred_public_name ?: ($a->name ?? ''))));
        })->filter()->implode(', ');

        $authorsAPA = $this->getAuthorsAPA($submission);
        $doiSuffix = $doiUrl !== '' ? " {$doiUrl}" : '';

        return [
            'APA' => "{$authorsAPA} ({$year}). {$title}. <em>{$journal}</em>" . ($volume ? ", {$volume}" : '') . ($number ? "({$number})" : '') . ($pages ? ", {$pages}" : '') . ".{$doiSuffix}",
            'ACM' => "{$authorsFull}. {$year}. {$title}. <em>{$journal}</em>" . ($volume ? ", {$volume}" : '') . ($number ? ", {$number}" : '') . ($pages ? ", {$pages}" : '') . '.' . ($doiUrl !== '' ? " DOI: {$doiUrl}" : ''),
            'ACS' => "{$authorsFull}. {$title}. <em>{$journal}</em> {$year}" . ($volume ? ", {$volume}" : '') . ($number ? "({$number})" : '') . ($pages ? ", {$pages}" : '') . ".{$doiSuffix}",
            'ABNT' => "{$authorsAbnt}. {$title}. {$journal}, {$year}." . ($volume ? " v. {$volume}" : '') . ($number ? ", n. {$number}" : '') . ($pages ? ", p. {$pages}" : '') . '.' . ($doiUrl !== '' ? " Disponível em: {$doiUrl}" : ''),
            'Chicago' => "{$authorsFull}. {$year}. &quot;{$title}.&quot; <em>{$journal}</em>" . ($volume ? " {$volume}" : '') . ($number ? ", no. {$number}" : '') . ($pages ? ": {$pages}" : '') . ".{$doiSuffix}",
            'Harvard' => "{$authorsFull} ({$year}) &#039;{$title}&#039;, <em>{$journal}</em>" . ($volume ? ", vol. {$volume}" : '') . ($number ? ", no. {$number}" : '') . ($pages ? ", pp. {$pages}" : '') . '.' . ($doiUrl !== '' ? " Available at: {$doiUrl}" : ''),
            'IEEE' => "{$authorsIEEE}, &quot;{$title},&quot; <em>{$journal}</em>" . ($volume ? ", vol. {$volume}" : '') . ($number ? ", no. {$number}" : '') . ($pages ? ", pp. {$pages}" : '') . ", {$year}.{$doiSuffix}",
            'MLA' => "{$authorsFull}. &quot;{$title}.&quot; <em>{$journal}</em>" . ($volume ? ", vol. {$volume}" : '') . ($number ? ", no. {$number}" : '') . ", {$year}" . ($pages ? ", pp. {$pages}" : '') . ".{$doiSuffix}",
            'Turabian' => "{$authorsFull}. &quot;{$title}.&quot; {$journal}" . ($volume ? " {$volume}" : '') . ($number ? ", no. {$number}" : '') . " ({$year})" . ($pages ? ": {$pages}" : '') . ".{$doiSuffix}",
            'Vancouver' => "{$authorsIEEE}. {$title}. {$journal}. {$year}" . ($volume ? ";{$volume}" : '') . ($number ? "({$number})" : '') . ($pages ? ":{$pages}" : '') . ".{$doiSuffix}",
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

