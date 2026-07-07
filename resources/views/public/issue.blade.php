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
        </div>

        <!-- Issue Info & Table of Contents -->
        <div class="lg:col-span-3 space-y-8">
            <div>
                <nav class="text-sm text-gray-500 mb-4">
                    <a href="{{ route('journal.public.archives', ['journal' => $journal->slug]) }}"
                        class="hover:text-primary-600">{{ __('Archives') }}</a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-900">{{ $issue->year }}</span>
                </nav>

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
