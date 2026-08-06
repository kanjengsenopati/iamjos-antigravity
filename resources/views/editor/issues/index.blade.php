@php
    $journal = current_journal();
@endphp

@extends('layouts.app')

@section('title', $isId ? 'Manajemen Terbitan' : 'Issue Management')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                <div>
                    <x-text.h1>{{ $isId ? 'Manajemen Terbitan' : 'Issue Management' }}</x-text.h1>
                    <x-text.body class="text-slate-500 mt-1">{{ $isId ? 'Kelola terbitan jurnal dan jadwal publikasi' : 'Manage journal issues and publication schedule' }}</x-text.body>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="{{ route('journal.issues.create', ['journal' => $journal->slug]) }}"
                        class="inline-flex items-center px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium shadow-sm hover:bg-primary-700 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ $isId ? '+ Buat Terbitan Baru' : '+ Create New Issue' }}
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
                <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ $isId ? 'Total Terbitan' : 'Total Issues' }}</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalIssues }}</p>
                        </div>
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ $isId ? 'Terbit' : 'Published' }}</p>
                            <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $publishedCount }}</p>
                        </div>
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ $isId ? 'Mendatang' : 'Upcoming' }}</p>
                            <p class="text-3xl font-bold text-blue-600 mt-1">{{ $upcomingCount }}</p>
                        </div>
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ $isId ? 'Total Artikel' : 'Total Articles' }}</p>
                            <p class="text-3xl font-bold text-purple-600 mt-1">{{ $totalArticles }}</p>
                        </div>
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div x-data="{
                activeTab: '{{ request('tab') === 'back' || request('year') ? 'back' : 'future' }}',
                publishModalOpen: false,
                publishData: { id: '', identifier: '', doi: '', publishUrl: '' },
                openPublishModal(data) {
                    this.publishData = data;
                    this.publishModalOpen = true;
                },
                unpublishModalOpen: false,
                unpublishData: { id: '', identifier: '', actionUrl: '' },
                openUnpublishModal(data) {
                    this.unpublishData = data;
                    this.unpublishModalOpen = true;
                },
                deleteModalOpen: false,
                deleteData: { id: '', identifier: '', actionUrl: '' },
                openDeleteModal(data) {
                    this.deleteData = data;
                    this.deleteModalOpen = true;
                }
            }" class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
                <!-- Tab Headers -->
                <div class="border-b border-slate-200 px-6">
                    <nav class="-mb-px flex space-x-8 overflow-x-auto no-scrollbar" aria-label="Tabs">
                        <button type="button" @click="activeTab = 'future'"
                            :class="activeTab === 'future'
                                ? 'border-primary-600 text-primary-600 font-semibold' 
                                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="flex-shrink-0 border-b-2 py-4 px-1 text-sm font-medium transition-all whitespace-nowrap flex items-center gap-2">
                            <i class="fa-solid fa-calendar text-base transition-colors" :class="activeTab === 'future' ? 'text-primary-600' : 'text-slate-400'"></i>
                            <span>{{ $isId ? 'Terbitan Mendatang' : 'Future Issues' }}</span>
                            <span class="px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider rounded-lg transition-colors"
                                :class="activeTab === 'future' ? 'bg-primary-600/10 text-primary-600' : 'bg-slate-100 text-slate-500'">
                                {{ $upcomingCount }}
                            </span>
                        </button>

                        <button type="button" @click="activeTab = 'back'"
                            :class="activeTab === 'back'
                                ? 'border-primary-600 text-primary-600 font-semibold' 
                                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="flex-shrink-0 border-b-2 py-4 px-1 text-sm font-medium transition-all whitespace-nowrap flex items-center gap-2">
                            <i class="fa-solid fa-box-archive text-base transition-colors" :class="activeTab === 'back' ? 'text-primary-600' : 'text-slate-400'"></i>
                            <span>{{ $isId ? 'Terbitan Lalu' : 'Back Issues' }}</span>
                            <span class="px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider rounded-lg transition-colors"
                                :class="activeTab === 'back' ? 'bg-primary-600/10 text-primary-600' : 'bg-slate-100 text-slate-500'">
                                {{ $publishedCount }}
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content: Future Issues -->
                <div x-show="activeTab === 'future'" x-cloak class="p-6">
                    @if ($futureIssues->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($futureIssues as $issue)
                                <div
                                    class="group bg-gradient-to-br from-white to-gray-50 rounded-[24px] border border-gray-200 overflow-hidden hover:shadow-lg hover:border-primary-200 transition-all duration-300">
                                    <!-- Cover Image -->
                                    <div
                                        class="aspect-[3/4] bg-gradient-to-br from-blue-50 to-blue-100 relative overflow-hidden">
                                        @if ($issue->cover_path)
                                            <img src="{{ Storage::disk('public')->url($issue->cover_path) }}"
                                                alt="{{ $issue->display_title }}"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        @else
                                            <div
                                                class="w-full h-full flex flex-col items-center justify-center text-primary-600">
                                                <svg class="w-16 h-16 opacity-50" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                                <span class="text-sm mt-2 opacity-50">{{ $isId ? 'Tidak ada sampul' : 'No Cover' }}</span>
                                            </div>
                                        @endif

                                        <!-- Status Badge -->
                                        <div class="absolute top-3 right-3">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                <span
                                                    class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5 animate-pulse"></span>
                                                {{ $isId ? 'Mendatang' : 'Upcoming' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="p-4">
                                        <h3 class="font-bold text-gray-900 text-lg mb-1">{{ $issue->identifier }}</h3>
                                        @if ($issue->title)
                                            <p class="text-gray-600 text-sm mb-3">{{ $issue->title }}</p>
                                        @endif

                                        <div class="flex items-center text-sm text-gray-500 mb-4">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            {{ $issue->submissions_count }} {{ $isId ? 'artikel' : 'articles' }}
                                        </div>

                                        <!-- 4 OJS Actions Bar -->
                                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-1.5 flex-wrap text-xs">
                                            <!-- 1. Edit -->
                                            <a href="{{ route('journal.issues.show', ['journal' => $journal->slug, 'issue' => $issue]) }}"
                                                class="text-indigo-600 hover:text-indigo-800 font-semibold transition-colors inline-flex items-center gap-1">
                                                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                                <span>Edit</span>
                                            </a>

                                            <!-- 2. Preview -->
                                            <a href="{{ route('journal.public.issue', ['journal' => $journal->slug, 'issue' => $issue->seq_id]) }}?preview=1"
                                                target="_blank"
                                                class="text-slate-600 hover:text-slate-900 font-semibold transition-colors inline-flex items-center gap-1">
                                                <i class="fa-solid fa-eye text-[11px]"></i>
                                                <span>Preview</span>
                                            </a>

                                            <!-- 3. Publish Issue -->
                                            <button type="button"
                                                @click="openPublishModal({
                                                    id: '{{ $issue->id }}',
                                                    identifier: '{{ addslashes($issue->identifier) }}',
                                                    doi: '{{ addslashes($issue->doi ?? \App\Services\DoiService::generateForIssue($issue, $journal) ?? '') }}',
                                                    publishUrl: '{{ route('journal.issues.publish', ['journal' => $journal->slug, 'issue' => $issue]) }}'
                                                })"
                                                class="text-emerald-600 hover:text-emerald-800 font-semibold transition-colors inline-flex items-center gap-1">
                                                <i class="fa-solid fa-upload text-[11px]"></i>
                                                <span>Publish Issue</span>
                                            </button>

                                            <!-- 4. Delete -->
                                            <form action="{{ route('journal.issues.destroy', ['journal' => $journal->slug, 'issue' => $issue]) }}"
                                                method="POST" class="inline"
                                                onsubmit="return confirm('{{ $isId ? 'Hapus terbitan ini secara permanen?' : 'Delete this issue permanently?' }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold transition-colors inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-trash-can text-[11px]"></i>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $isId ? 'Tidak Ada Terbitan Mendatang' : 'No Future Issues' }}</h3>
                            <p class="text-gray-500 mb-6">{{ $isId ? 'Buat terbitan baru untuk mulai menjadwalkan artikel untuk publikasi.' : 'Create a new issue to start scheduling articles for publication.' }}
                            </p>
                            <a href="{{ route('journal.issues.create', ['journal' => $journal->slug]) }}"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                {{ $isId ? 'Buat Terbitan Pertama' : 'Create First Issue' }}
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Tab Content: Back Issues (Published) -->
                <div x-show="activeTab === 'back'" x-cloak class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-box-archive text-primary-600"></i>
                            <span>{{ $isId ? 'Terbitan Terbit' : 'Published Back Issues' }}</span>
                        </h3>
                        
                        <!-- Filter Year Issue -->
                        @if ($availableYears->isNotEmpty())
                            <div class="flex items-center gap-2">
                                <label for="yearFilter" class="text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                                    <i class="fa-solid fa-filter text-slate-400 mr-1"></i>{{ $isId ? 'Filter Tahun Issue' : 'Filter Year Issue' }}
                                </label>
                                <select id="yearFilter" onchange="window.location.href = this.value"
                                    class="text-xs font-semibold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 focus:ring-2 focus:ring-primary-500 shadow-2xs">
                                    <option value="{{ route('journal.issues.index', ['journal' => $journal->slug, 'tab' => 'back']) }}">
                                        {{ $isId ? 'Semua Tahun' : 'All Years' }}
                                    </option>
                                    @foreach ($availableYears as $yr)
                                        <option value="{{ route('journal.issues.index', ['journal' => $journal->slug, 'tab' => 'back', 'year' => $yr]) }}"
                                            {{ (string)$selectedYear === (string)$yr ? 'selected' : '' }}>
                                            {{ $yr }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    @if ($backIssues->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($backIssues as $issue)
                                <div
                                    class="group bg-gradient-to-br from-white to-gray-50 rounded-[24px] border border-gray-200 overflow-hidden hover:shadow-lg hover:border-emerald-200 transition-all duration-300">
                                    <!-- Cover Image -->
                                    <div
                                        class="aspect-[3/4] bg-gradient-to-br from-emerald-50 to-emerald-100 relative overflow-hidden">
                                        @if ($issue->cover_path)
                                            <img src="{{ Storage::disk('public')->url($issue->cover_path) }}"
                                                alt="{{ $issue->display_title }}"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        @else
                                            <div
                                                class="w-full h-full flex flex-col items-center justify-center text-emerald-600">
                                                <svg class="w-16 h-16 opacity-50" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                                <span class="text-sm mt-2 opacity-50">{{ $isId ? 'Tidak ada sampul' : 'No Cover' }}</span>
                                            </div>
                                        @endif

                                        <!-- Status Badges Overlay on Cover Image -->
                                        <div class="absolute top-3 right-3 flex flex-col items-end gap-1.5 z-10">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/90 text-white backdrop-blur-md shadow-xs border border-emerald-400/40">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                                {{ $isId ? 'Terbit' : 'Published' }}
                                            </span>

                                            <!-- Prominent Premium Current Issue Badge directly on Cover Image -->
                                            @if ($currentIssue && $currentIssue->id === $issue->id)
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-extrabold bg-blue-600/95 text-white backdrop-blur-md shadow-md shadow-blue-900/30 border border-blue-400/40">
                                                    <i class="fa-solid fa-star text-yellow-300 text-xs"></i>
                                                    <span>Current Issue</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="p-4">
                                        <h3 class="font-bold text-gray-900 text-lg mb-1">{{ $issue->identifier }}</h3>
                                        @if ($issue->title)
                                            <p class="text-gray-600 text-sm mb-2">{{ $issue->title }}</p>
                                        @endif

                                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                   {{ $issue->submissions_count }} {{ $isId ? 'artikel' : 'articles' }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ $issue->published_at?->format('M d, Y') }}
                                            </span>
                                        </div>

                                        <!-- OJS Actions Bar -->
                                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-1.5 flex-wrap text-xs">
                                            <!-- 1. Edit -->
                                            <a href="{{ route('journal.issues.show', ['journal' => $journal->slug, 'issue' => $issue]) }}"
                                                class="text-indigo-600 hover:text-indigo-800 font-semibold transition-colors inline-flex items-center gap-1">
                                                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                                <span>Edit</span>
                                            </a>

                                            <!-- 2. Preview -->
                                            <a href="{{ route('journal.public.issue', ['journal' => $journal->slug, 'issue' => $issue->seq_id]) }}"
                                                target="_blank"
                                                class="text-slate-600 hover:text-slate-900 font-semibold transition-colors inline-flex items-center gap-1">
                                                <i class="fa-solid fa-eye text-[11px]"></i>
                                                <span>Preview</span>
                                            </a>

                                            <!-- 3. Unpublish Issue -->
                                            <button type="button"
                                                @click="openUnpublishModal({
                                                    id: '{{ $issue->id }}',
                                                    identifier: '{{ addslashes($issue->identifier) }}',
                                                    actionUrl: '{{ route('journal.issues.unpublish', ['journal' => $journal->slug, 'issue' => $issue]) }}'
                                                })"
                                                class="text-amber-600 hover:text-amber-800 font-semibold transition-colors inline-flex items-center gap-1">
                                                <i class="fa-solid fa-rotate-left text-[11px]"></i>
                                                <span>Unpublish</span>
                                            </button>

                                            <!-- 4. Current Issue Action (Shown only if NOT already Current Issue) -->
                                            @if (!$currentIssue || $currentIssue->id !== $issue->id)
                                                <form action="{{ route('journal.issues.current', ['journal' => $journal->slug, 'issue' => $issue]) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-blue-600 hover:text-blue-800 font-semibold transition-colors inline-flex items-center gap-1" title="{{ $isId ? 'Tetapkan sebagai terbitan terkini' : 'Set as current issue' }}">
                                                        <i class="fa-regular fa-star text-[11px]"></i>
                                                        <span>Current Issue</span>
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- 5. Delete -->
                                            <button type="button"
                                                @click="openDeleteModal({
                                                    id: '{{ $issue->id }}',
                                                    identifier: '{{ addslashes($issue->identifier) }}',
                                                    actionUrl: '{{ route('journal.issues.destroy', ['journal' => $journal->slug, 'issue' => $issue]) }}'
                                                })"
                                                class="text-rose-600 hover:text-rose-800 font-semibold transition-colors inline-flex items-center gap-1">
                                                <i class="fa-solid fa-trash-can text-[11px]"></i>
                                                <span>Delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if ($backIssues->hasPages())
                            <div class="mt-8">
                                {{ $backIssues->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $isId ? 'Tidak Ada Terbitan Terbit' : 'No Published Issues' }}</h3>
                        </div>
                    @endif
                </div>

                <!-- ====== PUBLISH ISSUE MODAL (PAKRT PREMIUM NATIVE - 24PX RADIUS, COMPACT) ====== -->
                <div x-show="publishModalOpen" x-cloak class="fixed z-50 inset-0 overflow-y-auto" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                        <!-- Backdrop -->
                        <div x-show="publishModalOpen"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            class="fixed inset-0 bg-slate-950/40 backdrop-blur-md transition-opacity"
                            @click="publishModalOpen = false"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <!-- Modal Dialog Container (rounded-[24px], compact sm:max-w-md) -->
                        <div x-show="publishModalOpen"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            class="relative inline-block align-middle bg-white rounded-[24px] text-left overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.12)] transform transition-all sm:my-8 sm:max-w-md sm:w-full border border-slate-100">
                            
                            <!-- Modal Header -->
                            <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 bg-slate-50/60">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm shadow-2xs">
                                        <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                                    </div>
                                    <x-text.h2 class="!text-sm !font-bold !text-slate-900">
                                        Publish Issue
                                    </x-text.h2>
                                </div>
                                <button type="button" @click="publishModalOpen = false"
                                    class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 transition-colors flex items-center justify-center">
                                    <i class="fa-solid fa-xmark text-sm"></i>
                                </button>
                            </div>

                            <!-- Modal Form -->
                            <form :action="publishData.publishUrl" method="POST">
                                @csrf
                                <div class="p-5 space-y-4">
                                    <!-- Option Checkbox: Send email -->
                                    <label for="send_email"
                                        class="group flex items-center gap-3 bg-slate-50/80 hover:bg-slate-100/60 border border-slate-200/80 rounded-xl p-3 cursor-pointer transition-all">
                                        <input type="checkbox" id="send_email" name="send_email" value="1" checked
                                            class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500 cursor-pointer">
                                        <span class="text-xs font-semibold text-slate-800 group-hover:text-slate-900 leading-tight">
                                            Send an email about this to all registered users.
                                        </span>
                                    </label>

                                    <!-- Confirmation Message -->
                                    <p class="text-xs font-semibold text-slate-700 px-0.5 leading-relaxed">
                                        Are you sure you want to publish the new issue?
                                    </p>

                                    <!-- DOI Section (Matching OJS Image 3) -->
                                    <div class="pt-3 border-t border-slate-100">
                                        <x-text.label class="mb-1 text-[10px] text-slate-400 font-bold uppercase tracking-wider block">
                                            DOI
                                        </x-text.label>
                                        <template x-if="publishData.doi">
                                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 flex items-center gap-2">
                                                <i class="fa-solid fa-link text-emerald-600 text-xs"></i>
                                                <p class="text-xs text-slate-600 leading-normal">
                                                    The DOI <span class="font-mono font-bold text-slate-900 bg-white px-1.5 py-0.5 rounded border border-slate-200" x-text="publishData.doi"></span> has been assigned.
                                                </p>
                                            </div>
                                        </template>
                                        <template x-if="!publishData.doi">
                                            <p class="text-xs text-slate-400 italic">
                                                No DOI configured for this issue.
                                            </p>
                                        </template>
                                    </div>
                                </div>

                                <!-- Modal Footer -->
                                <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                    <button type="button" @click="publishModalOpen = false"
                                        class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 bg-white border border-slate-200/80 rounded-xl hover:bg-slate-100/80 transition-all shadow-2xs">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] rounded-xl transition-all shadow-md shadow-emerald-600/20">
                                        OK
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ====== UNPUBLISH ISSUE MODAL (PAKRT PREMIUM NATIVE - CENTER ALIGNED 24PX) ====== -->
                <div x-show="unpublishModalOpen" x-cloak class="fixed z-50 inset-0 overflow-y-auto" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen p-4 text-center">
                        <!-- Backdrop -->
                        <div x-show="unpublishModalOpen"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            class="fixed inset-0 bg-slate-950/40 backdrop-blur-md transition-opacity"
                            @click="unpublishModalOpen = false"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <!-- Modal Dialog Container (rounded-[24px], compact sm:max-w-md, centered) -->
                        <div x-show="unpublishModalOpen"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4 sm:translate-y-0" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                            class="relative inline-block align-middle bg-white rounded-[24px] text-left overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.12)] transform transition-all my-auto sm:max-w-md sm:w-full border border-slate-100">
                            
                            <!-- Modal Header -->
                            <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 bg-amber-50/60">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-amber-100/80 text-amber-700 flex items-center justify-center font-bold text-sm shadow-2xs">
                                        <i class="fa-solid fa-rotate-left text-sm"></i>
                                    </div>
                                    <x-text.h2 class="!text-sm !font-bold !text-slate-900">
                                        Unpublish Issue
                                    </x-text.h2>
                                </div>
                                <button type="button" @click="unpublishModalOpen = false"
                                    class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 transition-colors flex items-center justify-center">
                                    <i class="fa-solid fa-xmark text-sm"></i>
                                </button>
                            </div>

                            <!-- Modal Form -->
                            <form :action="unpublishData.actionUrl" method="POST">
                                @csrf
                                <div class="p-5 space-y-3">
                                    <div class="p-3.5 bg-amber-50/80 border border-amber-200/60 rounded-xl flex items-start gap-3">
                                        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-sm mt-0.5 flex-shrink-0"></i>
                                        <div class="text-xs text-amber-900 leading-relaxed">
                                            <span class="font-bold block mb-0.5 text-amber-950" x-text="unpublishData.identifier"></span>
                                            {{ $isId ? 'Batalkan publikasi terbitan ini? Status seluruh artikel di dalam terbitan ini akan dikembalikan ke status Diterima (Accepted).' : 'Unpublish this issue? Article status will be reverted to Accepted status.' }}
                                        </div>
                                    </div>

                                    <p class="text-xs font-semibold text-slate-700 px-0.5 leading-relaxed">
                                        {{ $isId ? 'Apakah Anda yakin ingin membatalkan publikasi terbitan ini?' : 'Are you sure you want to unpublish this issue?' }}
                                    </p>
                                </div>

                                <!-- Modal Footer -->
                                <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                    <button type="button" @click="unpublishModalOpen = false"
                                        class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 bg-white border border-slate-200/80 rounded-xl hover:bg-slate-100/80 transition-all shadow-2xs">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 active:scale-[0.98] rounded-xl transition-all shadow-md shadow-amber-600/20">
                                        Unpublish Issue
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ====== DELETE ISSUE MODAL (PAKRT PREMIUM NATIVE - CENTER ALIGNED 24PX) ====== -->
                <div x-show="deleteModalOpen" x-cloak class="fixed z-50 inset-0 overflow-y-auto" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen p-4 text-center">
                        <!-- Backdrop -->
                        <div x-show="deleteModalOpen"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            class="fixed inset-0 bg-slate-950/40 backdrop-blur-md transition-opacity"
                            @click="deleteModalOpen = false"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <!-- Modal Dialog Container (rounded-[24px], compact sm:max-w-md, centered) -->
                        <div x-show="deleteModalOpen"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4 sm:translate-y-0" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                            class="relative inline-block align-middle bg-white rounded-[24px] text-left overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.12)] transform transition-all my-auto sm:max-w-md sm:w-full border border-slate-100">
                            
                            <!-- Modal Header -->
                            <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 bg-rose-50/60">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-rose-100/80 text-rose-700 flex items-center justify-center font-bold text-sm shadow-2xs">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </div>
                                    <x-text.h2 class="!text-sm !font-bold !text-slate-900">
                                        Delete Issue
                                    </x-text.h2>
                                </div>
                                <button type="button" @click="deleteModalOpen = false"
                                    class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 transition-colors flex items-center justify-center">
                                    <i class="fa-solid fa-xmark text-sm"></i>
                                </button>
                            </div>

                            <!-- Modal Form -->
                            <form :action="deleteData.actionUrl" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="p-5 space-y-3">
                                    <div class="p-3.5 bg-rose-50/80 border border-rose-200/60 rounded-xl flex items-start gap-3">
                                        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm mt-0.5 flex-shrink-0"></i>
                                        <div class="text-xs text-rose-900 leading-relaxed">
                                            <span class="font-bold block mb-0.5 text-rose-950" x-text="deleteData.identifier"></span>
                                            {{ $isId ? 'Hapus terbitan ini secara permanen? Tindakan ini tidak dapat dibatalkan.' : 'Delete this issue permanently? This action cannot be undone.' }}
                                        </div>
                                    </div>

                                    <p class="text-xs font-semibold text-slate-700 px-0.5 leading-relaxed">
                                        {{ $isId ? 'Apakah Anda yakin ingin menghapus terbitan ini?' : 'Are you sure you want to delete this issue?' }}
                                    </p>
                                </div>

                                <!-- Modal Footer -->
                                <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                    <button type="button" @click="deleteModalOpen = false"
                                        class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 bg-white border border-slate-200/80 rounded-xl hover:bg-slate-100/80 transition-all shadow-2xs">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:scale-[0.98] rounded-xl transition-all shadow-md shadow-rose-600/20">
                                        Delete Issue
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </div>

    @push('scripts')
        <script>
            // Handle successful operations
            @if (session('success'))
                // You can add toast notification here
            @endif
        </script>
    @endpush
@endsection
