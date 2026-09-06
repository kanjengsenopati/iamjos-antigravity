<?php

namespace App\Services;

/**
 * Service for parsing raw citation text into structured fields.
 *
 * Supports APA, Vancouver, and similar formats commonly used in academic journals.
 * Returns a structured array with extracted DOI, title, authors, year, etc.
 *
 * This service is used by ProcessCitationsJob to populate the citations table.
 */
class CitationParserService
{
    /**
     * Parse a single raw reference string into structured fields.
     *
     * @param  string $rawRef A single reference line (APA or similar format)
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

        $fieldsFound = 0;

        // ── 1. DOI ─────────────────────────────────────────────────────
        if (preg_match('/\bhttps?:\/\/(?:dx\.)?doi\.org\/(10\.\d{4,}\/[^\s\]>,;]+)/i', $rawRef, $m)) {
            $result['doi'] = rtrim($m[1], '.,;)"\'');
            $fieldsFound++;
        } elseif (preg_match('/\b(10\.\d{4,}\/[^\s\]>,;]+)/i', $rawRef, $m)) {
            $result['doi'] = rtrim($m[1], '.,;)"\'');
            $fieldsFound++;
        }

        // ── 2. URL (non-DOI) ───────────────────────────────────────────
        if (preg_match('/\bhttps?:\/\/(?!(?:dx\.)?doi\.org)[^\s\]>,;]+/i', $rawRef, $m)) {
            $result['url'] = rtrim($m[0], '.,;)"\'');
        }

        // ── 3. Year ────────────────────────────────────────────────────
        $year = null;
        if (preg_match('/\((\d{4})[a-z]?\)/', $rawRef, $m)) {
            $year = (int) $m[1];
            if ($year >= 1800 && $year <= 2100) {
                $result['year'] = $year;
                $fieldsFound++;
            }
        }

        // ── 4. Authors ─────────────────────────────────────────────────
        // Everything before the first "(YEAR)" — APA pattern
        if ($year && preg_match('/^(.+?)\s*\(\d{4}[a-z]?\)/', $rawRef, $m)) {
            $rawAuthors = trim($m[1], " ,.\t&");
            if ($rawAuthors && mb_strlen($rawAuthors) > 3) {
                $authorList = [];

                // Try semicolon split first (Vancouver style)
                $semiParts = preg_split('/;\s+/', $rawAuthors, -1, PREG_SPLIT_NO_EMPTY);

                if (count($semiParts) > 1) {
                    $authorList = $semiParts;
                } else {
                    // APA pattern: "Surname, I., Surname2, I2."
                    preg_match_all(
                        '/[^\s,][^,]+,\s+[A-Z](?:\.[A-Z])*\.(?=\s|,|&|$)/u',
                        $rawAuthors,
                        $am
                    );
                    if (!empty($am[0])) {
                        $authorList = $am[0];
                    } else {
                        // Fallback: split on ", " and group pairs, or just use as-is
                        $authorList = [$rawAuthors];
                    }
                }

                $cleanAuthors = [];
                foreach ($authorList as $author) {
                    $author = trim($author, " ,.\t&");
                    if (mb_strlen($author) > 2) {
                        $cleanAuthors[] = $author;
                    }
                }

                if (!empty($cleanAuthors)) {
                    $result['authors'] = $cleanAuthors;
                    $fieldsFound++;
                }
            }
        }

        // ── 5. Title ───────────────────────────────────────────────────
        // APA: text immediately after "(YEAR). " up to the next period+capital
        if ($year) {
            $afterYear = preg_replace('/^.*?\(\d{4}[a-z]?\)\.\s*/u', '', $rawRef, 1);
            if ($afterYear && $afterYear !== $rawRef) {
                // Title ends at ". [Capital letter]" which signals journal name
                if (preg_match('/^(.+?)(?:\.\s+[A-Z\p{Lu}]|\.$)/u', $afterYear, $tm)) {
                    $title = trim($tm[1]);
                    // Remove trailing URLs
                    $title = preg_replace('/\s+https?:\/\/\S+/i', '', $title);
                    $title = trim($title, " .\t");
                    if (mb_strlen($title) > 8) {
                        $result['title'] = $title;
                        $fieldsFound++;
                    }
                }
            }
        }

        // ── 6. Source (Journal Name) ───────────────────────────────────
        // After title ends with ". ", the journal name usually follows in italics
        // In plain text: it's the text between the title's terminal period and the volume/issue
        if ($year && $result['title']) {
            $titleEscaped = preg_quote($result['title'], '/');
            $pattern = '/' . $titleEscaped . '\.\s+(.+?)(?:,\s*\d+\(|\s+\d+\(|,\s*[Vv]ol|$)/u';
            if (preg_match($pattern, $rawRef, $sm)) {
                $source = trim($sm[1], " ,.\t");
                if (mb_strlen($source) > 3 && mb_strlen($source) < 300) {
                    $result['source'] = $source;
                    $fieldsFound++;
                }
            }
        }

        // ── 7. Volume & Issue ──────────────────────────────────────────
        if (preg_match('/[,\s](\d+)\((\d+)\)/', $rawRef, $m)) {
            $result['volume'] = $m[1];
            $result['issue'] = $m[2];
            $fieldsFound++;
        } elseif (preg_match('/[Vv]ol\.?\s*(\d+)[,;\s]+[Nn]o\.?\s*(\d+)/', $rawRef, $m)) {
            $result['volume'] = $m[1];
            $result['issue'] = $m[2];
            $fieldsFound++;
        }

        // ── 8. Pages ───────────────────────────────────────────────────
        if (preg_match('/[,\s](\d+)\s*[–\-]\s*(\d+)(?:[.,\s]|$)/', $rawRef, $m)) {
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
}
