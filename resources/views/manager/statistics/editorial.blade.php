@extends('layouts.app')

@php
    $currentLoc = session('app_locale', app()->getLocale());
    $isId = in_array($currentLoc, ['id', 'id_ID']);
@endphp

@section('title', ($isId ? 'Aktivitas Editorial - ' : 'Editorial Activity - ') . $journal->name)

@section('content')
    <div x-data="editorialDashboard()" x-init="init()" class="space-y-6">

        {{-- HEADER & DATE FILTER --}}
        <div
            class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text">
                    {{ $isId ? 'Aktivitas Editorial' : 'Editorial Activity' }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ $isId ? 'Pantau alur pengajuan, keputusan, dan efisiensi editorial.' : 'Monitor submission flow, decisions, and editorial efficiency.' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                {{-- Granularity Buttons --}}
                <div class="flex items-center bg-slate-100 rounded-lg p-1">
                    <button type="button" @click="setGranularity('daily')"
                        :class="granularity === 'daily' ? 'bg-white text-indigo-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                        class="px-3 py-1.5 text-xs rounded-md transition-all">
                        {{ $isId ? 'Harian' : 'Daily' }}
                    </button>
                    <button type="button" @click="setGranularity('weekly')"
                        :class="granularity === 'weekly' ? 'bg-white text-indigo-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                        class="px-3 py-1.5 text-xs rounded-md transition-all">
                        {{ $isId ? 'Mingguan' : 'Weekly' }}
                    </button>
                    <button type="button" @click="setGranularity('monthly')"
                        :class="granularity === 'monthly' ? 'bg-white text-indigo-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                        class="px-3 py-1.5 text-xs rounded-md transition-all">
                        {{ $isId ? 'Bulanan' : 'Monthly' }}
                    </button>
                </div>

                {{-- Date Range Picker --}}
                <div class="flex items-center gap-3 bg-slate-50 p-2 rounded-xl border border-slate-200">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-calendar text-slate-400 text-sm"></i>
                        <input type="date" x-model="dateStart" @change="fetchData()"
                            class="bg-transparent border-none text-sm focus:ring-0 text-slate-700 font-medium w-32">
                    </div>
                    <span class="text-slate-300">—</span>
                    <div class="flex items-center gap-2">
                        <input type="date" x-model="dateEnd" @change="fetchData()"
                            class="bg-transparent border-none text-sm focus:ring-0 text-slate-700 font-medium w-32">
                    </div>
                </div>
            </div>
        </div>

        {{-- LOADING STATE --}}
        <template x-if="loading">
            <div class="flex justify-center items-center py-20">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
            </div>
        </template>

        {{-- MAIN CONTENT --}}
        <template x-if="!loading">
            <div class="space-y-6">
                {{-- KPI CARDS --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">

                    {{-- Submissions Received --}}
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                {{ $isId ? 'Pengajuan' : 'Submissions' }}
                            </span>
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="12" y1="18" x2="12" y2="12"></line>
                                    <line x1="9" y1="15" x2="15" y2="15"></line>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-slate-800 text-center" x-text="kpi.received">0</div>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ $isId ? 'Total diterima dalam periode' : 'Total received in period' }}
                        </p>
                    </div>

                    {{-- Accepted --}}
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                {{ $isId ? 'Diterima' : 'Accepted' }}
                            </span>
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-slate-800 text-center" x-text="kpi.accepted">0</div>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ $isId ? 'Artikel diterima' : 'Articles accepted' }}
                        </p>
                    </div>

                    {{-- Acceptance Rate --}}
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                {{ $isId ? 'Tingkat Penerimaan' : 'Acceptance Rate' }}
                            </span>
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="20" x2="18" y2="10"></line>
                                    <line x1="12" y1="20" x2="12" y2="4"></line>
                                    <line x1="6" y1="20" x2="6" y2="14"></line>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-slate-800 text-center"><span x-text="kpi.acceptance_rate">0</span>%</div>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ $isId ? 'Dari pengajuan yang diputuskan' : 'Of decided submissions' }}
                        </p>
                    </div>

                    {{-- Days to First Decision --}}
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                {{ $isId ? 'Hari ke Keputusan ke-1' : 'Days to 1st Decision' }}
                            </span>
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-slate-800 text-center" x-text="kpi.avg_days_first || '-'">0</div>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ $isId ? 'Rata-rata hari' : 'Average days' }}
                        </p>
                    </div>

                    {{-- Days to Accept --}}
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                {{ $isId ? 'Hari untuk Diterima' : 'Days to Accept' }}
                            </span>
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2">
                                    </rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-slate-800 text-center" x-text="kpi.avg_days_accept || '-'">0</div>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ $isId ? 'Dari pengajuan sampai diterima' : 'From submission to acceptance' }}
                        </p>
                    </div>

                </div>

                {{-- CHARTS ROW --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Submission Trends (Multi-Line Area Chart) --}}
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-bold text-slate-700">
                                    {{ $isId ? 'Tren Pengajuan' : 'Submission Trends' }}
                                </h3>
                                <p class="text-xs text-slate-400">
                                    {{ $isId ? 'Ikhtisar aktivitas bulanan' : 'Monthly activity overview' }}
                                </p>
                            </div>
                            <div class="flex gap-4 text-xs">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-3 h-3 rounded-full" style="background: #6366f1"></span>
                                    {{ $isId ? 'Diterima' : 'Received' }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="w-3 h-3 rounded-full" style="background: #10b981"></span>
                                    {{ $isId ? 'Disetujui' : 'Accepted' }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="w-3 h-3 rounded-full" style="background: #ef4444"></span>
                                    {{ $isId ? 'Ditolak' : 'Declined' }}
                                </span>
                            </div>
                        </div>
                        <div id="trendChart" class="h-80"></div>
                    </div>

                    {{-- Decision Outcomes (Donut Chart) --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                        <div class="mb-4">
                            <h3 class="font-bold text-slate-700">
                                {{ $isId ? 'Hasil Keputusan' : 'Decision Outcomes' }}
                            </h3>
                            <p class="text-xs text-slate-400">
                                {{ $isId ? 'Distribusi keputusan akhir' : 'Distribution of final decisions' }}
                            </p>
                        </div>
                        <div id="outcomeChart" class="h-72"></div>
                    </div>

                </div>

                {{-- EFFICIENCY TREND --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-700">
                                {{ $isId ? 'Efisiensi Editorial' : 'Editorial Efficiency' }}
                            </h3>
                            <p class="text-xs text-slate-400">
                                {{ $isId ? 'Rata-rata hari untuk keputusan pertama dari waktu ke waktu' : 'Average days to first decision over time' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 text-xs px-3 py-1.5 rounded-full"
                            :class="kpi.avg_days_first <= 14 ? 'bg-green-50 text-green-600' : (kpi.avg_days_first <= 30 ?
                                'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-600')">
                            <i class="fa-solid" :class="kpi.avg_days_first <= 14 ? 'fa-circle-check' : 'fa-clock'"></i>
                            <span
                                x-text="kpi.avg_days_first <= 14 ? (isId ? 'Sangat Baik' : 'Excellent') : (kpi.avg_days_first <= 30 ? (isId ? 'Baik' : 'Good') : (isId ? 'Butuh Perbaikan' : 'Needs Improvement'))"></span>
                        </div>
                    </div>
                    <div id="efficiencyChart" class="h-64"></div>
                </div>

                {{-- INSIGHTS CARDS --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Desk Reject Info --}}
                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl p-5 border border-orange-100">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center">
                                <i class="fa-solid fa-ban text-white"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-700">
                                    {{ $isId ? 'Penolakan Awal (Desk Reject)' : 'Desk Rejects' }}
                                </h4>
                                <p class="text-xs text-slate-500">
                                    {{ $isId ? 'Ditolak pada tahap pengajuan' : 'Rejected at submission stage' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-2xl font-bold text-orange-600" x-text="outcomes.data ? outcomes.data[1] : 0">0
                        </div>
                    </div>

                    {{-- Review Reject Info --}}
                    <div class="bg-gradient-to-br from-red-50 to-rose-50 rounded-2xl p-5 border border-red-100">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-red-500 flex items-center justify-center">
                                <i class="fa-solid fa-times-circle text-white"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-700">
                                    {{ $isId ? 'Penolakan Review (Review Reject)' : 'Review Rejects' }}
                                </h4>
                                <p class="text-xs text-slate-500">
                                    {{ $isId ? 'Ditolak setelah tinjauan sejawat' : 'Rejected after peer review' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-2xl font-bold text-red-600" x-text="outcomes.data ? outcomes.data[2] : 0">0</div>
                    </div>

                    {{-- In Progress --}}
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-5 border border-indigo-100">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500 flex items-center justify-center">
                                <i class="fa-solid fa-hourglass-half text-white"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-700">
                                    {{ $isId ? 'Sedang Diproses' : 'In Progress' }}
                                </h4>
                                <p class="text-xs text-slate-500">
                                    {{ $isId ? 'Menunggu keputusan' : 'Awaiting decision' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-2xl font-bold text-indigo-600" x-text="outcomes.data ? outcomes.data[3] : 0">0
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- APEXCHARTS CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        function editorialDashboard() {
            return {
                isId: @json($isId),
                dateStart: new URLSearchParams(window.location.search).get('start') || '{{ now()->subYear()->format('Y-m-d') }}',
                dateEnd: new URLSearchParams(window.location.search).get('end') || '{{ now()->format('Y-m-d') }}',
                granularity: new URLSearchParams(window.location.search).get('granularity') || 'monthly',
                loading: true,
                kpi: {},
                trends: {},
                efficiency: {},
                outcomes: {},
                trendChart: null,
                outcomeChart: null,
                efficiencyChart: null,

                init() {
                    this.fetchData();
                },

                setGranularity(g) {
                    this.granularity = g;
                    this.fetchData();
                },

                async fetchData() {
                    this.loading = true;

                    // Update URL search parameters to make it persistent on page reload
                    const url = new URL(window.location.href);
                    url.searchParams.set('start', this.dateStart);
                    url.searchParams.set('end', this.dateEnd);
                    url.searchParams.set('granularity', this.granularity);
                    window.history.replaceState({}, '', url.toString());

                    try {
                        const response = await fetch(
                            `{{ route('journal.settings.statistics.editorial.data', ['journal' => $journal->slug]) }}?start=${this.dateStart}&end=${this.dateEnd}&granularity=${this.granularity}`
                        );
                        const data = await response.json();

                        this.kpi = data.kpi;
                        this.trends = data.trends;
                        this.efficiency = data.efficiency;
                        this.outcomes = data.outcomes;

                        this.loading = false;

                        // Wait for DOM update then render charts
                        this.$nextTick(() => {
                            this.renderCharts();
                        });
                    } catch (error) {
                        console.error('Error fetching data:', error);
                        this.loading = false;
                    }
                },

                renderCharts() {
                    // Destroy existing charts
                    if (this.trendChart) this.trendChart.destroy();
                    if (this.outcomeChart) this.outcomeChart.destroy();
                    if (this.efficiencyChart) this.efficiencyChart.destroy();

                    // 1. Trend Chart (Area)
                    this.trendChart = new ApexCharts(document.querySelector("#trendChart"), {
                        chart: {
                            type: 'area',
                            height: 320,
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'inherit',
                        },
                        series: [{
                                name: this.isId ? 'Diterima' : 'Received',
                                data: this.trends.received || []
                            },
                            {
                                name: this.isId ? 'Disetujui' : 'Accepted',
                                data: this.trends.accepted || []
                            },
                            {
                                name: this.isId ? 'Ditolak' : 'Declined',
                                data: this.trends.declined || []
                            },
                        ],
                        xaxis: {
                            categories: this.trends.categories || [],
                            labels: {
                                style: {
                                    colors: '#94a3b8',
                                    fontSize: '11px'
                                }
                            },
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#94a3b8',
                                    fontSize: '11px'
                                }
                            },
                        },
                        colors: ['#6366f1', '#10b981', '#ef4444'],
                        stroke: {
                            curve: 'smooth',
                            width: 2
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                opacityFrom: 0.4,
                                opacityTo: 0.05
                            },
                        },
                        grid: {
                            borderColor: '#e2e8f0',
                            strokeDashArray: 4,
                        },
                        legend: {
                            show: false
                        },
                        dataLabels: {
                            enabled: false
                        },
                    });
                    this.trendChart.render();

                    // Map outcome chart labels based on locale
                    const outcomeLabels = this.outcomes.labels ? this.outcomes.labels.map(label => {
                        if (this.isId) {
                            if (label === 'Accepted') return 'Disetujui';
                            if (label === 'Desk Reject') return 'Penolakan Awal';
                            if (label === 'Review Reject') return 'Penolakan Review';
                            if (label === 'In Progress') return 'Sedang Diproses';
                            if (label === 'Withdrawn') return 'Ditarik';
                        }
                        return label;
                    }) : [];

                    // 2. Outcome Chart (Donut)
                    this.outcomeChart = new ApexCharts(document.querySelector("#outcomeChart"), {
                        chart: {
                            type: 'donut',
                            height: 288,
                            fontFamily: 'inherit',
                        },
                        series: this.outcomes.data || [],
                        labels: outcomeLabels,
                        colors: this.outcomes.colors || ['#10b981', '#f97316', '#ef4444', '#6366f1', '#94a3b8'],
                        legend: {
                            position: 'bottom',
                            fontSize: '12px',
                            labels: {
                                colors: '#64748b'
                            },
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%',
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            label: this.isId ? 'Total Diputuskan' : 'Total Decided',
                                            color: '#475569',
                                            formatter: (w) => {
                                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            },
                                        },
                                    },
                                },
                            },
                        },
                        dataLabels: {
                            enabled: false
                        },
                    });
                    this.outcomeChart.render();

                    // 3. Efficiency Chart (Line)
                    this.efficiencyChart = new ApexCharts(document.querySelector("#efficiencyChart"), {
                        chart: {
                            type: 'line',
                            height: 256,
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'inherit',
                        },
                        series: [{
                            name: this.isId ? 'Rata-rata Hari untuk Keputusan' : 'Avg Days to Decision',
                            data: this.efficiency.data || [],
                        }],
                        xaxis: {
                            categories: this.efficiency.categories || [],
                            labels: {
                                style: {
                                    colors: '#94a3b8',
                                    fontSize: '11px'
                                }
                            },
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#94a3b8',
                                    fontSize: '11px'
                                }
                            },
                        },
                        colors: ['#8b5cf6'],
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        markers: {
                            size: 5,
                            colors: ['#8b5cf6'],
                            strokeColors: '#fff',
                            strokeWidth: 2,
                        },
                        grid: {
                            borderColor: '#e2e8f0',
                            strokeDashArray: 4,
                        },
                        annotations: {
                            yaxis: [{
                                y: 14,
                                borderColor: '#10b981',
                                borderWidth: 2,
                                strokeDashArray: 5,
                                label: {
                                    borderColor: '#10b981',
                                    style: {
                                        color: '#fff',
                                        background: '#10b981'
                                    },
                                    text: this.isId ? 'Target: 14 hari' : 'Target: 14 days',
                                },
                            }],
                        },
                    });
                    this.efficiencyChart.render();
                },
            };
        }
    </script>
@endsection
