@php
    $journal = current_journal();
@endphp

<x-app-layout>
    <x-slot name="title">My Reviews</x-slot>

    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">My Reviews</h1>
        <p class="mt-1 text-sm text-gray-500">Submissions assigned to you for peer review.</p>
    </x-slot>

    <!-- Status Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex items-center space-x-2">
                <span
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-100 text-yellow-700 text-sm font-bold">
                    {{ $statusCounts['pending'] }}
                </span>
                <span class="text-sm text-gray-600">Pending</span>
            </div>
            <div class="flex items-center space-x-2">
                <span
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 text-sm font-bold">
                    {{ $statusCounts['accepted'] }}
                </span>
                <span class="text-sm text-gray-600">In Progress</span>
            </div>
            <div class="flex items-center space-x-2">
                <span
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-700 text-sm font-bold">
                    {{ $statusCounts['completed'] }}
                </span>
                <span class="text-sm text-gray-600">Completed</span>
            </div>
            @if ($statusCounts['overdue'] > 0)
                <div class="flex items-center space-x-2">
                    <span
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-700 text-sm font-bold">
                        {{ $statusCounts['overdue'] }}
                    </span>
                    <span class="text-sm text-red-600 font-medium">Overdue</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Assignments List -->
    @if ($assignments->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="text-center py-16">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No review assignments</h3>
                <p class="text-gray-500">You have no submissions assigned for review.</p>
            </div>
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
