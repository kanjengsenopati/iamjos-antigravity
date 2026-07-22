@props(['submission', 'stageId', 'stageName', 'discussions' => collect(), 'participants' => collect(), 'journal'])

@php
    $currentUser = auth()->user();
    $isEditor = $currentUser->hasJournalPermission([
        \App\Models\Role::LEVEL_EDITOR,
        \App\Models\Role::LEVEL_SECTION_EDITOR,
        \App\Models\Role::LEVEL_MANAGER,
        \App\Models\Role::LEVEL_ADMIN
    ], $journal->id);
    $stageDiscussions = $discussions->where('stage_id', $stageId);
    $isId = app()->getLocale() === 'id';

    // Helper untuk menentukan Role label dan styling badge partisipan / pengirim pesan
    $assignedReviewerIds = $submission->relationLoaded('reviewAssignments') 
        ? $submission->reviewAssignments->pluck('reviewer_id')->filter()->toArray() 
        : \App\Models\ReviewAssignment::where('submission_id', $submission->id)->pluck('reviewer_id')->filter()->toArray();

    $getParticipantRole = function ($user) use ($submission, $journal, $isId, $assignedReviewerIds) {
        if (!$user) {
            return [
                'label' => $isId ? 'Pengguna' : 'User',
                'class' => 'bg-slate-50 text-slate-700',
                'style' => '',
                'textClass' => 'text-slate-700'
            ];
        }

        if ($user->id === $submission->user_id) {
            return [
                'label' => $isId ? 'Penulis' : 'Author',
                'class' => 'bg-amber-55 text-amber-750',
                'style' => 'background-color: rgba(245, 158, 11, 0.1); color: #D97706;',
                'textClass' => 'text-amber-700'
            ];
        }

        $isReviewer = in_array($user->id, $assignedReviewerIds) 
            || (method_exists($user, 'hasJournalRole') && $user->hasJournalRole('Reviewer', $journal->id))
            || (method_exists($user, 'hasRole') && $user->hasRole('Reviewer'));

        $isEditorUser = method_exists($user, 'hasJournalPermission') && $user->hasJournalPermission([
            \App\Models\Role::LEVEL_EDITOR,
            \App\Models\Role::LEVEL_SECTION_EDITOR,
            \App\Models\Role::LEVEL_MANAGER,
            \App\Models\Role::LEVEL_ADMIN
        ], $journal->id);

        if ($isReviewer && !$isEditorUser) {
            return [
                'label' => 'Reviewer',
                'class' => 'bg-purple-50 text-purple-700',
                'style' => 'background-color: rgba(147, 51, 234, 0.1); color: #7E22CE;',
                'textClass' => 'text-purple-700'
            ];
        }

        return [
            'label' => $isId ? 'Editor' : 'Editor',
            'class' => 'bg-blue-50 text-blue-700',
            'style' => '',
            'textClass' => 'text-blue-700'
        ];
    };

    // Stage names for display
    $stageLabels = [
        1 => $isId ? 'Naskah' : 'Pre-Review',
        2 => $isId ? 'Ulasan' : 'Review',
        3 => $isId ? 'Penyuntingan' : 'Copyediting',
        4 => $isId ? 'Produksi' : 'Production',
    ];
    $stageLabel = $stageLabels[$stageId] ?? ($isId ? 'Tahap ' : 'Stage ') . $stageId;
@endphp

<div class="bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[24px] overflow-hidden" x-data="discussionPanel({
    stageId: {{ $stageId }},
    submissionId: '{{ $submission->id }}',
    journalSlug: '{{ $journal->slug }}',
    csrfToken: '{{ csrf_token() }}',
    currentUserId: '{{ $currentUser->id }}',
    uploadImageUrl: '{{ route('journal.discussion.upload-image', ['journal' => $journal->slug]) }}',
    uploadFileUrl: '{{ route('journal.discussion.upload-file', $journal->slug) }}',
    createUrl: '{{ route('journal.discussion.create', ['journal' => $journal->slug, 'submission' => $submission]) }}',
})">

    {{-- Panel Header --}}
    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
        <x-text.h2 class="text-gray-900">{{ $isId ? 'Diskusi ' . $stageLabel : $stageLabel . ' Discussions' }}</x-text.h2>
        <button @click="openAddModal()" type="button"
            class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors">
            <i class="fa-solid fa-plus mr-1.5 text-[14px]"></i>
            <x-text.body class="inline font-semibold text-inherit">{{ $isId ? 'Tambah Diskusi' : 'Add Discussion' }}</x-text.body>
        </button>
    </div>

    {{-- Discussion List --}}
    <div class="divide-y divide-gray-100">
        @forelse($stageDiscussions as $discussion)
            @php
                $isOpen = $discussion->is_open;
                $messageCount = $discussion->messages->count();
                $replyCount = $messageCount - 1;
                $discussionParticipants = $discussion->participants ?? collect();
                $unreadCount = $discussion->unreadMessagesCountForUser($currentUser->id);
                
                // Get last read timestamp for highlighting
                $userParticipantRecord = $discussion->participantRecords->where('user_id', $currentUser->id)->first();
                $lastReadAt = $userParticipantRecord ? $userParticipantRecord->last_read_at : null;
            @endphp

            <details class="group" x-data="discussionThread({
                discussionId: '{{ $discussion->id }}',
                isOpen: {{ $isOpen ? 'true' : 'false' }},
                replyUrl: '{{ route('journal.discussion.reply', ['journal' => $journal->slug, 'submission' => $submission, 'discussion' => $discussion->id]) }}',
                closeUrl: '{{ route('journal.discussion.close', ['journal' => $journal->slug, 'submission' => $submission, 'discussion' => $discussion->id]) }}',
                reopenUrl: '{{ route('journal.discussion.reopen', ['journal' => $journal->slug, 'submission' => $submission, 'discussion' => $discussion->id]) }}',
                uploadFileUrl: '{{ route('journal.discussion.upload-file', $journal->slug) }}',
                uploadImageUrl: '{{ route('journal.discussion.upload-image', ['journal' => $journal->slug]) }}',
                markAsReadUrl: '{{ route('journal.discussion.read', ['journal' => $journal->slug, 'submission' => $submission, 'discussion' => $discussion->id]) }}',
                csrfToken: '{{ csrf_token() }}',
            })" @toggle="if($el.open) markAsRead()">

                {{-- Summary Row --}}
                <summary
                    class="flex items-center justify-between px-6 py-4 cursor-pointer transition-colors {{ $unreadCount > 0 ? 'bg-blue-50/50 font-semibold border-l-4 border-blue-600' : 'bg-white font-normal hover:bg-gray-50' }}">
                    <div class="flex items-center gap-4">
                        <i
                            class="fa-regular fa-comments text-gray-400 group-open:text-blue-600 transition-colors"></i>
                        <div>
                            <div class="flex items-center gap-2">
                                <x-text.body class="text-gray-900 group-open:text-blue-600 font-semibold inline">
                                    {{ $discussion->subject }}
                                </x-text.body>
                                @if (!$isOpen)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                        <x-text.caption class="not-italic text-gray-600 flex items-center">
                                            <i class="fa-solid fa-lock mr-1 text-[10px]"></i>
                                            {{ $isId ? 'Ditutup' : 'Closed' }}
                                        </x-text.caption>
                                    </span>
                                @endif
                                @if ($unreadCount > 0)
                                    <span class="unread-badge inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 ml-2 font-bold">
                                        <x-text.caption class="not-italic text-blue-700 font-bold">
                                            {{ $unreadCount }} {{ $isId ? 'Baru' : 'New' }}
                                        </x-text.caption>
                                    </span>
                                @endif
                            </div>
                            <x-text.caption class="text-gray-500 block">
                                {{ $isId ? 'Dari' : 'From' }} {{ $discussion->user->name }} •
                                {{ $discussion->created_at->format('M d, Y') }}
                            </x-text.caption>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Participant Avatars --}}
                        <div class="flex -space-x-2">
                            @foreach ($discussionParticipants->take(3) as $participant)
                                <div class="w-6 h-6 rounded-full bg-blue-50 border-2 border-white flex items-center justify-center text-blue-600 font-bold"
                                    title="{{ $participant->name }}">
                                    <x-text.caption class="not-italic font-bold text-blue-600 leading-none" style="font-size: 10px;">
                                        {{ strtoupper(substr($participant->name, 0, 1)) }}
                                    </x-text.caption>
                                </div>
                            @endforeach
                            @if ($discussionParticipants->count() > 3)
                                <div
                                    class="w-6 h-6 rounded-full bg-slate-100 border-2 border-white flex items-center justify-center text-slate-600 font-bold">
                                    <x-text.caption class="not-italic font-bold text-slate-600 leading-none" style="font-size: 10px;">
                                        +{{ $discussionParticipants->count() - 3 }}
                                    </x-text.caption>
                                </div>
                            @endif
                        </div>

                        {{-- Reply Count Badge --}}
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full {{ $replyCount > 0 ? 'bg-blue-50 text-blue-700' : 'bg-slate-50 text-slate-600' }}">
                            <x-text.caption class="not-italic font-semibold {{ $replyCount > 0 ? 'text-blue-700' : 'text-slate-600' }}">
                                {{ $replyCount }} {{ $isId ? 'balasan' : ($replyCount === 1 ? 'reply' : 'replies') }}
                            </x-text.caption>
                        </span>

                        <i
                            class="fa-solid fa-chevron-down text-gray-400 transform group-open:rotate-180 transition-transform"></i>
                    </div>
                </summary>                {{-- Expanded Content --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{-- Participants Header --}}
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-150">
                        <div class="flex items-center gap-2">
                            <x-text.label class="text-slate-400">{{ $isId ? 'Partisipan:' : 'Participants:' }}</x-text.label>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($discussionParticipants as $participant)
                                    @php
                                        $pRoleData = $getParticipantRole($participant);
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full {{ $pRoleData['class'] }}" style="{{ $pRoleData['style'] }}">
                                        <x-text.caption class="not-italic font-medium {{ $pRoleData['textClass'] }}">
                                            {{ $participant->name }}
                                        </x-text.caption>
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Close/Reopen Button for Editors --}}
                        @if ($isEditor)
                            <div>
                                @if ($isOpen)
                                    <form
                                        action="{{ route('journal.discussion.close', ['journal' => $journal->slug, 'submission' => $submission, 'discussion' => $discussion->id]) }}"
                                        method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center text-slate-500 hover:text-red-600 transition-colors">
                                            <i class="fa-solid fa-lock mr-1 text-[12px]"></i>
                                            <x-text.caption class="not-italic font-medium text-inherit">{{ $isId ? 'Tutup Diskusi' : 'Close Discussion' }}</x-text.caption>
                                        </button>
                                    </form>
                                @else
                                    <form
                                        action="{{ route('journal.discussion.reopen', ['journal' => $journal->slug, 'submission' => $submission, 'discussion' => $discussion->id]) }}"
                                        method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center text-slate-500 hover:text-emerald-600 transition-colors">
                                            <i class="fa-solid fa-lock-open mr-1 text-[12px]"></i>
                                            <x-text.caption class="not-italic font-medium text-inherit">{{ $isId ? 'Buka Kembali' : 'Reopen' }}</x-text.caption>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Messages Thread --}}
                    <div class="space-y-3">
                        @foreach ($discussion->messages as $message)
                            @php
                                $isOwner = $message->user_id === $currentUser->id;
                                $msgRoleData = $getParticipantRole($message->user);
                                $messageRole = $msgRoleData['label'];
                                
                                // Highlight if not owner and (never read OR newer than last read)
                                $isNew = !$isOwner && (is_null($lastReadAt) || $message->created_at->gt($lastReadAt));
                            @endphp
                            <div class="flex gap-2">
                                <div class="flex-shrink-0 mt-0.5">
                                    <div
                                        class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold">
                                        {{ strtoupper(substr($message->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                </div>
                                <div class="{{ $isNew ? 'bg-blue-50/30 border-blue-200' : 'bg-white border-gray-200' }} py-2.5 px-3 rounded-lg shadow-sm border flex-1">
                                    {{-- Message Header --}}
                                    <div class="flex justify-between items-start mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <x-text.body class="font-semibold text-slate-900 inline">{{ $message->user->name ?? 'User' }}</x-text.body>
                                            <span
                                                class="inline-flex items-center px-1.5 py-0.5 rounded {{ $msgRoleData['class'] }}" style="{{ $msgRoleData['style'] }}">
                                                <x-text.caption class="not-italic font-medium text-[10px] {{ $msgRoleData['textClass'] }}">
                                                    {{ $messageRole }}
                                                </x-text.caption>
                                            </span>
                                            <x-text.caption class="text-slate-400">{{ $message->created_at->format('M d, Y \a\t H:i') }}</x-text.caption>
                                            @if ($message->created_at != $message->updated_at)
                                                <x-text.caption class="text-slate-400 italic text-[11px]">({{ $isId ? 'diubah' : 'edited' }})</x-text.caption>
                                            @endif
                                        </div>

                                        {{-- Aksi Edit: hanya untuk pesan milik user sendiri --}}
                                        @if ($isOwner)
                                            <button type="button" @click="startEdit('{{ $message->id }}')"
                                                class="inline-flex items-center text-slate-400 hover:text-blue-600 transition-colors text-xs font-medium ml-2">
                                                <i class="fa-solid fa-pen-to-square mr-1 text-[11px]"></i>
                                                <x-text.caption class="not-italic text-inherit font-medium hover:underline">{{ $isId ? 'Edit' : 'Edit' }}</x-text.caption>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Display Mode --}}
                                    <div x-show="editingMessageId !== '{{ $message->id }}'">
                                        {{-- Message Body --}}
                                        <div class="prose prose-sm text-gray-700 max-w-none">
                                            {!! $message->body !!}
                                        </div>

                                        {{-- Attachments --}}
                                        @if ($message->files && $message->files->count() > 0)
                                            <div class="mt-2.5 pt-2.5 border-t border-gray-100">
                                                <x-text.caption class="block font-medium text-slate-500 mb-1.5">
                                                    <i class="fa-solid fa-paperclip mr-1"></i>
                                                    {{ $isId ? 'Lampiran' : 'Attachments' }}
                                                </x-text.caption>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($message->files as $file)
                                                        <a href="{{ route('journal.discussion.file.download', ['journal' => $journal->slug, 'file' => $file->id]) }}"
                                                            class="inline-flex items-center px-2 py-1 bg-slate-50 hover:bg-slate-100 rounded text-slate-700 transition-colors">
                                                            <i class="fa-regular fa-file mr-1.5 text-[12px]"></i>
                                                            <x-text.caption class="not-italic text-slate-700">{{ Str::limit($file->original_name, 20) }}</x-text.caption>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Edit Mode (Hanya untuk owner) --}}
                                    @if ($isOwner)
                                        <div x-show="editingMessageId === '{{ $message->id }}'" x-cloak class="mt-2 pt-2 border-t border-gray-100">
                                            <form action="{{ route('journal.discussion.message.update', ['journal' => $journal->slug, 'submission' => $submission, 'discussion' => $discussion->id, 'message' => $message->id]) }}"
                                                method="POST" class="space-y-3" @submit="submittingEdit = true">
                                                @csrf
                                                @method('PUT')

                                                <div>
                                                    <textarea name="body" id="edit-editor-{{ $message->id }}" class="hidden">{!! $message->body !!}</textarea>
                                                </div>

                                                <div class="flex justify-end gap-2 pt-1">
                                                    <button type="button" @click="cancelEdit('{{ $message->id }}')"
                                                        class="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-800 rounded bg-slate-100 hover:bg-slate-200 transition-colors font-medium">
                                                        <x-text.caption class="not-italic font-medium text-inherit">{{ $isId ? 'Batal' : 'Cancel' }}</x-text.caption>
                                                    </button>
                                                    <button type="submit" :disabled="submittingEdit"
                                                        class="inline-flex items-center px-3 py-1.5 text-xs bg-blue-600 text-white font-medium rounded hover:bg-blue-700 disabled:opacity-50 transition-colors">
                                                        <i class="fa-solid fa-check mr-1 text-[11px]"></i>
                                                        <x-text.caption class="not-italic font-medium text-white inline">
                                                            <span x-text="submittingEdit ? '{{ $isId ? 'Menyimpan...' : 'Saving...' }}' : '{{ $isId ? 'Simpan' : 'Save' }}'"></span>
                                                        </x-text.caption>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Reply Form --}}
                    @if ($isOpen)
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <div x-show="!replyExpanded">
                                <button @click="replyExpanded = true; $nextTick(() => initReplyEditor())" type="button"
                                    class="w-full text-left px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-500 hover:bg-gray-50 hover:border-gray-300 transition-all">
                                    <i class="fa-regular fa-comment-dots mr-2"></i>
                                    {{ $isId ? 'Tulis balasan...' : 'Write a reply...' }}
                                </button>
                            </div>
 
                            <div x-show="replyExpanded" x-cloak
                                class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                <form
                                    action="{{ route('journal.discussion.reply', ['journal' => $journal->slug, 'submission' => $submission, 'discussion' => $discussion->id]) }}"
                                    method="POST" class="space-y-4" @submit="submitting = true">
                                    @csrf
 
                                    {{-- Hidden fields for files --}}
                                    <template x-for="(file, index) in replyFiles" :key="file.id">
                                        <div>
                                            <input type="hidden" :name="'attached_files[' + index + '][id]'"
                                                :value="file.id">
                                            <input type="hidden" :name="'attached_files[' + index + '][name]'"
                                                :value="file.name">
                                        </div>
                                    </template>
 
                                    {{-- Rich Text Editor --}}
                                    <div>
                                        <label class="block mb-1">
                                            <x-text.body class="font-medium text-slate-700">{{ $isId ? 'Balasan Anda' : 'Your Reply' }}</x-text.body>
                                        </label>
                                        <textarea name="body" :id="'reply-editor-' + discussionId" class="hidden"></textarea>
                                    </div>
 
                                    {{-- File Attachments --}}
                                    <div class="border-t border-gray-100 pt-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <x-text.caption class="not-italic font-medium text-slate-700">{{ $isId ? 'Lampiran' : 'Attachments' }}</x-text.caption>
                                            <label
                                                :class="replyIsUploading ? 'opacity-50 cursor-not-allowed pointer-events-none' : 'cursor-pointer'">
                                                <x-text.caption class="not-italic text-blue-600 font-semibold hover:underline flex items-center">
                                                    <i class="fa-solid fa-paperclip mr-1"></i>
                                                    {{ $isId ? 'Tambah File' : 'Add File' }}
                                                </x-text.caption>
                                                <input type="file" class="sr-only" :disabled="replyIsUploading"
                                                    @change="uploadReplyFile($event)">
                                            </label>
                                        </div>
 
                                        <!-- Progress Bar for uploading reply file -->
                                        <div x-show="replyIsUploading" class="mb-2 p-3 border rounded bg-slate-50 space-y-2">
                                            <div class="flex items-center justify-between">
                                                <x-text.caption class="text-slate-600 font-medium">{{ $isId ? 'Mengunggah file...' : 'Uploading file...' }}</x-text.caption>
                                                <x-text.caption class="text-blue-600 font-semibold" x-text="replyUploadProgress + '%'"></x-text.caption>
                                            </div>
                                            <div class="w-full bg-slate-200 rounded-full h-1.5">
                                                <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-150" :style="'width: ' + replyUploadProgress + '%'"></div>
                                            </div>
                                        </div>
                                        <ul class="space-y-1">
                                            <template x-for="file in replyFiles" :key="file.id">
                                                <li
                                                    class="flex items-center justify-between py-1.5 px-2 bg-slate-50 rounded border border-gray-100">
                                                    <div class="flex items-center gap-2">
                                                        <i class="fa-regular fa-file text-gray-400"></i>
                                                        <x-text.caption class="not-italic text-slate-700 truncate max-w-[200px]" x-text="file.name"></x-text.caption>
                                                    </div>
                                                    <button type="button"
                                                        @click="replyFiles = replyFiles.filter(f => f.id !== file.id)"
                                                        class="text-red-600 hover:text-red-700">
                                                        <i class="fa-solid fa-times text-[12px]"></i>
                                                    </button>
                                                </li>
                                            </template>
                                            <template x-if="replyFiles.length === 0">
                                                <li class="py-1"><x-text.caption class="text-slate-400">{{ $isId ? 'Tidak ada file yang dilampirkan.' : 'No files attached.' }}</x-text.caption></li>
                                            </template>
                                        </ul>
                                    </div>
 
                                    {{-- Action Buttons --}}
                                    <div class="flex justify-end gap-2 pt-2">
                                        <button type="button" @click="replyExpanded = false; resetReplyForm()"
                                            class="px-4 py-2 text-slate-600 hover:text-slate-800">
                                            <x-text.body class="font-medium text-inherit">{{ $isId ? 'Batal' : 'Cancel' }}</x-text.body>
                                        </button>
                                        <button type="submit" :disabled="submitting"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                                            <i class="fa-solid fa-paper-plane mr-2 text-[14px]"></i>
                                            <x-text.body class="font-medium text-white inline">
                                                <span x-text="submitting ? '{{ $isId ? 'Mengirim...' : 'Sending...' }}' : '{{ $isId ? 'Kirim Balasan' : 'Send Reply' }}'"></span>
                                            </x-text.body>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        {{-- Closed Notice --}}
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <div class="bg-slate-50 rounded-lg p-4 text-center">
                                <x-text.body class="text-slate-500 flex items-center justify-center">
                                    <i class="fa-solid fa-lock mr-2"></i>
                                    {{ $isId ? 'Diskusi ini telah ditutup.' : 'This discussion is closed.' }}
                                    @if ($discussion->closed_at)
                                        {{ $isId ? 'Ditutup' : 'Closed' }} {{ $discussion->closed_at->diffForHumans() }}.
                                    @endif
                                </x-text.body>
                            </div>
                        </div>
                    @endif
                </div>
            </details>
        @empty
            <div class="px-6 py-10 text-center">
                <i class="fa-regular fa-comments text-gray-300 text-4xl mb-3"></i>
                <x-text.body class="text-slate-500 mb-2">{{ $isId ? 'Belum ada diskusi di tahap ini.' : 'No discussions in this stage yet.' }}</x-text.body>
                <button @click="openAddModal()" type="button"
                    class="mt-3 inline-flex items-center text-blue-600 font-semibold hover:text-blue-800 transition-colors">
                    <i class="fa-solid fa-plus mr-1.5 text-[14px]"></i>
                    <x-text.body class="font-semibold text-inherit inline">{{ $isId ? 'Mulai diskusi' : 'Start a discussion' }}</x-text.body>
                </button>
            </div>
        @endforelse
    </div>

    {{-- ==================== ADD DISCUSSION MODAL ==================== --}}
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="add-discussion-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"
                @click="showAddModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showAddModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative inline-block align-bottom bg-white rounded-[24px] text-left overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-gray-150 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <x-text.h2 id="add-discussion-title" class="text-gray-900 flex items-center">
                            <i class="fa-regular fa-comments text-blue-600 mr-2"></i>
                            {{ $isId ? 'Tambah Diskusi ' . $stageLabel : 'Add ' . $stageLabel . ' Discussion' }}
                        </x-text.h2>
                        <button @click="showAddModal = false" type="button"
                            class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fa-solid fa-times text-[18px]"></i>
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <form :action="createUrl" method="POST" class="p-6 space-y-5" @submit="submittingNew = true">
                    @csrf
                    <input type="hidden" name="stage_id" value="{{ $stageId }}">

                    {{-- Hidden inputs for attached files --}}
                    <template x-for="(file, index) in newDiscussionFiles" :key="file.id">
                        <div>
                            <input type="hidden" :name="'attached_files[' + index + '][id]'" :value="file.id">
                            <input type="hidden" :name="'attached_files[' + index + '][name]'"
                                :value="file.name">
                        </div>
                    </template>

                    {{-- Subject --}}
                    <div>
                        <label for="subject-{{ $stageId }}"
                            class="block mb-1">
                            <x-text.body class="font-medium text-slate-700">{{ $isId ? 'Subjek' : 'Subject' }} <span class="text-red-600">*</span></x-text.body>
                        </label>
                        <input type="text" name="subject" id="subject-{{ $stageId }}" required
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="{{ $isId ? 'Deskripsi singkat mengenai topik diskusi' : 'Brief description of the discussion topic' }}">
                    </div>

                    {{-- Participants Selection --}}
                    <div>
                        <label class="block mb-2">
                            <x-text.body class="font-medium text-slate-700">{{ $isId ? 'Partisipan' : 'Participants' }} <span class="text-red-600">*</span></x-text.body>
                            <x-text.caption class="text-slate-500 ml-1">
                                {{ $isId ? '(Pilih siapa saja yang harus menjadi bagian dari diskusi ini)' : '(Select who should be part of this discussion)' }}
                            </x-text.caption>
                        </label>
                        <div
                            class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50">
                            {{-- Current User (Always included, grayed out) --}}
                            <label
                                class="flex items-center gap-3 p-2 rounded-lg bg-blue-50/50 border border-blue-200">
                                <input type="checkbox" name="participants[]" value="{{ $currentUser->id }}" checked
                                    disabled
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded cursor-not-allowed">
                                <input type="hidden" name="participants[]" value="{{ $currentUser->id }}">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <div
                                        class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <x-text.body class="font-medium text-slate-900 block truncate">{{ $currentUser->name }} {{ $isId ? '(Anda)' : '(You)' }}</x-text.body>
                                        <x-text.caption class="not-italic text-slate-500 block truncate">{{ $currentUser->email }}</x-text.caption>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                    <x-text.caption class="not-italic font-semibold text-blue-700">{{ $isId ? 'Pembuat' : 'Creator' }}</x-text.caption>
                                </span>
                            </label>

                            {{-- Other Participants --}}
                            @foreach ($participants->reject(fn($p) => $p->id === $currentUser->id) as $participant)
                                @php
                                    $pRoleData = $getParticipantRole($participant);
                                    $role = $pRoleData['label'];
                                    $isOtherParty =
                                        ($currentUser->id === $submission->user_id && $role !== ($isId ? 'Penulis' : 'Author')) ||
                                        ($currentUser->id !== $submission->user_id && $role === ($isId ? 'Penulis' : 'Author'));
                                @endphp
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-white cursor-pointer transition-colors">
                                    <input type="checkbox" name="participants[]" value="{{ $participant->id }}"
                                        {{ $isOtherParty ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <div
                                            class="w-8 h-8 rounded-full {{ $role === ($isId ? 'Penulis' : 'Author') ? 'bg-amber-100 text-amber-700' : ($role === 'Reviewer' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700') }} flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($participant->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <x-text.body class="font-medium text-slate-900 block truncate">{{ $participant->name }}</x-text.body>
                                            <x-text.caption class="not-italic text-slate-500 block truncate">{{ $participant->email }}</x-text.caption>
                                        </div>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full {{ $pRoleData['class'] }}" style="{{ $pRoleData['style'] }}">
                                        <x-text.caption class="not-italic font-semibold {{ $pRoleData['textClass'] }}">{{ $role }}</x-text.caption>
                                    </span>
                                </label>
                            @endforeach

                            @if ($participants->reject(fn($p) => $p->id === $currentUser->id)->isEmpty())
                                <p class="text-sm text-slate-500 italic text-center py-2">{{ $isId ? 'Tidak ada partisipan lain yang tersedia.' : 'No other participants available.' }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Message --}}
                    <div>
                        <label for="new-discussion-editor-{{ $stageId }}"
                            class="block mb-1">
                            <x-text.body class="font-medium text-slate-700">{{ $isId ? 'Pesan' : 'Message' }} <span class="text-red-600">*</span></x-text.body>
                        </label>
                        <div class="mt-1">
                            <textarea name="body" id="new-discussion-editor-{{ $stageId }}"></textarea>
                        </div>
                    </div>

                    {{-- File Attachments --}}
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <x-text.h2 class="text-gray-900">{{ $isId ? 'Lampiran' : 'Attachments' }}</x-text.h2>
                            <label
                                :class="newIsUploading ? 'opacity-50 cursor-not-allowed pointer-events-none' : 'cursor-pointer'"
                                class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-800 transition-colors">
                                <i class="fa-solid fa-paperclip mr-1.5 text-[14px]"></i>
                                <x-text.body class="font-semibold text-inherit inline">{{ $isId ? 'Lampirkan File' : 'Attach File' }}</x-text.body>
                                <input type="file" class="sr-only" :disabled="newIsUploading" @change="uploadNewDiscussionFile($event)">
                            </label>
                        </div>

                        <!-- Progress Bar for uploading new file -->
                        <div x-show="newIsUploading" class="mb-3 p-4 border rounded-lg bg-slate-50 space-y-2">
                            <div class="flex items-center justify-between">
                                <x-text.body class="text-slate-600 font-medium">{{ $isId ? 'Mengunggah file...' : 'Uploading file...' }}</x-text.body>
                                <x-text.body class="text-blue-600 font-semibold" x-text="newUploadProgress + '%'"></x-text.body>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-150" :style="'width: ' + newUploadProgress + '%'"></div>
                            </div>
                        </div>
                        <ul class="space-y-2">
                            <template x-for="file in newDiscussionFiles" :key="file.id">
                                <li
                                    class="flex items-center justify-between py-2 px-3 bg-slate-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-regular fa-file text-gray-400"></i>
                                        <x-text.body class="text-slate-700 inline" x-text="file.name"></x-text.body>
                                        <x-text.caption class="not-italic text-slate-500 ml-2 inline"
                                            x-text="(file.size / 1024).toFixed(0) + ' KB'"></x-text.caption>
                                    </div>
                                    <button type="button" class="text-red-600 hover:text-red-700"
                                        @click="newDiscussionFiles = newDiscussionFiles.filter(f => f.id !== file.id)">
                                        <i class="fa-solid fa-times text-[14px]"></i>
                                    </button>
                                </li>
                            </template>
                            <template x-if="newDiscussionFiles.length === 0">
                                <li class="py-2"><x-text.body class="text-slate-500 italic">{{ $isId ? 'Tidak ada file yang dilampirkan.' : 'No files attached.' }}</x-text.body></li>
                            </template>
                        </ul>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" @click="showAddModal = false"
                            class="px-4 py-2.5 text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                            <x-text.body class="font-medium text-inherit">{{ $isId ? 'Batal' : 'Cancel' }}</x-text.body>
                        </button>
                        <button type="submit" :disabled="submittingNew"
                            class="inline-flex items-center px-4 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                            <i class="fa-solid fa-paper-plane mr-2 text-[14px]"></i>
                            <x-text.body class="font-medium text-white inline">
                                <span x-text="submittingNew ? '{{ $isId ? 'Membuat...' : 'Creating...' }}' : '{{ $isId ? 'Buat Diskusi' : 'Create Discussion' }}'"></span>
                            </x-text.body>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function registerDiscussionComponents() {
        // Main Discussion Panel
        Alpine.data('discussionPanel', (config) => ({
            showAddModal: false,
            newDiscussionFiles: [],
            newEditorInstance: null,
            submittingNew: false,
            newUploadProgress: 0,
            newIsUploading: false,

            ...config,

            openAddModal() {
                this.showAddModal = true;
                this.newDiscussionFiles = [];
                this.$nextTick(() => this.initNewDiscussionEditor());
            },

            initNewDiscussionEditor() {
                const editorEl = document.querySelector(`#new-discussion-editor-${this.stageId}`);
                if (!editorEl || this.newEditorInstance) return;

                ClassicEditor
                    .create(editorEl, {
                        simpleUpload: {
                            uploadUrl: this.uploadImageUrl,
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken
                            }
                        }
                    })
                    .then(editor => {
                        this.newEditorInstance = editor;
                    })
                    .catch(err => console.error(err));
            },

            uploadNewDiscussionFile(event) {
                const file = event.target.files[0];
                if (!file) return;

                let formData = new FormData();
                formData.append('file', file);

                this.newIsUploading = true;
                this.newUploadProgress = 0;

                const xhr = new XMLHttpRequest();
                xhr.open('POST', this.uploadFileUrl);
                xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        this.newUploadProgress = Math.round((e.loaded / e.total) * 100);
                    }
                };

                xhr.onload = () => {
                    this.newIsUploading = false;
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            this.newDiscussionFiles.push(data);
                        } catch (e) {
                            alert('{{ $isId ? 'Unggah gagal: Respons tidak valid' : 'Upload failed: Invalid response' }}');
                        }
                    } else {
                        alert('{{ $isId ? 'Unggah gagal: ' : 'Upload failed: ' }}' + xhr.statusText);
                    }
                };

                xhr.onerror = () => {
                    this.newIsUploading = false;
                    alert('{{ $isId ? 'Unggah gagal' : 'Upload failed' }}');
                };

                xhr.send(formData);
                event.target.value = '';
            }
        }));

        // Discussion Thread (Reply Area)
        Alpine.data('discussionThread', (config) => ({
            replyExpanded: false,
            replyFiles: [],
            replyEditorInstance: null,
            submitting: false,
            replyUploadProgress: 0,
            replyIsUploading: false,
            editingMessageId: null,
            editEditorInstances: {},
            submittingEdit: false,

            ...config,

            startEdit(messageId) {
                this.editingMessageId = messageId;
                this.$nextTick(() => {
                    this.initEditEditor(messageId);
                });
            },

            initEditEditor(messageId) {
                const editorEl = document.querySelector(`#edit-editor-${messageId}`);
                if (!editorEl || this.editEditorInstances[messageId]) return;

                ClassicEditor
                    .create(editorEl, {
                        simpleUpload: {
                            uploadUrl: this.uploadImageUrl,
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken
                            }
                        }
                    })
                    .then(editor => {
                        this.editEditorInstances[messageId] = editor;
                    })
                    .catch(err => console.error(err));
            },

            cancelEdit(messageId) {
                this.editingMessageId = null;
                if (this.editEditorInstances[messageId]) {
                    this.editEditorInstances[messageId].destroy();
                    delete this.editEditorInstances[messageId];
                }
            },

            initReplyEditor() {
                const editorEl = document.querySelector(`#reply-editor-${this.discussionId}`);
                if (!editorEl || this.replyEditorInstance) return;

                ClassicEditor
                    .create(editorEl, {
                        simpleUpload: {
                            uploadUrl: this.uploadImageUrl,
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken
                            }
                        }
                    })
                    .then(editor => {
                        this.replyEditorInstance = editor;
                    })
                    .catch(err => console.error(err));
            },

            resetReplyForm() {
                this.replyFiles = [];
                if (this.replyEditorInstance) {
                    this.replyEditorInstance.setData('');
                }
            },

            uploadReplyFile(event) {
                const file = event.target.files[0];
                if (!file) return;

                let formData = new FormData();
                formData.append('file', file);

                this.replyIsUploading = true;
                this.replyUploadProgress = 0;

                const xhr = new XMLHttpRequest();
                xhr.open('POST', this.uploadFileUrl);
                xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        this.replyUploadProgress = Math.round((e.loaded / e.total) * 100);
                    }
                };

                xhr.onload = () => {
                    this.replyIsUploading = false;
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            this.replyFiles.push(data);
                        } catch (e) {
                            alert('{{ $isId ? 'Unggah gagal: Respons tidak valid' : 'Upload failed: Invalid response' }}');
                        }
                    } else {
                        alert('{{ $isId ? 'Unggah gagal: ' : 'Upload failed: ' }}' + xhr.statusText);
                    }
                };

                xhr.onerror = () => {
                    this.replyIsUploading = false;
                    alert('{{ $isId ? 'Unggah gagal' : 'Upload failed' }}');
                };

                xhr.send(formData);
                event.target.value = '';
            },

            markAsRead() {
                // Optimistic UI update can happen here if needed, 
                // but usually we rely on the expansion to trigger the call.
                // We don't necessarily need to reload the page or change UI state immediately 
                // unless we want to remove the 'Unread' badge live.
                
                fetch(this.markAsReadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json'
                    }
                }).then(response => {
                    if (response.ok) {
                        // Optional: trigger an event or update local state to remove badge
                        const summaryEl = this.$el.querySelector('summary');
                        if(summaryEl) {
                           summaryEl.classList.remove('bg-indigo-50', 'font-semibold', 'border-l-4', 'border-indigo-600');
                           summaryEl.classList.add('bg-white', 'font-normal');
                           const badge = summaryEl.querySelector('.unread-badge');
                           if(badge) badge.remove();
                        }
                    }
                });
            }
        }));
    }

    if (window.Alpine) {
        registerDiscussionComponents();
    } else {
        document.addEventListener('alpine:init', registerDiscussionComponents);
    }
</script>
