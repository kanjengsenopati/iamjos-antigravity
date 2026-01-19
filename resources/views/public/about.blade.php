<x-public-layout :journal="$journal">
    @php $title = 'About the Journal'; @endphp

    <section class="bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">About the Journal</h1>

            {{-- About Content (HTML from TinyMCE) --}}
            @if(!empty($journal->about))
            <div class="prose prose-lg max-w-none mb-12">
                {!! $journal->about !!}
            </div>
            @else
            {{-- Default content if no custom about is set --}}
            <div class="prose prose-lg max-w-none mb-12">
                <h2>Journal Overview</h2>
                <p>{{ $journal->description ?? 'This journal is dedicated to advancing knowledge and research in its field.' }}</p>

                <h2>Peer Review Process</h2>
                <p>All manuscripts undergo double-blind peer review by at least two experts in the field.</p>

                <h2>Open Access Policy</h2>
                <p>This journal provides immediate open access to its content under Creative Commons licenses, making research freely available to the public.</p>
            </div>
            @endif

            {{-- Journal Summary Section --}}
            @if(!empty($journal->summary))
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fa-solid fa-book-open mr-2 text-indigo-600"></i>
                    Journal Summary
                </h3>
                <div class="prose prose-indigo max-w-none">
                    {!! $journal->summary !!}
                </div>
            </div>
            @endif

            {{-- Journal Information Card --}}
            <div class="bg-gray-50 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fa-solid fa-info-circle mr-2 text-gray-600"></i>
                    Journal Information
                </h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <dt class="text-sm text-gray-500">Publisher</dt>
                        <dd class="font-medium text-gray-900">{{ $journal->publisher ?? 'Not specified' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">ISSN (Online)</dt>
                        <dd class="font-medium text-gray-900 font-mono">{{ $journal->issn_online ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">ISSN (Print)</dt>
                        <dd class="font-medium text-gray-900 font-mono">{{ $journal->issn_print ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Access Type</dt>
                        <dd class="font-medium text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fa-solid fa-lock-open mr-1"></i>
                                Open Access
                            </span>
                        </dd>
                    </div>
                    @if($journal->abbreviation)
                    <div>
                        <dt class="text-sm text-gray-500">Abbreviation</dt>
                        <dd class="font-medium text-gray-900">{{ $journal->abbreviation }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Navigation Links --}}
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('journal.public.editorial-team', $journal->slug) }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-colors">
                    <i class="fa-solid fa-users mr-2 text-indigo-600"></i>
                    Editorial Team
                </a>
                <a href="{{ route('journal.public.home', $journal->slug) }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-colors">
                    <i class="fa-solid fa-home mr-2 text-indigo-600"></i>
                    Back to Homepage
                </a>
            </div>
        </div>
    </section>
</x-public-layout>