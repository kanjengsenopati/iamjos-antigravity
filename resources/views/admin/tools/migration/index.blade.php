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
    <div class="max-w-7xl mx-auto" x-data="{ activeSetupTab: '{{ $config && $config->database ? 'progress' : 'sql' }}' }">
        
        <!-- Setup State -->
        <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50 overflow-hidden">
                <!-- Tabs Header -->
                <div class="flex border-b border-slate-50">
                    <button @click="activeSetupTab = 'sql'" 
                        :class="activeSetupTab === 'sql' ? 'border-blue-500 text-blue-600 bg-blue-50/30' : 'border-transparent text-slate-400 hover:text-slate-600'"
                        class="flex-1 py-4 text-xs font-bold border-b-2 transition-all flex items-center justify-center gap-2">
                        <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center text-[10px]" :class="activeSetupTab === 'sql' ? 'border-blue-500 bg-blue-500 text-white' : 'border-slate-200'">1</span>
                        Database Source
                    </button>
                    <button @click="activeSetupTab = 'progress'" 
                        :disabled="!{{ $config && $config->database ? 'true' : 'false' }}"
                        :class="activeSetupTab === 'progress' ? 'border-blue-500 text-blue-600 bg-blue-50/30' : 'border-transparent text-slate-400 hover:text-slate-600'"
                        class="flex-1 py-4 text-xs font-bold border-b-2 transition-all flex items-center justify-center gap-2 disabled:opacity-30">
                        <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center text-[10px]" :class="activeSetupTab === 'progress' ? 'border-blue-500 bg-blue-500 text-white' : 'border-slate-200'">2</span>
                        Migration Sync
                    </button>
                    <button @click="activeSetupTab = 'files'" 
                        :disabled="!{{ $config && $config->database ? 'true' : 'false' }}"
                        :class="activeSetupTab === 'files' ? 'border-blue-500 text-blue-600 bg-blue-50/30' : 'border-transparent text-slate-400 hover:text-slate-600'"
                        class="flex-1 py-4 text-xs font-bold border-b-2 transition-all flex items-center justify-center gap-2 disabled:opacity-30">
                        <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center text-[10px]" :class="activeSetupTab === 'files' ? 'border-blue-500 bg-blue-500 text-white' : 'border-slate-200'">3</span>
                        Fill Assets
                    </button>
                </div>

                <!-- SQL Tab Content -->
                <div x-show="activeSetupTab === 'sql'" class="p-8" x-data="{ mode: '{{ $config && $config->connection_name === 'ojs_legacy' ? 'db' : 'file' }}' }">
                    
                    <div class="flex p-1 bg-slate-100 rounded-xl w-max mb-8">
                        <button @click="mode = 'db'" 
                            :class="mode === 'db' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            class="px-6 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                            Direct Database (Fast)
                        </button>
                        <button @click="mode = 'file'" 
                            :class="mode === 'file' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            class="px-6 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            SQL File (ETL)
                        </button>
                    </div>

                    @if($config && $config->database)
                        <div class="mb-6 p-4 bg-emerald-50 rounded-xl flex items-center justify-between border border-emerald-100">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="text-sm font-medium text-emerald-800">Source Ready: {{ $config->database }}</span>
                            </div>
                            <span class="text-[10px] bg-emerald-500 text-white px-2 py-0.5 rounded-full font-bold">ACTIVE</span>
                        </div>
                    @endif

                    <!-- Database Form -->
                    <div x-show="mode === 'db'" x-transition>
                        <form action="{{ route('admin.tools.migration.upload') }}" method="POST" class="space-y-6">
                            @csrf
                            <input type="hidden" name="type" value="database">
                            
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-500 mb-2">Host</label>
                                    <input type="text" name="host" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-100 outline-none transition-all" placeholder="127.0.0.1" value="{{ $config?->host ?? '127.0.0.1' }}" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-500 mb-2">Port</label>
                                    <input type="number" name="port" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-100 outline-none transition-all" placeholder="3306" value="{{ $config?->port ?? '3306' }}" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-500 mb-2">Database Name</label>
                                    <input type="text" name="database" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-100 outline-none transition-all" placeholder="ojs_legacy_db" value="{{ ($config?->connection_name === 'ojs_legacy') ? $config->database : '' }}" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-500 mb-2">Database User</label>
                                    <input type="text" name="username" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-100 outline-none transition-all" placeholder="root" value="{{ $config?->username ?? 'root' }}" required>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-500 mb-2">Database Password</label>
                                    <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-100 outline-none transition-all" placeholder="Leave empty if none">
                                </div>
                            </div>
                            
                            <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold hover:bg-slate-800 transition-all flex justify-center items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                Connect & Initialize
                            </button>
                        </form>
                    </div>

                    <!-- File Form -->
                    <div x-show="mode === 'file'" x-transition style="display: none;">
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
                    </div>

                    @if($config && $config->database)
                        <div class="mt-6 border-t border-slate-100 pt-6 flex justify-end">
                            <button type="button" @click="activeSetupTab = 'progress'" class="flex items-center gap-2 text-blue-600 font-bold hover:underline text-sm">
                                Lanjut ke Migration Dashboard
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

            <!-- SQL Data Preview -->
            @if(!empty($previewData))
             <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50 overflow-hidden mb-8">
                <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">SQL Data Preview</h2>
                        <p class="text-sm text-slate-500">Pilih jurnal yang ingin di-import. Biarkan kosong untuk meng-import SEMUA.</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 border-y border-slate-100">
                                <th class="px-6 py-3 w-10">
                                    <input type="checkbox" @change="toggleAllSource($event)" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </th>
                                <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">Nama Jurnal (SQL)</th>
                                <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 text-center">Jml Section</th>
                                <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 text-center">Jml Issue</th>
                                <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 text-center">Jml Article</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($previewData as $p)
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <input type="checkbox" :value="'{{ $p['id'] }}'" x-model="selectedSourceJournals" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-slate-800">{{ $p['name'] }}</span>
                                    <br><span class="text-xs font-mono text-slate-400">{{ $p['path'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-mono font-bold text-blue-600">{{ $p['sections_count'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-mono font-bold text-emerald-600">{{ $p['issues_count'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-mono font-bold text-amber-600">{{ $p['articles_count'] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Steps Grid (Migration Matrix) -->
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50 overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Migration Matrix (Sync)</h2>
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
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400 text-center">
                                    <span class="flex items-center justify-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span> Legacy (OJS)
                                    </span>
                                </th>
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400 text-center">
                                    <span class="flex items-center justify-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span> Migrated
                                    </span>
                                </th>
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400 text-center">
                                    <span class="flex items-center justify-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-slate-300 inline-block"></span> IamJOS
                                    </span>
                                </th>
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach([
                                'users'       => ['label' => 'Users & Roles',    'desc' => 'Accounts, passwords (default), and group roles', 'step' => 'users'],
                                'journals'    => ['label' => 'Journals',          'desc' => 'Core journal profiles and settings',              'step' => 'journals'],
                                'sections'    => ['label' => 'Sections',          'desc' => 'Journal taxonomies and abbreviations',             'step' => 'sections'],
                                'issues'      => ['label' => 'Issues',            'desc' => 'Volumes, numbers, and cover info',                 'step' => 'issues'],
                                'submissions' => ['label' => 'Articles',          'desc' => 'All submissions (published, review, etc)',          'step' => 'submissions'],
                                'authors'     => ['label' => 'Authors',           'desc' => 'Contributor roles and affiliations',               'step' => 'authors'],
                                'reviews'     => ['label' => 'Review Workflow',   'desc' => 'Review assignments, rounds, and decisions',        'step' => 'reviews'],
                                'discussions' => ['label' => 'Discussions',       'desc' => 'Queries, notes, and messages',                     'step' => 'discussions'],
                                'logs'        => ['label' => 'Event Logs',        'desc' => 'System audit logs and email history',              'step' => 'logs'],
                                'metrics'     => ['label' => 'Statistics',        'desc' => 'Legacy usage and view counts',                     'step' => 'metrics'],
                                'galleys'     => ['label' => 'Files & Galleys',   'desc' => 'All submission files and galleys',                 'step' => 'galleys'],
                            ] as $key => $meta)
                            @php
                                $legacyCount   = $stats[$key]['legacy_count']   ?? '—';
                                $migratedCount = $stats[$key]['migrated_count'] ?? 0;
                                $nativeCount   = $stats[$key]['native_count']   ?? 0;
                                $isSynced      = $migratedCount > 0;
                            @endphp
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-slate-700">{{ $meta['label'] }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs text-slate-500">{{ $meta['desc'] }}</span>
                                </td>
                                {{-- Pillar 1: Legacy (OJS Source) --}}
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-mono font-bold text-blue-600">
                                        {{ is_numeric($legacyCount) ? number_format($legacyCount) : $legacyCount }}
                                    </span>
                                </td>
                                {{-- Pillar 2: Migrated (via LegacyMapping) --}}
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-mono font-bold {{ $isSynced ? 'text-emerald-600' : 'text-slate-300' }}">
                                        {{ number_format($migratedCount) }}
                                    </span>
                                </td>
                                {{-- Pillar 3: IamJOS (existing, non-migrated) --}}
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-mono font-bold {{ $nativeCount > 0 ? 'text-amber-500' : 'text-slate-300' }}">
                                        {{ number_format($nativeCount) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="runStep('{{ $meta['step'] }}')" 
                                        class="text-blue-600 hover:text-blue-800 font-bold text-sm disabled:opacity-50 flex items-center justify-end gap-2 ml-auto"
                                        :disabled="loadingStep === '{{ $meta['step'] }}'">
                                        <template x-if="loadingStep === '{{ $meta['step'] }}'">
                                            <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </template>
                                        <span x-text="loadingStep === '{{ $meta['step'] }}' ? 'Processing...' : 'Run Step'"></span>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Journal Integrity Panel -->
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50 overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Journal Integrity Check</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Verifikasi data per-jurnal: issues dan artikel yang berhasil disinkronisasi.</p>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <!-- Bulk Action Dropdown -->
                        <div x-show="selectedTargetJournals.length > 0" x-cloak x-transition class="flex items-center gap-2 bg-red-50 px-4 py-2 rounded-xl border border-red-100">
                            <span class="text-[10px] font-bold text-red-600 uppercase tracking-widest"><span x-text="selectedTargetJournals.length"></span> Selected</span>
                            <div class="h-4 w-px bg-red-200"></div>
                            <form action="{{ route('admin.tools.migration.reset-articles') }}" method="POST" class="inline" onsubmit="return confirm('Hapus artikel pada jurnal terpilih?')">
                                @csrf
                                <template x-for="id in selectedTargetJournals">
                                    <input type="hidden" name="journal_ids[]" :value="id">
                                </template>
                                <button type="submit" class="text-[10px] font-bold text-red-600 hover:underline">Reset Articles</button>
                            </form>
                            <form action="{{ route('admin.tools.migration.reset-issues') }}" method="POST" class="inline" onsubmit="return confirm('Hapus issue pada jurnal terpilih?')">
                                @csrf
                                <template x-for="id in selectedTargetJournals">
                                    <input type="hidden" name="journal_ids[]" :value="id">
                                </template>
                                <button type="submit" class="text-[10px] font-bold text-red-600 hover:underline ml-2">Reset Issues</button>
                            </form>
                            <form action="{{ route('admin.tools.migration.reset-journals') }}" method="POST" class="inline" onsubmit="return confirm('Hapus seluruh data (Jurnal, Section, Issue, Artikel) pada jurnal terpilih?')">
                                @csrf
                                <template x-for="id in selectedTargetJournals">
                                    <input type="hidden" name="journal_ids[]" :value="id">
                                </template>
                                <button type="submit" class="text-[10px] font-bold text-red-600 hover:underline ml-2">Reset Everything</button>
                            </form>
                        </div>

                        <div class="flex items-center gap-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Complete</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Partial</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400"></span> Empty</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span> Native</span>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-4 py-3 w-10">
                                    <input type="checkbox" @click="toggleAllTargets()" :checked="selectedTargetJournals.length === allTargets.length && allTargets.length > 0" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                </th>
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">Journal Name</th>
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">Abbrev</th>
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">Path</th>
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 text-center">Issues</th>
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 text-center">Articles</th>
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 text-center">Status</th>
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($journalBreakdown as $j)
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="px-4 py-3">
                                    <input type="checkbox" value="{{ $j['id'] }}" x-model="selectedTargetJournals" class="rounded border-slate-300 text-red-600 focus:ring-red-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if($j['integrity'] === 'complete')
                                            <span class="block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                        @elseif($j['integrity'] === 'partial')
                                            <span class="block w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                        @elseif($j['integrity'] === 'empty')
                                            <span class="block w-2.5 h-2.5 rounded-full bg-red-400"></span>
                                        @else
                                            <span class="block w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                                        @endif
                                        <span class="text-sm font-bold text-slate-800">{{ $j['name'] }}</span>
                                    </div>
                                    @if(!$j['enabled'])
                                        <span class="ml-2 text-[9px] bg-red-50 text-red-400 font-bold uppercase px-1.5 py-0.5 rounded-full">Disabled</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-mono font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">{{ $j['abbreviation'] }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-mono text-slate-400">{{ $j['path'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm font-mono font-bold {{ $j['issues_count'] > 0 ? 'text-emerald-600' : 'text-red-400' }}">
                                        {{ $j['issues_count'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm font-mono font-bold {{ $j['articles_count'] > 0 ? 'text-emerald-600' : 'text-red-400' }}">
                                        {{ $j['articles_count'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($j['integrity'] === 'complete')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            Complete
                                        </span>
                                    @elseif($j['integrity'] === 'partial')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                                            Partial
                                        </span>
                                    @elseif($j['integrity'] === 'empty')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Empty
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                                            Native
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="showJournalDetails('{{ $j['id'] }}')" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        @if($j['is_migrated'])
                                            <form action="{{ route('admin.tools.migration.reset-journal', $j['id']) }}" method="POST" onsubmit="return confirm('Hapus semua data migrated untuk jurnal \'{{ $j['name'] }}\'? Artikel dan issue akan dihapus permanen.')">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Reset All">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-400 text-sm">Belum ada journal yang terdaftar.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @php
                    $emptyJournals  = collect($journalBreakdown)->where('integrity', 'empty')->count();
                    $partialJournals = collect($journalBreakdown)->where('integrity', 'partial')->count();
                @endphp
                @if($emptyJournals > 0 || $partialJournals > 0)
                <div class="p-4 bg-amber-50 border-t border-amber-100 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-xs text-amber-800 font-medium leading-relaxed">
                        <strong>Perhatian:</strong> Terdapat <strong>{{ $emptyJournals }} jurnal Empty</strong> dan <strong>{{ $partialJournals }} jurnal Partial</strong>.
                        Kemungkinan jurnal tersebut tidak memiliki data di OJS dump, atau terjadi kegagalan migrasi parsial.
                        Coba jalankan ulang <em>"Run Step → Issues"</em> dan <em>"Run Step → Articles"</em>.
                    </p>
                </div>
                @endif
            </div>

            <!-- Sync Summary Footer -->
            <div class="bg-emerald-900 text-white p-8 rounded-[24px] flex items-center justify-between shadow-xl shadow-emerald-100">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-white/10 rounded-2xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Migration Sync Complete?</h3>
                        <p class="text-emerald-100 text-sm opacity-80">Pastikan semua data di atas sudah sinkron sebelum mengisi aset file.</p>
                    </div>
                </div>
                <button @click="activeSetupTab = 'files'" class="bg-white text-emerald-900 px-8 py-3 rounded-xl font-bold hover:bg-emerald-50 transition-all shadow-lg">
                    Next: Fill Assets (Tab 3)
                </button>
            </div>
        </div>
        @endif
    </div>
        <!-- Danger Zone -->
        <div class="mt-12 bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-red-100/50 overflow-hidden">
            <div class="p-6 border-b border-red-50 bg-red-50/30 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-red-900">Danger Zone — Reset Migration Data</h2>
                        <p class="text-[11px] text-red-500/80 mt-0.5">Centang jurnal di tabel atas, atau biarkan kosong untuk reset semua jurnal.</p>
                    </div>
                </div>
                <!-- Selection indicator -->
                <div x-show="selectedTargetJournals.length > 0" x-cloak class="flex items-center gap-2 bg-amber-50 border border-amber-200 px-4 py-2 rounded-xl">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs font-bold text-amber-700"><span x-text="selectedTargetJournals.length"></span> jurnal dipilih — reset hanya pada jurnal ini</span>
                </div>
                <div x-show="selectedTargetJournals.length === 0" class="flex items-center gap-2 bg-red-50 border border-red-200 px-4 py-2 rounded-xl">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs font-bold text-red-600">Tidak ada jurnal dipilih — akan reset SEMUA</span>
                </div>
            </div>

            <!-- Journal quick-select chips -->
            <div class="px-8 pt-6 pb-2">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3">Pilih Jurnal Target (opsional):</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($journalBreakdown as $j)
                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border cursor-pointer transition-all text-[11px] font-bold select-none"
                           :class="selectedTargetJournals.includes('{{ $j['id'] }}') ? 'bg-red-600 text-white border-red-600' : 'bg-white text-slate-500 border-slate-200 hover:border-red-300 hover:text-red-600'">
                        <input type="checkbox" value="{{ $j['id'] }}" x-model="selectedTargetJournals" class="sr-only">
                        <span class="w-1.5 h-1.5 rounded-full @if($j['integrity'] === 'complete') bg-emerald-400 @elseif($j['integrity'] === 'partial') bg-amber-400 @else bg-red-300 @endif"
                              :class="selectedTargetJournals.includes('{{ $j['id'] }}') ? 'opacity-100' : ''"></span>
                        {{ $j['abbreviation'] ?: $j['name'] }}
                    </label>
                    @endforeach
                    <button type="button" @click="selectedTargetJournals = []" x-show="selectedTargetJournals.length > 0" x-cloak
                            class="px-3 py-1.5 rounded-full border border-slate-200 text-[11px] font-bold text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all">
                        Hapus Pilihan
                    </button>
                </div>
            </div>

            <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Reset Articles -->
                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 mb-1">Reset: Articles</h3>
                    <p class="text-[11px] text-slate-500 mb-4">Hapus data Submission & Publication hasil migrasi.</p>
                    <form action="{{ route('admin.tools.migration.reset-articles') }}" method="POST"
                          @submit.prevent="if(confirm(selectedTargetJournals.length > 0 ? `Hapus artikel pada ${selectedTargetJournals.length} jurnal terpilih?` : 'Hapus SEMUA artikel hasil migrasi dari database?')) { $el.submit(); }">
                        @csrf
                        <template x-for="id in selectedTargetJournals" :key="id">
                            <input type="hidden" name="journal_ids[]" :value="id">
                        </template>
                        <button type="submit" class="w-full py-2.5 bg-white border border-red-200 text-red-600 text-[11px] font-bold rounded-lg hover:bg-red-50 transition-all flex items-center justify-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span x-text="selectedTargetJournals.length > 0 ? `Reset Articles (${selectedTargetJournals.length} Jurnal)` : 'Reset ALL Articles'"></span>
                        </button>
                    </form>
                </div>

                <!-- Reset Issues -->
                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 mb-1">Reset: Issues</h3>
                    <p class="text-[11px] text-slate-500 mb-4">Hapus data Issues (Vol/Nomor) hasil migrasi.</p>
                    <form action="{{ route('admin.tools.migration.reset-issues') }}" method="POST"
                          @submit.prevent="if(confirm(selectedTargetJournals.length > 0 ? `Hapus issue pada ${selectedTargetJournals.length} jurnal terpilih?` : 'Hapus SEMUA data issue hasil migrasi?')) { $el.submit(); }">
                        @csrf
                        <template x-for="id in selectedTargetJournals" :key="id">
                            <input type="hidden" name="journal_ids[]" :value="id">
                        </template>
                        <button type="submit" class="w-full py-2.5 bg-white border border-red-200 text-red-600 text-[11px] font-bold rounded-lg hover:bg-red-50 transition-all flex items-center justify-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span x-text="selectedTargetJournals.length > 0 ? `Reset Issues (${selectedTargetJournals.length} Jurnal)` : 'Reset ALL Issues'"></span>
                        </button>
                    </form>
                </div>

                <!-- Reset Journals -->
                <div class="p-6 bg-slate-50 rounded-2xl border border-red-100">
                    <h3 class="text-sm font-bold text-red-800 mb-1">Reset: Journals (Total)</h3>
                    <p class="text-[11px] text-slate-500 mb-4">Hapus data Jurnal, Section, Issue & Artikel sekaligus.</p>
                    <form action="{{ route('admin.tools.migration.reset-journals') }}" method="POST"
                          @submit.prevent="if(confirm(selectedTargetJournals.length > 0 ? `HAPUS TOTAL data pada ${selectedTargetJournals.length} jurnal terpilih?` : 'PERINGATAN: Ini akan menghapus SELURUH data jurnal hasil migrasi. Lanjutkan?')) { $el.submit(); }">
                        @csrf
                        <template x-for="id in selectedTargetJournals" :key="id">
                            <input type="hidden" name="journal_ids[]" :value="id">
                        </template>
                        <button type="submit" class="w-full py-2.5 bg-red-600 text-white text-[11px] font-bold rounded-lg hover:bg-red-700 transition-all flex items-center justify-center gap-2 shadow-md shadow-red-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span x-text="selectedTargetJournals.length > 0 ? `Total Reset (${selectedTargetJournals.length} Jurnal)` : 'TOTAL RESET SEMUA'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Drilldown Modal -->
    <div x-show="detailsModal" 
         x-cloak 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        
        <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-4xl max-h-[85vh] overflow-hidden flex flex-col" @click.away="detailsModal = false">
            <!-- Modal Header -->
            <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h2 class="text-xl font-bold text-slate-900" x-text="detailJournal.name"></h2>
                    <p class="text-sm text-slate-500 mt-1">Select specific issues or articles to reset data.</p>
                </div>
                <button @click="detailsModal = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-white rounded-full transition-all shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Tabs -->
            <div class="flex border-b border-slate-100 bg-white">
                <button @click="activeTab = 'issues'" 
                        :class="activeTab === 'issues' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600'"
                        class="px-8 py-4 text-sm font-bold border-b-2 transition-all">
                    Issues (<span x-text="detailData.issues.length"></span>)
                </button>
                <button @click="activeTab = 'articles'" 
                        :class="activeTab === 'articles' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600'"
                        class="px-8 py-4 text-sm font-bold border-b-2 transition-all">
                    Articles (<span x-text="detailData.articles.length"></span>)
                </button>
            </div>

            <!-- Modal Content -->
            <div class="flex-1 overflow-y-auto p-8 bg-slate-50/30">
                <div x-show="detailLoading" class="flex flex-col items-center justify-center py-20">
                    <svg class="animate-spin h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <p class="text-sm text-slate-400 mt-4 font-medium">Fetching details...</p>
                </div>

                <div x-show="!detailLoading">
                    <!-- Issues List -->
                    <div x-show="activeTab === 'issues'">
                        <div class="space-y-3">
                            <template x-for="issue in detailData.issues" :key="issue.id">
                                <label :class="issue.is_migrated ? 'bg-white border-slate-200' : 'bg-slate-100 opacity-50 cursor-not-allowed'" 
                                       class="flex items-center p-4 rounded-2xl border transition-all hover:shadow-md cursor-pointer group">
                                    <input type="checkbox" 
                                           :value="issue.id" 
                                           x-model="selectedIssues" 
                                           :disabled="!issue.is_migrated"
                                           class="w-5 h-5 rounded border-slate-300 text-red-600 focus:ring-red-500 transition-all">
                                    <div class="ml-4 flex-1">
                                        <p class="text-sm font-bold text-slate-800" x-text="issue.title"></p>
                                        <p class="text-[10px] uppercase tracking-widest font-bold mt-0.5" 
                                           :class="issue.is_migrated ? 'text-emerald-500' : 'text-slate-400'"
                                           x-text="issue.is_migrated ? 'Migrated' : 'Native'"></p>
                                    </div>
                                    <svg x-show="issue.is_migrated" class="w-5 h-5 text-slate-300 group-hover:text-red-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- Articles List -->
                    <div x-show="activeTab === 'articles'">
                        <div class="space-y-3">
                            <template x-for="article in detailData.articles" :key="article.id">
                                <label :class="article.is_migrated ? 'bg-white border-slate-200' : 'bg-slate-100 opacity-50 cursor-not-allowed'" 
                                       class="flex items-center p-4 rounded-2xl border transition-all hover:shadow-md cursor-pointer group">
                                    <input type="checkbox" 
                                           :value="article.id" 
                                           x-model="selectedArticles" 
                                           :disabled="!article.is_migrated"
                                           class="w-5 h-5 rounded border-slate-300 text-red-600 focus:ring-red-500 transition-all">
                                    <div class="ml-4 flex-1">
                                        <p class="text-sm font-bold text-slate-800 line-clamp-1" x-text="article.title"></p>
                                        <p class="text-[10px] uppercase tracking-widest font-bold mt-0.5" 
                                           :class="article.is_migrated ? 'text-emerald-500' : 'text-slate-400'"
                                           x-text="article.is_migrated ? 'Migrated' : 'Native'"></p>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-8 border-t border-slate-100 bg-white flex items-center justify-between">
                <p class="text-xs text-slate-400 font-medium">
                    <span x-text="activeTab === 'issues' ? selectedIssues.length : selectedArticles.length"></span> items selected for deletion
                </p>
                <div class="flex gap-3">
                    <button @click="detailsModal = false" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded-xl transition-all">Cancel</button>
                    <button @click="resetSelectedItems()" 
                            :disabled="detailLoading || (activeTab === 'issues' ? selectedIssues.length === 0 : selectedArticles.length === 0)"
                            class="px-8 py-2.5 bg-red-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-red-200 hover:bg-red-700 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:translate-y-0 disabled:shadow-none">
                        Reset Selected
                    </button>
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
        selectedSourceJournals: [], // Selected journal IDs from SQL source
        
        // Target Reset State (for already migrated journals)
        selectedTargetJournals: [],
        allTargets: {!! json_encode(collect($journalBreakdown)->pluck('id')) !!},
        toggleAllTargets() {
            this.selectedTargetJournals = this.selectedTargetJournals.length === this.allTargets.length ? [] : [...this.allTargets];
        },

        // Drilldown Modal State
        detailsModal: false,
        detailLoading: false,
        detailJournal: { id: null, name: '' },
        activeTab: 'issues',
        detailData: { issues: [], articles: [] },
        selectedIssues: [],
        selectedArticles: [],

        showJournalDetails(journalId) {
            this.detailsModal = true;
            this.detailLoading = true;
            this.detailJournal.id = journalId;
            this.selectedIssues = [];
            this.selectedArticles = [];
            
            fetch(`{{ url('admin/tools/migration/details') }}/${journalId}`, {
                headers: this.getHeaders()
            })
            .then(res => res.json())
            .then(data => {
                this.detailJournal.name = data.journal.name;
                this.detailData.issues = data.issues;
                this.detailData.articles = data.articles;
                this.detailLoading = false;
            })
            .catch(e => {
                alert('Gagal mengambil detail: ' + e.message);
                this.detailsModal = false;
            });
        },

        resetSelectedItems() {
            const type = this.activeTab;
            const ids = type === 'issues' ? this.selectedIssues : this.selectedArticles;

            if (!confirm(`Hapus ${ids.length} item ${type} terpilih? Tindakan ini tidak dapat dibatalkan.`)) return;

            this.detailLoading = true;
            fetch('{{ route("admin.tools.migration.reset-items") }}', {
                method: 'POST',
                headers: this.getHeaders(),
                body: JSON.stringify({ type: type, ids: ids })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Refetch details to update list
                    this.showJournalDetails(this.detailJournal.id);
                } else {
                    alert('Gagal menghapus: ' + data.message);
                    this.detailLoading = false;
                }
            })
            .catch(e => {
                alert('Fatal Error: ' + e.message);
                this.detailLoading = false;
            });
        },

        toggleAllSource(event) {
            const ids = {!! json_encode(collect($previewData)->pluck('id')) !!};
            this.selectedSourceJournals = event.target.checked ? ids : [];
        },

        // Standard headers to always force JSON response from Laravel
        getHeaders() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            };
        },

        runStep(step) {
            this.loadingStep = step;
            fetch('{{ route("admin.tools.migration.run") }}', {
                method: 'POST',
                headers: this.getHeaders(),
                body: JSON.stringify({ 
                    step: step,
                    journal_ids: this.selectedSourceJournals // Pass the filter
                })
            })
            .then(res => {
                if (!res.ok && res.headers.get('content-type')?.includes('application/json') === false) {
                    throw new Error(`Server returned ${res.status}: Possible PHP error. Check Laravel logs.`);
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Migration Error:\n\n' + data.message);
                    this.loadingStep = null;
                }
            })
            .catch(e => {
                alert('Fatal Error: ' + e.message);
                this.loadingStep = null;
            });
        },

        async syncAll() {
            if (confirm('Jalankan semua tahapan migrasi secara berurutan?')) {
                const steps = ['journals', 'sections', 'issues', 'submissions', 'authors', 'metrics'];
                for (const step of steps) {
                    this.loadingStep = step;
                    try {
                        const res = await fetch('{{ route("admin.tools.migration.run") }}', {
                            method: 'POST',
                            headers: this.getHeaders(),
                            body: JSON.stringify({ 
                                step: step,
                                journal_ids: this.selectedSourceJournals
                            })
                        });
                        if (!res.ok && !res.headers.get('content-type')?.includes('application/json')) {
                            alert(`Server error pada step ${step}. Lihat Laravel log untuk detail.`);
                            break;
                        }
                        const data = await res.json();
                        if (!data.success) {
                            alert(`Gagal pada step ${step}:\n\n${data.message}`);
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
