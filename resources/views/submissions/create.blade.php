@extends('layouts.app')

@section('title', ($isId ? 'Pengajuan Baru - ' : 'New Submission - ') . $journal->name)

@section('content')
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* CKEditor Custom Styling */
        .ck-editor__editable {
            min-height: 250px !important;
        }

        .ck-editor__editable:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
        }
    </style>

    <!-- CKEditor 5 CDN (Free, No API Key Required) -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>

    <div class="max-w-5xl mx-auto py-8">

        <div x-data="submissionWizard()" x-cloak class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">

            <!-- Header / Progress -->
            <div class="bg-gray-50 border-b border-gray-200 px-8 py-4">
                <h1 class="text-xl font-bold text-gray-900 mb-4">{{ $isId ? 'Kirimkan Naskah' : 'Submit an Article' }}</h1>

                <!-- Progress Bar -->
                <div class="relative">
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                        <div :style="'width: ' + ((step - 1) / 3 * 100) + '%'"
                            class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-600 transition-all duration-500">
                        </div>
                    </div>
                    <div class="flex justify-between text-xs font-semibold text-gray-500">
                        <span :class="{ 'text-indigo-700': step >= 1 }">1. {{ $isId ? 'Mulai' : 'Start' }}</span>
                        <span :class="{ 'text-indigo-700': step >= 2 }">2. {{ $isId ? 'Unggah' : 'Upload' }}</span>
                        <span :class="{ 'text-indigo-700': step >= 3 }">3. {{ $isId ? 'Metadata' : 'Metadata' }}</span>
                        <span :class="{ 'text-indigo-700': step >= 4 }">4. {{ $isId ? 'Konfirmasi' : 'Confirmation' }}</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('journal.submissions.store', ['journal' => $journal->slug]) }}" method="POST"
                enctype="multipart/form-data" id="submissionForm" x-ref="form" novalidate>
                @csrf
                <input type="hidden" name="draft_id" value="{{ $draft->id ?? '' }}">

                <!-- Hidden File Input (ALWAYS in DOM for form submission) -->
                <input type="file" name="manuscript" x-ref="fileInput" class="hidden" accept=".doc,.docx,.pdf"
                    @change="handleFileChange($event.target.files[0])">

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="mx-8 mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                            <h4 class="text-sm font-bold text-red-800">{{ $isId ? 'Harap perbaiki kesalahan berikut:' : 'Please correct the following errors:' }}</h4>
                        </div>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Validation Errors (Frontend) -->
                <div x-show="validationErrors.length > 0" x-cloak
                    class="mx-8 mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                        <h4 class="text-sm font-bold text-red-800">{{ $isId ? 'Harap perbaiki kesalahan berikut:' : 'Please correct the following errors:' }}</h4>
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        <template x-for="error in validationErrors" :key="error">
                            <li x-text="error"></li>
                        </template>
                    </ul>
                </div>

                <!-- STEP 1: START -->
                <div x-show="step === 1" x-transition class="p-8">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $isId ? 'Persyaratan Pengajuan' : 'Submission Requirements' }}</h2>
                    <p class="text-sm text-gray-500 mb-6">{{ $isId ? 'Buat pengajuan baru ke' : 'Create a new submission to the' }} <span
                            class="font-bold">{{ $journal->name }}</span>. {{ $isId ? 'Harap periksa persyaratan berikut sebelum melanjutkan.' : 'Please check the following requirements before proceeding.' }}</p>

                    <!-- Section Selection -->
                    <div class="mb-8 max-w-md">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ $isId ? 'Bagian' : 'Section' }} <span
                                class="text-red-500">*</span></label>
                        <select name="section_id" x-model="section_id"
                            class="block w-full rounded-md border border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3" required>
                            <option value="" class="text-gray-500">{{ $isId ? 'Pilih bagian...' : 'Select a section...' }}</option>
                            @if ($sections->count() > 0)
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}" class="text-black"
                                        {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                        {{ $section->name }} {{ $section->is_active ? '' : ($isId ? '(Nonaktif)' : '(Inactive)') }}
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>{{ $isId ? 'Tidak ada bagian aktif yang ditemukan.' : 'No active sections found.' }}</option>
                            @endif
                        </select>
                    </div>

                    <!-- Checklist -->
                    <div class="space-y-4 mb-8">
                        <label class="block text-sm font-medium text-gray-700">{{ $isId ? 'Daftar Periksa Pengajuan' : 'Submission Checklist' }}</label>
                        @if ($submissionChecklists->isNotEmpty())
                            @foreach ($submissionChecklists as $item)
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="requirements[]" value="{{ $item->id }}"
                                            x-model="requirements"
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label class="font-medium text-gray-700">{{ $item->content }}</label>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-500 italic bg-gray-50 p-3 rounded">{{ $isId ? 'Tidak ada persyaratan khusus yang dicentang.' : 'No specific requirements checked.' }}
                            </p>
                        @endif
                    </div>

                    <!-- Copyright Notice -->
                    @if ($journal->license_terms)
                        <div class="bg-blue-50 p-4 rounded-lg mb-6 border border-blue-100">
                            <h4 class="text-sm font-bold text-blue-800 mb-2">{{ $isId ? 'Pernyataan Hak Cipta' : 'Copyright Notice' }}</h4>
                            <p class="text-xs text-blue-700 whitespace-pre-line">{{ $journal->license_terms }}</p>
                            <label class="flex items-center mt-3">
                                <input type="checkbox" required
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                <span class="ml-2 text-xs text-blue-700 font-medium">{{ $isId ? 'Saya menyetujui ketentuan hak cipta.' : 'I agree to the copyright terms.' }}</span>
                            </label>
                        </div>
                    @endif

                    <!-- Comments for the Editor -->
                    <div class="border-t border-gray-200 pt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ $isId ? 'Komentar untuk Editor (Opsional)' : 'Comments for the Editor (Optional)' }}</label>
                        <div id="commentsEditor" class="rounded-lg border border-gray-300">{!! old('comments_for_editor', ($draft && isset($draft->metadata['comments_for_editor'])) ? $draft->metadata['comments_for_editor'] : '') !!}</div>
                        <textarea name="comments_for_editor" id="commentsHidden" class="hidden" style="display: none;">{{ old('comments_for_editor', ($draft && isset($draft->metadata['comments_for_editor'])) ? $draft->metadata['comments_for_editor'] : '') }}</textarea>
                        <p class="text-xs text-gray-500 mt-2">{{ $isId ? 'Komentar ini hanya akan terlihat oleh tim editorial dan akan ditambahkan sebagai diskusi.' : 'These comments will be visible only to the editorial team and will be added as a discussion.' }}</p>
                    </div>
                </div>

                <!-- STEP 2: UPLOAD SUBMISSION -->
                <div x-show="step === 2" x-transition class="p-8">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $isId ? 'Unggah Pengajuan' : 'Upload Submission' }}</h2>
                    <p class="text-sm text-gray-500 mb-6">{{ $isId ? 'Unggah berkas naskah Anda. Format yang diperbolehkan: DOC, DOCX, PDF.' : 'Upload your manuscript file. Allowed formats: DOC, DOCX, PDF.' }}</p>

                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-10 flex flex-col items-center justify-center transition-colors bg-gray-50 hover:bg-gray-100 hover:border-indigo-400 cursor-pointer"
                        @click="$refs.fileInput.click()"
                        @dragover.prevent="$el.classList.add('border-indigo-500', 'bg-indigo-50')"
                        @dragleave.prevent="$el.classList.remove('border-indigo-500', 'bg-indigo-50')"
                        @drop.prevent="$el.classList.remove('border-indigo-500', 'bg-indigo-50'); $refs.fileInput.files = $event.dataTransfer.files; handleFileChange($event.dataTransfer.files[0])">

                        <div x-show="!fileName" class="text-center pointer-events-none">
                            <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-400 mb-3"></i>
                            <p class="text-sm font-medium text-gray-900">{{ $isId ? 'Seret dan letakkan berkas Anda di sini' : 'Drag and drop your file here' }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $isId ? 'atau klik untuk memilih berkas' : 'or click to browse' }}</p>
                        </div>

                        <div x-show="fileName" class="text-center w-full pointer-events-none">
                            <div class="flex items-center justify-center gap-3 mb-2">
                                <i class="fa-regular fa-file-word text-3xl text-indigo-600"></i>
                                <div class="text-left">
                                    <p class="text-sm font-medium text-gray-900" x-text="fileName"></p>
                                    <p class="text-xs text-gray-500" x-text="fileSize"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="fileName" class="mt-3 text-center">
                        <button type="button" @click.stop="clearFile"
                            class="text-xs text-red-600 hover:text-red-800 font-medium">
                            <i class="fa-solid fa-times mr-1"></i> {{ $isId ? 'Hapus Berkas' : 'Remove File' }}
                        </button>
                    </div>
                </div>

                <!-- STEP 3: ENTER METADATA -->
                <div x-show="step === 3" x-transition class="p-8">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $isId ? 'Masukkan Metadata' : 'Enter Metadata' }}</h2>

                    <!-- Title & Abstract -->
                    <div class="space-y-6 mb-8">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isId ? 'Judul' : 'Title' }} <span
                                    class="text-red-500">*</span></label>
                            <textarea name="title" x-model="title" rows="2"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="{{ $isId ? 'Judul Artikel' : 'Article Title' }}" required></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isId ? 'Subjudul' : 'Subtitle' }}</label>
                            <input type="text" name="subtitle" x-model="subtitle"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="{{ $isId ? 'Subjudul opsional' : 'Optional subtitle' }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isId ? 'Abstrak' : 'Abstract' }} <span
                                    class="text-red-500">*</span></label>
                            <div id="abstractEditor" class="rounded-lg border border-gray-300">{!! old('abstract', $draft->abstract ?? '') !!}</div>
                            <textarea name="abstract" id="abstractHidden" class="hidden" style="display: none;">{{ old('abstract', $draft->abstract ?? '') }}</textarea>
                        </div>
                        <div x-data="keywordInputCustom({{ json_encode(old('keywords', [])) }})" class="relative">
                            <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                {{ $isId ? 'Kata Kunci' : 'Keywords' }}
                                <i class="fa-solid fa-circle-question text-gray-400 cursor-pointer ml-1.5" 
                                   title="{{ $isId ? 'Tekan Enter atau koma untuk menambahkan kata kunci. Mulai mengetik untuk melihat saran.' : 'Press Enter or comma to add keywords. Start typing to see suggestions.' }}"></i>
                                <span class="text-xs text-gray-500 font-normal ml-2">{{ $isId ? '(Gunakan koma sebagai pemisah kata kunci)' : '(Use comma as separator)' }}</span>
                            </label>
                            <input type="text" 
                                x-model="newTag"
                                @keydown.enter.prevent="handleEnter()"
                                @keydown.comma.prevent="addTag()"
                                @input="fetchSuggestions()"
                                @keydown.arrow-down.prevent="highlightDown()"
                                @keydown.arrow-up.prevent="highlightUp()"
                                @keydown.escape.prevent="showSuggestions = false"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                placeholder="{{ $isId ? 'Ketik kata kunci dan tekan Enter' : 'Type keyword and press Enter' }}">
                            
                            <div x-show="showSuggestions && suggestions.length > 0" 
                                 @click.away="showSuggestions = false" 
                                 class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                                 x-cloak>
                                <template x-for="(suggestion, i) in suggestions" :key="suggestion">
                                    <div @click="selectSuggestion(suggestion)" 
                                         :class="{'bg-indigo-50 text-indigo-900': i === highlightedIndex, 'text-gray-700': i !== highlightedIndex}"
                                         class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 text-sm"
                                         x-text="suggestion">
                                    </div>
                                </template>
                            </div>

                            <template x-for="(tag, index) in tags" :key="tag">
                                <input type="hidden" :name="'keywords[' + index + ']'" :value="tag">
                            </template>

                            <div class="flex flex-wrap gap-2 mt-3">
                                <template x-for="(tag, index) in tags" :key="tag">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-gray-200 rounded-full text-xs text-gray-700 font-medium shadow-sm transition hover:border-gray-300">
                                        <span x-text="tag"></span>
                                        <button type="button" @click="removeTag(index)" 
                                            class="ml-1 text-red-500 hover:text-red-700 font-bold focus:outline-none text-sm leading-none">&times;</button>
                                    </span>
                                </template>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isId ? 'Referensi' : 'References' }}</label>
                            <textarea name="references" x-model="references" rows="5"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="{{ $isId ? 'Tempel referensi Anda di sini...' : 'Paste your references here...' }}"></textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ $isId ? 'Berikan daftar referensi untuk karya Anda.' : 'Provide a list of references for your work.' }}</p>
                        </div>
                    </div>

                    <!-- Contributors -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-medium text-gray-900">{{ $isId ? 'Daftar Kontributor' : 'List of Contributors' }}</label>
                            <button type="button" @click="addAuthor"
                                class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-md font-medium transition">
                                <i class="fa-solid fa-plus mr-1"></i> {{ $isId ? 'Tambah Kontributor' : 'Add Contributor' }}
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(author, index) in authors" :key="index">
                                <div class="bg-gray-50 rounded-[24px] p-5 border border-gray-200 relative group shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-300"
                                    :class="draggedIndex === index ? 'opacity-40 border-indigo-300 border-dashed bg-indigo-50/30' : 'hover:shadow-md hover:border-slate-300'"
                                    :draggable="dragEnabledIndex === index"
                                    @dragstart="dragStart($event, index)"
                                    @dragover.prevent
                                    @dragenter="dragEnter(index)"
                                    @dragend="dragEnd">
                                    
                                    <!-- Top-Right Action Cluster: Grip handle and Delete button -->
                                    <div class="absolute top-5 right-5 flex items-center gap-2">
                                        <!-- Grip Handle -->
                                        <div class="cursor-grab active:cursor-grabbing p-1.5 hover:bg-gray-100 rounded-lg transition"
                                            @mousedown="dragEnabledIndex = index"
                                            @mouseup="dragEnabledIndex = null"
                                            @mouseleave="dragEnabledIndex = null"
                                            title="{{ $isId ? 'Geser untuk mengurutkan' : 'Drag to reorder' }}">
                                            <i class="fa-solid fa-grip-vertical text-slate-400 hover:text-indigo-600 text-[18px]"></i>
                                        </div>
                                        
                                        <!-- Delete Button -->
                                        <button type="button" @click="removeAuthor(index)" x-show="authors.length > 1"
                                            class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                            title="{{ $isId ? 'Hapus Kontributor' : 'Delete Contributor' }}">
                                            <i class="fa-solid fa-trash-can text-[18px]"></i>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pr-16">
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">{{ $isId ? 'Nama Depan' : 'First Name' }}</label>
                                            <input type="text" :name="'authors[' + index + '][first_name]'"
                                                x-model="author.first_name"
                                                class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                required>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">{{ $isId ? 'Nama Belakang' : 'Last Name' }}</label>
                                            <input type="text" :name="'authors[' + index + '][last_name]'"
                                                x-model="author.last_name"
                                                class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                required>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">{{ $isId ? 'Surel (Email)' : 'Email' }}</label>
                                            <input type="email" :name="'authors[' + index + '][email]'"
                                                x-model="author.email"
                                                class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                required>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">{{ $isId ? 'Afiliasi' : 'Affiliation' }}</label>
                                            <input type="text" :name="'authors[' + index + '][affiliation]'"
                                                x-model="author.affiliation"
                                                class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">{{ $isId ? 'Negara' : 'Country' }}</label>
                                            <select :name="'authors[' + index + '][country]'"
                                                x-model="author.country"
                                                class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                                <option value="">{{ $isId ? 'Pilih Negara...' : 'Select Country...' }}</option>
                                                @foreach (config('countries', []) as $code => $name)
                                                    <option value="{{ $code }}">{{ $name }}</option>
                                                @endforeach
                                                @if (empty(config('countries')))
                                                    @php
                                                        $fallbacks = [
                                                            'ID' => 'Indonesia',
                                                            'MY' => 'Malaysia',
                                                            'SG' => 'Singapore',
                                                            'TH' => 'Thailand',
                                                            'VN' => 'Vietnam',
                                                            'PH' => 'Philippines',
                                                            'AU' => 'Australia',
                                                            'US' => 'United States',
                                                            'OTHER' => 'Other',
                                                        ];
                                                    @endphp
                                                    @foreach ($fallbacks as $code => $name)
                                                        <option value="{{ $code }}">{{ $name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" name="primary_contact" :value="index"
                                                x-model="primaryContactIndex"
                                                class="text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-2 text-xs font-semibold text-gray-600">{{ $isId ? 'Kontak Utama' : 'Primary Contact' }}</span>
                                        </label>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: CONFIRMATION -->
                <div x-show="step === 4" x-transition class="p-8">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $isId ? 'Konfirmasi Pengajuan' : 'Confirm Submission' }}</h2>
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-5 mb-6">
                        <p class="text-sm text-indigo-800">{{ $isId ? 'Harap tinjau data Anda sebelum menyelesaikan. Setelah dikirim, Anda mungkin tidak dapat langsung mengubah rincian tertentu.' : 'Please review your data before finishing. Once submitted, you may not be able to edit specific details immediately.' }}</p>
                    </div>

                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">{{ $isId ? 'Judul' : 'Title' }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold" x-text="title"></dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">{{ $isId ? 'Abstrak' : 'Abstract' }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 italic prose prose-sm max-w-none" x-html="abstractHtml">
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ $isId ? 'Berkas' : 'File' }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 flex items-center gap-2">
                                <i class="fa-regular fa-file-lines"></i>
                                <span x-text="fileName || '{{ $isId ? 'Tidak ada berkas yang dipilih' : 'No file selected' }}'"></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ $isId ? 'Kontributor' : 'Contributors' }}</dt>
                            <dd class="mt-1 text-sm text-gray-900" x-text="authors.length + ' ' + ('{{ $isId ? 'penulis' : 'author(s)' }}')"></dd>
                        </div>
                        <div x-show="references" class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">{{ $isId ? 'Referensi' : 'References' }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line break-words"
                                x-text="references"></dd>
                        </div>
                    </dl>


                </div>

                <!-- Footer Buttons -->
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 flex justify-between items-center rounded-b-xl">
                    <button type="button" x-show="step > 1" @click="step--"
                        class="text-gray-600 hover:text-gray-900 font-medium text-sm">
                        <i class="fa-solid fa-arrow-left mr-1"></i> {{ $isId ? 'Kembali' : 'Back' }}
                    </button>
                    <a href="{{ route('journal.submissions.index', ['journal' => $journal->slug]) }}" 
                       x-show="step === 1"
                       class="text-gray-600 hover:text-gray-900 font-medium text-sm flex items-center gap-1">
                        <i class="fa-solid fa-arrow-left mr-1"></i> {{ $isId ? 'Kembali ke Daftar Pengajuan' : 'Back To Submission' }}
                    </a>

                    <div class="flex items-center gap-3">
                        <button type="button" @click="cancelSubmission()"
                            class="px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            {{ $isId ? 'Batal' : 'Cancel' }}
                        </button>

                        <button type="button" x-show="step < 4" @click="nextStep()"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm font-medium shadow-sm transition">
                            {{ $isId ? 'Simpan & Lanjutkan' : 'Save & Continue' }} <i class="fa-solid fa-arrow-right ml-1"></i>
                        </button>

                        <button type="button" x-show="step === 4" @click="submitForm()" :disabled="isSubmitting"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg text-sm font-medium shadow-sm transition flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">{{ $isId ? 'Selesaikan Pengajuan' : 'Finish Submission' }} <i class="fa-solid fa-check ml-1"></i></span>
                            <span x-show="isSubmitting"><i class="fa-solid fa-spinner fa-spin mr-2"></i> {{ $isId ? 'Memproses...' : 'Processing...' }}</span>
                        </button>
                    </div>
                </div>

            </form>

            <form id="cancelSubmissionForm" action="{{ $draft ? route('journal.submissions.destroy', ['journal' => $journal->slug, 'submission' => $draft->id]) : '#' }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <script>
        // CKEditor Instances
        let editorInstance = null;
        let commentsEditorInstance = null;

        // Custom Upload Adapter for CKEditor
        class CustomUploadAdapter {
            constructor(loader) {
                this.loader = loader;
            }

            upload() {
                return this.loader.file.then(file => new Promise((resolve, reject) => {
                    const formData = new FormData();
                    formData.append('file', file);
                    fetch('{{ route('journal.upload.image', ['journal' => $journal->slug]) }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content
                            }
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.location) {
                                resolve({
                                    default: result.location
                                });
                            } else {
                                reject(result.error || 'Upload failed');
                            }
                        })
                        .catch(error => reject(error));
                }));
            }

            abort() {}
        }

        function CustomUploadAdapterPlugin(editor) {
            editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                return new CustomUploadAdapter(loader);
            };
        }

        // Initialize CKEditor
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Abstract Editor
            ClassicEditor
                .create(document.querySelector('#abstractEditor'), {
                    extraPlugins: [CustomUploadAdapterPlugin],
                    toolbar: {
                        items: [
                            'heading', '|',
                            'bold', 'italic', '|',
                            'bulletedList', 'numberedList', '|',
                            'outdent', 'indent', '|',
                            'link', 'imageUpload', 'blockQuote', 'insertTable', '|',
                            'undo', 'redo'
                        ]
                    },
                    image: {
                        toolbar: ['imageTextAlternative', 'imageStyle:inline', 'imageStyle:block',
                            'imageStyle:side'
                        ]
                    },
                    table: {
                        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                    },
                    placeholder: '{{ $isId ? "Masukkan abstrak Anda di sini..." : "Enter your abstract here..." }}'
                })
                .then(editor => {
                    editorInstance = editor;

                    // Sync content to hidden textarea on change
                    editor.model.document.on('change:data', () => {
                        document.querySelector('#abstractHidden').value = editor.getData();
                    });
                })
                .catch(error => {
                    console.error('CKEditor initialization failed:', error);
                });

            // Initialize Comments Editor
            ClassicEditor
                .create(document.querySelector('#commentsEditor'), {
                    extraPlugins: [CustomUploadAdapterPlugin],
                    toolbar: {
                        items: [
                            'heading', '|',
                            'bold', 'italic', '|',
                            'bulletedList', 'numberedList', '|',
                            'link', 'blockQuote', '|',
                            'undo', 'redo'
                        ]
                    },
                    placeholder: '{{ $isId ? "Masukkan komentar Anda untuk editor di sini..." : "Enter your comments for the editor here..." }}'
                })
                .then(editor => {
                    commentsEditorInstance = editor;
                    editor.model.document.on('change:data', () => {
                        document.querySelector('#commentsHidden').value = editor.getData();
                    });
                })
                .catch(error => {
                    console.error('Comments CKEditor initialization failed:', error);
                });
        });

        function submissionWizard() {
            return {
                step: {{ request('step', ($draft && isset($draft->metadata['current_step'])) ? $draft->metadata['current_step'] : 1) }},
                section_id: {!! json_encode(old('section_id', $draft ? $draft->section_id : '')) !!},
                requirements: @json(($draft && isset($draft->metadata['requirements'])) ? $draft->metadata['requirements'] : []),
                requiredRequirements: {!! json_encode($submissionChecklists->where('is_required', true)->pluck('id')->map(fn($id) => (string)$id)->toArray()) !!},
                validationErrors: [],
                title: {!! json_encode(old('title', ($draft && !str_starts_with($draft->title, 'Untitled Draft -')) ? $draft->title : '')) !!},
                subtitle: {!! json_encode(old('subtitle', $draft->subtitle ?? '')) !!},
                abstract: {!! json_encode(old('abstract', $draft->abstract ?? '')) !!},
                abstractHtml: {!! json_encode(old('abstract', $draft->abstract ?? '')) !!},
                fileName: {!! json_encode($draft ? ($draft->files->where('file_type', \App\Models\SubmissionFile::TYPE_MANUSCRIPT)->first()?->file_name ?? '') : '') !!},
                fileSize: {!! json_encode($draft ? (round(($draft->files->where('file_type', \App\Models\SubmissionFile::TYPE_MANUSCRIPT)->first()?->file_size ?? 0) / 1024 / 1024, 2) . ' MB') : '') !!},
                references: {!! json_encode(old('references', $draft->references ?? '')) !!},
                primaryContactIndex: {{ $draft ? ($draft->authors->search(fn($a) => $a->is_primary_contact) !== false ? $draft->authors->search(fn($a) => $a->is_primary_contact) : 0) : 0 }},
                draggedIndex: null,
                dragEnabledIndex: null,
                authors: 
                    @php
                        if ($draft && $draft->authors->isNotEmpty()) {
                            $authorsData = $draft->authors->map(function($author) {
                                return [
                                    'first_name' => $author->first_name,
                                    'last_name' => $author->last_name,
                                    'email' => $author->email,
                                    'affiliation' => $author->affiliation,
                                    'country' => $author->country,
                                ];
                            })->toArray();
                        } else {
                            $parts = explode(' ', auth()->user()->name, 2);
                            $first = old('authors.0.first_name', $parts[0]);
                            $last = old('authors.0.last_name', $parts[1] ?? '');
                            $authorsData = [
                                [
                                    'first_name' => $first,
                                    'last_name' => $last,
                                    'email' => old('authors.0.email', auth()->user()->email),
                                    'affiliation' => old('authors.0.affiliation', auth()->user()->affiliation),
                                    'country' => old('authors.0.country', auth()->user()->country),
                                ]
                            ];
                        }
                    @endphp
                    @json($authorsData)
                ,

                dragStart(event, index) {
                    this.draggedIndex = index;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', index);
                },

                dragEnter(index) {
                    if (this.draggedIndex === null || this.draggedIndex === index) return;
                    
                    const item = this.authors.splice(this.draggedIndex, 1)[0];
                    this.authors.splice(index, 0, item);
                    
                    // Update primary contact index dynamically if it is affected by the swap
                    if (this.primaryContactIndex === this.draggedIndex) {
                        this.primaryContactIndex = index;
                    } else if (this.draggedIndex < this.primaryContactIndex && index >= this.primaryContactIndex) {
                        this.primaryContactIndex--;
                    } else if (this.draggedIndex > this.primaryContactIndex && index <= this.primaryContactIndex) {
                        this.primaryContactIndex++;
                    }
                    
                    this.draggedIndex = index;
                },

                dragEnd() {
                    this.draggedIndex = null;
                    this.dragEnabledIndex = null;
                },

                canProceed() {
                    if (this.step === 1) {
                        return this.requiredRequirements.every(id => this.requirements.includes(id));
                    }
                    if (this.step === 2) {
                        return this.fileName !== '';
                    }
                    if (this.step === 3) {
                        // Get abstract from CKEditor
                        if (editorInstance) {
                            this.abstractHtml = editorInstance.getData();
                            // Strip HTML tags for plain text validation
                            const div = document.createElement('div');
                            div.innerHTML = this.abstractHtml;
                            this.abstract = div.textContent || div.innerText || '';
                        }
                        return this.title && this.abstract.trim() && this.authors.every(a => a.first_name && a.last_name &&
                            a.email);
                    }
                    return true;
                },

                nextStep() {
                    // Sync CKEditors
                    if (this.step === 1 && commentsEditorInstance) {
                        document.querySelector('#commentsHidden').value = commentsEditorInstance.getData();
                    }
                    if (this.step === 3 && editorInstance) {
                        document.querySelector('#abstractHidden').value = editorInstance.getData();
                        this.abstractHtml = editorInstance.getData();
                    }

                    // Validate current step
                    this.validationErrors = [];

                    if (this.step === 1) {
                        const allRequiredChecked = this.requiredRequirements.every(id => this.requirements.includes(id));
                        if (!allRequiredChecked) {
                            this.validationErrors.push('{{ $isId ? "Harap centang semua item daftar periksa pengajuan yang wajib." : "Please check all required submission checklist items." }}');
                        }
                    } else if (this.step === 2) {
                        if (!this.fileName) {
                            this.validationErrors.push('{{ $isId ? "Harap unggah berkas naskah." : "Please upload a manuscript file." }}');
                        }
                    } else if (this.step === 3) {
                        if (!this.title || this.title.trim() === '') {
                            this.validationErrors.push('{{ $isId ? "Judul wajib diisi." : "Title is required." }}');
                        }

                        if (editorInstance) {
                            this.abstractHtml = editorInstance.getData();
                            const div = document.createElement('div');
                            div.innerHTML = this.abstractHtml;
                            this.abstract = div.textContent || div.innerText || '';
                        }
                        if (!this.abstract || this.abstract.trim() === '') {
                            this.validationErrors.push('{{ $isId ? "Abstrak wajib diisi." : "Abstract is required." }}');
                        }

                        // Validate authors
                        this.authors.forEach((author, index) => {
                            if (!author.first_name || author.first_name.trim() === '') {
                                this.validationErrors.push(`{{ $isId ? 'Kontributor' : 'Contributor' }} ${index + 1}: {{ $isId ? 'Nama depan wajib diisi.' : 'First name is required.' }}`);
                            }
                            if (!author.last_name || author.last_name.trim() === '') {
                                this.validationErrors.push(`{{ $isId ? 'Kontributor' : 'Contributor' }} ${index + 1}: {{ $isId ? 'Nama belakang wajib diisi.' : 'Last name is required.' }}`);
                            }
                            if (!author.email || author.email.trim() === '') {
                                this.validationErrors.push(`{{ $isId ? 'Kontributor' : 'Contributor' }} ${index + 1}: {{ $isId ? 'Email wajib diisi.' : 'Email is required.' }}`);
                            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(author.email)) {
                                this.validationErrors.push(`{{ $isId ? 'Kontributor' : 'Contributor' }} ${index + 1}: {{ $isId ? 'Format email tidak valid.' : 'Valid email is required.' }}`);
                            }
                        });
                    }

                    // Show errors or proceed
                    if (this.validationErrors.length > 0) {
                        // Scroll to top to show errors
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    } else {
                        this.saveDraft(this.step + 1);
                    }
                },

                handleFileChange(file) {
                    if (!file) return;
                    this.fileName = file.name;
                    this.fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                },

                clearFile() {
                    this.fileName = '';
                    this.fileSize = '';
                    this.$refs.fileInput.value = '';
                },

                addAuthor() {
                    this.authors.push({
                        first_name: '',
                        last_name: '',
                        email: '',
                        affiliation: '',
                        country: ''
                    });
                },

                removeAuthor(index) {
                    if (this.authors.length <= 1) return;
                    this.authors.splice(index, 1);
                    if (this.primaryContactIndex >= index && this.primaryContactIndex > 0) {
                        this.primaryContactIndex--;
                    }
                },

                isSubmitting: false,

                submitForm() {
                    if (this.isSubmitting) return;
                    this.isSubmitting = true;
                    this.$refs.form.submit();
                },

                saveDraft(targetStep = null) {
                    if (commentsEditorInstance) {
                        document.querySelector('#commentsHidden').value = commentsEditorInstance.getData();
                    }
                    if (editorInstance) {
                        document.querySelector('#abstractHidden').value = editorInstance.getData();
                    }

                    const form = this.$refs.form;
                    form.action = "{{ route('journal.submissions.save-draft', ['journal' => $journal->slug]) }}";

                    let stepInput = form.querySelector('input[name="current_step"]');
                    if (!stepInput) {
                        stepInput = document.createElement('input');
                        stepInput.type = 'hidden';
                        stepInput.name = 'current_step';
                        form.appendChild(stepInput);
                    }
                    stepInput.value = targetStep !== null ? targetStep : this.step;

                    form.submit();
                },

                cancelSubmission() {
                    if (confirm('{{ $isId ? "Apakah Anda yakin ingin membatalkan pengajuan ini? Ini akan menghapus draf pengajuan." : "Are you sure you want to cancel this submission? This will delete the draft submission." }}')) {
                        const draftId = '{{ $draft->id ?? '' }}';
                        if (draftId) {
                            document.getElementById('cancelSubmissionForm').submit();
                        } else {
                            window.location.href = "{{ route('journal.submissions.index', ['journal' => $journal->slug]) }}";
                        }
                    }
                },

                init() {
                    const countryNamesToCodes = {};
                    const countriesList = @json(config('countries', []));
                    const fallbacks = {
                        'ID': 'Indonesia',
                        'MY': 'Malaysia',
                        'SG': 'Singapore',
                        'TH': 'Thailand',
                        'VN': 'Vietnam',
                        'PH': 'Philippines',
                        'AU': 'Australia',
                        'US': 'United States',
                        'OTHER': 'Other'
                    };
                    const countries = Object.keys(countriesList).length > 0 ? countriesList : fallbacks;
                    
                    Object.entries(countries).forEach(([code, name]) => {
                        countryNamesToCodes[name.toLowerCase()] = code;
                    });

                    // Normalize author countries to codes
                    this.authors.forEach(author => {
                        if (author.country) {
                            const trimmed = author.country.trim();
                            if (trimmed.length > 2) {
                                const mapped = countryNamesToCodes[trimmed.toLowerCase()];
                                if (mapped) {
                                    author.country = mapped;
                                }
                            }
                        }
                    });
                }
            }
        }

        function keywordInputCustom(initialKeywords = []) {
            return {
                tags: Array.isArray(initialKeywords) ? [...initialKeywords] : [],
                newTag: '',
                suggestions: [],
                showSuggestions: false,
                highlightedIndex: -1,
                controller: null,

                addTag() {
                    let tag = this.newTag.trim();
                    if (tag.endsWith(',')) {
                        tag = tag.slice(0, -1).trim();
                    }
                    if (tag && !this.tags.includes(tag)) {
                        this.tags.push(tag);
                    }
                    this.newTag = '';
                    this.suggestions = [];
                    this.showSuggestions = false;
                    this.highlightedIndex = -1;
                },

                removeTag(index) {
                    this.tags.splice(index, 1);
                },

                handleEnter() {
                    if (this.showSuggestions && this.highlightedIndex >= 0 && this.highlightedIndex < this.suggestions.length) {
                        this.selectSuggestion(this.suggestions[this.highlightedIndex]);
                    } else {
                        this.addTag();
                    }
                },

                fetchSuggestions() {
                    const value = this.newTag.trim();
                    if (value.length < 2) {
                        this.suggestions = [];
                        this.showSuggestions = false;
                        this.highlightedIndex = -1;
                        return;
                    }

                    if (this.controller) {
                        this.controller.abort();
                    }
                    this.controller = new AbortController();

                    fetch(`/api/keywords?query=${encodeURIComponent(value)}`, {
                        signal: this.controller.signal
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.suggestions = data.map(k => k.content).filter(content => !this.tags.includes(content));
                        this.showSuggestions = this.suggestions.length > 0;
                        this.highlightedIndex = -1;
                    })
                    .catch(err => {
                        if (err.name !== 'AbortError') {
                            console.error('Keyword fetch error:', err);
                        }
                    });
                },

                selectSuggestion(suggestion) {
                    if (!this.tags.includes(suggestion)) {
                        this.tags.push(suggestion);
                    }
                    this.newTag = '';
                    this.suggestions = [];
                    this.showSuggestions = false;
                    this.highlightedIndex = -1;
                },

                highlightDown() {
                    if (this.suggestions.length === 0) return;
                    this.highlightedIndex = (this.highlightedIndex + 1) % this.suggestions.length;
                },

                highlightUp() {
                    if (this.suggestions.length === 0) return;
                    this.highlightedIndex = (this.highlightedIndex - 1 + this.suggestions.length) % this.suggestions.length;
                }
            }
        }
    </script>
@endsection
