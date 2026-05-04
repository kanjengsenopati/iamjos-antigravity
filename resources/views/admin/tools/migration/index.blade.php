@extends(request()->routeIs('journal.settings.*') ? 'layouts.app' : 'layouts.admin')

@section('title', 'OJS Migration Dashboard')

@section('content')
<div x-data="migrationDashboard()">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">OJS Migration Dashboard</h1>
            <p class="mt-1 text-slate-500">Sync data from legacy MySQL OJS to IamJOS PostgreSQL.</p>
        </div>
        <div class="flex gap-3">
            <template x-if="config">
                <button @click="activeTab = 'dashboard'" 
                    class="px-5 py-2.5 rounded-xl font-medium transition-all"
                    :class="activeTab === 'dashboard' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-slate-600 border border-slate-200'">
                    Dashboard
                </button>
            </template>
            <button @click="activeTab = 'settings'" 
                class="px-5 py-2.5 rounded-xl font-medium transition-all"
                :class="activeTab === 'settings' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-slate-600 border border-slate-200'">
                Database Settings
            </button>
        </div>
    </div>

    @if($connectionError)
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm text-red-700 font-medium">Connection Error: {{ $connectionError }}</p>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto">
        
        <!-- Dashboard Tab -->
        <div x-show="activeTab === 'dashboard'" class="space-y-6" x-transition>
            @if($config)
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @foreach(['journals' => 'Journals', 'submissions' => 'Articles', 'authors' => 'Authors', 'metrics_downloads' => 'Downloads'] as $key => $label)
                <div class="bg-white p-6 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">{{ $label }}</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-slate-900">{{ number_format($stats[$key]['migrated_count'] ?? 0) }}</span>
                        <span class="text-xs text-slate-400 italic">of {{ number_format($stats[$key]['legacy_count'] ?? 0) }}</span>
                    </div>
                    <div class="mt-4 w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                        @php 
                            $percent = ($stats[$key]['legacy_count'] ?? 0) > 0 ? ($stats[$key]['migrated_count'] / $stats[$key]['legacy_count']) * 100 : 0;
                        @endphp
                        <div class="bg-emerald-500 h-full transition-all duration-1000" style="width: {{ min(100, $percent) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Migration Matrix -->
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50 overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Migration Matrix</h2>
                        <p class="text-sm text-slate-500">Audit trail for all migrated modules.</p>
                    </div>
                    <button @click="syncAll()" class="bg-slate-900 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-800 transition-all">
                        Deep Resync All
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400">Data Module</th>
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400 text-center">Legacy (MySQL)</th>
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400 text-center">Current (Postgre)</th>
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-400 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach([
                                'journals' => 'Journals & Settings',
                                'sections' => 'Sections & Taxonomy',
                                'issues' => 'Issues & Volumes',
                                'submissions' => 'Submissions & Metadata',
                                'authors' => 'Authors & Contributors',
                                'galleys' => 'Galleys & Physical Files',
                                'metrics_views' => 'Usage Metrics (Views)',
                                'metrics_downloads' => 'Usage Metrics (Downloads)'
                            ] as $key => $label)
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-semibold text-slate-700">{{ $label }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-mono text-slate-500">{{ number_format($stats[$key]['legacy_count'] ?? 0) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-mono text-emerald-600 font-bold">{{ number_format($stats[$key]['migrated_count'] ?? 0) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @php 
                                        $isComplete = ($stats[$key]['legacy_count'] ?? 0) > 0 && ($stats[$key]['migrated_count'] >= $stats[$key]['legacy_count']);
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isComplete ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $isComplete ? 'Synced' : 'Pending/Partial' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="runStep('{{ Str::before($key, '_') }}')" 
                                        class="text-blue-600 hover:text-blue-800 font-semibold text-sm disabled:opacity-50"
                                        :disabled="loadingStep === '{{ Str::before($key, '_') }}'">
                                        <span x-show="loadingStep !== '{{ Str::before($key, '_') }}'">Sync Step</span>
                                        <span x-show="loadingStep === '{{ Str::before($key, '_') }}'">Syncing...</span>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white p-12 rounded-[24px] text-center border border-dashed border-slate-300">
                <div class="mb-4 flex justify-center">
                    <div class="p-4 bg-blue-50 rounded-full">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-slate-800">No Legacy Connection Found</h3>
                <p class="text-slate-500 max-w-sm mx-auto mt-2">Please configure your remote MySQL connection settings to begin the migration process.</p>
                <button @click="activeTab = 'settings'" class="mt-6 bg-blue-600 text-white px-6 py-2.5 rounded-xl font-medium shadow-lg shadow-blue-200">
                    Go to Settings
                </button>
            </div>
            @endif
        </div>

        <!-- Settings Tab -->
        <div x-show="activeTab === 'settings'" class="max-w-2xl mx-auto" x-transition>
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50 overflow-hidden">
                <div class="p-6 border-b border-slate-50">
                    <h2 class="text-lg font-bold text-slate-800">Legacy MySQL Settings</h2>
                    <p class="text-sm text-slate-500">Provide credentials for the source OJS database.</p>
                </div>
                <form action="{{ route('admin.tools.migration.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    
                    @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
                        <ul class="list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2">Host</label>
                            <input type="text" name="host" value="{{ $config->host ?? 'localhost' }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2">Port</label>
                            <input type="text" name="port" value="{{ $config->port ?? '3306' }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2">Database Name</label>
                        <input type="text" name="database" value="{{ $config->database ?? '' }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2">Username</label>
                            <input type="text" name="username" value="{{ $config->username ?? 'root' }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2">Password</label>
                            <input type="password" name="password" placeholder="••••••••" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2">Legacy Base URL (Cloud Download)</label>
                        <input type="url" name="base_url" value="{{ $config->base_url ?? '' }}" placeholder="https://jurnal.univ.ac.id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        <p class="mt-1 text-[10px] text-slate-400 italic">Diperlukan untuk mengunduh PDF langsung dari server lama.</p>
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all">
                            Save Configuration
                        </button>
                    </div>
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
        activeTab: '{{ $config ? "dashboard" : "settings" }}',
        config: @json($config),
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
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .finally(() => {
                this.loadingStep = null;
            });
        },

        syncAll() {
            if (confirm('Jalankan Deep Resync untuk semua data? Ini mungkin memakan waktu.')) {
                // Logic for batch run
                const steps = ['journals', 'sections', 'issues', 'submissions', 'authors', 'galleys', 'metrics'];
                this.runSequence(steps);
            }
        },

        async runSequence(steps) {
            for (const step of steps) {
                this.loadingStep = step;
                await fetch('{{ route("admin.tools.migration.run") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ step: step })
                });
            }
            alert('Semua step berhasil dijalankan.');
            window.location.reload();
        }
    }
}
</script>
@endpush
