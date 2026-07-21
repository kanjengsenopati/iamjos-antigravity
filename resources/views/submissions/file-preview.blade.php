@php
    $journal = $file->submission->journal;
@endphp

<x-app-layout :journal="$journal" :journalSlug="$journalSlug">
    <x-slot name="title">Preview: {{ $file->file_name }}</x-slot>

    <div class="max-w-7xl mx-auto px-5 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <nav class="text-sm text-gray-500 mb-2">
                <a href="{{ route('journal.submissions.index', $journal->slug) }}"
                    class="hover:text-blue-600 transition-colors">Submissions</a>
                <span class="mx-2 text-gray-300">/</span>
                <a href="{{ route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $file->submission->url_slug]) }}"
                    class="hover:text-blue-600 transition-colors">Submission Detail</a>
                <span class="mx-2 text-gray-300">/</span>
                <span class="text-gray-700">File Preview</span>
            </nav>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0 flex-1">
                    <div class="p-3 bg-blue-50 rounded-xl flex-shrink-0">
                        <i class="fa-solid fa-file-lines text-blue-600 text-xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <x-text.h1 class="break-all">{{ $file->file_name }}</x-text.h1>
                        <x-text.caption class="not-italic mt-1 block">
                            {{ ucfirst($file->file_type) }} •
                            {{ number_format($file->file_size / 1024, 0) }} KB •
                            Uploaded {{ $file->created_at->format('M d, Y') }}
                        </x-text.caption>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0 whitespace-nowrap">
                    <a href="{{ $downloadUrl }}"
                        class="inline-flex items-center px-3 py-1.5 border border-slate-200 shadow-sm text-xs font-semibold rounded-lg text-slate-700 bg-white hover:bg-slate-50 focus:outline-none whitespace-nowrap transition-colors">
                        <i class="fa-solid fa-download mr-1.5 text-slate-400"></i>
                        Download
                    </a>
                    <a href="{{ route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $file->submission->url_slug]) }}"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none whitespace-nowrap transition-colors">
                        <i class="fa-solid fa-arrow-left mr-1.5"></i>
                        Back to Submission
                    </a>
                </div>
            </div>
        </div>

        {{-- Preview Frame --}}
        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            <div class="bg-slate-50/50 px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-eye text-slate-400"></i>
                    <x-text.h2>Document Preview</x-text.h2>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                    <x-text.caption class="not-italic text-slate-500">Self-hosted viewer &mdash; files never leave the server</x-text.caption>
                </div>
            </div>

            {{-- PDF.js Self-hosted Viewer --}}
            <div style="height: 80vh;">
                <x-pdf-viewer
                    :fileUrl="$previewUrl"
                    :fileName="$file->file_name"
                    :downloadUrl="$downloadUrl"
                    height="100%"
                />
            </div>
        </div>

        {{-- Fallback Message --}}
        <div class="mt-4 bg-blue-50 border border-blue-100 rounded-xl p-4">
            <div class="flex">
                <i class="fa-solid fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                <div class="text-sm text-blue-700">
                    <p class="font-medium">Having trouble viewing?</p>
                    <p class="mt-1">
                        If the document doesn't load, please try
                        <a href="{{ $downloadUrl }}" class="font-medium underline hover:text-blue-800">downloading</a>
                        the file directly.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
