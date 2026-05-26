{{-- Journal Landing Page (OJS 3.3 Modern Style) --}}
@php
    $primaryColor = $settings['primary_color'] ?? '#0369a1';
    $secondaryColor = $settings['secondary_color'] ?? '#7c3aed';

    // OJS 3.3 Logic: Show homepage image in body only if NOT used as header background
    $showHomepageImageInBody = $journal->homepage_image_path && !$journal->show_homepage_image_in_header;
@endphp

<x-layouts.public :journal="$journal" :settings="$settings" :title="$journal->name . ' - Home'">
    {{-- Announcement Banner (if any urgent announcements) --}}
    @if ($announcements->where('is_urgent', true)->isNotEmpty())
        @php $urgentAnnouncement = $announcements->where('is_urgent', true)->first(); @endphp
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6 rounded-r-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-bullhorn text-amber-500 text-lg"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-semibold text-amber-800">{{ $urgentAnnouncement->title }}</p>
                    <p class="text-sm text-amber-700 mt-1">
                        {{ Str::limit($urgentAnnouncement->excerpt ?? $urgentAnnouncement->content, 150) }}</p>
                </div>
                <a href="#announcements" class="text-sm text-amber-600 hover:underline">View all</a>
            </div>
        </div>
    @endif

    {{-- Homepage Image (OJS 3.3: Show in body when NOT used as header background) --}}
    @if ($showHomepageImageInBody)
        <div class="mb-8">
            <img src="{{ Storage::disk('public')->url($journal->homepage_image_path) }}" alt="{{ $journal->name }}"
                class="w-full h-auto rounded-lg shadow-md">
        </div>
    @endif

    {{-- MAIN CONTENT (Full Width) --}}
    <div>
        <div class="w-full">

            {{-- Journal Summary Section --}}
            @if ($journal->show_summary && !empty($journal->summary))
                <section class="mb-8 px-5">
                    <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                                style="background: {{ $primaryColor }}15;">
                                <i class="fa-solid fa-book-open text-xl" style="color: {{ $primaryColor }};"></i>
                            </div>
                            <h2 class="text-[22px] font-bold text-slate-900 leading-tight">
                                Welcome to {{ $journal->name }}
                            </h2>
                        </div>
                        <div class="text-[14px] font-medium text-slate-600 leading-relaxed text-justify prose prose-slate max-w-none">
                            {!! clean($journal->summary) !!}
                        </div>
                    </div>
                </section>
            @endif

            {{-- Current Issue Section (OJS 3.3 Style) --}}
            @if ($currentIssue)
                <section class="mb-8">
                    {{-- Section Header (OJS Style) --}}
                    <div class="mb-8">
                        <h2
                            class="text-xl font-bold text-slate-700 uppercase tracking-wide inline-block border-b-4 border-orange-400 pb-1">
                            Current Issue
                        </h2>
                    </div>

                    {{-- Issue Info Block (Cover + Description) --}}
                    <div
                        class="flex flex-col md:flex-row gap-6 mb-10 bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6">
                        {{-- Cover Image (Fixed Size) --}}
                        <div class="flex-shrink-0">
                            @if ($currentIssue->cover_path)
                                <a href="{{ route('journal.public.current', $journal->slug) }}">
                                    <img src="{{ Storage::disk('public')->url($currentIssue->cover_path) }}"
                                        alt="Cover Vol {{ $currentIssue->volume }}"
                                        class="w-48 h-auto rounded shadow-lg border border-slate-200 hover:shadow-xl transition-shadow mx-auto md:mx-0">
                                </a>
                            @else
                                <div
                                    class="w-48 h-64 bg-slate-100 flex items-center justify-center text-slate-400 rounded border border-slate-200 mx-auto md:mx-0">
                                    <i class="fa-regular fa-image text-4xl"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Issue Details --}}
                        <div class="flex-1 min-w-0">
                            <h3 class="text-2xl font-bold text-slate-900 mb-3 leading-tight">
                                Vol. {{ $currentIssue->volume }} No. {{ $currentIssue->number }}
                                ({{ $currentIssue->year }})@if ($currentIssue->title)
                                    : {{ $currentIssue->title }}
                                @endif
                            </h3>

                            {{-- Description (HTML Rendered) --}}
                            @if ($currentIssue->description)
                                <div class="prose prose-sm prose-slate max-w-none mb-4">
                                    {!! clean($currentIssue->description) !!}
                                </div>
                            @endif

                            {{-- Metadata --}}
                            <div class="space-y-2 text-sm text-slate-700 mt-4">
                                @if ($currentIssue->published_at)
                                    <p>
                                        <span class="font-bold text-slate-900">PUBLISHED:</span>
                                        {{ $currentIssue->published_at->format('Y-m-d') }}
                                    </p>
                                @endif

                                @if ($currentIssue->metadata['doi'] ?? false)
                                    <p>
                                        <span class="font-bold text-slate-900">DOI:</span>
                                        <a href="https://doi.org/{{ $currentIssue->metadata['doi'] }}"
                                            class="text-blue-600 hover:underline break-all" target="_blank">
                                            https://doi.org/{{ $currentIssue->metadata['doi'] }}
                                        </a>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Articles List (Table of Contents) --}}
                    @php
                        $issueArticles = $currentIssue
                            ->submissions()
                            ->where('status', 'published')
                            ->with(['authors', 'galleys', 'currentPublication'])
                            ->orderBy('created_at')
                            ->get();
                    @endphp

                    @if ($issueArticles->isNotEmpty())
                        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6">
                            <h3 class="text-xl font-bold text-slate-700 border-b border-slate-200 pb-2 mb-6">Articles
                            </h3>

                            <div class="space-y-8">
                                @foreach ($issueArticles as $article)
                                    <x-public.article-row :article="$article" :journal="$journal" />
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="bg-slate-50 rounded-lg p-8 text-center">
                            <i class="fa-regular fa-folder-open text-4xl text-slate-300 mb-3"></i>
                            <p class="text-slate-500">No articles published in this issue yet.</p>
                        </div>
                    @endif
                </section>
            @else
                {{-- No Current Issue --}}
                <section class="mb-8 px-5">
                    <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                                style="background: {{ $primaryColor }}15;">
                                <i class="fa-solid fa-book text-xl" style="color: {{ $primaryColor }};"></i>
                            </div>
                            <h2 class="text-[22px] font-bold text-slate-900 leading-tight">
                                Welcome to {{ $journal->name }}
                            </h2>
                        </div>
                        
                        <p class="text-[14px] font-medium text-slate-600 mb-8 text-justify leading-relaxed">
                            {{ $journal->description ?? 'A peer-reviewed scholarly journal dedicated to advancing knowledge and research.' }}
                        </p>

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('journal.submissions.create', $journal->slug) }}"
                                class="inline-flex items-center px-5 py-2.5 text-sm font-bold text-white rounded-xl transition-all shadow-sm hover:shadow-md"
                                style="background: {{ $primaryColor }};">
                                <i class="fa-solid fa-paper-plane mr-2"></i>
                                Submit Your Research
                            </a>
                            <a href="{{ route('journal.public.about', $journal->slug) }}"
                                class="inline-flex items-center px-5 py-2.5 text-sm font-bold border rounded-xl transition-colors hover:bg-slate-50"
                                style="color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">
                                <i class="fa-solid fa-info-circle mr-2"></i>
                                Learn More
                            </a>
                        </div>
                    </div>
                </section>

                {{-- Latest Articles (Alternative when no issue) --}}
                @if ($latestArticles->isNotEmpty())
                    <section class="mb-8 px-5">
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-xl font-bold text-slate-700 uppercase tracking-wide inline-block border-b-4 border-orange-400 pb-1">
                                Latest Articles
                            </h2>
                            <a href="{{ route('journal.public.archives', $journal->slug) }}" class="text-sm font-bold text-blue-600 hover:underline">
                                View All Archives
                            </a>
                        </div>

                        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6">
                            <div class="space-y-8">
                                @foreach ($latestArticles as $article)
                                    <x-public.article-row :article="$article" :journal="$journal" />
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif
            @endif

            {{-- Announcements Section --}}
            @if ($announcements->isNotEmpty())
                <section id="announcements" class="mb-8">
                    <div class="flex items-center justify-between mb-6 pb-3 border-b-2"
                        style="border-color: {{ $primaryColor }};">
                        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-bullhorn text-lg" style="color: {{ $primaryColor }};"></i>
                            Announcements
                        </h2>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($announcements as $announcement)
                            <div
                                class="bg-white rounded-lg shadow-sm border {{ $announcement->is_urgent ? 'border-amber-300 bg-amber-50/50' : 'border-slate-200' }} p-5 card-hover">
                                @if ($announcement->is_urgent)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-amber-700 bg-amber-100 rounded-full mb-2">
                                        <i class="fa-solid fa-star mr-1"></i> Important
                                    </span>
                                @endif
                                <p class="text-xs text-slate-500 mb-2">
                                    {{ \Carbon\Carbon::parse($announcement->created_at)->format('M d, Y') }}
                                </p>
                                <h3 class="text-base font-semibold text-slate-900 mb-2 line-clamp-2">
                                    {{ $announcement->title }}
                                </h3>
                                <p class="text-sm text-slate-600 line-clamp-3">
                                    {{ Str::limit($announcement->excerpt ?? strip_tags($announcement->content ?? ''), 120) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

        </div> {{-- End Main Content --}}

    </div> {{-- End Content Container --}}


    {{-- ADDITIONAL CONTENT (Indexed By, Sponsors, Partners) --}}
    @if (!empty($journal->additional_content))
        <section class="mt-12 pt-8 border-t border-slate-200">
            <div class="prose prose-slate max-w-none text-slate-600 text-center">
                {!! clean($journal->additional_content) !!}
            </div>
        </section>
    @endif
</x-layouts.public>
