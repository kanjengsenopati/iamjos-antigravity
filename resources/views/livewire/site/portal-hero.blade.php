<div class="relative w-full min-h-[85vh] flex flex-col items-center justify-center overflow-hidden bg-[#020617] isolate">

    {{-- 1. NOISE TEXTURE (FIXED: Very Low Opacity & Pointer Events None) --}}
    <div class="absolute inset-0 z-20 opacity-[0.03] pointer-events-none mix-blend-overlay"
         style="background-image: url(&quot;data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E&quot;);">
    </div>

    {{-- 2. AMBIENT GLOW (Behind Content) --}}
    <div class="absolute top-[-10%] left-[-10%] w-[40rem] h-[40rem] bg-blue-600/20 rounded-full blur-[128px] animate-pulse-slow pointer-events-none z-10"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40rem] h-[40rem] bg-indigo-600/10 rounded-full blur-[128px] pointer-events-none z-10"></div>

    {{-- 3. MAIN CONTENT (On Top, High Z-Index) --}}
    <div class="relative z-30 max-w-5xl mx-auto px-4 text-center">

        {{-- Badge --}}
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-blue-300 text-xs font-medium mb-8 backdrop-blur-md">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
            </span>
            Research & Innovation
        </div>

        {{-- Headline --}}
        <h1 class="text-5xl md:text-7xl font-bold tracking-tight text-white mb-6 drop-shadow-sm leading-tight">
            Discover <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400">Academic Excellence</span>
        </h1>

        {{-- Subheadline --}}
        <p class="text-lg text-slate-400 mb-10 max-w-2xl mx-auto leading-relaxed">
            Access over <span class="text-white font-bold">{{ number_format($stats['articles'] ?? 0) }}</span> peer-reviewed articles and join a community of <span class="text-white font-bold">{{ number_format($stats['authors'] ?? 0) }}</span> scholars pushing the boundaries of knowledge.
        </p>

        {{-- Search Bar Component (The Input Group) --}}
        <div class="max-w-3xl mx-auto relative group mb-12">
            {{-- Glow Behind --}}
            <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>

            <form action="{{ route('portal.search') }}" method="GET" class="relative">
                <input 
                    type="text" 
                    name="q"
                    wire:model.live.debounce.200ms="query"
                    placeholder="Search titles, keywords, authors..." 
                    class="block w-full h-16 pl-8 pr-32 rounded-full bg-white/5 backdrop-blur-xl border border-white/10 text-white placeholder-slate-400 text-lg shadow-2xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-white/20 transition-all font-light"
                    autocomplete="off"
                >

                {{-- Search Button --}}
                <button type="submit" class="absolute right-2 top-2 h-12 px-6 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-medium text-sm shadow-lg shadow-blue-900/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                    Search
                </button>
            </form>

            {{-- Live Suggestions Dropdown --}}
            @if(!empty($suggestions) && strlen($query) >= 2)
                <div class="absolute top-full left-0 right-0 mt-3 p-2 bg-[#0F172A]/90 backdrop-blur-3xl border border-white/10 rounded-2xl shadow-2xl text-left overflow-hidden ring-1 ring-white/5 animate-in fade-in slide-in-from-top-2 duration-200 z-50">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-3 py-2">Suggested Results</div>
                    @foreach($suggestions as $result)
                        <a href="{{ $result['url'] }}" class="flex items-start gap-3 px-3 py-3 rounded-xl hover:bg-white/5 transition-colors group/item">
                            <div class="mt-0.5 w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center text-blue-400 group-hover/item:text-blue-300 group-hover/item:bg-blue-500/30 transition-colors">
                                <i class="{{ $result['icon'] }} text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-slate-200 group-hover/item:text-white truncate transition-colors">{{ $result['title'] }}</h4>
                                <p class="text-xs text-slate-500 capitalize">{{ $result['type'] }}</p>
                            </div>
                        </a>
                    @endforeach
                    <a href="{{ route('portal.search', ['q' => $query]) }}" class="block px-3 py-3 text-center text-sm text-blue-400 hover:text-blue-300 font-medium border-t border-white/5 mt-1">
                        View all results for "{{ $query }}" &rarr;
                    </a>
                </div>
            @endif
        </div>

        {{-- Stats Grid (Use bg-white/5 border-white/10) --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 w-full">
            {{-- Item 1 --}}
            <div x-data="{ count: 0, target: {{ $stats['journals'] ?? 0 }} }" x-init="setTimeout(() => { let interval = setInterval(() => { if(count < target) { count += Math.ceil(target/50); if(count > target) count = target; } else { clearInterval(interval) } }, 20); }, 500)" class="flex flex-col items-center justify-center p-6 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 transition-colors group min-h-[140px]">
                <i class="fa-solid fa-book-bookmark text-2xl text-blue-400 mb-3 group-hover:scale-110 transition-transform"></i>
                <div class="text-3xl font-bold text-white mb-1" x-text="count.toLocaleString()">0</div>
                <div class="text-[10px] uppercase tracking-widest text-slate-400 font-semibold">Journals</div>
            </div>

            {{-- Item 2 --}}
            <div x-data="{ count: 0, target: {{ $stats['articles'] ?? 0 }} }" x-init="setTimeout(() => { let interval = setInterval(() => { if(count < target) { count += Math.ceil(target/50); if(count > target) count = target; } else { clearInterval(interval) } }, 20); }, 700)" class="flex flex-col items-center justify-center p-6 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 transition-colors group min-h-[140px]">
                <i class="fa-solid fa-file-lines text-2xl text-emerald-400 mb-3 group-hover:scale-110 transition-transform"></i>
                <div class="text-3xl font-bold text-white mb-1" x-text="count.toLocaleString()">0</div>
                <div class="text-[10px] uppercase tracking-widest text-slate-400 font-semibold">Articles</div>
            </div>

             {{-- Item 3 --}}
             <div x-data="{ count: 0, target: {{ $stats['authors'] ?? 0 }} }" x-init="setTimeout(() => { let interval = setInterval(() => { if(count < target) { count += Math.ceil(target/50); if(count > target) count = target; } else { clearInterval(interval) } }, 20); }, 900)" class="flex flex-col items-center justify-center p-6 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 transition-colors group min-h-[140px]">
                <i class="fa-solid fa-users text-2xl text-purple-400 mb-3 group-hover:scale-110 transition-transform"></i>
                <div class="text-3xl font-bold text-white mb-1" x-text="count.toLocaleString()">0</div>
                <div class="text-[10px] uppercase tracking-widest text-slate-400 font-semibold">Authors</div>
            </div>

             {{-- Item 4 --}}
             <div class="flex flex-col items-center justify-center p-6 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 transition-colors group min-h-[140px]">
                <i class="fa-solid fa-globe text-2xl text-cyan-400 mb-3 group-hover:scale-110 transition-transform"></i>
                <div class="text-3xl font-bold text-white mb-1">Global</div>
                <div class="text-[10px] uppercase tracking-widest text-slate-400 font-semibold">Reach</div>
            </div>
        </div>

    </div>
</div>
