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
    showFilters: true,
    filterStatus: '',
    filterSentFrom: '',
    filterSentTo: '',
    filterStage: '',
    expandedTemplates: [],

    toggleExpand(id) {
        if (this.expandedTemplates.includes(id)) {
            this.expandedTemplates = this.expandedTemplates.filter(item => item !== id);
        } else {
            this.expandedTemplates.push(id);
        }
    },

    isExpanded(id) {
        return this.expandedTemplates.includes(id);
    },

    clearFilters() {
        this.searchQuery = '';
        this.filterStatus = '';
        this.filterSentFrom = '';
        this.filterSentTo = '';
        this.filterStage = '';
    },

    hasActiveFilters() {
        return this.searchQuery !== '' || this.filterStatus !== '' || this.filterSentFrom !== '' || this.filterSentTo !== '' || this.filterStage !== '';
    },

    matchesFilter(item) {
        if (this.searchQuery !== '') {
            const q = this.searchQuery.toLowerCase().trim();
            const nameMatch = (item.name || '').toLowerCase().includes(q);
            const keyMatch = (item.key || '').toLowerCase().includes(q);
            const descMatch = (item.description || '').toLowerCase().includes(q);
            if (!nameMatch && !keyMatch && !descMatch) return false;
        }

        if (this.filterStatus === 'enabled' && !item.is_enabled) return false;
        if (this.filterStatus === 'disabled' && item.is_enabled) return false;
        if (this.filterStatus === 'custom' && !item.is_custom) return false;

        if (this.filterSentFrom !== '' && (item.sent_from || '') !== this.filterSentFrom) return false;
        if (this.filterSentTo !== '' && (item.sent_to || '') !== this.filterSentTo) return false;
        if (this.filterStage !== '' && (item.stage || '').toLowerCase() !== this.filterStage.toLowerCase()) return false;

        return true;
    },

    editTemplate(template) {
        // Ensure body is HTML formatted if it still has raw newlines without block tags
        let body = template.body || '';
        const hasBlock = /<(p|div|table|ul|ol|h[1-6]|blockquote)\b[^>]*>/i.test(body);
        if (!hasBlock && body.trim() !== '') {
            const paragraphs = body.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n\n').map(p => p.trim()).filter(p => p !== '');
            body = paragraphs.map(p => `<p>${p.replace(/\n/g, '<br />')}</p>`).join('\n');
            template.body = body;
        }

        this.editingTemplate = Object.assign({}, template);
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
                height: 340,
                menubar: false,
                forced_root_block: 'p',
                remove_trailing_brs: true,
                entity_encoding: 'raw',
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
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b; padding: 12px; } p { margin: 0 0 14px 0; } a { color: #2563eb !important; text-decoration: underline !important; font-weight: 500; } a:hover { color: #1d4ed8 !important; }',
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

    {{-- SUB-TAB 2: EMAIL TEMPLATES (OJS 3 REDESIGNED UI) --}}
    <div x-show="emailSubTab === 'templates'" x-cloak class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            {{-- OJS 3 HEADER --}}
            <div class="px-5 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 tracking-tight">{{ $isId ? 'Templat Surel' : 'Email Templates' }}</h3>
                </div>

                {{-- Action Cluster di Kanan (Search + Filters + Reset All) --}}
                <div class="flex items-center flex-wrap gap-2.5">
                    {{-- Search Box --}}
                    <div class="relative w-56 sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-search text-xs"></i>
                        </div>
                        <input type="text" x-model="searchQuery"
                            style="padding-left: 2rem !important;"
                            class="block w-full pr-7 py-1.5 border border-gray-300 rounded-md text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 transition-all shadow-sm"
                            placeholder="{{ $isId ? 'Cari templat...' : 'Search' }}">
                        <button x-show="searchQuery" @click="searchQuery = ''" type="button"
                            class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>

                    {{-- Filters Toggle Button (OJS 3 Navy Style) --}}
                    <button type="button" @click="showFilters = !showFilters"
                        :class="showFilters ? 'bg-[#006699] text-white border-[#006699]' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md border shadow-sm transition-colors cursor-pointer">
                        <i class="fa-solid fa-filter text-xs"></i>
                        <span>{{ $isId ? 'Filter' : 'Filters' }}</span>
                    </button>

                    {{-- Reset All Button (OJS 3 Red/Maroon Style) --}}
                    <button type="button"
                        onclick="submitForm('{{ route('journal.settings.workflow.email-templates.reset-all', ['journal' => $journalSlug]) }}', 'POST', '{{ $isId ? 'Apakah Anda yakin ingin mengatur ulang semua templat surel ke bawaan sistem?' : 'Are you sure you want to reset all email templates to system defaults?' }}')"
                        class="text-xs font-semibold text-[#a81010] hover:text-red-700 px-2 py-1.5 transition-colors cursor-pointer">
                        {{ $isId ? 'Atur Ulang Semua' : 'Reset All' }}
                    </button>
                </div>
            </div>

            {{-- 2-COLUMN OJS 3 BODY LAYOUT --}}
            <div class="flex flex-col md:flex-row min-h-[520px]">
                {{-- LEFT SIDEBAR: FILTERS --}}
                <aside x-show="showFilters" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
                    class="w-full md:w-56 flex-shrink-0 border-b md:border-b-0 md:border-r border-gray-200 p-5 bg-white space-y-4 select-none">
                    
                    <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                        <span class="font-bold text-gray-900 flex items-center gap-1.5 text-xs tracking-wider uppercase">
                            <i class="fa-solid fa-filter text-gray-500"></i>
                            {{ $isId ? 'Filter' : 'Filters' }}
                        </span>
                        <button x-show="hasActiveFilters()" @click="clearFilters()" type="button"
                            class="text-xs text-blue-600 hover:text-blue-800 hover:underline cursor-pointer">
                            {{ $isId ? 'Bersihkan' : 'Clear' }}
                        </button>
                    </div>

                    {{-- Category 1: Status --}}
                    <div class="space-y-1">
                        <button type="button" @click="filterStatus = (filterStatus === 'enabled' ? '' : 'enabled')"
                            :class="filterStatus === 'enabled' ? 'text-blue-600 font-bold' : 'text-blue-600 hover:underline'"
                            class="block text-left w-full text-xs py-0.5 transition-colors cursor-pointer">
                            Enabled
                        </button>
                        <button type="button" @click="filterStatus = (filterStatus === 'disabled' ? '' : 'disabled')"
                            :class="filterStatus === 'disabled' ? 'text-blue-600 font-bold' : 'text-blue-600 hover:underline'"
                            class="block text-left w-full text-xs py-0.5 transition-colors cursor-pointer">
                            Disabled
                        </button>
                        <button type="button" @click="filterStatus = (filterStatus === 'custom' ? '' : 'custom')"
                            :class="filterStatus === 'custom' ? 'text-blue-600 font-bold' : 'text-blue-600 hover:underline'"
                            class="block text-left w-full text-xs py-0.5 transition-colors cursor-pointer">
                            Custom Template
                        </button>
                    </div>

                    {{-- Category 2: Sent From --}}
                    <div class="space-y-1 pt-3 border-t border-gray-100">
                        <h5 class="text-xs font-bold text-gray-800 tracking-wide">Sent From</h5>
                        <div class="space-y-1">
                            @foreach (['Editor', 'Reviewer', 'Assistant', 'Reader'] as $from)
                                <button type="button" @click="filterSentFrom = (filterSentFrom === '{{ $from }}' ? '' : '{{ $from }}')"
                                    :class="filterSentFrom === '{{ $from }}' ? 'text-blue-600 font-bold' : 'text-blue-600 hover:underline'"
                                    class="block text-left w-full text-xs py-0.5 transition-colors cursor-pointer">
                                    {{ $from }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Category 3: Sent To --}}
                    <div class="space-y-1 pt-3 border-t border-gray-100">
                        <h5 class="text-xs font-bold text-gray-800 tracking-wide">Sent To</h5>
                        <div class="space-y-1">
                            @foreach (['Editor', 'Reviewer', 'Assistant', 'Author', 'Reader', 'Subscription Manager'] as $to)
                                <button type="button" @click="filterSentTo = (filterSentTo === '{{ $to }}' ? '' : '{{ $to }}')"
                                    :class="filterSentTo === '{{ $to }}' ? 'text-blue-600 font-bold' : 'text-blue-600 hover:underline'"
                                    class="block text-left w-full text-xs py-0.5 transition-colors cursor-pointer">
                                    {{ $to }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Category 4: Stage --}}
                    <div class="space-y-1 pt-3 border-t border-gray-100">
                        <h5 class="text-xs font-bold text-gray-800 tracking-wide">Stage</h5>
                        <div class="space-y-1">
                            @foreach (['Submission', 'Review', 'Copyediting', 'Production', 'Other'] as $stg)
                                <button type="button" @click="filterStage = (filterStage === '{{ $stg }}' ? '' : '{{ $stg }}')"
                                    :class="filterStage === '{{ $stg }}' ? 'text-blue-600 font-bold' : 'text-blue-600 hover:underline'"
                                    class="block text-left w-full text-xs py-0.5 transition-colors cursor-pointer">
                                    {{ $stg }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </aside>

                {{-- RIGHT MAIN CONTENT: TEMPLATE LIST --}}
                <main class="flex-1 bg-white p-4 sm:p-6 divide-y divide-gray-100 min-w-0">
                    {{-- Active Filter Indicators --}}
                    <div x-show="hasActiveFilters()" class="pb-3 flex items-center flex-wrap gap-2 text-xs">
                        <span class="text-gray-500 font-medium">{{ $isId ? 'Filter Aktif:' : 'Active Filters:' }}</span>
                        <template x-if="searchQuery">
                            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-xs">
                                "{{ searchQuery }}" <button type="button" @click="searchQuery = ''" class="cursor-pointer font-bold">×</button>
                            </span>
                        </template>
                        <template x-if="filterStatus">
                            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-xs capitalize">
                                Status: <span x-text="filterStatus"></span> <button type="button" @click="filterStatus = ''" class="cursor-pointer font-bold">×</button>
                            </span>
                        </template>
                        <template x-if="filterSentFrom">
                            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-xs">
                                From: <span x-text="filterSentFrom"></span> <button type="button" @click="filterSentFrom = ''" class="cursor-pointer font-bold">×</button>
                            </span>
                        </template>
                        <template x-if="filterSentTo">
                            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-xs">
                                To: <span x-text="filterSentTo"></span> <button type="button" @click="filterSentTo = ''" class="cursor-pointer font-bold">×</button>
                            </span>
                        </template>
                        <template x-if="filterStage">
                            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-xs">
                                Stage: <span x-text="filterStage"></span> <button type="button" @click="filterStage = ''" class="cursor-pointer font-bold">×</button>
                            </span>
                        </template>
                        <button type="button" @click="clearFilters()" class="text-xs text-red-600 hover:underline ml-2 cursor-pointer">
                            {{ $isId ? 'Hapus Semua' : 'Clear All' }}
                        </button>
                    </div>

                    @foreach ($emailTemplates as $template)
                        <div class="py-4 first:pt-0 last:pb-0"
                            x-data="{ 
                                template: {{ json_encode($template) }}, 
                                enabled: {{ $template->is_enabled ? 'true' : 'false' }} 
                            }"
                            x-show="matchesFilter(template)">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    {{-- OJS Badge Key Box --}}
                                    <div>
                                        <span class="inline-block px-1.5 py-0.5 text-[11px] font-mono font-medium text-blue-600 border border-blue-200 rounded uppercase bg-blue-50/40">
                                            {{ $template->key }}
                                        </span>
                                    </div>

                                    {{-- Template Name --}}
                                    <h4 @click="toggleExpand('{{ $template->id }}')"
                                        class="text-base font-bold text-gray-900 mt-1 cursor-pointer hover:text-blue-600 transition-colors">
                                        {{ $template->name }}
                                    </h4>

                                    {{-- Description --}}
                                    <p class="text-xs sm:text-sm text-gray-600 mt-0.5 leading-relaxed">
                                        {{ $template->description }}
                                    </p>
                                </div>

                                {{-- OJS Expand Chevron Button --}}
                                <div class="flex-shrink-0 flex items-center gap-2 pt-1">
                                    <button type="button" @click="toggleExpand('{{ $template->id }}')"
                                        class="w-7 h-7 flex items-center justify-center border border-gray-200 hover:border-gray-300 rounded hover:bg-gray-50 text-gray-500 transition-colors cursor-pointer"
                                        :title="isExpanded('{{ $template->id }}') ? 'Collapse' : 'Expand'">
                                        <i class="fa-solid text-xs transition-transform duration-200"
                                           :class="isExpanded('{{ $template->id }}') ? 'fa-chevron-up text-blue-600' : 'fa-chevron-down'"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Expanded Drawer Panel --}}
                            <div x-show="isExpanded('{{ $template->id }}')" x-collapse class="mt-3 pt-3 border-t border-gray-100 bg-gray-50/70 p-4 rounded-lg space-y-3">
                                {{-- Subject Preview --}}
                                <div class="text-xs">
                                    <span class="font-bold text-gray-700 uppercase tracking-wider text-[10px]">{{ $isId ? 'Subjek:' : 'Subject:' }}</span>
                                    <span class="text-gray-900 font-medium ml-1">{{ $template->subject }}</span>
                                </div>

                                {{-- Controls Row --}}
                                <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-200/60">
                                    {{-- Status Toggle Switch --}}
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                            @click="enabled = !enabled; updateTemplateStatus('{{ $template->id }}', enabled)"
                                            :class="enabled ? 'bg-emerald-500' : 'bg-gray-300'"
                                            class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                            role="switch"
                                            :aria-checked="enabled">
                                            <span :class="enabled ? 'translate-x-4' : 'translate-x-0'"
                                                class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out">
                                            </span>
                                        </button>
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                            :class="enabled ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                                            x-text="enabled ? '{{ $isId ? 'Aktif (ON)' : 'Enabled (ON)' }}' : '{{ $isId ? 'Nonaktif (OFF)' : 'Disabled (OFF)' }}'">
                                        </span>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex items-center gap-2">
                                        @if ($template->is_custom)
                                            <button type="button"
                                                onclick="submitForm('{{ route('journal.settings.workflow.email-templates.reset', ['journal' => $journalSlug, 'emailTemplate' => $template->id]) }}', 'POST', '{{ $isId ? 'Kembalikan templat ini ke konten bawaannya?' : 'Reset this template to its default content?' }}')"
                                                class="text-xs text-orange-600 hover:text-orange-900 bg-orange-50 hover:bg-orange-100 px-3 py-1.5 rounded font-medium transition-colors cursor-pointer">
                                                {{ $isId ? 'Atur Ulang Bawaan' : 'Reset to Default' }}
                                            </button>
                                        @endif

                                        <button type="button"
                                            data-template="{{ json_encode($template) }}"
                                            @click="editTemplate(JSON.parse($el.dataset.template))"
                                            class="text-xs text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded font-semibold transition-colors cursor-pointer flex items-center gap-1.5 border border-blue-200/60">
                                            <i class="fa-solid fa-pen text-[10px]"></i>
                                            <span>{{ $isId ? 'Ubah Templat' : 'Edit Template' }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </main>
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
                                            <button type="button" @click="insertVariable('{$editorName}')" title="{{ $isId ? 'Sisipkan {$editorName}' : 'Insert {$editorName}' }}" class="bg-white hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 border border-gray-200 px-2 py-1 rounded transition-colors cursor-pointer flex items-center gap-1.5 font-mono shadow-xs">
                                                <i class="fa-solid fa-plus text-[10px] text-indigo-500"></i> {$editorName}
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
