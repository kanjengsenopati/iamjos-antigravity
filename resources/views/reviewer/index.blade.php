@php
$journal = current_journal();
@endphp

<x-app-layout>
    <x-slot name="title">My Reviews</x-slot>

    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">My Reviews</h1>
        <p class="mt-1 text-sm text-gray-500">Submissions assigned to you for peer review.</p>
    </x-slot>

    <!-- Tabs Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            @php
            $currentStatus = request('status', 'pending');
            @endphp

            {{-- Pending Tab --}}
            <a href="{{ route('journal.reviewer.index', ['journal' => $journal->slug, 'status' => 'pending']) }}"
                class="{{ $currentStatus === 'pending'
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                    whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                Pending
                <span class="{{ $currentStatus === 'pending' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-900' }} ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block">
                    {{ $statusCounts['pending'] }}
                </span>
            </a>

            {{-- In Progress Tab --}}
            <a href="{{ route('journal.reviewer.index', ['journal' => $journal->slug, 'status' => 'in_progress']) }}"
                class="{{ $currentStatus === 'in_progress'
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                    whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                In Progress
                <span class="{{ $currentStatus === 'in_progress' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-900' }} ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block">
                    {{ $statusCounts['in_progress'] }}
                </span>
            </a>

            {{-- Completed Tab --}}
            <a href="{{ route('journal.reviewer.index', ['journal' => $journal->slug, 'status' => 'completed']) }}"
                class="{{ $currentStatus === 'completed'
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                    whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                Completed
                <span class="{{ $currentStatus === 'completed' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-900' }} ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block">
                    {{ $statusCounts['completed'] }}
                </span>
            </a>
        </nav>
    </div>

    <!-- Assignments List -->
    @if ($assignments->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
        @if ($currentStatus === 'pending')
        <div class="w-16 h-16 bg-yellow-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-regular fa-clock text-yellow-400 text-2xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No Pending Requests</h3>
        <p class="text-gray-500">You're all caught up! New review invitations will appear here.</p>
        @elseif ($currentStatus === 'in_progress')
        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-spinner text-blue-400 text-2xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No Reviews in Progress</h3>
        <p class="text-gray-500">Accept a pending invitation to start a review.</p>
        @else
        <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-check-double text-green-400 text-2xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No Completed Reviews</h3>
        <p class="text-gray-500">Reviews you complete will be archived here.</p>
        @endif
    </div>
    @else
    <div class="space-y-4">
        @foreach ($assignments as $assignment)
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Status Badge -->
                        <div class="flex items-center gap-2 mb-3">
                            @switch($assignment->status)
                            @case('pending')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Pending Response
                            </span>
                            @break

                            @case('accepted')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                In Progress
                            </span>
                            @break

                            @case('completed')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Completed
                            </span>
                            @break

                            @case('declined')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Declined
                            </span>
                            @break
                            @endswitch

                            @if ($assignment->isOverdue())
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Overdue
                            </span>
                            @endif

                            <span class="text-xs text-gray-500">Round {{ $assignment->round }}</span>
                        </div>

                        <!-- Title (Blind Review - No Author Info) -->
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            {{ $assignment->submission->title }}
                        </h3>

                        <!-- Meta Info -->
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                            <span>{{ $assignment->submission->section->name ?? 'Uncategorized' }}</span>
                            <span>•</span>
                            <span>Assigned: {{ $assignment->assigned_at?->format('M j, Y') }}</span>
                            @if ($assignment->due_date)
                            <span>•</span>
                            <span class="{{ $assignment->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                                Due: {{ $assignment->due_date->format('M j, Y') }}
                                @if (!$assignment->isOverdue() && $assignment->days_until_due !== null)
                                ({{ $assignment->days_until_due }} days)
                                @endif
                            </span>
                            @endif
                        </div>

                        <!-- Recommendation (if completed) -->
                        @if ($assignment->status === 'completed')
                        <div
                            class="mt-3 inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-{{ $assignment->recommendation_color }}-100 text-{{ $assignment->recommendation_color }}-800">
                            Your recommendation: {{ $assignment->recommendation_label }}
                        </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="ml-4 flex-shrink-0">
                        @if ($assignment->status === 'pending')
                        <div class="flex items-center gap-2">
                            <form action="{{ route('journal.reviewer.accept', ['journal' => $journal->slug, 'assignment' => $assignment]) }}" method="POST"
                                class="inline">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    Accept
                                </button>
                            </form>
                            <form action="{{ route('journal.reviewer.decline', ['journal' => $journal->slug, 'assignment' => $assignment]) }}" method="POST"
                                class="inline">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                    Decline
                                </button>
                            </form>
                        </div>
                        @elseif($assignment->status === 'accepted')
                        <a href="{{ route('journal.reviewer.show', ['journal' => $journal->slug, 'assignment' => $assignment]) }}"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Submit Review
                        </a>
                        @elseif($assignment->status === 'completed')
                        <a href="{{ route('journal.reviewer.show', ['journal' => $journal->slug, 'assignment' => $assignment]) }}"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                            View Review
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $assignments->links() }}
    </div>
    @endif
</x-app-layout>