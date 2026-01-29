@php
    $journal = current_journal();

    // Determine tab labels based on filter
    $tabLabels = [
        'queue' => 'My Assigned',
        'unassigned' => 'Unassigned',
        'active' => 'All Active',
        'archives' => 'Archives',
    ];
    $currentTabLabel = $tabLabels[$filter] ?? 'Submissions';
@endphp

<x-app-layout>
    <x-slot name="title">Submissions</x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Submissions</h1>
                <p class="mt-1 text-sm text-gray-500">Manage all submissions for {{ $journal->name }}.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('journal.submissions.create', ['journal' => $journal->slug]) }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                    <i class="fa-solid fa-plus mr-2"></i>
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

    <!-- ============================================= -->
    <!-- Submissions Container with Tab Navigation -->
    <!-- ============================================= -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        <!-- Tab Navigation (OJS 3.3 Style) -->
        <div class="border-b border-gray-200 bg-gray-50/50">
            <nav class="flex overflow-x-auto" aria-label="Tabs">
                @if ($isEditor)
                    {{-- Editor+ Tab Navigation --}}
                    <a href="{{ route('journal.submissions.index', ['journal' => $journal->slug]) }}?filter=queue"
                        class="relative py-4 px-6 text-center text-sm font-medium whitespace-nowrap transition-colors
                       {{ $filter === 'queue' ? 'border-b-2 border-indigo-500 text-indigo-600 bg-white' : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300' }}">
                        My Queue
                        <span
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                            {{ $filter === 'queue' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $statusCounts['queue'] ?? 0 }}
                        </span>
                    </a>
                    <a href="{{ route('journal.submissions.index', ['journal' => $journal->slug]) }}?filter=unassigned"
                        class="relative py-4 px-6 text-center text-sm font-medium whitespace-nowrap transition-colors
                       {{ $filter === 'unassigned' ? 'border-b-2 border-indigo-500 text-indigo-600 bg-white' : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300' }}">
                        Unassigned
                        <span
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                            {{ $filter === 'unassigned' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $statusCounts['unassigned'] ?? 0 }}
                        </span>
                    </a>
                    <a href="{{ route('journal.submissions.index', ['journal' => $journal->slug]) }}?filter=active"
                        class="relative py-4 px-6 text-center text-sm font-medium whitespace-nowrap transition-colors
                       {{ $filter === 'active' ? 'border-b-2 border-indigo-500 text-indigo-600 bg-white' : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300' }}">
                        All Active
                        <span
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                            {{ $filter === 'active' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $statusCounts['active'] ?? 0 }}
                        </span>
                    </a>
                    <a href="{{ route('journal.submissions.index', ['journal' => $journal->slug]) }}?filter=archives"
                        class="relative py-4 px-6 text-center text-sm font-medium whitespace-nowrap transition-colors
                       {{ $filter === 'archives' ? 'border-b-2 border-indigo-500 text-indigo-600 bg-white' : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300' }}">
                        Archives
                        <span
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                            {{ $filter === 'archives' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $statusCounts['archives'] ?? 0 }}
                        </span>
                    </a>
                @else
                    {{-- Author Tab Navigation --}}
                    <a href="{{ route('journal.submissions.index', ['journal' => $journal->slug]) }}?filter=active"
                        class="relative py-4 px-6 text-center text-sm font-medium whitespace-nowrap transition-colors
                       {{ $filter !== 'archives' ? 'border-b-2 border-indigo-500 text-indigo-600 bg-white' : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300' }}">
                        My Queue
                        <span
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                            {{ $filter !== 'archives' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $statusCounts['active'] ?? 0 }}
                        </span>
                    </a>
                    <a href="{{ route('journal.submissions.index', ['journal' => $journal->slug]) }}?filter=archives"
                        class="relative py-4 px-6 text-center text-sm font-medium whitespace-nowrap transition-colors
                       {{ $filter === 'archives' ? 'border-b-2 border-indigo-500 text-indigo-600 bg-white' : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300' }}">
                        Archives
                        <span
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                            {{ $filter === 'archives' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $statusCounts['archives'] ?? 0 }}
                        </span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- Tab Content Header: Current View Label + Search + Filters -->
        <div
            class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="text-base font-semibold text-gray-900">{{ $currentTabLabel }}</h2>

            <div class="flex items-center gap-3">
                <!-- Search -->
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" placeholder="Search"
                        class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 w-48">
                </div>

                <!-- Filters Button -->
                <button
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fa-solid fa-filter mr-2 text-gray-400"></i>
                    Filters
                </button>
            </div>
        </div>

        <!-- ============================================= -->
        <!-- Submissions List (OJS 3.3 Style) -->
        <!-- ============================================= -->
        @if ($submissions->count() > 0)
            <ul class="divide-y divide-gray-100">
                @foreach ($submissions as $submission)
                    @php
                        // Calculate discussion count
                        $discussionCount = $submission->discussions->count();

                        // Calculate reviewer progress (for review stage)
                        $reviewerCompleted = $submission->reviewAssignments->whereNotNull('date_completed')->count();
                        $reviewerTotal = $submission->reviewAssignments->count();

                        // Determine if submission is in review stage
                        $isInReview = $submission->stage === 'review' || $submission->status === 'in_review';

                        // Get primary author name
                        $primaryAuthor = $submission->authors->first();
                        $authorName = $primaryAuthor
                            ? $primaryAuthor->full_name ?? $primaryAuthor->first_name . ' ' . $primaryAuthor->last_name
                            : $submission->author?->name ?? 'Unknown Author';
                    @endphp

                    <li x-data="{ expanded: false }" class="hover:bg-gray-50/50 transition-colors">
                        <!-- Main Row -->
                        <div class="px-6 py-4 flex items-center justify-between gap-4">
                            <!-- Left: ID + Author + Title -->
                            <div class="flex items-center gap-4 min-w-0 flex-1">
                                <!-- Submission ID (Numeric like OJS) -->
                                <span class="text-sm font-medium text-gray-400 w-8 text-center flex-shrink-0">
                                    {{ $loop->iteration + ($submissions->currentPage() - 1) * $submissions->perPage() }}
                                </span>

                                <!-- Author & Title Stack -->
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $authorName }}</p>
                                    <a href="{{ route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $submission]) }}"
                                        class="text-sm text-gray-600 hover:text-indigo-600 hover:underline line-clamp-1">
                                        {{ $submission->title }}
                                    </a>
                                </div>
                            </div>

                            <!-- Right: Discussion/Reviewer + Status + View -->
                            <div class="flex items-center gap-3 flex-shrink-0">

                                <!-- Discussion/Reviewer Badge (OJS Style) -->
                                @if ($isInReview && $reviewerTotal > 0)
                                    {{-- Show Reviewer Progress: X/Y --}}
                                    <span
                                        class="inline-flex items-center gap-1.5 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-md"
                                        title="Reviewer progress: {{ $reviewerCompleted }} of {{ $reviewerTotal }} completed">
                                        <i class="fa-solid fa-user-check text-xs"></i>
                                        <span class="font-medium">{{ $reviewerCompleted }}/{{ $reviewerTotal }}</span>
                                    </span>
                                @elseif ($discussionCount > 0)
                                    {{-- Show Discussion Count --}}
                                    <span class="inline-flex items-center gap-1.5 text-sm text-gray-500"
                                        title="{{ $discussionCount }} discussion{{ $discussionCount > 1 ? 's' : '' }}">
                                        <i class="fa-regular fa-comment"></i>
                                        <span>{{ $discussionCount }}</span>
                                    </span>
                                @endif

                                <!-- Status Badge -->
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap
                                    @switch($submission->status)
                                        @case('draft') bg-gray-100 text-gray-700 @break
                                        @case('submitted') bg-blue-50 text-blue-700 border border-blue-200 @break
                                        @case('in_review') bg-amber-50 text-amber-700 border border-amber-200 @break
                                        @case('revision_required') bg-orange-50 text-orange-700 border border-orange-200 @break
                                        @case('accepted') bg-emerald-50 text-emerald-700 border border-emerald-200 @break
                                        @case('rejected') bg-red-50 text-red-700 border border-red-200 @break
                                        @case('published') bg-green-50 text-green-700 border border-green-200 @break
                                        @default bg-gray-100 text-gray-700
                                    @endswitch
                                ">
                                    {{ $submission->status_label }}
                                </span>

                                <!-- View Button -->
                                <a href="{{ route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $submission]) }}"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                    View
                                </a>

                                <!-- Expand/Collapse Button -->
                                <button @click="expanded = !expanded"
                                    class="p-1.5 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100 transition-colors">
                                    <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200"
                                        :class="{ 'rotate-180': expanded }"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Expanded Details (OJS Style) -->
                        <div x-show="expanded" x-collapse class="px-6 pb-4">
                            <div class="ml-12 pl-4 border-l-2 border-gray-200 space-y-2 py-2">
                                <!-- Section Info -->
                                @if ($submission->section)
                                    <p class="flex items-center gap-2 text-sm text-gray-600">
                                        <i class="fa-regular fa-folder text-gray-400 w-4"></i>
                                        <span>{{ $submission->section->name }}</span>
                                    </p>
                                @endif

                                <!-- Submission Code -->
                                <p class="flex items-center gap-2 text-sm text-gray-600">
                                    <i class="fa-solid fa-hashtag text-gray-400 w-4"></i>
                                    <span class="font-mono">{{ $submission->submission_code }}</span>
                                </p>

                                <!-- Discussion Summary -->
                                @if ($discussionCount > 0)
                                    <p class="flex items-center gap-2 text-sm text-gray-600">
                                        <i class="fa-regular fa-comment text-gray-400 w-4"></i>
                                        <span>{{ $discussionCount }} Open
                                            discussion{{ $discussionCount > 1 ? 's' : '' }}</span>
                                    </p>
                                @endif

                                <!-- Reviewer Info (if in review) -->
                                @if ($isInReview && $reviewerTotal > 0)
                                    <p class="flex items-center gap-2 text-sm text-gray-600">
                                        <i class="fa-solid fa-users text-gray-400 w-4"></i>
                                        <span>{{ $reviewerCompleted }} of {{ $reviewerTotal }} reviewers
                                            completed</span>
                                    </p>
                                @endif

                                <!-- Submitted Date -->
                                <p class="flex items-center gap-2 text-sm text-gray-500">
                                    <i class="fa-regular fa-calendar text-gray-400 w-4"></i>
                                    <span>Submitted on
                                        {{ $submission->submitted_at?->format('F j, Y') ?? $submission->created_at->format('F j, Y') }}</span>
                                </p>

                                <!-- Last Activity -->
                                <p class="flex items-center gap-2 text-sm text-gray-500">
                                    <i class="fa-regular fa-clock text-gray-400 w-4"></i>
                                    <span>Last activity recorded on
                                        {{ $submission->updated_at->format('l, F j, Y') }}.</span>
                                </p>

                                <!-- Activity Log & Notes Button (for Archives) -->
                                @if ($filter === 'archives')
                                    <div class="pt-2">
                                        <button type="button" 
                                                onclick="openLogModal('{{ route('submission.log.history', $submission->id) }}')"
                                                class="text-sm font-bold text-indigo-700 hover:text-indigo-900 hover:underline flex items-center gap-1 transition">
                                            Activity Log & Notes
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $submissions->withQueryString()->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 px-6">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-inbox text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    @if ($filter === 'unassigned')
                        No unassigned submissions
                    @elseif ($filter === 'archives')
                        No archived submissions
                    @elseif ($filter === 'queue')
                        No submissions in your queue
                    @else
                        No active submissions
                    @endif
                </h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    @if ($filter === 'unassigned')
                        All new submissions have been assigned to editors.
                    @elseif ($filter === 'archives')
                        Published or declined submissions will appear here.
                    @elseif ($filter === 'queue')
                        Submissions assigned to you will appear here.
                    @else
                        Get started by creating a new submission.
                    @endif
                </p>
                @if (!in_array($filter, ['archives', 'unassigned']))
                    <a href="{{ route('journal.submissions.create', ['journal' => $journal->slug]) }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                        <i class="fa-solid fa-plus mr-2"></i>
                        New Submission
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- Activity Log & Notes Modal --}}
    <div id="logModalBackdrop" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
        <div class="bg-white w-full max-w-4xl rounded-lg shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
            
            {{-- Content will be injected here --}}
            <div id="logModalContent" class="h-full">
                {{-- Loading Spinner Default --}}
                <div class="flex items-center justify-center h-64">
                    <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </div>
            
        </div>
    </div>

    {{-- Modal Script --}}
    <script>
        function openLogModal(url) {
            const modal = document.getElementById('logModalBackdrop');
            const content = document.getElementById('logModalContent');
            
            // 1. Show Modal with Loading State
            modal.classList.remove('hidden');
            content.innerHTML = '<div class="flex items-center justify-center h-64"><svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>';

            // 2. Fetch Data
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = '<div class="p-6 text-center text-red-500">Failed to load data.</div>';
                });
        }

        function closeLogModal() {
            document.getElementById('logModalBackdrop').classList.add('hidden');
        }

        // Close on click outside
        document.getElementById('logModalBackdrop').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogModal();
            }
        });
    </script>
</x-app-layout>
