@extends('layouts.admin')
 
@section('title', $isId ? 'Administrasi Situs' : 'Site Administration')
 
@section('content')
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight leading-none">{{ $isId ? 'Administrasi Situs' : 'Site Administration' }}</h1>
        <p class="text-sm text-slate-500 mt-1.5 leading-tight">{{ $isId ? 'Kelola instalasi IAMJOS, jurnal, dan pengaturan sistem Anda.' : 'Manage your IAMJOS installation, journals, and system settings.' }}</p>
    </div>
 
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Journals -->
        <div class="bg-white rounded-[24px] border border-slate-100 p-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-md transition-all duration-300 flex flex-col justify-between">
            <div class="flex items-center justify-center gap-2 mb-3">
                <div class="w-9 h-9 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <span class="text-[13.5px] font-bold text-slate-600 bg-slate-50 px-2 py-0.5 rounded-md">{{ $isId ? 'Total Jurnal' : 'Total Journals' }}</span>
            </div>
            <div class="text-center">
                <p class="text-3xl font-extrabold text-indigo-600 tracking-tight leading-none mb-1">{{ $journals->count() }}</p>
                <p class="text-[10px] font-bold tracking-wider text-slate-400">{{ $isId ? 'Jurnal' : 'Journals' }}</p>
            </div>
        </div>
 
        <!-- Active Journals -->
        <div class="bg-white rounded-[24px] border border-slate-100 p-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-md transition-all duration-300 flex flex-col justify-between">
            <div class="flex items-center justify-center gap-2 mb-3">
                <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-[13.5px] font-bold text-slate-600 bg-slate-50 px-2 py-0.5 rounded-md">{{ $isId ? 'Jurnal Aktif' : 'Active Journals' }}</span>
            </div>
            <div class="text-center">
                <p class="text-3xl font-extrabold text-emerald-600 tracking-tight leading-none mb-1">{{ $journals->where('enabled', true)->count() }}</p>
                <p class="text-[10px] font-bold tracking-wider text-slate-400">{{ $isId ? 'Aktif' : 'Active' }}</p>
            </div>
        </div>
 
        <!-- Total Submissions -->
        <div class="bg-white rounded-[24px] border border-slate-100 p-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-md transition-all duration-300 flex flex-col justify-between">
            <div class="flex items-center justify-center gap-2 mb-3">
                <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span class="text-[13.5px] font-bold text-slate-600 bg-slate-50 px-2 py-0.5 rounded-md">{{ $isId ? 'Total Naskah' : 'Total Submissions' }}</span>
            </div>
            <div class="text-center">
                <p class="text-3xl font-extrabold text-blue-600 tracking-tight leading-none mb-1">{{ $journals->sum('submissions_count') }}</p>
                <p class="text-[10px] font-bold tracking-wider text-slate-400">{{ $isId ? 'Artikel' : 'Articles' }}</p>
            </div>
        </div>
 
        <!-- Total Issues -->
        <div class="bg-white rounded-[24px] border border-slate-100 p-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-md transition-all duration-300 flex flex-col justify-between">
            <div class="flex items-center justify-center gap-2 mb-3">
                <div class="w-9 h-9 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <span class="text-[13.5px] font-bold text-slate-600 bg-slate-50 px-2 py-0.5 rounded-md">{{ $isId ? 'Terbitan Dipublikasikan' : 'Published Issues' }}</span>
            </div>
            <div class="text-center">
                <p class="text-3xl font-extrabold text-purple-600 tracking-tight leading-none mb-1">{{ $journals->sum('issues_count') }}</p>
                <p class="text-[10px] font-bold tracking-wider text-slate-400">{{ $isId ? 'Terbitan' : 'Issues' }}</p>
            </div>
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
