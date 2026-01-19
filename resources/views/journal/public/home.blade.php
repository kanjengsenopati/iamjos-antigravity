@php
$primaryColor = $settings['primary_color'] ?? '#4F46E5';
$secondaryColor = $settings['secondary_color'] ?? '#7C3AED';
@endphp

<x-journal-public-layout :journal="$journal" :settings="$settings">
    <x-slot name="title">{{ $journal->name }} - Homepage</x-slot>

    {{-- Hero Section --}}
    <section class="relative min-h-[600px] flex items-center overflow-hidden">
        {{-- Background with gradient overlay --}}
        <div class="absolute inset-0">
            @if (!empty($settings['hero_image']))
            <img src="{{ Storage::url($settings['hero_image']) }}" alt="Hero" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-white via-white/95 to-white/80"></div>
            @else
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-white to-purple-50"></div>
            {{-- Decorative elements --}}
            <div class="absolute top-20 right-20 w-72 h-72 rounded-full opacity-30"
                style="background: linear-gradient(135deg, {{ $primaryColor }}20, {{ $secondaryColor }}20); filter: blur(60px);">
            </div>
            <div class="absolute bottom-20 right-40 w-96 h-96 rounded-full opacity-20"
                style="background: linear-gradient(135deg, {{ $secondaryColor }}30, {{ $primaryColor }}30); filter: blur(80px);">
            </div>
            @endif
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                {{-- Text Content --}}
                <div>
                    {{-- Tagline Badge --}}
                    <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium mb-6"
                        style="background: {{ $primaryColor }}10; color: {{ $primaryColor }};">
                        <i class="fa-solid fa-star mr-2"></i>
                        {{ $settings['hero_tagline'] ?? 'Peer-Reviewed • Open Access • Indexed' }}
                    </div>

                    {{-- Title --}}
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
                        {{ $settings['hero_title'] ?? $journal->name }}
                    </h1>

                    {{-- Description --}}
                    <p class="text-lg text-gray-600 mb-8 max-w-xl">
                        {{ $settings['hero_description'] ?? ($journal->description ?? 'A peer-reviewed scholarly journal dedicated to advancing knowledge and research in the field.') }}
                    </p>

                    {{-- CTA Buttons --}}
                    <div class="flex flex-wrap gap-4 mb-10">
                        <a href="{{ route('journal.submissions.create', $journal->slug) }}"
                            class="inline-flex items-center px-6 py-3 text-base font-semibold text-white rounded-xl shadow-lg transition-all hover:shadow-xl hover:-translate-y-0.5"
                            style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }}); box-shadow: 0 10px 40px {{ $primaryColor }}40;">
                            <i class="fa-solid fa-paper-plane mr-2"></i>
                            Submit Your Research
                        </a>
                        <a href="{{ route('journal.public.current', $journal->slug) }}"
                            class="inline-flex items-center px-6 py-3 text-base font-semibold rounded-xl border-2 transition-all hover:bg-gray-50"
                            style="color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">
                            <i class="fa-solid fa-book-open mr-2"></i>
                            Browse Current Issue
                        </a>
                    </div>

                    {{-- Stats Row --}}
                    @if (!empty($settings['show_stats']))
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="text-center p-4 rounded-xl bg-white/80 backdrop-blur border border-gray-100">
                            <div class="w-10 h-10 rounded-lg mx-auto mb-2 flex items-center justify-center"
                                style="background: {{ $primaryColor }}15;">
                                <i class="fa-solid fa-check-circle" style="color: {{ $primaryColor }};"></i>
                            </div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $settings['stat_acceptance_rate'] ?? '25%' }}
                            </div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Acceptance Rate</div>
                        </div>
                        <div class="text-center p-4 rounded-xl bg-white/80 backdrop-blur border border-gray-100">
                            <div class="w-10 h-10 rounded-lg mx-auto mb-2 flex items-center justify-center"
                                style="background: {{ $secondaryColor }}15;">
                                <i class="fa-solid fa-clock" style="color: {{ $secondaryColor }};"></i>
                            </div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $settings['stat_review_time'] ?? '4 Weeks' }}
                            </div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Review Time</div>
                        </div>
                        <div class="text-center p-4 rounded-xl bg-white/80 backdrop-blur border border-gray-100">
                            <div class="w-10 h-10 rounded-lg mx-auto mb-2 flex items-center justify-center"
                                style="background: {{ $primaryColor }}15;">
                                <i class="fa-solid fa-chart-line" style="color: {{ $primaryColor }};"></i>
                            </div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $settings['stat_impact_factor'] ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Impact Factor</div>
                        </div>
                        <div class="text-center p-4 rounded-xl bg-white/80 backdrop-blur border border-gray-100">
                            <div class="w-10 h-10 rounded-lg mx-auto mb-2 flex items-center justify-center"
                                style="background: {{ $secondaryColor }}15;">
                                <i class="fa-solid fa-quote-right" style="color: {{ $secondaryColor }};"></i>
                            </div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $settings['stat_citations'] ?? '1000+' }}
                            </div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Citations</div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Right Side: Journal Cover / Abstract Image --}}
                <div class="hidden lg:block relative">
                    <div class="relative">
                        {{-- Decorative Card Stack --}}
                        <div class="absolute -top-4 -left-4 w-full h-full rounded-2xl transform rotate-3"
                            style="background: {{ $primaryColor }}20;"></div>
                        <div class="absolute -top-2 -left-2 w-full h-full rounded-2xl transform rotate-1"
                            style="background: {{ $secondaryColor }}20;"></div>

                        {{-- Main Card --}}
                        <div class="relative bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">
                            <div class="text-center mb-6">
                                @if ($journal->logo_path)
                                <img src="{{ Storage::url($journal->logo_path) }}" alt="{{ $journal->name }}"
                                    class="h-20 mx-auto mb-4">
                                @else
                                <div class="w-20 h-20 rounded-xl mx-auto mb-4 flex items-center justify-center"
                                    style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});">
                                    <span
                                        class="text-white font-bold text-2xl">{{ strtoupper(substr($journal->abbreviation ?? $journal->name, 0, 2)) }}</span>
                                </div>
                                @endif
                                <h3 class="text-xl font-bold text-gray-900">{{ $journal->name }}</h3>
                                @if ($journal->issn_online)
                                <p class="text-sm text-gray-500 mt-1">ISSN: {{ $journal->issn_online }}</p>
                                @endif
                            </div>

                            @if ($currentIssue)
                            <div class="border-t border-gray-100 pt-6">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-sm font-medium text-gray-500">Current Issue</span>
                                    <span class="text-xs px-2 py-1 rounded-full text-white"
                                        style="background: {{ $primaryColor }};">
                                        Vol. {{ $currentIssue->volume }} No. {{ $currentIssue->number }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600">{{ $currentIssue->title ?? 'Latest Issue' }}</p>
                                <a href="{{ route('journal.public.current', $journal->slug) }}"
                                    class="inline-flex items-center text-sm font-medium mt-3"
                                    style="color: {{ $primaryColor }};">
                                    View Issue <i class="fa-solid fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                            @endif

                            <div class="border-t border-gray-100 pt-6 mt-6">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Published Articles</span>
                                    <span class="font-bold text-gray-900">{{ $publishedCount ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Journal Summary Section (from TinyMCE) --}}
    @if(!empty($journal->summary))
    <section class="py-16 bg-white border-b border-gray-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">About This Journal</h2>
            </div>
            <div class="prose prose-lg max-w-none prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-indigo-600 prose-strong:text-gray-900">
                {!! $journal->summary !!}
            </div>
            <div class="text-center mt-8">
                <a href="{{ route('journal.public.about', $journal->slug) }}"
                    class="inline-flex items-center px-6 py-3 text-base font-semibold rounded-xl border-2 transition-all hover:bg-gray-50"
                    style="color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">
                    <i class="fa-solid fa-info-circle mr-2"></i>
                    Learn More About Us
                </a>
            </div>
        </div>
    </section>
    @endif

    {{-- Announcements Section --}}
    @if (!empty($settings['show_announcements']) && $announcements->isNotEmpty())
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Latest Announcements</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Stay updated with the latest news and calls for papers
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                @foreach ($announcements as $announcement)
                <div class="rounded-xl p-6 transition-all hover:-translate-y-1 hover:shadow-lg {{ !empty($announcement->is_urgent) ? 'bg-gradient-to-br from-indigo-50 to-purple-50 border-2' : 'bg-gray-50 border border-gray-100' }}"
                    style="{{ !empty($announcement->is_urgent) ? 'border-color: ' . $primaryColor . ';' : '' }}">
                    @if (!empty($announcement->is_urgent))
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mb-3"
                        style="background: {{ $primaryColor }}; color: white;">
                        <i class="fa-solid fa-bell mr-1"></i> Important
                    </span>
                    @endif
                    <p class="text-xs text-gray-500 mb-2">
                        {{ \Carbon\Carbon::parse($announcement->created_at)->format('M d, Y') }}
                    </p>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $announcement->title }}</h3>
                    <p class="text-sm text-gray-600">
                        {{ Str::limit($announcement->excerpt ?? ($announcement->content ?? ''), 100) }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Editorial Team Section --}}
    @if (!empty($settings['show_editorial_team']) && $editorialTeam->isNotEmpty())
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Editorial Team</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Meet our distinguished editors and reviewers</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($editorialTeam as $editor)
                <div
                    class="bg-white rounded-xl p-6 text-center border border-gray-100 hover:shadow-lg transition-all">
                    {{-- Avatar --}}
                    <div class="mb-4">
                        @if (!empty($editor->avatar))
                        <img src="{{ Storage::url($editor->avatar) }}" alt="{{ $editor->name }}"
                            class="w-20 h-20 rounded-full mx-auto object-cover">
                        @else
                        <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center text-2xl font-bold text-white"
                            style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});">
                            {{ strtoupper(substr($editor->name, 0, 1)) }}
                        </div>
                        @endif
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900">{{ $editor->name }}</h3>
                    <p class="text-sm font-medium mb-1" style="color: {{ $primaryColor }};">
                        {{ $editor->role }}
                    </p>
                    <p class="text-sm text-gray-500 mb-4">{{ $editor->affiliation ?? '' }}</p>

                    <div class="flex justify-center space-x-3">
                        @if (!empty($editor->email))
                        <a href="mailto:{{ $editor->email }}"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fa-solid fa-envelope"></i>
                        </a>
                        @endif
                        @if (!empty($editor->orcid))
                        <a href="https://orcid.org/{{ $editor->orcid }}" target="_blank"
                            class="text-gray-400 hover:text-green-600 transition-colors">
                            <i class="fa-brands fa-orcid"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('journal.public.editorial-team', $journal->slug) }}"
                    class="inline-flex items-center text-sm font-medium transition-colors"
                    style="color: {{ $primaryColor }};">
                    View Full Editorial Board <i class="fa-solid fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>
    @endif

    {{-- Indexed In Section --}}
    @if (!empty($settings['show_indexed_in']) && count($indexedInImages) > 0)
    <section class="py-12 bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Indexed In</h3>
            </div>

            <div class="overflow-hidden">
                <div class="flex animate-marquee space-x-16">
                    @foreach ($indexedInImages as $image)
                    <div class="flex-shrink-0">
                        <img src="{{ Storage::url($image) }}" alt="Indexer"
                            class="h-12 w-auto grayscale hover:grayscale-0 transition-all opacity-60 hover:opacity-100">
                    </div>
                    @endforeach
                    {{-- Duplicate for seamless loop --}}
                    @foreach ($indexedInImages as $image)
                    <div class="flex-shrink-0">
                        <img src="{{ Storage::url($image) }}" alt="Indexer"
                            class="h-12 w-auto grayscale hover:grayscale-0 transition-all opacity-60 hover:opacity-100">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Call to Action Section --}}
    <section class="py-20 relative overflow-hidden">
        <div class="absolute inset-0"
            style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});"></div>
        <div class="absolute inset-0 opacity-10"
            style="background-image: url('data:image/svg+xml,<svg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'><g fill=\'none\' fill-rule=\'evenodd\'><g fill=\'%23ffffff\' fill-opacity=\'1\'><path d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/></g></g></svg>');">
        </div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Ready to Publish Your Research?</h2>
            <p class="text-lg text-white/90 mb-8 max-w-2xl mx-auto">
                Join our community of researchers and contribute to advancing knowledge in your field.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('journal.submissions.create', $journal->slug) }}"
                    class="inline-flex items-center px-8 py-4 text-base font-semibold rounded-xl bg-white shadow-xl hover:shadow-2xl transition-all hover:-translate-y-0.5"
                    style="color: {{ $primaryColor }};">
                    <i class="fa-solid fa-paper-plane mr-2"></i>
                    Submit Manuscript
                </a>
                <a href="{{ route('journal.public.author-guidelines', $journal->slug) }}"
                    class="inline-flex items-center px-8 py-4 text-base font-semibold rounded-xl border-2 border-white/30 text-white hover:bg-white/10 transition-all">
                    <i class="fa-solid fa-book mr-2"></i>
                    Author Guidelines
                </a>
            </div>
        </div>
    </section>

</x-journal-public-layout>