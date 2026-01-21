{{-- Journal Card Component - IEEE Modern Style --}}
@props(['journal', 'showBadges' => true, 'showStats' => true])

@php
$primaryColor = $journal->getWebsiteSetting('primary_color') ?? '#0369a1';
$articlesCount = $journal->submissions_count ?? $journal->submissions()->published()->count();
$issuesCount = $journal->issues_count ?? $journal->issues()->published()->count();
@endphp

<div class="group bg-white rounded-xl shadow-sm hover:shadow-xl border border-gray-100 overflow-hidden transition-all duration-300 hover:-translate-y-1">
    {{-- Journal Thumbnail / Cover --}}
    <div class="relative h-40 overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200">
        @if($journal->thumbnail_path)
            <img src="{{ Storage::url($journal->thumbnail_path) }}" 
                 alt="{{ $journal->name }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @elseif($journal->logo_path)
            <div class="w-full h-full flex items-center justify-center p-6">
                <img src="{{ Storage::url($journal->logo_path) }}" 
                     alt="{{ $journal->name }}"
                     class="max-h-full max-w-full object-contain">
            </div>
        @else
            <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, {{ $primaryColor }}20, {{ $primaryColor }}40);">
                <span class="text-5xl font-bold" style="color: {{ $primaryColor }};">
                    {{ strtoupper(substr($journal->abbreviation ?? $journal->name, 0, 2)) }}
                </span>
            </div>
        @endif

        {{-- Badges Overlay --}}
        @if($showBadges)
            <div class="absolute top-3 left-3 flex flex-wrap gap-2">
                @if($journal->is_featured)
                    <span class="px-2 py-1 bg-amber-500 text-white text-xs font-bold rounded-full shadow">
                        <i class="fa-solid fa-star mr-1"></i>Featured
                    </span>
                @endif
                @if($journal->sinta_accreditation)
                    <span class="px-2 py-1 bg-green-600 text-white text-xs font-bold rounded-full shadow">
                        SINTA {{ $journal->sinta_accreditation }}
                    </span>
                @endif
                @if($journal->is_scopus_indexed)
                    <span class="px-2 py-1 bg-orange-500 text-white text-xs font-bold rounded-full shadow">
                        Scopus
                    </span>
                @endif
            </div>
        @endif

        {{-- Quick View Overlay (on hover) --}}
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
            <a href="{{ route('journal.public.home', $journal->slug) }}"
               class="w-full text-center py-2 bg-white/20 backdrop-blur-sm text-white text-sm font-medium rounded-lg hover:bg-white/30 transition-colors">
                <i class="fa-solid fa-external-link-alt mr-2"></i>
                Visit Journal
            </a>
        </div>
    </div>

    {{-- Card Body --}}
    <div class="p-5">
        {{-- Journal Title --}}
        <h3 class="font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
            <a href="{{ route('journal.public.home', $journal->slug) }}">
                {{ $journal->name }}
            </a>
        </h3>

        {{-- Abbreviation & ISSN --}}
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-3">
            @if($journal->abbreviation)
                <span class="font-medium text-gray-700">{{ $journal->abbreviation }}</span>
                <span>•</span>
            @endif
            @if($journal->issn_online)
                <span>e-ISSN: {{ $journal->issn_online }}</span>
            @endif
        </div>

        {{-- Description --}}
        @if($journal->description)
            <p class="text-sm text-gray-600 line-clamp-2 mb-4">
                {{ Str::limit($journal->description, 100) }}
            </p>
        @endif

        {{-- Stats Row --}}
        @if($showStats)
            <div class="flex items-center gap-4 pt-4 border-t border-gray-100 text-sm text-gray-500">
                <div class="flex items-center gap-1">
                    <i class="fa-regular fa-newspaper text-gray-400"></i>
                    <span>{{ number_format($issuesCount) }} Issues</span>
                </div>
                <div class="flex items-center gap-1">
                    <i class="fa-regular fa-file-lines text-gray-400"></i>
                    <span>{{ number_format($articlesCount) }} Articles</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Card Footer --}}
    <div class="px-5 pb-5 pt-0 flex gap-2">
        <a href="{{ route('journal.public.home', $journal->slug) }}"
           class="flex-1 text-center py-2.5 text-sm font-medium text-white rounded-lg transition-colors"
           style="background-color: {{ $primaryColor }};">
            Visit Site
        </a>
        <a href="{{ route('journal.public.current', $journal->slug) }}"
           class="flex-1 text-center py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
            Current Issue
        </a>
    </div>
</div>
