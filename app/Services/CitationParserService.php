<?php

namespace App\Services;

/**
 * Service for parsing raw citation text into structured fields.
 *
 * Supports APA, Vancouver, IEEE, and similar formats commonly used in academic journals.
 * Returns a structured array with extracted DOI, title, authors, year, source, volume, issue, pages, etc.
 *
 * This service is used by ProcessCitationsJob to populate the citations table.
 */
class CitationParserService
{
    /**
     * Parse a single raw reference string into structured fields.
     *
     * @param  string $rawRef A single reference line (APA, Vancouver, IEEE, etc.)
     * @return array{
     *     doi: ?string,
     *     title: ?string,
     *     authors: ?array,
     *     year: ?int,
     *     source: ?string,
     *     volume: ?string,
     *     issue: ?string,
     *     first_page: ?string,
     *     last_page: ?string,
     *     url: ?string,
     *     is_structured: bool
     * }
     */
    public static function parse(string $rawRef): array
    {
        $rawRef = trim($rawRef);

        $result = [
            'doi'           => null,
            'title'         => null,
            'authors'       => null,
            'year'          => null,
            'source'        => null,
            'volume'        => null,
            'issue'         => null,
            'first_page'    => null,
            'last_page'     => null,
            'url'           => null,
            'is_structured' => false,
        ];

        if (empty($rawRef) || mb_strlen($rawRef) < 10) {
            return $result;
        }

        // ── Normalization ──────────────────────────────────────────────
        // 1. Whitespace: normalize non-breaking space (U+00A0) and multiple spaces
        $cleanRef = preg_replace('/[\x{00A0}\s]+/u', ' ', $rawRef);

        // 2. IEEE / Vancouver leading numbering: "[1]", "(1)", "1.", "1. "
        $cleanRef = preg_replace('/^\s*(?:\[\d+\]|\(\d+\)|\d+\.)\s*/u', '', $cleanRef);

        // 3. Dashes: em-dash (—, \x{2014}), en-dash (–, \x{2013}), figure dash (‒, \x{2012}) to hyphen '-'
        $cleanRef = preg_replace('/[\x{2012}\x{2013}\x{2014}\x{2015}]/u', '-', $cleanRef);

        // 4. Quotes: normalize curly/smart quotes to straight quotes
        $cleanRef = str_replace(['“', '”', '„', '»', '«'], '"', $cleanRef);
        $cleanRef = str_replace(['‘', '’', '‚', '‹', '›', '`', 'ʻ', 'ʼ'], "'", $cleanRef);

        $fieldsFound = 0;

        // ── 1. DOI ─────────────────────────────────────────────────────
        if (preg_match('/\bhttps?:\/\/(?:dx\.)?doi\.org\/(10\.\d{4,}\/[^\s\]>,;]+)/i', $cleanRef, $m)) {
            $result['doi'] = rtrim($m[1], '.,;)"\'');
            $fieldsFound++;
        } elseif (preg_match('/\b(?:doi:\s*|doi\s+)?(10\.\d{4,}\/[^\s\]>,;]+)/i', $cleanRef, $m)) {
            $result['doi'] = rtrim($m[1], '.,;)"\'');
            $fieldsFound++;
        }

        // ── 2. URL (non-DOI) ───────────────────────────────────────────
        if (preg_match('/\bhttps?:\/\/(?!(?:dx\.)?doi\.org)[^\s\]>,;]+/i', $cleanRef, $m)) {
            $result['url'] = rtrim($m[0], '.,;)"\'');
        }

        // ── 3. Year ────────────────────────────────────────────────────
        $year = null;
        $isApaYear = false;
        $isVancouverYear = false;

        // APA style: "(2023)"
        if (preg_match('/\((\d{4})[a-z]?\)/', $cleanRef, $m)) {
            $year = (int) $m[1];
            $isApaYear = true;
        }
        // Vancouver style: "2023;" or "2023 Oct;" or ". 2023;"
        elseif (preg_match('/(?:[\.\s;]|^)(19\d{2}|20\d{2})\s*(?:[A-Za-z]{3,9}\s*(?:\d{1,2})?)?\s*;/u', $cleanRef, $m)) {
            $year = (int) $m[1];
            $isVancouverYear = true;
        }
        // IEEE style: ", 2023." or ", 2023," or "pp. 1-10, 2023"
        elseif (preg_match('/(?:,\s*|\.\s*)(?:[A-Za-z]+\.?\s+)?(19\d{2}|20\d{2})[a-z]?(?:\s*,|\s*\.|\s*;|\s*$)/u', $cleanRef, $m)) {
            $year = (int) $m[1];
        }
        // General fallback: 4-digit year bounded by word boundaries
        elseif (preg_match('/\b(19\d{2}|20\d{2})\b/', $cleanRef, $m)) {
            $year = (int) $m[1];
        }
        // IEEE/Vancouver fallback: year at end of citation without parentheses
        // e.g., "... pp. 12-20, 2023." or "... 2023;10(2):45-50."
        if (!$year && preg_match('/(?:,\s*|\.\s+|;\s*)(\d{4})(?:\s*[.;]?\s*$)/', $rawRef, $m)) {
            $year = (int) $m[1];
            if ($year >= 1800 && $year <= 2100) {
                $result['year'] = $year;
                $fieldsFound++;
            }
        }

        if ($year && $year >= 1800 && $year <= 2100) {
            $result['year'] = $year;
            $fieldsFound++;
        }

        // ── 4. Title, Authors, and Source (Multi-Style) ────────────────

        // Case A: IEEE style (Title enclosed in quotation marks: double or single)
        if (preg_match('/"([^"]{5,})"/u', $cleanRef, $tm) || preg_match('/(?:\s|^)\'([^\']{5,})\'/u', $cleanRef, $tm)) {
            $title = trim($tm[1], " .,;\"'");
            if (mb_strlen($title) > 5) {
                $result['title'] = $title;
                $fieldsFound++;
            }

            // Authors are text before the opening quote
            if (preg_match('/^(.+?)\s*["\']/u', $cleanRef, $am)) {
                $rawAuthors = trim($am[1], " ,;\t&");
                $parsedAuthors = self::extractAuthorList($rawAuthors);
                if (!empty($parsedAuthors)) {
                    $result['authors'] = $parsedAuthors;
                    $fieldsFound++;
                }
            }

            // Source is text after the closing quote and before vol/issue/pp/year/doi
            if (preg_match('/["\'][,\.]?\s*([^,]+?)(?:,\s*[Vv]ol|\s+[Vv]ol|,\s*pp|\s+pp|,\s*no|\s*no|,\s*\d{4}|$)/iu', $cleanRef, $sm)) {
                $source = trim($sm[1], " ,.\t");
                if (mb_strlen($source) > 2 && mb_strlen($source) < 300) {
                    $result['source'] = $source;
                    $fieldsFound++;
                }
            }
        }
        // Case B: APA style (Year in parentheses, e.g. "(2023). ")
        elseif ($isApaYear && $year) {
            // Authors: everything before the first "(YEAR)"
            if (preg_match('/^(.+?)\s*\(\d{4}[a-z]?\)/u', $cleanRef, $am)) {
                $rawAuthors = trim($am[1], " ,;\t&");
                $parsedAuthors = self::extractAuthorList($rawAuthors);
                if (!empty($parsedAuthors)) {
                    $result['authors'] = $parsedAuthors;
                    $fieldsFound++;
                }
            }

            // Title: text immediately after "(YEAR)" up to the next sentence boundary + capital letter
            $afterYear = preg_replace('/^.*?\(\d{4}[a-z]?\)[\.\s,]+\s*/u', '', $cleanRef, 1);
            if ($afterYear && $afterYear !== $cleanRef) {
                if (preg_match('/^(.+?)(?:[\.?!]\s+[A-Z\p{Lu}]|[\.?!]$)/u', $afterYear, $tm)) {
                    $title = trim($tm[1]);
                    $title = preg_replace('/\s+https?:\/\/\S+/i', '', $title);
                    $title = trim($title, " .?!,\t");
                    if (mb_strlen($title) > 8) {
                        $result['title'] = $title;
                        $fieldsFound++;
                    }
                }
            }

            // Source: text between title terminal punctuation and volume/issue/doi
            if ($result['title']) {
                $titleEscaped = preg_quote($result['title'], '/');
                $pattern = '/' . $titleEscaped . '[\.?!,]*\s+(.+?)(?:,\s*\d+\(|\s+\d+\(|,\s*[Vv]ol|\bhttps?:\/\/|$)/u';
                if (preg_match($pattern, $cleanRef, $sm)) {
                    $source = trim($sm[1], " ,.\t");
                    if (mb_strlen($source) > 3 && mb_strlen($source) < 300) {
                        $result['source'] = $source;
                        $fieldsFound++;
                    }
                }
            }
        }
        // Case C: Vancouver style (Year followed by semicolon, e.g. "2023;12(3):45-52")
        elseif ($isVancouverYear && $year) {
            // Text before the year
            $yearPos = mb_strpos($cleanRef, (string)$year);
            $beforeYear = $yearPos !== false ? mb_substr($cleanRef, 0, $yearPos) : '';
            $beforeYear = trim($beforeYear, " .\t;");

            // Smart split: split by sentence boundary, NOT by author initials (e.g. "Doe J., Smith B.")
            $segments = array_values(array_filter(
                preg_split('/(?<=[.?!])\s+(?!(?:[A-Z]\.|\&|,))(?=[A-Z])/u', $beforeYear),
                fn($s) => mb_strlen(trim($s, " .\t;")) > 2
            ));

            if (count($segments) >= 3) {
                // Segment 0: Authors, Last segment: Journal, Middle: Title
                $rawAuthors = array_shift($segments);
                $rawSource = array_pop($segments);
                $rawTitle = implode(' ', $segments);

                $parsedAuthors = self::extractAuthorList($rawAuthors);
                if (!empty($parsedAuthors)) {
                    $result['authors'] = $parsedAuthors;
                    $fieldsFound++;
                }
                $result['title'] = trim($rawTitle, " .?!\t");
                $fieldsFound++;
                $result['source'] = trim($rawSource, " .?!\t");
                $fieldsFound++;
            } elseif (count($segments) === 2) {
                // Segment 0: Authors, Segment 1: Title
                $parsedAuthors = self::extractAuthorList($segments[0]);
                if (!empty($parsedAuthors)) {
                    $result['authors'] = $parsedAuthors;
                    $fieldsFound++;
                }
                $result['title'] = trim($segments[1], " .?!\t");
                $fieldsFound++;
            }
        }

        // ── 5. Volume & Issue ──────────────────────────────────────────
        // Pattern 1: "3(2)" or ";3(2):"
        if (preg_match('/(?:[,\s;]|^)(\d+)\((\d+)\)/', $cleanRef, $m)) {
            $result['volume'] = $m[1];
            $result['issue'] = $m[2];
            $fieldsFound++;
        }
        // Pattern 2: "Vol. 3, No. 2" or "vol. 3, no. 2"
        elseif (preg_match('/[Vv]ol\.?\s*(\d+)[,;\s]+[Nn]o\.?\s*(\d+)/i', $cleanRef, $m)) {
            $result['volume'] = $m[1];
            $result['issue'] = $m[2];
            $fieldsFound++;
        }
        // Pattern 3: Volume only
        elseif (preg_match('/[Vv]ol\.?\s*(\d+)/i', $cleanRef, $m)) {
            $result['volume'] = $m[1];
            $fieldsFound++;
        }

        // ── 6. Pages ───────────────────────────────────────────────────
        // Pattern 1: ":20-29" or ": 20-29" (Vancouver) or "pp. 20-29" (IEEE)
        if (preg_match('/(?::|\bpp?\.?\s*)[,\s]*(\d+)\s*-\s*(\d+)/i', $cleanRef, $m)) {
            $result['first_page'] = $m[1];
            $result['last_page'] = $m[2];
            $fieldsFound++;
        }
        // Pattern 2: ", 20-29" (APA)
        elseif (preg_match('/[,\s](\d+)\s*-\s*(\d+)(?:[.,\s]|$)/', $cleanRef, $m)) {
            $result['first_page'] = $m[1];
            $result['last_page'] = $m[2];
            $fieldsFound++;
        }

        // ── Structured threshold ───────────────────────────────────────
        // Consider structured if at least 3 fields were successfully extracted
        $result['is_structured'] = ($fieldsFound >= 3);

        return $result;
    }

    /**
     * Extract author names list from a raw authors substring.
     *
     * @param string $rawAuthors
     * @return array<string>
     */
    protected static function extractAuthorList(string $rawAuthors): array
    {
        $rawAuthors = trim($rawAuthors, " ,;\t&");
        if (empty($rawAuthors) || mb_strlen($rawAuthors) < 3) {
            return [];
        }

        // 1. Semicolon split first (formal multiple authors in Vancouver or metadata)
        $semiParts = preg_split('/;\s+/', $rawAuthors, -1, PREG_SPLIT_NO_EMPTY);
        if (count($semiParts) > 1) {
            $authorList = $semiParts;
        } else {
            // 2. APA pattern: "Surname, I. A., Surname2, B., & Surname3, C."
            // Matches each author matching "[Surname], [Initials]"
            preg_match_all(
                '/([^\s,][^,]+?,\s+[A-Z]\.(?:\s*[A-Z]\.)*)(?:,\s*|\s+(?:and|&)\s+|$)/u',
                $rawAuthors,
                $am
            );
            if (!empty($am[1]) && count($am[1]) > 0) {
                $authorList = $am[1];
            } else {
                // 3. IEEE / Vancouver or First-Last pattern: "E. Evaluslana, C. Wongso, and D. Smith"
                // Replace " and " or " & " with comma, then split by comma
                $normalized = preg_replace('/\s+(?:and|&)\s+/i', ', ', $rawAuthors);
                $commaParts = preg_split('/,\s*/', $normalized, -1, PREG_SPLIT_NO_EMPTY);
                $authorList = !empty($commaParts) ? $commaParts : [$rawAuthors];
            }
        }

        $cleanAuthors = [];
        foreach ($authorList as $author) {
            $author = trim($author, " ,;\t&");
            if (mb_strlen($author) > 2) {
                $cleanAuthors[] = $author;
            }
        }

        return $cleanAuthors;
    }

    /**
     * Parse the full references field (newline-separated) into an array of structured results.
     *
     * @param  string $rawReferences  The entire publications.references TEXT blob
     * @return array<int, array>       Array of structured parse results, 0-indexed
     */
    public static function parseAll(string $rawReferences): array
    {
        if (empty(trim($rawReferences))) {
            return [];
        }

        $lines = array_values(array_filter(
            array_map('trim', explode("\n", $rawReferences)),
            fn($line) => mb_strlen($line) > 5
        ));

        $results = [];
        foreach ($lines as $index => $line) {
            $results[] = array_merge(
                ['seq' => $index + 1, 'raw_citation' => $line],
                self::parse($line)
            );
        }

        return $results;
    }

    /**
     * Convert a parsed citation array into a Google Scholar-compatible
     * citation_reference key=value meta content string.
     *
     * Replaces the duplicated logic that was in CitationService::parseReferenceToScholarMeta().
     *
     * @param  string $rawRef  A single reference line
     * @return string          key=value string OR original plain text as fallback
     */
    public static function toScholarMeta(string $rawRef): string
    {
        $rawRef = trim($rawRef);
        if (empty($rawRef)) {
            return '';
        }

        $parsed = self::parse($rawRef);
        $fields = [];

        if ($parsed['doi']) {
            $fields[] = 'citation_doi=' . $parsed['doi'];
        }
        if ($parsed['title']) {
            $fields[] = 'citation_title=' . $parsed['title'];
        }
        if (!empty($parsed['authors'])) {
            foreach ($parsed['authors'] as $author) {
                $fields[] = 'citation_author=' . $author;
            }
        }
        if ($parsed['year']) {
            $fields[] = 'citation_publication_date=' . $parsed['year'];
        }
        if ($parsed['source']) {
            $fields[] = 'citation_journal_title=' . $parsed['source'];
        }
        if ($parsed['volume']) {
            $fields[] = 'citation_volume=' . $parsed['volume'];
        }
        if ($parsed['issue']) {
            $fields[] = 'citation_issue=' . $parsed['issue'];
        }
        if ($parsed['first_page']) {
            $fields[] = 'citation_firstpage=' . $parsed['first_page'];
        }
        if ($parsed['last_page']) {
            $fields[] = 'citation_lastpage=' . $parsed['last_page'];
        }

        // Fallback: if less than 2 structured fields, return raw text
        // so Scholar can still attempt its own NLP parsing (no regression).
        if (count($fields) < 2) {
            return $rawRef;
        }

        return implode('; ', $fields) . ';';
    }
}
