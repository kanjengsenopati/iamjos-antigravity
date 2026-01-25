@php
$primaryColor = $settings['primary_color'] ?? '#0369a1';
$secondaryColor = $settings['secondary_color'] ?? '#7c3aed';

// Get PDF Galley for citation_pdf_url (Google Scholar)
$pdfGalley = $article->galleys?->where('file_type', 'application/pdf')->first()
?? $article->galleys?->whereIn('label', ['PDF', 'pdf'])->first()
?? $article->galleys?->first();

// Publication date logic
$publicationDate = $issue?->published_at ?? $article->published_at;
@endphp

<x-layouts.public :journal="$journal" :settings="$settings" :title="$article->title . ' | ' . $journal->name">
    {{-- ============================================ --}}
    {{-- GOOGLE SCHOLAR / HIGHWIRE PRESS META TAGS --}}
    {{-- CRITICAL for academic indexing --}}
    {{-- ============================================ --}}
    @push('meta_tags')
    {{-- Highwire Press / Google Scholar Tags --}}
    <meta name="citation_title" content="{{ $article->title }}">
    <meta name="citation_journal_title" content="{{ $journal->name }}">
    @if($journal->abbreviation)
    <meta name="citation_journal_abbrev" content="{{ $journal->abbreviation }}">
    @endif
    @if($journal->publisher)
    <meta name="citation_publisher" content="{{ $journal->publisher }}">
    @endif

    {{-- Publication Date (CRITICAL - Format: YYYY/MM/DD) --}}
    @if($publicationDate)
    <meta name="citation_publication_date" content="{{ $publicationDate->format('Y/m/d') }}">
    <meta name="citation_date" content="{{ $publicationDate->format('Y/m/d') }}">
    <meta name="citation_online_date" content="{{ $publicationDate->format('Y/m/d') }}">
    @endif

    {{-- Volume & Issue --}}
    @if($issue)
    @if($issue->volume)
    <meta name="citation_volume" content="{{ $issue->volume }}">
    @endif
    @if($issue->number)
    <meta name="citation_issue" content="{{ $issue->number }}">
    @endif
    @endif

    {{-- Page Numbers --}}
    @if($article->pages)
    @php
    $pages = explode('-', $article->pages);
    $firstPage = $pages[0] ?? $article->pages;
    $lastPage = $pages[1] ?? null;
    @endphp
    <meta name="citation_firstpage" content="{{ trim($firstPage) }}">
    @if($lastPage)
    <meta name="citation_lastpage" content="{{ trim($lastPage) }}">
    @endif
    @endif

    {{-- DOI (Critical for citation tracking) --}}
    @if($article->doi)
    <meta name="citation_doi" content="{{ $article->doi }}">
    @endif

    {{-- ISSN --}}
    @if($journal->issn_online)
    <meta name="citation_issn" content="{{ $journal->issn_online }}">
    @elseif($journal->issn_print)
    <meta name="citation_issn" content="{{ $journal->issn_print }}">
    @endif

    {{-- Authors (One tag per author - CRITICAL) --}}
    @if($article->authors && $article->authors->isNotEmpty())
    @foreach($article->authors as $author)
    <meta name="citation_author" content="{{ $author->full_name ?? ($author->given_name . ' ' . $author->family_name) }}">
    @if($author->affiliation)
    <meta name="citation_author_institution" content="{{ $author->affiliation }}">
    @endif
    @if($author->email)
    <meta name="citation_author_email" content="{{ $author->email }}">
    @endif
    @if($author->orcid)
    <meta name="citation_author_orcid" content="{{ $author->orcid }}">
    @endif
    @endforeach
    @endif

    {{-- Keywords --}}
    @if($article->keywords && is_array($article->keywords))
    <meta name="citation_keywords" content="{{ implode('; ', $article->keywords) }}">
    @endif

    {{-- Abstract URL (Landing Page) --}}
    <meta name="citation_abstract_html_url" content="{{ url()->current() }}">

    {{-- PDF URL (CRITICAL - Must point to actual download, NOT view page) --}}
    @if($pdfGalley)
    <meta name="citation_pdf_url" content="{{ route('journal.article.download', ['journal' => $journal->slug, 'article' => $article->slug ?? $article->id, 'galley' => $pdfGalley->id]) }}">
    @endif

    {{-- Language --}}
    <meta name="citation_language" content="{{ $article->locale ?? 'en' }}">

    {{-- Dublin Core Metadata --}}
    <meta name="DC.Title" content="{{ $article->title }}">
    <meta name="DC.Creator" content="{{ $article->authors?->pluck('full_name')->implode('; ') }}">
    <meta name="DC.Description" content="{{ Str::limit(strip_tags($article->abstract ?? ''), 300) }}">
    @if($publicationDate)
    <meta name="DC.Date" content="{{ $publicationDate->format('Y-m-d') }}">
    @endif
    <meta name="DC.Publisher" content="{{ $journal->publisher ?? $journal->name }}">
    <meta name="DC.Type" content="Text.Article">
    @if($article->doi)
    <meta name="DC.Identifier" scheme="DOI" content="{{ $article->doi }}">
    @endif

    {{-- Open Graph for Articles --}}
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $article->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($article->abstract ?? ''), 200) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($publicationDate)
    <meta property="article:published_time" content="{{ $publicationDate->toIso8601String() }}">
    @endif
    @if($article->section)
    <meta property="article:section" content="{{ $article->section->name }}">
    @endif

    {{-- Schema.org JSON-LD for ScholarlyArticle --}}
    @php
        $schemaData = [
            "@context" => "https://schema.org",
            "@type" => "ScholarlyArticle",
            "headline" => $article->title,
            "name" => $article->title,
            "description" => Str::limit(strip_tags($article->abstract ?? ''), 300),
            "author" => $article->authors?->map(function($author) {
                $a = [
                    "@type" => "Person",
                    "name" => $author->full_name ?? ($author->given_name . ' ' . $author->family_name),
                ];
                if (!empty($author->affiliation)) {
                    $a["affiliation"] = [
                        "@type" => "Organization",
                        "name" => $author->affiliation
                    ];
                }
                return $a;
            })->toArray() ?? [],
            "publisher" => [
                "@type" => "Organization",
                "name" => $journal->publisher ?? $journal->name
            ],
            "mainEntityOfPage" => url()->current()
        ];

        if ($publicationDate) {
            $schemaData["datePublished"] = $publicationDate->toIso8601String();
        }

        if ($issue) {
            $schemaData["isPartOf"] = [
                "@type" => "PublicationIssue",
                "issueNumber" => $issue->number ?? '',
                "isPartOf" => [
                    "@type" => "PublicationVolume",
                    "volumeNumber" => $issue->volume ?? '',
                    "isPartOf" => [
                        "@type" => "Periodical",
                        "name" => $journal->name,
                        "issn" => $journal->issn_online ?? $journal->issn_print
                    ]
                ]
            ];
        }

        if ($article->doi) {
            $schemaData["identifier"] = [
                "@type" => "PropertyValue",
                "propertyID" => "DOI",
                "value" => $article->doi
            ];
            $schemaData["sameAs"] = "https://doi.org/" . $article->doi;
        }
    @endphp
    <script type="application/ld+json">
        {!! json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @endpush

    {{-- ============================================ --}}
    {{-- BREADCRUMB NAVIGATION --}}
    {{-- ============================================ --}}
    <nav class="text-sm text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-2">
            <li>
                <a href="{{ route('journal.public.home', $journal->slug) }}" class="hover:text-slate-700 hover:underline">
                    <i class="fa-solid fa-home mr-1"></i>Home
                </a>
            </li>
            <li class="text-slate-400">/</li>
            <li>
                <a href="{{ route('journal.public.archives', $journal->slug) }}" class="hover:text-slate-700 hover:underline">
                    Archives
                </a>
            </li>
            @if($issue)
            <li class="text-slate-400">/</li>
            <li>
                <a href="{{ route('journal.public.issue', [$journal->slug, $issue->id]) }}" class="hover:text-slate-700 hover:underline">
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
    {{-- 2-COLUMN LAYOUT: MAIN + SIDEBAR --}}
    {{-- ============================================ --}}
    <div class="flex flex-col lg:flex-row gap-8">

        {{-- ============================================ --}}
        {{-- MAIN CONTENT (Left - 3/4 width) --}}
        {{-- ============================================ --}}
        <main class="w-full lg:w-3/4">

            {{-- Article Card --}}
            <article class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

                {{-- Section Badge --}}
                @if($article->section)
                <div class="px-6 pt-6">
                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full"
                        style="background: {{ $primaryColor }}15; color: {{ $primaryColor }};">
                        <i class="fa-solid fa-folder-open mr-1.5"></i>
                        {{ $article->section->name }}
                    </span>
                </div>
                @endif

                {{-- Title --}}
                <header class="px-6 pt-4 pb-6">
                    <h1 class="text-2xl md:text-3xl font-serif font-bold text-slate-900 leading-tight">
                        {{ $article->title }}
                    </h1>

                    @if($article->subtitle)
                    <p class="mt-2 text-lg text-slate-600">{{ $article->subtitle }}</p>
                    @endif

                    {{-- DOI Badge --}}
                    @if($article->doi)
                    <div class="mt-4">
                        <a href="https://doi.org/{{ $article->doi }}" target="_blank" rel="noopener"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <i class="fa-solid fa-link mr-2"></i>
                            https://doi.org/{{ $article->doi }}
                        </a>
                    </div>
                    @endif
                </header>

                {{-- Authors Section (OJS 3.3 Toggle Style) --}}
                @if($article->authors && $article->authors->isNotEmpty())
                <div class="px-6 pb-6 border-t border-slate-100 pt-6" x-data="{ activeAuthor: null }">
                    <h2 class="text-sm font-bold text-slate-600 uppercase tracking-wider mb-4">
                        <i class="fa-solid fa-users mr-2"></i>Authors
                    </h2>

                    <div class="space-y-3">
                        @foreach($article->authors as $author)
                        <div class="border-b border-slate-100 pb-3 last:border-b-0 last:pb-0">
                            {{-- Author Name Row (Clickable to expand) --}}
                            <button @click="activeAuthor = activeAuthor === {{ $loop->index }} ? null : {{ $loop->index }}"
                                class="flex items-center gap-2 w-full text-left focus:outline-none group">
                                {{-- Avatar --}}
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm"
                                    style="background: {{ $primaryColor }};">
                                    {{ strtoupper(substr($author->given_name ?? $author->full_name ?? 'A', 0, 1)) }}{{ strtoupper(substr($author->family_name ?? '', 0, 1)) }}
                                </div>

                                {{-- Name & Badges --}}
                                <div class="flex-1">
                                    <span class="font-semibold text-slate-800 group-hover:text-blue-600 transition-colors text-lg">
                                        {{ $author->full_name ?? ($author->given_name . ' ' . $author->family_name) }}
                                    </span>

                                    {{-- Badges --}}
                                    <span class="inline-flex items-center gap-2 ml-2">
                                        @if($author->is_corresponding)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded" title="Corresponding Author">
                                            <i class="fa-solid fa-envelope mr-1"></i>Corresponding
                                        </span>
                                        @endif
                                        @if($author->orcid)
                                        <a href="https://orcid.org/{{ $author->orcid }}" target="_blank"
                                            class="text-green-600 hover:text-green-700" title="ORCID: {{ $author->orcid }}"
                                            @click.stop>
                                            <i class="fa-brands fa-orcid text-lg"></i>
                                        </a>
                                        @endif
                                    </span>
                                </div>

                                {{-- Expand/Collapse Icon --}}
                                <svg class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                    :class="{'rotate-180': activeAuthor === {{ $loop->index }}}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            {{-- Author Details (Collapsible) --}}
                            <div x-show="activeAuthor === {{ $loop->index }}"
                                x-collapse
                                x-cloak
                                class="mt-3 ml-12 text-sm text-slate-600 space-y-2">
                                @if($author->affiliation)
                                <p class="flex items-start gap-2">
                                    <i class="fa-solid fa-building text-slate-400 mt-0.5"></i>
                                    <span>{{ $author->affiliation }}</span>
                                </p>
                                @endif
                                @if($author->country)
                                <p class="flex items-center gap-2">
                                    <i class="fa-solid fa-globe text-slate-400"></i>
                                    <span>{{ $author->country }}</span>
                                </p>
                                @endif
                                @if($author->email && $author->is_corresponding)
                                <p class="flex items-center gap-2">
                                    <i class="fa-solid fa-envelope text-slate-400"></i>
                                    <a href="mailto:{{ $author->email }}" class="text-blue-600 hover:underline">{{ $author->email }}</a>
                                </p>
                                @endif
                                @if($author->bio)
                                <div class="mt-2 text-slate-500 italic border-l-2 border-slate-200 pl-3">
                                    {!! $author->bio !!}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Article Meta Bar --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-600">
                    @if($publicationDate)
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-calendar text-slate-400"></i>
                        <span>Published: <strong>{{ $publicationDate->format('F d, Y') }}</strong></span>
                    </div>
                    @endif
                    @if($issue)
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-book text-slate-400"></i>
                        <span>Vol. {{ $issue->volume }} No. {{ $issue->number }}</span>
                    </div>
                    @endif
                    @if($article->pages)
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-file-lines text-slate-400"></i>
                        <span>Pages: {{ $article->pages }}</span>
                    </div>
                    @endif
                </div>

                {{-- Abstract Section --}}
                @if($article->abstract)
                <section class="px-6 py-6 border-t border-slate-200">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 uppercase tracking-wide flex items-center gap-2">
                        <i class="fa-solid fa-align-left text-slate-400"></i>
                        Abstract
                    </h2>
                    <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed">
                        {!! $article->abstract !!}
                    </div>
                </section>
                @endif

                {{-- Keywords --}}
                @if($article->keywords && is_array($article->keywords) && count($article->keywords) > 0)
                <section class="px-6 py-4 border-t border-slate-200">
                    <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-tags text-slate-400"></i>
                        Keywords
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($article->keywords as $keyword)
                        <a href="{{ route('journal.public.search', ['journal' => $journal->slug, 'q' => $keyword]) }}"
                            class="px-3 py-1 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-full transition-colors">
                            {{ $keyword }}
                        </a>
                        @endforeach
                    </div>
                </section>
                @endif

            </article>

            {{-- References Section --}}
            @if($article->references)
            <section class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4 uppercase tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-quote-left text-slate-400"></i>
                    References
                </h2>
                <div class="prose prose-sm prose-slate max-w-none">
                    {!! $article->references !!}
                </div>
            </section>
            @endif

            {{-- How to Cite --}}
            <section class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-quote-right text-slate-400"></i>
                    How to Cite
                </h3>
                <div id="citation-text" class="p-4 bg-slate-50 rounded-lg border border-slate-200 text-sm text-slate-600 font-mono leading-relaxed">
                    @php
                    $authorList = $article->authors?->map(function($author) {
                    $familyName = $author->family_name ?? explode(' ', $author->full_name ?? '')[count(explode(' ', $author->full_name ?? '')) - 1] ?? '';
                    $givenName = $author->given_name ?? explode(' ', $author->full_name ?? '')[0] ?? '';
                    return $familyName . ', ' . substr($givenName, 0, 1) . '.';
                    })->implode(', ') ?? 'Author';
                    @endphp
                    {{ $authorList }} ({{ $issue?->year ?? $publicationDate?->year ?? now()->year }}). {{ $article->title }}.
                    <em>{{ $journal->name }}</em>{{ $issue ? ', ' : '' }}@if($issue)<em>{{ $issue->volume }}</em>({{ $issue->number }})@endif{{ $article->pages ? ', ' . $article->pages : '' }}.
                    {{ $article->doi ? 'https://doi.org/' . $article->doi : '' }}
                </div>
                <button onclick="navigator.clipboard.writeText(document.getElementById('citation-text').textContent.trim()).then(() => { this.innerHTML = '<i class=\'fa-solid fa-check mr-1\'></i> Copied!'; setTimeout(() => { this.innerHTML = '<i class=\'fa-regular fa-copy mr-1\'></i> Copy Citation'; }, 2000); })"
                    class="mt-3 text-sm font-medium hover:underline flex items-center gap-1" style="color: {{ $primaryColor }};">
                    <i class="fa-regular fa-copy mr-1"></i> Copy Citation
                </button>
            </section>

            {{-- Related Articles --}}
            @if($relatedArticles && $relatedArticles->isNotEmpty())
            <section class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-newspaper text-slate-400"></i>
                    Related Articles
                </h3>
                <div class="space-y-4">
                    @foreach($relatedArticles as $related)
                    <a href="{{ route('journal.public.article', ['journal' => $journal->slug, 'article' => $related->slug ?? $related->id]) }}"
                        class="block p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors group">
                        <h4 class="font-medium text-slate-800 group-hover:text-blue-600 transition-colors line-clamp-2">
                            {{ $related->title }}
                        </h4>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ $related->authors->pluck('full_name')->implode(', ') }}
                        </p>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif

        </main>

        {{-- ============================================ --}}
        {{-- SIDEBAR (Right - 1/4 width) --}}
        {{-- ============================================ --}}
        <aside class="w-full lg:w-1/4 space-y-6">

            {{-- Cover Image --}}
            @if($article->cover_image)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <img src="{{ Storage::url($article->cover_image) }}"
                    alt="{{ $article->title }}"
                    class="w-full object-cover">
            </div>
            @elseif($issue && $issue->cover_image)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <img src="{{ Storage::url($issue->cover_image) }}"
                    alt="Issue Cover - Vol. {{ $issue->volume }} No. {{ $issue->number }}"
                    class="w-full object-cover">
            </div>
            @endif

            {{-- Download Galleys / Full Text --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-bold text-slate-700 mb-4 text-sm uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-download text-slate-400"></i>
                    Full Text
                </h3>

                @if($article->galleys && $article->galleys->isNotEmpty())
                <div class="space-y-2">
                    @foreach($article->galleys as $galley)
                    <a href="{{ route('journal.article.download', ['journal' => $journal->slug, 'article' => $article->slug ?? $article->id, 'galley' => $galley->id]) }}"
                        class="flex items-center justify-center gap-2 w-full text-center font-medium py-2.5 px-4 rounded-lg transition-all
                                      {{ strtoupper($galley->label) === 'PDF' ? 'text-white shadow-md hover:shadow-lg' : 'text-slate-700 bg-slate-100 hover:bg-slate-200' }}"
                        style="{{ strtoupper($galley->label) === 'PDF' ? 'background: linear-gradient(135deg, ' . $primaryColor . ', ' . $secondaryColor . ');' : '' }}">
                        @if(strtoupper($galley->label) === 'PDF')
                        <i class="fa-solid fa-file-pdf"></i>
                        @elseif(strtoupper($galley->label) === 'XML')
                        <i class="fa-solid fa-file-code"></i>
                        @elseif(strtoupper($galley->label) === 'HTML')
                        <i class="fa-brands fa-html5"></i>
                        @else
                        <i class="fa-solid fa-file-arrow-down"></i>
                        @endif
                        {{ $galley->label }}
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

            {{-- Issue Information --}}
            @if($issue)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-bold text-slate-700 mb-4 text-sm uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-book text-slate-400"></i>
                    Issue
                </h3>
                <a href="{{ route('journal.public.issue', [$journal->slug, $issue->id]) }}"
                    class="block p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors group">
                    <p class="font-semibold text-slate-800 group-hover:text-blue-600">
                        Vol. {{ $issue->volume }} No. {{ $issue->number }} ({{ $issue->year }})
                    </p>
                    @if($issue->title)
                    <p class="text-sm text-slate-500 mt-1">{{ $issue->title }}</p>
                    @endif
                </a>
            </div>
            @endif

            {{-- Article Metadata Box --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-bold text-slate-700 mb-4 text-sm uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-info-circle text-slate-400"></i>
                    Article Info
                </h3>

                <dl class="space-y-4 text-sm">
                    {{-- Published Date --}}
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Published</dt>
                        <dd class="text-slate-800 mt-1">{{ $publicationDate?->format('Y-m-d') ?? '-' }}</dd>
                    </div>

                    {{-- Section --}}
                    @if($article->section)
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Section</dt>
                        <dd class="text-slate-800 mt-1">{{ $article->section->name }}</dd>
                    </div>
                    @endif

                    {{-- DOI --}}
                    @if($article->doi)
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">DOI</dt>
                        <dd class="text-slate-800 mt-1 break-all">
                            <a href="https://doi.org/{{ $article->doi }}" target="_blank" class="text-blue-600 hover:underline">
                                {{ $article->doi }}
                            </a>
                        </dd>
                    </div>
                    @endif

                    {{-- Pages --}}
                    @if($article->pages)
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Pages</dt>
                        <dd class="text-slate-800 mt-1">{{ $article->pages }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- License / Copyright --}}
            @php
                $license = $article->license ?? $journal->default_license ?? 'CC BY 4.0';
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-bold text-slate-700 mb-4 text-sm uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-scale-balanced text-slate-400"></i>
                    License
                </h3>
                <div class="flex items-center gap-3">
                    <img src="https://licensebuttons.net/l/by/4.0/88x31.png"
                        alt="Creative Commons License"
                        class="h-6">
                    <p class="text-xs text-slate-600">
                        This work is licensed under a
                        <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" class="text-blue-600 hover:underline">
                            Creative Commons Attribution 4.0 International License
                        </a>.
                    </p>
                </div>
            </div>

            {{-- Share Buttons --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-bold text-slate-700 mb-4 text-sm uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-share-nodes text-slate-400"></i>
                    Share
                </h3>
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
                    <a href="mailto:?subject={{ urlencode($article->title) }}&body={{ urlencode(url()->current()) }}"
                        class="flex-1 flex items-center justify-center py-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                        <i class="fa-solid fa-envelope"></i>
                    </a>
                </div>
            </div>

        </aside>
    </div>

</x-layouts.public>