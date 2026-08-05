@extends('layouts.admin')
 
@section('title', $isId ? 'Administrasi Situs' : 'Site Administration')
 
@section('content')
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight leading-none">{{ $isId ? 'Administrasi Situs' : 'Site Administration' }}</h1>
        <p class="text-sm text-slate-500 mt-1.5 leading-tight">{{ $isId ? 'Kelola instalasi IAMJOS, jurnal, dan pengaturan sistem Anda.' : 'Manage your IAMJOS installation, journals, and system settings.' }}</p>
    </div>
 
    <!-- Quick Stats (scirepid.com Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card 1: Total Submissions (Blue Theme) -->
        <div class="relative overflow-hidden bg-blue-50/70 border border-blue-100/80 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-4">
            <div class="w-14 h-14 rounded-[20px] bg-blue-600 shadow-md shadow-blue-500/25 flex items-center justify-center text-white flex-shrink-0 relative z-10">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="relative z-10 min-w-0">
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-none mb-1">{{ number_format($journals->sum('submissions_count')) }}</p>
                <h3 class="text-sm font-bold text-slate-800 leading-snug truncate">{{ $isId ? 'Total Naskah' : 'Total Submissions' }}</h3>
                <p class="text-[11px] font-medium text-slate-500 truncate leading-tight mt-0.5">{{ $isId ? 'Artikel terdaftar' : 'Peer-reviewed articles' }}</p>
            </div>
            <!-- Decorative Background Watermark Circle -->
            <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-blue-200/50 pointer-events-none"></div>
        </div>

        <!-- Card 2: Total Journals (Amber / Orange Theme) -->
        <div class="relative overflow-hidden bg-amber-50/70 border border-amber-100/80 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-4">
            <div class="w-14 h-14 rounded-[20px] bg-amber-600 shadow-md shadow-amber-500/25 flex items-center justify-center text-white flex-shrink-0 relative z-10">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div class="relative z-10 min-w-0">
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-none mb-1">{{ number_format($journals->count()) }}</p>
                <h3 class="text-sm font-bold text-slate-800 leading-snug truncate">{{ $isId ? 'Total Jurnal' : 'Total Journals' }}</h3>
                <p class="text-[11px] font-medium text-slate-500 truncate leading-tight mt-0.5">{{ $isId ? 'Jurnal terdaftar' : 'Hosted publications' }}</p>
            </div>
            <!-- Decorative Background Watermark Circle -->
            <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-amber-200/50 pointer-events-none"></div>
        </div>

        <!-- Card 3: Active Journals (Purple / Violet Theme) -->
        <div class="relative overflow-hidden bg-purple-50/70 border border-purple-100/80 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-4">
            <div class="w-14 h-14 rounded-[20px] bg-violet-600 shadow-md shadow-violet-500/25 flex items-center justify-center text-white flex-shrink-0 relative z-10">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="relative z-10 min-w-0">
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-none mb-1">{{ number_format($journals->where('enabled', true)->count()) }}</p>
                <h3 class="text-sm font-bold text-slate-800 leading-snug truncate">{{ $isId ? 'Jurnal Aktif' : 'Active Journals' }}</h3>
                <p class="text-[11px] font-medium text-slate-500 truncate leading-tight mt-0.5">{{ $isId ? 'Status aktif & live' : 'Enabled & live' }}</p>
            </div>
            <!-- Decorative Background Watermark Circle -->
            <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-purple-200/50 pointer-events-none"></div>
        </div>

        <!-- Card 4: Published Issues (Emerald / Teal Theme) -->
        <div class="relative overflow-hidden bg-emerald-50/70 border border-emerald-100/80 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-4">
            <div class="w-14 h-14 rounded-[20px] bg-emerald-600 shadow-md shadow-emerald-500/25 flex items-center justify-center text-white flex-shrink-0 relative z-10">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div class="relative z-10 min-w-0">
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-none mb-1">{{ number_format($journals->sum('issues_count')) }}</p>
                <h3 class="text-sm font-bold text-slate-800 leading-snug truncate">{{ $isId ? 'Terbitan Dipublikasikan' : 'Published Issues' }}</h3>
                <p class="text-[11px] font-medium text-slate-500 truncate leading-tight mt-0.5">{{ $isId ? 'Terbitan rilis' : 'Released issues' }}</p>
            </div>
            <!-- Decorative Background Watermark Circle -->
            <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-emerald-200/50 pointer-events-none"></div>
        </div>
    </div>

    <!-- Recent Journals Quick View -->
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $isId ? 'Jurnal Terbaru' : 'Recent Journals' }}</h2>
                <p class="text-sm text-gray-500">{{ $isId ? 'Ikhtisar cepat dari jurnal yang dihosting' : 'Quick overview of hosted journals' }}</p>
            </div>
            <a href="{{ route('admin.journals.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                {{ $isId ? 'Lihat Semua' : 'View All' }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $isId ? 'Jurnal' : 'Journal' }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $isId ? 'Jalur' : 'Path' }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $isId ? 'Artikel' : 'Articles' }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $isId ? 'Terbitan' : 'Issues' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($journals->take(5) as $journal)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($journal->logo_path)
                                        <img src="{{ Storage::disk('public')->url($journal->logo_path) }}" alt="{{ $journal->name }}"
                                            class="w-10 h-10 rounded-lg object-cover border border-gray-200">
                                    @else
                                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <span
                                                class="text-indigo-600 font-bold text-sm">{{ strtoupper(substr($journal->abbreviation ?? $journal->name, 0, 2)) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $journal->name }}</p>
                                        @if ($journal->abbreviation)
                                            <p class="text-xs text-gray-500">{{ $journal->abbreviation }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <code
                                    class="font-mono text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">/{{ $journal->slug }}</code>
                            </td>
                            <td class="px-6 py-4">
                                @if ($journal->enabled)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                        {{ $isId ? 'Aktif' : 'Active' }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                        {{ $isId ? 'Nonaktif' : 'Disabled' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $journal->submissions_count }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $journal->issues_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div
                                    class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <p class="text-gray-500">{{ $isId ? 'Jurnal tidak ditemukan.' : 'No journals found.' }}</p>
                                <a href="{{ route('admin.journals.create') }}"
                                    class="inline-flex items-center gap-2 mt-4 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    {{ $isId ? 'Buat Jurnal Pertama' : 'Create First Journal' }}
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
