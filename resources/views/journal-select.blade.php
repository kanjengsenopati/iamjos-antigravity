<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Journal - {{ config('app.name') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom Select Arrow */
        select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 1rem center;
            background-repeat: no-repeat;
            background-size: 1.25rem 1.25rem;
            padding-right: 2.75rem;
        }
    </style>
</head>

<body class="font-sans antialiased text-slate-200 bg-slate-900 min-h-screen relative overflow-x-hidden" 
      x-data="{ 
          search: '', 
          filter: 'all',
          journals: @js($journals->map(fn($j) => ['name' => $j->name, 'abbr' => $j->abbreviation, 'enabled' => (bool)$j->enabled])),
          get visibleCount() {
              if (!this.journals || this.journals.length === 0) return 0;
              let count = 0;
              for (let j of this.journals) {
                  let matchesSearch = true;
                  if (this.search !== '') {
                      const q = this.search.toLowerCase();
                      matchesSearch = j.name.toLowerCase().includes(q) || (j.abbr && j.abbr.toLowerCase().includes(q));
                  }
                  let matchesFilter = true;
                  if (this.filter === 'active') matchesFilter = j.enabled;
                  if (this.filter === 'inactive') matchesFilter = !j.enabled;
                  
                  if (matchesSearch && matchesFilter) count++;
              }
              return count;
          },
          matchesSearch(name, abbr) {
              if (this.search === '') return true;
              const q = this.search.toLowerCase();
              return name.toLowerCase().includes(q) || (abbr && String(abbr).toLowerCase().includes(q));
          },
          matchesFilter(enabled) {
              if (this.filter === 'all') return true;
              if (this.filter === 'active') return enabled === true;
              if (this.filter === 'inactive') return enabled === false;
              return true;
          }
      }">

    <!-- Background Decor -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] bg-primary-600/20 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[50%] h-[50%] bg-blue-600/20 blur-[120px] rounded-full"></div>
        <div class="absolute inset-0 opacity-5"
            style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"1\"%3E%3Ccircle cx=\"3\" cy=\"3\" r=\"1.5\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
        </div>
    </div>

    <div class="relative z-10 min-h-screen flex flex-col p-6 lg:p-10">
        <!-- Top Navbar Menu (User Identity) -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-10 max-w-7xl mx-auto w-full">
            <div class="flex items-center gap-4 bg-white/5 border border-white/10 px-5 py-3 rounded-2xl backdrop-blur-md shadow-lg">
                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 rounded-full flex items-center justify-center font-bold text-white shadow-inner">
                    {{ auth()->user()->initials ?? strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-white font-semibold leading-tight text-sm sm:text-base">{{ auth()->user()->name }}</h2>
                    <p class="text-slate-400 text-xs sm:text-sm leading-tight">{{ auth()->user()->email }}</p>
                </div>
            </div>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-5 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-sm font-medium transition-all backdrop-blur-md hover:shadow-lg hover:text-white text-slate-300">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="hidden sm:inline">Sign Out</span>
                </button>
            </form>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto w-full flex-1">
            <!-- Header & Search/Filter -->
            <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-2 tracking-tight">Select Your Journal</h1>
                    <p class="text-slate-400 max-w-lg">Choose a journal to access its dashboard, manage submissions, and review articles.</p>
                </div>
                
                @if ($journals->isNotEmpty())
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Search -->
                    <div class="relative flex-1 sm:w-72">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" x-model="search" placeholder="Search journals by name or abbr..." 
                            class="w-full pl-11 pr-4 py-2.5 bg-slate-800/60 border border-slate-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 rounded-xl text-white placeholder-slate-400 backdrop-blur-md transition-all shadow-inner">
                    </div>
                    
                    <!-- Filter -->
                    <div class="relative w-full sm:w-48">
                        <select x-model="filter" class="w-full px-4 py-2.5 bg-slate-800/60 border border-slate-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 rounded-xl text-white backdrop-blur-md transition-all appearance-none cursor-pointer shadow-inner">
                            <option value="all">All Status</option>
                            <option value="active">Active Only</option>
                            <option value="inactive">Inactive Only</option>
                        </select>
                    </div>
                </div>
                @endif
            </div>

            <!-- Empty State No Journals At All -->
            @if ($journals->isEmpty())
                <div class="text-center py-20 bg-white/5 backdrop-blur-md rounded-3xl border border-white/10 shadow-2xl">
                    <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-slate-700">
                        <i class="fa-solid fa-book-open-reader text-3xl text-slate-500"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">No Journals Available</h3>
                    <p class="text-slate-400 mb-8 max-w-md mx-auto">There are no journals currently available or assigned to your account.</p>
                    <a href="{{ route('register') }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-500 text-white font-medium rounded-xl transition-all shadow-lg hover:shadow-primary-500/30 hover:-translate-y-0.5">
                        <i class="fa-solid fa-user-plus"></i>
                        Join a Journal
                    </a>
                </div>
            @else
                
                @if(isset($showJoinOption) && $showJoinOption)
                    <!-- Notice for Unassigned Users -->
                    <div class="mb-8 p-5 bg-gradient-to-r from-amber-500/10 to-transparent backdrop-blur-md rounded-2xl border border-amber-500/20 flex flex-col sm:flex-row items-center gap-5 sm:gap-6 relative overflow-hidden">
                        <div class="absolute inset-0 bg-amber-500/5 blur-xl"></div>
                        <div class="relative w-12 h-12 bg-amber-500/20 rounded-full flex items-center justify-center shrink-0 border border-amber-500/30">
                            <i class="fa-solid fa-circle-exclamation text-xl text-amber-400"></i>
                        </div>
                        <div class="relative text-center sm:text-left">
                            <h3 class="text-lg font-bold text-amber-50 mb-1">You haven't joined any journal yet</h3>
                            <p class="text-amber-200/70 text-sm">Browse the available journals below and click to register or join them.</p>
                        </div>
                    </div>
                @endif

                <!-- Journals Grid -->
                <!-- Use CSS Grid for dynamic columns (1 on mobile, 2 on tablet, 3-4 on desktop) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" x-show="visibleCount > 0" x-cloak>
                    @foreach ($journals as $index => $journal)
                        @php
                            $isUserJournal = isset($userJournals) && $userJournals->contains('id', $journal->id);
                            // Avoid quoting issues in Alpine
                            $jName = addcslashes($journal->name, "'\\");
                            $jAbbr = addcslashes($journal->abbreviation ?? '', "'\\");
                            $jEnabled = $journal->enabled ? 'true' : 'false';
                        @endphp
                        <a href="{{ $isUserJournal ? route('journal.select.go', ['journal' => $journal->slug]) : route('register', ['journal' => $journal->slug]) }}"
                            x-show="matchesSearch('{{ $jName }}', '{{ $jAbbr }}') && matchesFilter({{ $jEnabled }})"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform scale-95"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 transform scale-100"
                            x-transition:leave-end="opacity-0 transform scale-95"
                            class="group relative flex flex-col bg-white/5 backdrop-blur-md rounded-3xl border {{ $isUserJournal ? 'border-primary-500/40' : 'border-white/10' }} p-6 transition-all duration-300 hover:bg-white/10 hover:border-primary-400 hover:shadow-2xl hover:shadow-primary-500/20 hover:-translate-y-1.5 overflow-hidden">
                            
                            <!-- Highlight Glow logic on Active elements -->
                            <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/10 rounded-full blur-3xl -mr-10 -mt-10 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                            @if(!$isUserJournal && isset($showJoinOption) && $showJoinOption)
                                <!-- Join Badge -->
                                <div class="absolute -top-1 -right-1 px-4 py-1.5 bg-gradient-to-r from-primary-600 to-primary-500 text-white text-[10px] font-bold uppercase tracking-wider rounded-bl-xl rounded-tr-3xl shadow-lg">
                                    Join
                                </div>
                            @endif

                            <!-- Header: Logo & Status -->
                            <div class="flex items-start justify-between mb-5 relative z-10">
                                <div class="w-16 h-16 shrink-0 rounded-2xl bg-white/90 shadow-lg flex items-center justify-center overflow-hidden border border-white/20 group-hover:border-primary-300 transition-colors">
                                    @if ($journal->logo_path)
                                        <img src="{{ Storage::url($journal->logo_path) }}" alt="{{ $journal->name }}" class="w-full h-full object-contain p-2">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                                            <span class="text-2xl font-bold text-white">{{ strtoupper(substr($journal->abbreviation ?? $journal->name, 0, 2)) }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full {{ $journal->enabled ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20' }}">
                                    {{ $journal->enabled ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 mb-6 relative z-10">
                                <h3 class="text-lg font-bold text-white mb-2 leading-tight group-hover:text-primary-300 transition-colors line-clamp-2">
                                    {{ $journal->name }}
                                </h3>
                                @if ($journal->abbreviation)
                                    <p class="text-sm font-semibold text-primary-400/90 mb-3">{{ $journal->abbreviation }}</p>
                                @endif
                                <p class="text-sm text-slate-400 line-clamp-2 leading-relaxed">
                                    {{ $journal->description ?? 'No description available for this journal.' }}
                                </p>
                            </div>

                            <!-- Footer Stats -->
                            <div class="mt-auto pt-4 border-t border-white/10 flex flex-wrap items-center justify-between gap-2 text-xs font-medium text-slate-400 group-hover:text-slate-300 transition-colors relative z-10">
                                <div class="flex gap-4">
                                    @if ($journal->issn_print || $journal->issn_online)
                                        <span class="flex items-center gap-1.5 bg-slate-800/50 px-2 py-1 rounded-md" title="ISSN">
                                            <i class="fa-solid fa-barcode text-slate-500"></i>
                                            {{ $journal->issn_online ?? $journal->issn_print }}
                                        </span>
                                    @endif
                                    
                                    <span class="flex items-center gap-1.5 bg-slate-800/50 px-2 py-1 rounded-md" title="Articles">
                                        <i class="fa-regular fa-file-lines text-slate-500"></i>
                                        {{ $journal->submissions_count ?? $journal->submissions()->count() }}
                                    </span>
                                </div>
                                
                                <!-- Go Icon -->
                                <div class="w-8 h-8 rounded-full bg-primary-500/20 flex items-center justify-center text-primary-400 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all border border-primary-500/30">
                                    <i class="fa-solid fa-arrow-right text-sm"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                
                <!-- Empty Search Result State -->
                <div x-show="visibleCount === 0 && journals.length > 0" x-cloak
                    class="text-center py-16 bg-white/5 backdrop-blur-md rounded-3xl border border-white/10 mt-6 shadow-xl">
                    <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner border border-slate-700">
                        <i class="fa-solid fa-search text-2xl text-slate-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">No matches found</h3>
                    <p class="text-slate-400">We couldn't find any journal matching your current search or filters.</p>
                    <button @click.prevent="search = ''; filter = 'all'" class="mt-6 px-4 py-2 border border-primary-500/50 rounded-lg text-primary-400 hover:bg-primary-500/10 font-medium text-sm transition-all">
                        Clear all filters
                    </button>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="mt-12 text-center text-sm font-medium text-slate-500 flex-none pb-4">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
