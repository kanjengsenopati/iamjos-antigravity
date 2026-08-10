@php $title = $issue->display_title; @endphp

<x-layouts.public :journal="$journal" :settings="$settings" :title="$title">
    @push('meta_tags')
        <meta name="citation_journal_title" content="{{ $journal->name }}">
        @if ($journal->issn_online)
            <meta name="citation_issn" content="{{ $journal->issn_online }}">
        @endif
        <meta name="citation_volume" content="{{ $issue->volume }}">
        <meta name="citation_issue" content="{{ $issue->number }}">
        <meta name="citation_publication_date" content="{{ $issue->published_at?->format('Y/m/d') }}">
    @endpush

    <div class="grid lg:grid-cols-4 gap-8">
        <!-- Issue Cover -->
        <div class="lg:col-span-1 sticky top-28 self-start">
            @if ($issue->cover_path)
                <img src="{{ Storage::disk('public')->url($issue->cover_path) }}" alt="Issue Cover"
                    class="w-full h-auto rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
            @else
                <div
                    class="aspect-[3/4] bg-gradient-to-br from-primary-400 to-primary-600 rounded-[24px] flex items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] w-full">
                    <div class="text-center text-white p-6">
                        <p class="text-lg font-bold">Vol. {{ $issue->volume }}</p>
                        <p class="text-4xl font-bold">No. {{ $issue->number }}</p>
                        <p class="text-lg mt-2">{{ $issue->year }}</p>
                    </div>
                </div>
            @endif

            <!-- Issue Galleys (Full Issue) -->
            @if ($issue->issueGalleys && $issue->issueGalleys->isNotEmpty())
                <div class="mt-8">
                    <x-text.h2 class="mb-4 pb-2 border-b border-gray-200 uppercase tracking-wider text-slate-400 text-xs font-bold">
                        {{ app()->getLocale() === 'id' ? 'Terbitan Lengkap' : 'Full Issue' }}
                    </x-text.h2>
                    <div class="flex flex-col gap-3">
                        @foreach ($issue->issueGalleys->sortBy('sort_order') as $galley)
                            <a href="{{ Storage::disk('public')->url($galley->file_path) }}" target="_blank"
                                class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-slate-600 hover:bg-slate-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all hover:shadow-md active:scale-95 w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                                {{ strtoupper($galley->label) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Issue Info & Table of Contents -->
        <div class="lg:col-span-3 space-y-8">
            <div>
                <nav class="text-sm text-gray-500 mb-4">
                    <a href="{{ route('journal.public.home', ['journal' => $journal->slug]) }}"
                        class="hover:text-primary-600">{{ __('Home') }}</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('journal.public.archives', ['journal' => $journal->slug]) }}"
                        class="hover:text-primary-600">{{ __('Archives') }}</a>
                    <span class="mx-2">/</span>
                </nav>

                {{-- PREVIEW MODE BANNER (OJS STYLE) --}}
                @if (request()->has('preview') || request()->boolean('preview') || !empty($isPreview) || !$issue->is_published)
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 shadow-xs flex items-center gap-3.5 mb-6">
                        <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                            <i class="fa-solid fa-eye text-base"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-amber-200/80 text-amber-950">
                                    {{ app()->getLocale() === 'id' ? 'MODE PRATINJAU' : 'PREVIEW MODE' }}
                                </span>
                                <span class="text-xs font-semibold text-amber-800">
                                    {{ app()->getLocale() === 'id' ? 'Terbitan Belum Diterbitkan' : 'Unpublished Issue' }}
                                </span>
                            </div>
                            <p class="text-xs text-amber-800/90 mt-0.5 leading-normal">
                                {{ app()->getLocale() === 'id'
                                    ? 'Ini adalah tampilan pratinjau (preview) terbitan yang belum diterbitkan secara resmi. Akses ini hanya tersedia untuk kebutuhan peninjauan editorial.'
                                    : 'This is a preview display of an issue that has not been officially published. Access is restricted for editorial review purposes.' }}
                            </p>
                        </div>
                    </div>
                @endif

                <x-text.h1 class="mb-4">
                    {{ $issue->display_title }}
                </x-text.h1>

                @if ($issue->description)
                    <x-text.body class="mb-6 [&_p]:text-justify">
                        {!! clean($issue->description) !!}
                    </x-text.body>
                @endif

                <div class="flex flex-wrap items-center gap-4">
                    <x-text.caption>
                        {{ __('Published') }}: {{ $issue->published_at?->format('Y-m-d') }}
                    </x-text.caption>
                    <x-text.caption>•</x-text.caption>
                    <x-text.caption>
                        {{ $articles->count() }} {{ __('Articles') }}
                    </x-text.caption>
                </div>
            </div>

            <!-- Table of Contents -->
            <div class="space-y-8">
                @if (isset($articlesBySection) && $articlesBySection->isNotEmpty())
                    @foreach ($articlesBySection as $sectionName => $sectionArticles)
                        <div>
                            <x-text.h2 class="mb-4 pb-2 border-b border-gray-200 uppercase tracking-wider text-slate-400 text-xs">
                                {{ $sectionName }}
                            </x-text.h2>
                            <div class="space-y-6">
                                @foreach ($sectionArticles as $article)
                                    <x-public.article-row :article="$article" :journal="$journal" />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-12 bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <x-text.h2 class="mb-2 text-center">{{ __('No articles in this issue') }}</x-text.h2>
                        <x-text.body class="text-gray-500">{{ __('Articles will be added soon.') }}</x-text.body>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.public>
