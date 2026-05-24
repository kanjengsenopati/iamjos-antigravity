@php
    $primaryColor = $settings['primary_color'] ?? '#0369a1';
    $secondaryColor = $settings['secondary_color'] ?? '#7c3aed';

    // Get PDF Galley for citation_pdf_url (Google Scholar)
    $pdfGalley =
        $article->galleys?->where('file_type', 'application/pdf')->first() ??
        ($article->galleys?->whereIn('label', ['PDF', 'pdf'])->first() ?? $article->galleys?->first());

    // Publication date logic
    $publicationDate = $issue?->published_at ?? $article->published_at;

    // -------------------------------------------------------
    // Pre-compute shared variables used in both meta tags
    // and the references section HTML
    // -------------------------------------------------------
    $pub         = $article->currentPublication ?? $article;
    $pubTitle    = $pub->title ?? $article->title;
    $pubDate     = $pub->date_published ?? ($issue?->published_at ?? $article->published_at);
    $pubPages    = $pub->pages ?? $article->pages;
    $pubDoi      = $pub->doi ?? $article->doi;
    $pubKeywords = $pub->keywords ?? $article->keywords;
    $pubAbstract = $pub->abstract ?? $article->abstract;
    $pubAuthors  = $pub->authors ?? $article->authors;

    // BCP47 locale (id_ID → id, en_US → en)
    $rawLocale   = $article->locale ?? app()->getLocale() ?? 'en';
    $bcp47Locale = preg_replace('/_[A-Z]{2}$/', '', $rawLocale);

    // ISSN
    $issnValue = $journal->issn_online ?? $journal->issn_print ?? null;

    // Pages
    $firstPage = $lastPage = null;
    if ($pubPages) {
        $pageParts = explode('-', $pubPages, 2);
        $firstPage = trim($pageParts[0]);
        $lastPage  = isset($pageParts[1]) ? trim($pageParts[1]) : null;
    }

    // Keywords
    $processedKeywords = [];
    if ($pubKeywords) {
        $rawK = is_string($pubKeywords)
            ? (str_starts_with(trim($pubKeywords), '[') ? json_decode($pubKeywords, true) : explode(',', $pubKeywords))
            : (is_iterable($pubKeywords) ? $pubKeywords : []);
        foreach ($rawK as $k) {
            $val = is_array($k)
                ? ($k['value'] ?? ($k['content'] ?? ($k['name'] ?? null)))
                : (is_object($k) ? ($k->content ?? ($k->value ?? ($k->name ?? null))) : $k);
            if ($val) $processedKeywords[] = trim((string) $val);
        }
    }

    // Build parsed references array — split raw text by newline, strip empty lines
    // OJS uses Citation objects; we use the raw references field split by newline as equivalent
    $parsedRefs = [];
    $rawRefs = $pub->references ?? $article->references ?? null;
    if ($rawRefs) {
        $parsedRefs = array_values(array_filter(
            array_map('trim', explode("\n", $rawRefs)),
            fn($r) => strlen($r) > 5
        ));
    }

    // PDF galley URL — standard galley route (OJS-compatible path)
    $pdfGalleyUrl = null;
    if ($pdfGalley) {
        $pdfGalleyUrl = route('journal.article.galley', [
            'journal' => $journal->slug,
            'article' => $article->seq_id,
            'galley'  => $pdfGalley->seq_id ?? $pdfGalley->id,
        ]);
    }

    // Copyright
    $copyrightHolder = $pub->copyright_holder ?? ($journal->publisher ?? $journal->name);
    $copyrightYear   = $pub->copyright_year ?? ($issue->year ?? date('Y'));
    $licenseUrl      = $pub->license_url ?? ($journal->license_url ?? null);
@endphp

<x-layouts.public :journal="$journal" :settings="$settings" :title="$article->title . ' | ' . $journal->name" :article="true">



    {{-- ============================================ --}}
    {{-- GOOGLE SCHOLAR META TAGS (Keep existing) --}}
    {{-- ============================================ --}}
    {{-- ============================================ --}}
    {{-- GOOGLE SCHOLAR / HIGHWIRE PRESS META TAGS --}}
    {{-- CRITICAL for academic indexing --}}
    {{-- ============================================ --}}
    @push('meta_tags')
@php
/**
 * Google Scholar / HighWire Press + Dublin Core Meta Tags
 * Aligned with OJS 3.x GoogleScholarPlugin.php (pkp/googleScholar)
 * Reference: https://github.com/pkp/googleScholar/blob/main/GoogleScholarPlugin.php
 * Variables pre-computed in the outer @php block above.
 */
@endphp

{{-- ============================================================ --}}
{{-- GOOGLE SCHOLAR / HIGHWIRE PRESS META TAGS                    --}}
{{-- Aligned with OJS pkp/googleScholar plugin (2025)             --}}
{{-- Required: gs_meta_revision, citation_title, citation_author, --}}
{{--           citation_date, citation_journal_title              --}}
{{-- ============================================================ --}}
<meta name="gs_meta_revision" content="1.1">

{{-- Journal identification --}}
<meta name="citation_journal_title" content="{{ htmlspecialchars($journal->name) }}">
@if ($journal->abbreviation)
<meta name="citation_journal_abbrev" content="{{ htmlspecialchars($journal->abbreviation) }}">
@endif
@if ($journal->publisher)
<meta name="citation_publisher" content="{{ htmlspecialchars($journal->publisher) }}">
@endif
@php $issnValue = $journal->issn_online ?? $journal->issn_print ?? null; @endphp
@if ($issnValue)
<meta name="citation_issn" content="{{ htmlspecialchars($issnValue) }}">
@endif

{{-- Article title --}}
<meta name="citation_title" content="{{ htmlspecialchars($pubTitle) }}">

{{-- Language (BCP47 format: "id" not "id_ID") --}}
<meta name="citation_language" content="{{ $bcp47Locale }}">

{{-- Publication date — OJS uses citation_date only (not citation_publication_date) --}}
@if ($pubDate)
<meta name="citation_date" content="{{ $pubDate->format('Y/m/d') }}">
@endif

{{-- Online date — only when different from publication date --}}
@if ($article->published_at && $pubDate && $article->published_at->format('Y-m-d') !== $pubDate->format('Y-m-d'))
<meta name="citation_online_date" content="{{ $article->published_at->format('Y/m/d') }}">
@endif

{{-- Issue metadata --}}
@if ($issue)
@if ($issue->volume && $issue->getShowVolume ?? true)
<meta name="citation_volume" content="{{ htmlspecialchars($issue->volume) }}">
@endif
@if ($issue->number && $issue->getShowNumber ?? true)
<meta name="citation_issue" content="{{ htmlspecialchars($issue->number) }}">
@endif
@endif

{{-- Pages --}}
@if ($firstPage)
<meta name="citation_firstpage" content="{{ htmlspecialchars($firstPage) }}">
@endif
@if ($lastPage)
<meta name="citation_lastpage" content="{{ htmlspecialchars($lastPage) }}">
@endif

{{-- DOI --}}
@if ($pubDoi)
<meta name="citation_doi" content="{{ htmlspecialchars($pubDoi) }}">
@endif

{{-- Authors — OJS: getFullName(false, false) excludes preferred name & title/suffix --}}
{{-- Privacy: citation_author_email intentionally omitted (OJS does not include it) --}}
@foreach ($pubAuthors as $author)
@php
    // Build display name: prefer first+last, fallback to name field
    $authorName = trim(($author->first_name ?? '') . ' ' . ($author->last_name ?? ''));
    if (empty($authorName)) {
        $authorName = $author->preferred_public_name ?? $author->name ?? null;
    }
@endphp
@if ($authorName)
<meta name="citation_author" content="{{ htmlspecialchars($authorName) }}">
@if ($author->affiliation)
<meta name="citation_author_institution" content="{{ htmlspecialchars($author->affiliation) }}">
@endif
@if ($author->orcid)
<meta name="citation_author_orcid" content="{{ htmlspecialchars($author->orcid) }}">
@endif
@endif
@endforeach

{{-- Keywords — with xml:lang (OJS includes this) --}}
@foreach ($processedKeywords as $keyword)
<meta name="citation_keywords" xml:lang="{{ $bcp47Locale }}" content="{{ htmlspecialchars($keyword) }}">
@endforeach

{{-- Abstract URL and full text URL --}}
<meta name="citation_abstract_html_url" content="{{ url()->current() }}">
@if ($pdfGalleyUrl)
<meta name="citation_fulltext_html_url" content="{{ url()->current() }}">
<meta name="citation_pdf_url" content="{{ $pdfGalleyUrl }}">
@endif

{{-- Abstract — with xml:lang (OJS includes this) --}}
@if ($pubAbstract)
<meta name="citation_abstract" xml:lang="{{ $bcp47Locale }}" content="{{ htmlspecialchars(trim(strip_tags($pubAbstract))) }}">
@endif

{{-- References — properly escaped with htmlspecialchars (OJS: htmlspecialchars($citation->getRawCitation())) --}}
{{-- Limit to 50 to avoid page size issues --}}
@foreach (array_slice($parsedRefs, 0, 50) as $ref)
<meta name="citation_reference" content="{{ htmlspecialchars($ref) }}">
@endforeach

{{-- ============================================================ --}}
{{-- DUBLIN CORE METADATA                                         --}}
{{-- Aligned with OJS DC metadata plugin                          --}}
{{-- ============================================================ --}}
<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/">
<meta name="DC.Title" content="{{ htmlspecialchars($pubTitle) }}">

@foreach ($pubAuthors as $author)
@php
    $dcName = trim(($author->first_name ?? '') . ' ' . ($author->last_name ?? ''));
    if (empty($dcName)) $dcName = $author->preferred_public_name ?? $author->name ?? null;
@endphp
@if ($dcName)
<meta name="DC.Creator.PersonalName" content="{{ htmlspecialchars($dcName) }}">
@endif
@endforeach

@if ($pubAbstract)
<meta name="DC.Description" xml:lang="{{ $bcp47Locale }}" content="{{ htmlspecialchars(trim(strip_tags($pubAbstract))) }}">
@endif

@if ($pubDate)
<meta name="DC.Date.issued" scheme="ISO8601" content="{{ $pubDate->format('Y-m-d') }}">
@endif
@if ($article->submitted_at)
<meta name="DC.Date.dateSubmitted" scheme="ISO8601" content="{{ $article->submitted_at->format('Y-m-d') }}">
@endif
@if ($pub->updated_at ?? false)
<meta name="DC.Date.modified" scheme="ISO8601" content="{{ $pub->updated_at->format('Y-m-d') }}">
@endif

<meta name="DC.Format" scheme="IMT" content="application/pdf">
<meta name="DC.Language" scheme="ISO639-1" content="{{ $bcp47Locale }}">

@if ($pubDoi)
<meta name="DC.Identifier.DOI" content="{{ htmlspecialchars($pubDoi) }}">
@endif
<meta name="DC.Identifier.URI" content="{{ url()->current() }}">

<meta name="DC.Rights" content="Copyright (c) {{ $copyrightYear }} {{ htmlspecialchars($copyrightHolder) }}">
@if ($licenseUrl)
<meta name="DC.Rights" content="{{ htmlspecialchars($licenseUrl) }}">
@endif

<meta name="DC.Source" content="{{ htmlspecialchars($journal->name) }}">
@if ($issnValue)
<meta name="DC.Source.ISSN" content="{{ htmlspecialchars($issnValue) }}">
@endif
@if ($issue)
@if ($issue->volume)
<meta name="DC.Source.Volume" content="{{ htmlspecialchars($issue->volume) }}">
@endif
@if ($issue->number)
<meta name="DC.Source.Issue" content="{{ htmlspecialchars($issue->number) }}">
@endif
@endif
<meta name="DC.Source.URI" content="{{ route('journal.public.home', $journal->slug) }}">

@foreach ($processedKeywords as $keyword)
<meta name="DC.Subject" xml:lang="{{ $bcp47Locale }}" content="{{ htmlspecialchars($keyword) }}">
@endforeach

<meta name="DC.Type" content="Text.Serial.Journal">
<meta name="DC.Type.articleType" content="{{ htmlspecialchars($article->section?->title ?? 'Articles') }}">
@endpush
    {{-- Schema.org JSON-LD for ScholarlyArticle --}}
    @php
        $schemaData = [
            '@context' => 'https://schema.org',
            '@type' => 'ScholarlyArticle',
            'headline' => $article->title,
            'name' => $article->title,
            'description' => Str::limit(strip_tags($article->abstract ?? ''), 300),
            'author' =>
                $article->authors
                    ?->map(function ($author) {
                        $a = [
                            '@type' => 'Person',
                            'name' => $author->full_name ?? $author->last_name . ' ' . $author->first_name,
                        ];
                        if (!empty($author->affiliation)) {
                            $a['affiliation'] = [
                                '@type' => 'Organization',
                                'name' => $author->affiliation,
                            ];
                        }
                        return $a;
                    })
                    ->toArray() ?? [],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $journal->publisher ?? $journal->name,
            ],
            'mainEntityOfPage' => url()->current(),
        ];

        if ($publicationDate) {
            $schemaData['datePublished'] = $publicationDate->toIso8601String();
        }

        if ($article->created_at) {
            $schemaData['dateCreated'] = $article->created_at->toIso8601String();
        }

        if ($pub->updated_at) {
            $schemaData['dateModified'] = $pub->updated_at->toIso8601String();
        }

        if ($issue) {
            $schemaData['isPartOf'] = [
                '@type' => 'PublicationIssue',
                'issueNumber' => $issue->number ?? '',
                'isPartOf' => [
                    '@type' => 'PublicationVolume',
                    'volumeNumber' => $issue->volume ?? '',
                    'isPartOf' => [
                        '@type' => 'Periodical',
                        'name' => $journal->name,
                        'issn' => $journal->issn_online ?? $journal->issn_print,
                    ],
                ],
            ];
        }

        if ($pubDoi) {
            $schemaData['identifier'] = [
                '@type' => 'PropertyValue',
                'propertyID' => 'DOI',
                'value' => $pubDoi,
            ];
            $schemaData['sameAs'] = 'https://doi.org/' . $pubDoi;
        }
    @endphp
    <script type="application/ld+json">
        {!! json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
    </script>

    {{-- ============================================ --}}
    {{-- BREADCRUMB NAVIGATION --}}
    {{-- ============================================ --}}
    <nav class="text-sm text-slate-500 mb-8" aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-2">
            <li>
                <a href="{{ route('journal.public.home', $journal->slug) }}"
                    class="hover:text-primary-600 hover:underline">
                    <i class="fa-solid fa-home mr-1"></i>Home
                </a>
            </li>
            <li class="text-slate-400">/</li>
            <li>
                <a href="{{ route('journal.public.archives', $journal->slug) }}"
                    class="hover:text-primary-600 hover:underline">
                    Archives
                </a>
            </li>
            @if ($issue)
                <li class="text-slate-400">/</li>
                <li>
                    <a href="{{ route('journal.public.issue', [$journal->slug, $issue->seq_id]) }}"
                        class="hover:text-primary-600 hover:underline">
                        Vol. {{ $issue->volume }} No. {{ $issue->number }} ({{ $issue->year }})
                    </a>
                </li>
            @endif
            <li class="text-slate-400">/</li>
            <li class="text-slate-800 font-medium truncate max-w-xs">
                {{ Str::limit($article->title, 40) }}
            </li>
        </ol>
    </nav>

    {{-- ============================================ --}}
    {{-- 2-COLUMN LAYOUT: MAIN CONTENT + SIDEBAR --}}
    {{-- OJS 3.3 FLAT DESIGN (NO CARDS) --}}
    {{-- ============================================ --}}
    <div class="flex flex-col lg:flex-row gap-12">

        {{-- ================= LEFT COLUMN (MAIN CONTENT - 75%) ================= --}}
        <main class="w-full lg:w-3/4 min-w-0 space-y-10">

            {{-- 1. TITLE --}}
            <h1 class="text-3xl md:text-4xl font-serif font-bold text-slate-900 leading-tight">
                {{ $article->title }}
            </h1>

            @if ($article->subtitle)
                <p class="text-xl text-slate-600 -mt-6">{{ $article->subtitle }}</p>
            @endif

            {{-- 2. AUTHORS --}}
            <div class="space-y-3">
                @if ($article->authors && $article->authors->isNotEmpty())
                    @foreach ($article->authors as $author)
                        <div class="leading-snug">
                            <div class="font-bold text-slate-900 text-lg">
                                {{ $author->first_name }} {{ $author->last_name }}
                                @if ($author->is_corresponding)
                                    <span class="text-orange-500 text-sm ml-1" title="Corresponding Author">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                @endif
                                @if ($author->orcid)
                                    <a href="https://orcid.org/{{ $author->orcid }}" target="_blank"
                                        class="text-green-600 hover:text-green-700 ml-2"
                                        title="ORCID: {{ $author->orcid }}">
                                        <i class="fa-brands fa-orcid text-lg"></i>
                                    </a>
                                @endif
                            </div>
                            @if ($author->affiliation)
                                <div class="text-slate-500 text-sm italic">{{ $author->affiliation }}</div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- 3. DOI --}}
            @php
                $doi = $article->currentPublication->doi ?? $article->doi;
            @endphp
            @if ($doi)
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-bold text-slate-700">DOI:</span>
                    <a href="https://doi.org/{{ $doi }}" target="_blank"
                        class="text-primary-600 hover:underline break-all">
                        https://doi.org/{{ $doi }}
                    </a>
                </div>
            @endif

            {{-- 4. KEYWORDS --}}
            {{-- 4. KEYWORDS --}}
            @php
                $rawKeywords = $article->currentPublication->keywords ?? $article->keywords;
                $keywords = [];
                if (!empty($rawKeywords)) {
                    if (is_string($rawKeywords)) {
                        $keywords = array_filter(array_map('trim', explode(',', $rawKeywords)));
                    } elseif (is_array($rawKeywords)) {
                        $keywords = $rawKeywords;
                    }
                }
            @endphp

            @if (!empty($keywords))
                <div class="mb-6">
                    <span class="font-bold text-slate-700">Keywords:</span>
                    <span class="text-slate-600">
                        @foreach ($keywords as $keyword)
                            @if (!$loop->first)
                                ,
                            @endif
                            <a href="{{ route('journal.public.search', ['journal' => $journal->slug, 'q' => $keyword]) }}"
                                class="hover:text-primary-600 hover:underline">{{ $keyword }}</a>
                        @endforeach
                    </span>
                </div>
            @endif

            {{-- 5. ABSTRACT (Orange Underline) --}}
            @if ($article->abstract)
                <div class="pt-6">
                    <h3
                        class="text-xl font-bold text-slate-800 border-b-4 border-orange-400 inline-block mb-4 pb-1 uppercase tracking-wide">
                        Abstract
                    </h3>
                    <div class="prose max-w-none text-slate-700 leading-relaxed text-justify">
                        {!! clean($article->abstract) !!}
                    </div>
                </div>
            @endif

            {{-- 6. DOWNLOADS CHART (Orange Underline) --}}
            <div class="pt-6">
                <h3
                    class="text-xl font-bold text-slate-800 border-b-4 border-orange-400 inline-block mb-6 pb-1 uppercase tracking-wide">
                    Downloads
                </h3>

                {{-- Summary Stats (Inline) --}}
                <div class="flex gap-8 mb-6 text-center">
                    <div>
                        <div class="text-3xl font-bold text-blue-600">
                            {{ number_format(array_sum(($viewsData ?? collect())->toArray())) }}
                        </div>
                        <div class="text-sm text-slate-600 mt-1">Total Views</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-green-600">
                            {{ number_format(array_sum(($downloadsData ?? collect())->toArray())) }}
                        </div>
                        <div class="text-sm text-slate-600 mt-1">Total Downloads</div>
                    </div>
                </div>

                {{-- Chart --}}
                <div class="h-64 w-full">
                    <canvas id="statsChart"></canvas>
                </div>

                {{-- Geographic Distribution (Admin Only) --}}
                @if (auth()->check() &&
                        auth()->user()->hasJournalPermission([
                            \App\Models\Role::LEVEL_ADMIN,
                            \App\Models\Role::LEVEL_MANAGER,
                            \App\Models\Role::LEVEL_EDITOR
                        ], $journal->id) &&
                        !empty($countryStats))
                    <div class="mt-8 pt-6 border-t border-slate-200">
                        <h4 class="text-lg font-bold text-slate-700 mb-4">
                            <i class="fa-solid fa-globe mr-2"></i>
                            Geographic Distribution (Top 10 Countries)
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 text-slate-600">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold">Country</th>
                                        <th class="px-4 py-2 text-right font-semibold">Views</th>
                                        <th class="px-4 py-2 text-left font-semibold">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($countryStats as $stat)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-3 font-medium text-slate-800">
                                                <i class="fa-solid fa-flag mr-2 text-slate-400"></i>
                                                {{ $stat->country_code }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-slate-700">
                                                {{ number_format($stat->views) }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex-1 bg-slate-200 rounded-full h-2 overflow-hidden">
                                                        <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-blue-600"
                                                            style="width: {{ $stat->percentage }}%"></div>
                                                    </div>
                                                    <span class="text-xs text-slate-500 min-w-[3rem] text-right">
                                                        {{ number_format($stat->percentage, 1) }}%
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 7. REFERENCES — OJS-compatible structure --}}
            {{-- OJS: <section class="item references"> with <p> per citation --}}
            {{-- Source: pkp/ojs article_details.tpl --}}
            @if (!empty($parsedRefs))
                <section class="item references pt-6" id="references">
                    <h2 class="text-xl font-bold text-slate-800 border-b-4 border-orange-400 inline-block mb-4 pb-1 uppercase tracking-wide">
                        References
                    </h2>
                    <div class="value space-y-2 text-slate-600 text-sm leading-relaxed">
                        @foreach ($parsedRefs as $ref)
                            @php
                                // Escape the reference text, then linkify URLs (OJS: getRawCitationWithLinks())
                                $safeRef = e($ref);
                                $linkedRef = preg_replace_callback(
                                    '/(https?:\/\/[^\s\)\]>]+)/',
                                    function ($m) {
                                        $url = $m[1];
                                        // Strip trailing punctuation that's not part of the URL
                                        $trailing = '';
                                        if (preg_match('/[.,;:)\]]+$/', $url, $t)) {
                                            $url = substr($url, 0, -strlen($t[0]));
                                            $trailing = e($t[0]);
                                        }
                                        return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" '
                                            . 'class="text-blue-600 hover:text-blue-800 hover:underline break-all">'
                                            . $url . '</a>' . $trailing;
                                    },
                                    $safeRef
                                );
                            @endphp
                            <p class="reference-item">{!! $linkedRef !!}</p>
                        @endforeach
                    </div>
                </section>
            @elseif ($article->currentPublication->references ?? $article->references ?? null)
                {{-- Fallback: raw references text (OJS fallback: citationsRaw|escape|nl2br) --}}
                @php
                    $rawRefText = $article->currentPublication->references ?? $article->references;
                @endphp
                <section class="item references pt-6" id="references">
                    <h2 class="text-xl font-bold text-slate-800 border-b-4 border-orange-400 inline-block mb-4 pb-1 uppercase tracking-wide">
                        References
                    </h2>
                    <div class="value text-slate-600 text-sm leading-relaxed">
                        {!! nl2br(e($rawRefText)) !!}
                    </div>
                </section>
            @endif

            {{-- 8. SIMILAR ARTICLES (Blue Header) --}}
            @if ($relatedArticles && $relatedArticles->isNotEmpty())
                <div class="pt-8 border-t border-slate-200">
                    <h3 class="text-xl font-bold text-primary-700 mb-4">
                        <i class="fa-solid fa-newspaper mr-2"></i>Similar Articles
                    </h3>
                    <div class="space-y-3 text-slate-700">
                        @foreach ($relatedArticles as $related)
                            <div>
                                @php
                                    $authors = $related->authors
                                        ? $related->authors
                                            ->map(function ($author) {
                                                return trim($author->first_name . ' ' . $author->last_name);
                                            })
                                            ->implode(', ')
                                        : '';
                                    $issueLink = $related->issue
                                        ? route('journal.public.issue', [$journal->slug, $related->issue->seq_id])
                                        : '#';
                                    $issueText = $journal->name;
                                    if ($related->issue) {
                                        $issueText .=
                                            ': Vol. ' .
                                            ($related->issue->volume ?? '-') .
                                            ' No. ' .
                                            ($related->issue->number ?? '-') .
                                            ' (' .
                                            ($related->issue->year ?? '-') .
                                            '): ' .
                                            ($related->issue->published_at
                                                ? $related->issue->published_at->format('F')
                                                : '') .
                                            ': ' .
                                            $journal->name;
                                    }
                                @endphp
                                {{ $authors }},
                                <a href="{{ route('journal.public.article', ['journal' => $journal->slug, 'article' => $related->seq_id]) }}"
                                    class="hover:text-primary-600 hover:underline font-medium">
                                    {{ $related->title }}
                                </a>,
                                <a href="{{ $issueLink }}" class="hover:text-primary-600 hover:underline">
                                    {{ $issueText }}
                                </a>
                                @if (!empty($related->doi))
                                    <div class="text-sm mt-0.5">
                                        <span class="font-bold text-slate-600">DOI:</span>
                                        <a href="https://doi.org/{{ $related->doi }}" target="_blank"
                                            class="text-primary-600 hover:underline">
                                            https://doi.org/{{ $related->doi }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </main>

        {{-- ================= RIGHT COLUMN (SIDEBAR - 25%) ================= --}}
        {{-- ================= RIGHT COLUMN (SIDEBAR - 25%) ================= --}}
        <aside class="w-full lg:w-1/4 space-y-6">

            {{-- ISSUE COVER (Clickable - Links to Issue Page) --}}
            @if ($issue)
                @if ($issue->cover_path)
                    <a href="{{ route('journal.public.issue', [$journal->slug, $issue->seq_id]) }}"
                        class="block hover:opacity-90 transition group">
                        <img src="{{ Storage::url($issue->cover_path) }}" alt="{{ $issue->title }}"
                            class="w-full rounded shadow-md border border-slate-200">
                    </a>
                @endif
            @endif

            {{-- FULL TEXT BUTTONS (Teal/OJS Style) --}}
            <div class="bg-slate-50 p-5 rounded border border-slate-200">
                <h4 class="font-bold text-slate-700 text-xs uppercase mb-3 tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-download"></i>
                    Full Text
                </h4>

                @if ($article->galleys && $article->galleys->isNotEmpty())
                    <div class="space-y-2">
                        @foreach ($article->galleys as $galley)
                            @php
                                $downloadRoute = route('journal.article.galley', ['journal' => $journal->slug, 'article' => $article->seq_id, 'galley' => $galley->seq_id ?? $galley->id]);
                            @endphp
                            <a href="{{ $downloadRoute }}"
                                class="flex items-center justify-center w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 px-4 rounded transition shadow-sm gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                {{ $galley->label ?? 'PDF' }}
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400 italic text-center py-4">
                        <i class="fa-solid fa-file-circle-xmark mr-1"></i>
                        No files available.
                    </p>
                @endif
            </div>

            {{-- HOW TO CITE WIDGET (Premium PakRT Native) --}}
            {{-- HOW TO CITE WIDGET (Premium PakRT Native) --}}
            <div class="bg-white p-6 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                <h4 class="font-bold text-slate-800 text-sm uppercase mb-4 tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-quote-left text-primary-500"></i>
                    How to Cite
                </h4>

                @php
                    // Pre-generate Citations for better performance
                    $pub = $article->currentPublication ?? $article;
                    $authors = $pub->authors;
                    $year = $issue->year ?? ($pub->date_published ? $pub->date_published->format('Y') : date('Y'));
                    $month = $issue->published_at ? $issue->published_at->format('M') : ($pub->date_published ? $pub->date_published->format('M') : '');
                    
                    // Author formatting for APA (Last, F. M.)
                    $apaAuthors = $authors->map(function($a) {
                        $last = $a->last_name ?? '';
                        $firstInitial = $a->first_name ? substr($a->first_name, 0, 1) . '.' : '';
                        return trim($last . ', ' . $firstInitial);
                    })->implode(', ');

                    // Author formatting for IEEE (F. M. Last)
                    $ieeeAuthors = $authors->map(function($a) {
                        $last = $a->last_name ?? '';
                        $firstInitial = $a->first_name ? substr($a->first_name, 0, 1) . '.' : '';
                        return trim($firstInitial . ' ' . $last);
                    })->implode(', ');

                    $vol = $issue->volume ?? '';
                    $num = $issue->number ?? '';
                    $pages = $pub->pages ?? '';
                    $doi = $pub->doi ?? '';

                    // APA 7th Edition Style
                    $apaCitation = "{$apaAuthors} ({$year}). {$pub->title}. <i>{$journal->name}</i>";
                    if ($vol || $num) {
                        $apaCitation .= ", <i>" . ($vol ?: '') . "</i>" . ($num ? "({$num})" : "");
                    }
                    if ($pages) $apaCitation .= ", {$pages}";
                    if ($doi) $apaCitation .= ". https://doi.org/{$doi}";

                    // IEEE Style
                    $ieeeCitation = "{$ieeeAuthors}, \"{$pub->title},\" <i>{$journal->name}</i>";
                    if ($vol) $ieeeCitation .= ", vol. {$vol}";
                    if ($num) $ieeeCitation .= ", no. {$num}";
                    if ($pages) $ieeeCitation .= ", pp. {$pages}";
                    if ($month || $year) $ieeeCitation .= ", " . trim($month . ' ' . $year);
                    if ($doi) $ieeeCitation .= ", doi: {$doi}.";
                @endphp

                <div class="mb-4">
                    <select id="citationFormat" onchange="switchCitation()"
                        class="w-full bg-slate-50 border-none text-slate-700 text-sm rounded-xl focus:ring-2 focus:ring-primary-500 block p-2.5 font-medium cursor-pointer">
                        <option value="apa">APA 7th Edition</option>
                        <option value="ieee">IEEE Style</option>
                    </select>
                </div>

                <div class="relative group">
                    <div id="citationContent" class="text-sm text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-2xl border border-dashed border-slate-200 min-h-[100px] italic">
                        {!! $apaCitation !!}
                    </div>
                    
                    <button onclick="copyCitation()" 
                        class="absolute top-2 right-2 p-2 bg-white/80 backdrop-blur-sm rounded-lg shadow-sm text-slate-400 hover:text-emerald-600 transition-colors"
                        title="Copy to Clipboard">
                        <i id="copyIcon" class="fa-regular fa-copy"></i>
                    </button>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <a href="{{ route('citation.ris', [$journal->slug, $article->slug ?? $article->id]) }}" 
                        class="flex items-center justify-center gap-2 py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition uppercase tracking-wider">
                        <i class="fa-solid fa-file-export text-slate-400"></i>
                        RIS (EndNote)
                    </a>
                    <a href="{{ route('citation.bibtex', [$journal->slug, $article->slug ?? $article->id]) }}" 
                        class="flex items-center justify-center gap-2 py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition uppercase tracking-wider">
                        <i class="fa-solid fa-code text-slate-400"></i>
                        BibTeX
                    </a>
                </div>
                {{-- JATS XML Download --}}
                <a href="{{ route('journal.article.jats', ['journal' => $journal->slug, 'article' => $article->seq_id]) }}"
                   class="flex items-center justify-center gap-2 py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition uppercase tracking-wider mt-3">
                    <i class="fa-solid fa-file-code text-slate-400"></i>
                    JATS XML
                </a>
            </div>

            <script>
                const citations = {
                    apa: `{!! addslashes($apaCitation) !!}`,
                    ieee: `{!! addslashes($ieeeCitation) !!}`
                };

                function switchCitation() {
                    const format = document.getElementById('citationFormat').value;
                    document.getElementById('citationContent').innerHTML = citations[format];
                }

                function copyCitation() {
                    const content = document.getElementById('citationContent').innerText;
                    navigator.clipboard.writeText(content).then(() => {
                        const icon = document.getElementById('copyIcon');
                        icon.classList.replace('fa-copy', 'fa-check');
                        icon.classList.add('text-emerald-600');
                        
                        setTimeout(() => {
                            icon.classList.replace('fa-check', 'fa-copy');
                            icon.classList.remove('text-emerald-600');
                        }, 2000);
                    });
                }
            </script>

            {{-- ISSUE INFORMATION --}}
            @if ($issue)
                <div class="bg-slate-50 p-5 rounded border border-slate-200">
                    <h4 class="font-bold text-slate-700 text-xs uppercase mb-3 tracking-wider">Issue</h4>
                    <a href="{{ route('journal.public.issue', [$journal->slug, $issue->seq_id]) }}"
                        class="block hover:text-primary-600 transition">
                        <p class="font-semibold text-slate-800">
                            Vol. {{ $issue->volume }} No. {{ $issue->number }} ({{ $issue->year }})
                        </p>
                        @if ($issue->title)
                            <p class="text-sm text-slate-500 mt-1">{{ $issue->title }}</p>
                        @endif
                    </a>
                </div>
            @endif

            @inject('citationService', 'App\Services\CitationService')
            @php
                $citations = $citationService->getAllFormats($article);
            @endphp

            <div x-data="{ format: 'APA', citations: @js($citations) }" class="bg-slate-50 p-5 rounded border border-slate-200">
                <h4 class="font-bold text-slate-700 text-xs uppercase mb-3 tracking-wider">
                    More Citation Formats
                </h4>

                <select x-model="format" class="w-full mb-3 border border-slate-300 rounded text-xs p-2 bg-white">
                    <template x-for="(v, k) in citations" :key="k">
                        <option x-text="k"></option>
                    </template>
                </select>

                <div class="bg-white p-3 border rounded text-xs italic font-mono leading-relaxed break-words"
                    x-html="citations[format]"></div>

                <button
                    @click="
                        navigator.clipboard.writeText(
                            document.querySelector('[x-html]').innerText
                        );
                        $el.innerText='Copied';
                        setTimeout(()=> $el.innerText='Copy Citation',2000);
                    "
                    class="mt-3 text-sm text-primary-600 hover:underline">
                    Copy Citation
                </button>

                {{-- DOWNLOAD --}}
                <div class="mt-5 border-t pt-3 text-xs text-slate-600">
                    <p class="font-semibold mb-2">Download Citation</p>
                    <a href="{{ route('citation.ris', ['journal' => $journal->slug, 'article' => $article->seq_id]) }}"
                        class="block hover:underline">
                        ⬇ EndNote / Zotero / Mendeley (RIS)
                    </a>
                    <a href="{{ route('citation.bibtex', ['journal' => $journal->slug, 'article' => $article->seq_id]) }}"
                        class="block hover:underline">
                        ⬇ BibTeX
                    </a>
                    <a href="{{ route('journal.article.jats', ['journal' => $journal->slug, 'article' => $article->seq_id]) }}"
                        class="block hover:underline">
                        ⬇ JATS XML
                    </a>
                </div>
            </div>

            {{-- ARTICLE METADATA --}}
            <div class="bg-slate-50 p-5 rounded border border-slate-200">
                <h4 class="font-bold text-slate-700 text-xs uppercase mb-3 tracking-wider">Article Info</h4>
                <dl class="space-y-3 text-sm">
                    @if ($publicationDate)
                        <div>
                            <dt class="text-xs font-bold text-slate-400 uppercase">Published</dt>
                            <dd class="text-slate-800 mt-1">{{ $publicationDate->format('Y-m-d') }}</dd>
                        </div>
                    @endif
                    @if ($article->section)
                        <div>
                            <dt class="text-xs font-bold text-slate-400 uppercase">Section</dt>
                            <dd class="text-slate-800 mt-1">{{ $article->section->name }}</dd>
                        </div>
                    @endif
                    @if ($article->pages)
                        <div>
                            <dt class="text-xs font-bold text-slate-400 uppercase">Pages</dt>
                            <dd class="text-slate-800 mt-1">{{ $article->pages }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- LICENSE --}}
            <x-public.article-license :journal="$journal" :publication="$article->currentPublication" />

            {{-- FUNDING INFORMATION --}}
            @php
                $fundingInfo = $article->currentPublication?->funding_info ?? [];
            @endphp
            @if (!empty($fundingInfo) && is_array($fundingInfo))
                <div class="bg-slate-50 p-5 rounded border border-slate-200">
                    <h4 class="font-bold text-slate-700 text-xs uppercase mb-3 tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-hand-holding-dollar text-slate-400"></i>
                        Funding
                    </h4>
                    <ul class="space-y-2 text-sm text-slate-600">
                        @foreach ($fundingInfo as $funder)
                            @if (!empty($funder['funder_name']))
                                <li>
                                    <span class="font-medium text-slate-800">{{ $funder['funder_name'] }}</span>
                                    @if (!empty($funder['award_number']))
                                        <span class="text-slate-500"> — Grant: {{ $funder['award_number'] }}</span>
                                    @endif
                                    @if (!empty($funder['funder_doi']))
                                        <a href="https://doi.org/{{ $funder['funder_doi'] }}" target="_blank"
                                           class="ml-1 text-xs text-primary-500 hover:underline">
                                            <i class="fa-solid fa-external-link-alt"></i>
                                        </a>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- SHARE BUTTONS --}}
            <div class="bg-slate-50 p-5 rounded border border-slate-200">
                <h4 class="font-bold text-slate-700 text-xs uppercase mb-3 tracking-wider">Share</h4>
                <div class="flex gap-2">
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($article->title) }}"
                        target="_blank" rel="noopener"
                        class="flex-1 flex items-center justify-center py-2 rounded-lg bg-sky-100 text-sky-600 hover:bg-sky-200 transition-colors">
                        <i class="fa-brands fa-x-twitter"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                        target="_blank" rel="noopener"
                        class="flex-1 flex items-center justify-center py-2 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ urlencode($article->title) }}"
                        target="_blank" rel="noopener"
                        class="flex-1 flex items-center justify-center py-2 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors">
                        <i class="fa-brands fa-linkedin-in"></i>
                    </a>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($article->title . ' ' . url()->current()) }}"
                        target="_blank" rel="noopener"
                        class="flex-1 flex items-center justify-center py-2 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition-colors">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                </div>
            </div>

        </aside>
    </div>

    {{-- ============================================ --}}
    {{-- CHART.JS INITIALIZATION SCRIPT --}}
    {{-- ============================================ --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('statsChart');
                if (!ctx) return;

                const chartLabels = @json($chartLabels ?? []);
                const viewsData = @json($viewsData ?? []);
                const downloadsData = @json($downloadsData ?? []);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                                label: 'Views',
                                data: viewsData,
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: 'rgb(59, 130, 246)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                            },
                            {
                                label: 'Downloads',
                                data: downloadsData,
                                borderColor: 'rgb(34, 197, 94)',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: 'rgb(34, 197, 94)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: {
                                        size: 12,
                                        weight: '600'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: {
                                    size: 13,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 12
                                },
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: true,
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y
                                            .toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush

</x-layouts.public>
