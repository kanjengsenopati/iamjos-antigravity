@php
    $journal = current_journal();
    $journalSlug = $journal->slug;
    $currentLoc = session('app_locale', app()->getLocale());
    $isId = in_array($currentLoc, ['id', 'id_ID']);
@endphp

<x-app-layout :journal="$journal" :journalSlug="$journalSlug">
    <x-slot name="title">{{ $isId ? 'Pengaturan Website' : 'Website Settings' }} - {{ $journal->name }}</x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') || '{{ request('tab', 'setup') }}', setupSubTab: new URLSearchParams(window.location.search).get('setup_tab') || '{{ request('setup_tab', 'languages') }}', appearanceSubTab: new URLSearchParams(window.location.search).get('appearance_tab') || '{{ request('appearance_tab', 'theme') }}' }">
        {{-- Page Header --}}
        <div class="mb-8">
            <nav class="text-sm text-gray-500 mb-2">
                <a href="{{ route('journal.settings.index', $journalSlug) }}" class="hover:text-primary-600">{{ $isId ? 'Pengaturan' : 'Settings' }}</a>
                <span class="mx-2">/</span>
                <span class="text-gray-700">Website</span>
            </nav>
            <div class="flex items-center justify-between">
                <div>
                    <x-text.h1>{{ $isId ? 'Pengaturan Situs Website' : 'Website Settings' }}</x-text.h1>
                    <x-text.body class="text-slate-500 mt-1">{{ $isId ? 'Konfigurasi pengaturan publik, modul setup, dan tampilan jurnal Anda' : 'Configure your journal\'s public website setup and appearance' }}</x-text.body>
                </div>
                <div class="flex items-center gap-3">
                    {{-- OJS Language Switcher Dropdown --}}
                    @if (!empty($settings['supported_locales']) && is_array($settings['supported_locales']) && count($settings['supported_locales']) > 1)
                        <div class="relative" x-data="{ open: false }">
                            <button type="button" @click="open = !open" class="inline-flex items-center px-3.5 py-2 border border-slate-300 text-xs font-bold rounded-xl text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-sm gap-2">
                                <i class="fa-solid fa-globe text-primary-600"></i>
                                <span>{{ $isId ? 'Bahasa Indonesia' : 'English' }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-slate-150 py-1.5 z-50">
                                <a href="{{ route('locale.switch', 'en') }}" class="flex items-center justify-between px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 {{ !$isId ? 'font-bold text-primary-600 bg-primary-50/50' : '' }}">
                                    <span>English</span>
                                    <span class="text-[10px] text-slate-400 font-mono">en_US</span>
                                </a>
                                <a href="{{ route('locale.switch', 'id') }}" class="flex items-center justify-between px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 {{ $isId ? 'font-bold text-primary-600 bg-primary-50/50' : '' }}">
                                    <span>Bahasa Indonesia</span>
                                    <span class="text-[10px] text-slate-400 font-mono">id_ID</span>
                                </a>
                            </div>
                        </div>
                    @endif

                    <a href="{{ route('journal.public.home', $journalSlug) }}" target="_blank"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm">
                        <i class="fa-solid fa-external-link-alt mr-2 text-slate-400"></i>
                        {{ $isId ? 'Pratinjau Situs' : 'Preview Website' }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Success Message --}}
        @if (session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl p-4 shadow-sm">
                <div class="flex items-center">
                    <i class="fa-solid fa-check-circle text-emerald-500 mr-3"></i>
                    <span class="text-emerald-800 text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
                <div class="flex items-start">
                    <i class="fa-solid fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="text-red-800 font-bold mb-1 text-sm">{{ $isId ? 'Periksa kembali isian Anda:' : 'Please check your inputs:' }}</h4>
                        <ul class="list-disc list-inside text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Primary Top Tabs Navigation (OJS Standard) --}}
        <div class="border-b border-slate-200 mb-8">
            <nav class="flex space-x-8 overflow-x-auto no-scrollbar" aria-label="Tabs">
                <button type="button" @click="activeTab = 'appearance'; history.replaceState(null, '', '?tab=' + activeTab + '&setup_tab=' + setupSubTab + '&appearance_tab=' + appearanceSubTab)"
                    :class="activeTab === 'appearance' ? 'border-primary-600 text-primary-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="flex-shrink-0 py-4 px-1 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-palette text-base transition-colors" :class="activeTab === 'appearance' ? 'text-primary-600' : 'text-slate-400'"></i>
                    {{ $isId ? 'Tampilan (Appearance)' : 'Appearance' }}
                </button>
                <button type="button" @click="activeTab = 'setup'; history.replaceState(null, '', '?tab=' + activeTab + '&setup_tab=' + setupSubTab + '&appearance_tab=' + appearanceSubTab)"
                    :class="activeTab === 'setup' ? 'border-primary-600 text-primary-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="flex-shrink-0 py-4 px-1 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-sliders text-base transition-colors" :class="activeTab === 'setup' ? 'text-primary-600' : 'text-slate-400'"></i>
                    {{ $isId ? 'Pengaturan (Setup)' : 'Setup' }}
                </button>
                <button type="button" @click="activeTab = 'plugins'; history.replaceState(null, '', '?tab=' + activeTab + '&setup_tab=' + setupSubTab + '&appearance_tab=' + appearanceSubTab)"
                    :class="activeTab === 'plugins' ? 'border-primary-600 text-primary-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="flex-shrink-0 py-4 px-1 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-plug text-base transition-colors" :class="activeTab === 'plugins' ? 'text-primary-600' : 'text-slate-400'"></i>
                    Plugins
                </button>
                <button type="button" @click="activeTab = 'static_pages'; history.replaceState(null, '', '?tab=' + activeTab + '&setup_tab=' + setupSubTab + '&appearance_tab=' + appearanceSubTab)"
                    :class="activeTab === 'static_pages' ? 'border-primary-600 text-primary-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="flex-shrink-0 py-4 px-1 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-file-code text-base transition-colors" :class="activeTab === 'static_pages' ? 'text-primary-600' : 'text-slate-400'"></i>
                    {{ $isId ? 'Halaman Statis' : 'Static Pages' }}
                </button>
            </nav>
        </div>

        {{-- Main Form --}}
        <form action="{{ route('journal.settings.website.update', $journalSlug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="tab" :value="activeTab">
            <input type="hidden" name="setup_tab" :value="setupSubTab">
            <input type="hidden" name="appearance_tab" :value="appearanceSubTab">

            {{-- ============================================ --}}
            {{-- TAB 1: APPEARANCE --}}
            {{-- ============================================ --}}
            <div x-show="activeTab === 'appearance'" x-cloak class="space-y-6">
                {{-- Sub Tabs --}}
                <div class="border-b border-slate-100 mb-6">
                    <nav class="flex space-x-6">
                        <button type="button" @click="appearanceSubTab = 'theme'"
                            :class="appearanceSubTab === 'theme' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-slate-400 hover:text-slate-600'"
                            class="whitespace-nowrap py-3 px-1 text-sm font-medium border-b-2 transition-all">
                            {{ $isId ? 'Tema & Asset Logo' : 'Theme & Assets' }}
                        </button>
                        <button type="button" @click="appearanceSubTab = 'advanced'"
                            :class="appearanceSubTab === 'advanced' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-slate-400 hover:text-slate-600'"
                            class="whitespace-nowrap py-3 px-1 text-sm font-medium border-b-2 transition-all">
                            {{ $isId ? 'Warna Tema & Favicon' : 'Colors & Favicon' }}
                        </button>
                    </nav>
                </div>

                <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8">
                    <div x-show="appearanceSubTab === 'theme'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Logo Upload --}}
                        <div class="bg-gray-50/50 rounded-2xl border border-slate-150 p-6 h-fit"
                            x-data="{ logoPreview: '{{ $journal->logo_path ? Storage::disk('public')->url($journal->logo_path) : '' }}' }">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Logo</h3>
                            <p class="text-sm text-gray-500 mb-4">{{ $isId ? 'Unggah gambar logo untuk ditampilkan di bagian atas setiap halaman jurnal.' : 'Upload a logo image to be displayed at the top of every journal page.' }}</p>
                            <template x-if="logoPreview">
                                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200 inline-block">
                                    <img :src="logoPreview" alt="Logo Preview" class="max-h-20 w-auto">
                                    <p class="text-xs text-gray-500 mt-2">Logo Preview</p>
                                    <button type="button" @click="if(confirm('Delete logo?')) { fetch('{{ route('journal.settings.website.logo.delete', $journalSlug) }}', { method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'} }).then(() => { logoPreview = ''; }); }" class="text-red-600 text-xs mt-1 hover:underline block">{{ $isId ? 'Hapus Logo' : 'Remove Logo' }}</button>
                                </div>
                            </template>
                            <input type="file" name="logo" accept="image/jpeg,image/png,image/webp" @change="logoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : ''" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                        </div>

                        {{-- Journal Thumbnail --}}
                        <div class="bg-gray-50/50 rounded-2xl border border-slate-150 p-6 h-fit"
                            x-data="{ thumbnailPreview: '{{ $journal->thumbnail_path ? Storage::disk('public')->url($journal->thumbnail_path) : '' }}' }">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $isId ? 'Gambar Thumbnail Jurnal' : 'Journal Thumbnail' }}</h3>
                            <p class="text-sm text-gray-500 mb-4">{{ $isId ? 'Gambar kecil yang digunakan pada daftar jurnal dan hasil pencarian.' : 'A small image used in journal listings and search results.' }}</p>
                            <template x-if="thumbnailPreview">
                                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200 inline-block">
                                    <img :src="thumbnailPreview" alt="Thumbnail Preview" class="h-20 w-20 object-cover rounded-lg">
                                    <button type="button" @click="if(confirm('Delete thumbnail?')) { fetch('{{ route('journal.settings.website.thumbnail.delete', $journalSlug) }}', { method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'} }).then(() => { thumbnailPreview = ''; }); }" class="text-red-600 text-xs mt-1 hover:underline block">{{ $isId ? 'Hapus' : 'Remove' }}</button>
                                </div>
                            </template>
                            <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" @change="thumbnailPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : ''" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                        </div>

                        {{-- Homepage Image --}}
                        <div class="bg-gray-50/50 rounded-2xl border border-slate-150 p-6 h-fit lg:col-span-2"
                            x-data="{ homepagePreview: '{{ $journal->homepage_image_path ? Storage::disk('public')->url($journal->homepage_image_path) : '' }}' }">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $isId ? 'Gambar Halaman Utama (Homepage)' : 'Homepage Image' }}</h3>
                            <p class="text-sm text-gray-500 mb-4">{{ $isId ? 'Gambar ini akan ditampilkan secara menonjol di beranda jurnal Anda.' : 'This image will be displayed prominently on the journal homepage.' }}</p>
                            <template x-if="homepagePreview">
                                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200 inline-block">
                                    <img :src="homepagePreview" alt="Homepage Image Preview" class="max-h-40 w-auto rounded-lg shadow-sm">
                                </div>
                            </template>
                            <input type="file" name="homepage_image" accept="image/jpeg,image/png,image/webp" @change="homepagePreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : ''" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                        </div>

                        {{-- Page Footer --}}
                        <div class="bg-gray-50/50 rounded-2xl border border-slate-155 p-6 h-fit lg:col-span-2">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $isId ? 'Catatan Kaki (Page Footer)' : 'Page Footer' }}</h3>
                            <p class="text-sm text-gray-500 mb-4">{{ $isId ? 'Konten HTML kustom yang akan muncul di bagian bawah setiap halaman situs.' : 'Custom HTML content that will appear at the bottom of every page.' }}</p>
                            <textarea name="page_footer" id="page_footer" rows="5" class="w-full rounded-lg border-gray-300 font-mono text-sm">{{ $journal->page_footer }}</textarea>
                        </div>
                    </div>

                    <div x-show="appearanceSubTab === 'advanced'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Favicon --}}
                        <div class="bg-gray-50/50 rounded-2xl border border-slate-150 p-6 h-fit"
                            x-data="{ faviconPreview: '{{ $journal->favicon_path ? Storage::disk('public')->url($journal->favicon_path) : '' }}' }">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Favicon</h3>
                            <p class="text-sm text-gray-500 mb-4">{{ $isId ? 'Unggah icon favicon untuk ditampilkan pada tab browser.' : 'Upload a favicon to be displayed in the browser tab.' }}</p>
                            <input type="file" name="favicon" accept=".ico,.png,.jpg,.svg,.webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700">
                        </div>

                        {{-- Theme Colors --}}
                        <div class="bg-gray-50/50 rounded-2xl border border-slate-150 p-6 h-fit">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $isId ? 'Warna Aksentuasi Tema' : 'Theme Colors' }}</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isId ? 'Warna Utama' : 'Primary Color' }}</label>
                                    <input type="color" name="primary_color" id="primary_color" value="{{ $settings['primary_color'] ?? '#4F46E5' }}" class="w-full h-10 rounded border cursor-pointer">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isId ? 'Warna Kedua' : 'Secondary Color' }}</label>
                                    <input type="color" name="secondary_color" id="secondary_color" value="{{ $settings['secondary_color'] ?? '#7C3AED' }}" class="w-full h-10 rounded border cursor-pointer">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- TAB 2: SETUP (OJS 3.3 Redesigned Section!) --}}
            {{-- ============================================ --}}
            <div x-show="activeTab === 'setup'" x-cloak class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8">
                
                {{-- Two-Column Layout: Left Inner Sidebar & Right Content Panel --}}
                <div class="flex flex-col lg:flex-row gap-8">
                    
                    {{-- Left Column: Internal Sub-Menu Vertical Sidebar --}}
                    <div class="w-full lg:w-64 flex-shrink-0">
                        <div class="bg-slate-50/70 rounded-2xl p-3 border border-slate-200/60 sticky top-6">
                            <h4 class="px-3 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $isId ? 'MODUL SETUP' : 'SETUP MODULES' }}</h4>
                            <nav class="space-y-1 mt-1">
                                <button type="button" @click="setupSubTab = 'information'"
                                    :class="setupSubTab === 'information' ? 'bg-primary-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900'"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all text-left">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-info-circle text-base" :class="setupSubTab === 'information' ? 'text-white' : 'text-slate-400'"></i>
                                        <span>{{ $isId ? 'Informasi Jurnal' : 'Information' }}</span>
                                    </div>
                                    <span x-show="setupSubTab === 'information'" class="w-2 h-2 rounded-full bg-white"></span>
                                </button>

                                <button type="button" @click="setupSubTab = 'languages'"
                                    :class="setupSubTab === 'languages' ? 'bg-primary-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900'"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all text-left">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-language text-base" :class="setupSubTab === 'languages' ? 'text-white' : 'text-slate-400'"></i>
                                        <span>{{ $isId ? 'Bahasa (Languages)' : 'Languages' }}</span>
                                    </div>
                                    <span x-show="setupSubTab === 'languages'" class="w-2 h-2 rounded-full bg-white"></span>
                                </button>

                                <button type="button" @click="setupSubTab = 'navigation'"
                                    :class="setupSubTab === 'navigation' ? 'bg-primary-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900'"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all text-left">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-compass text-base" :class="setupSubTab === 'navigation' ? 'text-white' : 'text-slate-400'"></i>
                                        <span>{{ $isId ? 'Navigasi Situs' : 'Navigation' }}</span>
                                    </div>
                                    <span x-show="setupSubTab === 'navigation'" class="w-2 h-2 rounded-full bg-white"></span>
                                </button>

                                <button type="button" @click="setupSubTab = 'announcements'"
                                    :class="setupSubTab === 'announcements' ? 'bg-primary-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900'"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all text-left">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-bullhorn text-base" :class="setupSubTab === 'announcements' ? 'text-white' : 'text-slate-400'"></i>
                                        <span>{{ $isId ? 'Pengumuman' : 'Announcements' }}</span>
                                    </div>
                                    <span x-show="setupSubTab === 'announcements'" class="w-2 h-2 rounded-full bg-white"></span>
                                </button>

                                <button type="button" @click="setupSubTab = 'lists'"
                                    :class="setupSubTab === 'lists' ? 'bg-primary-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900'"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all text-left">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-list-ol text-base" :class="setupSubTab === 'lists' ? 'text-white' : 'text-slate-400'"></i>
                                        <span>{{ $isId ? 'Daftar & Batasan (Lists)' : 'Lists' }}</span>
                                    </div>
                                    <span x-show="setupSubTab === 'lists'" class="w-2 h-2 rounded-full bg-white"></span>
                                </button>

                                <button type="button" @click="setupSubTab = 'privacy'"
                                    :class="setupSubTab === 'privacy' ? 'bg-primary-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900'"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all text-left">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-user-shield text-base" :class="setupSubTab === 'privacy' ? 'text-white' : 'text-slate-400'"></i>
                                        <span>{{ $isId ? 'Pernyataan Privasi' : 'Privacy Statement' }}</span>
                                    </div>
                                    <span x-show="setupSubTab === 'privacy'" class="w-2 h-2 rounded-full bg-white"></span>
                                </button>

                                <button type="button" @click="setupSubTab = 'datetime'"
                                    :class="setupSubTab === 'datetime' ? 'bg-primary-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900'"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all text-left">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-clock text-base" :class="setupSubTab === 'datetime' ? 'text-white' : 'text-slate-400'"></i>
                                        <span>{{ $isId ? 'Tanggal & Waktu' : 'Date & Time' }}</span>
                                    </div>
                                    <span x-show="setupSubTab === 'datetime'" class="w-2 h-2 rounded-full bg-white"></span>
                                </button>
                            </nav>
                        </div>
                    </div>

                    {{-- Right Column: Main Form Panel --}}
                    <div class="flex-1 min-w-0">
                        
                        {{-- 1. INFORMATION MODULE --}}
                        <div x-show="setupSubTab === 'information'" class="space-y-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $isId ? 'Informasi & Panduan Jurnal' : 'Journal Information' }}</h3>
                                <p class="text-sm text-slate-500 mb-6">{{ $isId ? 'Konfigurasi panduan deskriptif kustom dan halaman informasi untuk pembaca, penulis, serta pustakawan.' : 'Configure custom descriptive guidelines and information pages for readers, authors, and librarians.' }}</p>
                            </div>

                            <div class="space-y-6">
                                <div class="bg-gray-50/50 rounded-2xl border border-slate-200 p-6">
                                    <h4 class="text-base font-semibold text-gray-900 mb-1">{{ $isId ? 'Untuk Pembaca (For Readers)' : 'For Readers' }}</h4>
                                    <p class="text-xs text-gray-500 mb-3">{{ $isId ? 'Informasi untuk pembaca yang ditampilkan pada halaman tentang jurnal.' : 'Information for readers displayed on the about page.' }}</p>
                                    <textarea name="info_readers" id="info_readers" rows="4" class="w-full rounded-lg border-gray-300">{{ $journal->info_readers }}</textarea>
                                </div>

                                <div class="bg-gray-50/50 rounded-2xl border border-slate-200 p-6">
                                    <h4 class="text-base font-semibold text-gray-900 mb-1">{{ $isId ? 'Untuk Penulis (For Authors)' : 'For Authors' }}</h4>
                                    <p class="text-xs text-gray-500 mb-3">{{ $isId ? 'Informasi dan panduan penulisan untuk penulis yang akan mengajukan naskah.' : 'Information and guidelines for authors submitting manuscripts.' }}</p>
                                    <textarea name="info_authors" id="info_authors" rows="4" class="w-full rounded-lg border-gray-300">{{ $journal->info_authors }}</textarea>
                                </div>

                                <div class="bg-gray-50/50 rounded-2xl border border-slate-200 p-6">
                                    <h4 class="text-base font-semibold text-gray-900 mb-1">{{ $isId ? 'Untuk Pustakawan (For Librarians)' : 'For Librarians' }}</h4>
                                    <p class="text-xs text-gray-500 mb-3">{{ $isId ? 'Informasi untuk pustakawan riset yang mengatalogkan jurnal ini.' : 'Information for research librarians cataloging this journal.' }}</p>
                                    <textarea name="info_librarians" id="info_librarians" rows="4" class="w-full rounded-lg border-gray-300">{{ $journal->info_librarians }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- 2. LANGUAGES MODULE (100% Database Driven OJS Table UI!) --}}
                        <div x-show="setupSubTab === 'languages'" class="space-y-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $isId ? 'Bahasa & Pengaturan Bahasa Utama (Languages)' : 'Languages' }}</h3>
                                <p class="text-sm text-slate-500 mb-6">{{ $isId ? 'Konfigurasi bahasa utama (Primary locale) dan bahasa yang tersedia untuk situs jurnal, formulir, serta pengajuan artikel.' : 'Configure the primary locale and available languages for your journal site, forms, and submissions.' }}</p>
                            </div>

                            @php
                                $primaryLoc = $settings['primary_locale'] ?? 'en';
                                $suppUi = is_array($settings['supported_locales'] ?? null) ? $settings['supported_locales'] : ['en', 'id'];
                                $suppForm = is_array($settings['supported_form_locales'] ?? null) ? $settings['supported_form_locales'] : ['en', 'id'];
                                $suppSub = is_array($settings['supported_submission_locales'] ?? null) ? $settings['supported_submission_locales'] : ['en', 'id'];
                            @endphp

                            <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-sm bg-white">
                                <table class="w-full text-left text-sm text-slate-700">
                                    <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500">
                                        <tr>
                                            <th scope="col" class="px-6 py-3.5">{{ $isId ? 'Bahasa (Locale)' : 'Locale' }}</th>
                                            <th scope="col" class="px-4 py-3.5 text-center">{{ $isId ? 'Bahasa Utama (Primary Locale)' : 'Primary locale' }}</th>
                                            <th scope="col" class="px-4 py-3.5 text-center">UI</th>
                                            <th scope="col" class="px-4 py-3.5 text-center">{{ $isId ? 'Formulir (Forms)' : 'Forms' }}</th>
                                            <th scope="col" class="px-4 py-3.5 text-center">{{ $isId ? 'Pengajuan (Submissions)' : 'Submissions' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="px-6 py-4 font-semibold text-slate-900 flex items-center gap-2">
                                                <span>English</span>
                                                <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full">en_US</span>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <input type="radio" name="primary_locale" value="en" {{ in_array($primaryLoc, ['en', 'en_US']) ? 'checked' : '' }} class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <input type="checkbox" name="supported_locales[]" value="en" {{ in_array('en', $suppUi) || in_array('en_US', $suppUi) ? 'checked' : '' }} class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <input type="checkbox" name="supported_form_locales[]" value="en" {{ in_array('en', $suppForm) || in_array('en_US', $suppForm) ? 'checked' : '' }} class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <input type="checkbox" name="supported_submission_locales[]" value="en" {{ in_array('en', $suppSub) || in_array('en_US', $suppSub) ? 'checked' : '' }} class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                            </td>
                                        </tr>
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="px-6 py-4 font-semibold text-slate-900 flex items-center gap-2">
                                                <span>Bahasa Indonesia</span>
                                                <span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full">id_ID</span>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <input type="radio" name="primary_locale" value="id" {{ in_array($primaryLoc, ['id', 'id_ID']) ? 'checked' : '' }} class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <input type="checkbox" name="supported_locales[]" value="id" {{ in_array('id', $suppUi) || in_array('id_ID', $suppUi) ? 'checked' : '' }} class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <input type="checkbox" name="supported_form_locales[]" value="id" {{ in_array('id', $suppForm) || in_array('id_ID', $suppForm) ? 'checked' : '' }} class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <input type="checkbox" name="supported_submission_locales[]" value="id" {{ in_array('id', $suppSub) || in_array('id_ID', $suppSub) ? 'checked' : '' }} class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- 3. NAVIGATION MODULE --}}
                        <div x-show="setupSubTab === 'navigation'" class="space-y-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $isId ? 'Manajemen Navigasi & Sidebar' : 'Navigation & Sidebar Management' }}</h3>
                                <p class="text-sm text-slate-500 mb-6">{{ $isId ? 'Kelola menu navigasi, tautan header, dan blok konten sidebar untuk jurnal Anda.' : 'Manage navigation menus, header links, and sidebar content blocks for your journal.' }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="border border-slate-200 rounded-2xl p-6 bg-slate-50/50 flex flex-col justify-between">
                                    <div>
                                        <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center mb-3">
                                            <i class="fa-solid fa-bars text-primary-600 text-lg"></i>
                                        </div>
                                        <h4 class="text-base font-bold text-gray-900 mb-1">{{ $isId ? 'Menu Navigasi' : 'Navigation Menus' }}</h4>
                                        <p class="text-xs text-slate-500 mb-4">{{ $isId ? 'Buat, edit, dan atur bilah menu untuk navigasi header dan footer.' : 'Create, edit, and arrange menu bars for your header and footer navigation.' }}</p>
                                    </div>
                                    <a href="{{ route('journal.settings.navigation.index', $journalSlug) }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 text-white text-xs font-bold rounded-xl hover:bg-primary-700 transition-colors shadow-sm">
                                        {{ $isId ? 'Kelola Menu Navigasi' : 'Manage Navigation Menus' }}
                                    </a>
                                </div>

                                <div class="border border-slate-200 rounded-2xl p-6 bg-slate-50/50 flex flex-col justify-between">
                                    <div>
                                        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center mb-3">
                                            <i class="fa-solid fa-columns text-emerald-600 text-lg"></i>
                                        </div>
                                        <h4 class="text-base font-bold text-gray-900 mb-1">{{ $isId ? 'Blok Sidebar' : 'Sidebar Blocks' }}</h4>
                                        <p class="text-xs text-slate-500 mb-4">{{ $isId ? 'Konfigurasi blok sidebar kustom, widget pengindeksan, dan tautan mitra.' : 'Configure custom sidebar blocks, indexing widgets, and partner links.' }}</p>
                                    </div>
                                    <a href="{{ route('journal.settings.sidebar.index', $journalSlug) }}" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                                        {{ $isId ? 'Kelola Blok Sidebar' : 'Manage Sidebar Blocks' }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- 4. ANNOUNCEMENTS MODULE --}}
                        <div x-show="setupSubTab === 'announcements'" class="space-y-6" x-data="{ enabled: {{ $journal->enable_announcements ? 'true' : 'false' }} }">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $isId ? 'Konfigurasi Pengumuman' : 'Announcements Configuration' }}</h3>
                                <p class="text-sm text-slate-500 mb-6">{{ $isId ? 'Aktifkan pengumuman berita dan informasi acara untuk pembaca dan penulis.' : 'Enable news and announcement notifications for readers and authors.' }}</p>
                            </div>

                            <div class="bg-gray-50/50 rounded-2xl border border-slate-200 p-6 space-y-6">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="hidden" name="enable_announcements" value="0">
                                        <input id="enable_announcements" name="enable_announcements" type="checkbox" value="1" x-model="enabled" class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500" {{ $journal->enable_announcements ? 'checked' : '' }}>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="enable_announcements" class="font-bold text-gray-900">{{ $isId ? 'Aktifkan fitur pengumuman' : 'Enable announcements' }}</label>
                                        <p class="text-gray-500">{{ $isId ? 'Pengumuman dapat dipublikasikan untuk menginformasikan berita dan acara jurnal kepada pembaca.' : 'Announcements may be published to inform readers of journal news and events.' }}</p>
                                    </div>
                                </div>

                                <div x-show="enabled" class="space-y-4 pt-4 border-t border-slate-200">
                                    <div>
                                        <label for="announcements_introduction" class="block text-sm font-medium text-gray-700 mb-1">{{ $isId ? 'Kata Pengantar Pengumuman' : 'Announcements Introduction' }}</label>
                                        <textarea name="announcements_introduction" id="announcements_introduction" rows="3" class="block w-full rounded-lg border-gray-300">{{ $journal->announcements_introduction }}</textarea>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label for="num_announcements_homepage" class="text-sm font-medium text-gray-700">{{ $isId ? 'Jumlah pengumuman di beranda:' : 'Display count on homepage:' }}</label>
                                        <input type="number" name="num_announcements_homepage" id="num_announcements_homepage" min="1" max="10" value="{{ $journal->num_announcements_homepage ?? 3 }}" class="w-24 rounded-lg border-gray-300 text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 5. LISTS MODULE --}}
                        <div x-show="setupSubTab === 'lists'" class="space-y-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $isId ? 'Batasan Daftar & Paginasi' : 'Lists & Pagination Limits' }}</h3>
                                <p class="text-sm text-slate-500 mb-6">{{ $isId ? 'Kendalikan berapa banyak item yang ditampilkan pada daftar publik dan editorial.' : 'Control how many items are displayed in public and editorial lists.' }}</p>
                            </div>

                            <div class="bg-gray-50/50 rounded-2xl border border-slate-200 p-6 max-w-lg">
                                <label for="items_per_page" class="block text-sm font-bold text-gray-900 mb-1">{{ $isId ? 'Jumlah Item Per Halaman (Items Per Page)' : 'Items Per Page' }}</label>
                                <p class="text-xs text-slate-500 mb-3">{{ $isId ? 'Jumlah item (seperti artikel pengajuan, edisi, atau pengumuman) yang ditampilkan per halaman.' : 'Number of items (such as submissions, issues, or announcements) shown per page.' }}</p>
                                <input type="number" name="items_per_page" id="items_per_page" min="5" max="100" value="{{ $settings['items_per_page'] ?? 25 }}" class="w-32 rounded-lg border-gray-300 text-sm font-bold text-slate-800">
                            </div>
                        </div>

                        {{-- 6. PRIVACY STATEMENT MODULE --}}
                        <div x-show="setupSubTab === 'privacy'" class="space-y-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $isId ? 'Pernyataan Privasi (Privacy Statement)' : 'Privacy Statement' }}</h3>
                                <p class="text-sm text-slate-500 mb-6">{{ $isId ? 'Sunting pernyataan privasi yang ditampilkan saat pengajuan naskah oleh penulis dan pendaftaran pengguna.' : 'Edit the privacy statement displayed during author submission and user registration.' }}</p>
                            </div>

                            <div class="bg-gray-50/50 rounded-2xl border border-slate-200 p-6">
                                <label for="privacy_statement" class="block text-sm font-bold text-gray-900 mb-2">{{ $isId ? 'Isi Pernyataan Privasi' : 'Statement Content' }}</label>
                                <textarea name="privacy_statement" id="privacy_statement" rows="8" class="w-full rounded-lg border-gray-300 font-mono text-sm">{{ $settings['privacy_statement'] ?? '' }}</textarea>
                            </div>
                        </div>

                        {{-- 7. DATE & TIME MODULE --}}
                        <div x-show="setupSubTab === 'datetime'" class="space-y-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $isId ? 'Pengaturan Tanggal & Waktu' : 'Date & Time Settings' }}</h3>
                                <p class="text-sm text-slate-500 mb-6">{{ $isId ? 'Konfigurasi zona waktu dan format tampilan tanggal untuk seluruh situs jurnal.' : 'Configure timezone and display formats for dates throughout the journal website.' }}</p>
                            </div>

                            <div class="bg-gray-50/50 rounded-2xl border border-slate-200 p-6 max-w-xl space-y-4">
                                <div>
                                    <label for="time_zone" class="block text-sm font-bold text-gray-900 mb-1">{{ $isId ? 'Zona Waktu' : 'Time Zone' }}</label>
                                    <select name="time_zone" id="time_zone" class="w-full rounded-lg border-gray-300 text-sm">
                                        <option value="Asia/Jakarta" {{ ($settings['time_zone'] ?? '') === 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB)</option>
                                        <option value="Asia/Makassar" {{ ($settings['time_zone'] ?? '') === 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar (WITA)</option>
                                        <option value="Asia/Jayapura" {{ ($settings['time_zone'] ?? '') === 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura (WIT)</option>
                                        <option value="UTC" {{ ($settings['time_zone'] ?? '') === 'UTC' ? 'selected' : '' }}>UTC</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="date_format" class="block text-sm font-bold text-gray-900 mb-1">{{ $isId ? 'Format Tampilan Tanggal' : 'Date Display Format' }}</label>
                                    <select name="date_format" id="date_format" class="w-full rounded-lg border-gray-300 text-sm">
                                        <option value="F j, Y" {{ ($settings['date_format'] ?? '') === 'F j, Y' ? 'selected' : '' }}>29 Juni 2026 (F j, Y)</option>
                                        <option value="Y-m-d" {{ ($settings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' }}>2026-06-29 (Y-m-d)</option>
                                        <option value="d/m/Y" {{ ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' }}>29/06/2026 (d/m/Y)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- TAB 3: PLUGINS (100% Dynamic Database Driven) --}}
            {{-- ============================================ --}}
            <div x-show="activeTab === 'plugins'" x-cloak class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8 space-y-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $isId ? 'Galeri Plugin & Integrasi Sistem' : 'Plugin Gallery & Integrations' }}</h3>
                    <p class="text-sm text-slate-500 mb-6">{{ $isId ? 'Kelola ekstensi sistem, plugin pengindeksan, dan perangkat eksternal.' : 'Manage system extensions, indexing plugins, and external tools.' }}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($plugins as $plugin)
                        <div class="border border-slate-200 rounded-2xl p-6 bg-slate-50/50 flex flex-col justify-between hover:border-slate-300 transition-colors shadow-sm">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <i class="{{ $plugin['icon'] }} {{ $plugin['color'] }} text-2xl"></i>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $plugin['status'] === 'Active' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $plugin['status'] === 'Active' ? ($isId ? 'Aktif' : 'Active') : ($isId ? 'Terpasang' : 'Installed') }}
                                    </span>
                                </div>
                                <h4 class="font-bold text-gray-900 mb-1 text-base">{{ $plugin['name'] }}</h4>
                                <p class="text-xs text-slate-500 mb-5 leading-relaxed">{{ $plugin['description'] }}</p>
                            </div>
                            <a href="{{ $plugin['route'] }}" class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-white border border-gray-300 text-xs font-bold rounded-xl text-gray-700 hover:bg-gray-50 transition-colors shadow-sm gap-2">
                                <i class="fa-solid fa-gear text-slate-400"></i>
                                {{ $isId ? 'Konfigurasi Plugin' : 'Configure Plugin' }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- TAB 4: STATIC PAGES (100% Dynamic Database Driven) --}}
            {{-- ============================================ --}}
            <div x-show="activeTab === 'static_pages'" x-cloak class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8 space-y-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $isId ? 'Manajemen Halaman Statis' : 'Static Pages Management' }}</h3>
                        <p class="text-sm text-slate-500">{{ $isId ? 'Buat dan publikasikan halaman berdiri sendiri (standalone) kustom untuk jurnal Anda.' : 'Create and publish custom standalone pages for your journal.' }}</p>
                    </div>
                    <a href="{{ Route::has('admin.site-pages.create') ? route('admin.site-pages.create') : (Route::has('site-pages.create') ? route('site-pages.create') : '#') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-xs font-bold rounded-xl hover:bg-primary-700 transition-colors shadow-sm">
                        <i class="fa-solid fa-plus mr-2"></i> {{ $isId ? 'Tambah Halaman Statis' : 'Add Static Page' }}
                    </a>
                </div>

                @if ($staticPages->count() > 0)
                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-sm bg-white">
                        <table class="w-full text-left text-sm text-slate-700">
                            <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5">{{ $isId ? 'Judul Halaman' : 'Page Title' }}</th>
                                    <th scope="col" class="px-4 py-3.5">{{ $isId ? 'Slug URL' : 'URL Slug' }}</th>
                                    <th scope="col" class="px-4 py-3.5 text-center">Status</th>
                                    <th scope="col" class="px-4 py-3.5">{{ $isId ? 'Tanggal Dibuat' : 'Date Created' }}</th>
                                    <th scope="col" class="px-6 py-3.5 text-right">{{ $isId ? 'Aksi' : 'Actions' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($staticPages as $page)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-4 font-semibold text-slate-900">
                                            <a href="{{ Route::has('site.page') ? route('site.page', $page->slug) : '#' }}" target="_blank" class="hover:text-primary-600 flex items-center gap-1.5">
                                                <span>{{ $page->title }}</span>
                                                <i class="fa-solid fa-external-link-alt text-[10px] text-slate-400"></i>
                                            </a>
                                        </td>
                                        <td class="px-4 py-4 font-mono text-xs text-slate-500">/page/{{ $page->slug }}</td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $page->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $page->is_published ? ($isId ? 'Terbit' : 'Published') : 'Draft' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-xs text-slate-500">{{ $page->created_at?->format('M j, Y') ?? '-' }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ Route::has('admin.site-pages.edit') ? route('admin.site-pages.edit', $page->id) : (Route::has('site-pages.edit') ? route('site-pages.edit', $page->id) : '#') }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-lg hover:bg-slate-200 transition-colors">
                                                <i class="fa-solid fa-pen-to-square mr-1.5 text-slate-500"></i> {{ $isId ? 'Edit' : 'Edit' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="border border-slate-200 rounded-2xl p-8 text-center bg-slate-50/50">
                        <i class="fa-solid fa-file-signature text-slate-400 text-4xl mb-3"></i>
                        <h4 class="text-base font-bold text-slate-700 mb-1">{{ $isId ? 'Belum Ada Halaman Statis' : 'No Custom Static Pages Found' }}</h4>
                        <p class="text-xs text-slate-500 mb-4 max-w-md mx-auto">{{ $isId ? 'Halaman statis memungkinkan Anda menambahkan halaman konten kustom seperti Kode Etik, Proses Mitra Bestari, atau Daftar Reviewer.' : 'Static pages allow you to add custom content pages such as Code of Ethics, Peer Review Process, or Peer Reviewers List.' }}</p>
                        <a href="{{ Route::has('admin.site-pages.create') ? route('admin.site-pages.create') : (Route::has('site-pages.create') ? route('site-pages.create') : '#') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-xs font-bold rounded-xl hover:bg-primary-700 transition-colors shadow-sm">
                            <i class="fa-solid fa-plus mr-2"></i> {{ $isId ? 'Tambah Halaman Statis' : 'Add Static Page' }}
                        </a>
                    </div>
                @endif
            </div>

            {{-- Submit Button --}}
            <div class="mt-8 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-semibold rounded-xl shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors cursor-pointer">
                    <i class="fa-solid fa-save mr-2"></i>
                    {{ $isId ? 'Simpan Pengaturan' : 'Save Settings' }}
                </button>
            </div>
        </form>
    </div>

    {{-- TinyMCE Script --}}
    @push('scripts')
        <script src="{{ asset('assets/js/vendors/plugins/tinymce/tinymce.min.js') }}"></script>
        <script>
            tinymce.init({
                selector: '#page_footer, #info_readers, #info_authors, #info_librarians, #announcements_introduction, #privacy_statement',
                height: 300,
                menubar: false,
                plugins: 'lists link image table code autoresize',
                toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | table link image | code',
                branding: false,
                license_key: 'gpl'
            });
        </script>
    @endpush
</x-app-layout>
