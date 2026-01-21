<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta Tags --}}
    <title>{{ $settings['site_title'] ?? 'IAMJOS' }} - Indonesian Academic Journal System</title>
    <meta name="description" content="{{ $settings['site_description'] ?? 'Discover peer-reviewed academic journals and research articles across multiple disciplines.' }}">
    <meta name="keywords" content="academic journals, research, publications, open access, Indonesia, scholarly articles">
    <meta name="generator" content="IAMJOS - Indonesian Academic Journal System">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $settings['site_title'] ?? 'IAMJOS' }}">
    <meta property="og:description" content="{{ $settings['site_description'] ?? 'Discover peer-reviewed academic journals.' }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="IAMJOS">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $settings['site_title'] ?? 'IAMJOS' }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&family=merriweather:400,700&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Line clamp utilities */
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    </style>

    @stack('styles')
</head>

<body class="antialiased font-sans bg-gray-50 text-gray-900" x-data="{ mobileMenuOpen: false }">
    {{-- ============================================ --}}
    {{-- NAVIGATION BAR --}}
    {{-- ============================================ --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('portal.home') }}" class="flex items-center gap-3">
                    @if($settings['site_logo'] ?? false)
                        <img src="{{ Storage::url($settings['site_logo']) }}" alt="IAMJOS" class="h-10">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold text-lg">
                            IJ
                        </div>
                    @endif
                    <span class="font-bold text-gray-900 hidden sm:block">IAMJOS</span>
                </a>

                {{-- Desktop Menu --}}
                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('portal.home') }}" class="text-gray-600 hover:text-gray-900 font-medium">Home</a>
                    <a href="{{ route('portal.journals') }}" class="text-gray-600 hover:text-gray-900 font-medium">Journals</a>
                    <a href="{{ route('portal.about') }}" class="text-gray-600 hover:text-gray-900 font-medium">About</a>
                    <a href="{{ route('portal.search') }}" class="text-gray-600 hover:text-gray-900 font-medium">Search</a>
                </div>

                {{-- Auth Buttons --}}
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="hidden md:inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                            <i class="fa-solid fa-gauge-high mr-2"></i>
                            Dashboard
                        </a>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2">
                                @if(auth()->user()->avatar)
                                    <img src="{{ Storage::url(auth()->user()->avatar) }}" class="w-8 h-8 rounded-full">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-medium">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                @endif
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}"
                           class="hidden md:inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                            Login
                        </a>
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700">
                            Register
                        </a>
                    @endauth

                    {{-- Mobile Menu Toggle --}}
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-gray-600">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileMenuOpen" x-cloak @click.away="mobileMenuOpen = false"
             class="md:hidden bg-white border-t border-gray-200 py-4">
            <div class="max-w-7xl mx-auto px-4 space-y-3">
                <a href="{{ route('portal.home') }}" class="block text-gray-700 hover:text-blue-600 py-2">Home</a>
                <a href="{{ route('portal.journals') }}" class="block text-gray-700 hover:text-blue-600 py-2">Journals</a>
                <a href="{{ route('portal.about') }}" class="block text-gray-700 hover:text-blue-600 py-2">About</a>
                <a href="{{ route('portal.search') }}" class="block text-gray-700 hover:text-blue-600 py-2">Search</a>
            </div>
        </div>
    </nav>

    {{-- Spacer for fixed nav --}}
    <div class="h-16"></div>

    {{-- ============================================ --}}
    {{-- DYNAMIC CONTENT BLOCKS --}}
    {{-- Render active blocks in sort_order --}}
    {{-- ============================================ --}}
    <main>
        @foreach($blocks as $block)
            @php
                $componentName = 'site.blocks.' . str_replace('_', '-', $block->key);
                $blockData = $blockData[$block->key] ?? [];
            @endphp

            @if(View::exists("components.{$componentName}"))
                <x-dynamic-component :component="$componentName" :block="$block" :data="$blockData" />
            @else
                {{-- Fallback for missing components (dev mode) --}}
                @if(config('app.debug'))
                    <div class="bg-yellow-50 border border-yellow-200 p-4 text-center text-yellow-700">
                        <i class="fa-solid fa-exclamation-triangle mr-2"></i>
                        Missing component: {{ $componentName }}
                    </div>
                @endif
            @endif
        @endforeach
    </main>

    {{-- ============================================ --}}
    {{-- FOOTER --}}
    {{-- ============================================ --}}
    <footer class="bg-slate-900 text-slate-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                {{-- About --}}
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold">
                            IJ
                        </div>
                        <span class="font-bold text-white">IAMJOS</span>
                    </div>
                    <p class="text-slate-400 text-sm mb-4 max-w-md">
                        {{ $settings['footer_description'] ?? 'Indonesian Academic Journal System - A modern platform for hosting and managing academic journals with OJS 3.3 feature parity.' }}
                    </p>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('portal.journals') }}" class="text-slate-400 hover:text-white transition-colors">All Journals</a></li>
                        <li><a href="{{ route('portal.search') }}" class="text-slate-400 hover:text-white transition-colors">Search Articles</a></li>
                        <li><a href="{{ route('portal.about') }}" class="text-slate-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="{{ route('login') }}" class="text-slate-400 hover:text-white transition-colors">Author Login</a></li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Contact</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        @if($settings['contact_email'] ?? false)
                            <li><i class="fa-solid fa-envelope mr-2"></i>{{ $settings['contact_email'] }}</li>
                        @endif
                        @if($settings['contact_phone'] ?? false)
                            <li><i class="fa-solid fa-phone mr-2"></i>{{ $settings['contact_phone'] }}</li>
                        @endif
                        @if($settings['contact_address'] ?? false)
                            <li><i class="fa-solid fa-location-dot mr-2"></i>{{ $settings['contact_address'] }}</li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Bottom Bar --}}
            <div class="border-t border-slate-800 mt-8 pt-8 flex flex-col md:flex-row items-center justify-between text-sm text-slate-500">
                <p>© {{ date('Y') }} IAMJOS. All rights reserved.</p>
                <p class="mt-4 md:mt-0">
                    Powered by <strong class="text-slate-400">IAMJOS</strong> - Indonesian Academic Journal System
                </p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
