@props(['search', 'sort', 'alpha', 'alphabet', 'brandColor' => '#00629B'])

<div class="space-y-6">
    {{-- SEARCH SECTION --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">
            <i class="fa-solid fa-magnifying-glass mr-1.5" style="color: {{ $brandColor }}"></i>
            Pencarian
        </h3>
        <form action="{{ route('portal.journals') }}" method="GET">
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ $search }}"
                       placeholder="Cari judul jurnal..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:border-transparent transition-shadow"
                       style="--tw-ring-color: {{ $brandColor }}30">
                <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            </div>
            
            {{-- Preserve existing query params --}}
            @if($sort) <input type="hidden" name="sort" value="{{ $sort }}"> @endif
            @if($alpha && $alpha !== 'all') <input type="hidden" name="alpha" value="{{ $alpha }}"> @endif
            
            <button type="submit" 
                    class="w-full mt-3 py-2 text-sm font-semibold text-white rounded-lg transition-opacity hover:opacity-90"
                    style="background-color: {{ $brandColor }}">
                Cari
            </button>
        </form>
    </div>

    {{-- ALPHABETICAL FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                <i class="fa-solid fa-font mr-1.5" style="color: {{ $brandColor }}"></i>
                Alfabet
            </h3>
            @if($alpha && $alpha !== 'all')
                <a href="{{ route('portal.journals', array_merge(request()->except('alpha'))) }}" 
                   class="text-xs font-medium text-red-500 hover:text-red-700 hover:underline transition-colors">
                    Hapus
                </a>
            @endif
        </div>
        
        <div class="flex flex-wrap gap-1">
            {{-- 'All' Button --}}
            <a href="{{ route('portal.journals', array_merge(request()->except('alpha'))) }}"
               class="w-8 h-8 flex items-center justify-center rounded-md text-xs font-semibold border transition-all duration-200"
               style="{{ (empty($alpha) || $alpha === 'all') 
                        ? "background-color: {$brandColor}; color: white; border-color: {$brandColor};" 
                        : "background-color: #f8fafc; color: #64748b; border-color: #e2e8f0;" }}">
                All
            </a>
            
            {{-- Letter Buttons --}}
            @foreach($alphabet as $letter)
                @if($letter !== 'all')
                    <a href="{{ route('portal.journals', array_merge(request()->query(), ['alpha' => $letter])) }}"
                       class="w-8 h-8 flex items-center justify-center rounded-md text-xs font-semibold border transition-all duration-200 hover:bg-slate-100"
                       style="{{ $alpha === $letter 
                                ? "background-color: {$brandColor}; color: white; border-color: {$brandColor};" 
                                : "background-color: #f8fafc; color: #64748b; border-color: #e2e8f0;" }}">
                        {{ $letter }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>

    {{-- CATEGORY FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">
            <i class="fa-solid fa-layer-group mr-1.5" style="color: {{ $brandColor }}"></i>
            Bidang Ilmu
        </h3>
        <div class="space-y-2 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
            @foreach([
                'Teknik & Rekayasa', 
                'Kesehatan & Kedokteran', 
                'Ilmu Sosial', 
                'Humaniora', 
                'Pendidikan', 
                'Ekonomi & Bisnis', 
                'Pertanian', 
                'Komputer & TI'
            ] as $subject)
                <label class="flex items-center gap-3 py-1.5 px-2 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors group">
                    <input type="checkbox" 
                           name="subject[]" 
                           value="{{ $subject }}" 
                           class="w-4 h-4 rounded border-slate-300 focus:ring-2 focus:ring-offset-0"
                           style="color: {{ $brandColor }}; --tw-ring-color: {{ $brandColor }}40">
                    <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors">{{ $subject }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- ACCREDITATION FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">
            <i class="fa-solid fa-award mr-1.5" style="color: {{ $brandColor }}"></i>
            Akreditasi
        </h3>
        <div class="space-y-2">
            @foreach([
                ['value' => 'sinta-1', 'label' => 'SINTA 1', 'icon' => 'fa-star', 'color' => 'text-amber-500'],
                ['value' => 'sinta-2', 'label' => 'SINTA 2', 'icon' => 'fa-star', 'color' => 'text-slate-400'],
                ['value' => 'sinta-3', 'label' => 'SINTA 3', 'icon' => 'fa-star', 'color' => 'text-orange-400'],
                ['value' => 'scopus', 'label' => 'Scopus Indexed', 'icon' => 'fa-globe', 'color' => 'text-blue-500'],
                ['value' => 'doaj', 'label' => 'DOAJ', 'icon' => 'fa-book-open', 'color' => 'text-purple-500'],
            ] as $accreditation)
                <label class="flex items-center gap-3 py-1.5 px-2 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors group">
                    <input type="checkbox" 
                           name="accreditation[]" 
                           value="{{ $accreditation['value'] }}" 
                           class="w-4 h-4 rounded border-slate-300 focus:ring-2 focus:ring-offset-0"
                           style="color: {{ $brandColor }}; --tw-ring-color: {{ $brandColor }}40">
                    <span class="flex items-center gap-2 text-sm text-slate-600 group-hover:text-slate-900 transition-colors">
                        <i class="fa-solid {{ $accreditation['icon'] }} {{ $accreditation['color'] }} text-xs"></i>
                        {{ $accreditation['label'] }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- APPLY FILTERS BUTTON (Mobile only visual, form submit on desktop) --}}
    <div class="lg:hidden">
        <button type="button" 
                onclick="this.closest('[x-data]').dispatchEvent(new CustomEvent('close-filters'))"
                class="w-full py-3 text-sm font-bold text-white rounded-lg shadow-sm transition-opacity hover:opacity-90"
                style="background-color: {{ $brandColor }}">
            Terapkan Filter
        </button>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 2px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
