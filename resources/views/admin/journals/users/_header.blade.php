@php
    $activeTab = $activeTab ?? 'users';
    $currentLoc = session('app_locale', app()->getLocale());
    $isId = in_array($currentLoc, ['id', 'id_ID']);
@endphp

<!-- Breadcrumb -->
<div class="mb-4">
    <nav class="flex mb-2" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">{{ $isId ? 'Manajer Jurnal' : 'Journal Manager' }}</span>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fa-solid fa-chevron-right text-slate-300 mx-2 text-[10px]"></i>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">{{ $isId ? 'Pengguna & Peran' : 'Users & Roles' }}</span>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fa-solid fa-chevron-right text-slate-300 mx-2 text-[10px]"></i>
                    <span class="text-xs font-bold text-primary-600 uppercase tracking-widest">
                        @if($activeTab === 'users')
                            {{ $isId ? 'Pengguna' : 'Users' }}
                        @elseif($activeTab === 'roles')
                            {{ $isId ? 'Peran' : 'Roles' }}
                        @elseif($activeTab === 'access')
                            {{ $isId ? 'Akses Situs' : 'Access' }}
                        @elseif($activeTab === 'notify')
                            {{ $isId ? 'Notifikasi Pengguna' : 'Notify Users' }}
                        @endif
                    </span>
                </div>
            </li>
        </ol>
    </nav>
</div>

<!-- Header Modul (Judul dan Tombol Aksi) -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <x-text.h1>{{ $isId ? 'Pengguna & Peran' : 'Users & Roles' }}</x-text.h1>
        <x-text.body class="mt-1 text-slate-500 block">
            @if($activeTab === 'users')
                {{ $isId ? 'Kelola pengguna yang terdaftar di' : 'Manage users enrolled in' }} <strong>{{ $journal->name }}</strong>{{ $isId ? '.' : '.' }}
            @elseif($activeTab === 'roles')
                {{ $isId ? 'Konfigurasikan peran pengguna dan tingkat akses alur kerja mereka.' : 'Configure user roles and their workflow access levels.' }}
            @elseif($activeTab === 'access')
                {{ $isId ? 'Konfigurasikan siapa yang dapat mendaftar dan mengakses jurnal ini.' : 'Configure who can register and access this journal.' }}
            @elseif($activeTab === 'notify')
                {{ $isId ? 'Kirim notifikasi surel massal ke pengguna di' : 'Send a bulk email notification to users in' }} <strong>{{ $journal->name }}</strong>{{ $isId ? '.' : '.' }}
            @endif
        </x-text.body>
    </div>
    
    <!-- Tombol Aksi Kanan Atas -->
    <div class="flex items-center gap-3">
        @if($activeTab === 'users')
            <a href="{{ route($routePrefix . '.enroll', ['journal' => $journal->slug]) }}"
                class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 text-slate-700 bg-white rounded-lg hover:bg-slate-50 text-sm font-medium transition-colors shadow-[0_2px_4px_rgba(0,0,0,0.02)] whitespace-nowrap">
                <i class="fa-solid fa-user-plus text-slate-400"></i>
                {{ $isId ? 'Daftarkan Pengguna' : 'Enroll Existing User' }}
            </a>
            <a href="{{ route($routePrefix . '.create', ['journal' => $journal->slug]) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition-colors shadow-sm shadow-blue-100 whitespace-nowrap">
                <i class="fa-solid fa-plus text-white"></i>
                {{ $isId ? '+ Buat Pengguna Baru' : '+ Create New User' }}
            </a>
        @elseif($activeTab === 'roles')
            <a href="{{ route($routePrefix . '.roles.create', ['journal' => $journal->slug]) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition-colors shadow-sm shadow-blue-100 whitespace-nowrap">
                <i class="fa-solid fa-plus text-white"></i>
                {{ $isId ? '+ Buat Peran Baru' : '+ Create New Role' }}
            </a>
        @endif
    </div>
</div>

<!-- Tabs UI (Horizontal Navigation) -->
<div class="border-b border-slate-200 mb-6">
    <nav class="-mb-px flex space-x-8 overflow-x-auto scrollbar-none" aria-label="Tabs">
        <a href="{{ route($routePrefix . '.index', ['journal' => $journal->slug]) }}"
            class="border-b-2 py-3 px-1 text-sm font-medium transition-all whitespace-nowrap flex items-center gap-2 {{ $activeTab === 'users' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <i class="fa-solid fa-user-group text-base {{ $activeTab === 'users' ? 'text-primary-600' : 'text-slate-400' }}"></i>
            {{ $isId ? 'Pengguna' : 'Users' }}
        </a>
        <a href="{{ route($routePrefix . '.roles', ['journal' => $journal->slug]) }}"
            class="border-b-2 py-3 px-1 text-sm font-medium transition-all whitespace-nowrap flex items-center gap-2 {{ $activeTab === 'roles' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <i class="fa-solid fa-user-tag text-base {{ $activeTab === 'roles' ? 'text-primary-600' : 'text-slate-400' }}"></i>
            {{ $isId ? 'Peran' : 'Roles' }}
        </a>
        <a href="{{ route($routePrefix . '.access', ['journal' => $journal->slug]) }}"
            class="border-b-2 py-3 px-1 text-sm font-medium transition-all whitespace-nowrap flex items-center gap-2 {{ $activeTab === 'access' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <i class="fa-solid fa-lock text-base {{ $activeTab === 'access' ? 'text-primary-600' : 'text-slate-400' }}"></i>
            {{ $isId ? 'Akses Situs' : 'Site Access' }}
        </a>
        <a href="{{ route($routePrefix . '.notify', ['journal' => $journal->slug]) }}"
            class="border-b-2 py-3 px-1 text-sm font-medium transition-all whitespace-nowrap flex items-center gap-2 {{ $activeTab === 'notify' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <i class="fa-solid fa-envelope text-base {{ $activeTab === 'notify' ? 'text-primary-600' : 'text-slate-400' }}"></i>
            {{ $isId ? 'Notifikasi Pengguna' : 'Notify Users' }}
        </a>
    </nav>
</div>
