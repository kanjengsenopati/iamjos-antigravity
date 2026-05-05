@extends('layouts.admin')

@section('title', 'OJS SQL Migration')

@section('content')
<div x-data="migrationDashboard()">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">OJS SQL Migration</h1>
            <p class="mt-1 text-slate-500 text-sm">Transform legacy MySQL OJS dump into IamJOS PostgreSQL structure.</p>
        </div>
        <div class="flex gap-3">
            @if($config)
            <form action="{{ route('admin.tools.migration.reset') }}" method="POST" onsubmit="return confirm('Hapus file dan reset progres?')">
                @csrf
                <button type="submit" class="px-5 py-2.5 rounded-xl font-medium bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 transition-all text-sm">
                    Reset & Cleanup
                </button>
            </form>
            @endif
        </div>
    </div>

    @if($fileError)
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm text-red-700 font-medium">Error: {{ $fileError }}</p>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto" x-data="{ activeSetupTab: '{{ ($config && $config->database && $config->base_url) ? 'progress' : ($config && $config->database ? 'files' : 'sql') }}' }">
        
        <!-- Setup State -->
        <div class="max-w-4xl mx-auto" x-show="activeSetupTab !== 'progress'" x-cloak>
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50 overflow-hidden">
                <!-- Tabs Header -->
                <div class="flex border-b border-slate-50">
                    <button @click="activeSetupTab = 'sql'" 
                        :class="activeSetupTab === 'sql' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600'"
                        class="flex-1 py-4 text-sm font-bold border-b-2 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" /></svg>
                        1. Database Source (SQL)
                    </button>
                    <button @click="activeSetupTab = 'files'" 
                        :class="activeSetupTab === 'files' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600'"
                        class="flex-1 py-4 text-sm font-bold border-b-2 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                        2. File Assets (Local Path)
                    </button>
                </div>

                <!-- SQL Tab Content -->
                <div x-show="activeSetupTab === 'sql'" class="p-8">
                    @if($config && $config->database)
                            <div class="mb-6 p-4 bg-emerald-50 rounded-xl flex items-center justify-between border border-emerald-100">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span class="text-sm font-medium text-emerald-800">Database Ready: {{ $config->database }}</span>
                                </div>
                                <span class="text-[10px] bg-emerald-500 text-white px-2 py-0.5 rounded-full font-bold">ACTIVE</span>
                            </div>
                        @endif

                        <form action="{{ route('admin.tools.migration.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            <input type="hidden" name="type" value="sql">
                            <div class="border-2 border-dashed border-slate-200 rounded-[24px] p-12 text-center hover:border-blue-400 transition-all group cursor-pointer relative">
                                <input type="file" name="sql_file" class="absolute inset-0 opacity-0 cursor-pointer" @change="fileName = $event.target.files[0].name">
                                <div class="flex flex-col items-center">
                                    <div class="p-4 bg-blue-50 rounded-full mb-4 group-hover:bg-blue-100 transition-all">
                                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                    </div>
                                    <h3 class="text-slate-800 font-bold" x-text="fileName || 'Click or drag SQL file here'"></h3>
                                    <p class="text-slate-400 text-sm mt-1">Maximum file size: 512MB (.sql)</p>
                                </div>
                            </div>
                            <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold hover:bg-slate-800 transition-all">
                                Upload & Initialize SQL
                            </button>
                        </form>

                        @if($config && $config->database)
                            <div class="mt-6 border-t border-slate-100 pt-6 flex justify-end">
                                <button type="button" @click="activeSetupTab = 'files'" class="flex items-center gap-2 text-blue-600 font-bold hover:underline text-sm">
                                    Lanjut ke Konfigurasi File Assets
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Files Tab Content (Adopt WP File Manager Features) -->
                    <div x-show="activeSetupTab === 'files'" class="p-8" x-data="fileManager()">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-2 text-sm">
                                <template x-for="bc in breadcrumbs" :key="bc.path">
                                    <div class="flex items-center">
                                        <button @click="loadPath(bc.path)" class="text-slate-400 hover:text-blue-600 font-medium" x-text="bc.name"></button>
                                        <svg class="w-4 h-4 text-slate-300 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                                    </div>
                                </template>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="$refs.fileInput.click()" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Upload Files
                                </button>
                                <button @click="createFolderPrompt()" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    New Folder
                                </button>
                                <input type="file" x-ref="fileInput" @change="uploadFiles($event)" multiple class="hidden">
                            </div>
                        </div>

                        <!-- Explorer Grid -->
                        <div class="border border-slate-100 rounded-[24px] overflow-hidden bg-slate-50/50 min-h-[400px]">
                            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 p-4 gap-4" x-show="!loading">
                                <template x-for="item in items" :key="item.path">
                                    <div class="group relative bg-white p-4 rounded-2xl border border-transparent hover:border-blue-200 hover:shadow-sm transition-all cursor-pointer text-center"
                                        @click="item.type === 'dir' ? loadPath(item.path) : selectFile(item)">
                                        <div class="flex justify-center mb-2">
                                            <template x-if="item.type === 'dir'">
                                                <svg class="w-12 h-12 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                                            </template>
                                            <template x-if="item.type === 'file'">
                                                <div class="relative">
                                                    <svg class="w-12 h-12 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                                                    <span class="absolute bottom-1 right-1 text-[8px] font-bold text-white bg-blue-600 px-1 rounded" x-text="item.extension"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <p class="text-[11px] font-bold text-slate-700 truncate" x-text="item.name"></p>
                                        <p class="text-[9px] text-slate-400 mt-0.5" x-text="item.size"></p>
                                        
                                        <!-- Actions -->
                                        <button @click.stop="deleteItem(item)" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 p-1 text-red-400 hover:bg-red-50 rounded-lg transition-all">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <!-- Loading/Empty State -->
                            <div x-show="loading" class="flex items-center justify-center h-[400px]">
                                <svg class="animate-spin h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                            <div x-show="!loading && items.length === 0" class="flex flex-col items-center justify-center h-[400px] text-slate-400">
                                <svg class="w-16 h-16 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                <p class="text-sm font-medium">Folder ini kosong</p>
                            </div>
                        </div>

                        <!-- Footer Integration -->
                        <form action="{{ route('admin.tools.migration.upload') }}" method="POST" class="mt-8 pt-8 border-t border-slate-50 space-y-6">
                            @csrf
                            <input type="hidden" name="type" value="files">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2">Selected OJS Files Path</label>
                                <div class="flex gap-2">
                                    <input type="text" name="base_url" x-model="currentPath" placeholder="Click folder to select" class="flex-1 px-5 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-mono text-slate-600 outline-none" readonly>
                                    <button type="submit" class="bg-slate-900 text-white px-8 py-3 rounded-xl font-bold hover:bg-slate-800 transition-all text-sm">
                                        Use This Folder
                                    </button>
                                </div>
                                <p class="mt-2 text-[11px] text-slate-400 italic">Gunakan navigasi di atas untuk masuk ke direktori tempat file OJS berada.</p>
                            </div>
                        </form>
                        
                        @if($config && $config->database)
                            <div class="mt-6 border-t border-slate-100 pt-6 flex justify-between items-center">
                                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-widest">* Opsional jika tidak migrasi file PDF</span>
                                <button type="button" @click="activeSetupTab = 'progress'" class="flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition-all text-sm shadow-lg shadow-emerald-200">
                                    Buka Migration Dashboard
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        <script>
            function fileManager() {
                return {
                    items: [],
                    breadcrumbs: [],
                    currentPath: '{{ $config->base_url ?? "" }}',
                    loading: false,
                    init() {
                        this.loadPath(this.currentPath);
                    },
                    async loadPath(path) {
                        this.loading = true;
                        try {
                            const res = await fetch(`{{ route('admin.file-manager.list') }}?path=${path}`);
                            const data = await res.json();
                            this.items = data.items;
                            this.breadcrumbs = data.breadcrumbs;
                            this.currentPath = data.currentPath;
                        } catch (e) {
                            alert('Gagal memuat file');
                        } finally {
                            this.loading = false;
                        }
                    },
                    async uploadFiles(e) {
                        const files = e.target.files;
                        if (!files.length) return;

                        const formData = new FormData();
                        formData.append('path', this.currentPath);
                        for (let i = 0; i < files.length; i++) {
                            formData.append('files[]', files[i]);
                        }

                        this.loading = true;
                        try {
                            await fetch(`{{ route('admin.file-manager.upload') }}`, {
                                method: 'POST',
                                body: formData,
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                            });
                            this.loadPath(this.currentPath);
                        } catch (e) {
                            alert('Gagal mengunggah file');
                        } finally {
                            this.loading = false;
                        }
                    },
                    async createFolderPrompt() {
                        const name = prompt('Nama folder baru:');
                        if (!name) return;

                        try {
                            await fetch(`{{ route('admin.file-manager.create-folder') }}`, {
                                method: 'POST',
                                body: JSON.stringify({ path: this.currentPath, name }),
                                headers: { 
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                                }
                            });
                            this.loadPath(this.currentPath);
                        } catch (e) {
                            alert('Gagal membuat folder');
                        }
                    },
                    async deleteItem(item) {
                        if (!confirm(`Hapus ${item.name}?`)) return;

                        try {
                            await fetch(`{{ route('admin.file-manager.delete') }}`, {
                                method: 'POST',
                                body: JSON.stringify({ path: item.path }),
                                headers: { 
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                                }
                            });
                            this.loadPath(this.currentPath);
                        } catch (e) {
                            alert('Gagal menghapus');
                        }
                    },
                    selectFile(item) {
                        // For migration purposes, we usually select a folder, but we can handle file clicks too
                        console.log('Selected file:', item.path);
                    }
                }
            }
        </script>

        @if($config && $config->database)
        <!-- Migration Progress State -->
        <div x-show="activeSetupTab === 'progress'" class="space-y-6">
            <!-- File Info Card -->
            <div class="bg-white p-6 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Source: {{ $config->database }}</h3>
                        <p class="text-sm text-slate-500 italic">File successfully parsed and ready for transformation.</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Target Database</p>
                    <p class="text-sm font-bold text-emerald-600">PostgreSQL (Local)</p>
                </div>
            </div>

            <!-- Steps Grid -->
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50 overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Migration Matrix</h2>
                        <p class="text-sm text-slate-500">Step-by-step data transformation.</p>
                    </div>
                    <button @click="syncAll()" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
                        Run All Steps Sequence
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400">Phase</th>
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400">Description</th>
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400 text-center">Migrated</th>
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach([
                                'journals' => ['label' => 'Journals', 'desc' => 'Core journal profiles and settings'],
                                'sections' => ['label' => 'Sections', 'desc' => 'Journal taxonomies and abbreviations'],
                                'issues' => ['label' => 'Issues', 'desc' => 'Volumes, numbers, and cover info'],
                                'submissions' => ['label' => 'Articles', 'desc' => 'Full metadata and SEO content'],
                                'authors' => ['label' => 'Authors', 'desc' => 'Contributor roles and affiliations'],
                                'metrics' => ['label' => 'Statistics', 'desc' => 'Legacy usage and view counts'],
                                'galleys' => ['label' => 'Files', 'desc' => 'PDF downloads from legacy URL']
                            ] as $key => $meta)
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-slate-700">{{ $meta['label'] }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs text-slate-500">{{ $meta['desc'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-mono font-bold text-emerald-600">{{ number_format($stats[$key]['migrated_count'] ?? 0) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @php 
                                        $migratedCount = $stats[$key]['migrated_count'] ?? 0;
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $migratedCount > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $migratedCount > 0 ? 'Synced' : 'Waiting' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="runStep('{{ $key }}')" 
                                        class="text-blue-600 hover:text-blue-800 font-bold text-sm disabled:opacity-50 flex items-center justify-end gap-2 ml-auto"
                                        :disabled="loadingStep === '{{ $key }}'">
                                        <template x-if="loadingStep === '{{ $key }}'">
                                            <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </template>
                                        <span x-text="loadingStep === '{{ $key }}' ? 'Processing...' : 'Run Step'"></span>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
        <!-- Danger Zone -->
        <div class="mt-12 bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-red-100/50 overflow-hidden">
            <div class="p-6 border-b border-red-50 bg-red-50/30 flex items-center gap-3">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-red-900">Danger Zone</h2>
                    <p class="text-[11px] text-red-600/70 uppercase tracking-widest font-bold">Reset Migration Data</p>
                </div>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Reset Articles -->
                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 mb-1">Reset Articles</h3>
                    <p class="text-[11px] text-slate-500 mb-4">Hapus semua data Submission, Publication, dan Files dari database.</p>
                    <form action="{{ route('admin.tools.migration.reset-articles') }}" method="POST" onsubmit="return confirm('Hapus SEMUA data artikel?')">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-white border border-red-200 text-red-600 text-[11px] font-bold rounded-lg hover:bg-red-50 transition-all">
                            RESET ARTICLES
                        </button>
                    </form>
                </div>

                <!-- Reset Issues -->
                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 mb-1">Reset Issues</h3>
                    <p class="text-[11px] text-slate-500 mb-4">Hapus semua data Issues (Voli/Nomor) dari database.</p>
                    <form action="{{ route('admin.tools.migration.reset-issues') }}" method="POST" onsubmit="return confirm('Hapus SEMUA data issue?')">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-white border border-red-200 text-red-600 text-[11px] font-bold rounded-lg hover:bg-red-50 transition-all">
                            RESET ISSUES
                        </button>
                    </form>
                </div>

                <!-- Reset Journals -->
                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 mb-1">Reset Journals</h3>
                    <p class="text-[11px] text-slate-500 mb-4">Hapus semua data Jurnal, Sections, dan keterkaitannya (TOTAL RESET).</p>
                    <form action="{{ route('admin.tools.migration.reset-journals') }}" method="POST" onsubmit="return confirm('PERINGATAN: Ini akan menghapus SEMUA data jurnal. Lanjutkan?')">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-white border border-red-200 text-red-600 text-[11px] font-bold rounded-lg hover:bg-red-50 transition-all">
                            RESET JOURNALS
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function migrationDashboard() {
    return {
        fileName: '',
        loadingStep: null,

        runStep(step) {
            this.loadingStep = step;
            fetch('{{ route("admin.tools.migration.run") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ step: step })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(e => alert('Fatal Error: ' + e.message))
            .finally(() => {
                this.loadingStep = null;
            });
        },

        async syncAll() {
            if (confirm('Jalankan semua tahapan migrasi secara berurutan?')) {
                const steps = ['journals', 'sections', 'issues', 'submissions', 'authors', 'metrics', 'galleys'];
                for (const step of steps) {
                    this.loadingStep = step;
                    try {
                        const res = await fetch('{{ route("admin.tools.migration.run") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ step: step })
                        });
                        const data = await res.json();
                        if (!data.success) {
                            alert(`Gagal pada step ${step}: ${data.message}`);
                            break;
                        }
                    } catch (e) {
                        alert(`Fatal Error pada step ${step}: ${e.message}`);
                        break;
                    }
                }
                this.loadingStep = null;
                window.location.reload();
            }
        }
    }
}
</script>
@endpush
