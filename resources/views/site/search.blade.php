@extends('layouts.portal')

@section('title', 'Hasil Pencarian')

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
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Hasil Pencarian</h1>
                <p class="mt-2 text-slate-500 text-base md:text-lg">
                    @if ($query)
                        Menampilkan hasil untuk "<strong>{{ $query }}</strong>"
                    @else
                        Cari jurnal dan artikel ilmiah
                    @endif
                </p>

                {{-- Search Form --}}
                <div class="mt-6">
                    <form id="searchForm" method="GET" action="{{ route('portal.search') }}"
                        class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <input type="text" name="q" value="{{ $query }}"
                                placeholder="Cari jurnal, artikel, atau penulis..."
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                required>
                        </div>
                        <div class="flex gap-3">
                            <input type="hidden" name="sort" value="{{ $sort }}">
                            <select name="category"
                                class="px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Semua Kategori</option>
                                <option value="journals" {{ $category === 'journals' ? 'selected' : '' }}>Jurnal</option>
                                <option value="articles" {{ $category === 'articles' ? 'selected' : '' }}>Artikel</option>
                            </select>
                            <button type="submit"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm font-medium transition-colors">
                                <i class="fa-solid fa-search mr-2"></i>
                                Cari
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
                                Semua ({{ $journals->count() + $articles->total() }})
                            </button>
                            <button @click="activeTab = 'journals'"
                                :class="activeTab === 'journals' ? 'border-blue-500 text-blue-600' :
                                    'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                                Jurnal ({{ $journals->count() }})
                            </button>
                            <button @click="activeTab = 'articles'"
                                :class="activeTab === 'articles' ? 'border-blue-500 text-blue-600' :
                                    'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                                Artikel ({{ $articles->total() }})
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
                                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Jurnal</h3>
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
                                </div>
                            @endif

                            {{-- Articles Section --}}
                            @if ($articles->isNotEmpty())
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold text-slate-900">Artikel</h3>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-slate-500">Urutkan:</span>
                                            <select name="sort" onchange="this.form.submit()" form="searchForm"
                                                class="text-sm border border-slate-300 rounded px-2 py-1 focus:ring-1 focus:ring-blue-500">
                                                <option value="relevance" {{ $sort === 'relevance' ? 'selected' : '' }}>
                                                    Relevansi</option>
                                                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Terbaru
                                                </option>
                                                <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Terlama
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        @foreach ($articles as $article)
                                            <div
                                                class="bg-white border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                                <div class="flex items-start gap-3">
                                                    <div
                                                        class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                        <i class="fa-solid fa-file-alt text-green-600"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h4 class="font-semibold text-slate-900">
                                                            <a href="{{ route('submission.view', [$article->journal->slug, $article->slug]) }}"
                                                                class="hover:text-blue-600 line-clamp-2">
                                                                {{ $article->title }}
                                                            </a>
                                                        </h4>
                                                        <p class="text-sm text-slate-600 mt-1">
                                                            oleh {{ $article->authors->pluck('display_name')->join(', ') }}
                                                        </p>
                                                        <div class="flex items-center gap-4 mt-2 text-xs text-slate-500">
                                                            <span>{{ $article->journal->name }}</span>
                                                            @if ($article->published_at)
                                                                <span>{{ $article->published_at->format('M Y') }}</span>
                                                            @endif
                                                            @if ($article->section)
                                                                <span>{{ $article->section->title }}</span>
                                                            @endif
                                                        </div>
                                                        @if ($article->abstract)
                                                            <p class="text-sm text-slate-700 mt-2 line-clamp-3">
                                                                {{ Str::limit(strip_tags($article->abstract), 200) }}
                                                            </p>
                                                        @endif
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
                                <div class="text-center py-12">
                                    <i class="fa-solid fa-book text-4xl text-slate-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-slate-900 mb-2">Tidak ada jurnal ditemukan</h3>
                                    <p class="text-slate-500">Coba gunakan kata kunci yang berbeda</p>
                                </div>
                            @endif
                        </div>

                        {{-- ARTICLES ONLY --}}
                        <div x-show="activeTab === 'articles'" x-transition>
                            @if ($articles->isNotEmpty())
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-slate-900">Artikel</h3>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-slate-500">Urutkan:</span>
                                        <select name="sort" onchange="this.form.submit()"
                                            class="text-sm border border-slate-300 rounded px-2 py-1 focus:ring-1 focus:ring-blue-500">
                                            <option value="relevance" {{ $sort === 'relevance' ? 'selected' : '' }}>
                                                Relevansi</option>
                                            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Terbaru
                                            </option>
                                            <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Terlama
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    @foreach ($articles as $article)
                                        <div
                                            class="bg-white border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="fa-solid fa-file-alt text-green-600"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="font-semibold text-slate-900">
                                                        <a href="{{ route('submission.view', [$article->journal->slug, $article->slug]) }}"
                                                            class="hover:text-blue-600 line-clamp-2">
                                                            {{ $article->title }}
                                                        </a>
                                                    </h4>
                                                    <p class="text-sm text-slate-600 mt-1">
                                                        oleh {{ $article->authors->pluck('display_name')->join(', ') }}
                                                    </p>
                                                    <div class="flex items-center gap-4 mt-2 text-xs text-slate-500">
                                                        <span>{{ $article->journal->name }}</span>
                                                        @if ($article->published_at)
                                                            <span>{{ $article->published_at->format('M Y') }}</span>
                                                        @endif
                                                        @if ($article->section)
                                                            <span>{{ $article->section->title }}</span>
                                                        @endif
                                                    </div>
                                                    @if ($article->abstract)
                                                        <p class="text-sm text-slate-700 mt-2 line-clamp-3">
                                                            {{ Str::limit(strip_tags($article->abstract), 200) }}
                                                        </p>
                                                    @endif
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
                                <div class="text-center py-12">
                                    <i class="fa-solid fa-file-alt text-4xl text-slate-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-slate-900 mb-2">Tidak ada artikel ditemukan</h3>
                                    <p class="text-slate-500">Coba gunakan kata kunci yang berbeda</p>
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
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="text-center">
                    <i class="fa-solid fa-search text-6xl text-slate-300 mb-6"></i>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4">Pencarian Jurnal & Artikel</h2>
                    <p class="text-slate-500 text-lg mb-8 max-w-2xl mx-auto">
                        Temukan jurnal ilmiah dan artikel penelitian dari berbagai bidang studi.
                        Gunakan kata kunci untuk mencari judul, abstrak, atau nama penulis.
                    </p>

                    {{-- Popular Searches --}}
                    <div class="mb-8">
                        <h3 class="text-sm font-medium text-slate-700 mb-3">Pencarian Populer:</h3>
                        <div class="flex flex-wrap justify-center gap-2">
                            <a href="{{ route('portal.search', ['q' => 'pendidikan']) }}"
                                class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-sm hover:bg-slate-200 transition-colors">
                                Pendidikan
                            </a>
                            <a href="{{ route('portal.search', ['q' => 'teknologi']) }}"
                                class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-sm hover:bg-slate-200 transition-colors">
                                Teknologi
                            </a>
                            <a href="{{ route('portal.search', ['q' => 'ekonomi']) }}"
                                class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-sm hover:bg-slate-200 transition-colors">
                                Ekonomi
                            </a>
                            <a href="{{ route('portal.search', ['q' => 'kesehatan']) }}"
                                class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-sm hover:bg-slate-200 transition-colors">
                                Kesehatan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
