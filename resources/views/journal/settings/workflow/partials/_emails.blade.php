@php
    $journal = current_journal();
    $journalSlug = $journal->slug;
    $currentLoc = session('app_locale', app()->getLocale());
    $isId = in_array($currentLoc, ['id', 'id_ID']);
@endphp

<div x-data="{
    emailSubTab: '{{ session('email_subtab', request('subtab', 'config')) }}',
    showEditModal: false,
    editingTemplate: null,
    searchQuery: '',

    editTemplate(template) {
        this.editingTemplate = template;
        this.showEditModal = true;
        this.$nextTick(() => {
            this.initEmailBodyTinyMCE();
        });
    },

    closeEditModal() {
        this.destroyEmailBodyTinyMCE();
        this.showEditModal = false;
    },

    initEmailBodyTinyMCE() {
        const selector = '#editing_template_body';
        if (typeof tinymce !== 'undefined' && tinymce.get('editing_template_body')) {
            tinymce.get('editing_template_body').remove();
        }

        const self = this;
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: selector,
                height: 320,
                menubar: false,
                plugins: 'lists link image table code autoresize',
                toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | removeformat | code',
                branding: false,
                license_key: 'gpl',
                relative_urls: false,
                remove_script_host: false,
                convert_urls: false,
                link_assume_external_targets: 'https',
                link_default_target: '_blank',
                default_link_target: '_blank',
                content_style: 'a { color: #2563eb !important; text-decoration: underline !important; font-weight: 500; } a:hover { color: #1d4ed8 !important; }',
                setup: function(editor) {
                    editor.on('init', function() {
                        if (self.editingTemplate && self.editingTemplate.body) {
                            editor.setContent(self.editingTemplate.body);
                        }
                    });
                    editor.on('change keyup blur NodeChange', function() {
                        if (self.editingTemplate) {
                            self.editingTemplate.body = editor.getContent();
                        }
                    });
                },
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
                            reject({
                                message: 'HTTP Error: ' + xhr.status,
                                remove: true
                            });
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
        }
    },

    destroyEmailBodyTinyMCE() {
        if (typeof tinymce !== 'undefined' && tinymce.get('editing_template_body')) {
            tinymce.get('editing_template_body').remove();
        }
    },

    insertVariable(varTag) {
        if (typeof tinymce !== 'undefined' && tinymce.get('editing_template_body')) {
            tinymce.get('editing_template_body').execCommand('mceInsertContent', false, varTag);
            if (this.editingTemplate) {
                this.editingTemplate.body = tinymce.get('editing_template_body').getContent();
            }
        } else {
            if (this.editingTemplate) {
                this.editingTemplate.body = (this.editingTemplate.body || '') + varTag;
            }
        }
    },

    async updateTemplateStatus(templateId, enabled) {
        const url = `/{{ $journalSlug }}/settings/workflow/email-templates/${templateId}/toggle`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            });

            if (!response.ok) {
                alert('{{ $isId ? "Gagal memperbarui status. Silakan coba lagi." : "Failed to update status. Please try again." }}');
                window.location.reload();
            }
        } catch (e) {
            console.error(e);
            alert('{{ $isId ? "Terjadi kesalahan." : "An error occurred." }}');
            window.location.reload();
        }
    }
}">

    {{-- SUB-TAB NAVIGATION BAR --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex space-x-8" aria-label="Email Sub-Tabs">
            <button type="button" @click="emailSubTab = 'config'"
                :class="emailSubTab === 'config' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                class="py-3.5 px-1 border-b-2 text-sm flex items-center gap-2.5 transition-all cursor-pointer">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                    :class="emailSubTab === 'config' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500'">
                    <i class="fa-solid fa-at text-xs"></i>
                </div>
                <span>{{ $isId ? 'Konfigurasi Surel' : 'Email Configuration' }}</span>
            </button>
            <button type="button" @click="emailSubTab = 'templates'"
                :class="emailSubTab === 'templates' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                class="py-3.5 px-1 border-b-2 text-sm flex items-center gap-2.5 transition-all cursor-pointer">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                    :class="emailSubTab === 'templates' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500'">
                    <i class="fa-solid fa-envelope-open-text text-xs"></i>
                </div>
                <span>{{ $isId ? 'Templat Surel' : 'Email Templates' }}</span>
            </button>
        </nav>
    </div>

    {{-- SUB-TAB 1: EMAIL CONFIGURATION --}}
    <div x-show="emailSubTab === 'config'" x-cloak class="space-y-6">
        <form action="{{ route('journal.settings.workflow.update', ['journal' => $journal->slug]) }}" method="POST"
            @submit="if (typeof tinymce !== 'undefined' && tinymce.get('email_signature')) { tinymce.get('email_signature').triggerSave(); }">
            @csrf
            @method('PUT')
            <input type="hidden" name="tab" value="emails">

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-at text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">{{ $isId ? 'Konfigurasi Surel' : 'Email Configuration' }}</h3>
                        <p class="text-sm text-gray-500">{{ $isId ? 'Konfigurasikan pengaturan pengiriman surel.' : 'Configure email sending settings.' }}</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label for="email_signature" class="block text-sm font-medium text-gray-700 mb-2">{{ $isId ? 'Tanda Tangan Surel' : 'Email Signature' }}</label>
                        <textarea name="email_signature" id="email_signature" rows="5"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="{{ $isId ? 'cth. Editor, ' : 'e.g. The Editor, ' }}{{ $journal->name }}">{{ $journal->email_signature }}</textarea>
                        <p class="mt-2 text-xs text-gray-500">{{ $isId ? 'Tanda tangan ini akan ditambahkan di bagian bawah surel keluar.' : 'This signature will be appended to the bottom of outgoing emails.' }}</p>
                    </div>

                    <div>
                        <label for="email_bounce_address" class="block text-sm font-medium text-gray-700 mb-2">{{ $isId ? 'Alamat Pantulan (Bounce Address)' : 'Bounce Address' }}</label>
                        <input type="email" name="email_bounce_address" id="email_bounce_address"
                            value="{{ $journal->email_bounce_address }}"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <div class="mt-2 p-3 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-700 flex items-start gap-2">
                            <i class="fa-solid fa-info-circle mt-0.5"></i>
                            <span>{{ $isId ? 'Surel yang tidak terkirim akan dikembalikan ke alamat ini. Pastikan konfigurasi server Anda mengizinkan pengiriman atas nama domain ini.' : 'Undeliverable emails will be returned to this address. Ensure your server configuration allows sending on behalf of this domain.' }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer">
                        <i class="fa-solid fa-check mr-2"></i>
                        {{ $isId ? 'Simpan Pengaturan' : 'Save Setup' }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- SUB-TAB 2: EMAIL TEMPLATES --}}
    <div x-show="emailSubTab === 'templates'" x-cloak class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Toolbar Header & Refined Search Box --}}
            <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/80">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-envelope-open-text text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">{{ $isId ? 'Templat Surel' : 'Email Templates' }}</h3>
                        <p class="text-xs text-gray-500">{{ $isId ? 'Kelola dan sesuaikan templat pesan surel sistem.' : 'Manage and customize automated email templates.' }}</p>
                    </div>
                </div>

                {{-- Kotak Pencarian Terpisah Tanpa Overlap --}}
                <div class="relative w-full sm:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none z-10">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" x-model="searchQuery"
                        style="padding-left: 2.75rem !important;"
                        class="block w-full pr-4 py-2 border border-gray-300 rounded-lg leading-5 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all shadow-sm"
                        placeholder="{{ $isId ? 'Cari templat surel...' : 'Find email template...' }}">
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ $isId ? 'Nama Templat' : 'Template Name' }}</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                                {{ $isId ? 'Status' : 'Status' }}</th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                                {{ $isId ? 'Aksi' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($emailTemplates as $template)
                            <tr x-show="searchQuery === '' || '{{ strtolower($template->name) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($template->key) }}'.includes(searchQuery.toLowerCase())">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-gray-900">{{ $template->name }}</span>
                                        <span class="text-xs text-indigo-600 font-mono mt-0.5">{{ $template->key }}</span>
                                        <span class="text-xs text-gray-500 truncate max-w-md mt-1">{{ Str::limit($template->description, 70) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-2" x-data="{ enabled: {{ $template->is_enabled ? 'true' : 'false' }} }">
                                        <button type="button" 
                                            @click="enabled = !enabled; updateTemplateStatus({{ $template->id }}, enabled)"
                                            :class="enabled ? 'bg-emerald-500' : 'bg-gray-300'"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            role="switch" 
                                            :aria-checked="enabled">
                                            <span class="sr-only">Toggle email template status</span>
                                            <span :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out">
                                            </span>
                                        </button>
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                            :class="enabled ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                                            x-text="enabled ? '{{ $isId ? 'ON' : 'ON' }}' : '{{ $isId ? 'OFF' : 'OFF' }}'">
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-3">
                                        @if ($template->is_custom)
                                            <button type="button"
                                                onclick="submitForm('{{ route('journal.settings.workflow.email-templates.reset', ['journal' => $journalSlug, 'emailTemplate' => $template->id]) }}', 'POST', '{{ $isId ? 'Kembalikan templat ini ke konten bawaannya?' : 'Reset this template to its default content?' }}')"
                                                class="text-xs text-orange-600 hover:text-orange-900 bg-orange-50 hover:bg-orange-100 px-2.5 py-1 rounded-md font-medium transition-colors cursor-pointer">
                                                {{ $isId ? 'Atur Ulang' : 'Reset' }}
                                            </button>
                                        @endif

                                        <button type="button" @click="editTemplate({{ $template }})"
                                            class="text-xs text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-md font-medium transition-colors cursor-pointer flex items-center gap-1">
                                            <i class="fa-solid fa-pen text-[10px]"></i>
                                            <span>{{ $isId ? 'Ubah' : 'Edit' }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- EDIT TEMPLATE MODAL --}}
    <template x-teleport="body">
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-[99] overflow-y-auto" role="dialog"
            aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">

                {{-- Backdrop --}}
                <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true"
                    @click="closeEditModal()">
                </div>

                {{-- Modal Panel --}}
                <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative bg-white rounded-xl shadow-xl w-full max-w-3xl overflow-hidden transform transition-all z-[100]">

                    <template x-if="editingTemplate">
                        <form :action="'/{{ $journalSlug }}/settings/workflow/email-templates/' + editingTemplate?.id"
                            method="POST"
                            @submit="if (typeof tinymce !== 'undefined' && tinymce.get('editing_template_body')) { tinymce.get('editing_template_body').triggerSave(); }">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="subtab" value="templates">

                            <div class="bg-white px-6 py-6">
                                <div class="flex items-center justify-between mb-5">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">
                                            {{ $isId ? 'Ubah Templat Surel' : 'Edit Email Template' }}
                                        </h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <span class="font-medium text-gray-700"
                                                x-text="editingTemplate.name"></span> - <span
                                                x-text="editingTemplate.key" class="font-mono text-xs"></span>
                                        </p>
                                    </div>
                                    <button type="button" @click="closeEditModal()"
                                        class="text-gray-400 hover:text-gray-500 cursor-pointer">
                                        <i class="fa-solid fa-xmark text-xl"></i>
                                    </button>
                                </div>

                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-6 text-sm text-blue-800">
                                    <p x-text="editingTemplate.description"></p>
                                </div>

                                <div class="space-y-5">
                                    {{-- Subject --}}
                                    <div>
                                        <label for="subject"
                                            class="block text-sm font-semibold text-gray-700 mb-1">{{ $isId ? 'Baris Subjek' : 'Subject Line' }}</label>
                                        <input type="text" name="subject" id="subject"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            x-model="editingTemplate.subject" required>
                                    </div>

                                    {{-- Body --}}
                                    <div>
                                        <label for="editing_template_body"
                                            class="block text-sm font-semibold text-gray-700 mb-1">{{ $isId ? 'Badan Surel' : 'Email Body' }}</label>
                                        <textarea name="body" id="editing_template_body" rows="10"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm leading-relaxed"
                                            x-model="editingTemplate.body" required></textarea>
                                    </div>

                                    {{-- Variables Hint --}}
                                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-3.5">
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                {{ $isId ? 'Variabel yang Tersedia' : 'Available Variables' }}
                                            </p>
                                            <span class="text-[11px] text-gray-400 font-normal">
                                                {{ $isId ? 'Klik untuk menyisipkan ke editor' : 'Click to insert into editor' }}
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap gap-2 text-xs font-mono text-gray-600">
                                            <button type="button" @click="insertVariable('{$authorName}')" title="{{ $isId ? 'Sisipkan {$authorName}' : 'Insert {$authorName}' }}" class="bg-white hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 border border-gray-200 px-2 py-1 rounded transition-colors cursor-pointer flex items-center gap-1.5 font-mono shadow-xs">
                                                <i class="fa-solid fa-plus text-[10px] text-indigo-500"></i> {$authorName}
                                            </button>
                                            <button type="button" @click="insertVariable('{$recipientName}')" title="{{ $isId ? 'Sisipkan {$recipientName}' : 'Insert {$recipientName}' }}" class="bg-white hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 border border-gray-200 px-2 py-1 rounded transition-colors cursor-pointer flex items-center gap-1.5 font-mono shadow-xs">
                                                <i class="fa-solid fa-plus text-[10px] text-indigo-500"></i> {$recipientName}
                                            </button>
                                            <button type="button" @click="insertVariable('{$submissionTitle}')" title="{{ $isId ? 'Sisipkan {$submissionTitle}' : 'Insert {$submissionTitle}' }}" class="bg-white hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 border border-gray-200 px-2 py-1 rounded transition-colors cursor-pointer flex items-center gap-1.5 font-mono shadow-xs">
                                                <i class="fa-solid fa-plus text-[10px] text-indigo-500"></i> {$submissionTitle}
                                            </button>
                                            <button type="button" @click="insertVariable('{$journalName}')" title="{{ $isId ? 'Sisipkan {$journalName}' : 'Insert {$journalName}' }}" class="bg-white hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 border border-gray-200 px-2 py-1 rounded transition-colors cursor-pointer flex items-center gap-1.5 font-mono shadow-xs">
                                                <i class="fa-solid fa-plus text-[10px] text-indigo-500"></i> {$journalName}
                                            </button>
                                            <button type="button" @click="insertVariable('{$submissionUrl}')" title="{{ $isId ? 'Sisipkan {$submissionUrl}' : 'Insert {$submissionUrl}' }}" class="bg-white hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 border border-gray-200 px-2 py-1 rounded transition-colors cursor-pointer flex items-center gap-1.5 font-mono shadow-xs">
                                                <i class="fa-solid fa-plus text-[10px] text-indigo-500"></i> {$submissionUrl}
                                            </button>
                                            <button type="button" @click="insertVariable('{$signature}')" title="{{ $isId ? 'Sisipkan {$signature}' : 'Insert {$signature}' }}" class="bg-white hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 border border-gray-200 px-2 py-1 rounded transition-colors cursor-pointer flex items-center gap-1.5 font-mono shadow-xs">
                                                <i class="fa-solid fa-plus text-[10px] text-indigo-500"></i> {$signature}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-100">
                                <button type="submit"
                                    class="inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm cursor-pointer">
                                    {{ $isId ? 'Simpan Perubahan' : 'Save Changes' }}
                                </button>
                                <button type="button" @click="closeEditModal()"
                                    class="inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm cursor-pointer">
                                    {{ $isId ? 'Batal' : 'Cancel' }}
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
    <script src="{{ asset('assets/js/vendors/plugins/tinymce/tinymce.min.js') }}"></script>
    <script>
        tinymce.init({
            selector: '#email_signature',
            height: 350,
            menubar: false,
            plugins: 'lists link image table code autoresize',
            toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | table link image | code',
            branding: false,
            license_key: 'gpl',
            relative_urls: false,
            remove_script_host: false,
            convert_urls: false,
            link_assume_external_targets: 'https',
            link_default_target: '_blank',
            default_link_target: '_blank',
            content_style: 'a { color: #2563eb !important; text-decoration: underline !important; font-weight: 500; } a:hover { color: #1d4ed8 !important; }',
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
                        reject({
                            message: 'HTTP Error: ' + xhr.status,
                            remove: true
                        });
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
