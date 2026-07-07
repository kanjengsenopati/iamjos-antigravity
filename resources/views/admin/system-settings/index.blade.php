@extends('layouts.admin')

@section('title', $isId ? 'Pengaturan Sistem' : 'System Settings')

@section('content')
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ $isId ? 'Pengaturan Sistem' : 'System Settings' }}</h1>
        <p class="mt-1 text-gray-500">{{ $isId ? 'Kelola konfigurasi teknis untuk seluruh aplikasi. Perubahan akan segera diterapkan.' : 'Manage application-wide technical configuration. Changes take effect immediately.' }}</p>
    </div>

    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 flex items-start gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $groupLabels = [
            'email'        => $isId ? 'Email & Server SMTP' : 'Email & SMTP Server',
            'pagination'   => $isId ? 'Batas Tampilan & Halaman' : 'Pagination & Display Limits',
            'uploads'      => $isId ? 'Batasan Unggahan Berkas' : 'File Upload Constraints',
            'reviewer'     => $isId ? 'Pengingat Reviewer' : 'Reviewer Reminders',
            'integrations' => $isId ? 'Integrasi Eksternal' : 'External Integrations',
            'app'          => $isId ? 'Aplikasi' : 'Application',
        ];

        $groupIcons = [
            'email'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />',
            'pagination'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />',
            'uploads'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />',
            'reviewer'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />',
            'integrations' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />',
            'app'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
        ];

        // URL-like keys that should use type="url"
        $urlKeys = ['crossref_deposit_url_live', 'crossref_deposit_url_test', 'crossref_api_base_url', 'recaptcha_verify_url', 'google_scholar_search_url'];

        // Indonesian translations for setting keys (labels)
        $settingLabels = [
            'pagination_submissions' => 'Submisi per Halaman',
            'pagination_issues' => 'Isu per Halaman',
            'pagination_journals' => 'Jurnal per Halaman',
            'pagination_reviews' => 'Penugasan Review per Halaman',
            'pagination_announcements' => 'Pengumuman per Halaman',
            'pagination_notifications' => 'Notifikasi per Halaman',
            'pagination_search_results' => 'Hasil Pencarian per Halaman',
            'pagination_portal_journals' => 'Jurnal per Halaman di Portal',
            'homepage_latest_articles_count' => 'Jumlah Artikel Terbaru di Beranda',
            'homepage_featured_journals_count' => 'Jumlah Jurnal Unggulan di Beranda',
            'homepage_announcements_count' => 'Jumlah Pengumuman di Beranda',
            'homepage_editorial_team_count' => 'Jumlah Tim Redaksi di Beranda',
            'portal_featured_journals_count' => 'Jumlah Jurnal Unggulan di Portal',
            'portal_latest_articles_count' => 'Jumlah Artikel Terbaru di Portal',
            'upload_max_size_manuscript' => 'Ukuran Maksimal Manuskrip',
            'upload_max_size_galley' => 'Ukuran Maksimal Berkas Galley',
            'upload_max_size_avatar' => 'Ukuran Maksimal Avatar',
            'upload_max_size_image' => 'Ukuran Maksimal Gambar Umum',
            'upload_allowed_extensions_manuscript' => 'Ekstensi Manuskrip yang Diperbolehkan',
            'upload_allowed_extensions_galley' => 'Ekstensi Berkas Galley yang Diperbolehkan',
            'upload_allowed_extensions_avatar' => 'Ekstensi Avatar yang Diperbolehkan',
            'upload_allowed_extensions_image' => 'Ekstensi Gambar yang Diperbolehkan',
            'reviewer_reminder_days_before' => 'Hari Pengingat Sebelum Batas Waktu',
            'reviewer_reminder_overdue_interval_days' => 'Interval Pengingat Keterlambatan (Hari)',
            'crossref_deposit_url_live' => 'URL Deposit Live Crossref',
            'crossref_deposit_url_test' => 'URL Deposit Uji Coba Crossref',
            'crossref_api_base_url' => 'URL Dasar API REST Crossref',
            'recaptcha_verify_url' => 'URL Verifikasi Google reCAPTCHA',
            'google_scholar_search_url' => 'URL Dasar Pencarian Google Scholar',
            'maintenance_mode' => 'Mode Pemeliharaan',
            'app_version' => 'Versi Aplikasi',
            'mail_mailer' => 'Driver Email / Mailer',
            'mail_host' => 'Alamat Host SMTP',
            'mail_port' => 'Port SMTP',
            'mail_username' => 'Username SMTP',
            'mail_password' => 'Password SMTP',
            'mail_encryption' => 'Protokol Enkripsi SMTP',
            'mail_from_address' => 'Alamat Email Pengirim (From Address)',
            'mail_from_name' => 'Nama Pengirim (From Name)',
            'mail_queue_connection' => 'Koneksi Antrean Email',
        ];

        // Indonesian translations for setting descriptions
        $settingDescriptions = [
            'pagination_submissions' => 'Jumlah submisi/artikel yang ditampilkan per halaman di dashboard.',
            'pagination_issues' => 'Jumlah terbitan/isu jurnal yang ditampilkan per halaman.',
            'pagination_journals' => 'Jumlah jurnal yang ditampilkan per halaman pada kelola jurnal.',
            'pagination_reviews' => 'Jumlah penugasan tinjauan sejawat (review) yang ditampilkan per halaman.',
            'pagination_announcements' => 'Jumlah pengumuman yang ditampilkan per halaman.',
            'pagination_notifications' => 'Jumlah notifikasi yang ditampilkan per halaman.',
            'pagination_search_results' => 'Jumlah hasil pencarian yang ditampilkan per halaman.',
            'pagination_portal_journals' => 'Jumlah daftar jurnal yang ditampilkan per halaman di portal utama.',
            'homepage_latest_articles_count' => 'Jumlah artikel terbaru yang ditampilkan di halaman beranda.',
            'homepage_featured_journals_count' => 'Jumlah jurnal unggulan yang ditampilkan di halaman beranda.',
            'homepage_announcements_count' => 'Jumlah pengumuman yang ditampilkan di halaman beranda.',
            'homepage_editorial_team_count' => 'Jumlah anggota tim redaksi yang ditampilkan di beranda.',
            'portal_featured_journals_count' => 'Jumlah jurnal unggulan yang ditampilkan di daftar portal.',
            'portal_latest_articles_count' => 'Jumlah artikel terbaru yang ditampilkan di portal utama.',
            'upload_max_size_manuscript' => 'Ukuran maksimal berkas manuskrip yang dapat diunggah dalam byte (default: 50 MB = 52428800).',
            'upload_max_size_galley' => 'Ukuran maksimal berkas produksi/galley yang dapat diunggah dalam byte (default: 100 MB = 104857600).',
            'upload_max_size_avatar' => 'Ukuran maksimal gambar avatar profil dalam byte (default: 2 MB = 2097152).',
            'upload_max_size_image' => 'Ukuran maksimal gambar umum dalam byte (default: 5 MB = 5242880).',
            'upload_allowed_extensions_manuscript' => 'Daftar ekstensi berkas manuskrip yang diperbolehkan, dipisahkan dengan koma.',
            'upload_allowed_extensions_galley' => 'Daftar ekstensi berkas galley yang diperbolehkan, dipisahkan dengan koma.',
            'upload_allowed_extensions_avatar' => 'Daftar ekstensi gambar avatar yang diperbolehkan, dipisahkan dengan koma.',
            'upload_allowed_extensions_image' => 'Daftar ekstensi gambar umum yang diperbolehkan, dipisahkan dengan koma.',
            'reviewer_reminder_days_before' => 'Hari-hari sebelum batas waktu untuk mengirimkan email pengingat kepada reviewer (dipisahkan dengan koma, misal: 7,3,1,0).',
            'reviewer_reminder_overdue_interval_days' => 'Interval dalam hari untuk mengirim kembali email pengingat reviewer setelah melewati batas waktu.',
            'crossref_deposit_url_live' => 'URL endpoint resmi (live) Crossref untuk pendaftaran DOI.',
            'crossref_deposit_url_test' => 'URL endpoint uji coba (sandbox/test) Crossref untuk pendaftaran DOI.',
            'crossref_api_base_url' => 'URL dasar REST API Crossref untuk pencarian dan penarikan metadata DOI.',
            'recaptcha_verify_url' => 'Endpoint verifikasi reCAPTCHA Google di sisi server.',
            'google_scholar_search_url' => 'URL dasar pencarian Google Scholar.',
            'maintenance_mode' => 'Jika diaktifkan, aplikasi akan menampilkan halaman pemeliharaan (maintenance) bagi pengunjung non-admin.',
            'app_version' => 'String versi aplikasi saat ini.',
            'mail_mailer' => 'Driver email yang akan digunakan (smtp, phpmail, atau log).',
            'mail_host' => 'Alamat server SMTP (SMTP host).',
            'mail_port' => 'Port server SMTP (contoh: 25, 465, 587, 1025, 2525).',
            'mail_username' => 'Username SMTP (kosongkan jika tidak diperlukan otentikasi).',
            'mail_password' => 'Password SMTP (kosongkan jika tidak diperlukan otentikasi).',
            'mail_encryption' => 'Protokol enkripsi koneksi SMTP (none, tls, atau ssl).',
            'mail_from_address' => 'Alamat email pengirim yang digunakan sebagai "From".',
            'mail_from_name' => 'Nama pengirim yang digunakan sebagai "From Name".',
            'mail_queue_connection' => 'Driver antrean email yang akan digunakan (sync = langsung dikirim/cPanel, database = antrean di database, redis = antrean di Redis).',
        ];
    @endphp

    <div x-data="{ activeTab: localStorage.getItem('system_settings_active_tab') || 'email' }">
        @if ($settings->isNotEmpty())
            <div class="flex flex-wrap gap-2.5 mb-8 pb-4 border-b border-gray-100">
                @foreach ($settings as $group => $groupSettings)
                    @php
                        $tabLabel = $groupLabels[$group] ?? ucfirst($group);
                        $tabIcon  = $groupIcons[$group] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4" />';
                    @endphp
                    <button
                        type="button"
                        @click="activeTab = '{{ $group }}'; localStorage.setItem('system_settings_active_tab', '{{ $group }}')"
                        class="px-4 py-2.5 rounded-xl text-sm font-medium transition-all cursor-pointer inline-flex items-center gap-2 border"
                        :class="activeTab === '{{ $group }}' 
                            ? 'bg-blue-600 border-blue-600 text-white shadow-md shadow-blue-500/20' 
                            : 'bg-white border-slate-100 text-slate-600 hover:bg-slate-50 hover:text-slate-800 shadow-sm'"
                    >
                        <svg class="w-4 h-4 flex-shrink-0" :class="activeTab === '{{ $group }}' ? 'text-white' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2px;">
                            {!! $tabIcon !!}
                        </svg>
                        <span>{{ $tabLabel }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        <div class="space-y-8 max-w-5xl">
            @forelse ($settings as $group => $groupSettings)
                @php
                    $label = $groupLabels[$group] ?? ucfirst($group);
                    $icon  = $groupIcons[$group] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 4a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />';
                    $isTwoCol = in_array($group, ['email', 'integrations', 'pagination', 'reviewer', 'uploads']);
                @endphp

                <div 
                    x-show="activeTab === '{{ $group }}'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden"
                >
                    <!-- Card Header -->
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $icon !!}
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-[16px] font-semibold text-slate-800">{{ $label }}</h2>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $groupSettings->count() }} {{ $isId ? 'pengaturan' : ('setting' . ($groupSettings->count() !== 1 ? 's' : '')) }}</p>
                        </div>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('admin.system-settings.update') }}" method="POST">
                        @csrf

                        <div class="{{ $isTwoCol ? 'p-6 grid grid-cols-1 md:grid-cols-2 gap-6' : 'divide-y divide-gray-50' }}">
                            @foreach ($groupSettings as $setting)
                                @php
                                    $displayLabel = $isId && isset($settingLabels[$setting->key]) 
                                        ? $settingLabels[$setting->key] 
                                        : ucwords(str_replace('_', ' ', $setting->key));
                                    
                                    $displayDescription = $isId && isset($settingDescriptions[$setting->key]) 
                                        ? $settingDescriptions[$setting->key] 
                                        : $setting->description;
                                @endphp
                                <div class="{{ $isTwoCol ? ($setting->type === 'json' ? 'md:col-span-2 space-y-1.5' : 'space-y-1.5') : 'px-6 py-5' }}">
                                    @if ($setting->key === 'mail_mailer')
                                    {{-- Custom Mailer Dropdown --}}
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ $displayLabel }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <select
                                        id="{{ $setting->key }}"
                                        name="{{ $setting->key }}"
                                        class="w-full {{ $isTwoCol ? '' : 'sm:w-64' }} px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer"
                                    >
                                        <option value="smtp" {{ old($setting->key, $setting->value) === 'smtp' ? 'selected' : '' }}>{{ $isId ? 'Server SMTP (Direkomendasikan)' : 'SMTP Server (Recommended)' }}</option>
                                        <option value="phpmail" {{ old($setting->key, $setting->value) === 'phpmail' ? 'selected' : '' }}>{{ $isId ? 'Fungsi PHP mail()' : 'PHP mail() Function' }}</option>
                                        <option value="log" {{ old($setting->key, $setting->value) === 'log' ? 'selected' : '' }}>{{ $isId ? 'Log (Hanya untuk Dev/Uji coba)' : 'Log (Dev/Testing only)' }}</option>
                                    </select>
                                    @if ($displayDescription)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $displayDescription }}</p>
                                    @endif

                                @elseif ($setting->key === 'mail_encryption')
                                    {{-- Custom Encryption Dropdown --}}
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ $displayLabel }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <select
                                        id="{{ $setting->key }}"
                                        name="{{ $setting->key }}"
                                        class="w-full {{ $isTwoCol ? '' : 'sm:w-64' }} px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer"
                                    >
                                        <option value="none" {{ old($setting->key, $setting->value) === 'none' ? 'selected' : '' }}>{{ $isId ? 'Tidak ada (Teks biasa)' : 'None (Plain text)' }}</option>
                                        <option value="tls" {{ old($setting->key, $setting->value) === 'tls' ? 'selected' : '' }}>TLS (STARTTLS - port 587)</option>
                                        <option value="ssl" {{ old($setting->key, $setting->value) === 'ssl' ? 'selected' : '' }}>SSL (SMTPS - port 465)</option>
                                    </select>
                                    @if ($displayDescription)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $displayDescription }}</p>
                                    @endif

                                @elseif ($setting->key === 'mail_queue_connection')
                                    {{-- Custom Queue Connection Dropdown --}}
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ $displayLabel }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <select
                                        id="{{ $setting->key }}"
                                        name="{{ $setting->key }}"
                                        class="w-full {{ $isTwoCol ? '' : 'sm:w-64' }} px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer"
                                    >
                                        <option value="sync" {{ old($setting->key, $setting->value) === 'sync' ? 'selected' : '' }}>{{ $isId ? 'Kirim Langsung (Tanpa Pengaturan Server / cPanel)' : 'Direct Send (No Server Setup / cPanel)' }}</option>
                                        <option value="database" {{ old($setting->key, $setting->value) === 'database' ? 'selected' : '' }}>{{ $isId ? 'Antrean Database (Direkomendasikan dengan Cron)' : 'Database Queue (Recommended with Cron)' }}</option>
                                        <option value="redis" {{ old($setting->key, $setting->value) === 'redis' ? 'selected' : '' }}>{{ $isId ? 'Antrean Redis (Performa Tinggi / Supervisor)' : 'Redis Queue (High Performance / Supervisor)' }}</option>
                                    </select>
                                    @if ($displayDescription)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $displayDescription }}</p>
                                    @endif

                                @elseif ($setting->key === 'mail_password')
                                    {{-- Custom Password Field with Show/Hide Toggle --}}
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ $displayLabel }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <div class="relative w-full {{ $isTwoCol ? '' : 'sm:w-80' }}" x-data="{ show: false }">
                                        <input
                                            :type="show ? 'text' : 'password'"
                                            id="{{ $setting->key }}"
                                            name="{{ $setting->key }}"
                                            value="{{ old($setting->key, $setting->value) }}"
                                            class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                        <button
                                            type="button"
                                            @click="show = !show"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer"
                                        >
                                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                    @if ($displayDescription)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $displayDescription }}</p>
                                    @endif

                                @elseif ($setting->type === 'boolean')
                                    {{-- Boolean: checkbox toggle --}}
                                    <div class="flex items-start gap-4">
                                        <div class="flex items-center h-6 mt-0.5">
                                            <input
                                                type="checkbox"
                                                id="{{ $setting->key }}"
                                                name="{{ $setting->key }}"
                                                value="1"
                                                {{ $setting->typed_value ? 'checked' : '' }}
                                                class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer"
                                            >
                                        </div>
                                        <label for="{{ $setting->key }}" class="cursor-pointer flex-1">
                                            <span class="block text-sm font-medium text-gray-900">
                                                {{ $displayLabel }}
                                            </span>
                                            @if ($displayDescription)
                                                <span class="block text-xs text-gray-500 mt-0.5">{{ $displayDescription }}</span>
                                            @endif
                                            <span class="block text-xs text-gray-400 mt-1 font-mono">{{ $setting->key }}</span>
                                        </label>
                                    </div>

                                @elseif ($setting->type === 'integer')
                                    {{-- Integer: number input --}}
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ $displayLabel }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <input
                                        type="number"
                                        id="{{ $setting->key }}"
                                        name="{{ $setting->key }}"
                                        value="{{ old($setting->key, $setting->value) }}"
                                        class="w-full {{ $isTwoCol ? '' : 'sm:w-64' }} px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                    @if ($displayDescription)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $displayDescription }}</p>
                                    @endif

                                @elseif ($setting->type === 'json')
                                    {{-- JSON: textarea --}}
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ $displayLabel }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <textarea
                                        id="{{ $setting->key }}"
                                        name="{{ $setting->key }}"
                                        rows="4"
                                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    >{{ old($setting->key, $setting->value) }}</textarea>
                                    @if ($displayDescription)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $displayDescription }}</p>
                                    @endif

                                @else
                                    {{-- String: text or url input --}}
                                    @php
                                        $inputType = in_array($setting->key, $urlKeys) ? 'url' : 'text';
                                    @endphp
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ $displayLabel }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <input
                                        type="{{ $inputType }}"
                                        id="{{ $setting->key }}"
                                        name="{{ $setting->key }}"
                                        value="{{ old($setting->key, $setting->value) }}"
                                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                    @if ($displayDescription)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $displayDescription }}</p>
                                    @endif
                                @endif
                                </div>
                            @endforeach
                        </div>

                        @if ($group === 'email')
                            {{-- Test Connection Section --}}
                            <div class="px-6 py-6 bg-slate-50 border-t border-gray-100" x-data="testEmailHandler()">
                                <h2 class="text-[16px] font-semibold text-slate-800 mb-1">{{ $isId ? 'Uji Konfigurasi SMTP' : 'Test SMTP Configuration' }}</h2>
                                <p class="text-[13px] text-slate-500 mb-4 font-normal">{{ $isId ? 'Kirim email tes untuk memverifikasi bahwa server SMTP Anda dikonfigurasi dengan benar. Simpan pengaturan Anda sebelum menguji.' : 'Send a test email to verify that your SMTP server is configured correctly. Save your settings before testing.' }}</p>
                                
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <input 
                                        type="email" 
                                        x-model="email" 
                                        placeholder="recipient@example.com" 
                                        class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm w-full sm:w-80 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition-all"
                                    >
                                    <button 
                                        type="button" 
                                        @click="sendTestEmail()"
                                        :disabled="loading || !email"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white text-sm font-semibold rounded-xl shadow-md shadow-blue-500/10 hover:shadow-blue-500/20 transition-all cursor-pointer"
                                    >
                                        <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-text="loading ? '{{ $isId ? 'Mengirim...' : 'Sending...' }}' : '{{ $isId ? 'Kirim Email Tes' : 'Send Test Email' }}'">{{ $isId ? 'Kirim Email Tes' : 'Send Test Email' }}</span>
                                    </button>
                                </div>
                                
                                <div x-show="statusMessage" class="mt-4 p-4 rounded-[16px] text-sm" :class="statusType === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-100/60' : 'bg-red-50 text-red-800 border border-red-100/60'" style="display: none;">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <template x-if="statusType === 'success'">
                                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2.5px;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </template>
                                            <template x-if="statusType !== 'success'">
                                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2.5px;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </template>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-semibold text-[14px]" :class="statusType === 'success' ? 'text-emerald-900' : 'text-red-900'" x-text="statusType === 'success' ? 'Pengiriman Berhasil' : 'Pengiriman Gagal'"></p>
                                            <p class="mt-0.5 text-xs opacity-90 leading-relaxed" x-text="statusMessage"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Save Button -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
                        <button
                             type="submit"
                             class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl shadow-sm shadow-indigo-500/25 hover:bg-indigo-700 hover:shadow-indigo-500/40 transition-all"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $isId ? 'Simpan' : 'Save' }} {{ $label }}
                        </button>
                    </div>
                </form>
            </div>
        @empty
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-12 text-center max-w-2xl mx-auto">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                
                <h3 class="text-lg font-semibold text-slate-800 mb-2">{{ $isId ? 'Pengaturan Sistem Tidak Ditemukan' : 'No System Settings Found' }}</h3>
                <p class="text-slate-500 text-sm mb-8 leading-relaxed">
                    {{ $isId ? 'Tabel konfigurasi sistem saat ini kosong. Inisialisasi database dengan pengaturan default untuk mengonfigurasi email/SMTP, batasan berkas, integrasi, dan parameter lainnya.' : 'The system configuration table is currently empty. Initialize the database with default settings to configure email/SMTP, file constraints, integrations, and other parameters.' }}
                </p>

                <form action="{{ route('admin.system-settings.seed') }}" method="POST" class="inline-block">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-lg shadow-blue-500/20 hover:shadow-blue-500/35 transition-all cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        {{ $isId ? 'Inisialisasi Pengaturan Default' : 'Initialize Default Settings' }}
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-gray-400 text-xs">{{ $isId ? 'Metode Alternatif CLI:' : 'Alternative CLI Method:' }}</p>
                    <code class="block mt-2 px-3 py-2 bg-slate-50 border border-slate-100 rounded-lg text-slate-600 text-xs font-mono select-all">php artisan db:seed --class=SystemSettingsSeeder</code>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
    function testEmailHandler() {
        return {
            email: '',
            loading: false,
            statusMessage: '',
            statusType: '',

            sendTestEmail() {
                if (!this.email) return;
                this.loading = true;
                this.statusMessage = '';
                this.statusType = '';

                fetch("{{ route('admin.system-settings.test-email') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ email: this.email })
                })
                .then(async response => {
                    const isJson = response.headers.get('content-type')?.includes('application/json');
                    const data = isJson ? await response.json() : null;
                    return {
                        status: response.status,
                        body: data,
                        text: !isJson ? await response.text() : null
                    };
                })
                .then(res => {
                    this.loading = false;
                    if (res.status === 200 && res.body && res.body.success) {
                        this.statusType = 'success';
                        this.statusMessage = res.body.message || 'Test email sent successfully!';
                    } else {
                        this.statusType = 'error';
                        if (res.body && res.body.message) {
                            this.statusMessage = res.body.message;
                        } else if (res.status === 500) {
                            this.statusMessage = 'SMTP Test Failed: Kesalahan Internal Server (Status 500). Silakan periksa konfigurasi SMTP Anda.';
                        } else {
                            this.statusMessage = 'Gagal mengirim email tes. Status kode: ' + res.status;
                        }
                    }
                })
                .catch(error => {
                    this.loading = false;
                    this.statusType = 'error';
                    this.statusMessage = 'Terjadi kesalahan jaringan atau koneksi terputus: ' + error.message;
                });
            }
        };
    }
</script>
@endpush
