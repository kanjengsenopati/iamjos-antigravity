@props(['journal', 'brandColor' => '#00629B'])

<article class="group bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-lg hover:border-slate-300 transition-all duration-300 overflow-hidden flex flex-col h-full">
    {{-- Card Header: Cover Image --}}
    <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
        @if($journal->thumbnail_path)
            <img src="{{ Storage::url($journal->thumbnail_path) }}" 
                 alt="{{ $journal->name }}" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                 loading="lazy">
        @else
            {{-- Placeholder with gradient --}}
            <div class="w-full h-full flex flex-col items-center justify-center"
                 style="background: linear-gradient(135deg, {{ $brandColor }}15, {{ $brandColor }}30);">
                <span class="text-5xl font-bold" style="color: {{ $brandColor }}">
                    {{ strtoupper(substr($journal->name, 0, 2)) }}
                </span>
                <span class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Jurnal</span>
            </div>
        @endif

        {{-- Accreditation Badge (Top Right) --}}
        @if($journal->sinta_level)
            <div class="absolute top-3 right-3">
                <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wide shadow-sm
                    {{ $journal->sinta_level <= 2 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                    SINTA {{ $journal->sinta_level }}
                </span>
            </div>
        @endif
    </div>

    {{-- Card Body --}}
    <div class="flex flex-col flex-grow p-5">
        {{-- Badges Row --}}
        <div class="flex flex-wrap gap-1.5 mb-3">
            @if($journal->is_open_access)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                    <i class="fa-solid fa-lock-open text-[8px]"></i>
                    Open Access
                </span>
            @endif
            @if($journal->is_scopus_indexed)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                    Scopus
                </span>
            @endif
            @if($journal->is_doaj_indexed)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-100">
                    DOAJ
                </span>
            @endif
        </div>

        {{-- Title --}}
        <h3 class="text-base font-bold text-slate-900 leading-snug mb-2 line-clamp-2 group-hover:text-slate-700 transition-colors">
            <a href="{{ route('journal.public.home', $journal->slug) }}" 
               class="hover:underline decoration-2 underline-offset-2"
               style="text-decoration-color: {{ $brandColor }}">
                {{ $journal->name }}
            </a>
        </h3>

        {{-- ISSN --}}
        @if($journal->issn_online || $journal->issn_print)
            <p class="text-xs text-slate-400 font-mono mb-3">
                ISSN: {{ $journal->issn_online ?? $journal->issn_print }}
            </p>
        @endif

        {{-- Stats Row --}}
        <div class="flex items-center gap-4 mt-auto pt-4 border-t border-slate-100">
            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <i class="fa-solid fa-newspaper text-slate-400"></i>
                <span>{{ $journal->issues_count ?? 0 }} Issue</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <i class="fa-solid fa-file-lines text-slate-400"></i>
                <span>{{ $journal->submissions_count ?? 0 }} Article</span>
            </div>
        </div>
    </div>

    {{-- Card Footer: Action Buttons --}}
    <div class="px-5 pb-5">
        <div class="flex gap-2">
            <a href="{{ route('journal.public.home', $journal->slug) }}"
               class="flex-1 text-center px-3 py-2 rounded-lg text-xs font-semibold border-2 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm"
               style="border-color: {{ $brandColor }}; color: {{ $brandColor }};"
               onmouseover="this.style.backgroundColor='{{ $brandColor }}'; this.style.color='white';"
               onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $brandColor }}';">
                <i class="fa-solid fa-eye mr-1"></i>
                View Journal
            </a>
            @if($journal->currentIssue)
                <a href="{{ route('journal.public.current', $journal->slug) }}"
                   class="flex-1 text-center px-3 py-2 rounded-lg text-xs font-semibold bg-green-600 text-white border-2 border-green-600 hover:bg-green-700 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm">
                    <i class="fa-solid fa-newspaper mr-1"></i>
                    Current Issue
                </a>
            @endif
        </div>
    </div>
</article>
