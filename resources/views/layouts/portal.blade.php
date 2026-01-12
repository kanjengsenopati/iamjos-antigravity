<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'IAMJOS') - Portal Jurnal Akademik</title>
    <meta name="description" content="@yield('description', 'Platform Jurnal Akademik Indonesia')">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                            950: '#2e1065',
                        },
                        accent: {
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
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        display: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                        serif: ['Playfair Display', 'Georgia', 'serif'],
                    },
                    typography: {
                        DEFAULT: {
                            css: {
                                maxWidth: 'none',
                            },
                        },
                    },
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c4b5fd; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #8b5cf6; }

        /* Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* Hero Background Pattern */
        .hero-pattern {
            background-image:
                radial-gradient(at 40% 20%, hsla(262, 83%, 58%, 0.3) 0px, transparent 50%),
                radial-gradient(at 80% 0%, hsla(225, 89%, 67%, 0.2) 0px, transparent 50%),
                radial-gradient(at 0% 50%, hsla(280, 75%, 70%, 0.2) 0px, transparent 50%),
                radial-gradient(at 80% 50%, hsla(340, 65%, 68%, 0.15) 0px, transparent 50%),
                radial-gradient(at 0% 100%, hsla(269, 100%, 77%, 0.2) 0px, transparent 50%),
                radial-gradient(at 80% 100%, hsla(240, 80%, 70%, 0.15) 0px, transparent 50%);
        }

        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        /* Smooth scroll */
        html { scroll-behavior: smooth; }

        /* Prose styles for content */
        .prose-content h1, .prose-content h2, .prose-content h3, .prose-content h4 {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-weight: 700;
            color: #1f2937;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .prose-content h1 { font-size: 2.25rem; }
        .prose-content h2 { font-size: 1.875rem; }
        .prose-content h3 { font-size: 1.5rem; }
        .prose-content p { margin-bottom: 1.5rem; line-height: 1.8; color: #4b5563; }
        .prose-content ul, .prose-content ol { margin-bottom: 1.5rem; padding-left: 1.5rem; }
        .prose-content li { margin-bottom: 0.5rem; color: #4b5563; }
        .prose-content a { color: #7c3aed; text-decoration: underline; }
        .prose-content a:hover { color: #6d28d9; }
        .prose-content blockquote {
            border-left: 4px solid #8b5cf6;
            padding-left: 1.5rem;
            margin: 2rem 0;
            font-style: italic;
            color: #6b7280;
        }

        @stack('styles')
    </style>
</head>

<body class="font-sans bg-gray-50 text-gray-900 antialiased" x-data="{ mobileMenu: false, scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 50)">

    <!-- Navbar -->
    <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-white shadow-sm">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="{{ route('portal.home') }}" class="flex items-center space-x-3 group">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-500 via-accent-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-primary-500/30 transition-shadow">
                        <span class="text-white font-bold text-xl font-display">I</span>
                    </div>
                    <div class="hidden sm:block">
                        <h1 class="text-xl font-bold font-display text-gray-900">IAMJOS</h1>
                        <p class="text-xs -mt-0.5 text-gray-500">Portal Jurnal Akademik</p>
                    </div>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex items-center space-x-8">
                    <a href="{{ route('portal.home') }}" class="text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors {{ request()->routeIs('portal.home') ? 'text-primary-600' : '' }}">
                        Beranda
                    </a>
                    <a href="{{ route('portal.journals') }}" class="text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors {{ request()->routeIs('portal.journals') ? 'text-primary-600' : '' }}">
                        Jurnal
                    </a>
                    <a href="{{ route('portal.about') }}" class="text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors {{ request()->routeIs('portal.about') ? 'text-primary-600' : '' }}">
                        Tentang Kami
                    </a>
                </nav>

                <!-- Auth Buttons -->
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="hidden sm:inline-flex items-center px-5 py-2.5 text-sm font-medium rounded-xl bg-gradient-to-r from-primary-600 to-accent-600 text-white hover:from-primary-700 hover:to-accent-700 transition-all shadow-lg shadow-primary-500/25">
                            <i class="fas fa-th-large mr-2"></i>
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-5 py-2.5 text-sm font-medium rounded-xl bg-gradient-to-r from-primary-600 to-accent-600 text-white hover:from-primary-700 hover:to-accent-700 transition-all shadow-lg shadow-primary-500/25">
                            Daftar
                        </a>
                    @endauth

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 rounded-lg text-gray-600 hover:text-primary-600 hover:bg-gray-100 transition-colors">
                        <i class="fas" :class="mobileMenu ? 'fa-times' : 'fa-bars'" class="text-xl"></i>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Mobile Menu -->
        <div x-show="mobileMenu" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="lg:hidden bg-white border-t shadow-xl">
            <div class="px-4 py-6 space-y-4">
                <a href="{{ route('portal.home') }}" @click="mobileMenu = false" class="block text-gray-600 hover:text-primary-600 font-medium">Beranda</a>
                <a href="{{ route('portal.journals') }}" @click="mobileMenu = false" class="block text-gray-600 hover:text-primary-600 font-medium">Jurnal</a>
                <a href="{{ route('portal.about') }}" @click="mobileMenu = false" class="block text-gray-600 hover:text-primary-600 font-medium">Tentang Kami</a>
                @guest
                    <a href="{{ route('login') }}" class="block text-gray-600 hover:text-primary-600 font-medium">Masuk</a>
                @endguest
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-20">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        @php
            $footerContent = \App\Models\SiteContent::getAll();
        @endphp
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                <!-- About -->
                <div class="lg:col-span-2">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-500 via-accent-500 to-pink-500 rounded-xl flex items-center justify-center">
                            <span class="text-white font-bold text-xl font-display">I</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold font-display">IAMJOS</h3>
                            <p class="text-sm text-gray-400">Portal Jurnal Akademik</p>
                        </div>
                    </div>
                    <p class="text-gray-400 mb-6 max-w-md leading-relaxed">
                        {{ $footerContent['footer_about'] ?? 'IAMJOS adalah platform open-access untuk publikasi dan penyebaran karya ilmiah di Indonesia.' }}
                    </p>
                    <!-- Social Links -->
                    <div class="flex items-center space-x-4">
                        @if(!empty($footerContent['social_facebook']))
                            <a href="{{ $footerContent['social_facebook'] }}" target="_blank" class="w-10 h-10 bg-gray-800 hover:bg-primary-600 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        @endif
                        @if(!empty($footerContent['social_twitter']))
                            <a href="{{ $footerContent['social_twitter'] }}" target="_blank" class="w-10 h-10 bg-gray-800 hover:bg-primary-600 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fab fa-twitter"></i>
                            </a>
                        @endif
                        @if(!empty($footerContent['social_instagram']))
                            <a href="{{ $footerContent['social_instagram'] }}" target="_blank" class="w-10 h-10 bg-gray-800 hover:bg-primary-600 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fab fa-instagram"></i>
                            </a>
                        @endif
                        @if(!empty($footerContent['social_youtube']))
                            <a href="{{ $footerContent['social_youtube'] }}" target="_blank" class="w-10 h-10 bg-gray-800 hover:bg-primary-600 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fab fa-youtube"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-6">Tautan Cepat</h4>
                    <ul class="space-y-3">
                        <li><a href="{{ route('portal.journals') }}" class="text-gray-400 hover:text-white transition-colors">Daftar Jurnal</a></li>
                        <li><a href="{{ route('portal.about') }}" class="text-gray-400 hover:text-white transition-colors">Tentang Kami</a></li>
                        <li><a href="{{ route('portal.search') }}" class="text-gray-400 hover:text-white transition-colors">Cari Artikel</a></li>
                        @guest
                            <li><a href="{{ route('register') }}" class="text-gray-400 hover:text-white transition-colors">Daftar Akun</a></li>
                            <li><a href="{{ route('login') }}" class="text-gray-400 hover:text-white transition-colors">Masuk</a></li>
                        @endguest
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-lg font-semibold mb-6">Kontak</h4>
                    <ul class="space-y-4">
                        @if(!empty($footerContent['footer_address']))
                            <li class="flex items-start">
                                <i class="fas fa-map-marker-alt text-primary-400 mt-1 mr-3"></i>
                                <span class="text-gray-400">{{ $footerContent['footer_address'] }}</span>
                            </li>
                        @endif
                        @if(!empty($footerContent['footer_email']))
                            <li class="flex items-center">
                                <i class="fas fa-envelope text-primary-400 mr-3"></i>
                                <a href="mailto:{{ $footerContent['footer_email'] }}" class="text-gray-400 hover:text-white transition-colors">{{ $footerContent['footer_email'] }}</a>
                            </li>
                        @endif
                        @if(!empty($footerContent['footer_phone']))
                            <li class="flex items-center">
                                <i class="fas fa-phone text-primary-400 mr-3"></i>
                                <a href="tel:{{ $footerContent['footer_phone'] }}" class="text-gray-400 hover:text-white transition-colors">{{ $footerContent['footer_phone'] }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-gray-500 text-sm">
                        &copy; {{ date('Y') }} IAMJOS. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
