{{-- Hero Search Block - Premium Academic Style --}}
@props(['block', 'data' => []])

@php
$config = $block->config ?? [];
$headline = $config['headline'] ?? 'Discover Academic Excellence';
$subheadline = $config['subheadline'] ?? 'Search across peer-reviewed journals and scholarly articles';
$backgroundType = $config['background_type'] ?? 'gradient';
$backgroundGradient = $config['background_gradient'] ?? 'from-slate-900 via-blue-900 to-indigo-900';
$backgroundImage = $config['background_image'] ?? null;
$showStats = $config['show_stats'] ?? true;
$showPopularTopics = $config['show_popular_topics'] ?? true;
$popularTopics = $config['popular_topics'] ?? ['AI', 'Education', 'Economics', 'Health'];

$totalJournals = $data['total_journals'] ?? 0;
$totalArticles = $data['total_articles'] ?? 0;
@endphp

<section class="relative min-h-[500px] md:min-h-[600px] flex items-center justify-center overflow-hidden"
    @if($backgroundType === 'image' && $backgroundImage)
        style="background-image: url('{{ Storage::url($backgroundImage) }}'); background-size: cover; background-position: center;"
    @endif
>
    {{-- Background --}}
    @if($backgroundType === 'gradient')
        <div class="absolute inset-0 bg-gradient-to-br {{ $backgroundGradient }}"></div>
    @elseif($backgroundType === 'image' && $backgroundImage)
        <div class="absolute inset-0 bg-slate-900/70"></div>
    @else
        <div class="absolute inset-0 bg-slate-900"></div>
    @endif

    {{-- Animated Background Pattern --}}
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)"/>
        </svg>
    </div>

    {{-- Floating Elements (Decoration) --}}
    <div class="absolute top-20 left-10 w-20 h-20 bg-blue-500/20 rounded-full blur-2xl animate-pulse"></div>
    <div class="absolute bottom-20 right-10 w-32 h-32 bg-indigo-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>

    {{-- Content --}}
    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10">
        {{-- Badge --}}
        <div class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-white/90 text-sm mb-6 border border-white/20">
            <i class="fa-solid fa-graduation-cap mr-2 text-blue-400"></i>
            Indonesian Academic Journal System
        </div>

        {{-- Headline --}}
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
            {!! $headline !!}
        </h1>

        {{-- Subheadline with Stats --}}
        <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
            @if($showStats && ($totalJournals > 0 || $totalArticles > 0))
                Search across <span class="font-bold text-white">{{ number_format($totalJournals) }}</span> journals and 
                <span class="font-bold text-white">{{ number_format($totalArticles) }}</span> peer-reviewed articles
            @else
                {{ $subheadline }}
            @endif
        </p>

        {{-- Search Bar --}}
        <form action="{{ route('portal.search') }}" method="GET" class="max-w-3xl mx-auto">
            <div class="relative flex items-center bg-white rounded-2xl shadow-2xl overflow-hidden p-2">
                {{-- Search Icon --}}
                <div class="absolute left-6 text-gray-400">
                    <i class="fa-solid fa-search text-xl"></i>
                </div>

                {{-- Input --}}
                <input type="text" 
                       name="q" 
                       placeholder="Search articles, authors, journals, DOIs..."
                       class="flex-1 w-full py-5 pl-14 pr-4 text-lg text-gray-900 placeholder-gray-400 border-0 focus:ring-0 focus:outline-none"
                       autocomplete="off">

                {{-- Search Button --}}
                <button type="submit" 
                        class="hidden md:flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-search mr-2"></i>
                    Search
                </button>
            </div>

            {{-- Mobile Search Button --}}
            <button type="submit" 
                    class="md:hidden w-full mt-3 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl">
                <i class="fa-solid fa-search mr-2"></i>
                Search
            </button>
        </form>

        {{-- Popular Topics --}}
        @if($showPopularTopics && count($popularTopics) > 0)
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <span class="text-sm text-blue-200">Popular:</span>
                @foreach($popularTopics as $topic)
                    <a href="{{ route('portal.search', ['q' => $topic]) }}"
                       class="px-4 py-2 bg-white/10 backdrop-blur-sm text-white text-sm rounded-full hover:bg-white/20 border border-white/20 transition-all">
                        {{ $topic }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Quick Stats --}}
        @if($showStats)
            <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto">
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-white" data-count="{{ $totalJournals }}">
                        {{ number_format($totalJournals) }}
                    </div>
                    <div class="text-sm text-blue-200 mt-1">Journals</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-white" data-count="{{ $totalArticles }}">
                        {{ number_format($totalArticles) }}
                    </div>
                    <div class="text-sm text-blue-200 mt-1">Articles</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-white" data-count="{{ $data['total_authors'] ?? 0 }}">
                        {{ number_format($data['total_authors'] ?? 0) }}
                    </div>
                    <div class="text-sm text-blue-200 mt-1">Authors</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-white">
                        50+
                    </div>
                    <div class="text-sm text-blue-200 mt-1">Countries</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Bottom Wave --}}
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="w-full h-16 md:h-24 fill-gray-50">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C57.14,118.92,134.3,89.4,198.11,79.57,261.91,69.73,264.36,67.22,321.39,56.44Z"/>
        </svg>
    </div>
</section>
