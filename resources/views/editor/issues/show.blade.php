@php
    $journal = current_journal();
@endphp

@extends('layouts.app')

@section('title', 'Issue: ' . $issue->identifier)

@section('content')
    <div x-data="{
        showAddArticleModal: false,
        showReorderModal: false,
        modalX: 0,
        modalY: 0,
        isDragging: false,
        dragStartX: 0,
        dragStartY: 0,
        startDrag(e) {
            // Only start dragging if not clicking on buttons or inputs
            if (e.target.closest('button') || e.target.closest('input')) return;
            this.isDragging = true;
            this.dragStartX = e.clientX - this.modalX;
            this.dragStartY = e.clientY - this.modalY;
        },
        drag(e) {
            if (!this.isDragging) return;
            this.modalX = e.clientX - this.dragStartX;
            this.modalY = e.clientY - this.dragStartY;
        },
        stopDrag() {
            this.isDragging = false;
        },
        openReorderModal() {
            this.modalX = 0;
            this.modalY = 0;
            this.showReorderModal = true;
        },
        initSortable(el) {
            if (!el) return;
            // Wait for Sortable to be available
            const init = () => {
                if (typeof Sortable !== 'undefined') {
                    new Sortable(el, {
                        animation: 150,
                        handle: '.drag-handle',
                        ghostClass: 'opacity-50'
                    });
                } else {
                    setTimeout(init, 50);
                }
            };
            init();
        },
        selectedArticles: [],
        toggleArticle(id) {
            const index = this.selectedArticles.indexOf(id);
            if (index === -1) {
                this.selectedArticles.push(id);
            } else {
                this.selectedArticles.splice(index, 1);
            }
        },
        activeTab: '{{ session("activeTab", "toc") }}'
    }"
    @mousemove.window="drag"
    @mouseup.window="stopDrag"
    class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                    <a href="{{ route('journal.issues.index', ['journal' => $journal->slug]) }}"
                        class="hover:text-indigo-600 transition-colors">Issues</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-gray-900">{{ $issue->identifier }}</span>
                </div>

                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <!-- Issue Info -->
                    <div class="flex items-start gap-6">
                        <!-- Cover -->
                        <div
                            class="w-32 h-44 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-xl overflow-hidden shadow-lg flex-shrink-0">
                            @if ($issue->cover_path)
                                <img src="{{ Storage::disk('public')->url($issue->cover_path) }}" alt="{{ $issue->display_title }}"
                                    class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-indigo-600">
                                    <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h1 class="text-2xl font-bold text-gray-900">{{ $issue->identifier }}</h1>
                                @if ($issue->is_published)
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Published
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5 animate-pulse"></span>
                                        Upcoming
                                    </span>
                                @endif
                            </div>

                            @if ($issue->title)
                                <p class="text-lg text-gray-600 mb-3">{{ $issue->title }}</p>
                            @endif

                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ $issue->submissions->count() }} articles
                                </span>
                                @if ($issue->published_at)
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Published {{ $issue->published_at->format('M d, Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-3">
                        <form action="{{ route('journal.issues.destroy', ['journal' => $journal->slug, 'issue' => $issue]) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this issue?')"
                                class="inline-flex items-center px-4 py-2 bg-white border border-red-200 rounded-lg text-red-600 font-medium hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete Issue
                            </button>
                        </form>

                        @if ($issue->is_published)
                            <form
                                action="{{ route('journal.issues.unpublish', ['journal' => $journal->slug, 'issue' => $issue]) }}"
                                method="POST">
                                @csrf
                                <button type="submit"
                                    onclick="return confirm('Are you sure you want to unpublish this issue?')"
                                    class="inline-flex items-center px-4 py-2 bg-orange-50 border border-orange-200 rounded-lg text-orange-700 font-medium hover:bg-orange-100 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                    Unpublish
                                </button>
                            </form>
                            <a href="{{ route('journal.public.issue', ['journal' => $journal->slug, 'issue' => $issue->seq_id]) }}"
                                target="_blank"
                                class="inline-flex items-center px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 font-medium hover:bg-emerald-100 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                View Public
                            </a>
                        @else
                            <form
                                action="{{ route('journal.issues.publish', ['journal' => $journal->slug, 'issue' => $issue]) }}"
                                method="POST">
                                @csrf
                                <button type="submit"
                                    onclick="return confirm('Publish this issue? All assigned articles will also be published.')"
                                    class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg font-medium shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:bg-emerald-700 transition-all">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Publish Issue
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                    <button @click="activeTab = 'toc'"
                        :class="activeTab === 'toc' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Table of Contents
                    </button>
                    <button @click="activeTab = 'data'"
                        :class="activeTab === 'data' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Issue Data
                    </button>
                    <button @click="activeTab = 'galleys'"
                        :class="activeTab === 'galleys' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Issue Galleys
                    </button>
                    <button @click="activeTab = 'identifiers'"
                        :class="activeTab === 'identifiers' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Identifiers
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div>
                <!-- TAB: Table of Contents -->
                <div x-show="activeTab === 'toc'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Table of Contents (Main Column) -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Table of Contents</h2>
                                <p class="text-sm text-gray-500">{{ $issue->submissions->count() }} articles in this issue
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($issue->submissions->count() > 0)
                                    <button @click="openReorderModal()"
                                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                        Urutkan Artikel
                                    </button>
                                @endif
                                @if (!$issue->is_published)
                                    <button @click="showAddArticleModal = true"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add Article
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if ($issue->submissions->count() > 0)
                            <div class="divide-y divide-gray-100">
                                @foreach ($articlesBySection as $sectionName => $articles)
                                    <div class="p-6">
                                        <!-- Section Header -->
                                        <h3 class="text-sm font-semibold text-indigo-600 uppercase tracking-wider mb-4">
                                            {{ $sectionName }}
                                        </h3>

                                        <!-- Articles in Section -->
                                        <div class="space-y-4">
                                            @foreach ($articles as $index => $article)
                                                <div
                                                    class="group relative bg-gradient-to-r from-gray-50 to-white rounded-xl p-4 border border-gray-100 hover:border-indigo-200 hover:shadow-md transition-all">
                                                    <div class="flex items-start gap-4">
                                                        <!-- Article Number -->
                                                        <div
                                                            class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-700 font-bold text-sm">
                                                            {{ $loop->parent->iteration }}.{{ $loop->iteration }}
                                                        </div>

                                                        <!-- Article Info -->
                                                        <div class="flex-1 min-w-0">
                                                            <a href="{{ route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $article]) }}"
                                                                class="text-base font-semibold text-gray-900 hover:text-indigo-600 transition-colors line-clamp-2">
                                                                {{ $article->title }}
                                                            </a>

                                                            <div
                                                                class="flex items-center gap-2 mt-2 text-sm text-gray-500">
                                                                <span>{{ $article->authors->pluck('name')->join(', ') }}</span>
                                                            </div>

                                                            @if ($article->keywords)
                                                                <div class="flex flex-wrap gap-1 mt-2">
                                                                    @foreach (array_slice($article->keywords_array, 0, 3) as $keyword)
                                                                        <span
                                                                            class="inline-block px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">
                                                                            {{ $keyword }}
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Status, Pages & Actions -->
                                                        <div class="flex-shrink-0 flex flex-col items-end gap-2">
                                                            <div class="flex items-center gap-2">
                                                                @if ($article->status === 'published')
                                                                    <span
                                                                        class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-medium">
                                                                        Published
                                                                    </span>
                                                                @else
                                                                    <span
                                                                        class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-xs font-medium">
                                                                        {{ $article->status_label }}
                                                                    </span>
                                                                @endif

                                                                @if (!$issue->is_published)
                                                                    <form
                                                                        action="{{ route('journal.issues.remove-article', ['journal' => $journal->slug, 'issue' => $issue, 'submission' => $article]) }}"
                                                                        method="POST"
                                                                        class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                            onclick="return confirm('Remove this article from the issue?')"
                                                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                                            title="Remove from issue">
                                                                            <svg class="w-4 h-4" fill="none"
                                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round" stroke-width="2"
                                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                            </svg>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>

                                                            @php
                                                                $pages = ($article->currentPublication ?? $article)->pages;
                                                            @endphp
                                                            @if ($pages)
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 border border-indigo-100/50 text-indigo-700 rounded-lg text-xs font-mono font-bold shadow-sm">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                                    </svg>
                                                                    {{ $pages }}
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center px-2.5 py-1 bg-slate-50 border border-slate-100 text-slate-400 rounded-lg text-xs font-mono italic">
                                                                    No pages
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-12 text-center">
                                <div
                                    class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Articles Yet</h3>
                                <p class="text-gray-500 mb-6">Start adding accepted submissions to this issue.</p>
                                @if (!$issue->is_published)
                                    <button @click="showAddArticleModal = true"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add First Article
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Issue Details Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Issue Details</h3>

                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Volume</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ $issue->volume }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Number</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ $issue->number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Year</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ $issue->year }}</dd>
                            </div>
                            @if ($issue->title)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Title</dt>
                                    <dd class="text-gray-900">{{ $issue->title }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    @if ($issue->is_published)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                                            Published
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                            Unpublished
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            @if ($issue->published_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Published Date</dt>
                                    <dd class="text-gray-900">{{ $issue->published_at->format('F d, Y') }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="text-gray-900">{{ $issue->created_at->format('F d, Y') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Quick Stats Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Article Statistics</h3>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                <span class="text-sm text-gray-600">Total Articles</span>
                                <span class="text-lg font-bold text-gray-900">{{ $issue->submissions->count() }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-xl">
                                <span class="text-sm text-emerald-700">Published</span>
                                <span class="text-lg font-bold text-emerald-700">
                                    {{ $issue->submissions->where('status', 'published')->count() }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-xl">
                                <span class="text-sm text-yellow-700">Pending</span>
                                <span class="text-lg font-bold text-yellow-700">
                                    {{ $issue->submissions->where('status', '!=', 'published')->count() }}
                                </span>
                            </div>
                        </div>

                        @if (!$issue->is_published && $issue->submissions->where('status', 'accepted')->count() > 0)
                            <div class="mt-4 p-3 bg-blue-50 rounded-xl">
                                <p class="text-sm text-blue-700">
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $issue->submissions->where('status', 'accepted')->count() }} article(s) will be
                                    published when you publish this issue.
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Danger Zone -->
                    @if (!$issue->is_published && $issue->submissions->count() === 0)
                        <div class="bg-red-50 rounded-2xl border border-red-100 p-6">
                            <h3 class="text-lg font-bold text-red-900 mb-4">Danger Zone</h3>
                            <p class="text-sm text-red-700 mb-4">Delete this issue permanently. This action cannot be
                                undone.</p>
                            <form
                                action="{{ route('journal.issues.destroy', ['journal' => $journal->slug, 'issue' => $issue]) }}"
                                method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Are you sure you want to delete this issue? This action cannot be undone.')"
                                    class="w-full px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors">
                                    Delete Issue
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                <!-- End Sidebar -->
            </div>
            <!-- End TAB: Table of Contents -->

            <!-- TAB: Issue Data -->
                <div x-show="activeTab === 'data'" x-cloak>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <form action="{{ route('journal.issues.update', ['journal' => $journal->slug, 'issue' => $issue]) }}" method="POST" enctype="multipart/form-data"
                            x-data="{
                                volume: '{{ old('volume', $issue->volume) }}',
                                number: '{{ old('number', $issue->number) }}',
                                year: '{{ old('year', $issue->year) }}',
                                title: '{{ old('title', $issue->title) }}',
                                showTitle: {{ old('show_title', $issue->show_title ?? false) ? 'true' : 'false' }},
                                urlPath: '{{ old('url_path', $issue->url_path) }}',
                                manualUrlPath: {{ old('url_path', $issue->url_path) ? 'true' : 'false' }},
                                generateSlug() {
                                    if (this.manualUrlPath) return;
                                    let slug = '';
                                    if (this.showTitle && this.title) {
                                        slug = this.title;
                                    } else {
                                        slug = 'v' + (this.volume || '1') + '-n' + (this.number || '1') + '-' + (this.year || new Date().getFullYear());
                                    }
                                    this.urlPath = slug.toLowerCase().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-').replace(/^-+|-+$/g, '');
                                }
                            }">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="active_tab" value="data">

                            <div class="p-6 space-y-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                        </svg>
                                        Issue Identification
                                    </h3>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                                        <div>
                                            <label for="volume" class="block text-sm font-medium text-gray-700 mb-1">Volume <span class="text-red-500">*</span></label>
                                            <input type="number" id="volume" name="volume" x-model="volume" @input="generateSlug" min="1" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            @error('volume')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label for="number" class="block text-sm font-medium text-gray-700 mb-1">Number <span class="text-red-500">*</span></label>
                                            <input type="number" id="number" name="number" x-model="number" @input="generateSlug" min="1" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            @error('number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year <span class="text-red-500">*</span></label>
                                            <input type="number" id="year" name="year" x-model="year" @input="generateSlug" min="2000" max="2100" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            @error('year')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-6 mb-6 text-sm text-gray-600">
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="show_volume" value="1" {{ old('show_volume', $issue->show_volume ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            Show Volume
                                        </label>
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="show_number" value="1" {{ old('show_number', $issue->show_number ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            Show Number
                                        </label>
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="show_year" value="1" {{ old('show_year', $issue->show_year ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            Show Year
                                        </label>
                                    </div>

                                    <div class="mb-4">
                                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title (Optional)</label>
                                        <input type="text" id="title" name="title" x-model="title" @input="generateSlug" placeholder="e.g. Special Issue on Technology" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="mb-6 text-sm text-gray-600">
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="show_title" value="1" x-model="showTitle" @change="generateSlug" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            Show Title in Issue Identification
                                        </label>
                                    </div>

                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                                        <textarea id="description" name="description" rows="5" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 tinymce-editor">{{ old('description', $issue->description) }}</textarea>
                                        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <hr class="border-gray-100">

                                <!-- Cover Image -->
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Cover Image
                                    </h3>

                                    <div class="flex items-start gap-6">
                                        @if($issue->cover_path)
                                            <div class="w-32 h-44 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0 relative group">
                                                <img src="{{ Storage::disk('public')->url($issue->cover_path) }}" alt="Cover" class="w-full h-full object-cover">
                                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <button type="button" onclick="if(confirm('Delete cover?')) { 
                                                        fetch('{{ route('journal.issues.cover.delete', ['journal' => $journal->slug, 'issue' => $issue]) }}', {
                                                            method: 'DELETE',
                                                            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                                                        }).then(() => window.location.reload())
                                                    }" class="bg-red-600 text-white rounded-full p-2 hover:bg-red-700">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="flex-1">
                                            <input type="file" name="cover" id="cover" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                            <p class="mt-2 text-xs text-gray-500">Recommended size: 600x800px. Max size: 2MB. Formats: JPG, PNG.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- End TAB: Issue Data -->

                <!-- TAB: Issue Galleys -->
                <div x-show="activeTab === 'galleys'" x-cloak>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Issue Galleys</h2>
                                <p class="text-sm text-gray-500">Upload full-issue galleys (e.g., complete issue PDF).</p>
                            </div>
                        </div>

                        <!-- Galley List -->
                        <div class="divide-y divide-gray-100">
                            @forelse($issue->issueGalleys as $galley)
                                <div class="p-6 flex items-center justify-between hover:bg-gray-50 transition-colors" x-data="{ editing: false }">
                                    <div class="flex items-center gap-4 flex-1">
                                        <div class="w-10 h-10 rounded-lg bg-red-100 flex flex-shrink-0 items-center justify-center text-red-600">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </div>
                                        <div x-show="!editing">
                                            <h4 class="font-medium text-gray-900">{{ $galley->label }}</h4>
                                            <p class="text-sm text-gray-500">{{ $galley->original_file_name }}</p>
                                        </div>
                                        <div x-show="editing" x-cloak class="flex-1 max-w-md">
                                            <form action="{{ route('journal.issues.galleys.update', ['journal' => $journal->slug, 'issue' => $issue, 'galley' => $galley]) }}" method="POST" class="flex items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="label" value="{{ $galley->label }}" required class="flex-1 px-3 py-1.5 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">Save</button>
                                                <button type="button" @click="editing = false" class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Cancel</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" @click="editing = !editing" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <a href="{{ Storage::disk('public')->url($galley->file_path) }}" target="_blank" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <form action="{{ route('journal.issues.galleys.delete', ['journal' => $journal->slug, 'issue' => $issue, 'galley' => $galley]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this galley?')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="p-12 text-center text-gray-500">
                                    <p>No issue galleys uploaded yet.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Upload Form -->
                        <div class="p-6 bg-gray-50 border-t border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-900 mb-4">Upload New Galley</h3>
                            <form action="{{ route('journal.issues.galleys.upload', ['journal' => $journal->slug, 'issue' => $issue]) }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-4">
                                @csrf
                                <input type="hidden" name="active_tab" value="galleys">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Galley Label <span class="text-red-500">*</span></label>
                                    <input type="text" name="label" placeholder="e.g. PDF" required class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">File <span class="text-red-500">*</span></label>
                                    <input type="file" name="file" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-lg bg-white">
                                </div>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                                    Upload
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- End TAB: Issue Galleys -->

                <!-- TAB: Identifiers -->
                <div x-show="activeTab === 'identifiers'" x-cloak>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <form action="{{ route('journal.issues.update', ['journal' => $journal->slug, 'issue' => $issue]) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="active_tab" value="identifiers">
                            
                            <!-- Must include other required fields as hidden so validation doesn't fail -->
                            <input type="hidden" name="volume" value="{{ $issue->volume }}">
                            <input type="hidden" name="number" value="{{ $issue->number }}">
                            <input type="hidden" name="year" value="{{ $issue->year }}">

                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                    Public URL & DOI
                                </h3>

                                <div class="space-y-6">
                                    <div>
                                        <label for="url_path" class="block text-sm font-medium text-gray-700 mb-1">Public URL Path</label>
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-500 text-sm">{{ url($journal->slug . '/issue/view/') }}/</span>
                                            <input type="text" id="url_path" name="url_path" value="{{ old('url_path', $issue->url_path) }}" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Leave blank to auto-generate based on Volume/Number/Year.</p>
                                        @error('url_path')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    @if ($journal->doi_prefix)
                                        <div class="mt-8 pt-6 border-t border-gray-100">
                                            <h4 class="text-sm font-bold text-gray-900 mb-4">DOI</h4>
                                            
                                            @if ($issue->doi)
                                                <div class="space-y-4">
                                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                                        <p class="text-gray-900 font-mono text-sm mb-2">{{ $issue->doi }}</p>
                                                        <p class="text-xs text-gray-500">The DOI is assigned to this Issue.</p>
                                                    </div>
                                                    
                                                    <div class="flex gap-2">
                                                        <form action="{{ route('journal.issues.doi.clear', ['journal' => $journal->slug, 'issue' => $issue]) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" onclick="return confirm('Are you sure you want to clear this DOI?')" class="px-4 py-1.5 bg-white border border-pink-500 text-pink-600 rounded-lg text-sm font-bold hover:bg-pink-50 transition-colors">
                                                                Clear
                                                            </button>
                                                        </form>
                                                    </div>

                                                    <div class="mt-6">
                                                        <p class="text-xs text-gray-500 mb-2">Use the following option to clear DOIs of all objects (articles and galleys) currently scheduled for this issue.</p>
                                                        <button type="button" onclick="alert('Not implemented yet.')" class="px-4 py-1.5 bg-white border border-pink-500 text-pink-600 rounded-lg text-sm font-bold hover:bg-pink-50 transition-colors">
                                                            Clear Issue Objects DOIs
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="space-y-4">
                                                    <p class="text-sm text-gray-500">No DOI is currently assigned to this issue.</p>
                                                    <form action="{{ route('journal.issues.doi.assign', ['journal' => $journal->slug, 'issue' => $issue]) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition-colors">
                                                            Assign DOI
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="mt-8 pt-6 border-t border-gray-100">
                                            <h4 class="text-sm font-bold text-gray-900 mb-4">DOI</h4>
                                            <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                                <p class="text-sm text-yellow-700">DOI assignment is not configured for this journal. Please configure the DOI prefix in Journal Settings to enable DOI assignment.</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                    Save Identifiers
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- End TAB: Identifiers -->
            </div>
            <!-- End Tabs Content Wrapper -->
        </div>

        <!-- Add Article Modal -->
        <div x-show="showAddArticleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="showAddArticleModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm transition-opacity"
                    @click="showAddArticleModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div x-show="showAddArticleModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <form
                        action="{{ route('journal.issues.add-articles', ['journal' => $journal->slug, 'issue' => $issue]) }}"
                        method="POST">
                        @csrf

                        <div class="px-6 py-5 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900" id="modal-title">Add Articles to Issue
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">Select accepted submissions to add to this issue
                                    </p>
                                </div>
                                <button type="button" @click="showAddArticleModal = false"
                                    class="text-gray-400 hover:text-gray-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="px-6 py-4 max-h-96 overflow-y-auto">
                            @if ($availableSubmissions->count() > 0)
                                <div class="space-y-3">
                                    @foreach ($availableSubmissions as $submission)
                                        <label
                                            class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 cursor-pointer transition-colors"
                                            :class="{
                                                'ring-2 ring-indigo-500 bg-indigo-50': selectedArticles.includes(
                                                    '{{ $submission->id }}')
                                            }">
                                            <input type="checkbox" name="submission_ids[]" value="{{ $submission->id }}"
                                                @change="toggleArticle('{{ $submission->id }}')"
                                                class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900 line-clamp-2">{{ $submission->title }}
                                                </p>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    {{ $submission->authors->pluck('name')->join(', ') }}
                                                </p>
                                                <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                                    <span
                                                        class="px-2 py-0.5 bg-gray-200 rounded">{{ $submission->section->name ?? 'Uncategorized' }}</span>
                                                    <span>Accepted
                                                        {{ $submission->accepted_at?->format('M d, Y') ?? 'Recently' }}</span>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <div
                                        class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Available Submissions</h3>
                                    <p class="text-gray-500">All accepted submissions have already been assigned to issues.
                                    </p>
                                </div>
                            @endif
                        </div>

                        @if ($availableSubmissions->count() > 0)
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                                <p class="text-sm text-gray-600">
                                    <span x-text="selectedArticles.length"></span> article(s) selected
                                </p>
                                <div class="flex gap-3">
                                    <button type="button" @click="showAddArticleModal = false"
                                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit" :disabled="selectedArticles.length === 0"
                                        :class="{ 'opacity-50 cursor-not-allowed': selectedArticles.length === 0 }"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                        Add Selected
                                    </button>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Reorder Articles Modal -->
        <div x-show="showReorderModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="showReorderModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm transition-opacity"
                    @click="showReorderModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div x-show="showReorderModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    :style="{ transform: 'translate(' + modalX + 'px, ' + modalY + 'px)', transition: isDragging ? 'none' : '' }"
                    class="inline-block align-bottom bg-white rounded-[24px] text-left overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <form
                        action="{{ route('journal.issues.reorder-articles', ['journal' => $journal->slug, 'issue' => $issue]) }}"
                        method="POST">
                        @csrf

                        <!-- Draggable Header -->
                        <div @mousedown="startDrag" class="px-6 py-5 border-b border-slate-100 cursor-move select-none bg-slate-50/50 flex items-center justify-between">
                            <div>
                                <h3 class="text-[22px] font-bold text-slate-900" id="modal-title">Urutkan Artikel</h3>
                                <p class="text-sm font-medium text-slate-600 mt-1">Geser (drag) artikel untuk mengubah urutan di dalam issue ini</p>
                            </div>
                            <button type="button" @click="showReorderModal = false"
                                class="text-slate-400 hover:text-slate-500 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Draggable Content -->
                        <div class="px-6 py-4 max-h-[400px] overflow-y-auto">
                            @if ($issue->submissions->count() > 0)
                                <div class="space-y-6">
                                    @foreach ($articlesBySection as $sectionName => $articles)
                                        <div>
                                            <h4 class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-3">
                                                {{ $sectionName }}
                                            </h4>
                                            
                                            <!-- List for SortableJS -->
                                            <div x-init="initSortable($el)" class="space-y-2">
                                                @foreach ($articles as $article)
                                                    <div data-id="{{ $article->id }}"
                                                        class="flex items-center gap-3 p-3 bg-white border border-slate-100 rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-indigo-200 transition-all">
                                                        
                                                        <!-- Drag handle -->
                                                        <div class="drag-handle cursor-grab active:cursor-grabbing p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M4 6h16M4 12h16M4 18h16" />
                                                            </svg>
                                                        </div>

                                                        <!-- Article info -->
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-semibold text-slate-800 line-clamp-1">
                                                                {{ $article->title }}
                                                            </p>
                                                            <p class="text-xs text-slate-400 italic line-clamp-1 mt-0.5">
                                                                {{ $article->authors->pluck('name')->join(', ') }}
                                                            </p>
                                                        </div>

                                                        <!-- Pages badge -->
                                                        @php
                                                            $pages = ($article->currentPublication ?? $article)->pages;
                                                        @endphp
                                                        @if ($pages)
                                                            <span class="flex-shrink-0 px-2 py-0.5 bg-indigo-50 border border-indigo-100/50 text-indigo-700 rounded text-xs font-mono font-semibold">
                                                                {{ $pages }}
                                                            </span>
                                                        @endif

                                                        <!-- Input hidden to submit the order -->
                                                        <input type="hidden" name="order[]" value="{{ $article->id }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <p class="text-slate-600 font-medium text-sm">Tidak ada artikel di dalam issue ini.</p>
                                </div>
                            @endif
                        </div>

                        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-3">
                            <button type="button" @click="showReorderModal = false"
                                class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-700 font-medium hover:bg-slate-50 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                Simpan Urutan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script src="{{ asset('assets/js/vendors/plugins/tinymce/tinymce.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '.tinymce-editor',
                height: 300,
                menubar: false,
                plugins: 'lists link image table code autoresize',
                toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code',
                branding: false,
                license_key: 'gpl',
                body_class: 'prose prose-slate max-w-none text-slate-700 text-sm leading-relaxed text-justify p-4',
                content_css: '{{ Vite::asset("resources/css/app.css") }}'
            });
        });
    </script>
@endpush
