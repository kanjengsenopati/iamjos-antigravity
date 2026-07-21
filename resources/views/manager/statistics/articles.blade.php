@extends('layouts.app')

@php
    $currentLoc = session('app_locale', app()->getLocale());
    $isId = in_array($currentLoc, ['id', 'id_ID']);
@endphp

@section('title', $isId ? 'Statistik Artikel (StatCounter)' : 'Article Statistics (StatCounter)')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap/dist/css/jsvectormap.min.css">
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .sparkline-bar {
            transition: all 0.2s ease;
        }

        .sparkline-bar:hover {
            opacity: 0.8;
            transform: scaleY(1.1);
        }

        /* StatCounter Checkbox Custom Styling */
        .stat-checkbox:checked {
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
        }
    </style>
@endpush

@section('content')
    <div x-data="articleDashboard()" x-init="initDashboard()" class="max-w-7xl mx-auto py-6 px-5 space-y-6">

        {{-- BREADCRUMB --}}
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-3 text-xs font-medium">
                <li>
                    <a href="{{ route('journal.dashboard', ['journal' => current_journal()->slug]) }}"
                        class="text-slate-400 hover:text-slate-600 transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="text-slate-300">/</li>
                <li class="text-slate-400">{{ $isId ? 'Statistik' : 'Statistics' }}</li>
                <li class="text-slate-300">/</li>
                <li class="text-slate-800 font-bold" aria-current="page">{{ $isId ? 'Artikel' : 'Articles' }}</li>
            </ol>
        </nav>

        {{-- STICKY GLASSMORPHISM SUB-NAVIGATION & HEADER --}}
        <div class="sticky top-0 z-30 backdrop-blur-md bg-white/80 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-4 sm:p-5 border border-white/40">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Title & Brand --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-[22px] font-bold text-slate-900 leading-tight">
                            {{ $isId ? 'Statistik Ringkasan (Summary Stats)' : 'Summary Stats' }}
                        </h1>
                        <p class="text-[12px] font-normal italic text-slate-400">
                            {{ $isId ? 'Analitik komprehensif tayangan, sesi, pengunjung, & unduhan ala StatCounter' : 'StatCounter-style article analytics: views, sessions, visitors & downloads' }}
                        </p>
                    </div>
                </div>

                {{-- StatCounter Navigation Tabs --}}
                <div class="flex items-center bg-slate-100/80 p-1 rounded-2xl">
                    <button type="button" @click="activeTab = 'summary'"
                        :class="activeTab === 'summary' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                        class="px-4 py-2 text-xs rounded-xl transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span>{{ $isId ? 'Ringkasan' : 'Summary' }}</span>
                    </button>
                    <button type="button" @click="activeTab = 'pages'"
                        :class="activeTab === 'pages' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                        class="px-4 py-2 text-xs rounded-xl transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                        <span>{{ $isId ? 'Artikel' : 'Pages' }}</span>
                    </button>
                    <button type="button" @click="activeTab = 'traffic'"
                        :class="activeTab === 'traffic' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                        class="px-4 py-2 text-xs rounded-xl transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $isId ? 'Lokasi' : 'Traffic Sources' }}</span>
                    </button>
                    <button type="button" @click="activeTab = 'activity'"
                        :class="activeTab === 'activity' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                        class="px-4 py-2 text-xs rounded-xl transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $isId ? 'Aktivitas Terkini' : 'Recent Activity' }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- FILTER BAR & STATCOUNTER DATE PRESETS --}}
        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 border-none">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                {{-- Presets Quick Filter Buttons --}}
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mr-2 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        {{ $isId ? 'Filter Presets:' : 'Filter Presets:' }}
                    </span>
                    <button type="button" @click="setPreset('7days')"
                        :class="preset === '7days' ? 'bg-blue-600 text-white shadow-sm font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                        class="px-3 py-1.5 text-xs rounded-xl transition-all">
                        {{ $isId ? '7 Hari Terakhir' : 'Last 7 Days' }}
                    </button>
                    <button type="button" @click="setPreset('14days')"
                        :class="preset === '14days' ? 'bg-blue-600 text-white shadow-sm font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                        class="px-3 py-1.5 text-xs rounded-xl transition-all">
                        {{ $isId ? '14 Hari Terakhir (StatCounter)' : 'Last 14 Days (StatCounter)' }}
                    </button>
                    <button type="button" @click="setPreset('30days')"
                        :class="preset === '30days' ? 'bg-blue-600 text-white shadow-sm font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                        class="px-3 py-1.5 text-xs rounded-xl transition-all">
                        {{ $isId ? '30 Hari Terakhir' : 'Last 30 Days' }}
                    </button>
                    <button type="button" @click="setPreset('this_month')"
                        :class="preset === 'this_month' ? 'bg-blue-600 text-white shadow-sm font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                        class="px-3 py-1.5 text-xs rounded-xl transition-all">
                        {{ $isId ? 'Bulan Ini' : 'This Month' }}
                    </button>
                </div>

                {{-- Granularity & Custom Date Controls --}}
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Granularity --}}
                    <div class="flex items-center bg-slate-100 rounded-xl p-1">
                        <button type="button" @click="setGranularity('daily')"
                            :class="granularity === 'daily' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                            class="px-3 py-1.5 text-xs rounded-lg transition-all">
                            {{ $isId ? 'Harian' : 'Daily' }}
                        </button>
                        <button type="button" @click="setGranularity('weekly')"
                            :class="granularity === 'weekly' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                            class="px-3 py-1.5 text-xs rounded-lg transition-all">
                            {{ $isId ? 'Mingguan' : 'Weekly' }}
                        </button>
                        <button type="button" @click="setGranularity('monthly')"
                            :class="granularity === 'monthly' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                            class="px-3 py-1.5 text-xs rounded-lg transition-all">
                            {{ $isId ? 'Bulanan' : 'Monthly' }}
                        </button>
                    </div>

                    {{-- Custom Date Inputs --}}
                    <div class="flex items-center gap-2 bg-slate-50 rounded-xl p-1.5 border border-slate-200">
                        <input type="date" x-model="dateStart" @change="preset = 'custom'; fetchData()"
                            class="w-32 px-2.5 py-1 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                        <span class="text-slate-400 text-xs font-medium">-</span>
                        <input type="date" x-model="dateEnd" @change="preset = 'custom'; fetchData()"
                            class="w-32 px-2.5 py-1 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                    </div>

                    {{-- Loading Indicator --}}
                    <span x-show="isLoading" x-cloak class="inline-flex items-center gap-1.5 text-blue-600 text-xs font-bold">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        {{-- TAB 1: SUMMARY (RINGKASAN METRIK STATCOUNTER) --}}
        <div x-show="activeTab === 'summary'" class="space-y-6">

            {{-- MAIN STATCOUNTER CHART CONTAINER --}}
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 border-none">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-[16px] font-semibold text-slate-800 flex items-center gap-2">
                            <span>{{ $isId ? 'Grafik Tren Multi-Metrik' : 'Multi-Metric Trend Chart' }}</span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-600 font-bold">StatCounter R5</span>
                        </h2>
                        <p class="text-[12px] font-normal italic text-slate-400">
                            {{ $isId ? 'Centang/hapus centang pada kartu metrik di bawah untuk mengontrol garis grafik' : 'Check/uncheck metric cards below to toggle series lines on the chart' }}
                        </p>
                    </div>

                    {{-- Direct CSV Export Button --}}
                    <a :href="getCsvExportUrl()" target="_blank"
                        class="px-4 py-2 text-xs font-bold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>{{ $isId ? 'Export CSV' : 'Export CSV' }}</span>
                    </a>
                </div>

                {{-- ApexCharts Main Line Chart --}}
                <div id="statCounterChart" class="h-80 w-full"></div>

                {{-- STATCOUNTER INTERACTIVE METRIC CHECKBOX CARDS ROW --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mt-6 pt-6 border-t border-slate-100">
                    {{-- 1. Page Views (Lime Green) --}}
                    <label @click="toggleSeries('Page Views')"
                        class="cursor-pointer bg-slate-50/80 hover:bg-slate-100 p-4 rounded-2xl transition-all border border-slate-200/60 flex flex-col justify-between group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" x-model="visibleSeries['Page Views']"
                                    class="w-4 h-4 rounded text-lime-500 focus:ring-lime-400 accent-lime-500 cursor-pointer">
                                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Page Views</span>
                            </div>
                            <span class="w-2.5 h-2.5 rounded-full bg-lime-500"></span>
                        </div>
                        <div class="text-[18px] font-bold text-slate-900 group-hover:scale-105 transition-transform" x-text="formatNumber(kpi.views)">0</div>
                    </label>

                    {{-- 2. Sessions (Cyan) --}}
                    <label @click="toggleSeries('Sessions')"
                        class="cursor-pointer bg-slate-50/80 hover:bg-slate-100 p-4 rounded-2xl transition-all border border-slate-200/60 flex flex-col justify-between group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" x-model="visibleSeries['Sessions']"
                                    class="w-4 h-4 rounded text-cyan-500 focus:ring-cyan-400 accent-cyan-500 cursor-pointer">
                                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Sessions</span>
                            </div>
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                        </div>
                        <div class="text-[18px] font-bold text-slate-900 group-hover:scale-105 transition-transform" x-text="formatNumber(kpi.sessions)">0</div>
                    </label>

                    {{-- 3. Visitors (Pink) --}}
                    <label @click="toggleSeries('Visitors')"
                        class="cursor-pointer bg-slate-50/80 hover:bg-slate-100 p-4 rounded-2xl transition-all border border-slate-200/60 flex flex-col justify-between group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" x-model="visibleSeries['Visitors']"
                                    class="w-4 h-4 rounded text-pink-500 focus:ring-pink-400 accent-pink-500 cursor-pointer">
                                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Visitors</span>
                            </div>
                            <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span>
                        </div>
                        <div class="text-[18px] font-bold text-slate-900 group-hover:scale-105 transition-transform" x-text="formatNumber(kpi.visitors)">0</div>
                    </label>

                    {{-- 4. New Visitors (Yellow) --}}
                    <label @click="toggleSeries('New Visitors')"
                        class="cursor-pointer bg-slate-50/80 hover:bg-slate-100 p-4 rounded-2xl transition-all border border-slate-200/60 flex flex-col justify-between group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" x-model="visibleSeries['New Visitors']"
                                    class="w-4 h-4 rounded text-amber-500 focus:ring-amber-400 accent-amber-500 cursor-pointer">
                                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500">New Visitors</span>
                            </div>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        </div>
                        <div class="text-[18px] font-bold text-slate-900 group-hover:scale-105 transition-transform" x-text="formatNumber(kpi.new_visitors)">0</div>
                    </label>

                    {{-- 5. Downloads (Orange) --}}
                    <label @click="toggleSeries('Downloads')"
                        class="cursor-pointer bg-slate-50/80 hover:bg-slate-100 p-4 rounded-2xl transition-all border border-slate-200/60 flex flex-col justify-between group col-span-2 sm:col-span-1">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" x-model="visibleSeries['Downloads']"
                                    class="w-4 h-4 rounded text-orange-500 focus:ring-orange-400 accent-orange-500 cursor-pointer">
                                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Downloads</span>
                            </div>
                            <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                        </div>
                        <div class="text-[18px] font-bold text-slate-900 group-hover:scale-105 transition-transform" x-text="formatNumber(kpi.downloads)">0</div>
                    </label>
                </div>
            </div>

            {{-- SECONDARY HIGHLIGHTS ROW: HIGHLIGHT CARDS & DONUT --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Highlight Card: Top Country --}}
                <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 border-none flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1 block">{{ $isId ? 'Negara Teratas' : 'Top Country' }}</span>
                        <div class="text-[22px] font-bold text-slate-900" x-text="kpi.top_country?.name || '-'">-</div>
                        <div class="text-[14px] font-medium text-emerald-600 mt-1 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="kpi.top_country ? formatNumber(kpi.top_country.total) + ' ' + (isId ? 'kunjungan' : 'visits') : '0 visits'"></span>
                        </div>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Highlight Card: Busiest Day --}}
                <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 border-none flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1 block">{{ $isId ? 'Hari Tersibuk' : 'Busiest Day' }}</span>
                        <div class="text-[22px] font-bold text-slate-900" x-text="kpi.busiest_day?.formatted || '-'">-</div>
                        <div class="text-[14px] font-medium text-blue-600 mt-1 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span x-text="kpi.busiest_day ? formatNumber(kpi.busiest_day.total) + ' ' + (isId ? 'akses' : 'hits') : '0 hits'"></span>
                        </div>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                {{-- Engagement Donut Chart --}}
                <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 border-none">
                    <h2 class="text-[16px] font-semibold text-slate-800 mb-2">{{ $isId ? 'Rasio Interaksi' : 'Engagement Ratio' }}</h2>
                    <div id="ratioDonutChart" class="h-44 flex items-center justify-center"></div>
                </div>
            </div>
        </div>

        {{-- TAB 2: PAGES & ARTICLES (TABEL KINERJA ARTIKEL) --}}
        <div x-show="activeTab === 'pages'" class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden border-none">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-[16px] font-semibold text-slate-800">{{ $isId ? 'Artikel Berkinerja Terbaik' : 'Top Performing Articles' }}</h2>
                    <p class="text-[12px] font-normal italic text-slate-400">{{ $isId ? 'Peringkat artikel berdasarkan jumlah tayangan & unduhan' : 'Articles ranked by page views and downloads' }}</p>
                </div>
                <div class="relative w-64">
                    <input type="text" x-model="searchQuery" placeholder="{{ $isId ? 'Cari judul artikel...' : 'Search article title...' }}"
                        class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 bg-slate-50">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/70 text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                        <tr>
                            <th class="px-6 py-4 w-12">#</th>
                            <th class="px-6 py-4">{{ $isId ? 'Judul Artikel' : 'Article Title' }}</th>
                            <th class="px-6 py-4 w-28 text-center">{{ $isId ? 'Bagian' : 'Section' }}</th>
                            <th class="px-6 py-4 w-28 text-center">
                                <span class="text-lime-600 font-bold">Views</span>
                            </th>
                            <th class="px-6 py-4 w-28 text-center">
                                <span class="text-orange-600 font-bold">Downloads</span>
                            </th>
                            <th class="px-6 py-4 w-36 text-center">{{ $isId ? 'Tren Harian' : 'Daily Trend' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(row, index) in filteredTableData" :key="row.id">
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl text-xs font-bold"
                                        :class="index < 3 ? 'bg-gradient-to-tr from-amber-400 to-orange-500 text-white shadow-sm' : 'bg-slate-100 text-slate-600'"
                                        x-text="index + 1"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="max-w-lg">
                                        <a :href="row.url" target="_blank"
                                            class="font-semibold text-slate-900 hover:text-blue-600 line-clamp-2 transition-colors text-[14px]"
                                            x-text="row.title"></a>
                                        <div class="text-[12px] font-normal italic text-slate-400 mt-1" x-text="row.author"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-lg bg-slate-100 text-slate-600"
                                        x-text="row.section"></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-[18px] font-bold text-lime-600" x-text="formatNumber(row.views)"></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-[18px] font-bold text-orange-600" x-text="formatNumber(row.downloads)"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="h-7 w-full flex items-end gap-px bg-slate-50 p-1 rounded-lg">
                                        <template x-for="(val, i) in row.sparkline" :key="i">
                                            <div class="flex-1 bg-blue-500 rounded-t sparkline-bar hover:bg-blue-600"
                                                :style="'height: ' + getSparklineHeight(val, row.sparkline) + '%'"
                                                :title="val + ' views'"></div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template x-if="filteredTableData.length === 0 && !isLoading">
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <p class="font-medium">{{ $isId ? 'Tidak ada data artikel yang ditemukan' : 'No article data found' }}</p>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 3: TRAFFIC & GEO (DISTRIBUSI LOKASI PEMBACA) --}}
        <div x-show="activeTab === 'traffic'" class="space-y-6">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                {{-- Geo World Map (Span 2) --}}
                <div class="xl:col-span-2 bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 border-none">
                    <div class="mb-4">
                        <h2 class="text-[16px] font-semibold text-slate-800">{{ $isId ? 'Peta Distribusi Global Pembaca' : 'Global Reader Distribution Map' }}</h2>
                        <p class="text-[12px] font-normal italic text-slate-400">{{ $isId ? 'Arahkan kursor pada negara untuk melihat total tayangan' : 'Hover over a country to see detailed views count' }}</p>
                    </div>
                    <div id="worldMap" class="h-80 bg-slate-50/60 rounded-2xl"></div>
                </div>

                {{-- Top Country Bar Chart (Span 1) --}}
                <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 border-none">
                    <div class="mb-4">
                        <h2 class="text-[16px] font-semibold text-slate-800">{{ $isId ? 'Top 10 Negara' : 'Top 10 Countries' }}</h2>
                        <p class="text-[12px] font-normal italic text-slate-400">{{ $isId ? 'Peringkat tayangan terbanyak' : 'Ranked by total page views' }}</p>
                    </div>
                    <div id="countryBarChart" class="h-80"></div>
                </div>
            </div>
        </div>

        {{-- TAB 4: RECENT ACTIVITY LOG STREAM (AKTIVITAS TERKINI) --}}
        <div x-show="activeTab === 'activity'" class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden border-none">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-[16px] font-semibold text-slate-800 flex items-center gap-2">
                        <span>{{ $isId ? 'Log Stream Aktivitas Terkini' : 'Recent Activity Stream Log' }}</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    </h2>
                    <p class="text-[12px] font-normal italic text-slate-400">{{ $isId ? 'Daftar 25 interaksi pembaca terbaru secara real-time' : 'Real-time feed of latest 25 reader interactions' }}</p>
                </div>
                <button type="button" @click="fetchData()" class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Refresh</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/70 text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                        <tr>
                            <th class="px-6 py-4 w-36">{{ $isId ? 'Waktu' : 'Timestamp' }}</th>
                            <th class="px-6 py-4 w-28 text-center">{{ $isId ? 'Tipe' : 'Type' }}</th>
                            <th class="px-6 py-4">{{ $isId ? 'Artikel' : 'Article' }}</th>
                            <th class="px-6 py-4 w-44">{{ $isId ? 'Lokasi' : 'Location' }}</th>
                            <th class="px-6 py-4 w-40 text-right font-mono">{{ $isId ? 'Masked IP' : 'Masked IP' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="act in recentActivity" :key="act.id">
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-[13px] font-bold text-slate-800" x-text="act.time_ago"></div>
                                    <div class="text-[11px] font-normal italic text-slate-400" x-text="act.date"></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest rounded-lg"
                                        :class="act.type === 'view' ? 'bg-lime-100 text-lime-800' : 'bg-orange-100 text-orange-800'"
                                        x-text="act.type"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <a :href="act.article_url" target="_blank" class="font-semibold text-slate-900 hover:text-blue-600 line-clamp-1 text-[13px]" x-text="act.article_title"></a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-700" x-text="act.country_code"></span>
                                        <span class="text-[13px] font-medium text-slate-700" x-text="act.country_name"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-xs text-slate-500" x-text="act.ip_address"></td>
                            </tr>
                        </template>
                        <template x-if="recentActivity.length === 0 && !isLoading">
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    <p class="font-medium">{{ $isId ? 'Belum ada aktivitas yang tercatat' : 'No recent activity recorded' }}</p>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsvectormap"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsvectormap/dist/maps/world.js"></script>

    <script>
        function articleDashboard() {
            return {
                // State
                isId: @json($isId),
                activeTab: 'summary',
                preset: '14days', // StatCounter Default 14 Days
                dateStart: '{{ now()->subDays(13)->format('Y-m-d') }}',
                dateEnd: '{{ now()->format('Y-m-d') }}',
                granularity: 'daily',
                isLoading: false,
                searchQuery: '',

                // StatCounter Toggable Series Checkboxes
                visibleSeries: {
                    'Page Views': true,
                    'Sessions': true,
                    'Visitors': true,
                    'New Visitors': true,
                    'Downloads': true
                },

                // Data Store
                kpi: {
                    views: 0,
                    sessions: 0,
                    visitors: 0,
                    new_visitors: 0,
                    downloads: 0,
                    top_country: null,
                    busiest_day: null
                },
                chartData: {
                    categories: [],
                    views: [],
                    sessions: [],
                    visitors: [],
                    new_visitors: [],
                    downloads: []
                },
                mapData: {},
                tableData: [],
                recentActivity: [],

                // Chart instances
                chartInstance: null,
                mapInstance: null,
                countryBarChartInstance: null,
                ratioDonutChartInstance: null,

                // Computed
                get filteredTableData() {
                    if (!this.searchQuery) return this.tableData;
                    return this.tableData.filter(row => row.title.toLowerCase().includes(this.searchQuery.toLowerCase()));
                },

                // Init Dashboard
                initDashboard() {
                    this.initMainChart();
                    this.initMap();
                    this.initCountryBarChart();
                    this.initRatioDonutChart();
                    this.fetchData();
                },

                // Presets handler
                setPreset(name) {
                    this.preset = name;
                    const today = new Date();
                    let start = new Date();

                    if (name === '7days') {
                        start.setDate(today.getDate() - 6);
                    } else if (name === '14days') {
                        start.setDate(today.getDate() - 13);
                    } else if (name === '30days') {
                        start.setDate(today.getDate() - 29);
                    } else if (name === 'this_month') {
                        start = new Date(today.getFullYear(), today.getMonth(), 1);
                    }

                    this.dateStart = start.toISOString().split('T')[0];
                    this.dateEnd = today.toISOString().split('T')[0];
                    this.fetchData();
                },

                setGranularity(val) {
                    this.granularity = val;
                    this.fetchData();
                },

                // ApexCharts StatCounter Multi-Metric Main Line Chart
                initMainChart() {
                    const options = {
                        chart: {
                            type: 'line',
                            height: 320,
                            fontFamily: 'Inter, sans-serif',
                            toolbar: { show: false },
                            animations: { enabled: true, easing: 'easeinout', speed: 400 }
                        },
                        series: [
                            { name: 'Page Views', data: [] },
                            { name: 'Sessions', data: [] },
                            { name: 'Visitors', data: [] },
                            { name: 'New Visitors', data: [] },
                            { name: 'Downloads', data: [] }
                        ],
                        colors: ['#84cc16', '#06b6d4', '#ec4899', '#eab308', '#f97316'], // StatCounter Colors: Green, Cyan, Pink, Yellow, Orange
                        stroke: {
                            curve: 'smooth',
                            width: 2.5
                        },
                        markers: {
                            size: 4,
                            strokeColors: '#ffffff',
                            strokeWidth: 2,
                            hover: { size: 6 }
                        },
                        grid: {
                            borderColor: '#f1f5f9',
                            strokeDashArray: 3
                        },
                        xaxis: {
                            categories: [],
                            labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#64748b', fontSize: '11px' },
                                formatter: (val) => val ? parseInt(val).toLocaleString() : '0'
                            }
                        },
                        legend: { show: false }, // Controlled via StatCounter Checkbox Cards
                        tooltip: {
                            shared: true,
                            intersect: false,
                            y: { formatter: (val) => val ? parseInt(val).toLocaleString() : '0' }
                        }
                    };

                    this.chartInstance = new ApexCharts(document.querySelector("#statCounterChart"), options);
                    this.chartInstance.render();
                },

                // Toggle visibility of specific series line on ApexCharts
                toggleSeries(seriesName) {
                    if (!this.chartInstance) return;
                    this.chartInstance.toggleSeries(seriesName);
                },

                // Initialize World Map
                initMap() {
                    const mapEl = document.querySelector('#worldMap');
                    if (!mapEl) return;

                    try {
                        this.mapInstance = new jsVectorMap({
                            selector: '#worldMap',
                            map: 'world',
                            zoomButtons: false,
                            zoomOnScroll: false,
                            regionStyle: {
                                initial: { fill: '#e2e8f0', stroke: '#cbd5e1', strokeWidth: 0.5 },
                                hover: { fill: '#2563eb' }
                            },
                            visualizeData: {
                                scale: ['#93c5fd', '#1d4ed8'],
                                values: {}
                            }
                        });
                    } catch (e) {
                        console.warn('Map initialization error:', e);
                    }
                },

                // Initialize Country Bar Chart
                initCountryBarChart() {
                    const options = {
                        chart: {
                            type: 'bar',
                            height: 280,
                            fontFamily: 'Inter, sans-serif',
                            toolbar: { show: false }
                        },
                        series: [{ name: 'Views', data: [] }],
                        colors: ['#2563eb'],
                        plotOptions: {
                            bar: { horizontal: true, borderRadius: 6, barHeight: '55%' }
                        },
                        dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#fff'] } },
                        xaxis: { categories: [], labels: { style: { colors: '#64748b' } } },
                        yaxis: { labels: { style: { colors: '#334155', fontWeight: 600 } } },
                        grid: { borderColor: '#f1f5f9' },
                        legend: { show: false }
                    };
                    this.countryBarChartInstance = new ApexCharts(document.querySelector('#countryBarChart'), options);
                    this.countryBarChartInstance.render();
                },

                // Initialize Ratio Donut Chart
                initRatioDonutChart() {
                    const options = {
                        chart: { type: 'donut', height: 180, fontFamily: 'Inter, sans-serif' },
                        series: [0, 0],
                        labels: ['Views', 'Downloads'],
                        colors: ['#84cc16', '#f97316'],
                        plotOptions: { pie: { donut: { size: '70%' } } },
                        dataLabels: { enabled: false },
                        legend: { position: 'bottom', fontSize: '11px' }
                    };
                    this.ratioDonutChartInstance = new ApexCharts(document.querySelector('#ratioDonutChart'), options);
                    this.ratioDonutChartInstance.render();
                },

                // Fetch Data from Backend API
                async fetchData() {
                    this.isLoading = true;

                    const url = new URL(
                        '{{ route('journal.settings.statistics.articles.data', ['journal' => current_journal()->slug]) }}',
                        window.location.origin
                    );
                    url.searchParams.set('start', this.dateStart);
                    url.searchParams.set('end', this.dateEnd);
                    url.searchParams.set('granularity', this.granularity);

                    try {
                        const response = await fetch(url);
                        const data = await response.json();

                        this.kpi = data.kpi;
                        this.chartData = data.chart;
                        this.mapData = data.map;
                        this.tableData = data.table;
                        this.recentActivity = data.recent_activity || [];

                        this.updateMainChart();
                        this.updateMap();
                        this.updateCountryBarChart();
                        this.updateRatioDonutChart();

                    } catch (err) {
                        console.error('Failed to fetch stats:', err);
                    } finally {
                        this.isLoading = false;
                    }
                },

                // Update Main Line Chart Series Data
                updateMainChart() {
                    if (!this.chartInstance) return;

                    this.chartInstance.updateOptions({
                        xaxis: { categories: this.chartData.categories }
                    });

                    this.chartInstance.updateSeries([
                        { name: 'Page Views', data: this.chartData.views },
                        { name: 'Sessions', data: this.chartData.sessions },
                        { name: 'Visitors', data: this.chartData.visitors },
                        { name: 'New Visitors', data: this.chartData.new_visitors },
                        { name: 'Downloads', data: this.chartData.downloads }
                    ]);
                },

                // Update Map
                updateMap() {
                    const mapEl = document.querySelector('#worldMap');
                    if (!mapEl) return;
                    mapEl.innerHTML = '';
                    try {
                        this.mapInstance = new jsVectorMap({
                            selector: '#worldMap',
                            map: 'world',
                            zoomButtons: false,
                            zoomOnScroll: false,
                            regionStyle: {
                                initial: { fill: '#e2e8f0', stroke: '#cbd5e1', strokeWidth: 0.5 },
                                hover: { fill: '#2563eb' }
                            },
                            visualizeData: {
                                scale: ['#93c5fd', '#1d4ed8'],
                                values: this.mapData
                            }
                        });
                    } catch (e) {
                        console.warn('Map update error:', e);
                    }
                },

                // Update Country Bar Chart
                updateCountryBarChart() {
                    if (!this.countryBarChartInstance) return;
                    const entries = Object.entries(this.mapData).sort((a, b) => b[1] - a[1]).slice(0, 10);
                    this.countryBarChartInstance.updateOptions({ xaxis: { categories: entries.map(e => e[0]) } });
                    this.countryBarChartInstance.updateSeries([{ name: 'Views', data: entries.map(e => e[1]) }]);
                },

                // Update Ratio Donut Chart
                updateRatioDonutChart() {
                    if (!this.ratioDonutChartInstance) return;
                    this.ratioDonutChartInstance.updateSeries([this.kpi.views || 0, this.kpi.downloads || 0]);
                },

                // Helper: Format CSV export URL
                getCsvExportUrl() {
                    const url = new URL(
                        '{{ route('journal.settings.statistics.articles.export-csv', ['journal' => current_journal()->slug]) }}',
                        window.location.origin
                    );
                    url.searchParams.set('start', this.dateStart);
                    url.searchParams.set('end', this.dateEnd);
                    return url.toString();
                },

                formatNumber(num) {
                    return num ? num.toLocaleString() : '0';
                },

                getSparklineHeight(val, sparkline) {
                    const max = Math.max(...sparkline, 1);
                    return Math.max(4, (val / max) * 100);
                }
            };
        }
    </script>
@endpush
