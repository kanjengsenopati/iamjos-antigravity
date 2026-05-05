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
    <div class="max-w-7xl mx-auto">
        @if(!$config)
        <!-- Upload State -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50 overflow-hidden">
                <div class="p-8 border-b border-slate-50">
                    <h2 class="text-lg font-bold text-slate-800">1. Upload SQL Dump</h2>
                    <p class="text-sm text-slate-500">Unggah file dump (.sql) dari database MySQL OJS Anda.</p>
                </div>
                <form action="{{ route('admin.tools.migration.upload') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                    @csrf
                    <div class="border-2 border-dashed border-slate-200 rounded-[24px] p-12 text-center hover:border-blue-400 transition-all group cursor-pointer relative">
                        <input type="file" name="sql_file" class="absolute inset-0 opacity-0 cursor-pointer" @change="fileName = $event.target.files[0].name">
                        <div class="flex flex-col items-center">
                            <div class="p-4 bg-blue-50 rounded-full mb-4 group-hover:bg-blue-100 transition-all">
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <h3 class="text-slate-800 font-bold" x-text="fileName || 'Click or drag SQL file here'"></h3>
                            <p class="text-slate-400 text-sm mt-1">Maximum file size: 512MB (.sql, .sql.gz)</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2">OJS Files Path (Relative to Project Root)</label>
                        <input type="text" name="base_url" placeholder="storage/app/migrations/files" class="w-full px-5 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm">
                        <p class="mt-2 text-[11px] text-slate-400 italic">Lokasi folder files OJS yang sudah diunggah ke server. Digunakan untuk migrasi PDF secara lokal.</p>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all flex justify-center items-center gap-2">
                        <span>Initialize Migration</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </form>
            </div>
        </div>
        @else
        <!-- Migration Progress State -->
        <div class="space-y-6">
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
