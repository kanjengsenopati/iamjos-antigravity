@extends('layouts.journal')

@section('title', 'COUNTER R5 Statistics')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">COUNTER R5 Statistics</h1>
            <p class="mt-1 text-sm text-gray-500">
                Laporan statistik penggunaan sesuai standar COUNTER Release 5.
                Digunakan untuk pelaporan ke Scopus, Web of Science, dan agregator akademik.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('journal.settings.statistics.counter.ir.csv', ['journal' => $journal->slug, 'begin_date' => $beginDate, 'end_date' => $endDate]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">
                <i class="fa-solid fa-file-csv"></i>
                Export CSV
            </a>
        </div>
    </div>

    {{-- Date Range Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6" x-data="counterStats()">
        <form @submit.prevent="loadData()" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Dari Bulan</label>
                <input type="month" x-model="beginDate"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Sampai Bulan</label>
                <input type="month" x-model="endDate"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <i class="fa-solid fa-filter mr-1"></i>
                Terapkan Filter
            </button>
        </form>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6" x-show="trData">
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Total Item Requests</p>
                <p class="text-3xl font-bold text-blue-800 mt-1" x-text="formatNumber(totalRequests)">—</p>
                <p class="text-xs text-blue-500 mt-1">Views + Downloads</p>
            </div>
            <div class="bg-green-50 rounded-xl p-4 border border-green-100">
                <p class="text-xs font-semibold text-green-600 uppercase tracking-wider">Total Views</p>
                <p class="text-3xl font-bold text-green-800 mt-1" x-text="formatNumber(totalViews)">—</p>
                <p class="text-xs text-green-500 mt-1">Halaman artikel dibuka</p>
            </div>
            <div class="bg-purple-50 rounded-xl p-4 border border-purple-100">
                <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Total Downloads</p>
                <p class="text-3xl font-bold text-purple-800 mt-1" x-text="formatNumber(totalDownloads)">—</p>
                <p class="text-xs text-purple-500 mt-1">File PDF diunduh</p>
            </div>
        </div>

        {{-- TR Monthly Chart --}}
        <div class="mt-6" x-show="trData">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Tren Bulanan (Title Report)</h3>
            <div class="h-48">
                <canvas id="counterChart"></canvas>
            </div>
        </div>

        {{-- IR Table --}}
        <div class="mt-8" x-show="irData && irData.Report_Items && irData.Report_Items.length > 0">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Item Report — Per Artikel</h3>
                <span class="text-xs text-gray-400" x-text="'Total: ' + (irData?.Report_Items?.length ?? 0) + ' artikel'"></span>
            </div>
            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Judul Artikel</th>
                            <th class="px-4 py-3 text-left font-semibold">DOI</th>
                            <th class="px-4 py-3 text-right font-semibold">Views</th>
                            <th class="px-4 py-3 text-right font-semibold">Downloads</th>
                            <th class="px-4 py-3 text-right font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="item in irData.Report_Items" :key="item.Item">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-800 max-w-xs truncate" x-text="item.Item"></td>
                                <td class="px-4 py-3 text-gray-500 font-mono text-xs" x-text="item.DOI || '—'"></td>
                                <td class="px-4 py-3 text-right text-gray-700" x-text="formatNumber(item.Metric_Types?.Unique_Item_Requests ?? 0)"></td>
                                <td class="px-4 py-3 text-right text-gray-700" x-text="formatNumber(item.Metric_Types?.Total_Item_Investigations ?? 0)"></td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900" x-text="formatNumber(item.Metric_Types?.Total_Item_Requests ?? 0)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Empty state --}}
        <div class="mt-8 text-center py-12 text-gray-400" x-show="irData && (!irData.Report_Items || irData.Report_Items.length === 0)">
            <i class="fa-solid fa-chart-bar text-4xl mb-3"></i>
            <p class="text-sm">Belum ada data statistik untuk periode ini.</p>
        </div>

        {{-- Loading state --}}
        <div class="mt-8 text-center py-12 text-gray-400" x-show="loading">
            <i class="fa-solid fa-spinner fa-spin text-2xl mb-3"></i>
            <p class="text-sm">Memuat data...</p>
        </div>
    </div>

    {{-- API Info --}}
    <div class="bg-slate-50 rounded-xl border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">
            <i class="fa-solid fa-code mr-2 text-slate-400"></i>
            COUNTER R5 API Endpoints
        </h3>
        <div class="space-y-2 text-xs font-mono text-slate-600">
            <div class="flex items-center gap-2">
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded font-sans font-semibold">GET</span>
                <span>/api/v1/counter/tr/{{ $journal->slug }}?begin_date=YYYY-MM&end_date=YYYY-MM</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded font-sans font-semibold">GET</span>
                <span>/api/v1/counter/ir/{{ $journal->slug }}?begin_date=YYYY-MM&end_date=YYYY-MM</span>
            </div>
        </div>
        <p class="text-xs text-slate-500 mt-3">Endpoint ini publik dan dapat diakses oleh Scopus, Web of Science, dan agregator akademik lainnya.</p>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function counterStats() {
    return {
        beginDate: '{{ $beginDate }}',
        endDate:   '{{ $endDate }}',
        trData:    null,
        irData:    null,
        loading:   false,
        chart:     null,

        get totalRequests()  { return this.trData?.Report_Items?.[0]?.Metric_Types?.Total_Item_Requests ?? 0; },
        get totalViews()     { return this.trData?.Report_Items?.[0]?.Metric_Types?.Unique_Item_Requests ?? 0; },
        get totalDownloads() { return this.trData?.Report_Items?.[0]?.Metric_Types?.Total_Item_Investigations ?? 0; },

        formatNumber(n) {
            return Number(n).toLocaleString('id-ID');
        },

        async loadData() {
            this.loading = true;
            const params = `begin_date=${this.beginDate}&end_date=${this.endDate}`;

            try {
                const [trRes, irRes] = await Promise.all([
                    fetch(`{{ route('journal.settings.statistics.counter.tr', $journal->slug) }}?${params}`),
                    fetch(`{{ route('journal.settings.statistics.counter.ir', $journal->slug) }}?${params}`),
                ]);
                this.trData = await trRes.json();
                this.irData = await irRes.json();
                this.$nextTick(() => this.renderChart());
            } catch (e) {
                console.error('Failed to load COUNTER data', e);
            } finally {
                this.loading = false;
            }
        },

        renderChart() {
            const performances = this.trData?.Report_Items?.[0]?.Performances ?? [];
            const labels    = performances.map(p => p.Period.Begin_Date.substring(0, 7));
            const views     = performances.map(p => p.Instances.find(i => i.Metric_Type === 'Unique_Item_Requests')?.Count ?? 0);
            const downloads = performances.map(p => p.Instances.find(i => i.Metric_Type === 'Total_Item_Investigations')?.Count ?? 0);

            const ctx = document.getElementById('counterChart');
            if (!ctx) return;

            if (this.chart) this.chart.destroy();

            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Views', data: views, backgroundColor: 'rgba(99, 102, 241, 0.7)', borderRadius: 4 },
                        { label: 'Downloads', data: downloads, backgroundColor: 'rgba(16, 185, 129, 0.7)', borderRadius: 4 },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: { x: { stacked: false }, y: { beginAtZero: true, ticks: { precision: 0 } } },
                },
            });
        },

        init() {
            this.loadData();
        },
    };
}
</script>
@endpush
@endsection
