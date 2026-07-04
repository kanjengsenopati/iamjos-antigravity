@extends('layouts.app')

@php
    $currentLoc = session('app_locale', app()->getLocale());
    $isId = in_array($currentLoc, ['id', 'id_ID']);
@endphp

@section('title', 'Roles & Permissions')

@section('content')
<div x-data="{ createRoleModalOpen: false }" class="relative">
    <!-- Header -->
    @include('admin.journals.users._header', ['activeTab' => 'roles'])

    <!-- Roles Table (Alpine + Axios) -->
    @include('livewire.admin.users.roles-table', [
        'roles' => $roles,
        'journal' => $journal,
        'routePrefix' => $routePrefix
    ])

    <!-- Create Role Modal -->
    <div x-show="createRoleModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog" aria-modal="true" aria-labelledby="create-role-modal-title" @keydown.escape.window="createRoleModalOpen = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="createRoleModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm"
                @click="createRoleModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="createRoleModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative inline-block w-full max-w-5xl overflow-hidden text-left align-bottom transition-all transform bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:my-8 sm:align-middle ring-1 ring-black ring-opacity-5">

                <form action="{{ route($routePrefix . '.roles.store', ['journal' => $journal->slug]) }}" method="POST"
                    x-data="{ selectedLevel: 5, activeTab: 'identity' }">
                    @csrf

                    <!-- Header -->
                    <div class="relative px-6 py-4 bg-white border-b border-slate-200 rounded-t-[24px]">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-[12px] bg-primary-50 border border-primary-100">
                                    <i class="text-lg text-primary-600 fa-solid fa-user-gear"></i>
                                </div>
                                <div>
                                    <x-text.h1 id="create-role-modal-title" class="text-slate-800 font-bold tracking-tight !text-[18px]">
                                        {{ $isId ? 'Buat Peran Baru' : 'Create New Role' }}
                                    </x-text.h1>
                                    <p class="text-xs text-slate-400 font-medium">{{ $isId ? 'Tentukan properti, tingkat izin, dan akses alur kerja untuk peran ini.' : 'Define properties, permission levels, and workflow access for this role.' }}</p>
                                </div>
                            </div>
                            <button type="button" @click="createRoleModalOpen = false"
                                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-all duration-200 focus:outline-none cursor-pointer">
                                <i class="text-lg fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 md:p-8 space-y-6 max-h-[70vh] overflow-y-auto">
                        <!-- Main Card Container (Tabbed Form) -->
                        <div class="bg-white rounded-[24px] border border-slate-100 overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                            <!-- Tab Navigation -->
                            <div class="border-b border-slate-100 bg-slate-50/50">
                                <nav class="flex overflow-x-auto px-6" aria-label="Tabs">
                                    <button type="button" @click="activeTab = 'identity'"
                                        :class="activeTab === 'identity' ? 'border-primary-500 text-primary-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                        class="flex-shrink-0 px-6 py-4 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                                        <i class="fa-solid fa-id-card text-base transition-colors" :class="activeTab === 'identity' ? 'text-primary-600' : 'text-slate-400'"></i>
                                        {{ $isId ? 'Identitas Peran' : 'Role Identity' }}
                                    </button>
                                    <button type="button" @click="activeTab = 'permission'"
                                        :class="activeTab === 'permission' ? 'border-primary-500 text-primary-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                        class="flex-shrink-0 px-6 py-4 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                                        <i class="fa-solid fa-shield-halved text-base transition-colors" :class="activeTab === 'permission' ? 'text-primary-600' : 'text-slate-400'"></i>
                                        {{ $isId ? 'Tingkat Izin' : 'Permission Level' }}
                                    </button>
                                    <button type="button" @click="activeTab = 'stages'"
                                        :class="activeTab === 'stages' ? 'border-primary-500 text-primary-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                        class="flex-shrink-0 px-6 py-4 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                                        <i class="fa-solid fa-route text-base transition-colors" :class="activeTab === 'stages' ? 'text-primary-600' : 'text-slate-400'"></i>
                                        {{ $isId ? 'Tahap Alur Kerja' : 'Workflow Stages' }}
                                    </button>
                                    <button type="button" @click="activeTab = 'options'"
                                        :class="activeTab === 'options' ? 'border-primary-500 text-primary-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                        class="flex-shrink-0 px-6 py-4 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                                        <i class="fa-solid fa-sliders text-base transition-colors" :class="activeTab === 'options' ? 'text-primary-600' : 'text-slate-400'"></i>
                                        {{ $isId ? 'Opsi Peran' : 'Role Options' }}
                                    </button>
                                </nav>
                            </div>

                            <!-- Tab Contents -->
                            <div class="p-6 md:p-8">
                                <!-- TAB 1: IDENTITY -->
                                <div x-show="activeTab === 'identity'" x-cloak x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                                    
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center">
                                            <i class="fa-solid fa-id-card text-primary-600 text-lg"></i>
                                        </div>
                                        <div>
                                            <x-text.h2>1. {{ $isId ? 'Identitas Peran' : 'Role Identity' }}</x-text.h2>
                                            <x-text.body class="text-slate-500 text-xs mt-0.5">{{ $isId ? 'Informasi dasar dan identifikasi visual untuk peran ini.' : 'Basic information and visual identification for this role.' }}</x-text.body>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-4">
                                            <div>
                                                <label for="name" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Nama Peran' : 'Role Name' }}</label>
                                                <input type="text" name="name" id="name" placeholder="{{ $isId ? 'misal: Editor Tamu' : 'e.g. Guest Editor' }}"
                                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5"
                                                    required>
                                            </div>
                                            <div>
                                                <label for="abbreviation" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Singkatan (Abbreviation)' : 'Abbreviation' }}</label>
                                                <input type="text" name="abbreviation" id="abbreviation" placeholder="{{ $isId ? 'misal: ET' : 'e.g. GE' }}" maxlength="5"
                                                    class="mt-1 block w-24 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5">
                                                <x-text.caption class="mt-1 block">{{ $isId ? 'Maksimal 5 karakter.' : 'Max 5 characters.' }}</x-text.caption>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">{{ $isId ? 'Warna Peran' : 'Role Color' }}</label>
                                            <div class="flex flex-wrap gap-3" x-data="{ selectedColor: 'blue' }">
                                                <!-- Hidden Input -->
                                                <input type="hidden" name="color" x-model="selectedColor">

                                                @php
                                                $colors = [
                                                    'red' => 'bg-red-500',
                                                    'orange' => 'bg-orange-500',
                                                    'amber' => 'bg-amber-500',
                                                    'green' => 'bg-emerald-500',
                                                    'teal' => 'bg-teal-500',
                                                    'blue' => 'bg-blue-500',
                                                    'indigo' => 'bg-indigo-500',
                                                    'purple' => 'bg-purple-500',
                                                    'pink' => 'bg-pink-500',
                                                    'gray' => 'bg-gray-500',
                                                ];
                                                @endphp

                                                @foreach ($colors as $name => $class)
                                                <button type="button" @click="selectedColor = '{{ $name }}'"
                                                    class="w-10 h-10 rounded-full {{ $class }} flex items-center justify-center transition-transform hover:scale-110 focus:outline-none ring-offset-2"
                                                    :class="{ 'ring-2 ring-gray-400': selectedColor === '{{ $name }}' }">
                                                    <i x-show="selectedColor === '{{ $name }}'"
                                                        class="fa-solid fa-check text-white text-sm"></i>
                                                </button>
                                                @endforeach
                                            </div>
                                            <x-text.caption class="mt-2 block">{{ $isId ? 'Digunakan untuk lencana dan identifikasi dalam kisi tabel.' : 'Used for badges and identification in the grid.' }}</x-text.caption>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 2: PERMISSION LEVEL -->
                                <div x-show="activeTab === 'permission'" x-cloak x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                                    
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center">
                                            <i class="fa-solid fa-shield-halved text-primary-600 text-lg"></i>
                                        </div>
                                        <div>
                                            <x-text.h2>2. {{ $isId ? 'Tingkat Izin' : 'Permission Level' }}</x-text.h2>
                                            <x-text.body class="text-slate-500 text-xs mt-0.5">{{ $isId ? 'Menentukan kemampuan inti dan cakupan akses dari peran ini.' : 'Determines the core capabilities and access scope of this role.' }}</x-text.body>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @php
                                        $levels = $isId ? [
                                            [
                                                'val' => 1,
                                                'label' => 'Manajer Jurnal',
                                                'desc' => 'Akses penuh ke semua pengaturan, pengguna, dan pengajuan di dalam jurnal ini.',
                                                'icon' => 'fa-screwdriver-wrench',
                                            ],
                                            [
                                                'val' => 2,
                                                'label' => 'Editor Bagian',
                                                'desc' => 'Dapat menyunting pengajuan yang ditugaskan dan membuat keputusan editorial.',
                                                'icon' => 'fa-pen-to-square',
                                            ],
                                            [
                                                'val' => 3,
                                                'label' => 'Asisten',
                                                'desc' => 'Akses terbatas. Hanya dapat bekerja pada tahap alur kerja tertentu dari item yang ditugaskan.',
                                                'icon' => 'fa-hand-holding-hand',
                                            ],
                                            [
                                                'val' => 4,
                                                'label' => 'Peninjau',
                                                'desc' => 'Hanya dapat mengakses dan melakukan peninjauan pada pengajuan yang ditugaskan.',
                                                'icon' => 'fa-magnifying-glass',
                                            ],
                                            [
                                                'val' => 5,
                                                'label' => 'Penulis',
                                                'desc' => 'Dapat mengirimkan artikel dan hanya dapat melacak kemajuan mereka sendiri.',
                                                'icon' => 'fa-user-pen',
                                            ],
                                            [
                                                'val' => 6,
                                                'label' => 'Pembaca',
                                                'desc' => 'Akses baca-saja ke konten yang diterbitkan.',
                                                'icon' => 'fa-book-open',
                                            ],
                                        ] : [
                                            [
                                                'val' => 1,
                                                'label' => 'Journal Manager',
                                                'desc' => 'Full access to all settings, users, and submissions within this journal.',
                                                'icon' => 'fa-screwdriver-wrench',
                                            ],
                                            [
                                                'val' => 2,
                                                'label' => 'Section Editor',
                                                'desc' => 'Can edit assigned submissions and make editorial decisions.',
                                                'icon' => 'fa-pen-to-square',
                                            ],
                                            [
                                                'val' => 3,
                                                'label' => 'Assistant',
                                                'desc' => 'Restricted access. Can only work on specific workflow stages of assigned items.',
                                                'icon' => 'fa-hand-holding-hand',
                                            ],
                                            [
                                                'val' => 4,
                                                'label' => 'Reviewer',
                                                'desc' => 'Can only access and perform reviews on assigned submissions.',
                                                'icon' => 'fa-magnifying-glass',
                                            ],
                                            [
                                                'val' => 5,
                                                'label' => 'Author',
                                                'desc' => 'Can submit articles and track their own progress only.',
                                                'icon' => 'fa-user-pen',
                                            ],
                                            [
                                                'val' => 6,
                                                'label' => 'Reader',
                                                'desc' => 'Read-only access to published content.',
                                                'icon' => 'fa-book-open',
                                            ],
                                        ];
                                        @endphp

                                        @foreach ($levels as $level)
                                        <div class="relative flex flex-col h-full">
                                            <label class="relative flex flex-col p-4 bg-white border rounded-xl cursor-pointer hover:border-primary-500 hover:bg-primary-50/30 transition-all group h-full"
                                                :class="selectedLevel == {{ $level['val'] }} ? 'border-primary-600 ring-1 ring-primary-600 bg-primary-50/20' : 'border-gray-200'">

                                                <input type="radio" name="permission_level" value="{{ $level['val'] }}"
                                                    x-model="selectedLevel" class="sr-only">

                                                <div class="flex items-center gap-3 mb-2">
                                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors"
                                                        :class="selectedLevel == {{ $level['val'] }} ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-500 group-hover:bg-primary-100 group-hover:text-primary-600'">
                                                        <i class="fa-solid {{ $level['icon'] }} text-lg"></i>
                                                    </div>
                                                    <span class="font-semibold text-slate-800"
                                                        :class="selectedLevel == {{ $level['val'] }} ? 'text-primary-700 font-bold' : ''">
                                                        {{ $level['label'] }}
                                                    </span>
                                                </div>
                                                <x-text.body class="text-xs text-slate-500 leading-relaxed">{{ $level['desc'] }}</x-text.body>

                                                <!-- Checkmark Badge -->
                                                <div class="absolute top-3 right-3" x-show="selectedLevel == {{ $level['val'] }}" x-transition>
                                                    <i class="fa-solid fa-circle-check text-primary-600 text-lg"></i>
                                                </div>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- TAB 3: WORKFLOW STAGE ASSIGNMENT -->
                                <div x-show="activeTab === 'stages'" x-cloak x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                                    
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center">
                                            <i class="fa-solid fa-route text-primary-600 text-lg"></i>
                                        </div>
                                        <div>
                                            <x-text.h2>3. {{ $isId ? 'Penetapan Tahap Alur Kerja' : 'Workflow Stage Assignment' }}</x-text.h2>
                                            <x-text.body class="text-slate-500 text-xs mt-0.5">{{ $isId ? 'Konfigurasikan tahap alur kerja editorial mana yang dapat diakses oleh peran ini.' : 'Configure which stages of the editorial workflow this role can access.' }}</x-text.body>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                        <label
                                            class="flex items-start p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer bg-white">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="stages[]" value="submission"
                                                    class="w-5 h-5 text-primary-600 border-slate-300 rounded focus:ring-primary-500 cursor-pointer">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <span class="font-semibold text-slate-800">{{ $isId ? 'Pengajuan' : 'Submission' }}</span>
                                                <x-text.caption class="text-slate-400 text-xs mt-1 block">{{ $isId ? 'Pemeriksaan awal & penugasan' : 'Initial checks & assignment' }}</x-text.caption>
                                            </div>
                                        </label>

                                        <label
                                            class="flex items-start p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer bg-white">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="stages[]" value="review"
                                                    class="w-5 h-5 text-primary-600 border-slate-300 rounded focus:ring-primary-500 cursor-pointer">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <span class="font-semibold text-slate-800">{{ $isId ? 'Ulasan' : 'Review' }}</span>
                                                <x-text.caption class="text-slate-400 text-xs mt-1 block">{{ $isId ? 'Manajemen ulasan sejawat' : 'Peer review management' }}</x-text.caption>
                                            </div>
                                        </label>

                                        <label
                                            class="flex items-start p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer bg-white">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="stages[]" value="copyediting"
                                                    class="w-5 h-5 text-primary-600 border-slate-300 rounded focus:ring-primary-500 cursor-pointer">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <span class="font-semibold text-slate-800">{{ $isId ? 'Copyediting' : 'Copyediting' }}</span>
                                                <x-text.caption class="text-slate-400 text-xs mt-1 block">{{ $isId ? 'Tata bahasa & pemformatan' : 'Grammar & formatting' }}</x-text.caption>
                                            </div>
                                        </label>

                                        <label
                                            class="flex items-start p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer bg-white">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="stages[]" value="production"
                                                    class="w-5 h-5 text-primary-600 border-slate-300 rounded focus:ring-primary-500 cursor-pointer">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <span class="font-semibold text-slate-800">{{ $isId ? 'Produksi' : 'Production' }}</span>
                                                <x-text.caption class="text-slate-400 text-xs mt-1 block">{{ $isId ? 'Pembuatan naskah akhir (galley)' : 'Final galley creation' }}</x-text.caption>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- TAB 4: ROLE OPTIONS -->
                                <div x-show="activeTab === 'options'" x-cloak x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                                    
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center">
                                            <i class="fa-solid fa-sliders text-primary-600 text-lg"></i>
                                        </div>
                                        <div>
                                            <x-text.h2>4. {{ $isId ? 'Opsi Peran' : 'Role Options' }}</x-text.h2>
                                            <x-text.body class="text-slate-500 text-xs mt-0.5">{{ $isId ? 'Konfigurasi tambahan untuk visibilitas dan kemampuan pengguna.' : 'Additional configuration for visibility and user capabilities.' }}</x-text.body>
                                        </div>
                                    </div>

                                    <div class="divide-y divide-slate-100">
                                        {{-- OPTION 1: Self Registration --}}
                                        <div class="flex items-center justify-between py-4 group">
                                            <div class="flex flex-col pr-8 max-w-2xl">
                                                <label for="allow_registration" class="text-sm font-semibold text-slate-800 cursor-pointer group-hover:text-primary-600 transition-colors">
                                                    {{ $isId ? 'Izinkan pendaftaran mandiri pengguna' : 'Allow user self-registration' }}
                                                </label>
                                                <x-text.caption class="text-slate-500 mt-1 block">
                                                    {{ $isId ? 'Pengguna dapat memilih peran ini saat mendaftarkan akun. Berguna untuk Penulis dan Peninjau.' : 'Users can select this role when registering an account. Useful for Authors and Reviewers.' }}
                                                </x-text.caption>
                                            </div>

                                            <div class="flex-shrink-0 ml-4">
                                                <select name="allow_registration" id="allow_registration"
                                                    class="block w-28 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm py-2 px-3 bg-white border cursor-pointer">
                                                    <option value="1">{{ $isId ? 'Ya' : 'Yes' }}</option>
                                                    <option value="0" selected>{{ $isId ? 'Tidak' : 'No' }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- OPTION 2: Contributor List --}}
                                        <div class="flex items-center justify-between py-4 group">
                                            <div class="flex flex-col pr-8 max-w-2xl">
                                                <label for="show_contributor" class="text-sm font-semibold text-slate-800 cursor-pointer group-hover:text-primary-600 transition-colors">
                                                    {{ $isId ? 'Tampilkan gelar peran dalam daftar kontributor' : 'Show role title in contributor list' }}
                                                </label>
                                                <x-text.caption class="text-slate-500 mt-1 block">
                                                    {{ $isId ? 'Menampilkan nama peran di sebelah nama pengguna dalam rincian publikasi.' : 'Displays the role name next to the user\'s name in publication details.' }}
                                                </x-text.caption>
                                            </div>

                                            <div class="flex-shrink-0 ml-4">
                                                <select name="show_contributor" id="show_contributor"
                                                    class="block w-28 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm py-2 px-3 bg-white border cursor-pointer">
                                                    <option value="1" selected>{{ $isId ? 'Ya' : 'Yes' }}</option>
                                                    <option value="0">{{ $isId ? 'Tidak' : 'No' }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- OPTION 3: Make Submissions --}}
                                        <div class="flex items-center justify-between py-4 group">
                                            <div class="flex flex-col pr-8 max-w-2xl">
                                                <label for="allow_submission" class="text-sm font-semibold text-slate-800 cursor-pointer group-hover:text-primary-600 transition-colors">
                                                    {{ $isId ? 'Izinkan peran ini untuk membuat pengajuan baru' : 'Allow this role to make new submissions' }}
                                                </label>
                                                <x-text.caption class="text-slate-500 mt-1 block">
                                                    {{ $isId ? 'Pengguna dengan peran ini dapat memulai wizard pengajuan. Biasanya diaktifkan untuk Penulis.' : 'Users with this role can start the submission wizard. Typically enabled for Authors.' }}
                                                </x-text.caption>
                                            </div>

                                            <div class="flex-shrink-0 ml-4">
                                                <select name="allow_submission" id="allow_submission"
                                                    class="block w-28 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm py-2 px-3 bg-white border cursor-pointer">
                                                    <option value="1">{{ $isId ? 'Ya' : 'Yes' }}</option>
                                                    <option value="0" selected>{{ $isId ? 'Tidak' : 'No' }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-[24px]">
                        <button type="button" @click="createRoleModalOpen = false"
                            class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 transition-colors cursor-pointer">
                            {{ $isId ? 'Batal' : 'Cancel' }}
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-primary-600 text-white rounded-xl text-sm font-semibold hover:bg-primary-700 transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-save"></i>
                            {{ $isId ? 'Simpan Peran' : 'Save Role' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection