<x-public-layout :journal="$journal">
    @php $title = 'Archives'; @endphp

    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Archives</h1>
                <p class="text-gray-500 mt-2">Browse all published issues</p>
            </div>

            <!-- Year Filter -->
            @if ($years->isNotEmpty())
                <div class="flex flex-wrap gap-2 mb-8">
                    <a href="{{ route('journal.public.archives', ['journal' => $journal->slug]) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium {{ !$year ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">
                        All Years
                    </a>
                    @foreach ($years as $y)
                        <a href="{{ route('journal.public.archives', ['journal' => $journal->slug, 'year' => $y]) }}"
                            class="px-4 py-2 rounded-lg text-sm font-medium {{ $year == $y ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">
                            {{ $y }}
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Issues Grid -->
            @if ($issues->isEmpty())
                <div class="bg-gray-50 rounded-xl p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No issues published yet</h3>
                    <p class="text-gray-500">Check back later for new publications.</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($issues as $issue)
                        <a href="{{ route('journal.public.issue', ['journal' => $journal->slug, 'issue' => $issue]) }}"
                            class="group">
                            <div class="bg-gray-50 rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
                                <!-- Cover -->
                                <div
                                    class="aspect-[3/4] bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                                    @if ($issue->cover_path)
                                        <img src="{{ Storage::url($issue->cover_path) }}" alt="Issue Cover"
                                            class="w-full h-full object-cover">
                                    @else
                                        <div class="text-center text-white p-6">
                                            <p class="text-sm font-medium">Vol. {{ $issue->volume }}</p>
                                            <p class="text-3xl font-bold">No. {{ $issue->number }}</p>
                                            <p class="text-sm mt-2">{{ $issue->year }}</p>
                                        </div>
                                    @endif
                                </div>
                                <!-- Info -->
                                <div class="p-4 bg-white">
                                    <h3
                                        class="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">
                                        {{ $issue->display_title }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $issue->submissions_count }} Articles
                                    </p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $issues->links() }}
                </div>
            @endif
        </div>
    </section>
</x-public-layout>
