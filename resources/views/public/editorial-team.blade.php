<x-public-layout :journal="$journal">
    @php $title = 'Editorial Team'; @endphp

    <section class="bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Editorial Team</h1>
            <p class="text-gray-600 mb-8">Meet our distinguished editors and reviewers who ensure the quality and integrity of published research.</p>

            {{-- Editorial Team Description (HTML from TinyMCE) --}}
            @if(!empty($journal->editorial_team_description))
            <div class="prose prose-lg max-w-none">
                {!! $journal->editorial_team_description !!}
            </div>
            @else
            {{-- Default content if no custom editorial team description is set --}}
            <div class="space-y-8">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Editor-in-Chief</h2>
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xl font-bold">
                                EC
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Editor-in-Chief</p>
                                <p class="text-sm text-gray-600">{{ $journal->publisher ?? $journal->name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Editorial Board</h2>
                    <div class="bg-gray-50 rounded-xl p-6">
                        <p class="text-gray-500 italic">
                            <i class="fa-solid fa-info-circle mr-2 text-gray-400"></i>
                            Editorial board members will be listed here. Journal managers can customize this content from the Settings page.
                        </p>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Reviewers</h2>
                    <div class="bg-gray-50 rounded-xl p-6">
                        <p class="text-gray-500 italic">
                            <i class="fa-solid fa-heart mr-2 text-red-400"></i>
                            We thank our dedicated peer reviewers for their invaluable contributions to maintaining the quality of published research.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Navigation Links --}}
            <div class="mt-12 pt-8 border-t border-gray-200 flex flex-wrap gap-4">
                <a href="{{ route('journal.public.about', $journal->slug) }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-colors">
                    <i class="fa-solid fa-book mr-2 text-indigo-600"></i>
                    About the Journal
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