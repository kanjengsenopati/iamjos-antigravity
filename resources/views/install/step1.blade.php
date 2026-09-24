@extends('install.layout', ['step' => 1])

@section('content')

<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-800">Pre-Flight System Check</h2>
        <p class="text-gray-500 text-sm mt-1">Verifikasi lengkap semua dependensi, ekstensi, dan konfigurasi server sebelum memulai instalasi.</p>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 1: PHP RUNTIME ENVIRONMENT                     --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                PHP Runtime Environment
            </h3>
        </div>
        <div class="p-4 space-y-5">

            {{-- PHP Version --}}
            <div class="flex items-center justify-between p-3 rounded-md {{ $requirements['php']['pass'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                <div>
                    <span class="font-medium text-gray-700">PHP Version</span>
                    <span class="text-xs text-gray-500 block">Minimum: {{ $requirements['php']['required'] }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-mono border px-2 py-1 bg-white rounded">{{ $requirements['php']['current'] }}</span>
                    @if($requirements['php']['pass'])
                        <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @else
                        <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    @endif
                </div>
            </div>

            {{-- Required Extensions --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Ekstensi Wajib</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($requirements['extensions'] as $ext => $info)
                    <div class="flex items-center justify-between bg-white border border-gray-100 p-2.5 rounded-md shadow-sm">
                        <div>
                            <span class="text-gray-700 font-mono text-sm">{{ $ext }}</span>
                            <span class="text-xs text-gray-400 block">{{ $info['purpose'] }}</span>
                        </div>
                        @if($info['loaded'])
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                OK
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-red-700 bg-red-50 px-2 py-0.5 rounded">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Missing
                            </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Image Processing Driver --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Driver Pemrosesan Gambar <span class="text-gray-400 normal-case font-normal">(minimal 1 aktif)</span></p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(['gd' => 'GD Library', 'imagick' => 'ImageMagick'] as $driver => $label)
                    <div class="flex items-center justify-between bg-white border border-gray-100 p-2.5 rounded-md shadow-sm">
                        <span class="text-gray-700 font-mono text-sm">{{ $label }}</span>
                        @if($requirements['imageDriver'][$driver])
                            <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded">Tersedia</span>
                        @else
                            <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded">Tidak Ada</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @if(!$requirements['imageDriver']['pass'])
                    <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Minimal satu driver gambar (GD atau Imagick) harus aktif untuk upload & resize gambar.
                    </p>
                @endif
            </div>

            {{-- Database Drivers --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Driver Database <span class="text-gray-400 normal-case font-normal">(minimal 1 aktif)</span></p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    @foreach($requirements['databaseDrivers'] as $driver => $info)
                    <div class="flex items-center justify-between bg-white border border-gray-100 p-2.5 rounded-md shadow-sm">
                        <div>
                            <span class="text-gray-700 font-mono text-sm">{{ $driver }}</span>
                            <span class="text-xs text-gray-400 block">{{ $info['label'] }}</span>
                        </div>
                        @if($info['loaded'])
                            <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded">Tersedia</span>
                        @else
                            <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded">Tidak Ada</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @if(!$requirements['anyDatabaseDriver'])
                    <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Minimal satu driver database harus aktif. Install ekstensi <code class="bg-red-100 px-1 rounded">pdo_pgsql</code>, <code class="bg-red-100 px-1 rounded">pdo_mysql</code>, atau <code class="bg-red-100 px-1 rounded">pdo_sqlite</code>.
                    </p>
                @endif
            </div>

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 2: FILE & DIRECTORY PERMISSIONS                --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                File & Directory Permissions
            </h3>
        </div>
        <div class="p-4">
            <div class="space-y-1.5">
                @foreach($requirements['permissions'] as $path => $writable)
                <div class="flex items-center justify-between bg-white border border-gray-100 p-2.5 rounded-md shadow-sm">
                    <span class="text-gray-700 font-mono text-sm">{{ $path }}</span>
                    @if($writable)
                        <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded border border-green-200">Writable</span>
                    @else
                        <span class="text-xs font-medium text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-200">Not Writable</span>
                    @endif
                </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-500 mt-3 italic">
                Pastikan user web server memiliki izin tulis. Jalankan:
                <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-600 not-italic">chmod -R 775 storage bootstrap/cache</code>
            </p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 3: APPLICATION FOUNDATION                      --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Application Foundation
            </h3>
        </div>
        <div class="p-4 space-y-1.5">

            {{-- Composer Vendor --}}
            <div class="flex items-center justify-between bg-white border border-gray-100 p-2.5 rounded-md shadow-sm">
                <div>
                    <span class="text-gray-700 text-sm font-medium">Composer Dependencies</span>
                    <span class="text-xs text-gray-400 block">vendor/ & autoload.php</span>
                </div>
                @if($requirements['foundation']['vendor_exists'])
                    <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded">Terinstall</span>
                @else
                    <span class="text-xs font-medium text-red-700 bg-red-50 px-2 py-0.5 rounded">Tidak Ditemukan</span>
                @endif
            </div>

            {{-- .env File --}}
            <div class="flex items-center justify-between bg-white border border-gray-100 p-2.5 rounded-md shadow-sm">
                <div>
                    <span class="text-gray-700 text-sm font-medium">Environment File (.env)</span>
                    <span class="text-xs text-gray-400 block">Konfigurasi aplikasi</span>
                </div>
                @if($requirements['foundation']['env_exists'])
                    <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded">Ada</span>
                @else
                    <span class="text-xs font-medium text-red-700 bg-red-50 px-2 py-0.5 rounded">Tidak Ada</span>
                @endif
            </div>

            {{-- APP_KEY --}}
            <div class="flex items-center justify-between bg-white border border-gray-100 p-2.5 rounded-md shadow-sm">
                <div>
                    <span class="text-gray-700 text-sm font-medium">Encryption Key (APP_KEY)</span>
                    <span class="text-xs text-gray-400 block">Enkripsi session, cookie, & CSRF token</span>
                </div>
                @if($requirements['foundation']['app_key_set'])
                    <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded">Terkonfigurasi</span>
                @else
                    <span class="text-xs font-medium text-red-700 bg-red-50 px-2 py-0.5 rounded">Belum Di-set</span>
                @endif
            </div>

            {{-- Storage Symlink --}}
            <div class="flex items-center justify-between bg-white border border-gray-100 p-2.5 rounded-md shadow-sm">
                <div>
                    <span class="text-gray-700 text-sm font-medium">Storage Symlink</span>
                    <span class="text-xs text-gray-400 block">public/storage → storage/app/public</span>
                </div>
                @if($requirements['foundation']['storage_link'])
                    <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded">Aktif</span>
                @else
                    <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded">Belum Dibuat</span>
                @endif
            </div>

            {{-- Contextual Help Messages --}}
            @if(!$requirements['foundation']['vendor_exists'])
                <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-xs text-red-700">
                        <strong>Composer belum diinstall.</strong> Jalankan perintah berikut di root direktori aplikasi:
                        <code class="block mt-1 bg-red-100 px-2 py-1 rounded">composer install --no-dev --optimize-autoloader</code>
                    </p>
                </div>
            @endif

            @if(!$requirements['foundation']['env_exists'])
                <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-xs text-red-700">
                        <strong>File .env tidak ditemukan.</strong> Salin dari template:
                        <code class="block mt-1 bg-red-100 px-2 py-1 rounded">cp .env.example .env && php artisan key:generate</code>
                    </p>
                </div>
            @elseif(!$requirements['foundation']['app_key_set'])
                <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-xs text-red-700">
                        <strong>APP_KEY belum di-generate.</strong> Jalankan:
                        <code class="block mt-1 bg-red-100 px-2 py-1 rounded">php artisan key:generate</code>
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 4: FRONTEND BUILD ASSETS (NON-BLOCKING)        --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @php $frontendReady = $requirements['frontend']['build_exists'] && $requirements['frontend']['manifest_exists']; @endphp
    <div class="bg-white rounded-lg border {{ $frontendReady ? 'border-gray-200' : 'border-amber-300' }} overflow-hidden">
        <div class="px-4 py-3 {{ $frontendReady ? 'bg-gray-50 border-b border-gray-200' : 'bg-amber-50 border-b border-amber-200' }}">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <svg class="h-4 w-4 {{ $frontendReady ? 'text-indigo-500' : 'text-amber-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Frontend Build Assets
                <span class="text-xs font-normal text-gray-400 normal-case ml-1">(tidak memblokir instalasi)</span>
            </h3>
        </div>
        <div class="p-4 space-y-1.5">
            <div class="flex items-center justify-between bg-white border border-gray-100 p-2.5 rounded-md shadow-sm">
                <div>
                    <span class="text-gray-700 text-sm font-medium">Build Directory</span>
                    <span class="text-xs text-gray-400 block">public/build/</span>
                </div>
                @if($requirements['frontend']['build_exists'])
                    <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded">Ada</span>
                @else
                    <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded">Belum Ada</span>
                @endif
            </div>

            <div class="flex items-center justify-between bg-white border border-gray-100 p-2.5 rounded-md shadow-sm">
                <div>
                    <span class="text-gray-700 text-sm font-medium">Vite Manifest</span>
                    <span class="text-xs text-gray-400 block">manifest.json</span>
                </div>
                @if($requirements['frontend']['manifest_exists'])
                    <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded">Ada</span>
                @else
                    <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded">Belum Ada</span>
                @endif
            </div>

            @if(!$frontendReady)
                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-md">
                    <p class="text-xs text-amber-800">
                        <strong>Aset frontend belum di-compile.</strong> Halaman utama aplikasi tidak akan tampil dengan benar tanpa build assets.
                        <code class="block mt-1 bg-amber-100 px-2 py-1 rounded text-amber-900">npm install && npm run build</code>
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 5: OPTIONAL FEATURES (NON-BLOCKING)            --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Fitur Opsional
                <span class="text-xs font-normal text-gray-400 normal-case ml-1">(tidak memblokir instalasi)</span>
            </h3>
        </div>
        <div class="p-4 space-y-4">

            {{-- System Binaries --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Binary Sistem Eksternal</p>
                <div class="space-y-1.5">
                    @foreach($requirements['optionalBinaries'] as $binary => $info)
                    <div class="flex items-center justify-between bg-white border border-gray-100 p-2.5 rounded-md shadow-sm">
                        <div>
                            <span class="text-gray-700 font-mono text-sm">{{ $binary }}</span>
                            <span class="text-xs text-gray-400 block">{{ $info['purpose'] }}</span>
                        </div>
                        @if($info['available'] === null)
                            <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded" title="exec() disabled, tidak dapat memeriksa">Tidak Dapat Dicek</span>
                        @elseif($info['available'])
                            <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded">Tersedia</span>
                        @else
                            <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded">Tidak Tersedia</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Critical PHP Functions --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Fungsi PHP Kritis</p>
                <div class="space-y-1.5">
                    @foreach($requirements['criticalFunctions'] as $func => $info)
                    <div class="flex items-center justify-between bg-white border border-gray-100 p-2.5 rounded-md shadow-sm">
                        <div>
                            <span class="text-gray-700 font-mono text-sm">{{ $func }}()</span>
                            <span class="text-xs text-gray-400 block">{{ $info['purpose'] }}</span>
                        </div>
                        @if(!$info['disabled'])
                            <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded">Aktif</span>
                        @else
                            <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded">Disabled</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @if(collect($requirements['criticalFunctions'])->where('disabled', true)->isNotEmpty())
                    <p class="text-xs text-amber-700 mt-2 italic">
                        Beberapa fungsi PHP dinonaktifkan. Ini umum pada shared hosting (aaPanel/cPanel).
                        Hapus dari <code class="bg-amber-100 px-1 rounded not-italic">disable_functions</code> di php.ini jika diperlukan.
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- STATUS BANNER & ACTION BUTTONS                         --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="pt-4 border-t border-gray-200">
        @if(!$requirements['allPass'])
            <div class="mb-4 p-3.5 bg-red-50 border border-red-200 rounded-lg flex items-start gap-2.5">
                <svg class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="text-sm text-red-800 font-semibold">Persyaratan Belum Terpenuhi</p>
                    <p class="text-xs text-red-600 mt-0.5">Selesaikan semua item bertanda merah di atas, lalu klik <strong>Re-Check</strong> untuk memverifikasi ulang.</p>
                </div>
            </div>
        @else
            <div class="mb-4 p-3.5 bg-green-50 border border-green-200 rounded-lg flex items-start gap-2.5">
                <svg class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="text-sm text-green-800 font-semibold">Semua Persyaratan Wajib Terpenuhi!</p>
                    <p class="text-xs text-green-600 mt-0.5">Server Anda siap. Anda dapat melanjutkan ke tahap konfigurasi database.</p>
                </div>
            </div>
        @endif

        <div class="flex justify-end gap-3">
            <a href="{{ route('install.index') }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition text-sm font-medium">
                Re-Check
            </a>
            
            <a href="{{ $requirements['allPass'] ? route('install.step2') : '#' }}" 
               class="px-5 py-2.5 rounded-md text-sm font-medium transition {{ $requirements['allPass'] ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm' : 'bg-indigo-300 text-white cursor-not-allowed' }}"
               @if(!$requirements['allPass']) onclick="event.preventDefault(); alert('Selesaikan semua persyaratan wajib (bertanda merah) sebelum melanjutkan ke tahap berikutnya.');" @endif>
                Next Step &rarr;
            </a>
        </div>
    </div>
</div>

@endsection
