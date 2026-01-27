<x-layouts.public :journal="$journal" :settings="$settings" :title="$title">
    <!-- Search Hero Section -->
    <section class="bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 py-16 relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-0 w-96 h-96 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-64 h-64 bg-white/5 rounded-full translate-x-1/4 translate-y-1/4">
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-8">
                <h1 class="text-3xl sm:text-4xl font-bold text-white mb-3">Search Articles</h1>
                <p class="text-primary-100 max-w-2xl mx-auto">
                    Find published research articles by title, author, keywords, or abstract
                </p>
            </div>

            <!-- Search Form -->
            <form action="{{ route('journal.public.search', ['journal' => $journal->slug]) }}" method="GET" class="space-y-4">
                <!-- Main Search Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="q" value="{{ $query ?? '' }}"
                        placeholder="Enter your search term..."
                        class="w-full pl-14 pr-32 py-4 text-lg rounded-2xl border-0 shadow-xl focus:ring-4 focus:ring-white/30 placeholder-gray-400 text-gray-900"
                        autofocus>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                        <button type="submit"
                            class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                            Search
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <!-- Search Type -->
                    <div class="flex items-center space-x-2">
                        <label class="text-primary-100 text-sm">Search in:</label>
                        <select name="type"
                            class="bg-white/10 text-white border-0 rounded-lg text-sm focus:ring-2 focus:ring-white/30 py-2 px-3">
                            <option value="all" {{ ($type ?? 'all') === 'all' ? 'selected' : '' }}
                                class="text-gray-900">All Fields</option>
                            <option value="title" {{ ($type ?? '') === 'title' ? 'selected' : '' }}
                                class="text-gray-900">Title</option>
                            <option value="author" {{ ($type ?? '') === 'author' ? 'selected' : '' }}
                                class="text-gray-900">Author</option>
                            <option value="keywords" {{ ($type ?? '') === 'keywords' ? 'selected' : '' }}
                                class="text-gray-900">Keywords</option>
                            <option value="abstract" {{ ($type ?? '') === 'abstract' ? 'selected' : '' }}
                                class="text-gray-900">Abstract</option>
                        </select>
                    </div>

                    <!-- Year Filter -->
                    @if (isset($years) && $years->count() > 0)
                        <div class="flex items-center space-x-2">
                            <label class="text-primary-100 text-sm">Year:</label>
                            <select name="year"
                                class="bg-white/10 text-white border-0 rounded-lg text-sm focus:ring-2 focus:ring-white/30 py-2 px-3">
                                <option value="" class="text-gray-900">All Years</option>
                                @foreach ($years as $y)
                                    <option value="{{ $y }}" {{ ($year ?? '') == $y ? 'selected' : '' }}
                                        class="text-gray-900">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </section>

    <!-- Search Results Section -->
    <section class="py-12 bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($query)
                <!-- Results Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            @if ($totalFound > 0)
                                Found <span class="text-primary-600">{{ $totalFound }}</span>
                                {{ Str::plural('result', $totalFound) }}
                            @else
                                No results found
                            @endif
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            for "{{ $query }}"
                            @if ($type !== 'all')
                                in {{ ucfirst($type) }}
                            @endif
                            @if ($year)
                                from {{ $year }}
                            @endif
                        </p>
                    </div>

                    @if ($totalFound > 0)
                        <a href="{{ route('journal.public.search', ['journal' => $journal->slug]) }}"
                            class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                            Clear search
                        </a>
                    @endif
                </div>

                @if ($totalFound > 0 && $results->count() > 0)
                    <!-- Results List -->
                    <div class="space-y-6">
                        @foreach ($results as $article)
                            <article
                                class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg hover:border-primary-200 transition-all duration-300 group">
                                <div class="flex gap-6">
                                    <!-- Article Content -->
                                    <div class="flex-1 min-w-0">
                                        <!-- Section & Issue Badge -->
                                        <div class="flex flex-wrap items-center gap-2 mb-3">
                                            @if ($article->section)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-700">
                                                    {{ $article->section->name }}
                                                </span>
                                            @endif
                                            @if ($article->issue)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                    {{ $article->issue->identifier }}
                                                </span>
                                            @endif
                                            @if ($article->published_at)
                                                <span class="text-xs text-gray-500">
                                                    Published {{ $article->published_at->format('M d, Y') }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Title -->
                                        <h3
                                            class="text-lg font-bold text-gray-900 group-hover:text-primary-600 transition-colors mb-2">
                                            <a href="{{ route('journal.public.article', ['journal' => $journal->slug, 'submission' => $article]) }}">
                                                {{ $article->title }}
                                            </a>
                                        </h3>

                                        <!-- Authors -->
                                        @if ($article->authors->count() > 0)
                                            <p class="text-sm text-gray-600 mb-3">
                                                <span class="font-medium">By:</span>
                                                {{ $article->authors->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') }}
                                            </p>
                                        @endif

                                        <!-- Abstract Snippet -->
                                        @if ($article->abstract)
                                            <p class="text-sm text-gray-500 leading-relaxed mb-4 line-clamp-2">
                                                {{ Str::limit(strip_tags($article->abstract), 200) }}
                                            </p>
                                        @endif

                                        <!-- Keywords -->
                                        @if ($article->keywords)
                                            <div class="flex flex-wrap gap-1.5 mb-4">
                                                @foreach (array_slice(explode(',', $article->keywords), 0, 4) as $keyword)
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                                                        {{ trim($keyword) }}
                                                    </span>
                                                @endforeach
                                                @if (count(explode(',', $article->keywords)) > 4)
                                                    <span
                                                        class="text-xs text-gray-400">+{{ count(explode(',', $article->keywords)) - 4 }}
                                                        more</span>
                                                @endif
                                            </div>
                                        @endif

                                        <!-- Actions -->
                                        <div class="flex items-center gap-4">
                                            <a href="{{ route('journal.public.article', ['journal' => $journal->slug, 'submission' => $article]) }}"
                                                class="inline-flex items-center text-sm font-medium text-primary-600 hover:text-primary-700">
                                                View Details
                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>

                                            @if ($article->files->where('file_type', 'galley')->count() > 0)
                                                <a href="{{ route('journal.public.article.reader', ['journal' => $journal->slug, 'submission' => $article]) }}"
                                                    class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                    </svg>
                                                    Read PDF
                                                </a>
                                            @endif

                                            @if ($article->doi)
                                                <a href="https://doi.org/{{ $article->doi }}" target="_blank"
                                                    class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                                                    <span class="font-medium">DOI:</span>
                                                    <span class="ml-1">{{ $article->doi }}</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if ($results->hasPages())
                        <div class="mt-10">
                            {{ $results->links() }}
                        </div>
                    @endif
                @else
                    <!-- No Results -->
                    <div class="text-center py-16">
                        <div class="w-20 h-20 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No articles found</h3>
                        <p class="text-gray-500 max-w-md mx-auto mb-6">
                            We couldn't find any articles matching "{{ $query }}". Try using different keywords
                            or check your spelling.
                        </p>
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                            <a href="{{ route('journal.public.search', ['journal' => $journal->slug]) }}"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                New Search
                            </a>
                            <a href="{{ route('journal.public.archives', ['journal' => $journal->slug]) }}"
                                class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                                Browse Archives
                            </a>
                        </div>
                    </div>
                @endif
            @else
                <!-- Search Tips / Empty State -->
                <div class="max-w-2xl mx-auto text-center py-12">
                    <div
                        class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-primary-100 to-primary-200 rounded-2xl flex items-center justify-center">
                        <svg class="w-12 h-12 text-primary-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Start Your Research</h2>
                    <p class="text-gray-600 mb-8 leading-relaxed">
                        Enter keywords, author names, or phrases to find relevant articles in our journal.
                        You can also filter by search type and publication year.
                    </p>

                    <!-- Search Tips -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6 text-left">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            Search Tips
                        </h3>
                        <ul class="space-y-3 text-sm text-gray-600">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Use specific terms for more relevant results (e.g., "machine learning" instead of
                                    just "learning")</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Select "Author" to search specifically by researcher name</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Use the year filter to narrow down results to a specific publication period</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Search is case-insensitive, so "AI" and "ai" will return the same results</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Quick Links -->
                    <div class="mt-8 flex flex-wrap justify-center gap-4">
                        <a href="{{ route('journal.public.current', ['journal' => $journal->slug]) }}"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-colors">
                            <svg class="w-4 h-4 mr-2 text-primary-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2" />
                            </svg>
                            Current Issue
                        </a>
                        <a href="{{ route('journal.public.archives', ['journal' => $journal->slug]) }}"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-colors">
                            <svg class="w-4 h-4 mr-2 text-primary-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                            Browse Archives
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
