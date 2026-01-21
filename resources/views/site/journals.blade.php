@extends('layouts.portal')

@section('title', 'Jelajahi Jurnal')

@php
    $brandColor = $settings['primary_color'] ?? '#00629B';
@endphp

@section('content')
<div x-data="{ mobileFiltersOpen: false }" class="min-h-screen bg-slate-50 pb-20">

    {{-- ============================================ --}}
    {{-- PAGE HEADER --}}
    {{-- ============================================ --}}
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-12">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Jelajahi Jurnal</h1>
            <p class="mt-2 text-slate-500 text-base md:text-lg max-w-2xl">
                Temukan koleksi jurnal ilmiah berkualitas tinggi dari berbagai bidang studi.
            </p>
            
            {{-- Mobile Filter Button --}}
            <div class="mt-6 lg:hidden">
                <button @click="mobileFiltersOpen = true" 
                        type="button"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 active:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-sliders" style="color: {{ $brandColor }}"></i>
                    Filter & Pencarian
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MAIN GRID CONTAINER --}}
    {{-- ============================================ --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- ============================================ --}}
            {{-- SIDEBAR (Desktop Only - 3 Columns) --}}
            {{-- ============================================ --}}
            <aside class="hidden lg:block lg:col-span-3">
                <div class="sticky top-24">
                    <x-site.journal-sidebar 
                        :search="$search" 
                        :sort="$sort" 
                        :alpha="$alpha" 
                        :alphabet="$alphabet" 
                        :brand-color="$brandColor" 
                    />
                </div>
            </aside>

            {{-- ============================================ --}}
            {{-- MOBILE DRAWER (Off-Canvas) --}}
            {{-- ============================================ --}}
            <template x-teleport="body">
                <div x-show="mobileFiltersOpen" 
                     x-cloak
                     class="fixed inset-0 z-50 lg:hidden"
                     role="dialog" 
                     aria-modal="true"
                     aria-labelledby="filter-drawer-title">
                    
                    {{-- Backdrop --}}
                    <div x-show="mobileFiltersOpen"
                         @click="mobileFiltersOpen = false" 
                         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                    </div>

                    {{-- Drawer Panel --}}
                    <div x-show="mobileFiltersOpen"
                         class="fixed right-0 top-0 bottom-0 w-full max-w-sm bg-white shadow-2xl overflow-y-auto"
                         x-transition:enter="transform transition ease-out duration-300"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transform transition ease-in duration-200"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full">
                        
                        {{-- Drawer Header --}}
                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                            <h3 id="filter-drawer-title" class="font-bold text-lg text-slate-900">Filter Jurnal</h3>
                            <button @click="mobileFiltersOpen = false" 
                                    type="button"
                                    class="p-2 -mr-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200 transition-colors">
                                <i class="fa-solid fa-xmark text-xl"></i>
                                <span class="sr-only">Tutup</span>
                            </button>
                        </div>

                        {{-- Drawer Body --}}
                        <div class="p-6">
                            <x-site.journal-sidebar 
                                :search="$search" 
                                :sort="$sort" 
                                :alpha="$alpha" 
                                :alphabet="$alphabet" 
                                :brand-color="$brandColor" 
                            />
                        </div>
                    </div>
                </div>
            </template>

            {{-- ============================================ --}}
            {{-- CONTENT AREA (9 Columns) --}}
            {{-- ============================================ --}}
            <main class="lg:col-span-9 min-w-0">
                
                {{-- Results Bar --}}
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 p-4 bg-white rounded-lg border border-slate-200 shadow-sm">
                    <p class="text-sm text-slate-600">
                        Menampilkan 
                        <span class="font-semibold text-slate-900">{{ $journals->firstItem() ?? 0 }}-{{ $journals->lastItem() ?? 0 }}</span> 
                        dari 
                        <span class="font-semibold text-slate-900">{{ $totalJournals }}</span> jurnal
                    </p>

                    <div class="flex items-center gap-2">
                        <label for="sort-select" class="text-xs font-medium text-slate-500 uppercase tracking-wide">Urutkan:</label>
                        <select id="sort-select"
                                onchange="window.location.href=this.value" 
                                class="appearance-none pl-3 pr-8 py-1.5 bg-slate-50 border border-slate-200 rounded-md text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-1 cursor-pointer"
                                style="--tw-ring-color: {{ $brandColor }}">
                            <option value="{{ route('portal.journals', array_merge(request()->query(), ['sort' => 'name'])) }}" 
                                    {{ $sort == 'name' ? 'selected' : '' }}>Nama (A-Z)</option>
                            <option value="{{ route('portal.journals', array_merge(request()->query(), ['sort' => 'newest'])) }}" 
                                    {{ $sort == 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="{{ route('portal.journals', array_merge(request()->query(), ['sort' => 'articles'])) }}" 
                                    {{ $sort == 'articles' ? 'selected' : '' }}>Terpopuler</option>
                        </select>
                    </div>
                </div>

                {{-- Active Filters --}}
                @if($search || ($alpha && $alpha !== 'all'))
                    <div class="flex flex-wrap items-center gap-2 mb-6">
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Filter Aktif:</span>
                        @if($search)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                                "{{ Str::limit($search, 20) }}"
                                <a href="{{ route('portal.journals', array_merge(request()->except('search'))) }}" 
                                   class="ml-1 text-slate-400 hover:text-red-500 transition-colors">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            </span>
                        @endif
                        @if($alpha && $alpha !== 'all')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                <i class="fa-solid fa-font text-slate-400"></i>
                                Huruf: {{ $alpha }}
                                <a href="{{ route('portal.journals', array_merge(request()->except('alpha'))) }}" 
                                   class="ml-1 text-slate-400 hover:text-red-500 transition-colors">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            </span>
                        @endif
                        <a href="{{ route('portal.journals') }}" 
                           class="text-xs font-semibold hover:underline transition-colors"
                           style="color: {{ $brandColor }}">
                            Reset Semua
                        </a>
                    </div>
                @endif

                {{-- Journal Cards Grid --}}
                @if($journals->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                        @foreach($journals as $journal)
                            <x-site.journal-card :journal="$journal" :brand-color="$brandColor" />
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-10 pt-6 border-t border-slate-200">
                        {{ $journals->links() }}
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="bg-white rounded-xl border-2 border-dashed border-slate-200 p-12 text-center">
                        <div class="mx-auto w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                            <i class="fa-regular fa-folder-open text-2xl text-slate-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Tidak Ada Hasil</h3>
                        <p class="text-slate-500 mb-6 max-w-sm mx-auto">
                            Maaf, kami tidak dapat menemukan jurnal yang sesuai dengan kriteria pencarian Anda.
                        </p>
                        <a href="{{ route('portal.journals') }}" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white shadow-sm hover:opacity-90 transition-opacity"
                           style="background-color: {{ $brandColor }}">
                            <i class="fa-solid fa-rotate-left"></i>
                            Hapus Semua Filter
                        </a>
                    </div>
                @endif

            </main>
        </div>
    </div>
</div>
@endsection
