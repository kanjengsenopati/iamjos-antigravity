@props(['article', 'journal'])

@php
    $publication = $article->currentPublication ?? $article;
    $authors = $publication->authors ?? collect();
    $doi = $publication->doi;
    $pages = $publication->pages;
    $galleys = $article->galleys ?? collect();
    $views = $article->views_count ?? 0;
    $downloads = $article->downloads_count ?? 0;
@endphp

<div {{ $attributes->merge(['class' => 'relative flex flex-col gap-4 p-6 bg-white border-l-4 border-blue-600 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-lg transition-all group']) }}>
    
    {{-- Top Right Page Numbers --}}
    @if($pages)
        <div class="absolute top-6 right-6">
            <x-text.caption class="font-mono text-slate-400">
                {{ $pages }}
            </x-text.caption>
        </div>
    @endif

    <div class="flex-1 min-w-0 pr-12">
        {{-- Title --}}
        <x-text.h2 class="group-hover:text-blue-600 transition-colors mb-3">
            <a href="{{ route('journal.public.article', ['journal' => $journal->slug, 'article' => $article->seq_id]) }}" class="block">
                {{ $publication->title }}
            </a>
        </x-text.h2>

        {{-- Authors List --}}
        @if($authors->isNotEmpty())
            <div class="flex items-start gap-2 mb-3">
                <div class="mt-0.5 shrink-0 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="flex flex-wrap gap-x-2 gap-y-1">
                    @foreach($authors as $author)
                        <x-text.body class="text-sm">
                            <span class="font-bold text-slate-700">{{ $author->first_name }} {{ $author->last_name }}</span>@if($author->affiliation)<span class="text-slate-400 font-normal">, {{ $author->affiliation }}</span>@endif@if(!$loop->last)<span class="text-slate-300">;</span>@endif
                        </x-text.body>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- DOI --}}
        @if($doi)
            <div class="flex items-center gap-2 mb-4">
                <div class="shrink-0 text-orange-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                </div>
                <x-text.caption class="text-blue-600 font-semibold tracking-normal lowercase italic">
                    DOI: <a href="https://doi.org/{{ $doi }}" target="_blank" class="hover:underline">https://doi.org/{{ $doi }}</a>
                </x-text.caption>
            </div>
        @endif

        {{-- Action Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mt-2">
            {{-- Galleys --}}
            <div class="flex flex-wrap gap-2">
                @foreach($galleys as $galley)
                    <a href="{{ route('journal.article.download', ['journal' => $journal->slug, 'article' => $article->slug ?? $article->id, 'galley' => $galley->id]) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all hover:shadow-md active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                        {{ strtoupper($galley->label ?? 'PDF') }}
                    </a>
                @endforeach
            </div>

            {{-- Metrics --}}
            <div class="flex items-center gap-4 bg-slate-50 px-4 py-2 rounded-2xl border border-slate-100">
                <div class="flex items-center gap-1.5" title="Abstract Views">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <x-text.caption class="font-bold text-slate-500">
                        {{ $views }} <span class="text-[10px] uppercase tracking-wider font-medium text-slate-400 ml-0.5">Views</span>
                    </x-text.caption>
                </div>
                <div class="w-px h-3 bg-slate-200"></div>
                <div class="flex items-center gap-1.5" title="File Downloads">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    <x-text.caption class="font-bold text-slate-500">
                        {{ $downloads }} <span class="text-[10px] uppercase tracking-wider font-medium text-slate-400 ml-0.5">Downloads</span>
                    </x-text.caption>
                </div>
            </div>
        </div>
    </div>
</div>
