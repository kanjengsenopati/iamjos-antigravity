@extends('layouts.portal')

@section('title', 'Search Results')

@php
    $brandColor = $settings['primary_color'] ?? '#00629B';
@endphp

@section('content')
    <div x-data="{ activeTab: 'all' }" class="min-h-screen bg-slate-50 pb-20">

        {{-- ============================================ --}}
        {{-- PAGE HEADER --}}
        {{-- ============================================ --}}
        <div class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-12">
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Search Results</h1>
                <p class="mt-2 text-slate-500 text-base md:text-lg">
                    @if ($query)
                        Showing results for "<strong>{{ $query }}</strong>"
                    @else
                        Find journals and scholarly articles
                    @endif
                </p>

                {{-- Search Form --}}
                <div class="mt-6">
                    <form id="searchForm" method="GET" action="{{ route('portal.search') }}"
                        class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <input type="text" name="q" value="{{ $query }}"
                                placeholder="Search journals, articles, or authors..."
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                required>
                        </div>
                        <div class="flex gap-3">
                            <input type="hidden" name="sort" value="{{ $sort }}">
                            <select name="category"
                                class="px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">All Categories</option>
                                <option value="journals" {{ $category === 'journals' ? 'selected' : '' }}>Journals</option>
                                <option value="articles" {{ $category === 'articles' ? 'selected' : '' }}>Articles</option>
                            </select>
                            <button type="submit"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm font-medium transition-colors">
                                <i class="fa-solid fa-search mr-2"></i>
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- RESULTS TABS --}}
        {{-- ============================================ --}}
        @if ($query && (strlen($query) >= 2 || !empty($category)))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                    <div class="border-b border-slate-200">
                        <nav class="flex">
                            <button @click="activeTab = 'all'"
                                :class="activeTab === 'all' ? 'border-blue-500 text-blue-600' :
                                    'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                                All ({{ $journals->count() + $articles->total() }})
                            </button>
                            <button @click="activeTab = 'journals'"
                                :class="activeTab === 'journals' ? 'border-blue-500 text-blue-600' :
                                    'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                                Journals ({{ $journals->count() }})
                            </button>
                            <button @click="activeTab = 'articles'"
                                :class="activeTab === 'articles' ? 'border-blue-500 text-blue-600' :
                                    'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                                Articles ({{ $articles->total() }})
                            </button>
                        </nav>
                    </div>

                    {{-- ============================================ --}}
                    {{-- RESULTS CONTENT --}}
                    {{-- ============================================ --}}
                    <div class="p-6">

                        {{-- ALL RESULTS --}}
                        <div x-show="activeTab === 'all'" x-transition>
                            {{-- Journals Section --}}
                            @if ($journals->isNotEmpty())
                                <div class="mb-8">
                                    <h3 class="text-lg font-semibold text-slate-900 mb-4 divider-title"><span>Journals</span></h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach ($journals as $journal)
                                            <div
                                                class="bg-white border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow group">
                                                <div class="flex items-start gap-4">
                                                    <div
                                                        class="w-14 h-14 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 group-hover:border-blue-600 transition-colors">
                                                        <i class="fa-solid fa-book text-blue-600 group-hover:text-white"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h4 class="font-bold text-slate-900 truncate">
                                                            <a href="{{ route('journal.public.home', $journal->slug) }}"
                                                                class="hover:text-blue-600 text-lg transition-colors">
                                                                {{ $journal->name }}
                                                            </a>
                                                        </h4>
                                                        <p class="text-sm text-slate-500 mt-1 line-clamp-2">
                                                            {{ Str::limit($journal->description, 120) }}
                                                        </p>
                                                        <div class="flex items-center gap-4 mt-3 text-xs font-medium text-slate-400 capitalize">
                                                            <span class="flex items-center gap-1.5 bg-slate-100 px-2 py-1 rounded text-slate-600">
                                                                <i class="fa-solid fa-file-text"></i>
                                                                {{ number_format($journal->submissions_count) }} Articles
                                                            </span>
                                                            @if ($journal->abbreviation)
                                                                <span class="flex items-center gap-1.5 bg-indigo-50 px-2 py-1 rounded text-indigo-600">
                                                                    <i class="fa-solid fa-tag"></i>
                                                                    {{ $journal->abbreviation }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Articles Section --}}
                            @if ($articles->isNotEmpty())
                                <div>
                                    <div class="flex items-center justify-between mb-4 mt-12 pb-2 border-b border-slate-100">
                                        <h3 class="text-lg font-bold text-slate-900">Research Articles</h3>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-slate-500">Sort by:</span>
                                            <select name="sort" onchange="this.form.submit()" form="searchForm"
                                                class="text-sm border border-slate-300 rounded px-2 py-1 focus:ring-1 focus:ring-blue-500">
                                                <option value="relevance" {{ $sort === 'relevance' ? 'selected' : '' }}>Relevance</option>
                                                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
                                                <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="space-y-6">
                                        @foreach ($articles as $article)
                                            <div class="bg-white border border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-lg transition-all group relative overflow-hidden">
                                                <div class="flex flex-col md:flex-row gap-6">
                                                    {{-- Article Content --}}
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-3 mb-3">
                                                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase tracking-wider rounded border border-emerald-100">Research Article</span>
                                                            <span class="text-xs text-slate-400 font-medium">Published in {{ $article->journal->name }}</span>
                                                        </div>

                                                        <h4 class="text-xl font-bold text-slate-900 leading-snug">
                                                            <a href="{{ route('journal.public.article', [$article->journal->slug, $article->slug]) }}"
                                                                class="hover:text-blue-600 transition-colors">
                                                                {{ $article->title }}
                                                            </a>
                                                        </h4>

                                                        <p class="text-sm text-blue-600 font-medium mt-2 italic">
                                                            by {{ $article->authors->pluck('display_name')->join(', ') }}
                                                        </p>

                                                        @if ($article->abstract)
                                                            <p class="text-sm text-slate-600 mt-4 leading-relaxed line-clamp-2">
                                                                {{ Str::limit(strip_tags($article->abstract), 250) }}
                                                            </p>
                                                        @endif

                                                        <div class="flex flex-wrap items-center gap-y-4 gap-x-6 mt-6 pt-5 border-t border-slate-50">
                                                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                                                <i class="fa-regular fa-calendar text-blue-500"></i>
                                                                <span>{{ $article->published_at ? $article->published_at->format('M d, Y') : 'Original Date' }}</span>
                                                            </div>
                                                            @if ($article->section)
                                                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                                                    <i class="fa-regular fa-folder text-blue-500"></i>
                                                                    <span>{{ $article->section->title }}</span>
                                                                </div>
                                                            @endif
                                                            
                                                            {{-- Metrics --}}
                                                            <div class="flex items-center gap-4 ml-auto border-l border-slate-100 pl-6">
                                                                <div class="flex flex-col items-center">
                                                                    <span class="text-lg font-bold text-slate-800">{{ number_format($article->views_count) }}</span>
                                                                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-tighter">Views</span>
                                                                </div>
                                                                <div class="flex flex-col items-center">
                                                                    <span class="text-lg font-bold text-slate-800">{{ number_format($article->downloads_count) }}</span>
                                                                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-tighter">Downloads</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Pagination --}}
                                    @if ($articles->hasPages())
                                        <div class="mt-8">
                                            {{ $articles->appends(request()->query())->links() }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- JOURNALS ONLY --}}
                        <div x-show="activeTab === 'journals'" x-transition>
                            @if ($journals->isNotEmpty())
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach ($journals as $journal)
                                        <div
                                            class="bg-white border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="fa-solid fa-book text-blue-600"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="font-semibold text-slate-900 truncate">
                                                        <a href="{{ route('journal.public.home', $journal->slug) }}"
                                                            class="hover:text-blue-600">
                                                            {{ $journal->name }}
                                                        </a>
                                                    </h4>
                                                    <p class="text-sm text-slate-600 mt-1 line-clamp-2">
                                                        {{ Str::limit($journal->description, 120) }}
                                                    </p>
                                                    <div class="flex items-center gap-4 mt-2 text-xs text-slate-500">
                                                        <span>{{ $journal->submissions_count }} artikel</span>
                                                        @if ($journal->abbreviation)
                                                            <span>{{ $journal->abbreviation }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-20 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm mb-4">
                                        <i class="fa-solid fa-book text-3xl text-slate-300"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 mb-2">No journals found</h3>
                                    <p class="text-slate-500 max-w-xs mx-auto">Try using different keywords or checking all categories</p>
                                </div>
                            @endif
                        </div>

                        {{-- ARTICLES ONLY --}}
                        <div x-show="activeTab === 'articles'" x-transition>
                            @if ($articles->isNotEmpty())
                                <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-100">
                                    <h3 class="text-xl font-bold text-slate-900">Articles</h3>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-slate-500">Sort by:</span>
                                        <select name="sort" onchange="this.form.submit()"
                                            class="text-sm border border-slate-300 rounded px-2 py-1 focus:ring-1 focus:ring-blue-500">
                                            <option value="relevance" {{ $sort === 'relevance' ? 'selected' : '' }}>Relevance</option>
                                            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
                                            <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    @foreach ($articles as $article)
                                        <div class="bg-white border border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-lg transition-all group overflow-hidden">
                                            <div class="flex flex-col md:flex-row gap-6">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-3 mb-3">
                                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase tracking-wider rounded border border-emerald-100">Research Article</span>
                                                        <span class="text-xs text-slate-400 font-medium">Published in {{ $article->journal->name }}</span>
                                                    </div>

                                                    <h4 class="text-xl font-bold text-slate-900 leading-snug">
                                                        <a href="{{ route('journal.public.article', [$article->journal->slug, $article->slug]) }}"
                                                            class="hover:text-blue-600 transition-colors">
                                                            {{ $article->title }}
                                                        </a>
                                                    </h4>
                                                    <p class="text-sm text-blue-600 font-medium mt-2 italic">
                                                        by {{ $article->authors->pluck('display_name')->join(', ') }}
                                                    </p>
                                                    <div class="flex flex-wrap items-center gap-4 mt-4 text-[11px] font-bold uppercase tracking-wide text-slate-400">
                                                        <span class="flex items-center gap-1.5"><i class="fa-regular fa-calendar-alt text-blue-500"></i> {{ $article->published_at ? $article->published_at->format('M d, Y') : 'N/A' }}</span>
                                                        <span class="flex items-center gap-1.5"><i class="fa-regular fa-folder text-blue-500"></i> {{ $article->section->title ?? 'General' }}</span>
                                                    </div>

                                                    @if ($article->abstract)
                                                        <p class="text-sm text-slate-600 mt-4 leading-relaxed line-clamp-3">
                                                            {{ Str::limit(strip_tags($article->abstract), 300) }}
                                                        </p>
                                                    @endif
                                                </div>

                                                {{-- Stats column for Articles view --}}
                                                <div class="flex md:flex-col items-center justify-center md:border-l border-slate-100 md:pl-8 min-w-[120px] gap-6 md:gap-4 mt-4 md:mt-0">
                                                    <div class="text-center">
                                                        <div class="text-2xl font-black text-slate-800">{{ number_format($article->views_count) }}</div>
                                                        <div class="text-[10px] uppercase font-heavy text-slate-400 tracking-widest">Article Views</div>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="text-2xl font-black text-slate-800">{{ number_format($article->downloads_count) }}</div>
                                                        <div class="text-[10px] uppercase font-heavy text-slate-400 tracking-widest">Downloads</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Pagination --}}
                                @if ($articles->hasPages())
                                    <div class="mt-8">
                                        {{ $articles->appends(request()->query())->links() }}
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-20 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm mb-4">
                                        <i class="fa-solid fa-file-alt text-3xl text-slate-300"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 mb-2">No articles found</h3>
                                    <p class="text-slate-500 max-w-xs mx-auto">Try broadening your search criteria or checking the filters</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- ============================================ --}}
            {{-- EMPTY STATE --}}
            {{-- ============================================ --}}
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-50 text-blue-600 rounded-2xl mb-8">
                        <i class="fa-solid fa-magnifying-glass text-4xl"></i>
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 mb-4">Search Journals & Research</h2>
                    <p class="text-slate-500 text-lg mb-10 max-w-2xl mx-auto font-medium">
                        Access thousands of peer-reviewed articles and international journals across multiple disciplines. 
                        Search by title, keywords, authors, or digital object identifiers.
                    </p>

                    {{-- Popular Searches --}}
                    @if($popularKeywords->isNotEmpty())
                    <div class="mb-8">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Popular Keywords</h3>
                        <div class="flex flex-wrap justify-center gap-3">
                            @foreach($popularKeywords as $kw)
                                <a href="{{ route('portal.search', ['q' => $kw->content]) }}"
                                    class="px-5 py-2 bg-white border border-slate-200 text-slate-700 rounded-full text-sm font-semibold hover:border-blue-500 hover:text-blue-600 hover:shadow-md transition-all">
                                    <i class="fa-solid fa-hashtag text-[10px] opacity-40 mr-1.5"></i>
                                    {{ ucwords($kw->content) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
