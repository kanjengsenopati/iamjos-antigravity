@php
    $journal = current_journal();
@endphp

@extends('layouts.app')

@section('title', 'Create New Issue')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                    <a href="{{ route('journal.issues.index', ['journal' => $journal->slug]) }}"
                        class="hover:text-indigo-600 transition-colors">Issues</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-gray-900">Create New</span>
                </div>

                <h1 class="text-3xl font-bold text-indigo-700">
                    Create New Issue
                </h1>
                <p class="mt-2 text-gray-500">
                    Set up a new issue to schedule articles for publication.
                </p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <form action="{{ route('journal.issues.store', ['journal' => $journal->slug]) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="p-6 space-y-6">
                        <!-- Issue Identification -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                Issue Identification
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Volume -->
                                <div>
                                    <label for="volume" class="block text-sm font-medium text-gray-700 mb-1">
                                        Volume <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" id="volume" name="volume"
                                        value="{{ old('volume', $suggestedVolume) }}" min="1" required
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition-colors @error('volume') border-red-500 @enderror">
                                    @error('volume')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Number -->
                                <div>
                                    <label for="number" class="block text-sm font-medium text-gray-700 mb-1">
                                        Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" id="number" name="number"
                                        value="{{ old('number', $suggestedNumber) }}" min="1" required
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition-colors @error('number') border-red-500 @enderror">
                                    @error('number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Year -->
                                <div>
                                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">
                                        Year <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" id="year" name="year"
                                        value="{{ old('year', $suggestedYear) }}" min="2000" max="2100" required
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition-colors @error('year') border-red-500 @enderror">
                                    @error('year')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <p class="mt-3 text-sm text-gray-500 bg-gray-50 rounded-lg p-3">
                                <svg class="w-4 h-4 inline-block mr-1 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                This will create issue: <strong>Vol. {{ $suggestedVolume }} No. {{ $suggestedNumber }},
                                    {{ $suggestedYear }}</strong>
                            </p>
                        </div>

                        <!-- Title (Optional) -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h7" />
                                </svg>
                                Issue Title
                            </h3>

                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                    Title <span class="text-gray-400">(Optional - for special/themed issues)</span>
                                </label>
                                <input type="text" id="title" name="title" value="{{ old('title') }}"
                                    placeholder="e.g., Special Issue on AI in Education"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition-colors @error('title') border-red-500 @enderror">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Cover Image -->
                        <div x-data="{ previewUrl: null }">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Cover Image
                            </h3>

                            <div class="flex items-start gap-6">
                                <!-- Preview -->
                                <div
                                    class="w-32 h-44 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl overflow-hidden flex-shrink-0">
                                    <template x-if="previewUrl">
                                        <img :src="previewUrl" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!previewUrl">
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </template>
                                </div>

                                <!-- Upload -->
                                <div class="flex-1">
                                    <label for="cover" class="block text-sm font-medium text-gray-700 mb-1">
                                        Upload Cover <span class="text-gray-400">(Optional)</span>
                                    </label>
                                    <div class="relative">
                                        <input type="file" id="cover" name="cover" accept="image/*"
                                            @change="previewUrl = URL.createObjectURL($event.target.files[0])"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">
                                        Recommended size: 300x400 pixels. Max file size: 2MB.
                                    </p>
                                    @error('cover')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                        <a href="{{ route('journal.issues.index', ['journal' => $journal->slug]) }}"
                            class="px-6 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-medium shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:bg-indigo-700 transition-all">
                            Create Issue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
