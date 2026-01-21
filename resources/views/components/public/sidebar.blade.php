{{-- Dynamic Public Sidebar Component (OJS 3.3 Style) --}}
@props(['journal', 'sidebarBlocks' => collect()])

@php
$primaryColor = $journal->getWebsiteSettings()['primary_color'] ?? '#0369a1';
@endphp

<div class="space-y-6">
    @forelse($sidebarBlocks as $block)
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            {{-- Block Header --}}
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                    @if($block->icon ?? false)
                        <i class="{{ $block->icon }}" style="color: {{ $primaryColor }};"></i>
                    @endif
                    {{ $block->title }}
                </h3>
            </div>

            {{-- Block Content --}}
            <div class="p-4">
                @if(($block->type ?? '') === 'custom')
                    {{-- Render Custom HTML Content --}}
                    <div class="prose prose-sm max-w-none text-slate-700">
                        {!! $block->content !!}
                    </div>
                @elseif(($block->type ?? '') === 'system' && ($block->component_name ?? false))
                    {{-- Render System Component Dynamically --}}
                    @try
                        <x-dynamic-component 
                            :component="$block->component_name" 
                            :journal="$journal" 
                            :block="$block" 
                        />
                    @catch (\Exception $e)
                        <p class="text-sm text-slate-500 italic">Component not available</p>
                    @endtry
                @endif
            </div>
        </div>
    @empty
        {{-- Default Sidebar Blocks when none configured --}}
        
        {{-- Information Block --}}
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-info-circle" style="color: {{ $primaryColor }};"></i>
                    Information
                </h3>
            </div>
            <div class="p-4">
                <ul class="space-y-2.5 text-sm">
                    <li>
                        <a href="{{ route('journal.public.about', $journal->slug) }}" 
                           class="flex items-center gap-2.5 text-slate-600 hover:text-slate-900 transition-colors">
                            <i class="fa-solid fa-book-open w-4 text-slate-400"></i>
                            For Readers
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('journal.public.author-guidelines', $journal->slug) }}" 
                           class="flex items-center gap-2.5 text-slate-600 hover:text-slate-900 transition-colors">
                            <i class="fa-solid fa-pen w-4 text-slate-400"></i>
                            For Authors
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('journal.public.editorial-team', $journal->slug) }}" 
                           class="flex items-center gap-2.5 text-slate-600 hover:text-slate-900 transition-colors">
                            <i class="fa-solid fa-user-tie w-4 text-slate-400"></i>
                            For Librarians
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Make A Submission Block --}}
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane" style="color: {{ $primaryColor }};"></i>
                    Make a Submission
                </h3>
            </div>
            <div class="p-4">
                <p class="text-sm text-slate-600 mb-4">
                    Interested in submitting to this journal? We recommend that you review the 
                    <a href="{{ route('journal.public.about', $journal->slug) }}" class="text-blue-600 hover:underline">About the Journal</a> 
                    page for the journal's section policies, as well as the 
                    <a href="{{ route('journal.public.author-guidelines', $journal->slug) }}" class="text-blue-600 hover:underline">Author Guidelines</a>.
                </p>
                <a href="{{ route('journal.submissions.create', $journal->slug) }}"
                   class="block w-full text-center px-4 py-2.5 text-sm font-medium text-white rounded-lg transition-colors"
                   style="background: {{ $primaryColor }};">
                    <i class="fa-solid fa-paper-plane mr-2"></i>
                    Submit Article
                </a>
            </div>
        </div>

        {{-- Current Issue Block --}}
        @php
            $currentIssue = $journal->issues()->where('is_published', true)->orderBy('published_at', 'desc')->first();
        @endphp
        @if($currentIssue)
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-book-open" style="color: {{ $primaryColor }};"></i>
                        Current Issue
                    </h3>
                </div>
                <div class="p-4">
                    @if($currentIssue->cover_path)
                        <a href="{{ route('journal.public.current', $journal->slug) }}" class="block mb-4">
                            <img src="{{ Storage::url($currentIssue->cover_path) }}" 
                                 alt="{{ $currentIssue->display_title }}"
                                 class="w-full rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        </a>
                    @endif
                    <div class="text-center">
                        <a href="{{ route('journal.public.current', $journal->slug) }}" 
                           class="text-sm font-semibold hover:underline" style="color: {{ $primaryColor }};">
                            {{ $currentIssue->display_title }}
                        </a>
                        @if($currentIssue->published_at)
                            <p class="text-xs text-slate-500 mt-1">
                                Published: {{ $currentIssue->published_at->format('M d, Y') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Journal Metrics Block --}}
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-chart-line" style="color: {{ $primaryColor }};"></i>
                    Journal Metrics
                </h3>
            </div>
            <div class="p-4">
                @php
                    $publishedCount = $journal->submissions()->where('status', 'published')->count();
                    $issueCount = $journal->issues()->where('is_published', true)->count();
                @endphp
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-center p-3 bg-slate-50 rounded-lg">
                        <div class="text-lg font-bold text-slate-900">{{ $publishedCount }}</div>
                        <div class="text-xs text-slate-500">Articles</div>
                    </div>
                    <div class="text-center p-3 bg-slate-50 rounded-lg">
                        <div class="text-lg font-bold text-slate-900">{{ $issueCount }}</div>
                        <div class="text-xs text-slate-500">Issues</div>
                    </div>
                </div>
                @if($journal->issn_online || $journal->issn_print)
                    <div class="mt-4 pt-4 border-t border-slate-100 space-y-2">
                        @if($journal->issn_online)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-500">e-ISSN</span>
                                <span class="font-mono text-slate-900">{{ $journal->issn_online }}</span>
                            </div>
                        @endif
                        @if($journal->issn_print)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-500">p-ISSN</span>
                                <span class="font-mono text-slate-900">{{ $journal->issn_print }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

    @endforelse
</div>
