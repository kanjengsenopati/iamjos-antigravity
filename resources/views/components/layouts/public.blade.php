{{-- Master Public Layout Component for Journal Pages (OJS 3.3 Parity) --}}
@props(['journal', 'settings' => [], 'title' => null, 'description' => null])

@php
$primaryColor = $settings['primary_color'] ?? '#0369a1';
$secondaryColor = $settings['secondary_color'] ?? '#7c3aed';

// OJS 3.3 Header Logic:
// If homepage_image exists AND show_homepage_image_in_header is TRUE -> show as header background
// Otherwise -> show standard branding header
$showImageInHeader = $journal->homepage_image_path && $journal->show_homepage_image_in_header;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
@if($journal->favicon_path ?? false)
<link rel="icon" href="{{ Storage::url($journal->favicon_path) }}">
@else
<link rel="icon" type="image/webp" href="{{ asset('assets/media/logos/logo.webp') }}">
<link rel="apple-touch-icon" href="{{ asset('assets/media/logos/logo.webp') }}">
@endif
<title>{{ $title ?? $journal->name ?? 'IAMJOS' }}</title>
@stack('meta_tags')
<meta name="description" content="{{ $description ?? ($journal->description ?? 'Open-access academic journal platform') }}">
<meta name="keywords" content="{{ $journal->keywords ?? 'academic, journal, research, publication, open access' }}">
<meta name="generator" content="IAMJOS - Indonesian Academic Journal System">
@if($journal->block_search_indexing)
<meta name="robots" content="noindex, nofollow">
@else
<meta name="robots" content="index, follow">
@endif
<link rel="canonical" href="{{ url()->current() }}">
<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $title ?? $journal->name }}">
<meta property="og:description" content="{{ $description ?? Str::limit($journal->description ?? '', 200) }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="{{ $journal->name ?? 'IAMJOS' }}">
@if($journal->logo_path ?? false)
<meta property="og:image" content="{{ Storage::url($journal->logo_path) }}">
@else
<meta property="og:image" content="{{ asset('assets/media/logos/logo.webp') }}">
<meta property="og:image:width" content="512">
<meta property="og:image:height" content="512">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title ?? $journal->name }}">
<meta name="twitter:description" content="{{ $description ?? Str::limit($journal->description ?? '', 200) }}">
@if($journal->logo_path ?? false)
<meta name="twitter:image" content="{{ Storage::url($journal->logo_path) }}">
@else
<meta name="twitter:image" content="{{ asset('assets/media/logos/logo.webp') }}">
@endif
{{-- DC metadata moved to page-level @push('meta_tags') to avoid duplicates --}}
@if($journal->custom_headers)
{!! $journal->custom_headers !!}
@endif
@if($journal->custom_meta_tags)
{!! $journal->custom_meta_tags !!}
@endif


    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Dynamic CSS Variables --}}
    <style>
        :root {
            --primary-color: {
                    {
                    $primaryColor
                }
            }

            ;

            --secondary-color: {
                    {
                    $secondaryColor
                }
            }

            ;

            --primary-50: {
                    {
                    $primaryColor
                }
            }

            10;

            --primary-100: {
                    {
                    $primaryColor
                }
            }

            20;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Gradient text utility */
        .text-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Button styles */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
            box-shadow: 0 10px 40px -10px var(--primary-color);
        }

        /* Card hover effect */
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.1);
        }

        /* Line clamp utilities */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Custom prose styling */
        .prose-journal {
            --tw-prose-body: #475569;
            --tw-prose-headings: #0f172a;
            --tw-prose-links: var(--primary-color);
        }
    </style>

    @stack('styles')
</head>

<body class="antialiased font-sans bg-slate-50 text-slate-900" x-data="{ mobileMenuOpen: false }">


    {{-- ============================================ --}}
    {{-- BRANDING HEADER (OJS 3.3 Logic) --}}
    {{-- ============================================ --}}
    @if($showImageInHeader)
    {{-- CASE 1: Homepage Image as Header Background --}}
    <header class="relative min-h-[200px] md:min-h-[280px] flex items-center"
        style="background-image: url('{{ Storage::url($journal->homepage_image_path) }}'); background-size: cover; background-position: center;">
        {{-- Dark Overlay for readability --}}
        <div class="absolute inset-0 bg-gradient-to-r from-slate-900/80 via-slate-900/60 to-slate-900/40"></div>

        {{-- Header Content --}}
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
            <div class="flex items-center space-x-6">
                {{-- Logo --}}
                @if($journal->logo_path)
                <img src="{{ Storage::url($journal->logo_path) }}"
                    alt="{{ $journal->name }}"
                    class="h-20 md:h-24 w-auto drop-shadow-lg">
                @else
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-xl flex items-center justify-center text-white text-3xl font-bold shadow-lg"
                    style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});">
                    {{ strtoupper(substr($journal->abbreviation ?? $journal->name ?? 'J', 0, 2)) }}
                </div>
                @endif

                {{-- Journal Title & Description --}}
                <div class="text-white">
                    <h1 class="text-2xl md:text-4xl font-serif font-bold drop-shadow-lg">
                        {{ $journal->name ?? 'Academic Journal' }}
                    </h1>
                    {{-- Dynamic ISSN Information Block --}}
                    @if($journal->issn_online || $journal->issn_print)
                    <div class="flex items-center gap-2 mt-2 text-sm text-white/90 drop-shadow">
                        @if($journal->issn_online)
                            @if($journal->url_issn_online)
                                <a href="{{ $journal->url_issn_online }}" target="_blank" class="hover:text-blue-400 hover:underline transition-colors duration-200">E-ISSN: {{ $journal->issn_online }}</a>
                            @else
                                <span>E-ISSN: {{ $journal->issn_online }}</span>
                            @endif
                        @endif

                        @if($journal->issn_online && $journal->issn_print)
                            <span class="mx-1 opacity-70">|</span>
                        @endif

                        @if($journal->issn_print)
                            @if($journal->url_issn_print)
                                <a href="{{ $journal->url_issn_print }}" target="_blank" class="hover:text-blue-400 hover:underline transition-colors duration-200">P-ISSN: {{ $journal->issn_print }}</a>
                            @else
                                <span>P-ISSN: {{ $journal->issn_print }}</span>
                            @endif
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </header>
    @else
    {{-- CASE 2: Standard Branding Header (No Background Image) --}}
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-5 py-8"> {{-- px-5 per PakRT rules --}}
            <div class="flex flex-col md:flex-row items-center md:items-center gap-4 md:gap-6 text-center md:text-left">
                {{-- Row 1: Logo --}}
                <div class="flex-shrink-0">
                    @if($journal->logo_path)
                    <img src="{{ Storage::url($journal->logo_path) }}"
                        alt="{{ $journal->name }}"
                        class="h-20 md:h-24 w-auto object-contain">
                    @else
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-[24px] flex items-center justify-center text-white shadow-lg"
                        style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});">
                        <x-text.h1 class="text-white">{{ strtoupper(substr($journal->abbreviation ?? $journal->name ?? 'J', 0, 2)) }}</x-text.h1>
                    </div>
                    @endif
                </div>

                {{-- Row 2 & 3 Container --}}
                <div class="flex flex-col gap-1">
                    {{-- Row 2: Journal Title --}}
                    <x-text.h1>
                        {{ $journal->name ?? 'Academic Journal' }}
                    </x-text.h1>

                    {{-- Row 3: ISSN Information --}}
                    @if($journal->issn_online || $journal->issn_print)
                    <div class="flex flex-wrap justify-center md:justify-start items-center gap-x-3 gap-y-1">
                        @if($journal->issn_online)
                            <x-text.caption class="not-italic">
                                <span class="font-bold text-slate-700">E-ISSN:</span> 
                                @if($journal->url_issn_online)
                                    <a href="{{ $journal->url_issn_online }}" target="_blank" class="hover:text-blue-600 transition-colors">{{ $journal->issn_online }}</a>
                                @else
                                    {{ $journal->issn_online }}
                                @endif
                            </x-text.caption>
                        @endif

                        @if($journal->issn_online && $journal->issn_print)
                            <span class="hidden md:block text-slate-300">|</span>
                        @endif

                        @if($journal->issn_print)
                            <x-text.caption class="not-italic">
                                <span class="font-bold text-slate-700">P-ISSN:</span>
                                @if($journal->url_issn_print)
                                    <a href="{{ $journal->url_issn_print }}" target="_blank" class="hover:text-blue-600 transition-colors">{{ $journal->issn_print }}</a>
                                @else
                                    {{ $journal->issn_print }}
                                @endif
                            </x-text.caption>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </header>
    @endif

    {{-- Dynamic Navigation Bar --}}
    <x-public.navbar :journal="$journal" :primary-menu="$primaryMenu ?? collect()" :user-menu="$userMenu ?? collect()" />

    {{-- ============================================ --}}
    {{-- MAIN LAYOUT CONTAINER --}}
    {{-- ============================================ --}}
    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{-- FLEX WRAPPER: Column on Mobile, Row on Desktop --}}
            <div class="flex flex-col lg:flex-row gap-8 items-start">

                {{-- LEFT COLUMN: Main Content --}}
                <main class="w-full {{ $sidebarBlocks->isNotEmpty() ? 'lg:w-3/4' : '' }} min-w-0 transition-all duration-300">
                    {{-- Flash Messages --}}
                    @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                    @endif
                    @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                        {{ session('error') }}
                    </div>
                    @endif

                    {{-- Page Content --}}
                    {{ $slot }}
                </main>

                {{-- RIGHT COLUMN: Sidebar (Only render if blocks exist) --}}
                @if($sidebarBlocks->isNotEmpty())
                <aside class="w-full lg:w-1/4 flex-shrink-0 space-y-6">
                    <x-public.sidebar :journal="$journal" :sidebar-blocks="$sidebarBlocks" />
                </aside>
                @endif

            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="bg-slate-900 text-slate-300 py-12 border-t border-slate-800 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- 1. DYNAMIC PAGE FOOTER (From Admin HTML Editor) --}}
            <div class="w-full mb-10 text-center">
                @if(!empty($journal->page_footer))
                {{-- Use 'prose-invert' for light text on dark bg --}}
                <div class="prose prose-sm prose-invert max-w-none text-slate-400 mx-auto">
                    {!! $journal->page_footer !!}
                </div>
                @else
                {{-- Default Fallback if empty --}}
                <div class="space-y-2">
                    <p>&copy; {{ date('Y') }} {{ $journal->name }}.</p>
                    @if($journal->issn_online || $journal->issn_print)
                    <p class="text-sm text-slate-500">
                        {{ $journal->issn_online ? 'e-ISSN: ' . $journal->issn_online : '' }}
                        {{ $journal->issn_print ? ' | p-ISSN: ' . $journal->issn_print : '' }}
                    </p>
                    @endif
                </div>
                @endif
            </div>

            {{-- 2. BRANDING --}}
            <div class="flex justify-center items-center border-t border-slate-800 pt-8">
                <a href="{{ env('APP_URL') }}" target="_blank" class="group flex flex-col items-center opacity-70 hover:opacity-100 transition">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider group-hover:text-slate-400">Platform & workflow by</span>
                    <span class="text-lg font-bold text-white group-hover:text-blue-400 transition-colors">IAMJOS</span>
                </a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>