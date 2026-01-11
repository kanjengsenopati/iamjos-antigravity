<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'IAMJOS' }} - {{ $journal->name ?? 'Academic Journal' }}</title>
    <meta name="description"
        content="{{ $description ?? ($journal->description ?? 'Open-access academic journal platform') }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            "50": "#f0f9ff",
                            "100": "#e0f2fe",
                            "200": "#bae6fd",
                            "300": "#7dd3fc",
                            "400": "#38bdf8",
                            "500": "#0ea5e9",
                            "600": "#0284c7",
                            "700": "#0369a1",
                            "800": "#075985",
                            "900": "#0c4a6e",
                            "950": "#082f49"
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        serif: ['Merriweather', 'Georgia', 'serif'],
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Merriweather:wght@400;700&display=swap"
        rel="stylesheet">

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="font-sans bg-gray-50 text-gray-900 antialiased">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Journal Name -->
                <div class="flex items-center space-x-4">
                    <a href="{{ isset($journal) ? route('journal.public.home', ['journal' => $journal->slug]) : route('portal.home') }}"
                        class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg flex items-center justify-center">
                            <span
                                class="text-white font-bold text-lg">{{ substr($journal->abbreviation ?? 'J', 0, 1) }}</span>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-bold text-gray-900">{{ $journal->abbreviation ?? 'IAMJOS' }}</h1>
                            <p class="text-xs text-gray-500 -mt-0.5">{{ $journal->name ?? 'Academic Journal' }}</p>
                        </div>
                    </a>
                </div>

                <!-- Navigation -->
                @if (isset($journal))
                    <nav class="hidden md:flex items-center space-x-8">
                        <a href="{{ route('journal.public.home', ['journal' => $journal->slug]) }}"
                            class="text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">Home</a>
                        <a href="{{ route('journal.public.current', ['journal' => $journal->slug]) }}"
                            class="text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">Current
                            Issue</a>
                        <a href="{{ route('journal.public.archives', ['journal' => $journal->slug]) }}"
                            class="text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">Archives</a>
                        <a href="{{ route('journal.public.about', ['journal' => $journal->slug]) }}"
                            class="text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">About</a>
                        <a href="{{ route('journal.public.author-guidelines', ['journal' => $journal->slug]) }}"
                            class="text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">For
                            Authors</a>
                    </nav>
                @endif

                <!-- Actions -->
                <div class="flex items-center space-x-3">
                    <!-- Search Bar -->
                    @if (isset($journal))
                        <div x-data="{ searchOpen: false }" class="relative">
                            <!-- Search Toggle/Form -->
                            <div class="flex items-center">
                                <!-- Collapsed: Icon Button -->
                                <button x-show="!searchOpen"
                                    @click="searchOpen = true; $nextTick(() => $refs.searchInput.focus())"
                                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </button>

                                <!-- Expanded: Search Form -->
                                <form x-show="searchOpen" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95" @click.away="searchOpen = false"
                                    action="{{ route('journal.public.search', ['journal' => $journal->slug]) }}"
                                    method="GET" class="flex items-center">
                                    <div class="relative">
                                        <input type="text" name="q" x-ref="searchInput"
                                            placeholder="Search articles..."
                                            class="w-48 sm:w-64 pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <button type="button" @click="searchOpen = false"
                                        class="ml-2 p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @auth
                        <!-- User Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open"
                                class="flex items-center space-x-2 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                                @if (auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                                        class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div
                                        class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-full flex items-center justify-center">
                                        <span
                                            class="text-white text-xs font-semibold">{{ auth()->user()->initials }}</span>
                                    </div>
                                @endif
                                <span
                                    class="hidden sm:block text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" @click.away="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50"
                                x-cloak>
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('dashboard') }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    Dashboard
                                </a>
                                <a href="{{ route('profile.edit') }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Profile Settings
                                </a>
                                <div class="border-t border-gray-100 mt-2 pt-2">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                            <svg class="w-4 h-4 mr-3 text-red-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Sign Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium text-gray-600 hover:text-primary-600">Login</a>
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Submit Manuscript
                        </a>
                    @endauth

                    <!-- Mobile menu button -->
                    <button type="button"
                        class="md:hidden p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg" x-data
                        @click="$dispatch('toggle-mobile-menu')">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        @if (isset($journal))
            <div x-data="{ open: false }" @toggle-mobile-menu.window="open = !open" x-show="open" x-cloak
                class="md:hidden bg-white border-t border-gray-200">
                <div class="px-4 py-3 space-y-2">
                    <a href="{{ route('journal.public.home', ['journal' => $journal->slug]) }}"
                        class="block py-2 text-sm font-medium text-gray-600 hover:text-primary-600">Home</a>
                    <a href="{{ route('journal.public.current', ['journal' => $journal->slug]) }}"
                        class="block py-2 text-sm font-medium text-gray-600 hover:text-primary-600">Current Issue</a>
                    <a href="{{ route('journal.public.archives', ['journal' => $journal->slug]) }}"
                        class="block py-2 text-sm font-medium text-gray-600 hover:text-primary-600">Archives</a>
                    <a href="{{ route('journal.public.about', ['journal' => $journal->slug]) }}"
                        class="block py-2 text-sm font-medium text-gray-600 hover:text-primary-600">About</a>
                    <a href="{{ route('journal.public.author-guidelines', ['journal' => $journal->slug]) }}"
                        class="block py-2 text-sm font-medium text-gray-600 hover:text-primary-600">For Authors</a>
                </div>
            </div>
        @endif
    </header>

    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Journal Info -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg flex items-center justify-center">
                            <span
                                class="text-white font-bold text-lg">{{ substr($journal->abbreviation ?? 'J', 0, 1) }}</span>
                        </div>
                        <h3 class="text-lg font-bold text-white">{{ $journal->abbreviation ?? 'IAMJOS' }}</h3>
                    </div>
                    <p class="text-sm text-gray-400 mb-4">
                        {{ $journal->description ?? 'An open-access academic journal platform.' }}</p>
                    @if (isset($journal) && ($journal->issn_online || $journal->issn_print))
                        <div class="text-sm">
                            @if ($journal->issn_online)
                                <p><span class="text-gray-500">e-ISSN:</span> {{ $journal->issn_online }}</p>
                            @endif
                            @if ($journal->issn_print)
                                <p><span class="text-gray-500">p-ISSN:</span> {{ $journal->issn_print }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Quick Links -->
                @if (isset($journal))
                    <div>
                        <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Quick Links</h4>
                        <ul class="space-y-2">
                            <li><a href="{{ route('journal.public.current', ['journal' => $journal->slug]) }}"
                                    class="text-sm hover:text-white transition-colors">Current Issue</a></li>
                            <li><a href="{{ route('journal.public.archives', ['journal' => $journal->slug]) }}"
                                    class="text-sm hover:text-white transition-colors">Archives</a></li>
                            <li><a href="{{ route('journal.public.about', ['journal' => $journal->slug]) }}"
                                    class="text-sm hover:text-white transition-colors">About Journal</a></li>
                            <li><a href="{{ route('journal.public.editorial-team', ['journal' => $journal->slug]) }}"
                                    class="text-sm hover:text-white transition-colors">Editorial Team</a></li>
                        </ul>
                    </div>

                    <!-- For Authors -->
                    <div>
                        <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">For Authors</h4>
                        <ul class="space-y-2">
                            <li><a href="{{ route('journal.public.author-guidelines', ['journal' => $journal->slug]) }}"
                                    class="text-sm hover:text-white transition-colors">Author Guidelines</a></li>
                            <li><a href="{{ route('login') }}"
                                    class="text-sm hover:text-white transition-colors">Submit
                                    Manuscript</a></li>
                            <li><a href="{{ route('login') }}"
                                    class="text-sm hover:text-white transition-colors">Login /
                                    Register</a></li>
                        </ul>
                    </div>
                @endif
            </div>

            <div
                class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row items-center justify-between text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} {{ $journal->name ?? 'IAMJOS' }}. Powered by IAMJOS.</p>
                <a href="{{ route('portal.home') }}" class="mt-2 md:mt-0 hover:text-white transition-colors">
                    ← Back to Portal
                </a>
            </div>
        </div>
    </footer>
</body>

</html>
