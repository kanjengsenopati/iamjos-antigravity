@props([
    'journal',
    'submission',
    'pubStatus'
])

{{--
    This component uses the Alpine.js data scope from its parent container.
    Parent must define:
    - galleyModalOpen
    - editingGalley
    - galleyLabel, galleyLocale, galleyUrlPath, isRemote, remoteUrl, selectedFile
    - errors
    - submitGalley()
    - isSubmitting
--}}
<div x-show="galleyModalOpen" x-cloak class="fixed inset-0 z-[60] overflow-y-auto"
    role="dialog" aria-modal="true" aria-labelledby="galley-modal-title" @keydown.escape.window="galleyModalOpen = false">

    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Backdrop --}}
        <div x-show="galleyModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm"
            @click="galleyModalOpen = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- Modal Panel --}}
        <div x-show="galleyModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative inline-block w-full max-w-xl overflow-hidden text-left align-bottom transition-all transform bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:my-8 sm:align-middle ring-1 ring-black ring-opacity-5">

            {{-- Header (Clean minimal styling) --}}
            <div class="px-6 pt-6 pb-4 flex items-start justify-between">
                <div class="flex items-start space-x-4 flex-1">
                    {{-- Icon Container --}}
                    <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-rose-100">
                        <i class="text-lg text-rose-600 fa-solid fa-file-circle-plus"></i>
                    </div>
                    
                    {{-- Title & Subtitle --}}
                    <div class="flex-1">
                        <x-text.h2 id="galley-modal-title" class="text-gray-900 font-semibold tracking-tight !text-lg"
                            x-text="editingGalley ? 'Edit Galley' : 'Add Publication Galley'">
                            Add Publication Galley
                        </x-text.h2>
                        <x-text.body class="text-gray-500 mt-1">
                            Upload a file or link to an external source for reader download
                        </x-text.body>
                    </div>
                </div>

                {{-- Close Button --}}
                <button @click="galleyModalOpen = false"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1.5 rounded-full hover:bg-gray-100 focus:outline-none">
                    <i class="text-lg fa-solid fa-xmark"></i>
                    <span class="sr-only">Close</span>
                </button>
            </div>

            {{-- Form Body --}}
            <div class="px-6 py-4 space-y-4 bg-white">

                {{-- Row 1: Label & Language --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    {{-- Galley Label --}}
                    <div>
                        <label for="galley-label">
                            <x-text.body class="font-semibold text-slate-800 mb-1 block">
                                Galley Label <span class="text-red-500">*</span>
                            </x-text.body>
                        </label>
                        <input type="text" id="galley-label" x-model="galleyLabel" required
                            placeholder="e.g., PDF, HTML, EPUB"
                            class="block w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 py-1.5 text-sm"
                            :class="{ 'border-red-500': errors.label }">
                        <template x-if="errors.label">
                            <x-text.caption class="text-red-600 block mt-1" x-text="errors.label[0]"></x-text.caption>
                        </template>
                        <x-text.caption class="block mt-1">Will be displayed as the download button label</x-text.caption>
                    </div>

                    {{-- Language --}}
                    <div>
                        <label for="galley-locale">
                            <x-text.body class="font-semibold text-slate-800 mb-1 block">Language</x-text.body>
                        </label>
                        <select id="galley-locale" x-model="galleyLocale"
                            class="block w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 py-1.5 text-sm">
                            <option value="en">English</option>
                            <option value="id">Indonesian</option>
                            <option value="ar">Arabic</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                            <option value="es">Spanish</option>
                            <option value="pt">Portuguese</option>
                            <option value="zh">Chinese</option>
                            <option value="ja">Japanese</option>
                            <option value="ko">Korean</option>
                            <option value="ru">Russian</option>
                        </select>
                    </div>
                </div>

                {{-- Row 2: URL Path --}}
                <div>
                    <label for="galley-url-path">
                        <x-text.body class="font-semibold text-slate-800 mb-1 block">
                            URL Path <span class="font-normal text-slate-400">(optional)</span>
                        </x-text.body>
                    </label>
                    <div class="flex items-stretch shadow-sm rounded-lg overflow-hidden group focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-1">
                        <span
                            class="inline-flex items-center px-3 text-slate-500 bg-slate-50 border border-r-0 border-slate-200 rounded-l-lg group-focus-within:border-blue-500 group-focus-within:text-blue-600 transition-colors text-sm">
                            /article/{{ $submission->slug }}/
                        </span>
                        <input type="text" id="galley-url-path" x-model="galleyUrlPath" placeholder="pdf"
                            class="flex-1 border-slate-200 rounded-r-lg focus:ring-0 focus:border-blue-500 group-focus-within:border-blue-500 py-1.5 text-sm">
                    </div>
                    <template x-if="errors.url_path">
                        <x-text.caption class="text-red-600 block mt-1" x-text="errors.url_path[0]"></x-text.caption>
                    </template>
                    <x-text.caption class="block mt-1">Custom slug for SEO-friendly URLs. Only letters, numbers, dashes, and underscores.</x-text.caption>
                </div>

                {{-- Divider --}}
                <div class="relative pt-1">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-slate-100"></div>
                    </div>
                    <div class="relative flex justify-start">
                        <x-text.label class="pr-3 bg-white text-slate-400">File Source</x-text.label>
                    </div>
                </div>

                {{-- Remote Toggle --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" x-model="isRemote" id="is-remote-checkbox"
                            class="w-5 h-5 text-blue-600 border-slate-300 rounded cursor-pointer focus:ring-blue-500 transition-all duration-150 ease-in-out">
                    </div>
                    <div class="ml-2">
                        <label for="is-remote-checkbox"
                            class="cursor-pointer select-none">
                            <x-text.body class="font-semibold text-slate-850 hover:text-blue-600 transition-colors">
                                This galley will be available at a separate website
                            </x-text.body>
                        </label>
                        <x-text.caption class="block mt-0.5">
                            Check this if the file is hosted externally (e.g., publisher's website, cloud storage)
                        </x-text.caption>
                    </div>
                </div>

                {{-- Dynamic Content Area --}}
                <div class="min-h-[160px] transition-all duration-300">

                    {{-- Remote URL Input (if isRemote) --}}
                    <div x-show="isRemote"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <label for="galley-remote-url">
                            <x-text.body class="font-semibold text-slate-800 mb-2 block">
                                Remote URL <span class="text-red-500">*</span>
                            </x-text.body>
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="text-slate-400 fa-solid fa-link"></i>
                            </div>
                            <input type="url" id="galley-remote-url" x-model="remoteUrl"
                                placeholder="https://example.com/article.pdf"
                                class="block w-full pl-10 border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 transition-all"
                                :class="{ 'border-red-500': errors.url_remote }"
                                :required="isRemote">
                        </div>
                        <template x-if="errors.url_remote">
                            <x-text.caption class="text-red-600 block mt-1" x-text="errors.url_remote[0]"></x-text.caption>
                        </template>
                        <div class="flex items-start mt-2 space-x-2 bg-blue-50 p-2.5 rounded-[12px] border border-blue-100">
                            <i class="mt-0.5 fa-solid fa-circle-info shrink-0 text-blue-500"></i>
                            <x-text.caption class="text-blue-700 not-italic font-medium">
                                Users will be redirected to this URL when they click the download button.
                            </x-text.caption>
                        </div>
                    </div>

                    {{-- File Upload (if NOT isRemote) --}}
                    <div x-show="!isRemote"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <label>
                            <x-text.body class="font-semibold text-slate-800 mb-1 block">
                                Upload File <span class="text-red-500"
                                    x-show="!editingGalley">*</span>
                                <span class="font-normal text-slate-400"
                                    x-show="editingGalley">(leave empty to keep current)</span>
                            </x-text.body>
                        </label>

                        {{-- Drop Zone --}}
                        <div class="relative group">
                            <label for="galley-file-input"
                                class="flex flex-col items-center justify-center w-full h-28 transition-all duration-200 border-2 border-dashed rounded-[20px] cursor-pointer"
                                :class="selectedFile ? 'border-emerald-400 bg-emerald-50/50 shadow-sm' :
                                    'border-slate-300 bg-slate-50 hover:border-blue-400 hover:bg-blue-50/50 hover:shadow-sm'">

                                <template x-if="!selectedFile">
                                    <div class="flex flex-col items-center justify-center py-4 text-center">
                                        <div class="flex items-center justify-center w-10 h-10 mb-2 transition-transform duration-300 bg-blue-100 rounded-full group-hover:scale-110 group-hover:bg-blue-200">
                                            <i class="text-lg text-blue-600 fa-solid fa-cloud-arrow-up"></i>
                                        </div>
                                        <x-text.body class="font-semibold text-slate-700">
                                            <span class="text-blue-600 underline decoration-blue-300 decoration-2 underline-offset-2 group-hover:decoration-blue-500">Click to upload</span> or drag and drop
                                        </x-text.body>
                                        <x-text.caption class="mt-0.5">PDF, HTML, EPUB, XML, DOC (Max 50MB)</x-text.caption>
                                    </div>
                                </template>

                                <template x-if="selectedFile">
                                    <div class="flex items-center justify-center w-full h-full p-4">
                                        <div class="flex items-center w-full max-w-sm p-3 bg-white rounded-[16px] shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                                            <div class="flex items-center justify-center w-8 h-8 mr-3 bg-emerald-100 rounded-full shrink-0">
                                                <i class="text-md text-emerald-600 fa-solid fa-file-circle-check"></i>
                                            </div>
                                            <div class="flex-1 min-w-0 text-left">
                                                <x-text.body class="font-semibold text-slate-900 truncate text-sm"
                                                    x-text="selectedFileName"></x-text.body>
                                                <x-text.caption class="text-emerald-600 font-semibold cursor-pointer hover:underline group-hover:text-emerald-700 block mt-0.5">Click to change file</x-text.caption>
                                            </div>
                                            <div class="ml-2 shrink-0">
                                                <i class="text-slate-300 fa-solid fa-pen-to-square"></i>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <input id="galley-file-input" type="file"
                                    class="hidden"
                                    accept=".pdf,.html,.htm,.epub,.xml,.doc,.docx"
                                    @change="handleGalleyFileSelect($event)">
                            </label>
                        </div>
                        <template x-if="errors.file">
                            <x-text.caption class="text-red-600 block mt-1" x-text="errors.file[0]"></x-text.caption>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between px-6 py-3 bg-slate-50 border-t border-slate-100 rounded-b-[24px]">
                <x-text.caption class="hidden sm:block">
                    <i class="mr-1 fa-solid fa-info-circle"></i>
                    Galleys are the final published formats
                </x-text.caption>
                <div class="flex items-center w-full gap-3 sm:w-auto">
                    <button type="button" @click="galleyModalOpen = false"
                        class="w-full px-5 py-2 transition-colors bg-white border border-slate-200 rounded-[12px] sm:w-auto hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm">
                        <x-text.body class="font-semibold text-slate-700">Cancel</x-text.body>
                    </button>
                    <button type="button" @click="submitGalley()"
                        :disabled="isSubmitting || (!isRemote && !selectedFile && !editingGalley)"
                        class="w-full inline-flex justify-center items-center px-5 py-2 transition-all bg-blue-600 border border-transparent rounded-[12px] sm:w-auto hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none hover:shadow-md">
                        <template x-if="isSubmitting">
                            <span class="flex items-center">
                                <i class="mr-2 fa-solid fa-circle-notch fa-spin"></i>
                                <x-text.body class="font-semibold text-white">Saving...</x-text.body>
                            </span>
                        </template>
                        <template x-if="!isSubmitting">
                            <span class="flex items-center">
                                <i class="mr-2 fa-solid fa-check"></i>
                                <x-text.body class="font-semibold text-white" x-text="editingGalley ? 'Update Galley' : 'Save Galley'"></x-text.body>
                            </span>
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
