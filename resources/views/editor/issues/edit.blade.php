@php
    $journal = current_journal();
@endphp

@extends('layouts.app')

@section('title', 'Edit Issue: ' . $issue->identifier)

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                    <a href="{{ route('journal.issues.index', ['journal' => $journal->slug]) }}" class="hover:text-primary-600 transition-colors">Issues</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="{{ route('journal.issues.show', ['journal' => $journal->slug, 'issue' => $issue]) }}"
                        class="hover:text-primary-600 transition-colors">{{ $issue->identifier }}</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-gray-900">Edit</span>
                </div>

                <h1
                    class="text-3xl font-bold bg-gradient-to-r from-primary-600 to-primary-800 bg-clip-text text-transparent">
                    Edit Issue
                </h1>
                <p class="mt-2 text-gray-500">
                    Update issue details and cover image.
                </p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <form action="{{ route('journal.issues.update', ['journal' => $journal->slug, 'issue' => $issue]) }}" method="POST" enctype="multipart/form-data"
                    x-data="{
                        volume: '{{ old('volume', $issue->volume) }}',
                        number: '{{ old('number', $issue->number) }}',
                        year: '{{ old('year', $issue->year) }}',
                        title: '{{ old('title', $issue->title) }}',
                        showTitle: {{ old('show_title', $issue->show_title ?? false) ? 'true' : 'false' }},
                        urlPath: '{{ old('url_path', $issue->url_path) }}',
                        manualUrlPath: {{ old('url_path', $issue->url_path) ? 'true' : 'false' }},
                        generateSlug() {
                            if (this.manualUrlPath) return;
                            let slug = '';
                            if (this.showTitle && this.title) {
                                slug = this.title;
                            } else {
                                slug = 'v' + (this.volume || '1') + '-n' + (this.number || '1') + '-' + (this.year || new Date().getFullYear());
                            }
                            this.urlPath = slug.toLowerCase().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-').replace(/^-+|-+$/g, '');
                        }
                    }">
                    @csrf
                    @method('PUT')

                    <div class="p-6 space-y-6">
                        <!-- Issue Identification -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                Issue Identification
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                                <!-- Volume -->
                                <div>
                                    <label for="volume" class="block text-sm font-medium text-gray-700 mb-1">
                                        Volume <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" id="volume" name="volume"
                                        x-model="volume" @input="generateSlug"
                                        min="1" required
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-primary-500 transition-colors @error('volume') border-red-500 @enderror">
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
                                        x-model="number" @input="generateSlug"
                                        min="1" required
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-primary-500 transition-colors @error('number') border-red-500 @enderror">
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
                                        x-model="year" @input="generateSlug"
                                        min="2000" max="2100" required
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-primary-500 transition-colors @error('year') border-red-500 @enderror">
                                    @error('year')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- OJS 3.3 Display Toggles -->
                            <div class="flex flex-wrap gap-6 mb-4 bg-gray-50 rounded-lg p-4">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="show_volume" value="1"
                                        {{ old('show_volume', $issue->show_volume ?? true) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                    <span class="ml-2 text-sm font-medium text-gray-700">Show Volume</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="show_number" value="1"
                                        {{ old('show_number', $issue->show_number ?? true) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                    <span class="ml-2 text-sm font-medium text-gray-700">Show Number</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="show_year" value="1"
                                        {{ old('show_year', $issue->show_year ?? true) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                    <span class="ml-2 text-sm font-medium text-gray-700">Show Year</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="show_title" value="1"
                                        x-model="showTitle" @change="generateSlug"
                                        class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                    <span class="ml-2 text-sm font-medium text-gray-700">Show Title</span>
                                </label>
                            </div>
                        </div>

                        <!-- Title (Optional) -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
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
                                <input type="text" id="title" name="title"
                                    x-model="title" @input="generateSlug"
                                    placeholder="e.g., Special Issue on AI in Education"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-primary-500 transition-colors @error('title') border-red-500 @enderror">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Description
                            </h3>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Issue Description <span class="text-gray-400">(Optional)</span>
                                </label>
                                <textarea id="description" name="description" rows="6"
                                    placeholder="Enter detailed description for this issue..."
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-primary-500 transition-colors @error('description') border-red-500 @enderror">{{ old('description', $issue->description) }}</textarea>
                                <p class="mt-2 text-sm text-gray-500">
                                    <svg class="w-4 h-4 inline-block mr-1 text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    You can use HTML formatting. A rich text editor (TinyMCE/CKEditor) can be integrated
                                    here.
                                </p>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Cover Image -->
                        <div x-data="{ previewUrl: '{{ $issue->cover_path ? Storage::disk('public')->url($issue->cover_path) : '' }}' }">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
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
                                        Upload New Cover <span class="text-gray-400">(Optional)</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="file" id="cover" name="cover" accept="image/*"
                                            @change="previewUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : ''"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                                        <template x-if="previewUrl">
                                            <button type="button"
                                                @click="if(confirm('Delete cover image?')) {
                                                    fetch('{{ route('journal.issues.cover.delete', ['journal' => $journal->slug, 'issue' => $issue]) }}', {
                                                        method: 'DELETE',
                                                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'}
                                                    }).then(() => { previewUrl = ''; document.getElementById('cover').value = ''; });
                                                }"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-50 text-red-600 rounded-lg text-sm font-medium hover:bg-red-100 transition-colors">
                                                Remove
                                            </button>
                                        </template>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">
                                        Recommended size: 300x400 pixels. Max file size: 2MB. Leave empty to keep current
                                        cover.
                                    </p>
                                    @error('cover')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- URL Path -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                Custom URL
                            </h3>

                            <div class="mb-6">
                                <label for="url_path" class="block text-sm font-medium text-gray-700 mb-1">
                                    URL Path <span class="text-gray-400">(Optional)</span>
                                </label>
                                <input type="text" id="url_path" name="url_path" 
                                    x-model="urlPath" @input="manualUrlPath = true"
                                    placeholder="e.g., vol1-no2-2026"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-primary-500 transition-colors @error('url_path') border-red-500 @enderror">
                                <p class="mt-2 text-sm text-gray-500">
                                    An optional path to use in the URL instead of the issue ID.
                                </p>
                                @error('url_path')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- DOI Identifiers (OJS Compliance) -->
                        <div class="pt-6 border-t border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                Public Identifiers (DOI)
                            </h3>

                            @if($journal->doi_enabled && in_array('issues', $journal->doi_objects ?? []))
                                @if($issue->doi)
                                    <div class="mb-4 p-3 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Current DOI:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $issue->doi }}</span>
                                        </div>
                                        <a href="https://doi.org/{{ $issue->doi }}" target="_blank" class="text-xs text-indigo-600 hover:underline flex items-center gap-1">
                                            View
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        </a>
                                    </div>
                                @endif

                                <div>
                                    <label for="doi_suffix" class="block text-sm font-medium text-gray-700 mb-1">
                                        DOI Suffix <span class="text-gray-400">(Optional)</span>
                                    </label>
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-4 py-3 rounded-l-xl border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                            {{ $journal->doi_prefix }}/
                                        </span>
                                        <input type="text" id="doi_suffix" name="doi_suffix" 
                                            value="{{ old('doi_suffix', $issue->doi_suffix) }}"
                                            placeholder="e.g. v{{ $issue->volume }}i{{ $issue->number }}"
                                            class="flex-1 px-4 py-3 rounded-r-xl border border-gray-300 focus:border-primary-500 focus:ring-primary-500 transition-colors @error('doi_suffix') border-red-500 @enderror">
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">
                                        If empty, a DOI will be automatically generated upon publication if not already present.
                                    </p>
                                    @error('doi_suffix')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @else
                                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-sm text-gray-500">
                                    DOI registration for Issues is currently disabled in <a href="{{ route('journal.settings.tools.crossref.index', $journal->slug) }}" class="text-primary-600 hover:underline">Crossref settings</a>.
                                </div>
                            @endif
                        </div>

                        <!-- Publication Status -->
                        @if ($issue->is_published)
                            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-emerald-800">Published Issue</h4>
                                        <p class="text-sm text-emerald-700 mt-1">
                                            This issue is currently published. Changes will be reflected immediately on the
                                            public site.
                                            Published on {{ $issue->published_at->format('F d, Y') }}.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Form Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                        <a href="{{ route('journal.issues.show', ['journal' => $journal->slug, 'issue' => $issue]) }}"
                            class="px-6 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-medium shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:bg-indigo-700 transition-all">
                            Update Issue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/vendors/plugins/tinymce/tinymce.min.js') }}"></script>
<script>
    tinymce.init({
        selector: '#description',
        height: 350,
        menubar: false,
        plugins: 'lists link image table code autoresize',
        toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | table link image | code',
        branding: false,
        license_key: 'gpl',
        body_class: 'prose prose-slate max-w-none text-slate-700 leading-relaxed text-justify p-4',
        content_css: '{{ Vite::asset("resources/css/app.css") }}',
        images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', '{{ route('profile.upload.image') }}');
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

            xhr.upload.onprogress = (e) => {
                progress(e.loaded / e.total * 100);
            };

            xhr.onload = () => {
                if (xhr.status === 403) {
                    reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
                    return;
                }

                if (xhr.status < 200 || xhr.status >= 300) {
                    reject('HTTP Error: ' + xhr.status);
                    return;
                }

                const json = JSON.parse(xhr.responseText);

                if (!json || typeof json.location != 'string') {
                    reject('Invalid JSON: ' + xhr.responseText);
                    return;
                }

                resolve(json.location);
            };

            xhr.onerror = () => {
                reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
            };

            const formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());

            xhr.send(formData);
        })
    });
</script>
@endpush
