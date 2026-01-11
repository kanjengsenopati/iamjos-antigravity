@php
    $journal = $file->submission->journal;
@endphp

<x-app-layout :journal="$journal" :journalSlug="$journalSlug">
    <x-slot name="title">Preview: {{ $file->file_name }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <nav class="text-sm text-gray-500 mb-2">
                <a href="{{ route('journal.submissions.index', $journal->slug) }}"
                    class="hover:text-indigo-600">Submissions</a>
                <span class="mx-2">/</span>
                <a href="{{ route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $file->submission_id]) }}"
                    class="hover:text-indigo-600">Submission Detail</a>
                <span class="mx-2">/</span>
                <span class="text-gray-700">File Preview</span>
            </nav>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-indigo-100 rounded-lg">
                        <i class="fa-solid fa-file-lines text-indigo-600 text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ $file->file_name }}</h1>
                        <p class="text-sm text-gray-500">
                            {{ ucfirst($file->file_type) }} •
                            {{ number_format($file->file_size / 1024, 0) }} KB •
                            Uploaded {{ $file->created_at->format('M d, Y') }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ $downloadUrl }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                        <i class="fa-solid fa-download mr-2"></i>
                        Download
                    </a>
                    <a href="{{ route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $file->submission_id]) }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                        Back to Submission
                    </a>
                </div>
            </div>
        </div>

        {{-- Preview Frame --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-eye text-gray-400"></i>
                    <span class="text-sm font-medium text-gray-700">Document Preview</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <i class="fa-solid fa-info-circle"></i>
                    <span>Powered by Google Docs Viewer</span>
                </div>
            </div>

            {{-- Google Docs Viewer iframe --}}
            <div class="relative" style="height: 80vh;">
                {{-- Loading Placeholder --}}
                <div id="preview-loading" class="absolute inset-0 flex items-center justify-center bg-gray-50">
                    <div class="text-center">
                        <i class="fa-solid fa-spinner fa-spin text-indigo-500 text-3xl mb-3"></i>
                        <p class="text-gray-600 text-sm">Loading document preview...</p>
                    </div>
                </div>

                <iframe id="preview-frame" src="{{ $previewUrl }}" class="w-full h-full border-0"
                    onload="document.getElementById('preview-loading').style.display='none';" frameborder="0"
                    allowfullscreen>
                </iframe>
            </div>
        </div>

        {{-- Fallback Message --}}
        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <i class="fa-solid fa-exclamation-triangle text-yellow-500 mt-0.5 mr-3"></i>
                <div class="text-sm text-yellow-700">
                    <p class="font-medium">Can't see the preview?</p>
                    <p class="mt-1">
                        If the document doesn't load, please try
                        <a href="{{ $downloadUrl }}" class="font-medium underline">downloading</a>
                        the file directly or open it in a new tab.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
