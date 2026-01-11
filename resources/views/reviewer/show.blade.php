@php
    $journal = current_journal();
@endphp

<x-app-layout>
    <x-slot name="title">Review Submission</x-slot>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('journal.reviewer.index', ['journal' => $journal->slug]) }}"
                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Review Submission</h1>
                <p class="mt-1 text-sm text-gray-500">Round {{ $assignment->round }} • Due
                    {{ $assignment->due_date?->format('M j, Y') ?? 'No deadline' }}</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6" x-data="{
        discussionModalOpen: false,
        fileWizardOpen: false,
        discussionFiles: [],
        wizardStep: 1,
        tempUploadedFile: null,
        editorInstance: null,
        messageBody: '',
        discussionStageId: 2, // Review Stage
    
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
        }
    }" x-init="$watch('discussionModalOpen', value => { if (value) setTimeout(() => initEditor(), 100); })">

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Left Column: Manuscript Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Submission Info (Blind Review) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800">
                            {{ $submission->section->name ?? 'Uncategorized' }}
                        </span>
                        <span class="text-sm text-gray-500">
                            Submitted {{ $submission->submitted_at?->format('M j, Y') }}
                        </span>
                    </div>

                    <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $submission->title }}</h2>

                    <!-- Additional Info Table -->
                    <div class="mb-6 bg-gray-50 rounded-lg p-4 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span
                                class="block text-gray-500 text-xs uppercase tracking-wider font-semibold">Language</span>
                            <span class="text-gray-900">{{ $submission->metadata['language'] ?? 'English' }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500 text-xs uppercase tracking-wider font-semibold">Submission
                                Type</span>
                            <span class="text-gray-900">{{ $submission->section->name ?? 'Article' }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500 text-xs uppercase tracking-wider font-semibold">Manuscript
                                ID</span>
                            <span class="text-gray-900">{{ substr($submission->id, 0, 8) }}</span>
                        </div>
                        <div>
                            <span
                                class="block text-gray-500 text-xs uppercase tracking-wider font-semibold">Copyright</span>
                            <span class="text-gray-900">© {{ now()->year }} {{ $journal->name }}</span>
                        </div>
                    </div>

                    <!-- Abstract -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Abstract</h3>
                        <div class="prose prose-sm max-w-none text-gray-600">
                            {!! $submission->abstract !!}
                        </div>
                    </div>

                    <!-- Keywords -->
                    @if ($submission->keywords)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Keywords</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($submission->keywords_array as $keyword)
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-700">
                                        {{ $keyword }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Manuscript Files -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Manuscript Files</h3>

                    @if ($manuscriptFiles->isEmpty())
                        <p class="text-gray-500">No manuscript files available.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($manuscriptFiles as $file)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                            <i class="fa-solid fa-file-pdf text-red-500"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $file->file_name }}</p>
                                            <p class="text-sm text-gray-500">
                                                {{ $file->file_type_label }} • Version {{ $file->version }} •
                                                {{ $file->file_size_formatted }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @php
                                            $extension = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
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
                                        <a href="{{ route('files.download', $file) }}"
                                            class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                                            <i class="fa-solid fa-download mr-1.5"></i>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Review Discussions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Review Discussions</h3>
                        <button @click="discussionModalOpen = true; resetDiscussionForm()"
                            class="text-sm text-primary-600 font-medium hover:text-primary-800">
                            + Add Discussion
                        </button>
                    </div>
                    <div class="divide-y divide-gray-100">
                        {{-- Filter discussions for Stage 2 (Review) --}}
                        @forelse($submission->discussions->where('stage_id', 2) as $discussion)
                            <details class="group">
                                <summary
                                    class="flex items-center justify-between px-4 py-3 cursor-pointer hover:bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-4">
                                        <i class="fa-regular fa-comments text-gray-400 group-open:text-primary-500"></i>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 group-open:text-primary-600">
                                                {{ $discussion->subject }}</p>
                                            <p class="text-xs text-gray-500">
                                                From {{ $discussion->user->name }} •
                                                {{ $discussion->created_at->format('M d') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $discussion->messages->count() }} replies
                                        </span>
                                        <i
                                            class="fa-solid fa-chevron-down text-gray-400 transform group-open:rotate-180 transition-transform"></i>
                                    </div>
                                </summary>
                                <div class="px-4 py-4 bg-gray-50 border-t border-gray-100 space-y-4 rounded-b-lg">
                                    @foreach ($discussion->messages as $message)
                                        <div class="flex gap-3">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 text-xs font-bold">
                                                    {{ substr($message->user->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div
                                                class="bg-white p-3 rounded-lg shadow-sm border border-gray-200 flex-1">
                                                <div class="flex justify-between items-start mb-1">
                                                    <span
                                                        class="text-xs font-semibold text-gray-900">{{ $message->user->name }}</span>
                                                    <span
                                                        class="text-xs text-gray-400">{{ $message->created_at->format('M d, H:i') }}</span>
                                                </div>
                                                <div class="prose prose-sm text-gray-700 max-w-none">
                                                    {!! $message->body !!}
                                                </div>
                                                @if ($message->files->count() > 0)
                                                    <div class="mt-3 border-t border-gray-100 pt-2">
                                                        <p class="text-xs font-bold text-gray-500 mb-1">Attached Files:
                                                        </p>
                                                        <ul class="space-y-1">
                                                            @foreach ($message->files as $file)
                                                                <li>
                                                                    <a href="{{ route('journal.discussion.file.download', ['journal' => $journal->slug, 'file' => $file->id]) }}"
                                                                        class="text-xs text-blue-600 hover:underline flex items-center">
                                                                        <i class="fa-solid fa-paperclip mr-1"></i>
                                                                        {{ $file->original_name }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    {{-- Reply Form --}}
                                    <div class="mt-4 pl-11">
                                        <form
                                            action="{{ route('journal.discussion.reply', ['journal' => $journal->slug, 'submission' => $submission->id, 'discussion' => $discussion->id]) }}"
                                            method="POST">
                                            @csrf
                                            <div class="flex gap-2">
                                                <input type="text" name="body" placeholder="Write a reply..."
                                                    class="flex-1 shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                                <button type="submit"
                                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none">Reply</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </details>
                        @empty
                            <div class="px-6 py-4 text-center text-sm text-gray-500 italic">
                                No discussions started in this stage.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Review Form (only if not completed) -->
                @if ($assignment->status !== 'completed')
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Submit Your Review</h3>

                        <form
                            action="{{ route('journal.reviewer.submit', ['journal' => $journal->slug, 'assignment' => $assignment]) }}"
                            method="POST" x-data="{ recommendation: '{{ old('recommendation') }}' }">
                            @csrf

                            <!-- Recommendation -->
                            <div class="mb-6">
                                <label for="recommendation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Recommendation <span class="text-red-500">*</span>
                                </label>
                                <select name="recommendation" id="recommendation" x-model="recommendation" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">Select your recommendation...</option>
                                    <option value="accept">Accept - Ready for publication</option>
                                    <option value="minor_revision">Minor Revision - Accept with minor changes</option>
                                    <option value="major_revision">Major Revision - Significant changes required
                                    </option>
                                    <option value="resubmit">Resubmit for Review - Needs substantial rework</option>
                                    <option value="reject">Reject - Not suitable for publication</option>
                                </select>
                                @error('recommendation')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Comments for Author -->
                            <div class="mb-6">
                                <label for="comments_for_author" class="block text-sm font-medium text-gray-700 mb-2">
                                    Comments for Author <span class="text-red-500">*</span>
                                </label>
                                <textarea name="comments_for_author" id="comments_for_author" rows="8" required
                                    placeholder="Provide detailed feedback..."
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('comments_for_author') }}</textarea>
                                @error('comments_for_author')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">These comments will be visible to the author.</p>
                            </div>

                            <!-- Comments for Editor (Confidential) -->
                            <div class="mb-6">
                                <label for="comments_for_editor" class="block text-sm font-medium text-gray-700 mb-2">
                                    Confidential Comments for Editor
                                </label>
                                <textarea name="comments_for_editor" id="comments_for_editor" rows="4"
                                    placeholder="Optional: Share any confidential observations..."
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('comments_for_editor') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-yellow-500" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    These comments are confidential and will only be visible to the editor.
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                                <a href="{{ route('journal.reviewer.index', ['journal' => $journal->slug]) }}"
                                    class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                    Save as Draft
                                </a>
                                <button type="submit"
                                    class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Submit Review
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Completed Review Summary -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Your Review</h3>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Completed {{ $assignment->completed_at?->format('M j, Y') }}
                            </span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Recommendation</h4>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-{{ $assignment->recommendation_color }}-100 text-{{ $assignment->recommendation_color }}-800">
                                    {{ $assignment->recommendation_label }}
                                </span>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Comments for Author</h4>
                                <div class="prose prose-sm max-w-none text-gray-700 bg-gray-50 rounded-lg p-4">
                                    {!! nl2br(e($assignment->comments_for_author)) !!}
                                </div>
                            </div>

                            @if ($assignment->comments_for_editor)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Confidential Comments for Editor
                                    </h4>
                                    <div
                                        class="prose prose-sm max-w-none text-gray-700 bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                        {!! nl2br(e($assignment->comments_for_editor)) !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Review Guidelines -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Review Guidelines</h3>

                    <div class="space-y-4 text-sm text-gray-600">
                        <div class="flex items-start space-x-3">
                            <div
                                class="w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-primary-700 font-bold text-xs">1</span>
                            </div>
                            <p>Read the manuscript thoroughly and assess its scientific quality.</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div
                                class="w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-primary-700 font-bold text-xs">2</span>
                            </div>
                            <p>Evaluate methodology, results, and conclusions for validity.</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div
                                class="w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-primary-700 font-bold text-xs">3</span>
                            </div>
                            <p>Provide constructive feedback to help improve the manuscript.</p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="font-medium text-gray-900 mb-2">Need Help?</h4>
                        <p class="text-sm text-gray-500">Contact the editorial team if you have questions.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Discussion Modal (Triggered by 'Add Discussion') -->
        <div x-show="discussionModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="discussionModalOpen = false"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <div
                    class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full sm:p-6">
                    <div>
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
                            <i class="fa-regular fa-comments text-indigo-600 text-lg"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Start a Review Discussion
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Discussions are a good way to communicate with editors about reviews, delays, or
                                    other concerns.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6">
                        <form id="discussion-form"
                            action="{{ route('journal.discussion.create', ['journal' => $journal->slug, 'submission' => $submission->id]) }}"
                            method="POST">
                            @csrf
                            <input type="hidden" name="stage_id" :value="discussionStageId">

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Subject</label>
                                    <input type="text" name="subject" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Message</label>
                                    <div class="mt-1">
                                        <textarea id="discussion-editor" name="body" class="block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                                    </div>
                                </div>

                                {{-- Attached Files Display --}}
                                <template x-if="discussionFiles.length > 0">
                                    <div class="mt-4">
                                        <p class="text-sm font-medium text-gray-700 mb-2">Attached Files:</p>
                                        <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                                            <template x-for="file in discussionFiles" :key="file.id">
                                                <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                                    <div class="w-0 flex-1 flex items-center">
                                                        <i
                                                            class="fa-solid fa-paperclip text-gray-400 flex-shrink-0"></i>
                                                        <span class="ml-2 flex-1 w-0 truncate"
                                                            x-text="file.original_name"></span>
                                                    </div>
                                                    <input type="hidden" name="files[]" :value="file.id">
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>

                                <div class="mt-4">
                                    <button type="button" @click="fileWizardOpen = true"
                                        class="text-sm text-indigo-600 hover:text-indigo-500 font-medium flex items-center">
                                        <i class="fa-solid fa-paperclip mr-1"></i> Attach File
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="button" @click="submitDiscussion()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:col-start-2 sm:text-sm">
                            Create Discussion
                        </button>
                        <button type="button" @click="discussionModalOpen = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- File Upload Wizard Modal (Reuse logic or simplify) --}}
        <div x-show="fileWizardOpen" style="display: none;" class="fixed inset-0 z-[60] overflow-y-auto"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="fileWizardOpen = false"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <div
                    class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div x-show="wizardStep === 1">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Upload File</h3>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Select File</label>
                            <input type="file" @change="handleFileUpload($event)"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>
                    <div x-show="wizardStep === 2">
                        <div class="text-center">
                            <i class="fa-solid fa-check-circle text-green-500 text-4xl mb-3"></i>
                            <h3 class="text-lg font-medium text-gray-900">File Uploaded!</h3>
                            <button @click="completeWizard()"
                                class="mt-4 w-full bg-indigo-600 text-white rounded-md py-2">Attach to
                                Discussion</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
