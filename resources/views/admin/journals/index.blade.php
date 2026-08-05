@extends('layouts.admin')

@section('title', $isId ? 'Jurnal yang Dihosting' : 'Hosted Journals')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $isId ? 'Jurnal yang Dihosting' : 'Hosted Journals' }}</h1>
            <p class="mt-1 text-gray-500">{{ $isId ? 'Kelola semua jurnal yang dihosting pada instalasi ini.' : 'Manage all journals hosted on this installation.' }}</p>
        </div>
        <a href="{{ route('admin.journals.create') }}"
            class="inline-flex items-center justify-center gap-2
          px-5 py-2.5
          bg-indigo-600 text-white
          rounded-xl font-semibold
          shadow-md shadow-indigo-600/30
          hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-700/40
          active:scale-95
          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
          transition-all duration-200">
            <i class="fa-solid fa-plus text-sm"></i>
            <span>{{ $isId ? 'Jurnal' : 'Journal' }}</span>
        </a>


    </div>

    <!-- Stats Bar (scirepid.com Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Card 1: Total Journals (Blue Theme) -->
        <div class="relative overflow-hidden bg-blue-50/70 border border-blue-100/80 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-4">
            <div class="w-14 h-14 rounded-[20px] bg-blue-600 shadow-md shadow-blue-500/25 flex items-center justify-center text-white flex-shrink-0 relative z-10">
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
            <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-blue-200/50 pointer-events-none"></div>
        </div>

        <!-- Card 2: Active Journals (Emerald Theme) -->
        <div class="relative overflow-hidden bg-emerald-50/70 border border-emerald-100/80 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-4">
            <div class="w-14 h-14 rounded-[20px] bg-emerald-600 shadow-md shadow-emerald-500/25 flex items-center justify-center text-white flex-shrink-0 relative z-10">
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
            <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-emerald-200/50 pointer-events-none"></div>
        </div>

        <!-- Card 3: Disabled Journals (Amber / Orange Theme) -->
        <div class="relative overflow-hidden bg-amber-50/70 border border-amber-100/80 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-4">
            <div class="w-14 h-14 rounded-[20px] bg-amber-600 shadow-md shadow-amber-500/25 flex items-center justify-center text-white flex-shrink-0 relative z-10">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
            <div class="relative z-10 min-w-0">
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-none mb-1">{{ number_format($journals->where('enabled', false)->count()) }}</p>
                <h3 class="text-sm font-bold text-slate-800 leading-snug truncate">{{ $isId ? 'Jurnal Nonaktif' : 'Disabled Journals' }}</h3>
                <p class="text-[11px] font-medium text-slate-500 truncate leading-tight mt-0.5">{{ $isId ? 'Status dinonaktifkan' : 'Currently inactive' }}</p>
            </div>
            <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-amber-200/50 pointer-events-none"></div>
        </div>

        <!-- Card 4: Total Articles (Purple / Violet Theme) -->
        <div class="relative overflow-hidden bg-purple-50/70 border border-purple-100/80 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-4">
            <div class="w-14 h-14 rounded-[20px] bg-violet-600 shadow-md shadow-violet-500/25 flex items-center justify-center text-white flex-shrink-0 relative z-10">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="relative z-10 min-w-0">
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-none mb-1">{{ number_format($journals->sum('submissions_count')) }}</p>
                <h3 class="text-sm font-bold text-slate-800 leading-snug truncate">{{ $isId ? 'Total Artikel' : 'Total Articles' }}</h3>
                <p class="text-[11px] font-medium text-slate-500 truncate leading-tight mt-0.5">{{ $isId ? 'Artikel terdaftar' : 'Peer-reviewed articles' }}</p>
            </div>
            <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-purple-200/50 pointer-events-none"></div>
        </div>
    </div>

    <!-- Journals Table -->
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <input type="text" placeholder="{{ $isId ? 'Cari jurnal...' : 'Search journals...' }}" id="searchInput"
                        class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-64"
                        style="padding-left: 2.5rem !important;">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500">
                {{ $isId ? 'Menampilkan' : 'Showing' }} <span class="font-medium text-gray-900" id="visibleCount">{{ $journals->count() }}</span> {{ $isId ? 'dari' : 'of' }}
                <span class="font-medium text-gray-900">{{ $journals->count() }}</span> {{ $isId ? 'jurnal' : 'journals' }}
            </p>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full" id="journalsTable">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">{{ $isId ? 'Jalur' : 'Path' }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $isId ? 'Nama' : 'Name' }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Status</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $isId ? 'Deskripsi' : 'Description' }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right w-64">
                            {{ $isId ? 'Aksi' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($journals as $journal)
                        <tr class="journal-row hover:bg-gray-50 transition-colors"
                            data-name="{{ strtolower($journal->name) }}" data-path="{{ strtolower($journal->slug) }}">
                            <td class="px-6 py-4">
                                <a href="{{ route('journal.public.home', $journal->slug) }}" target="_blank"
                                    class="group flex items-center gap-1 hover:text-indigo-800 transition-colors"
                                    title="View Journal Homepage">
                                    <code
                                        class="font-mono text-sm text-indigo-600 bg-indigo-50 group-hover:bg-indigo-100 px-2 py-1 rounded transition-colors">/{{ $journal->slug }}</code>
                                    <svg class="w-3 h-3 text-indigo-400 group-hover:text-indigo-600 opacity-0 group-hover:opacity-100 transition-all"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($journal->logo_path)
                                        <img src="{{ Storage::disk('public')->url($journal->logo_path) }}" alt="{{ $journal->name }}"
                                            class="w-10 h-10 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                                    @else
                                        <div
                                            class="w-10 h-10 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <span
                                                class="text-indigo-600 font-bold text-sm">{{ strtoupper(substr($journal->abbreviation ?? $journal->name, 0, 2)) }}</span>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 truncate">{{ $journal->name }}</p>
                                        @if ($journal->abbreviation)
                                            <p class="text-xs text-gray-500">{{ $journal->abbreviation }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($journal->enabled)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                        {{ $isId ? 'Aktif' : 'Active' }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                        {{ $isId ? 'Nonaktif' : 'Disabled' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-600 line-clamp-2 max-w-md">
                                    {{ $journal->description ?? ($isId ? 'Deskripsi tidak tersedia.' : 'No description available.') }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Settings Wizard -->
                                    <a href="{{ route('admin.journals.edit', $journal) }}"
                                        class="group relative inline-flex items-center justify-center w-9 h-9 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors"
                                        title="{{ $isId ? 'Wisaya Pengaturan' : 'Settings Wizard' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                        <span
                                            class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                            {{ $isId ? 'Wisaya Pengaturan' : 'Settings Wizard' }}
                                        </span>
                                    </a>

                                    <!-- Users -->
                                    <a href="{{ route('journal.admin.users.index', ['journal' => $journal->slug]) }}"
                                        class="group relative inline-flex items-center justify-center w-9 h-9 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition-colors"
                                        title="{{ $isId ? 'Pengguna' : 'Users' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <span
                                            class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                            {{ $isId ? 'Pengguna' : 'Users' }}
                                        </span>
                                    </a>

                                    <!-- Edit -->
                                    <a href="{{ route('admin.journals.edit', $journal) }}"
                                        class="group relative inline-flex items-center justify-center w-9 h-9 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors"
                                        title="{{ $isId ? 'Edit' : 'Edit' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span
                                            class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                            {{ $isId ? 'Edit' : 'Edit' }}
                                        </span>
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.journals.destroy', $journal) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('{{ $isId ? 'Apakah Anda yakin ingin menghapus jurnal ini? Tindakan ini tidak dapat dibatalkan.' : 'Are you sure you want to delete this journal? This action cannot be undone.' }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="group relative inline-flex items-center justify-center w-9 h-9 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span
                                                class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                                                {{ $isId ? 'Hapus' : 'Delete' }}
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div
                                    class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $isId ? 'Jurnal Tidak Ditemukan' : 'No Journals Found' }}</h3>
                                <p class="text-gray-500 mb-6">{{ $isId ? 'Mulai dengan membuat jurnal pertama Anda.' : 'Get started by creating your first journal.' }}</p>
                                <a href="{{ route('admin.journals.create') }}"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <!-- Pagination (if using pagination) -->
        @if ($journals->count() > 10)
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-sm text-gray-500">{{ $isId ? 'Menampilkan semua' : 'Showing all' }} {{ $journals->count() }} {{ $isId ? 'jurnal' : 'journals' }}</p>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Simple client-side search
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.journal-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.dataset.name;
                const path = row.dataset.path;
                const isVisible = name.includes(searchTerm) || path.includes(searchTerm);

                row.style.display = isVisible ? '' : 'none';
                if (isVisible) visibleCount++;
            });

            document.getElementById('visibleCount').textContent = visibleCount;
        });
    </script>
@endpush
