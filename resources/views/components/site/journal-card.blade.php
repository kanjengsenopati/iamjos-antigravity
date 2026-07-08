@props(['journal', 'brandColor' => '#00629B'])

<article class="group bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-2xl hover:shadow-indigo-100/50 transition-all duration-500 overflow-hidden flex flex-col h-full">
    {{-- Card Header: Cover Image --}}
    <div class="relative aspect-[16/10] overflow-hidden bg-slate-50">
        @if($journal->thumbnail_path)
            <img src="{{ Storage::disk('public')->url($journal->thumbnail_path) }}" 
                 alt="{{ $journal->name }}" 
                 class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-700"
                 loading="lazy">
        @else
            {{-- Futuristic Placeholder --}}
            <div class="w-full h-full flex flex-col items-center justify-center p-8 bg-gradient-to-br from-slate-50 to-indigo-50/30">
                <div class="w-16 h-16 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-4 group-hover:rotate-12 transition-transform duration-500">
                    <span class="text-2xl font-black text-indigo-600">
                        {{ strtoupper(substr($journal->name, 0, 1)) }}
                    </span>
                </div>
                <div class="h-1.5 w-12 bg-indigo-100 rounded-full"></div>
            </div>
        @endif

        {{-- Accreditation Badge --}}
        @if($journal->sinta_level)
            <div class="absolute top-4 right-4">
                <span class="inline-flex items-center px-3 py-1 bg-white/90 backdrop-blur-md rounded-xl text-[10px] font-black tracking-tight shadow-sm border border-white/20
                    {{ $journal->sinta_level <= 2 ? 'text-amber-600' : 'text-slate-600' }}">
                    Sinta {{ $journal->sinta_level }}
                </span>
            </div>
        @endif
        
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </div>

    {{-- Card Body --}}
    <div class="flex flex-col flex-grow p-6">
        {{-- Tags Row --}}
        <div class="flex flex-wrap gap-2 mb-4">
            @if($journal->is_open_access)
                <span class="text-[9px] font-black tracking-tight px-2 py-1 bg-emerald-50 text-emerald-600 rounded-lg">
                    OA
                </span>
            @endif
            @if($journal->is_scopus_indexed)
                <span class="text-[9px] font-black tracking-tight px-2 py-1 bg-blue-50 text-blue-600 rounded-lg">
                    Scopus
                </span>
            @endif
        </div>

        {{-- Title --}}
        <h3 class="text-lg font-black text-slate-900 leading-tight mb-3 line-clamp-2 group-hover:text-indigo-600 transition-colors">
            <a href="{{ route('journal.public.home', $journal->slug) }}">
                {{ $journal->name }}
            </a>
        </h3>

        {{-- Meta Info --}}
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-[10px] font-bold text-slate-500 mb-6 tracking-tight">
            @if($journal->issn_print)
                <span class="flex items-center gap-1.5">
                    <span class="px-1.5 py-0.5 bg-slate-100 border border-slate-200/80 text-slate-700 rounded text-[8px] font-extrabold tracking-tighter">P-ISSN</span>
                    <span class="text-slate-800 font-extrabold tracking-normal">{{ $journal->issn_print }}</span>
                </span>
            @endif
            @if($journal->issn_online)
                <span class="flex items-center gap-1.5">
                    <span class="px-1.5 py-0.5 bg-slate-100 border border-slate-200/80 text-slate-700 rounded text-[8px] font-extrabold tracking-tighter">E-ISSN</span>
                    <span class="text-slate-800 font-extrabold tracking-normal">{{ $journal->issn_online }}</span>
                </span>
            @endif
            @if(!$journal->issn_print && !$journal->issn_online)
                <span class="flex items-center gap-1 italic text-slate-500 font-semibold">{{ __('No ISSN Listed') }}</span>
            @endif
            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
            <span class="text-indigo-600 font-black uppercase tracking-wider transition-colors group-hover:text-indigo-800">
                {{ $journal->abbreviation ?? 'ARCHIVE' }}
            </span>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 gap-4 mt-auto pt-6 border-t border-slate-100/50">
            <div class="px-3 py-2.5 bg-slate-50 rounded-2xl group-hover:bg-indigo-50/50 transition-all duration-300 border border-slate-100/80 group-hover:border-indigo-100/50 flex flex-col items-center justify-center text-center">
                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-600 mb-1 block">{{ __('Issues') }}</span>
                <span class="text-[18px] font-black text-slate-800">{{ number_format($journal->issues_count ?? 0) }}</span>
            </div>
            <div class="px-3 py-2.5 bg-slate-50 rounded-2xl group-hover:bg-indigo-50/50 transition-all duration-300 border border-slate-100/80 group-hover:border-indigo-100/50 flex flex-col items-center justify-center text-center">
                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-600 mb-1 block">{{ __('Articles') }}</span>
                <span class="text-[18px] font-black text-slate-800">{{ number_format($journal->submissions_count ?? 0) }}</span>
            </div>
        </div>
    </div>

    {{-- Action Footer --}}
    <div class="px-6 pb-7">
        <a href="{{ route('journal.public.home', $journal->slug) }}"
           class="w-full h-14 flex items-center justify-center gap-3 bg-indigo-600 text-white text-xs font-bold tracking-tight rounded-2xl shadow-xl shadow-indigo-100 hover:bg-indigo-700 hover:shadow-indigo-200 hover:-translate-y-1 transition-all duration-300">
            {{ __('Explore Journal') }}
            <i class="fa-solid fa-arrow-right-long transition-transform group-hover:translate-x-1"></i>
        </a>
    </div>
</article>
