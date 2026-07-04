@extends('layouts.app')

@section('title', ($isId ? 'Pusat Laporan' : 'Report Center') . ' - ' . $journal->name)

@section('content')
    <div x-data="reportCenter()" class="space-y-6">

        {{-- HEADER --}}
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 p-6">
            <h1 class="text-2xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text">
                {{ $isId ? 'Pusat Laporan' : 'Report Center' }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $isId ? 'Buat dan unduh laporan statistik terperinci untuk' : 'Generate and download detailed statistics reports for' }}
                {{ $journal->name }}.
            </p>
        </div>

        {{-- REPORT CARDS GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- Card: Usage Report --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/60 hover:border-blue-400 hover:shadow-md transition-all cursor-pointer group"
                @click="openModal('usage', isId ? 'Statistik Penggunaan' : 'Usage Statistics', isId ? 'Tayangan dan unduhan diagregasi per bulan dan artikel (gaya COUNTER).' : 'Views and downloads aggregated by month and article (COUNTER style).')">
                <div class="flex items-center gap-4 mb-3">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0"
                        style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800 group-hover:text-blue-600 transition-colors">
                        {{ $isId ? 'Laporan Penggunaan' : 'Usage Report' }}
                    </h3>
                </div>
                <p class="text-sm text-slate-500 mt-2">
                    {{ $isId ? 'Tayangan dan unduhan diagregasi per bulan dan jenis artikel (gaya COUNTER).' : 'Views and downloads aggregated by month and article type (COUNTER style).' }}
                </p>
                <div class="mt-4 flex items-center text-xs text-blue-600 font-medium">
                    <span>{{ $isId ? 'Buat Laporan' : 'Generate Report' }}</span>
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </div>

            {{-- Card: Review Report --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/60 hover:border-purple-400 hover:shadow-md transition-all cursor-pointer group"
                @click="openModal('reviews', isId ? 'Kinerja Reviewer' : 'Reviewer Performance', isId ? 'Detail penugasan reviewer, waktu penyelesaian, dan rekomendasi.' : 'Details on reviewer assignments, completion times, and recommendations.')">
                <div class="flex items-center gap-4 mb-3">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0"
                        style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800 group-hover:text-purple-600 transition-colors">
                        {{ $isId ? 'Laporan Review' : 'Review Report' }}
                    </h3>
                </div>
                <p class="text-sm text-slate-500 mt-2">
                    {{ $isId ? 'Detail penugasan reviewer, waktu penyelesaian, dan rekomendasi.' : 'Details on reviewer assignments, completion times, and recommendations.' }}
                </p>
                <div class="mt-4 flex items-center text-xs text-purple-600 font-medium">
                    <span>{{ $isId ? 'Buat Laporan' : 'Generate Report' }}</span>
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </div>

            {{-- Card: Articles Report --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/60 hover:border-emerald-400 hover:shadow-md transition-all cursor-pointer group"
                @click="openModal('articles', isId ? 'Pengajuan Artikel' : 'Article Submissions', isId ? 'Metadata lengkap dari semua pengajuan termasuk penulis dan status.' : 'Comprehensive metadata of all submissions including authors and status.')">
                <div class="flex items-center gap-4 mb-3">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0"
                        style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800 group-hover:text-emerald-600 transition-colors">
                        {{ $isId ? 'Laporan Artikel' : 'Articles Report' }}
                    </h3>
                </div>
                <p class="text-sm text-slate-500 mt-2">
                    {{ $isId ? 'Metadata lengkap dari semua pengajuan termasuk penulis dan status.' : 'Comprehensive metadata of all submissions including authors and status.' }}
                </p>
                <div class="mt-4 flex items-center text-xs text-emerald-600 font-medium">
                    <span>{{ $isId ? 'Buat Laporan' : 'Generate Report' }}</span>
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </div>

        </div>

        {{-- INFO SECTION --}}
        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-6 border border-indigo-100">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-500 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold text-slate-700">{{ $isId ? 'Tentang Laporan' : 'About Reports' }}</h4>
                    <p class="text-sm text-slate-600 mt-1">
                        {{ $isId ? 'Laporan dibuat dalam format CSV untuk analisis mudah di Excel atau Google Sheets. Dataset besar diproses dalam bagian-bagian untuk memastikan unduhan cepat tanpa masalah memori. Gunakan fitur pratinjau untuk memverifikasi filter Anda sebelum mengunduh.' : 'Reports are generated in CSV format for easy analysis in Excel or Google Sheets. Large datasets are processed in chunks to ensure fast downloads without memory issues. Use the preview feature to verify your filters before downloading.' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- GENERATOR MODAL --}}
        <div x-show="showModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" @click.outside="showModal = false">

                {{-- Modal Header --}}
                <div
                    class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-slate-50 to-white">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800" x-text="modalTitle">{{ $isId ? 'Buat Laporan' : 'Generate Report' }}</h3>
                        <p class="text-xs text-slate-500" x-text="modalDesc"></p>
                    </div>
                    <button @click="showModal = false"
                        class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 overflow-y-auto">
                    <form id="exportForm" method="POST"
                        action="{{ route('journal.settings.statistics.reports.export', ['journal' => $journal->slug]) }}">
                        @csrf
                        <input type="hidden" name="type" x-model="selectedType">

                        {{-- Date Filter --}}
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="report_start_date"
                                    class="block text-sm font-semibold text-slate-700 mb-2">{{ $isId ? 'Tanggal Mulai' : 'Start Date' }}</label>
                                <input type="date" id="report_start_date" name="start" x-model="start"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-400 focus:ring focus:ring-indigo-100 transition-all text-sm">
                            </div>
                            <div>
                                <label for="report_end_date" class="block text-sm font-semibold text-slate-700 mb-2">{{ $isId ? 'Tanggal Akhir' : 'End Date' }}</label>
                                <input type="date" id="report_end_date" name="end" x-model="end"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-400 focus:ring focus:ring-indigo-100 transition-all text-sm">
                            </div>
                        </div>

                        {{-- Data Preview Area --}}
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-sm font-semibold text-slate-700">{{ $isId ? 'Pratinjau Data' : 'Data Preview' }}</span>
                                <button type="button" @click="fetchPreview()"
                                    class="flex items-center gap-1.5 text-xs text-indigo-600 font-semibold hover:text-indigo-800 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                        </path>
                                    </svg>
                                    {{ $isId ? 'Perbarui Pratinjau' : 'Refresh Preview' }}
                                </button>
                            </div>

                            <div
                                class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs overflow-x-auto min-h-[150px] max-h-[250px]">
                                {{-- Loading State --}}
                                <template x-if="isLoading">
                                    <div class="flex items-center justify-center gap-2 text-slate-400 py-8">
                                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-indigo-600"></div>
                                        <span>{{ $isId ? 'Memuat pratinjau...' : 'Loading preview...' }}</span>
                                    </div>
                                </template>

                                {{-- Data Table --}}
                                <template x-if="!isLoading && previewData.length > 0">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="border-b-2 border-slate-200">
                                                <template x-for="key in Object.keys(previewData[0])"
                                                    :key="key">
                                                    <th class="py-2 px-3 uppercase font-bold text-slate-500 whitespace-nowrap"
                                                        x-text="translateHeader(key)"></th>
                                                </template>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(row, idx) in previewData" :key="idx">
                                                <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-100/50">
                                                    <template x-for="(val, key) in row" :key="key">
                                                        <td class="py-2 px-3 text-slate-600 whitespace-nowrap max-w-[200px] truncate"
                                                            x-text="val ?? '-'"></td>
                                                    </template>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </template>

                                {{-- No Data --}}
                                <template x-if="!isLoading && previewData.length === 0">
                                    <div class="flex flex-col items-center justify-center text-slate-400 py-8">
                                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        <span>{{ $isId ? 'Tidak ada data ditemukan untuk rentang tanggal ini.' : 'No data found for this date range.' }}</span>
                                    </div>
                                </template>
                            </div>
                            <p class="text-xs text-slate-400 mt-2">{{ $isId ? 'Menampilkan 5 baris pertama. Unduh untuk mendapatkan data lengkap.' : 'Showing first 5 rows. Download to get complete data.' }}</p>
                        </div>

                        {{-- Actions --}}
                        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="showModal = false"
                                class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors cursor-pointer">
                                {{ $isId ? 'Batal' : 'Cancel' }}
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                {{ $isId ? 'Unduh CSV' : 'Download CSV' }}
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>

    <script>
        function reportCenter() {
            const isId = {{ $isId ? 'true' : 'false' }};

            return {
                showModal: false,
                modalTitle: '',
                modalDesc: '',
                selectedType: '',
                isId: isId,
                start: '{{ now()->subMonth()->format('Y-m-d') }}',
                end: '{{ now()->format('Y-m-d') }}',
                previewData: [],
                isLoading: false,

                openModal(type, title, desc) {
                    this.selectedType = type;
                    this.modalTitle = title;
                    this.modalDesc = desc;
                    this.showModal = true;
                    this.previewData = [];
                    this.fetchPreview();
                },

                translateHeader(key) {
                    if (!this.isId) {
                        return key.replace(/_/g, ' ');
                    }

                    const translations = {
                        'code': 'Kode Artikel',
                        'title': 'Judul Artikel',
                        'author': 'Penulis',
                        'section': 'Bagian',
                        'status': 'Status',
                        'stage': 'Tahap',
                        'issue': 'Terbitan',
                        'submitted_at': 'Tanggal Diajukan',
                        'accepted_at': 'Tanggal Diterima',
                        'published_at': 'Tanggal Diterbitkan',

                        'article_code': 'Kode Artikel',
                        'article_title': 'Judul Artikel',
                        'reviewer': 'Peninjau',
                        'reviewer_affiliation': 'Afiliasi Peninjau',
                        'round': 'Putaran',
                        'recommendation': 'Rekomendasi',
                        'assigned_at': 'Tanggal Ditugaskan',
                        'due_date': 'Tenggat Waktu',
                        'responded_at': 'Tanggal Direspon',
                        'completed_at': 'Tanggal Selesai',
                        'days_taken': 'Durasi (Hari)',

                        'metric_type': 'Jenis Metrik',
                        'month': 'Bulan',
                        'count': 'Jumlah'
                    };

                    const lowerKey = key.toLowerCase();
                    return translations[lowerKey] || key.replace(/_/g, ' ');
                },

                async fetchPreview() {
                    this.isLoading = true;

                    try {
                        const response = await fetch(
                            '{{ route('journal.settings.statistics.reports.preview', ['journal' => $journal->slug]) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                        'content'),
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    type: this.selectedType,
                                    start: this.start,
                                    end: this.end
                                })
                            });

                        const data = await response.json();
                        this.previewData = Array.isArray(data) ? data : [];
                    } catch (err) {
                        console.error('Preview fetch error:', err);
                        this.previewData = [];
                    } finally {
                        this.isLoading = false;
                    }
                }
            }
        }
    </script>
@endsection

