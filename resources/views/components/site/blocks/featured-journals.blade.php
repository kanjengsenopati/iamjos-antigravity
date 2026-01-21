{{-- Featured Journals Block - Interactive Grid Cards --}}
@props(['block', 'data' => []])

@php
$config = $block->config ?? [];
$title = $config['title'] ?? 'Featured Journals';
$subtitle = $config['subtitle'] ?? 'Explore our top-rated peer-reviewed publications';
$layout = $config['layout'] ?? 'grid';
$columns = $config['columns'] ?? 4;
$limit = $config['limit'] ?? 8;
$showBadges = $config['show_badges'] ?? true;
$showStats = $config['show_stats'] ?? true;

$journals = $data['journals'] ?? collect();
@endphp

<section class="py-16 md:py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="text-center mb-12">
            <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-blue-600 bg-blue-100 rounded-full mb-4">
                <i class="fa-solid fa-star mr-2"></i>
                Featured
            </span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                {{ $title }}
            </h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                {{ $subtitle }}
            </p>
        </div>

        {{-- Journals Grid --}}
        @if($journals->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ $columns }} gap-6">
                @foreach($journals->take($limit) as $journal)
                    <x-site.journal-card :journal="$journal" :show-badges="$showBadges" :show-stats="$showStats" />
                @endforeach
            </div>

            {{-- View All Button --}}
            <div class="text-center mt-12">
                <a href="{{ route('portal.journals') }}"
                   class="inline-flex items-center px-8 py-4 bg-white border-2 border-gray-200 text-gray-700 font-semibold rounded-xl hover:border-blue-500 hover:text-blue-600 transition-all group">
                    View All Journals
                    <i class="fa-solid fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        @else
            <div class="text-center py-12 bg-white rounded-xl border border-gray-200">
                <i class="fa-solid fa-book text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No journals available yet.</p>
            </div>
        @endif
    </div>
</section>
