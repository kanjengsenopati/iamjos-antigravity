@php
    $journal = current_journal();
    $pageTitle = $filter === 'archives' ? 'Archives' : 'My Queue';
    $pageDesc =
        $filter === 'archives' ? 'View your published or declined submissions.' : 'Track your active submissions.';
@endphp

<x-app-layout>
    <x-slot name="title">{{ $pageTitle }}</x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $pageDesc }}</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('journal.submissions.create', ['journal' => $journal->slug]) }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Submission
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fa-solid fa-check-circle text-green-500 mr-3"></i>
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fa-solid fa-exclamation-circle text-red-500 mr-3"></i>
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Submissions List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        @if ($submissions->count() > 0)
            <ul class="divide-y divide-gray-200">
                @foreach ($submissions as $submission)
                    <li class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                                    class="text-lg font-semibold text-gray-900 hover:text-indigo-600 transition-colors">
                                    {{ $submission->title }}
                                </a>
                                <div class="mt-1 flex items-center gap-4 text-sm text-gray-500">
                                    @if ($submission->section)
                                        <span>
                                            <i class="fa-regular fa-folder mr-1"></i>
                                            {{ $submission->section->name }}
                                        </span>
                                    @endif
                                    <span>
                                        <i class="fa-regular fa-calendar mr-1"></i>
                                        {{ $submission->submitted_at?->format('M d, Y') ?? $submission->created_at->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 ml-4">
                                <!-- Status Badge -->
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @switch($submission->status)
                                        @case('draft') bg-gray-100 text-gray-800 @break
                                        @case('submitted') bg-blue-100 text-blue-800 @break
                                        @case('in_review') bg-yellow-100 text-yellow-800 @break
                                        @case('revision_required') bg-orange-100 text-orange-800 @break
                                        @case('accepted') bg-green-100 text-green-800 @break
                                        @case('rejected') bg-red-100 text-red-800 @break
                                        @case('published') bg-emerald-100 text-emerald-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch
                                ">
                                    {{ $submission->status_label }}
                                </span>

                                <a href="{{ route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    View
                                </a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $submissions->withQueryString()->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    {{ $filter === 'archives' ? 'No archived submissions found' : 'No active submissions' }}
                </h3>
                <p class="text-gray-500 mb-6">
                    {{ $filter === 'archives' ? 'Submissions that are published or declined will appear here.' : 'Get started by submitting your first article.' }}
                </p>
                @if ($filter !== 'archives')
                    <a href="{{ route('journal.submissions.create', ['journal' => $journal->slug]) }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Submission
                    </a>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
