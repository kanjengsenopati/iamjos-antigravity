@php
    $journal = current_journal();
    $allDiscussions = $submission->discussions;

    // Map stage_id to stage key for Alpine state
    $stageMap = [
        1 => 'submission',
        2 => 'review',
        3 => 'copyediting',
        4 => 'production',
    ];
    $defaultStage = $stageMap[$submission->stage_id] ?? 'submission';
@endphp

<x-app-layout>
    <x-slot name="title">{{ $submission->title }}</x-slot>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

    <div x-data="{
        activeTab: 'workflow',
        activeStage: '{{ $defaultStage }}',
        fileModalOpen: false,
        discussionModalOpen: false,
        fileWizardOpen: false,
        uploadStage: '{{ $defaultStage }}',
        discussionStageId: {{ $submission->stage_id }},
    
        // Assign Editor Modal State
        assignEditorModalOpen: false,
        editorSearch: '',
        editorResults: [],
        selectedEditor: null,
        editorRole: 'editor',
        isSearchingEditors: false,
    
        async searchEditors() {
            if (this.editorSearch.length < 2) {
                this.editorResults = [];
                return;
            }
            this.isSearchingEditors = true;
            try {
                const res = await fetch(`{{ route('journal.workflow.reviewers.search', $journal->slug) }}?q=${encodeURIComponent(this.editorSearch)}&role=editor`);
                this.editorResults = await res.json();
            } catch (e) {
                console.error(e);
            }
            this.isSearchingEditors = false;
        },
    
        selectEditor(editor) {
            this.selectedEditor = editor;
            this.editorSearch = editor.name;
            this.editorResults = [];
        },
    
        resetEditorModal() {
            this.selectedEditor = null;
            this.editorSearch = '';
            this.editorResults = [];
            this.editorRole = 'editor';
        },
    
        // Reviewer Modal State
        reviewerModalOpen: false,
        selectedReviewer: null,
        reviewerSearch: '',
        reviewerResults: [],
        reviewMethod: 'double_blind',
        responseDueDate: '',
        reviewDueDate: '',
        isSearching: false,
    
        async searchReviewers() {
            if (this.reviewerSearch.length < 2) {
                this.reviewerResults = [];
                return;
            }
            this.isSearching = true;
            try {
                const res = await fetch(`{{ route('journal.workflow.reviewers.search', $journal->slug) }}?q=${encodeURIComponent(this.reviewerSearch)}`);
                this.reviewerResults = await res.json();
            } catch (e) {
                console.error(e);
            }
            this.isSearching = false;
        },
    
        selectReviewer(reviewer) {
            this.selectedReviewer = reviewer;
            this.reviewerSearch = reviewer.name;
            this.reviewerResults = [];
        },
    
        resetReviewerModal() {
            this.selectedReviewer = null;
            this.reviewerSearch = '';
            this.reviewerResults = [];
            this.reviewMethod = 'double_blind';
            this.responseDueDate = '';
            this.reviewDueDate = '';
        },
    
        // Discussion Wizard State
        discussionFiles: [],
        wizardStep: 1,
        tempUploadedFile: null,
    
        // CKEditor
        editorInstance: null,
        messageBody: '',
    
        initEditor() {
            if (this.editorInstance) return;
            ClassicEditor
                .create(document.querySelector('#discussion-editor'), {
                    simpleUpload: {
                        uploadUrl: '{{ route('journal.discussion.upload-image', ['journal' => $journal->slug]) }}',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }
                })
                .then(editor => {
                    this.editorInstance = editor;
                    editor.model.document.on('change:data', () => {
                        this.messageBody = editor.getData();
                    });
                })
                .catch(error => {
                    console.error(error);
                });
        },
    
        resetDiscussionForm() {
            this.discussionFiles = [];
            this.messageBody = '';
            if (this.editorInstance) {
                this.editorInstance.setData('');
            }
        },
    
        submitDiscussion() {
            if (!this.messageBody || this.messageBody.trim() === '') {
                alert('Message is required');
                return;
            }
            document.querySelector('#discussion-form').submit();
        },
    
        // Wizard Actions
        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
    
            let formData = new FormData();
            formData.append('file', file);
    
            fetch('{{ route('journal.discussion.upload-file', $journal->slug) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    this.tempUploadedFile = data;
                    this.wizardStep = 2;
                })
                .catch(err => alert('Upload failed'));
        },
    
        completeWizard() {
            if (this.tempUploadedFile) {
                this.discussionFiles.push(this.tempUploadedFile);
            }
            this.wizardStep = 1;
            this.tempUploadedFile = null;
            this.fileWizardOpen = false;
        },
    
        addAnotherFile() {
            if (this.tempUploadedFile) {
                this.discussionFiles.push(this.tempUploadedFile);
            }
            this.wizardStep = 1;
            this.tempUploadedFile = null;
        },
    
        // ============== Editorial Decision Modals ==============
        // Send to Review Modal
        sendToReviewModalOpen: false,
        availableFiles: [],
        selectedFilesForPromotion: [],
        isLoadingFiles: false,
    
        // Accept Skip Review Modal  
        skipReviewModalOpen: false,
        skipReviewNotes: '',
    
        // Decline Modal
        declineModalOpen: false,
        declineReason: '',
        notifyAuthor: true,
    
        async loadAvailableFiles() {
            this.isLoadingFiles = true;
            try {
                const res = await fetch(`{{ route('journal.workflow.available-files', ['journal' => $journal->slug, 'submission' => $submission->slug]) }}`);
                const data = await res.json();
                this.availableFiles = data.files;
                // Pre-select all files by default
                this.selectedFilesForPromotion = data.files.map(f => ({ id: f.id, type: f.type }));
            } catch (e) {
                console.error('Failed to load files:', e);
            }
            this.isLoadingFiles = false;
        },
    
        toggleFileSelection(file) {
            const index = this.selectedFilesForPromotion.findIndex(f => f.id === file.id);
            if (index > -1) {
                this.selectedFilesForPromotion.splice(index, 1);
            } else {
                this.selectedFilesForPromotion.push({ id: file.id, type: file.type });
            }
        },
    
        isFileSelected(file) {
            return this.selectedFilesForPromotion.some(f => f.id === file.id);
        },
    
        openSendToReviewModal() {
            this.sendToReviewModalOpen = true;
            this.loadAvailableFiles();
        },
    
        openSkipReviewModal() {
            this.skipReviewModalOpen = true;
            this.loadAvailableFiles();
        },
    
        resetDeclineModal() {
            this.declineReason = '';
            this.notifyAuthor = true;
        }
    }" x-init="$watch('discussionModalOpen', value => { if (value) setTimeout(() => initEditor(), 100); })">

        {{-- Header Section --}}
        <div class="mb-6">
            <nav class="text-sm text-gray-500 mb-2">
                <a href="{{ route('journal.submissions.index', $journal->slug) }}"
                    class="hover:text-indigo-600">Submissions</a>
            </nav>
            <div class="flex flex-wrap items-center gap-3 mb-2">
                <h1 class="text-3xl font-bold text-gray-900 leading-tight">{{ $submission->title }}</h1>
                @php
                    $stageColors = [
                        1 => 'bg-blue-100 text-blue-800', // Submission
                        2 => 'bg-yellow-100 text-yellow-800', // Review
                        3 => 'bg-teal-100 text-teal-800', // Copyediting
                        4 => 'bg-green-100 text-green-800', // Production
                    ];
                    $stageNames = [
                        1 => 'Submission',
                        2 => 'Review',
                        3 => 'Copyediting',
                        4 => 'Production',
                    ];
                    $currentStageColor = $stageColors[$submission->stage_id] ?? 'bg-gray-100 text-gray-800';
                    $currentStageName = $stageNames[$submission->stage_id] ?? 'Unknown';

                    // Status badge
                    $statusColors = [
                        1 => 'bg-gray-100 text-gray-700', // Submitted
                        2 => 'bg-emerald-100 text-emerald-700', // Published
                        3 => 'bg-red-100 text-red-700', // Rejected
                        4 => 'bg-orange-100 text-orange-700', // Revision Required
                        5 => 'bg-blue-100 text-blue-700', // In Review
                        6 => 'bg-green-100 text-green-700', // Accepted
                    ];
                    $statusNames = [
                        1 => 'Submitted',
                        2 => 'Published',
                        3 => 'Rejected',
                        4 => 'Revision Required',
                        5 => 'in_review',
                        6 => 'Accepted',
                    ];
                    $statusColor = $statusColors[$submission->status] ?? 'bg-gray-100 text-gray-700';
                    $statusName = $statusNames[$submission->status] ?? 'Unknown';

                    // Override stage for Declined/Published
                    if ($submission->status == 3) {
                        $currentStageColor = 'bg-red-100 text-red-800';
                        $currentStageName = 'Declined';
                    } elseif ($submission->status == 2) {
                        $currentStageColor = 'bg-indigo-100 text-indigo-800';
                        $currentStageName = 'Published';
                    }

                    $isRejected = $submission->status == 3;
                @endphp
                <div class="flex gap-2">
                    <span
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium {{ $currentStageColor }}">
                        <i class="fa-solid fa-layer-group mr-1.5 text-xs"></i>
                        {{ $currentStageName }}
                    </span>
                    <span
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium {{ $statusColor }}">
                        <i class="fa-solid fa-circle-dot mr-1.5 text-xs"></i>
                        {{ $statusName }}
                    </span>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-500">
                <span class="font-medium text-gray-900">
                    {{ $submission->authors->first()->name ?? 'Unknown Author' }}
                </span>
                <span class="mx-2">•</span>
                Submitted
                {{ $submission->submitted_at?->format('M d, Y') ?? $submission->created_at->format('M d, Y') }}
                <span class="mx-2">•</span>
                <span class="font-mono text-gray-700">{{ $submission->submission_code }}</span>
            </div>
        </div>

        {{-- WARNING BANNERS --}}
        @php
            $isUnassigned = $submission->editorialAssignments->where('is_active', true)->isEmpty();
            $isRejected = $submission->status == 3;
        @endphp

        {{-- REJECTED WARNING BANNER --}}
        @if ($isRejected)
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                <div class="flex items-center">
                    <i class="fa-solid fa-ban text-red-500 text-xl mr-3"></i>
                    <div>
                        <p class="text-red-800 font-semibold">This submission has been declined.</p>
                        <p class="text-red-600 text-sm">Workflow actions are disabled. This submission will appear in
                            the Archives.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- UNASSIGNED WARNING BANNER --}}
        @if ($isUnassigned && !$isRejected)
            @role('Editor|Section Editor|Journal Manager|Admin|Super Admin')
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fa-solid fa-exclamation-triangle text-red-500 text-xl mr-3"></i>
                            <div>
                                <p class="text-red-800 font-semibold">No editor has been assigned to this submission.</p>
                                <p class="text-red-600 text-sm">Assign an editor to enable editorial decisions.</p>
                            </div>
                        </div>
                        <button @click="assignEditorModalOpen = true; resetEditorModal()"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">
                            Assign Editor
                        </button>
                    </div>
                </div>
            @endrole
        @endif

        {{-- Main Navigation (Tabs) --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <button @click="activeTab = 'workflow'"
                    :class="activeTab === 'workflow' ? 'border-indigo-600 text-indigo-600' :
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-lg focus:outline-none">
                    Workflow
                </button>
                <button @click="activeTab = 'publication'"
                    :class="activeTab === 'publication' ? 'border-indigo-600 text-indigo-600' :
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-lg focus:outline-none">
                    Publication
                </button>
            </nav>
        </div>

        {{-- Workflow Content --}}
        <div x-show="activeTab === 'workflow'">

            {{-- Stage Navigation (Blue Bar) --}}
            <div class="bg-gray-100 p-1 rounded-t-lg border-b border-gray-200 flex space-x-1">
                @foreach (['submission' => 1, 'review' => 2, 'copyediting' => 3, 'production' => 4] as $stageName => $stageId)
                    <button
                        @click="activeStage = '{{ $stageName }}'; uploadStage = '{{ $stageName }}'; discussionStageId = {{ $stageId }}"
                        :class="activeStage === '{{ $stageName }}' ?
                            'bg-white text-indigo-600 border-t-4 border-indigo-600 shadow-sm' :
                            'text-gray-600 hover:bg-white/50'"
                        class="px-6 py-3 text-sm font-medium rounded-t-sm transition-all focus:outline-none flex-1 lg:flex-none">
                        {{ ucfirst($stageName) }}
                    </button>
                @endforeach
            </div>

            {{-- Submission Stage Content --}}
            <div x-show="activeStage === 'submission'" class="bg-gray-50/50 min-h-screen pt-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                    {{-- Main Panel Area --}}
                    <div
                        class="{{ auth()->user()->hasAnyRole(['Editor', 'Section Editor', 'Journal Manager', 'Admin', 'Super Admin'])? 'lg:col-span-3': 'lg:col-span-4' }} space-y-8">

                        {{-- Files Panel --}}
                        <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                            <div
                                class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                                <h3 class="text-base font-bold text-gray-900">Submission Files</h3>
                                @if (auth()->user()->hasAnyRole(['Editor', 'Section Editor', 'Journal Manager', 'Admin', 'Super Admin']) ||
                                        !$submission->submitted_at)
                                    <button @click="fileModalOpen = true"
                                        class="text-sm text-indigo-600 font-medium hover:text-indigo-800">
                                        + Upload File
                                    </button>
                                @endif
                            </div>
                            {{-- Responsive wrapper for file table --}}
                            <div class="overflow-x-auto">
                                <table class="min-w-full table-fixed divide-y divide-gray-200">
                                    <colgroup>
                                        <col class="w-1/2 md:w-auto">
                                        <col class="w-32">
                                        <col class="w-32 flex-shrink-0">
                                    </colgroup>
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                File</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($submission->files->where('stage', 'submission') as $file)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center">
                                                        <div
                                                            class="flex-shrink-0 h-10 w-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                                                            @php
                                                                $extension = strtolower(
                                                                    pathinfo($file->file_name, PATHINFO_EXTENSION),
                                                                );
                                                                $iconClass = match ($extension) {
                                                                    'pdf' => 'fa-file-pdf text-red-500',
                                                                    'doc', 'docx' => 'fa-file-word text-blue-500',
                                                                    'xls', 'xlsx' => 'fa-file-excel text-green-500',
                                                                    'ppt',
                                                                    'pptx'
                                                                        => 'fa-file-powerpoint text-orange-500',
                                                                    default => 'fa-file-lines text-gray-500',
                                                                };
                                                            @endphp
                                                            <i class="fa-solid {{ $iconClass }} text-lg"></i>
                                                        </div>
                                                        <div class="ml-4 min-w-0 flex-1">
                                                            <div class="text-sm font-medium text-gray-900 truncate"
                                                                title="{{ $file->file_name }}">
                                                                {{ $file->file_name }}
                                                            </div>
                                                            <div class="text-xs text-gray-500">
                                                                {{ ucfirst($file->file_type) }} •
                                                                {{ number_format($file->file_size / 1024, 0) }} KB •
                                                                v{{ $file->version ?? 1 }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $file->created_at->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex items-center justify-end gap-2">
                                                        {{-- Preview Button --}}
                                                        @php
                                                            $viewableExtensions = [
                                                                'pdf',
                                                                'doc',
                                                                'docx',
                                                                'xls',
                                                                'xlsx',
                                                                'ppt',
                                                                'pptx',
                                                                'odt',
                                                                'ods',
                                                                'odp',
                                                            ];
                                                            $isViewable = in_array($extension, $viewableExtensions);
                                                        @endphp
                                                        @if ($isViewable)
                                                            <a href="{{ route('files.preview', $file) }}"
                                                                title="Preview"
                                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </a>
                                                        @endif
                                                        {{-- Download Button --}}
                                                        <a href="{{ route('files.download', $file) }}" title="Download"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors">
                                                            <i class="fa-solid fa-download"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3"
                                                    class="px-6 py-8 text-center text-sm text-gray-500 italic">
                                                    No files uploaded to this stage.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Discussions Panel - Stage 1: Submission --}}
                        <x-discussion-panel :submission="$submission" :stageId="1" stageName="submission" :discussions="$allDiscussions"
                            :participants="$participants" :journal="$journal" />




                    </div>







                    {{-- Active Stage Placeholder for other tabs --}}
                    @role('Editor|Section Editor|Journal Manager|Admin|Super Admin')
                        <div class="lg:col-span-1 space-y-6">
                            {{-- Workflow Actions --}}
                            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Workflow Actions
                                </h4>
                                @role('Editor|Section Editor|Admin|Super Admin')
                                    @if ($isRejected)
                                        {{-- RED ALERT: Locked state for rejected submissions --}}
                                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                            <div class="flex items-start">
                                                <i class="fa-solid fa-lock text-red-500 mt-0.5 mr-2"></i>
                                                <div>
                                                    <p class="text-sm font-semibold text-red-800">Submission Declined</p>
                                                    <p class="text-xs text-red-600 mt-1">
                                                        This submission has been declined and archived. No further actions are
                                                        allowed.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif ($isUnassigned)
                                        {{-- BLUE INFO BOX: Disabled state for unassigned --}}
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                            <div class="flex items-start">
                                                <i class="fa-solid fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                                <p class="text-sm text-blue-700">
                                                    Assign an editor to enable the editorial decisions for this stage.
                                                </p>
                                            </div>
                                        </div>
                                        {{-- DISABLED STATE: Show grayed out buttons for unassigned --}}
                                        <button disabled
                                            class="w-full mb-2 px-4 py-2.5 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed text-sm font-medium">
                                            <i class="fa-solid fa-arrow-right mr-2"></i>Send to Review
                                        </button>
                                        <button disabled
                                            class="w-full px-4 py-2.5 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed text-sm font-medium">
                                            <i class="fa-solid fa-ban mr-2"></i>Decline Submission
                                        </button>
                                    @else
                                        {{-- ACTIVE STATE: Enabled workflow actions --}}
                                        <button @click="openSendToReviewModal()"
                                            class="w-full mb-2 px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition-colors">
                                            <i class="fa-solid fa-arrow-right mr-2"></i>Send to Review
                                        </button>
                                        <button @click="openSkipReviewModal()"
                                            class="w-full mb-2 px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium transition-colors">
                                            <i class="fa-solid fa-forward mr-2"></i>Accept & Skip Review
                                        </button>
                                        <button @click="declineModalOpen = true; resetDeclineModal()"
                                            class="w-full px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition-colors">
                                            <i class="fa-solid fa-ban mr-2"></i>Decline Submission
                                        </button>
                                    @endif
                                @else
                                    <p class="text-sm text-gray-500 italic">Actions available to Editors.</p>
                                @endrole
                            </div>

                            {{-- Participants (Modernized - OJS 3.3 Style) --}}
                            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Participants</h4>
                                    @role('Journal Manager|Admin|Super Admin')
                                        <button @click="assignEditorModalOpen = true; resetEditorModal()"
                                            class="text-xs font-medium px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors">
                                            <i class="fa-solid fa-plus text-xs mr-1"></i> Assign
                                        </button>
                                    @endrole
                                </div>

                                @php
                                    // Group participants by role
                                    $groupedParticipants = [
                                        'Journal Manager' => [],
                                        'Editor' => [],
                                        'Section Editor' => [],
                                        'Author' => [],
                                        'Reviewer' => [],
                                    ];

                                    // Add editorial assignments
                                    foreach (
                                        $submission->editorialAssignments->where('is_active', true)
                                        as $assignment
                                    ) {
                                        $roleName = ucfirst(str_replace('_', ' ', $assignment->role));
                                        if (!isset($groupedParticipants[$roleName])) {
                                            $groupedParticipants[$roleName] = [];
                                        }
                                        $groupedParticipants[$roleName][] = [
                                            'user' => $assignment->user,
                                            'role' => $roleName,
                                        ];
                                    }

                                    // Add submitting author
                                    if ($submission->authors->first()) {
                                        $groupedParticipants['Author'][] = [
                                            'user' => $submission->authors->first(),
                                            'role' => 'Author',
                                        ];
                                    }

                                    // Role colors and initials
                                    $roleColors = [
                                        'Journal Manager' => 'bg-purple-500',
                                        'Editor' => 'bg-blue-500',
                                        'Section Editor' => 'bg-indigo-500',
                                        'Author' => 'bg-amber-500',
                                        'Reviewer' => 'bg-emerald-500',
                                    ];
                                @endphp

                                <div class="space-y-4">
                                    @foreach ($groupedParticipants as $role => $members)
                                        @if (count($members) > 0)
                                            {{-- Role Group Header --}}
                                            <div>
                                                <h5
                                                    class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">
                                                    {{ $role }}
                                                </h5>
                                                <div class="space-y-2">
                                                    @foreach ($members as $member)
                                                        @php
                                                            $user = $member['user'];
                                                            $initials = strtoupper(substr($user->name, 0, 1));
                                                            if (str_contains($user->name, ' ')) {
                                                                $parts = explode(' ', $user->name);
                                                                $initials = strtoupper(
                                                                    substr($parts[0], 0, 1) .
                                                                        substr($parts[1] ?? '', 0, 1),
                                                                );
                                                            }
                                                            $avatarColor = $roleColors[$role] ?? 'bg-gray-500';
                                                        @endphp
                                                        {{-- User Item --}}
                                                        <div
                                                            class="flex items-center gap-3 py-2 px-3 rounded-lg hover:bg-gray-50 transition-colors group">
                                                            {{-- Avatar --}}
                                                            <div
                                                                class="w-9 h-9 rounded-full {{ $avatarColor }} flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                                                {{ $initials }}
                                                            </div>
                                                            {{-- Name --}}
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-sm font-semibold text-gray-900 truncate">
                                                                    {{ $user->name }}</p>
                                                                <p class="text-xs text-gray-500 truncate">
                                                                    {{ $user->email }}</p>
                                                            </div>
                                                            {{-- Hover Actions --}}
                                                            <div
                                                                class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                @role('Admin|Super Admin')
                                                                    <button title="Notify"
                                                                        class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors">
                                                                        <i class="fa-solid fa-envelope text-xs"></i>
                                                                    </button>
                                                                    @if ($role !== 'Author')
                                                                        <button title="Remove"
                                                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                                                            <i class="fa-solid fa-trash text-xs"></i>
                                                                        </button>
                                                                    @endif
                                                                @endrole
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                    @if (array_sum(array_map('count', $groupedParticipants)) === 0)
                                        <p class="text-sm text-gray-400 italic text-center py-4">No participants assigned
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endrole

                </div>
            </div>

            {{-- ==================== REVIEW STAGE ==================== --}}
            <div x-show="activeStage === 'review'" class="bg-gray-50/50 min-h-screen pt-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    {{-- Main Panel Area --}}
                    <div class="lg:col-span-3 space-y-6">

                        {{-- Reviewers Panel --}}
                        <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                            <div
                                class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                                <h3 class="text-base font-bold text-gray-900">
                                    <i class="fa-solid fa-user-check text-indigo-500 mr-2"></i>Reviewers
                                </h3>
                                @if (auth()->user()->hasRole(['Editor', 'Section Editor', 'Admin', 'Super Admin']))
                                    <button @click="reviewerModalOpen = true; resetReviewerModal()"
                                        class="text-sm text-indigo-600 font-medium hover:text-indigo-800">
                                        + Add Reviewer
                                    </button>
                                @endif
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Reviewer</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Assigned</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Due Date</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Status</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Recommendation</th>
                                            <th
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($submission->reviewAssignments as $assignment)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div
                                                            class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold text-sm">
                                                            {{ strtoupper(substr($assignment->reviewer->name ?? 'R', 0, 1)) }}
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $assignment->reviewer->name ?? 'Unknown' }}</div>
                                                            <div class="text-xs text-gray-500">
                                                                {{ $assignment->reviewer->email ?? '' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $assignment->assigned_at?->format('M d, Y') ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if ($assignment->due_date)
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $assignment->isOverdue() ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                                            {{ $assignment->due_date->format('M d, Y') }}
                                                            @if ($assignment->isOverdue())
                                                                <i class="fa-solid fa-exclamation-circle ml-1"></i>
                                                            @endif
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'accepted' => 'bg-blue-100 text-blue-800',
                                                            'completed' => 'bg-green-100 text-green-800',
                                                            'declined' => 'bg-red-100 text-red-800',
                                                            'cancelled' => 'bg-gray-100 text-gray-600',
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$assignment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                        {{ $assignment->status_label }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    @if ($assignment->recommendation)
                                                        <span
                                                            class="font-medium text-{{ $assignment->recommendation_color }}-600">{{ $assignment->recommendation_label }}</span>
                                                    @else
                                                        <span class="text-gray-400 italic">Awaiting</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <form
                                                        action="{{ route('journal.workflow.unassign-reviewer', ['journal' => $journal->slug, 'submission' => $submission->slug, 'assignment' => $assignment->id]) }}"
                                                        method="POST" class="inline"
                                                        onsubmit="return confirm('Remove this reviewer?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="text-red-600 hover:text-red-900 text-xs">Unassign</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6"
                                                    class="px-6 py-8 text-center text-sm text-gray-500 italic">
                                                    No reviewers assigned yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Review Files Panel --}}
                        <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                            <div
                                class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                                <h3 class="text-base font-bold text-gray-900">
                                    <i class="fa-solid fa-file-lines text-indigo-500 mr-2"></i>Review Files
                                </h3>
                            </div>
                            <div class="p-6">
                                @forelse($submission->files->where('stage', 'review') as $file)
                                    <div
                                        class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 rounded-lg px-2 -mx-2 transition-colors">
                                        <div class="flex items-center">
                                            @php
                                                $extension = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                                                $iconClass = match ($extension) {
                                                    'pdf' => 'fa-file-pdf text-red-500',
                                                    'doc', 'docx' => 'fa-file-word text-blue-500',
                                                    'xls', 'xlsx' => 'fa-file-excel text-green-500',
                                                    'ppt', 'pptx' => 'fa-file-powerpoint text-orange-500',
                                                    default => 'fa-file-lines text-gray-500',
                                                };
                                                $viewableExtensions = [
                                                    'pdf',
                                                    'doc',
                                                    'docx',
                                                    'xls',
                                                    'xlsx',
                                                    'ppt',
                                                    'pptx',
                                                    'odt',
                                                ];
                                                $isViewable = in_array($extension, $viewableExtensions);
                                            @endphp
                                            <div
                                                class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center mr-3">
                                                <i class="fa-solid {{ $iconClass }}"></i>
                                            </div>
                                            <div>
                                                <span
                                                    class="text-sm font-medium text-gray-700">{{ $file->file_name }}</span>
                                                <p class="text-xs text-gray-500">
                                                    {{ number_format($file->file_size / 1024, 0) }} KB</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            @if ($isViewable)
                                                <a href="{{ route('files.preview', $file) }}" title="Preview"
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded text-gray-400 hover:text-indigo-600 hover:bg-indigo-50">
                                                    <i class="fa-solid fa-eye text-sm"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('files.download', $file) }}" title="Download"
                                                class="inline-flex items-center justify-center w-7 h-7 rounded text-gray-400 hover:text-emerald-600 hover:bg-emerald-50">
                                                <i class="fa-solid fa-download text-sm"></i>
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 italic text-center py-4">No review files uploaded.
                                    </p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Review Discussions - Stage 2 --}}
                        <x-discussion-panel :submission="$submission" :stageId="2" stageName="review" :discussions="$allDiscussions"
                            :participants="$participants" :journal="$journal" />
                    </div>

                    {{-- Sidebar --}}
                    <div class="lg:col-span-1 space-y-6">
                        {{-- Editor Decision Panel --}}
                        @if (auth()->user()->hasRole(['Editor', 'Section Editor', 'Admin', 'Super Admin']))
                            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Editor
                                    Decision</h4>

                                @if ($submission->stage_id == 2 && $submission->status != 3)
                                    {{-- Active - Stage 2 and not declined --}}
                                    <div class="space-y-3">
                                        <form
                                            action="{{ route('journal.workflow.record-decision', ['journal' => $journal->slug, 'submission' => $submission->slug]) }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="decision" value="accept">
                                            <button type="submit"
                                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                                <i class="fa-solid fa-check mr-2"></i> Accept Submission
                                            </button>
                                        </form>
                                        <form
                                            action="{{ route('journal.workflow.record-decision', ['journal' => $journal->slug, 'submission' => $submission->slug]) }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="decision" value="request_revisions">
                                            <button type="submit"
                                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-yellow-300 shadow-sm text-sm font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100">
                                                <i class="fa-solid fa-pen mr-2"></i> Request Revisions
                                            </button>
                                        </form>
                                        <form
                                            action="{{ route('journal.workflow.record-decision', ['journal' => $journal->slug, 'submission' => $submission->slug]) }}"
                                            method="POST"
                                            onsubmit="return confirm('This will decline the submission. Continue?')">
                                            @csrf
                                            <input type="hidden" name="decision" value="decline">
                                            <button type="submit"
                                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                                <i class="fa-solid fa-xmark mr-2"></i> Decline
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    {{-- Disabled - Stage has passed or submission declined --}}
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4">
                                        <p class="text-sm text-gray-600 flex items-center">
                                            <i class="fa-solid fa-check-circle text-gray-400 mr-2"></i>
                                            @if ($submission->status == 3)
                                                Submission has been declined.
                                            @elseif($submission->stage_id > 2)
                                                Review stage complete. Moved to
                                                <strong
                                                    class="ml-1">{{ ucfirst($stageNames[$submission->stage_id] ?? 'next stage') }}</strong>.
                                            @else
                                                Awaiting review stage.
                                            @endif
                                        </p>
                                    </div>
                                    <button disabled
                                        class="w-full mb-2 px-4 py-2 bg-gray-100 text-gray-400 rounded-md cursor-not-allowed text-sm font-medium">
                                        <i class="fa-solid fa-check mr-2"></i> Accept Submission
                                    </button>
                                    <button disabled
                                        class="w-full mb-2 px-4 py-2 bg-gray-100 text-gray-400 rounded-md cursor-not-allowed text-sm font-medium">
                                        <i class="fa-solid fa-pen mr-2"></i> Request Revisions
                                    </button>
                                    <button disabled
                                        class="w-full px-4 py-2 bg-gray-100 text-gray-400 rounded-md cursor-not-allowed text-sm font-medium">
                                        <i class="fa-solid fa-xmark mr-2"></i> Decline
                                    </button>
                                @endif
                            </div>
                        @endif

                        {{-- Review Round Info --}}
                        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Review Round</h4>
                            @php $currentRound = $submission->currentReviewRound(); @endphp
                            @if ($currentRound)
                                <div class="text-center">
                                    <span class="text-3xl font-bold text-indigo-600">{{ $currentRound->round }}</span>
                                    <p class="text-sm text-gray-500 mt-1">{{ $currentRound->status_label }}</p>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic text-center">No review round started.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================== COPYEDITING STAGE ==================== --}}
            <div x-show="activeStage === 'copyediting'" class="bg-gray-50/50 min-h-screen pt-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    {{-- Main Panel --}}
                    <div class="lg:col-span-3 space-y-6">
                        {{-- Copyedited Files --}}
                        <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                            <div
                                class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                                <h3 class="text-base font-bold text-gray-900">
                                    <i class="fa-solid fa-file-pen text-teal-500 mr-2"></i>Copyedited Files
                                </h3>
                                <button @click="fileModalOpen = true; uploadStage = 'copyediting'"
                                    class="text-sm text-indigo-600 font-medium hover:text-indigo-800">
                                    + Upload File
                                </button>
                            </div>
                            <div class="p-6">
                                @forelse($submission->files->where('stage', 'copyediting') as $file)
                                    <div
                                        class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 rounded-lg px-2 -mx-2 transition-colors">
                                        <div class="flex items-center">
                                            @php
                                                $extension = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                                                $iconClass = match ($extension) {
                                                    'pdf' => 'fa-file-pdf text-red-500',
                                                    'doc', 'docx' => 'fa-file-word text-blue-500',
                                                    'xls', 'xlsx' => 'fa-file-excel text-green-500',
                                                    'ppt', 'pptx' => 'fa-file-powerpoint text-orange-500',
                                                    default => 'fa-file-lines text-gray-500',
                                                };
                                                $viewableExtensions = [
                                                    'pdf',
                                                    'doc',
                                                    'docx',
                                                    'xls',
                                                    'xlsx',
                                                    'ppt',
                                                    'pptx',
                                                    'odt',
                                                ];
                                                $isViewable = in_array($extension, $viewableExtensions);
                                            @endphp
                                            <div
                                                class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center mr-3">
                                                <i class="fa-solid {{ $iconClass }}"></i>
                                            </div>
                                            <div>
                                                <span
                                                    class="text-sm font-medium text-gray-700">{{ $file->file_name }}</span>
                                                <p class="text-xs text-gray-500">
                                                    {{ $file->created_at->format('M d, Y') }} •
                                                    {{ number_format($file->file_size / 1024, 0) }} KB</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            @if ($isViewable)
                                                <a href="{{ route('files.preview', $file) }}" title="Preview"
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded text-gray-400 hover:text-indigo-600 hover:bg-indigo-50">
                                                    <i class="fa-solid fa-eye text-sm"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('files.download', $file) }}" title="Download"
                                                class="inline-flex items-center justify-center w-7 h-7 rounded text-gray-400 hover:text-emerald-600 hover:bg-emerald-50">
                                                <i class="fa-solid fa-download text-sm"></i>
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 italic text-center py-4">No copyedited files yet.
                                    </p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Copyediting Discussions - Stage 3 --}}
                        <x-discussion-panel :submission="$submission" :stageId="3" stageName="copyediting"
                            :discussions="$allDiscussions" :participants="$participants" :journal="$journal" />
                    </div>

                    {{-- Sidebar --}}
                    <div class="lg:col-span-1 space-y-6">
                        @if (auth()->user()->hasRole(['Editor', 'Section Editor', 'Admin', 'Super Admin']))
                            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Actions</h4>
                                @if ($submission->stage_id == 3 && $submission->status != 3)
                                    <form
                                        action="{{ route('journal.workflow.send-production', ['journal' => $journal->slug, 'submission' => $submission->slug]) }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700">
                                            <i class="fa-solid fa-arrow-right mr-2"></i> Send to Production
                                        </button>
                                    </form>
                                @else
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4">
                                        <p class="text-sm text-gray-600 flex items-center">
                                            <i class="fa-solid fa-check-circle text-gray-400 mr-2"></i>
                                            @if ($submission->status == 3)
                                                Submission has been declined.
                                            @elseif($submission->stage_id > 3)
                                                Copyediting complete. Moved to Production.
                                            @else
                                                Awaiting copyediting stage.
                                            @endif
                                        </p>
                                    </div>
                                    <button disabled
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-100 text-gray-400 rounded-md cursor-not-allowed text-sm font-medium">
                                        <i class="fa-solid fa-arrow-right mr-2"></i> Send to Production
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @php
                $issueOptions = $issues->map(function ($i) {
                    $title = $i->title ? " - {$i->title}" : '';
                    $status = $i->is_published ? ' [Published]' : ' [Future]';
                    return [
                        'id' => $i->id,
                        'label' => "Vol {$i->volume}, No {$i->number} ({$i->year}){$title}{$status}",
                        'is_published' => $i->is_published,
                    ];
                });
            @endphp
            {{-- ==================== PRODUCTION STAGE ==================== --}}
            <div x-show="activeStage === 'production'" class="bg-gray-50/50 min-h-screen pt-6"
                x-data="{
                    galleyModalOpen: false,
                    scheduleModalOpen: false,
                    editGalleyModalOpen: false,
                    editingGalley: null,
                    issues: {{ json_encode($issueOptions) }},
                    selectedIssueId: '{{ $submission->issue_id ?? '' }}',
                    isLoadingIssues: false,
                
                    openScheduleModal() {
                        this.scheduleModalOpen = true;
                    },
                
                    openEditGalley(galley) {
                        this.editingGalley = galley;
                        this.editGalleyModalOpen = true;
                    }
                }">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                    {{-- Main Panel Area --}}
                    <div class="lg:col-span-3 space-y-6">

                        {{-- ====== GALLEYS PANEL ====== --}}
                        <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                            <div
                                class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                                <div>
                                    <h3 class="text-base font-bold text-gray-900">
                                        <i class="fa-solid fa-file-lines text-green-500 mr-2"></i>Publication Galleys
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">Final files that will be available to
                                        readers.</p>
                                </div>
                                @role('Editor|Section Editor|Admin|Super Admin')
                                    <button @click="galleyModalOpen = true"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 shadow-sm transition-colors">
                                        <i class="fa-solid fa-plus mr-1.5"></i> Add Galley
                                    </button>
                                @endrole
                            </div>

                            {{-- Galleys Table --}}
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Format</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                File</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Language</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Uploaded</th>
                                            <th
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($submission->galleys as $galley)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold 
                                                        {{ strtolower($galley->label) === 'pdf'
                                                            ? 'bg-red-100 text-red-700'
                                                            : (strtolower($galley->label) === 'html'
                                                                ? 'bg-orange-100 text-orange-700'
                                                                : (strtolower($galley->label) === 'epub'
                                                                    ? 'bg-purple-100 text-purple-700'
                                                                    : 'bg-gray-100 text-gray-700')) }}">
                                                        <i class="fa-solid {{ $galley->label_icon }} mr-1.5"></i>
                                                        {{ $galley->label }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center">
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900">
                                                                {{ $galley->file->file_name ?? 'Remote File' }}
                                                            </p>
                                                            @if ($galley->file)
                                                                <p class="text-xs text-gray-500">
                                                                    {{ number_format($galley->file->file_size / 1024, 0) }}
                                                                    KB
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="text-sm text-gray-600">{{ $galley->locale_name }}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $galley->created_at->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                                    <div class="flex items-center justify-end gap-1">
                                                        @if ($galley->download_url)
                                                            <a href="{{ $galley->download_url }}" target="_blank"
                                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                                                                title="Download">
                                                                <i class="fa-solid fa-download"></i>
                                                            </a>
                                                        @endif
                                                        @role('Editor|Section Editor|Admin|Super Admin')
                                                            <form
                                                                action="{{ route('journal.workflow.galley.destroy', ['journal' => $journal->slug, 'submission' => $submission->id, 'galley' => $galley->id]) }}"
                                                                method="POST" class="inline"
                                                                onsubmit="return confirm('Are you sure you want to delete this galley?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                                                    title="Delete">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endrole
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-12 text-center">
                                                    <div class="flex flex-col items-center">
                                                        <div
                                                            class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                                            <i
                                                                class="fa-solid fa-file-circle-plus text-gray-400 text-xl"></i>
                                                        </div>
                                                        <p class="text-sm font-medium text-gray-900">No galleys
                                                            uploaded yet</p>
                                                        <p class="text-xs text-gray-500 mt-1">Upload PDF, HTML, or EPUB
                                                            files for readers to download.</p>
                                                        @role('Editor|Section Editor|Admin|Super Admin')
                                                            <button @click="galleyModalOpen = true"
                                                                class="mt-3 text-sm text-green-600 font-medium hover:text-green-700">
                                                                <i class="fa-solid fa-plus mr-1"></i> Add your first galley
                                                            </button>
                                                        @endrole
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Production Discussions - Stage 4 --}}
                        <x-discussion-panel :submission="$submission" :stageId="4" stageName="production"
                            :discussions="$allDiscussions" :participants="$participants" :journal="$journal" />

                    </div>

                    {{-- ====== SIDEBAR ====== --}}
                    <div class="lg:col-span-1 space-y-6">

                        {{-- Publication Status Card --}}
                        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Publication</h4>

                            @if ($submission->status === 'published')
                                {{-- Published State --}}
                                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-4">
                                    <div class="flex items-center">
                                        <i class="fa-solid fa-check-circle text-emerald-500 text-xl mr-3"></i>
                                        <div>
                                            <p class="text-sm font-semibold text-emerald-800">Published</p>
                                            <p class="text-xs text-emerald-600">
                                                {{ $submission->published_at?->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                                @if ($submission->issue)
                                    <p class="text-sm text-gray-600 mb-4">
                                        <i class="fa-solid fa-book text-gray-400 mr-2"></i>
                                        {{ $submission->issue->identifier }}
                                    </p>
                                @endif
                                @role('Editor|Section Editor|Admin|Super Admin')
                                    <form
                                        action="{{ route('journal.workflow.unpublish', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit"
                                            onclick="return confirm('Are you sure you want to unpublish this submission?')"
                                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-200 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 transition-colors">
                                            <i class="fa-solid fa-eye-slash mr-2"></i> Unpublish
                                        </button>
                                    </form>
                                @endrole
                            @elseif($submission->issue_id)
                                {{-- Scheduled State --}}
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                    <div class="flex items-center">
                                        <i class="fa-solid fa-calendar-check text-blue-500 text-xl mr-3"></i>
                                        <div>
                                            <p class="text-sm font-semibold text-blue-800">Scheduled</p>
                                            <p class="text-xs text-blue-600">{{ $submission->issue->identifier }}</p>
                                        </div>
                                    </div>
                                </div>

                                @role('Editor|Section Editor|Admin|Super Admin')
                                    @if (!$submission->hasGalleys())
                                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                                            <p class="text-xs text-amber-700">
                                                <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                                                Upload at least one galley to publish.
                                            </p>
                                        </div>
                                    @endif

                                    <div class="space-y-2">
                                        <form
                                            action="{{ route('journal.workflow.publish', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                                            method="POST">
                                            @csrf
                                            <button type="submit" {{ !$submission->hasGalleys() ? 'disabled' : '' }}
                                                class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white {{ $submission->hasGalleys() ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-gray-300 cursor-not-allowed' }} transition-colors">
                                                <i class="fa-solid fa-rocket mr-2"></i> Publish Now
                                            </button>
                                        </form>

                                        <form
                                            action="{{ route('journal.workflow.unschedule', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                                            method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-200 text-sm font-medium rounded-lg text-gray-600 bg-white hover:bg-gray-50 transition-colors">
                                                <i class="fa-solid fa-calendar-xmark mr-2"></i> Unschedule
                                            </button>
                                        </form>
                                    </div>
                                @endrole
                            @else
                                {{-- Not Scheduled State --}}
                                <p class="text-sm text-gray-500 mb-4">
                                    This submission is not scheduled for publication yet.
                                </p>

                                @role('Editor|Section Editor|Admin|Super Admin')
                                    <button @click="openScheduleModal()"
                                        class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors">
                                        <i class="fa-solid fa-calendar-plus mr-2"></i> Schedule for Publication
                                    </button>
                                @endrole
                            @endif
                        </div>

                        {{-- Participants (Modern Team Card) --}}
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                            {{-- Header --}}
                            <div
                                class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                                <h4 class="text-sm font-bold text-gray-900">Participants</h4>
                                @role('Editor|Section Editor|Journal Manager|Admin|Super Admin')
                                    <button @click="assignEditorModalOpen = true; resetEditorModal()"
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition-colors">
                                        <i class="fa-solid fa-user-plus mr-1.5"></i> Assign
                                    </button>
                                @endrole
                            </div>

                            {{-- Participants List --}}
                            <div class="divide-y divide-gray-100">
                                {{-- Assigned Editors --}}
                                @forelse($submission->editorialAssignments->where('is_active', true) as $assignment)
                                    <div class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors group">
                                        {{-- Avatar --}}
                                        @if ($assignment->user->profile_photo_path ?? false)
                                            <img src="{{ asset('storage/' . $assignment->user->profile_photo_path) }}"
                                                class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow-sm"
                                                alt="{{ $assignment->user->name }}">
                                        @else
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                                {{ strtoupper(substr($assignment->user->name, 0, 1)) }}
                                            </div>
                                        @endif

                                        {{-- Info --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 line-clamp-1">
                                                {{ $assignment->user->name }}</p>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 w-fit mt-0.5">
                                                {{ ucfirst(str_replace('_', ' ', $assignment->role)) }}
                                            </span>
                                        </div>

                                        {{-- Actions --}}
                                        <div
                                            class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button type="button"
                                                @click="discussionModalOpen = true; discussionStageId = 4; resetDiscussionForm()"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                                title="Start Discussion">
                                                <i class="fa-solid fa-envelope text-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center">
                                        <div
                                            class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                            <i class="fa-solid fa-user-slash text-gray-400"></i>
                                        </div>
                                        <p class="text-sm text-gray-500 italic">No editors assigned</p>
                                        @role('Editor|Section Editor|Journal Manager|Admin|Super Admin')
                                            <button @click="assignEditorModalOpen = true; resetEditorModal()"
                                                class="mt-2 text-xs text-indigo-600 font-medium hover:text-indigo-800">
                                                + Assign an editor
                                            </button>
                                        @endrole
                                    </div>
                                @endforelse

                                {{-- Author (Separator) --}}
                                @if ($submission->authors->first())
                                    <div
                                        class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors group bg-amber-50/30">
                                        {{-- Avatar --}}
                                        <div
                                            class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                            {{ strtoupper(substr($submission->authors->first()->name, 0, 1)) }}
                                        </div>

                                        {{-- Info --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 line-clamp-1">
                                                {{ $submission->authors->first()->name }}</p>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 w-fit mt-0.5">
                                                Author
                                            </span>
                                        </div>

                                        {{-- Actions --}}
                                        <div
                                            class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button type="button"
                                                @click="discussionModalOpen = true; discussionStageId = 4; resetDiscussionForm()"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                                title="Start Discussion">
                                                <i class="fa-solid fa-envelope text-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ====== ADD GALLEY MODAL ====== --}}
                <div x-show="galleyModalOpen" x-cloak class="fixed z-50 inset-0 overflow-y-auto" role="dialog"
                    aria-modal="true">
                    <div
                        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="galleyModalOpen" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75 transition-opacity"
                            @click="galleyModalOpen = false"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                        <div x-show="galleyModalOpen" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            class="inline-block align-bottom bg-white rounded-xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fa-solid fa-file-arrow-up text-green-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                    <h3 class="text-lg leading-6 font-semibold text-gray-900">Add Publication Galley
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">Upload a final file for readers to download.
                                    </p>
                                </div>
                            </div>

                            <form
                                action="{{ route('journal.workflow.galley.store', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                                method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                                @csrf

                                <div>
                                    <label for="galley-label" class="block text-sm font-medium text-gray-700">
                                        Galley Label <span class="text-red-500">*</span>
                                    </label>
                                    <select name="label" id="galley-label" required
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                        <option value="PDF">PDF</option>
                                        <option value="HTML">HTML</option>
                                        <option value="EPUB">EPUB</option>
                                        <option value="XML">XML (JATS)</option>
                                        <option value="MP3">MP3 (Audio)</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Choose the format type for this file.</p>
                                </div>

                                <div>
                                    <label for="galley-locale"
                                        class="block text-sm font-medium text-gray-700">Language</label>
                                    <select name="locale" id="galley-locale"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                        <option value="en">English</option>
                                        <option value="id">Indonesian</option>
                                        <option value="ar">Arabic</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="galley-file" class="block text-sm font-medium text-gray-700">
                                        File <span class="text-red-500">*</span>
                                    </label>
                                    <input type="file" name="file" id="galley-file" required
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                                    <p class="mt-1 text-xs text-gray-500">Maximum file size: 50MB</p>
                                </div>

                                <div class="mt-5 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                    <button type="submit"
                                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:col-start-2 sm:text-sm">
                                        <i class="fa-solid fa-upload mr-2"></i> Upload Galley
                                    </button>
                                    <button type="button" @click="galleyModalOpen = false"
                                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ====== SCHEDULE FOR PUBLICATION MODAL ====== --}}
                <div x-show="scheduleModalOpen" x-cloak class="fixed z-50 inset-0 overflow-y-auto" role="dialog"
                    aria-modal="true">
                    <div
                        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="scheduleModalOpen" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75 transition-opacity"
                            @click="scheduleModalOpen = false"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                        <div x-show="scheduleModalOpen" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            class="inline-block align-bottom bg-white rounded-xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fa-solid fa-calendar-plus text-indigo-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                    <h3 class="text-lg leading-6 font-semibold text-gray-900">Schedule for Publication
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">Assign this submission to an issue.</p>
                                </div>
                            </div>

                            <form
                                action="{{ route('journal.workflow.assign-issue', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                                method="POST" class="mt-5 space-y-4">
                                @csrf

                                <div>
                                    <label for="issue-select" class="block text-sm font-medium text-gray-700">
                                        Select Issue <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1 relative">
                                        <template x-if="isLoadingIssues">
                                            <div class="flex items-center justify-center py-4">
                                                <i class="fa-solid fa-spinner fa-spin text-gray-400 mr-2"></i>
                                                <span class="text-sm text-gray-500">Loading issues...</span>
                                            </div>
                                        </template>
                                        <template x-if="!isLoadingIssues && issues.length === 0">
                                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                                <p class="text-sm text-amber-700">
                                                    <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                                                    No issues found. Please create an issue first.
                                                </p>
                                            </div>
                                        </template>
                                        <template x-if="!isLoadingIssues && issues.length > 0">
                                            <select name="issue_id" id="issue-select" required
                                                x-model="selectedIssueId"
                                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">-- Select an Issue --</option>
                                                <template x-for="issue in issues" :key="issue.id">
                                                    <option :value="issue.id" x-text="issue.label"></option>
                                                </template>
                                            </select>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" id="permissions-confirmed"
                                            name="permissions_confirmed"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="permissions-confirmed" class="font-medium text-gray-700">Copyright
                                            Confirmed</label>
                                        <p class="text-gray-500">I confirm that all copyright and permissions are in
                                            order.</p>
                                    </div>
                                </div>

                                <div class="mt-5 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                    <button type="submit" :disabled="!selectedIssueId"
                                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed sm:col-start-2 sm:text-sm">
                                        <i class="fa-solid fa-calendar-check mr-2"></i> Save Schedule
                                    </button>
                                    <button type="button" @click="scheduleModalOpen = false"
                                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- ==================== PUBLICATION TAB ==================== --}}
        @php
            $publication = $submission->currentPublication() ?? $submission->getOrCreatePublication();
            $pubStatus = $publication->status ?? 1;
            $pubAuthors =
                $publication->authors && $publication->authors->isNotEmpty()
                    ? $publication->authors
                    : $submission->authors;
        @endphp
        <div x-show="activeTab === 'publication'" x-cloak x-data="{
            pubTab: 'title',
            contributorModalOpen: false,
            editingContributor: null,
            issues: {{ json_encode($issueOptions) }},
            sections: [],
            isLoadingIssues: false,
            isLoadingSections: false,
        
            async loadSections() {
                this.isLoadingSections = true;
                try {
                    const res = await fetch('{{ route('journal.workflow.sections.list', $journal->slug) }}');
                    this.sections = await res.json();
                } catch (e) { console.error(e); }
                this.isLoadingSections = false;
            },
        
            openContributorModal(contributor = null) {
                this.editingContributor = contributor;
                this.contributorModalOpen = true;
            },
        
            init() {
                this.loadSections();
            },
        
            // Reordering Logic
            reorderModalOpen: false,
            reorderList: [],
            allAuthors: {{ json_encode($pubAuthors) }},
            isSavingOrder: false,
        
            openReorderModal() {
                // Clone authors to local list for manipulation
                this.reorderList = JSON.parse(JSON.stringify(this.allAuthors));
                this.reorderModalOpen = true;
            },
        
            moveUp(index) {
                if (index > 0) {
                    const temp = this.reorderList[index];
                    this.reorderList[index] = this.reorderList[index - 1];
                    this.reorderList[index - 1] = temp;
                }
            },
        
            moveDown(index) {
                if (index < this.reorderList.length - 1) {
                    const temp = this.reorderList[index];
                    this.reorderList[index] = this.reorderList[index + 1];
                    this.reorderList[index + 1] = temp;
                }
            },
        
            async saveOrder() {
                this.isSavingOrder = true;
                const order = this.reorderList.map(a => a.id);
                try {
                    const response = await fetch('{{ route('journal.workflow.publication.contributors.reorder', ['journal' => $journal->slug, 'submission' => $submission->slug]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order })
                    });
        
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        alert('Failed to save order. Please try again.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('An error occurred.');
                }
                this.isSavingOrder = false;
            }
        }">

            {{-- Status Bar Header --}}
            <div class="bg-white border-b border-gray-200 -mx-6 -mt-6 px-6 py-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-900">Publication</h2>
                        @php
                            $statusColors = [
                                1 => 'bg-gray-100 text-gray-700',
                                2 => 'bg-blue-100 text-blue-700',
                                3 => 'bg-emerald-100 text-emerald-700',
                                4 => 'bg-orange-100 text-orange-700',
                            ];
                            $statusLabels = [
                                1 => 'Unscheduled',
                                2 => 'Scheduled',
                                3 => 'Published',
                                4 => 'Unpublished',
                            ];
                        @endphp
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$pubStatus] ?? 'bg-gray-100 text-gray-700' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $pubStatus == 3 ? 'bg-emerald-500' : ($pubStatus == 2 ? 'bg-blue-500' : 'bg-gray-400') }}"></span>
                            {{ $statusLabels[$pubStatus] ?? 'Unknown' }}
                        </span>
                        @if ($publication && $publication->issue)
                            <span class="text-sm text-gray-500">
                                <i class="fa-solid fa-book text-gray-400 mr-1"></i>
                                {{ $publication->issue->identifier }}
                            </span>
                        @endif
                    </div>

                    {{-- Main Action Button --}}
                    @role('Editor|Section Editor|Admin|Super Admin')
                        <div class="flex items-center gap-2">
                            @if ($pubStatus == 3)
                                <form
                                    action="{{ route('journal.workflow.publication.unpublish', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                                    method="POST">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Are you sure?')"
                                        class="inline-flex items-center px-4 py-2 border border-orange-200 text-sm font-medium rounded-lg text-orange-700 bg-white hover:bg-orange-50">
                                        <i class="fa-solid fa-eye-slash mr-2"></i> Unpublish
                                    </button>
                                </form>
                            @elseif($pubStatus == 2)
                                <form
                                    action="{{ route('journal.workflow.publication.publish', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                                    method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm">
                                        <i class="fa-solid fa-rocket mr-2"></i> Publish
                                    </button>
                                </form>
                            @else
                                <button @click="pubTab = 'issue'" :disabled="!issues.length"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fa-solid fa-calendar-plus mr-2"></i> Schedule for Publication
                                </button>
                            @endif
                        </div>
                    @endrole
                </div>
            </div>

            {{-- Split Layout: Sidebar + Content --}}
            <div class="flex gap-8">

                {{-- Left Vertical Sidebar Navigation --}}
                <nav class="w-56 flex-shrink-0">
                    <div class="sticky top-24 space-y-1">
                        <button @click="pubTab = 'title'"
                            :class="pubTab === 'title' ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600' :
                                'text-gray-600 hover:bg-gray-50 border-l-4 border-transparent'"
                            class="w-full text-left px-4 py-2.5 text-sm font-medium rounded-r-lg transition-colors">
                            <i class="fa-solid fa-heading w-5 mr-2 text-center"></i> Title & Abstract
                        </button>
                        <button @click="pubTab = 'contributors'"
                            :class="pubTab === 'contributors' ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600' :
                                'text-gray-600 hover:bg-gray-50 border-l-4 border-transparent'"
                            class="w-full text-left px-4 py-2.5 text-sm font-medium rounded-r-lg transition-colors">
                            <i class="fa-solid fa-users w-5 mr-2 text-center"></i> Contributors
                            <span
                                class="ml-auto text-xs bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded">{{ $pubAuthors->count() }}</span>
                        </button>
                        <button @click="pubTab = 'metadata'"
                            :class="pubTab === 'metadata' ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600' :
                                'text-gray-600 hover:bg-gray-50 border-l-4 border-transparent'"
                            class="w-full text-left px-4 py-2.5 text-sm font-medium rounded-r-lg transition-colors">
                            <i class="fa-solid fa-tags w-5 mr-2 text-center"></i> Metadata
                        </button>
                        <button @click="pubTab = 'references'"
                            :class="pubTab === 'references' ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600' :
                                'text-gray-600 hover:bg-gray-50 border-l-4 border-transparent'"
                            class="w-full text-left px-4 py-2.5 text-sm font-medium rounded-r-lg transition-colors">
                            <i class="fa-solid fa-quote-left w-5 mr-2 text-center"></i> References
                        </button>
                        <button @click="pubTab = 'issue'"
                            :class="pubTab === 'issue' ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600' :
                                'text-gray-600 hover:bg-gray-50 border-l-4 border-transparent'"
                            class="w-full text-left px-4 py-2.5 text-sm font-medium rounded-r-lg transition-colors">
                            <i class="fa-solid fa-book-open w-5 mr-2 text-center"></i> Issue
                        </button>
                        <button @click="pubTab = 'license'"
                            :class="pubTab === 'license' ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600' :
                                'text-gray-600 hover:bg-gray-50 border-l-4 border-transparent'"
                            class="w-full text-left px-4 py-2.5 text-sm font-medium rounded-r-lg transition-colors">
                            <i class="fa-solid fa-scale-balanced w-5 mr-2 text-center"></i> License & DOI
                        </button>
                    </div>
                </nav>

                {{-- Right Content Area --}}
                <div class="flex-1 min-w-0">

                    {{-- ====== TITLE & ABSTRACT ====== --}}
                    <div x-show="pubTab === 'title'"
                        class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-base font-bold text-gray-900">Title & Abstract</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Edit the publication title and abstract.</p>
                        </div>
                        <form
                            action="{{ route('journal.workflow.publication.title.update', ['journal' => $journal->slug, 'submission' => $submission->slug]) }}"
                            method="POST" class="p-6 space-y-5">
                            @csrf

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="title"
                                    value="{{ old('title', $publication->title ?? $submission->title) }}" required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-lg font-medium">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                                <input type="text" name="subtitle"
                                    value="{{ old('subtitle', $publication->subtitle ?? $submission->subtitle) }}"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Abstract</label>
                                <textarea name="abstract" id="publicationAbstract" rows="8"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('abstract', $publication->abstract ?? $submission->abstract) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">HTML formatting is allowed.</p>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-gray-100">
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                                    <i class="fa-solid fa-save mr-2"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- ====== CONTRIBUTORS ====== --}}
                    <div x-show="pubTab === 'contributors'"
                        class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Contributors</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Manage authors and contributors for this
                                    publication.</p>
                            </div>
                            @role('Editor|Section Editor|Admin|Super Admin')
                                <button @click="openContributorModal()"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                                    <i class="fa-solid fa-plus mr-1.5"></i> Add Contributor
                                </button>
                                <button @click="openReorderModal()" :disabled="allAuthors.length < 2"
                                    class="ml-2 inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fa-solid fa-arrow-down-short-wide mr-1.5"></i> Order
                                </button>
                            @endrole
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Affiliation</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                            Primary</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                            In Browse</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($pubAuthors as $index => $author)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $index + 1 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold mr-3">
                                                        {{ strtoupper(substr($author->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">
                                                            {{ $author->name }}</p>
                                                        @if ($author->orcid)
                                                            <a href="{{ $author->orcid_url }}" target="_blank"
                                                                class="text-xs text-green-600 hover:underline">
                                                                <i class="fa-brands fa-orcid mr-0.5"></i> ORCID
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $author->email }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                                {{ $author->affiliation ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if ($author->is_corresponding)
                                                    <i class="fa-solid fa-check-circle text-emerald-500"></i>
                                                @else
                                                    <span class="text-gray-300">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if ($author->include_in_browse ?? true)
                                                    <i class="fa-solid fa-check-circle text-emerald-500"></i>
                                                @else
                                                    <span class="text-gray-300">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                @role('Editor|Section Editor|Admin|Super Admin')
                                                    <div class="flex items-center justify-end gap-1">
                                                        <button type="button"
                                                            @click="openContributorModal({
                                                                id: '{{ $author->id }}',
                                                                given_name: '{{ $author->given_name ?? '' }}',
                                                                family_name: '{{ $author->family_name ?? '' }}',
                                                                email: '{{ $author->email }}',
                                                                affiliation: '{{ $author->affiliation ?? '' }}',
                                                                country: '{{ $author->country ?? '' }}',
                                                                orcid: '{{ $author->orcid ?? '' }}',
                                                                is_corresponding: {{ $author->is_corresponding ? 'true' : 'false' }},
                                                                include_in_browse: {{ $author->include_in_browse ?? true ? 'true' : 'false' }}
                                                            })"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>
                                                        <form
                                                            action="{{ route('journal.workflow.publication.contributor.destroy', ['journal' => $journal->slug, 'submission' => $submission->id, 'author' => $author->id]) }}"
                                                            method="POST" class="inline"
                                                            onsubmit="return confirm('Remove this contributor?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endrole
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center">
                                                    <div
                                                        class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                                        <i class="fa-solid fa-user-plus text-gray-400 text-xl"></i>
                                                    </div>
                                                    <p class="text-sm font-medium text-gray-900">No contributors yet
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-1">Add authors and contributors
                                                        to this publication.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ====== METADATA ====== --}}
                    <div x-show="pubTab === 'metadata'"
                        class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-base font-bold text-gray-900">Metadata</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Keywords and other metadata for indexing.</p>
                        </div>
                        <form
                            action="{{ route('journal.workflow.publication.metadata.update', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                            method="POST" class="p-6 space-y-5">
                            @csrf

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keywords</label>
                                <input type="text" name="keywords"
                                    value="{{ old('keywords', $publication->keywords ?? $submission->keywords) }}"
                                    placeholder="Separate keywords with commas"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="mt-1 text-xs text-gray-500">Enter keywords separated by commas (e.g., machine
                                    learning, AI, neural networks)</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pages</label>
                                <input type="text" name="pages"
                                    value="{{ old('pages', $publication->pages ?? '') }}" placeholder="e.g., 1-12"
                                    class="block w-48 rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">URL Path</label>
                                <div class="flex items-center">
                                    <span
                                        class="text-sm text-gray-500 mr-2">{{ config('app.url') }}/article/view/</span>
                                    <input type="text" name="url_path"
                                        value="{{ old('url_path', $publication->url_path ?? '') }}"
                                        placeholder="custom-url-slug"
                                        class="block w-64 rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-gray-100">
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                                    <i class="fa-solid fa-save mr-2"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- ====== REFERENCES (Placeholder) ====== --}}
                    <div x-show="pubTab === 'references'"
                        class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-base font-bold text-gray-900">References</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Manage article references and citations.</p>
                        </div>
                        <div class="p-12 text-center">
                            <div
                                class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-quote-left text-gray-400 text-xl"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-900">References Management</p>
                            <p class="text-xs text-gray-500 mt-1">This feature will be available in a future update.
                            </p>
                        </div>
                    </div>

                    {{-- ====== ISSUE (SCHEDULING) ====== --}}
                    <div x-show="pubTab === 'issue'"
                        class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-base font-bold text-gray-900">Issue</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Schedule this publication to an issue.</p>
                        </div>
                        <form
                            action="{{ route('journal.workflow.publication.issue.assign', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                            method="POST" class="p-6 space-y-5">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Issue <span
                                            class="text-red-500">*</span></label>
                                    <template x-if="isLoadingIssues">
                                        <div class="flex items-center py-2 text-sm text-gray-500">
                                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                                        </div>
                                    </template>
                                    <template x-if="!isLoadingIssues">
                                        <select name="issue_id" required
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">-- Select Issue --</option>
                                            <template x-for="issue in issues" :key="issue.id">
                                                <option :value="issue.id"
                                                    :selected="issue.id === '{{ $publication->issue_id ?? '' }}'"
                                                    x-text="issue.label">
                                                </option>
                                            </template>
                                        </select>
                                    </template>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                                    <template x-if="isLoadingSections">
                                        <div class="flex items-center py-2 text-sm text-gray-500">
                                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                                        </div>
                                    </template>
                                    <template x-if="!isLoadingSections">
                                        <select name="section_id"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">-- Select Section --</option>
                                            <template x-for="section in sections" :key="section.id">
                                                <option :value="section.id"
                                                    :selected="section
                                                        .id === '{{ $publication->section_id ?? ($submission->section_id ?? '') }}'"
                                                    x-text="section.title"></option>
                                            </template>
                                        </select>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pages</label>
                                    <input type="text" name="pages"
                                        value="{{ old('pages', $publication->pages ?? '') }}"
                                        placeholder="e.g., 1-12"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Published</label>
                                    <input type="date" name="date_published"
                                        value="{{ old('date_published', $publication->date_published?->format('Y-m-d') ?? '') }}"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            @if ($publication->issue_id)
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <i class="fa-solid fa-calendar-check text-blue-500 mr-3"></i>
                                        <div>
                                            <p class="text-sm font-medium text-blue-800">Currently Scheduled</p>
                                            <p class="text-xs text-blue-600">
                                                {{ $publication->issue->identifier ?? 'Unknown Issue' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="flex justify-between pt-4 border-t border-gray-100">
                                @if ($publication->issue_id)
                                    <form
                                        action="{{ route('journal.workflow.publication.unschedule', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 border border-gray-200 text-sm font-medium rounded-lg text-gray-600 bg-white hover:bg-gray-50">
                                            <i class="fa-solid fa-calendar-xmark mr-2"></i> Unschedule
                                        </button>
                                    </form>
                                @else
                                    <div></div>
                                @endif
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                                    <i class="fa-solid fa-calendar-check mr-2"></i>
                                    {{ $publication->issue_id ? 'Update Schedule' : 'Schedule' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- ====== LICENSE & DOI ====== --}}
                    <div x-show="pubTab === 'license'"
                        class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-base font-bold text-gray-900">License & DOI</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Copyright, licensing, and identifier information.
                            </p>
                        </div>
                        <form
                            action="{{ route('journal.workflow.publication.license.update', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                            method="POST" class="p-6 space-y-5">
                            @csrf

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">DOI</label>
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-500 mr-2">https://doi.org/</span>
                                    <input type="text" name="doi"
                                        value="{{ old('doi', $publication->doi ?? '') }}"
                                        placeholder="10.xxxx/xxxxx"
                                        class="block w-64 rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Copyright
                                        Holder</label>
                                    <input type="text" name="copyright_holder"
                                        value="{{ old('copyright_holder', $publication->copyright_holder ?? '') }}"
                                        placeholder="e.g., The Author(s)"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Copyright Year</label>
                                    <input type="number" name="copyright_year"
                                        value="{{ old('copyright_year', $publication->copyright_year ?? date('Y')) }}"
                                        min="1900" max="2100"
                                        class="block w-32 rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">License URL</label>
                                <input type="url" name="license_url"
                                    value="{{ old('license_url', $publication->license_url ?? '') }}"
                                    placeholder="https://creativecommons.org/licenses/by/4.0/"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="mt-1 text-xs text-gray-500">Common licenses: CC BY 4.0, CC BY-SA 4.0, CC
                                    BY-NC 4.0</p>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-gray-100">
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                                    <i class="fa-solid fa-save mr-2"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

            {{-- ====== ADD/EDIT CONTRIBUTOR MODAL ====== --}}
            <div x-show="contributorModalOpen" x-cloak class="fixed z-50 inset-0 overflow-y-auto" role="dialog"
                aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="contributorModalOpen" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75 transition-opacity"
                        @click="contributorModalOpen = false"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div x-show="contributorModalOpen" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="inline-block align-bottom bg-white rounded-xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                        <div class="sm:flex sm:items-start mb-5">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fa-solid fa-user-plus text-indigo-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-semibold text-gray-900"
                                    x-text="editingContributor ? 'Edit Contributor' : 'Add Contributor'"></h3>
                                <p class="mt-1 text-sm text-gray-500">Enter the contributor's information.</p>
                            </div>
                        </div>

                        <form
                            :action="editingContributor
                                ?
                                '{{ url('/' . $journal->slug . '/workflow') }}/' + '{{ $submission->id }}' +
                                '/publication/contributor/' + editingContributor.id :
                                '{{ route('journal.workflow.publication.contributor.store', ['journal' => $journal->slug, 'submission' => $submission->id]) }}'"
                            method="POST" class="space-y-4">
                            @csrf
                            <template x-if="editingContributor">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="given_name" required
                                        :value="editingContributor?.given_name || ''"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="family_name" required
                                        :value="editingContributor?.family_name || ''"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span
                                        class="text-red-500">*</span></label>
                                <input type="email" name="email" required
                                    :value="editingContributor?.email || ''"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Affiliation</label>
                                <input type="text" name="affiliation"
                                    :value="editingContributor?.affiliation || ''"
                                    placeholder="e.g., Harvard University"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                    <input type="text" name="country" :value="editingContributor?.country || ''"
                                        placeholder="e.g., United States"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ORCID iD</label>
                                    <input type="text" name="orcid" :value="editingContributor?.orcid || ''"
                                        placeholder="0000-0000-0000-0000"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <div class="space-y-3 pt-2">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="is_corresponding" value="1"
                                            :checked="editingContributor?.is_corresponding"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label class="font-medium text-gray-700">Principal contact for editorial
                                            correspondence</label>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="include_in_browse" value="1"
                                            :checked="editingContributor?.include_in_browse ?? true"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label class="font-medium text-gray-700">Include this contributor in browse
                                            lists</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="submit"
                                    class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:col-start-2 sm:text-sm">
                                    <i class="fa-solid fa-save mr-2"></i> <span
                                        x-text="editingContributor ? 'Update' : 'Add'"></span>
                                </button>
                                <button type="button"
                                    @click="contributorModalOpen = false; editingContributor = null"
                                    class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ====== REORDER CONTRIBUTORS MODAL ====== --}}
            <div x-show="reorderModalOpen" x-cloak class="fixed z-50 inset-0 overflow-y-auto" role="dialog"
                aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="reorderModalOpen" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75 transition-opacity"
                        @click="reorderModalOpen = false"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div x-show="reorderModalOpen" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="inline-block align-bottom bg-white rounded-xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-6">

                        <div class="sm:flex sm:items-start mb-5">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fa-solid fa-arrow-down-short-wide text-indigo-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-semibold text-gray-900">Order Contributors</h3>
                                <p class="mt-1 text-sm text-gray-500">Drag and drop or use arrows to change the order.
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-2 text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-lg p-1 max-h-[300px] overflow-y-auto">
                            <ul class="space-y-1">
                                <template x-for="(author, index) in reorderList" :key="author.id">
                                    <li
                                        class="flex items-center justify-between p-2 bg-white border border-gray-200 rounded shadow-sm">
                                        <span class="font-medium text-gray-900 truncate flex-1 mr-2"
                                            x-text="author.name"></span>
                                        <div class="flex items-center space-x-1">
                                            <button type="button" @click="moveUp(index)" :disabled="index === 0"
                                                class="p-1 text-gray-400 hover:text-indigo-600 disabled:opacity-30 disabled:hover:text-gray-400">
                                                <i class="fa-solid fa-arrow-up"></i>
                                            </button>
                                            <button type="button" @click="moveDown(index)"
                                                :disabled="index === reorderList.length - 1"
                                                class="p-1 text-gray-400 hover:text-indigo-600 disabled:opacity-30 disabled:hover:text-gray-400">
                                                <i class="fa-solid fa-arrow-down"></i>
                                            </button>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <div class="mt-5 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="button" @click="saveOrder()" :disabled="isSavingOrder"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none disabled:opacity-75 sm:col-start-2 sm:text-sm">
                                <i class="fa-solid fa-spinner fa-spin mr-2" x-show="isSavingOrder"></i>
                                <span x-text="isSavingOrder ? 'Saving...' : 'Done'"></span>
                            </button>
                            <button type="button" @click="reorderModalOpen = false"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- NEW DISCUSSION MODAL --}}
        <div x-show="discussionModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="discussionModalOpen = false"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <div
                    class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
                    <div class="mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Add Discussion</h3>
                    </div>

                    <form id="discussion-form"
                        action="{{ route('journal.discussion.create', ['journal' => $journal->slug, 'submission' => $submission]) }}"
                        method="POST">
                        @csrf
                        <input type="hidden" name="stage_id" x-model="discussionStageId">

                        {{-- Hidden inputs for attached files --}}
                        <template x-for="(file, index) in discussionFiles" :key="file.id">
                            <div>
                                <input type="hidden" :name="'attached_files[' + index + '][id]'"
                                    :value="file.id">
                                <input type="hidden" :name="'attached_files[' + index + '][name]'"
                                    :value="file.name">
                            </div>
                        </template>

                        <div class="space-y-4">
                            <div>
                                <label for="subject"
                                    class="block text-sm font-medium text-gray-700">Subject</label>
                                <input type="text" name="subject" id="subject"
                                    class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    required>
                            </div>

                            @if (!auth()->user()->hasRole('Author'))
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Notify
                                        Participants</label>
                                    <div
                                        class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50">
                                        @forelse($participants as $participant)
                                            <label
                                                class="flex items-center gap-3 p-2 rounded-lg hover:bg-white cursor-pointer transition-colors">
                                                <input type="checkbox" name="participants[]"
                                                    value="{{ $participant->id }}" checked
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                                    {{-- Avatar --}}
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                        {{ strtoupper(substr($participant->name, 0, 1)) }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <span
                                                            class="text-sm font-medium text-gray-900 block truncate">{{ $participant->name }}</span>
                                                        <span
                                                            class="text-xs text-gray-500 block truncate">{{ $participant->email }}</span>
                                                    </div>
                                                </div>
                                                {{-- Role Badge --}}
                                                @php
                                                    $role =
                                                        $participant->id === $submission->user_id ? 'Author' : 'Editor';
                                                @endphp
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $role === 'Author' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                                    {{ $role }}
                                                </span>
                                            </label>
                                        @empty
                                            <p class="text-sm text-gray-500 italic text-center py-2">No other
                                                participants to notify.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label for="discussion-editor"
                                    class="block text-sm font-medium text-gray-700">Message</label>
                                <div class="mt-1">
                                    <textarea name="body" id="discussion-editor"></textarea>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900">Attached Files</h4>
                                    <button type="button" @click="fileWizardOpen = true"
                                        class="text-sm text-indigo-600 font-medium hover:underline">
                                        + Attach File
                                    </button>
                                </div>
                                <ul class="mt-3 space-y-2">
                                    <template x-for="file in discussionFiles" :key="file.id">
                                        <li
                                            class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded border border-gray-200">
                                            <div class="flex items-center">
                                                <i class="fa-regular fa-file text-gray-400 mr-2"></i>
                                                <span class="text-sm text-gray-700" x-text="file.name"></span>
                                                <span class="ml-2 text-xs text-gray-500"
                                                    x-text="(file.size / 1024).toFixed(0) + ' KB'"></span>
                                            </div>
                                            <button type="button" class="text-xs text-red-600 hover:text-red-800"
                                                @click="discussionFiles = discussionFiles.filter(f => f.id !== file.id)">Remove</button>
                                        </li>
                                    </template>
                                    <template x-if="discussionFiles.length === 0">
                                        <li class="text-sm text-gray-500 italic">No files attached.</li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="button" @click="submitDiscussion()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:col-start-2 sm:text-sm">
                                OK
                            </button>
                            <button type="button" @click="discussionModalOpen = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- FILE WIZARD MODAL (Nested/Overlay) --}}
        <div x-show="fileWizardOpen" style="display: none;" class="fixed inset-0 z-[60] overflow-y-auto"
            aria-labelledby="wizard-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <div
                    class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="mb-4 border-b border-gray-200 pb-2">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="wizard-title">
                            Add File to Discussion
                        </h3>
                        <div class="flex space-x-2 mt-2">
                            <span :class="wizardStep >= 1 ? 'text-indigo-600 font-bold' : 'text-gray-400'"
                                class="text-xs">1. Upload</span>
                            <span :class="wizardStep >= 2 ? 'text-indigo-600 font-bold' : 'text-gray-400'"
                                class="text-xs">2. Metadata</span>
                            <span :class="wizardStep >= 3 ? 'text-indigo-600 font-bold' : 'text-gray-400'"
                                class="text-xs">3. Confirm</span>
                        </div>
                    </div>

                    {{-- Step 1: Upload --}}
                    <div x-show="wizardStep === 1">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Article Component</label>
                                <select
                                    class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option>Article Text</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div
                                class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md relative hover:bg-gray-50">
                                <div class="space-y-1 text-center">
                                    <i class="fa-solid fa-cloud-arrow-up text-gray-400 text-3xl"></i>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label
                                            class="relative cursor-pointer rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                            <span>Upload a file</span>
                                            <input type="file" class="sr-only" @change="handleFileUpload">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">Drag and drop or select file</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:flex sm:flex-row-reverse">
                            <button type="button" @click="fileWizardOpen = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancel</button>
                        </div>
                    </div>

                    {{-- Step 2: Metadata --}}
                    <template x-if="wizardStep === 2">
                        <div>
                            <div class="space-y-4">
                                <p class="text-sm text-gray-500">File uploaded. Please review metadata.</p>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Filename</label>
                                    <input type="text" x-model="tempUploadedFile.name"
                                        class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                            <div class="mt-5 sm:flex sm:flex-row-reverse">
                                <button type="button" @click="wizardStep = 3"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Continue</button>
                                <button type="button" @click="fileWizardOpen = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancel</button>
                            </div>
                        </div>
                    </template>

                    {{-- Step 3: Confirm --}}
                    <div x-show="wizardStep === 3">
                        <div class="text-center py-4">
                            <div
                                class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-3">
                                <i class="fa-solid fa-check text-green-600 text-lg"></i>
                            </div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">File Added</h3>
                            <p class="text-sm text-gray-500 mt-2">The file <span class="font-bold"
                                    x-text="tempUploadedFile && tempUploadedFile.name"></span> is ready to be
                                attached.
                            </p>
                        </div>
                        <div class="mt-5 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="button" @click="completeWizard()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:col-start-2 sm:text-sm">
                                Complete
                            </button>
                            <button type="button" @click="addAnotherFile()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                Add Another File
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Original File Modal for main submission files (kept for reference or reuse) --}}
        <div x-show="fileModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="fileModalOpen = false"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <div
                    class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                    <div class="mb-5">
                        <h3 class="text-lg leading-6 font-bold text-gray-900">Upload Submission File</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Uploading file to <strong class="text-indigo-600"><span
                                    x-text="uploadStage.charAt(0).toUpperCase() + uploadStage.slice(1)"></span></strong>
                            stage.
                        </p>
                    </div>

                    <form
                        action="{{ route('journal.workflow.file.store', ['journal' => $journal->slug, 'submission' => $submission->slug]) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="stage" x-model="uploadStage">

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
                                <div
                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 hover:border-indigo-400 transition-colors cursor-pointer relative group">
                                    <div class="space-y-1 text-center">
                                        <i
                                            class="fa-solid fa-cloud-arrow-up text-gray-400 text-3xl mb-3 group-hover:text-indigo-500 transition-colors"></i>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <span
                                                class="relative bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload a file</span>
                                            </span>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            PDF, DOC, DOCX, XLS up to 10MB
                                        </p>
                                        <p x-ref="fileNameDisplay"
                                            class="text-sm text-indigo-600 font-medium mt-2 min-h-[20px]"></p>
                                    </div>
                                    <input type="file" name="file"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required
                                        @change="$refs.fileNameDisplay.innerText = $event.target.files[0].name">
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:col-start-2 sm:text-sm">
                                <i class="fa-solid fa-upload mr-2 mt-0.5"></i> Upload
                            </button>
                            <button type="button" @click="fileModalOpen = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ==================== ADD REVIEWER MODAL ==================== --}}
        <div x-show="reviewerModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="reviewer-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="reviewerModalOpen = false"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <div
                    class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="mb-6">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="reviewer-modal-title">
                            <i class="fa-solid fa-user-plus text-indigo-500 mr-2"></i>Add Reviewer
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">Search and assign a reviewer to this submission.</p>
                    </div>

                    <form
                        action="{{ route('journal.workflow.assign-reviewer', ['journal' => $journal->slug, 'submission' => $submission->slug]) }}"
                        method="POST">
                        @csrf
                        <input type="hidden" name="reviewer_id" x-bind:value="selectedReviewer?.id || ''">

                        <div class="space-y-5">
                            {{-- Reviewer Search --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search Reviewer</label>
                                <div class="relative">
                                    <input type="text" x-model="reviewerSearch"
                                        @input.debounce.300ms="searchReviewers()"
                                        placeholder="Type name or email..."
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-search text-gray-400"></i>
                                    </div>
                                    <template x-if="isSearching">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <i class="fa-solid fa-spinner fa-spin text-gray-400"></i>
                                        </div>
                                    </template>
                                </div>
                                {{-- Search Results Dropdown --}}
                                <template x-if="reviewerResults.length > 0">
                                    <ul
                                        class="mt-1 border border-gray-200 rounded-md bg-white shadow-lg max-h-40 overflow-y-auto">
                                        <template x-for="reviewer in reviewerResults" :key="reviewer.id">
                                            <li @click="selectReviewer(reviewer)"
                                                class="px-4 py-2 hover:bg-indigo-50 cursor-pointer flex items-center justify-between">
                                                <div>
                                                    <span class="text-sm font-medium text-gray-900"
                                                        x-text="reviewer.name"></span>
                                                    <span class="text-xs text-gray-500 ml-2"
                                                        x-text="reviewer.email"></span>
                                                </div>
                                                <i class="fa-solid fa-plus text-indigo-500"></i>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                                {{-- Selected Reviewer Display --}}
                                <template x-if="selectedReviewer">
                                    <div
                                        class="mt-2 flex items-center justify-between bg-indigo-50 p-3 rounded-lg border border-indigo-200">
                                        <div class="flex items-center">
                                            <div
                                                class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-xs">
                                                <span x-text="selectedReviewer.name.charAt(0).toUpperCase()"></span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-indigo-900"
                                                    x-text="selectedReviewer.name"></p>
                                                <p class="text-xs text-indigo-700" x-text="selectedReviewer.email">
                                                </p>
                                            </div>
                                        </div>
                                        <button type="button" @click="selectedReviewer = null; reviewerSearch = ''"
                                            class="text-indigo-600 hover:text-indigo-800">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            {{-- Review Method --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Review Method</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <label
                                        class="relative flex cursor-pointer rounded-lg border bg-white p-3 shadow-sm focus:outline-none"
                                        :class="reviewMethod === 'double_blind' ? 'border-indigo-500 ring-2 ring-indigo-500' :
                                            'border-gray-300'">
                                        <input type="radio" name="review_method" value="double_blind"
                                            x-model="reviewMethod" class="sr-only">
                                        <span class="flex flex-1 flex-col text-center">
                                            <i class="fa-solid fa-eye-slash text-gray-500 text-lg mb-1"></i>
                                            <span class="block text-xs font-medium text-gray-900">Double Blind</span>
                                        </span>
                                    </label>
                                    <label
                                        class="relative flex cursor-pointer rounded-lg border bg-white p-3 shadow-sm focus:outline-none"
                                        :class="reviewMethod === 'blind' ? 'border-indigo-500 ring-2 ring-indigo-500' :
                                            'border-gray-300'">
                                        <input type="radio" name="review_method" value="blind"
                                            x-model="reviewMethod" class="sr-only">
                                        <span class="flex flex-1 flex-col text-center">
                                            <i class="fa-solid fa-user-secret text-gray-500 text-lg mb-1"></i>
                                            <span class="block text-xs font-medium text-gray-900">Blind</span>
                                        </span>
                                    </label>
                                    <label
                                        class="relative flex cursor-pointer rounded-lg border bg-white p-3 shadow-sm focus:outline-none"
                                        :class="reviewMethod === 'open' ? 'border-indigo-500 ring-2 ring-indigo-500' :
                                            'border-gray-300'">
                                        <input type="radio" name="review_method" value="open"
                                            x-model="reviewMethod" class="sr-only">
                                        <span class="flex flex-1 flex-col text-center">
                                            <i class="fa-solid fa-eye text-gray-500 text-lg mb-1"></i>
                                            <span class="block text-xs font-medium text-gray-900">Open</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            {{-- Due Dates --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Response Due
                                        Date</label>
                                    <input type="date" name="response_due_date" x-model="responseDueDate"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Review Due
                                        Date</label>
                                    <input type="date" name="review_due_date" x-model="reviewDueDate"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit" :disabled="!selectedReviewer"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed sm:col-start-2 sm:text-sm">
                                <i class="fa-solid fa-paper-plane mr-2"></i> Assign Reviewer
                            </button>
                            <button type="button" @click="reviewerModalOpen = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ==================== ASSIGN EDITOR MODAL ==================== --}}
        <div x-show="assignEditorModalOpen" x-cloak class="fixed z-50 inset-0 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div x-show="assignEditorModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75 transition-opacity"
                    @click="assignEditorModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div x-show="assignEditorModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fa-solid fa-user-plus text-indigo-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Assign Editor
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Search and assign an editor to handle this submission.
                            </p>
                        </div>
                    </div>

                    <form
                        action="{{ route('journal.workflow.assign-editor', ['journal' => $journal->slug, 'submission' => $submission->slug]) }}"
                        method="POST" class="mt-5">
                        @csrf
                        <div class="space-y-4">
                            {{-- Editor Search --}}
                            <div>
                                <label for="editor-search"
                                    class="block text-sm font-medium text-gray-700 mb-1">Search
                                    Editor</label>
                                <div class="relative">
                                    <input type="text" id="editor-search" x-model="editorSearch"
                                        @input.debounce.300ms="searchEditors()"
                                        placeholder="Type to search editors..."
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        autocomplete="off">
                                    <div x-show="isSearchingEditors" class="absolute right-3 top-2.5">
                                        <i class="fa-solid fa-spinner fa-spin text-gray-400"></i>
                                    </div>
                                </div>

                                {{-- Search Results Dropdown --}}
                                <div x-show="editorResults.length > 0"
                                    class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                                    style="max-width: calc(100% - 2rem);">
                                    <template x-for="editor in editorResults" :key="editor.id">
                                        <div @click="selectEditor(editor)"
                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50">
                                            <div class="flex items-center">
                                                <span class="font-medium block truncate"
                                                    x-text="editor.name"></span>
                                            </div>
                                            <span class="text-gray-500 text-xs" x-text="editor.email"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Selected Editor Display --}}
                            <div x-show="selectedEditor"
                                class="bg-indigo-50 border border-indigo-200 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-sm"
                                            x-text="selectedEditor?.name?.charAt(0)?.toUpperCase()">
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900"
                                                x-text="selectedEditor?.name">
                                            </p>
                                            <p class="text-xs text-gray-500" x-text="selectedEditor?.email"></p>
                                        </div>
                                    </div>
                                    <button type="button" @click="selectedEditor = null; editorSearch = ''"
                                        class="text-gray-400 hover:text-gray-600">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="user_id" :value="selectedEditor?.id">
                            </div>

                            {{-- Role Selection --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Assignment Role</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label
                                        class="relative flex cursor-pointer rounded-lg border bg-white p-3 shadow-sm focus:outline-none"
                                        :class="editorRole === 'editor' ? 'border-indigo-500 ring-2 ring-indigo-500' :
                                            'border-gray-300'">
                                        <input type="radio" name="role" value="editor" x-model="editorRole"
                                            class="sr-only">
                                        <span class="flex flex-1 flex-col text-center">
                                            <i class="fa-solid fa-user-pen text-gray-500 text-lg mb-1"></i>
                                            <span class="block text-xs font-medium text-gray-900">Editor</span>
                                        </span>
                                    </label>
                                    <label
                                        class="relative flex cursor-pointer rounded-lg border bg-white p-3 shadow-sm focus:outline-none"
                                        :class="editorRole === 'section_editor' ? 'border-indigo-500 ring-2 ring-indigo-500' :
                                            'border-gray-300'">
                                        <input type="radio" name="role" value="section_editor"
                                            x-model="editorRole" class="sr-only">
                                        <span class="flex flex-1 flex-col text-center">
                                            <i class="fa-solid fa-user-tag text-gray-500 text-lg mb-1"></i>
                                            <span class="block text-xs font-medium text-gray-900">Section
                                                Editor</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit" :disabled="!selectedEditor"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed sm:col-start-2 sm:text-sm">
                                <i class="fa-solid fa-user-plus mr-2"></i> Assign Editor
                            </button>
                            <button type="button" @click="assignEditorModalOpen = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ==================== SEND TO REVIEW MODAL ==================== --}}
        <div x-show="sendToReviewModalOpen" x-cloak class="fixed z-50 inset-0 overflow-y-auto"
            aria-labelledby="send-review-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="sendToReviewModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75 transition-opacity"
                    @click="sendToReviewModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="sendToReviewModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fa-solid fa-arrow-right text-indigo-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-semibold text-gray-900" id="send-review-modal-title">
                                Send to Review
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Select files to promote to the Review stage. Original files will remain in the
                                Submission stage.
                            </p>
                        </div>
                    </div>

                    <form
                        action="{{ route('journal.workflow.promote-review', ['journal' => $journal->slug, 'submission' => $submission->slug]) }}"
                        method="POST" class="mt-5">
                        @csrf

                        {{-- File Selection --}}
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <h4 class="text-sm font-medium text-gray-900">
                                    <i class="fa-solid fa-file-lines text-gray-400 mr-2"></i>
                                    Select Files to Promote
                                </h4>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <template x-if="isLoadingFiles">
                                    <div class="px-4 py-8 text-center">
                                        <i class="fa-solid fa-spinner fa-spin text-gray-400 text-xl"></i>
                                        <p class="text-sm text-gray-500 mt-2">Loading files...</p>
                                    </div>
                                </template>
                                <template x-if="!isLoadingFiles && availableFiles.length === 0">
                                    <div class="px-4 py-8 text-center">
                                        <i class="fa-solid fa-folder-open text-gray-300 text-2xl"></i>
                                        <p class="text-sm text-gray-500 mt-2">No files available for promotion.</p>
                                    </div>
                                </template>
                                <template x-if="!isLoadingFiles && availableFiles.length > 0">
                                    <ul class="divide-y divide-gray-100">
                                        <template x-for="file in availableFiles" :key="file.id">
                                            <li class="px-4 py-3 hover:bg-gray-50 cursor-pointer"
                                                @click="toggleFileSelection(file)">
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="checkbox" :checked="isFileSelected(file)"
                                                        :name="'selected_files[' + availableFiles.indexOf(file) + '][id]'"
                                                        :value="file.id"
                                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                    <input type="hidden" x-show="isFileSelected(file)"
                                                        :name="'selected_files[' + availableFiles.indexOf(file) + '][type]'"
                                                        :value="file.type">
                                                    <div class="ml-3 flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate"
                                                            x-text="file.name"></p>
                                                        <p class="text-xs text-gray-500">
                                                            <span x-text="file.source"></span> •
                                                            <span
                                                                x-text="(file.size / 1024).toFixed(0) + ' KB'"></span>
                                                            •
                                                            <span x-text="file.created_at"></span>
                                                        </p>
                                                    </div>
                                                </label>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                            </div>
                            <div class="bg-gray-50 px-4 py-2 border-t border-gray-200">
                                <p class="text-xs text-gray-500">
                                    <span x-text="selectedFilesForPromotion.length"></span> file(s) selected
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                                <i class="fa-solid fa-arrow-right mr-2"></i> Send to Review
                            </button>
                            <button type="button" @click="sendToReviewModalOpen = false"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ==================== ACCEPT & SKIP REVIEW MODAL ==================== --}}
        <div x-show="skipReviewModalOpen" x-cloak class="fixed z-50 inset-0 overflow-y-auto"
            aria-labelledby="skip-review-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="skipReviewModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75 transition-opacity"
                    @click="skipReviewModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="skipReviewModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fa-solid fa-forward text-emerald-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-semibold text-gray-900" id="skip-review-modal-title">
                                Accept & Skip Review
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                This will bypass the Review stage and move the submission directly to Copyediting.
                            </p>
                        </div>
                    </div>

                    <form
                        action="{{ route('journal.workflow.skip-review', ['journal' => $journal->slug, 'submission' => $submission->slug]) }}"
                        method="POST" class="mt-5">
                        @csrf

                        {{-- Warning Banner --}}
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                            <div class="flex">
                                <i class="fa-solid fa-exclamation-triangle text-yellow-500 mt-0.5 mr-3"></i>
                                <div class="text-sm text-yellow-700">
                                    <p class="font-medium">Are you sure?</p>
                                    <p class="mt-1">This action will accept the submission without peer review. Use
                                        this only for trusted authors or special cases.</p>
                                </div>
                            </div>
                        </div>

                        {{-- File Selection --}}
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <h4 class="text-sm font-medium text-gray-900">
                                    <i class="fa-solid fa-file-lines text-gray-400 mr-2"></i>
                                    Select Files to Promote to Copyediting
                                </h4>
                            </div>
                            <div class="max-h-48 overflow-y-auto">
                                <template x-if="isLoadingFiles">
                                    <div class="px-4 py-6 text-center">
                                        <i class="fa-solid fa-spinner fa-spin text-gray-400"></i>
                                        <p class="text-sm text-gray-500 mt-2">Loading files...</p>
                                    </div>
                                </template>
                                <template x-if="!isLoadingFiles && availableFiles.length > 0">
                                    <ul class="divide-y divide-gray-100">
                                        <template x-for="file in availableFiles" :key="file.id">
                                            <li class="px-4 py-3 hover:bg-gray-50 cursor-pointer"
                                                @click="toggleFileSelection(file)">
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="checkbox" :checked="isFileSelected(file)"
                                                        :name="'selected_files[' + availableFiles.indexOf(file) + '][id]'"
                                                        :value="file.id"
                                                        class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                                                    <input type="hidden" x-show="isFileSelected(file)"
                                                        :name="'selected_files[' + availableFiles.indexOf(file) + '][type]'"
                                                        :value="file.type">
                                                    <div class="ml-3 flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate"
                                                            x-text="file.name"></p>
                                                        <p class="text-xs text-gray-500"
                                                            x-text="file.source + ' • ' + (file.size / 1024).toFixed(0) + ' KB'">
                                                        </p>
                                                    </div>
                                                </label>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                            </div>
                        </div>

                        {{-- Notes (Optional) --}}
                        <div class="mt-4">
                            <label for="skip-review-notes" class="block text-sm font-medium text-gray-700">Notes
                                (Optional)</label>
                            <textarea id="skip-review-notes" name="notes" rows="2" x-model="skipReviewNotes"
                                placeholder="Reason for skipping review..."
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"></textarea>
                        </div>

                        <div class="mt-5 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:col-start-2 sm:text-sm">
                                <i class="fa-solid fa-check mr-2"></i> Accept & Skip Review
                            </button>
                            <button type="button" @click="skipReviewModalOpen = false"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ==================== DECLINE SUBMISSION MODAL ==================== --}}
        <div x-show="declineModalOpen" x-cloak class="fixed z-50 inset-0 overflow-y-auto"
            aria-labelledby="decline-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="declineModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75 transition-opacity"
                    @click="declineModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="declineModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fa-solid fa-ban text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-semibold text-gray-900" id="decline-modal-title">
                                Decline Submission
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                This will reject the submission. Please provide a reason for declining.
                            </p>
                        </div>
                    </div>

                    <form
                        action="{{ route('journal.workflow.decline', ['journal' => $journal->slug, 'submission' => $submission->slug]) }}"
                        method="POST" class="mt-5">
                        @csrf

                        {{-- Reason Textarea --}}
                        <div>
                            <label for="decline-reason" class="block text-sm font-medium text-gray-700">
                                Reason for Declining <span class="text-red-500">*</span>
                            </label>
                            <textarea id="decline-reason" name="reason" rows="4" required minlength="10" x-model="declineReason"
                                placeholder="Please explain why this submission is being declined. This will be logged for future reference."
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"></textarea>
                            <p class="mt-1 text-xs text-gray-500">Minimum 10 characters required.</p>
                        </div>

                        {{-- Notify Author Checkbox --}}
                        <div class="mt-4 flex items-start">
                            <div class="flex items-center h-5">
                                <input id="notify-author" name="notify_author" type="checkbox"
                                    x-model="notifyAuthor"
                                    class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="notify-author" class="font-medium text-gray-700">Notify the
                                    Author</label>
                                <p class="text-gray-500">Send an email notification to the author about this decision.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit" :disabled="declineReason.length < 10"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed sm:col-start-2 sm:text-sm">
                                <i class="fa-solid fa-ban mr-2"></i> Decline Submission
                            </button>
                            <button type="button" @click="declineModalOpen = false"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <style>
        .ck-editor__editable {
            min-height: 250px !important;
        }

        .ck-editor__editable:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
        }
    </style>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const abstractTextarea = document.querySelector('#publicationAbstract');
            if (abstractTextarea) {
                ClassicEditor
                    .create(abstractTextarea, {
                        toolbar: {
                            items: [
                                'heading', '|',
                                'bold', 'italic', '|',
                                'bulletedList', 'numberedList', '|',
                                'outdent', 'indent', '|',
                                'link', 'blockQuote', 'insertTable', '|',
                                'undo', 'redo'
                            ]
                        }
                    })
                    .catch(error => {
                        console.error('CKEditor initialization failed:', error);
                    });
            }
        });
    </script>
</x-app-layout>
