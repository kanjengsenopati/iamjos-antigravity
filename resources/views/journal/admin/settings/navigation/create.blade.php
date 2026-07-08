@extends('layouts.app')

@php
    $currentLoc = session('app_locale', app()->getLocale());
    $isId = in_array($currentLoc, ['id', 'id_ID']);
    $systemLabels = [
        'Home' => 'Beranda',
        'About' => 'Tentang',
        'About the Journal' => 'Tentang Jurnal',
        'Editorial Team' => 'Tim Editorial',
        'Current' => 'Terkini',
        'Archives' => 'Arsip',
        'Announcements' => 'Pengumuman',
        'Author Guidelines' => 'Panduan Penulis',
        'Submissions' => 'Kiriman',
        'Privacy Statement' => 'Pernyataan Privasi',
        'Contact' => 'Kontak',
        'Search' => 'Cari',
        'Login' => 'Masuk',
        'Register' => 'Daftar',
        'Admin' => 'Admin',
        'Administration' => 'Administrasi',
        'Dashboard' => 'Dasbor',
        'View Profile' => 'Lihat Profil',
        'Logout' => 'Keluar',
    ];
@endphp

@section('title', ($isId ? 'Buat Butir Menu Navigasi - ' : 'Create Navigation Menu Item - ') . $journal->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $isId ? 'Buat Butir Menu Navigasi' : 'Create Navigation Menu Item' }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $isId ? 'Buat butir menu baru yang dapat ditugaskan ke menu navigasi.' : 'Create a new menu item that can be assigned to navigation menus.' }}</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('journal.settings.navigation.index', $journal->slug) }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50 shadow-sm transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                {{ $isId ? 'Kembali ke Manajemen Navigasi' : 'Back to Navigation Manager' }}
            </a>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fa-solid fa-check-circle text-green-500 mr-3"></i>
            <span class="text-green-800">{{ __(session('success')) }}</span>
        </div>
    </div>
    @endif

    {{-- Form --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6">
        <form action="{{ route('journal.settings.navigation.items.store', $journal->slug) }}" method="POST" class="space-y-6" x-data="{ itemType: 'custom' }">
            @csrf

            {{-- Title --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ $isId ? 'Judul *' : 'Title *' }}</label>
                <input type="text" name="title" required
                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="{{ $isId ? 'cth., Tentang Kami' : 'e.g., About Us' }}">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Navigation Page Type --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ $isId ? 'Jenis Halaman Navigasi' : 'Navigation Page Type' }}</label>
                <select name="type" x-model="itemType"
                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="custom">{{ $isId ? 'Tautan Kustom' : 'Custom Link' }}</option>
                    <option value="route">{{ $isId ? 'Halaman Sistem' : 'System Page' }}</option>
                    <option value="page">{{ $isId ? 'Halaman Kustom' : 'Custom Page' }}</option>
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- URL (for custom links) --}}
            <div x-show="itemType === 'custom'">
                <label class="block text-sm font-medium text-slate-700 mb-1">URL</label>
                <input type="text" name="url"
                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="{{ $isId ? 'https://contoh.com atau /jalur-internal' : 'https://example.com or /internal-path' }}">
                <p class="text-xs text-slate-500 mt-1">{{ $isId ? 'Masukkan URL lengkap (https://...) atau jalur relatif (/jalur)' : 'Enter a full URL (https://...) or relative path (/path)' }}</p>
                @error('url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- System Page (for route links) --}}
            <div x-show="itemType === 'route'">
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ $isId ? 'Halaman Sistem' : 'System Page' }}</label>
                <select name="route_name" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ $isId ? '-- Pilih halaman sistem --' : '-- Select a system page --' }}</option>
                    @foreach($availableRoutes as $config)
                    <option value="{{ $config['name'] }}">{{ $isId && isset($systemLabels[$config['label']]) ? $systemLabels[$config['label']] : $config['label'] }}</option>
                    @endforeach
                </select>
                @error('route_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Custom Page Fields --}}
            <div x-show="itemType === 'page'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ $isId ? 'Jalur Halaman *' : 'Page Path *' }}</label>
                    <div class="flex items-center">
                        <span class="text-sm text-slate-500 mr-2">{{ url('/') }}/{{ $journal->slug }}/</span>
                        <input type="text" name="path"
                            class="flex-1 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="about-us">
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $isId ? 'Halaman ini akan dapat diakses pada:' : 'This page will be accessible at:' }} {{ url('/') }}/{{ $journal->slug }}/YOUR_PATH
                        <br>{{ $isId ? 'Catatan: Tidak boleh ada dua halaman yang memiliki jalur yang sama.' : 'Note: No two pages can have the same path.' }}
                    </p>
                    @error('path')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ $isId ? 'Konten *' : 'Content *' }}</label>
                    <textarea id="page_content" name="content"
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        rows="15" placeholder="{{ $isId ? 'Masukkan konten halaman Anda di sini...' : 'Enter your page content here...' }}"></textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Preview Section --}}
                <div class="border-t border-slate-200 pt-4">
                    <h4 class="text-sm font-medium text-slate-700 mb-2">{{ $isId ? 'Pratinjau' : 'Preview' }}</h4>
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 min-h-[200px]">
                        <div id="preview_content" class="prose prose-sm max-w-none">
                            <!-- Content will be populated here -->
                        </div>
                    </div>
                </div>
            </div>

            {{-- Icon --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ $isId ? 'Ikon (opsional)' : 'Icon (optional)' }}</label>
                <input type="text" name="icon"
                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="fa-solid fa-info-circle">
                <p class="text-xs text-slate-500 mt-1">{{ $isId ? 'Kelas ikon Font Awesome (cth., fa-solid fa-home)' : 'Font Awesome icon class (e.g., fa-solid fa-home)' }}</p>
                @error('icon')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Target --}}
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="target" value="_blank"
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-slate-700">{{ $isId ? 'Buka di tab/jendela baru' : 'Open in new tab/window' }}</span>
                </label>
                @error('target')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit Buttons --}}
            <div class="flex justify-end gap-3 pt-6 border-t border-slate-200">
                <a href="{{ route('journal.settings.navigation.index', $journal->slug) }}"
                    class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors">
                    {{ $isId ? 'Batal' : 'Cancel' }}
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-check mr-2"></i>
                    {{ $isId ? 'Buat Butir Menu' : 'Create Menu Item' }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- TinyMCE Script --}}
<script src="{{ asset('assets/js/vendors/plugins/tinymce/tinymce.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize TinyMCE when the page loads
    tinymce.init({
        selector: '#page_content',
        height: 400,
        menubar: false,
        plugins: 'lists link image table code autoresize',
        toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | table link image | code',
        branding: false,
        license_key: 'gpl',
        setup: function(editor) {
            // Update preview on content change
            editor.on('change', function() {
                const content = editor.getContent();
                const preview = document.getElementById('preview_content');
                if (preview) {
                    preview.innerHTML = content;
                }
            });
        },
        images_upload_handler: function(blobInfo, progress) {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', '{{ route("profile.upload.image") }}');
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                xhr.upload.onprogress = function(e) {
                    progress(e.loaded / e.total * 100);
                };

                xhr.onload = function() {
                    if (xhr.status === 403) {
                        reject('HTTP Error: ' + xhr.status);
                        return;
                    }

                    if (xhr.status < 200 || xhr.status >= 300) {
                        reject('HTTP Error: ' + xhr.status);
                        return;
                    }

                    const json = JSON.parse(xhr.responseText);

                    if (!json || typeof json.location != 'string') {
                        reject('Invalid JSON: ' + xhr.responseText);
                        return;
                    }

                    resolve(json.location);
                };

                xhr.onerror = function() {
                    reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
                };

                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());

                xhr.send(formData);
            });
        }
    });
});
</script>
@endsection