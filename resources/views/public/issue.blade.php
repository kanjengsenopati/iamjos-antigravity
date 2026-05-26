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

    <!-- Issue Header -->
    <section class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid lg:grid-cols-4 gap-8">
                <!-- Issue Cover -->
                <div class="lg:col-span-1">
                    <div
                        class="aspect-[3/4] bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center sticky top-24">
                        @if ($issue->cover_path)
                            <img src="{{ Storage::disk('public')->url($issue->cover_path) }}" alt="Issue Cover"
                                class="w-full h-full object-cover rounded-xl">
                        @else
                            <div class="text-center text-white p-6">
                                <p class="text-lg font-bold">Vol. {{ $issue->volume }}</p>
                                <p class="text-4xl font-bold">No. {{ $issue->number }}</p>
                                <p class="text-lg mt-2">{{ $issue->year }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Issue Info -->
                <div class="lg:col-span-3">
                    <nav class="text-sm text-gray-500 mb-4">
                        <a href="{{ route('journal.public.archives', ['journal' => $journal->slug]) }}"
                            class="hover:text-primary-600">Archives</a>
                        <span class="mx-2">/</span>
                        <span class="text-gray-900">{{ $issue->year }}</span>
                    </nav>

                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $issue->display_title }}</h1>

                    @if ($issue->title)
                        <p class="text-xl text-gray-600 mb-4">{{ $issue->title }}</p>
                    @endif

                    @if ($issue->description)
                        <div class="prose max-w-none text-gray-700 mb-6">
                            {!! clean($issue->description) !!}
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-8">
                        <span>Published: {{ $issue->published_at?->format('F j, Y') }}</span>
                        <span>•</span>
                        <span>{{ $articles->count() }} Articles</span>
                    </div>

                    <!-- Table of Contents -->
                    <div class="space-y-8">
                        @if (isset($articlesBySection) && $articlesBySection->isNotEmpty())
                            @foreach ($articlesBySection as $sectionName => $sectionArticles)
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                        {{ $sectionName }}
                                    </h2>
                                    <div class="space-y-8">
                                        @foreach ($sectionArticles as $article)
                                            <x-public.article-row :article="$article" :journal="$journal" />
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No articles in this issue</h3>
                                <p class="text-gray-500">Articles will be added soon.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
