<?php

namespace App\Services;

use App\Models\Submission;
use DOMDocument;
use DOMElement;
use InvalidArgumentException;

class JatsXmlService
{
    private DOMDocument $dom;

    /**
     * Generate JATS 1.3 XML dari data submission.
     * @throws InvalidArgumentException jika submission tidak memiliki publication atau journal
     */
    public function generate(Submission $submission): string
    {
        $pub     = $submission->currentPublication;
        $journal = $submission->journal;

        if (!$pub) {
            throw new InvalidArgumentException("Submission {$submission->id} has no publication");
        }
        if (!$journal) {
            throw new InvalidArgumentException("Submission {$submission->id} has no journal");
        }

        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;

        // Root element <article>
        $article = $this->dom->createElement('article');
        $article->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        $article->setAttribute('xmlns:ali', 'http://www.niso.org/schemas/ali/1.0/');
        $article->setAttribute('article-type', $this->sectionToArticleType($submission->section?->title));
        $article->setAttribute('xml:lang', $this->toBcp47($submission->locale ?? 'en'));
        $article->setAttribute('dtd-version', '1.3');
        $this->dom->appendChild($article);

        // <front>
        $article->appendChild($this->buildFront($submission));

        // <body/> — kosong, konten ada di PDF galley
        $article->appendChild($this->dom->createElement('body'));

        // <back> — opsional, hanya jika ada references
        $back = $this->buildBack($submission);
        if ($back) {
            $article->appendChild($back);
        }

        return $this->dom->saveXML();
    }

    // ─── FRONT ────────────────────────────────────────────────────────────────

    private function buildFront(Submission $submission): DOMElement
    {
        $front = $this->dom->createElement('front');
        $front->appendChild($this->buildJournalMeta($submission));
        $front->appendChild($this->buildArticleMeta($submission));
        return $front;
    }

    private function buildJournalMeta(Submission $submission): DOMElement
    {
        $journal = $submission->journal;
        $meta    = $this->dom->createElement('journal-meta');

        // <journal-id>
        $jid = $this->dom->createElement('journal-id', $journal->slug);
        $jid->setAttribute('journal-id-type', 'publisher-id');
        $meta->appendChild($jid);

        // <journal-title-group>
        $titleGroup = $this->dom->createElement('journal-title-group');
        $titleGroup->appendChild($this->dom->createElement('journal-title', $this->esc($journal->name)));
        if (!empty($journal->abbreviation)) {
            $titleGroup->appendChild($this->dom->createElement('abbrev-journal-title', $this->esc($journal->abbreviation)));
        }
        $meta->appendChild($titleGroup);

        // <issn>
        if (!empty($journal->issn_print)) {
            $issn = $this->dom->createElement('issn', $journal->issn_print);
            $issn->setAttribute('pub-type', 'ppub');
            $meta->appendChild($issn);
        }
        if (!empty($journal->issn_online)) {
            $issn = $this->dom->createElement('issn', $journal->issn_online);
            $issn->setAttribute('pub-type', 'epub');
            $meta->appendChild($issn);
        }

        // <publisher>
        $publisher = $this->dom->createElement('publisher');
        $publisher->appendChild($this->dom->createElement('publisher-name', $this->esc($journal->publisher ?? $journal->name)));
        $meta->appendChild($publisher);

        return $meta;
    }

    private function buildArticleMeta(Submission $submission): DOMElement
    {
        $pub     = $submission->currentPublication;
        $journal = $submission->journal;
        $issue   = $submission->issue;
        $meta    = $this->dom->createElement('article-meta');

        // <article-id pub-id-type="doi">
        if (!empty($pub->doi)) {
            $doi = $this->dom->createElement('article-id', $this->esc($pub->doi));
            $doi->setAttribute('pub-id-type', 'doi');
            $meta->appendChild($doi);
        }

        // <title-group>
        $titleGroup = $this->dom->createElement('title-group');
        $titleGroup->appendChild($this->dom->createElement('article-title', $this->esc($pub->title ?? $submission->title)));
        if (!empty($pub->subtitle)) {
            $titleGroup->appendChild($this->dom->createElement('subtitle', $this->esc($pub->subtitle)));
        }
        $meta->appendChild($titleGroup);

        // <contrib-group>
        $meta->appendChild($this->buildContribGroup($submission));

        // <pub-date>
        $pubDate = $pub->date_published ?? $issue?->published_at ?? null;
        if ($pubDate) {
            $pubDateEl = $this->dom->createElement('pub-date');
            $pubDateEl->setAttribute('publication-format', 'electronic');
            $pubDateEl->appendChild($this->dom->createElement('year', $pubDate->format('Y')));
            $pubDateEl->appendChild($this->dom->createElement('month', $pubDate->format('m')));
            $pubDateEl->appendChild($this->dom->createElement('day', $pubDate->format('d')));
            $meta->appendChild($pubDateEl);
        }

        // <volume> <issue>
        if (!empty($issue?->volume)) {
            $meta->appendChild($this->dom->createElement('volume', $this->esc($issue->volume)));
        }
        if (!empty($issue?->number)) {
            $meta->appendChild($this->dom->createElement('issue', $this->esc($issue->number)));
        }

        // <fpage> <lpage>
        if (!empty($pub->pages)) {
            $pages = explode('-', $pub->pages, 2);
            $meta->appendChild($this->dom->createElement('fpage', trim($pages[0])));
            if (isset($pages[1]) && trim($pages[1]) !== '') {
                $meta->appendChild($this->dom->createElement('lpage', trim($pages[1])));
            }
        }

        // <permissions>
        $meta->appendChild($this->buildPermissions($submission));

        // <abstract>
        if (!empty($pub->abstract)) {
            $abstract = $this->dom->createElement('abstract');
            $p        = $this->dom->createElement('p', $this->esc(trim(strip_tags($pub->abstract))));
            $abstract->appendChild($p);
            $meta->appendChild($abstract);
        }

        // <kwd-group>
        $keywords = $this->parseKeywords($pub->keywords ?? null);
        if (!empty($keywords)) {
            $kwdGroup = $this->dom->createElement('kwd-group');
            $kwdGroup->setAttribute('kwd-group-type', 'author-keywords');
            foreach ($keywords as $kw) {
                $kwdGroup->appendChild($this->dom->createElement('kwd', $this->esc($kw)));
            }
            $meta->appendChild($kwdGroup);
        }

        // <funding-group> — informasi pendanaan (Crossref Funder Registry / JATS)
        $fundingInfo = $pub->funding_info ?? [];
        if (!empty($fundingInfo) && is_array($fundingInfo)) {
            foreach ($fundingInfo as $funder) {
                if (empty($funder['funder_name'])) {
                    continue;
                }
                $fundingGroup = $this->dom->createElement('funding-group');
                $awardGroup   = $this->dom->createElement('award-group');

                $fundingSource = $this->dom->createElement('funding-source');
                $fundingSource->appendChild($this->dom->createElement('institution', $this->esc($funder['funder_name'])));
                if (!empty($funder['funder_doi'])) {
                    $instWrap = $this->dom->createElement('institution-wrap');
                    $instId   = $this->dom->createElement('institution-id', $this->esc($funder['funder_doi']));
                    $instId->setAttribute('institution-id-type', 'doi');
                    $instWrap->appendChild($instId);
                    $fundingSource->appendChild($instWrap);
                }
                $awardGroup->appendChild($fundingSource);

                if (!empty($funder['award_number'])) {
                    $awardGroup->appendChild($this->dom->createElement('award-id', $this->esc($funder['award_number'])));
                }

                $fundingGroup->appendChild($awardGroup);
                $meta->appendChild($fundingGroup);
            }
        }

        return $meta;
    }

    private function buildContribGroup(Submission $submission): DOMElement
    {
        $pub          = $submission->currentPublication;
        $contribGroup = $this->dom->createElement('contrib-group');

        $authors = $pub?->authors ?? collect();
        if ($authors->isEmpty()) {
            return $contribGroup;
        }

        foreach ($authors->sortBy('sort_order') as $author) {
            $contrib = $this->dom->createElement('contrib');
            $contrib->setAttribute('contrib-type', 'author');
            if ($author->is_corresponding) {
                $contrib->setAttribute('corresp', 'yes');
            }

            // <name>
            $name = $this->dom->createElement('name');
            $name->appendChild($this->dom->createElement('surname', $this->esc($author->last_name ?? '')));
            $name->appendChild($this->dom->createElement('given-names', $this->esc($author->first_name ?? '')));
            $contrib->appendChild($name);

            // <contrib-id contrib-id-type="orcid">
            if (!empty($author->orcid)) {
                $orcidEl = $this->dom->createElement('contrib-id', 'https://orcid.org/' . $this->cleanOrcid($author->orcid));
                $orcidEl->setAttribute('contrib-id-type', 'orcid');
                $contrib->appendChild($orcidEl);
            }

            // <aff>
            if (!empty($author->affiliation)) {
                $contrib->appendChild($this->dom->createElement('aff', $this->esc($author->affiliation)));
            }

            $contribGroup->appendChild($contrib);
        }

        return $contribGroup;
    }

    private function buildPermissions(Submission $submission): DOMElement
    {
        $pub     = $submission->currentPublication;
        $journal = $submission->journal;
        $issue   = $submission->issue;

        $year   = $pub->copyright_year ?? $issue?->year ?? (int) date('Y');
        $holder = $pub->copyright_holder ?? $journal->publisher ?? $journal->name;

        $permissions = $this->dom->createElement('permissions');
        $permissions->appendChild($this->dom->createElement(
            'copyright-statement',
            $this->esc("Copyright (c) {$year} {$holder}")
        ));
        $permissions->appendChild($this->dom->createElement('copyright-year', (string) $year));
        $permissions->appendChild($this->dom->createElement('copyright-holder', $this->esc($holder)));

        if (!empty($pub->license_url)) {
            $license    = $this->dom->createElement('license');
            $licenseRef = $this->dom->createElementNS('http://www.niso.org/schemas/ali/1.0/', 'ali:license_ref', $this->esc($pub->license_url));
            $license->appendChild($licenseRef);
            $permissions->appendChild($license);
        }

        return $permissions;
    }

    // ─── BACK ─────────────────────────────────────────────────────────────────

    private function buildBack(Submission $submission): ?DOMElement
    {
        $pub  = $submission->currentPublication;
        $refs = $pub?->references ?? null;

        if (empty($refs)) {
            return null;
        }

        $back    = $this->dom->createElement('back');
        $refList = $this->buildRefList($refs);
        $back->appendChild($refList);
        return $back;
    }

    private function buildRefList(string $references): DOMElement
    {
        $refList = $this->dom->createElement('ref-list');
        $lines   = explode("\n", $references);
        $n       = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) < 5) {
                continue;
            }
            $n++;
            $ref = $this->dom->createElement('ref');
            $ref->setAttribute('id', "ref-{$n}");

            $mixedCitation = $this->dom->createElement('mixed-citation');
            $mixedCitation->appendChild($this->dom->createTextNode($line));

            // Ekstrak DOI jika ada
            $doi = $this->extractDoi($line);
            if ($doi) {
                $pubId = $this->dom->createElement('pub-id', $this->esc($doi));
                $pubId->setAttribute('pub-id-type', 'doi');
                $mixedCitation->appendChild($pubId);
            }

            $ref->appendChild($mixedCitation);
            $refList->appendChild($ref);
        }

        return $refList;
    }

    // ─── HELPERS ──────────────────────────────────────────────────────────────

    /**
     * Konversi locale OJS ke BCP47 (id_ID → id, en_US → en).
     */
    private function toBcp47(?string $locale): string
    {
        if (empty($locale)) {
            return 'en';
        }
        return preg_replace('/_[A-Z]{2}$/', '', $locale) ?: 'en';
    }

    /**
     * Petakan section title ke article-type JATS.
     */
    private function sectionToArticleType(?string $sectionTitle): string
    {
        if (empty($sectionTitle)) {
            return 'research-article';
        }

        $lower = strtolower(trim($sectionTitle));

        return match (true) {
            in_array($lower, ['research article', 'original article', 'original research', 'articles']) => 'research-article',
            in_array($lower, ['review', 'review article', 'literature review'])                          => 'review-article',
            in_array($lower, ['case report', 'case study', 'case reports'])                              => 'case-report',
            $lower === 'editorial'                                                                        => 'editorial',
            in_array($lower, ['letter', 'letter to the editor', 'letters'])                              => 'letter',
            in_array($lower, ['brief report', 'brief communication', 'short communication'])             => 'brief-report',
            default                                                                                       => 'research-article',
        };
    }

    /**
     * Bersihkan ORCID dari prefix URL.
     */
    private function cleanOrcid(string $orcid): string
    {
        return preg_replace('#^https?://(www\.)?orcid\.org/#', '', trim($orcid));
    }

    /**
     * Ekstrak DOI dari teks referensi.
     */
    private function extractDoi(string $refText): ?string
    {
        if (preg_match('/\b(10\.\d{4,}\/\S+)/i', $refText, $matches)) {
            return rtrim($matches[1], '.,;)');
        }
        return null;
    }

    /**
     * Parse keywords dari berbagai format (string CSV, JSON array, iterable).
     * @return string[]
     */
    private function parseKeywords(mixed $keywords): array
    {
        if (empty($keywords)) {
            return [];
        }

        $items = [];

        if (is_string($keywords)) {
            if (str_starts_with(trim($keywords), '[')) {
                $decoded = json_decode($keywords, true);
                $items   = is_array($decoded) ? $decoded : explode(',', $keywords);
            } else {
                $items = explode(',', $keywords);
            }
        } elseif (is_iterable($keywords)) {
            $items = $keywords;
        }

        $result = [];
        foreach ($items as $item) {
            if (is_array($item)) {
                $val = $item['value'] ?? $item['content'] ?? $item['name'] ?? null;
            } elseif (is_object($item)) {
                $val = $item->content ?? $item->value ?? $item->name ?? null;
            } else {
                $val = $item;
            }

            $val = trim((string) ($val ?? ''));
            if ($val !== '') {
                $result[] = $val;
            }
        }

        return $result;
    }

    /**
     * XML-escape string untuk digunakan sebagai text content.
     * DOMDocument menangani escaping secara otomatis saat menggunakan createElement/createTextNode,
     * tapi helper ini berguna untuk nilai yang dimasukkan langsung.
     */
    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
