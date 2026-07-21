@extends('layouts.app')

@section('title', 'Scholar IAMJOS Monitor - ' . $journal->name)

@section('content')
    <div class="space-y-6 px-5">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('journal.settings.tools.index', ['journal' => $journal->slug]) }}"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-[24px] bg-white text-slate-500 hover:text-blue-600 hover:bg-blue-50/50 transition-all shadow-[0_8px_30px_rgb(0,0,0,0.04)]"
                    title="Back to Tools">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <x-text.h1>Scholar IAMJOS Monitor</x-text.h1>
                    <x-text.body class="mt-1">Real-time monitoring of article visibility on Google Scholar.</x-text.body>
                </div>
            </div>
            <div>
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-widest bg-blue-600/10 text-blue-600">
                    <i class="fa-solid fa-robot mr-1.5"></i> Auto-checking enabled (Every 7 days)
                </span>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Total Monitored --}}
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 flex items-center justify-between">
                <div>
                    <x-text.label>Total Monitored</x-text.label>
                    <div class="flex items-baseline mt-1">
                        <span class="text-[22px] font-bold text-slate-900 leading-tight">{{ number_format($totalMonitored) }}</span>
                        <x-text.caption class="ml-2">articles</x-text.caption>
                    </div>
                </div>
                <div class="w-10 h-10 bg-blue-600/10 rounded-xl flex items-center justify-center text-blue-600">
                    <i class="fa-solid fa-list-check text-lg"></i>
                </div>
            </div>

            {{-- Indexed --}}
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 flex items-center justify-between">
                <div>
                    <x-text.label>Indexed</x-text.label>
                    <div class="flex items-baseline mt-1">
                        <x-text.amount>{{ number_format($indexedCount) }}</x-text.amount>
                        <x-text.caption class="ml-2">found</x-text.caption>
                    </div>
                </div>
                <div class="w-10 h-10 bg-emerald-600/10 rounded-xl flex items-center justify-center text-emerald-600">
                    <i class="fa-solid fa-check-circle text-lg"></i>
                </div>
            </div>

            {{-- Issues --}}
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 flex items-center justify-between">
                <div>
                    <x-text.label>Issues</x-text.label>
                    <div class="flex items-baseline mt-1">
                        <span class="text-[18px] font-bold text-red-600 leading-tight">{{ number_format($issuesCount) }}</span>
                        <x-text.caption class="ml-2">missing</x-text.caption>
                    </div>
                </div>
                <div class="w-10 h-10 bg-red-600/10 rounded-xl flex items-center justify-center text-red-600">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                </div>
            </div>

            {{-- Success Rate --}}
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 flex items-center justify-between">
                <div>
                    <x-text.label>Indexed Rate</x-text.label>
                    <div class="flex items-baseline mt-1">
                        <span class="text-[22px] font-bold text-slate-900 leading-tight">{{ $successRate }}%</span>
                    </div>
                </div>
                <div
                    class="w-10 h-10 rounded-xl flex items-center justify-center
                {{ $successRate >= 90 ? 'bg-emerald-600/10 text-emerald-600' : ($successRate >= 70 ? 'bg-amber-600/10 text-amber-600' : 'bg-red-600/10 text-red-600') }}">
                    <i class="fa-solid fa-chart-pie text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Main Monitor List --}}
        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
                <x-text.h2>Monitor List</x-text.h2>
                <div class="flex items-center gap-2">
                    {{-- Optional Filter Buttons could go here --}}
                </div>
            </div>

            <div class="divide-y divide-slate-50">
                @forelse ($submissions as $submission)
                    @php
                        $stat = $submission->indexStat;
                        // Determine status based on monitoring setting
                        $isMonitored = $stat && $stat->is_monitored;

                        if (!$isMonitored) {
                            $status = 'not_monitored';
                        } else {
                            $status = $stat
                                ? ($stat->is_indexed
                                    ? 'indexed'
                                    : ($stat->last_check_status === 'not_found'
                                        ? 'missing'
                                        : ($stat->last_check_status === 'error'
                                            ? 'error'
                                            : 'pending')))
                                : 'pending';
                        }

                        // Colors based on status
                        $borderClass = match ($status) {
                            'indexed' => 'bg-emerald-600',
                            'missing' => 'bg-red-600',
                            'error' => 'bg-amber-600',
                            'not_monitored' => 'bg-slate-300',
                            default => 'bg-blue-600',
                        };

                        $badgeClass = match ($status) {
                            'indexed' => 'bg-emerald-600/10 text-emerald-600',
                            'missing' => 'bg-red-600/10 text-red-600',
                            'error' => 'bg-amber-600/10 text-amber-600',
                            'not_monitored' => 'bg-slate-400/10 text-slate-400',
                            default => 'bg-blue-600/10 text-blue-600',
                        };

                        $badgeLabel = match ($status) {
                            'indexed' => 'Indexed',
                            'missing' => 'Not Found',
                            'error' => 'Check Failed',
                            'not_monitored' => 'Not Monitored',
                            default => 'Pending Check',
                        };
                    @endphp

                    <div class="group relative flex items-center justify-between p-4 hover:bg-slate-50/50 transition-colors">
                        {{-- Left Color Indicator --}}
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $borderClass }}"></div>

                        {{-- Main Content --}}
                        <div class="flex items-center gap-4 pl-3 flex-1 min-w-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-1">
                                    <x-text.body class="font-semibold text-slate-900 truncate group-hover:text-blue-600 transition-colors">
                                        {{ $submission->title }}
                                    </x-text.body>
                                    <span
                                        class="inline-flex items-center rounded-md px-2 py-1 text-xs font-bold {{ $badgeClass }}">
                                        {{ $badgeLabel }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <x-text.caption class="flex items-center truncate max-w-[200px]">
                                        <i class="fa-regular fa-user mr-1.5 text-slate-400"></i>
                                        {{ $submission->author->name ?? 'Unknown Author' }}
                                    </x-text.caption>
                                    @if ($isMonitored && $stat && $stat->last_checked_at)
                                        <x-text.caption class="flex items-center"
                                            title="{{ $stat->last_checked_at->format('d M Y H:i') }}">
                                            <i class="fa-regular fa-clock mr-1.5 text-slate-400"></i>
                                            Checked {{ $stat->last_checked_at->diffForHumans() }}
                                        </x-text.caption>
                                    @elseif($isMonitored)
                                        <x-text.caption class="flex items-center">
                                            <i class="fa-regular fa-clock mr-1.5 text-slate-400"></i>
                                            In Queue
                                        </x-text.caption>
                                    @endif

                                    @if ($isMonitored && $stat && $stat->scholar_url)
                                        <x-text.caption class="flex items-center text-blue-600 font-medium" title="Manual URL Configured">
                                            <i class="fa-solid fa-link mr-1"></i> Public URL
                                        </x-text.caption>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 ml-4">
                            @if ($stat && $stat->scholar_url)
                                <a href="{{ $stat->scholar_url }}" target="_blank"
                                    class="p-2 text-slate-400 hover:text-blue-600 transition-colors rounded-full hover:bg-blue-50"
                                    title="View on Google Scholar">
                                    <i class="fa-brands fa-google-scholar text-base"></i>
                                </a>
                            @endif

                            @if (!$isMonitored)
                                <button type="button"
                                    @click="$dispatch('open-scholar-modal', { 
                                            title: '{{ addslashes($submission->title) }}', 
                                            id: '{{ $submission->id }}', 
                                            url: '{{ $stat->scholar_url ?? route('journal.public.article', ['journal' => $journal->slug, 'article' => $submission->seq_id]) }}',
                                            isMonitored: false
                                        })"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                                    <i class="fa-solid fa-plus text-slate-400"></i>
                                    Add to Watchlist
                                </button>
                            @else
                                <div class="flex gap-2">
                                    <button type="button"
                                        @click="$dispatch('open-scholar-modal', { 
                                            title: '{{ addslashes($submission->title) }}', 
                                            id: '{{ $submission->id }}', 
                                            url: '{{ $stat->scholar_url ?? route('journal.public.article', ['journal' => $journal->slug, 'article' => $submission->seq_id]) }}',
                                            isMonitored: true
                                        })"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                                        <i class="fa-solid fa-gear text-slate-400"></i>
                                        Config
                                    </button>

                                    {{-- Quick Check (Only if monitored) --}}
                                    <form
                                        action="{{ route('journal.settings.stats.scholar.check', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="p-2 text-slate-400 hover:text-blue-600 transition-colors rounded-full hover:bg-blue-50"
                                            title="Check Now">
                                            <i class="fa-solid fa-rotate-right"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div
                            class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                            <i class="fa-solid fa-robot text-2xl"></i>
                        </div>
                        <x-text.h2 class="mb-1">No Articles Monitored Yet</x-text.h2>
                        <x-text.body>Once you publish articles, they will appear here automatically.</x-text.body>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($submissions->hasPages())
                <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/50">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    </div>
    {{-- Shared Modal --}}
    <div x-data="{
        open: false,
        title: '',
        submissionId: '',
        url: '',
        action: '',
        initModal(title, id, currentUrl, isMonitored) {
            this.title = title;
            this.submissionId = id;
            this.url = currentUrl;
            this.open = true;
        }
    }"
        @open-scholar-modal.window="initModal($event.detail.title, $event.detail.id, $event.detail.url, $event.detail.isMonitored)"
        x-show="open" class="relative z-50" style="display: none;">

        <div class="fixed inset-0 bg-slate-900 bg-opacity-40 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-[24px] bg-white text-left shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <form method="POST"
                        :action="'{{ route('journal.settings.stats.scholar.index', ['journal' => $journal->slug]) }}/' +
                        submissionId">
                        @csrf
                        @method('PUT')
                        <div class="bg-white px-6 pb-6 pt-6">
                            <x-text.h2 x-text="'Configure Monitoring: ' + title"></x-text.h2>
                            <div class="mt-4">
                                <x-text.label for="scholar_url" class="block mb-2">Article Public URL</x-text.label>
                                <div class="mt-1">
                                    <input type="url" name="scholar_url" id="scholar_url" x-model="url"
                                        class="block w-full rounded-lg border-slate-200 py-2 px-3 text-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] focus:border-blue-500 focus:ring-blue-500 text-sm leading-6"
                                        placeholder="https://journal.com/index.php/abc/article/view/100">
                                </div>
                                <x-text.caption class="block mt-2">Enter the full URL of this article on your journal website. We will search Google Scholar for this exact link.</x-text.caption>
                            </div>
                        </div>
                        <div class="bg-slate-50/50 px-6 py-4 sm:flex sm:flex-row-reverse justify-between gap-2 border-t border-slate-50">
                            <div class="flex flex-row-reverse gap-2">
                                <button type="submit" name="action" value="monitor"
                                    class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto transition-colors">
                                    Save & Monitor
                                </button>
                                <button type="button" @click="open = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Cancel</button>
                            </div>

                            <button type="submit" name="action" value="pause"
                                class="inline-flex w-full justify-center rounded-lg bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 shadow-sm hover:bg-red-100 sm:w-auto border border-red-100 transition-colors"
                                onclick="return confirm('Are you sure you want to pause monitoring for this article?')">
                                Pause Monitoring
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
