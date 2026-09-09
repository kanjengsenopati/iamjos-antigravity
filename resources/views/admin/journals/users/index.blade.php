@extends('layouts.app')

@php
    $currentLoc = session('app_locale', app()->getLocale());
    $isId = in_array($currentLoc, ['id', 'id_ID']);
@endphp

@section('title', $isId ? 'Manajemen Pengguna' : 'User Management')

@section('content')
    <div x-data="{
        enrollModalOpen: false,
        createUserModalOpen: false,
        emailModalOpen: false,
        recipientId: '',
        recipientName: '',
        recipientEmail: '',
        subject: '',
        body: '',
        isSubmitting: false,
        errorMessage: '',
        successMessage: '',
        searchQuery: '{{ request('search') }}',
        selectedRole: '{{ request('role') }}',
        isLoading: false,
        searchError: '',
        openEmailModal(user) {
            this.recipientId = user.id;
            this.recipientName = user.name;
            this.recipientEmail = user.email;
            this.subject = '[{{ $journal->name }}] ';
            this.body = 'Dear ' + user.name + ',\n\n';
            this.emailModalOpen = true;
            this.errorMessage = '';
            this.successMessage = '';
        },
        sendEmail() {
            if (!this.subject.trim() || !this.body.trim()) {
                this.errorMessage = 'Subject and message body are required.';
                return;
            }
            this.isSubmitting = true;
            this.errorMessage = '';
            this.successMessage = '';
            
            fetch('{{ route($routePrefix . '.email', ['journal' => $journal->slug, 'user' => '__USER_ID__']) }}'.replace('__USER_ID__', this.recipientId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    subject: this.subject,
                    body: this.body
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                this.isSubmitting = false;
                if (data.success) {
                    this.successMessage = data.message;
                    setTimeout(() => {
                        this.emailModalOpen = false;
                        this.successMessage = '';
                    }, 2000);
                } else {
                    this.errorMessage = data.message || 'Failed to send email.';
                }
            })
            .catch(error => {
                this.isSubmitting = false;
                this.errorMessage = error.message || 'An error occurred while sending the email.';
            });
        },
        performSearch() {
            const url = new URL(window.location.href);
            const query = this.searchQuery.trim();
            if (query.length >= 3 || query.length === 0) {
                url.searchParams.set('search', query);
                this.searchError = '';
            } else {
                this.searchError = 'Pencarian minimal 3 karakter.';
                url.searchParams.delete('search');
            }
            url.searchParams.set('role', this.selectedRole);
            url.searchParams.set('page', 1);
            this.fetchTable(url.toString());
        },
        triggerSearch() {
            const query = this.searchQuery.trim();
            if (query.length > 0 && query.length < 3) {
                this.searchError = 'Pencarian minimal 3 karakter.';
                return;
            }
            this.searchError = '';
            this.performSearch();
        },
        fetchTable(url) {
            this.isLoading = true;
            window.history.pushState({}, '', url);

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('users-table-container').innerHTML = html;
                this.isLoading = false;
            })
            .catch(error => {
                console.error('Fetch failed:', error);
                this.isLoading = false;
            });
        }
    }" class="relative">
        <!-- Header -->
        @include('admin.journals.users._header', ['activeTab' => 'users'])

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <form action="{{ route($routePrefix . '.index', ['journal' => $journal->slug]) }}" method="GET"
            @submit.prevent="triggerSearch()"
            class="flex flex-col sm:flex-row gap-4">
            
            <!-- Input & Button Search Cluster -->
            <div class="flex flex-col flex-1">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                        </div>
                        <input type="text" name="search" x-model="searchQuery"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white transition-colors"
                            placeholder="{{ $isId ? 'Cari nama, username, atau surel...' : 'Search by name, username, or email...' }}"
                            style="padding-left: 2.5rem !important;">
                    </div>
                    <button type="button" @click="triggerSearch()"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition-all duration-155 flex items-center gap-1.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i> {{ $isId ? 'Cari' : 'Search' }}
                    </button>
                </div>
                <!-- Notifikasi Error Karakter < 3 -->
                <div x-show="searchError" x-cloak class="text-xs text-red-500 font-semibold mt-1.5 flex items-center gap-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                    <span x-text="searchError"></span>
                </div>
            </div>

            <div class="relative min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-filter text-gray-400"></i>
                </div>
                <select name="role" x-model="selectedRole" x-on:change="performSearch()"
                    class="block w-full pl-10 pr-10 py-2 border border-gray-200 rounded-lg sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white appearance-none cursor-pointer"
                    style="padding-left: 2.5rem !important; padding-right: 2.5rem !important;">
                    <option value="">{{ $isId ? 'Semua Peran' : 'All Roles' }}</option>
                    @foreach ($roles as $roleName)
                        <option value="{{ $roleName }}">{{ $roleName }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table Container with Loading indicator -->
    <div class="relative min-h-[200px]">
        <!-- Top Loading Progress Bar -->
        <div x-show="isLoading" class="absolute top-0 left-0 right-0 h-1 bg-indigo-100 overflow-hidden z-20" x-cloak>
            <div class="h-full bg-indigo-600 animate-pulse w-full"></div>
        </div>
        
        <!-- Loading Blur Overlay -->
        <div x-show="isLoading" class="absolute inset-0 bg-white/40 backdrop-blur-[1px] flex items-center justify-center z-10" x-cloak>
            <div class="flex items-center gap-2 px-4 py-2 bg-white/80 rounded-full shadow-sm border border-gray-100">
                <i class="fa-solid fa-circle-notch animate-spin text-indigo-600"></i>
                <span class="text-xs font-semibold text-gray-600">{{ $isId ? 'Mencari...' : 'Searching...' }}</span>
            </div>
        </div>

        <div id="users-table-container" @click="
            if ($event.target.closest('nav a, .pagination a')) {
                $event.preventDefault();
                const href = $event.target.closest('nav a, .pagination a').getAttribute('href');
                if (href) fetchTable(href);
            }
        ">
            @include('admin.journals.users._table')
        </div>
    </div>

    <!-- Email Modal -->
    <div x-show="emailModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog" aria-modal="true" aria-labelledby="email-modal-title" @keydown.escape.window="emailModalOpen = false">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="emailModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm"
                @click="emailModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="emailModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative inline-block w-full max-w-lg overflow-hidden text-left align-bottom transition-all transform bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:my-8 sm:align-middle ring-1 ring-black ring-opacity-5">

                <!-- Header -->
                <div class="relative px-6 py-4 bg-white border-b border-slate-200 rounded-t-[24px]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-[12px] bg-blue-50 border border-blue-100">
                                <i class="text-lg text-blue-600 fa-solid fa-paper-plane"></i>
                            </div>
                            <div>
                                <x-text.h1 id="email-modal-title" class="text-slate-800 font-bold tracking-tight !text-[18px]">
                                    {{ $isId ? 'Kirim Surel' : 'Send Email' }}
                                </x-text.h1>
                                <p class="text-xs text-slate-400 font-medium">{{ $isId ? 'Buat surel untuk pengguna ini' : 'Compose an email to user' }}</p>
                            </div>
                        </div>
                        <button @click="emailModalOpen = false"
                            class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-all duration-200 focus:outline-none">
                            <i class="text-lg fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-5 bg-white space-y-4">
                    <!-- Success/Error Banner -->
                    <div x-show="successMessage" x-cloak class="p-3 bg-emerald-50 border border-emerald-100 rounded-lg text-emerald-800 text-sm flex items-start gap-2">
                        <i class="fa-solid fa-circle-check mt-0.5 text-emerald-600"></i>
                        <span x-text="successMessage"></span>
                    </div>

                    <div x-show="errorMessage" x-cloak class="p-3 bg-red-50 border border-red-100 rounded-lg text-red-800 text-sm flex items-start gap-2">
                        <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-600"></i>
                        <span x-text="errorMessage"></span>
                    </div>

                    <!-- To Recipient -->
                    <div>
                        <x-text.label class="block mb-1">{{ $isId ? 'Kepada' : 'To Recipient' }}</x-text.label>
                        <div class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 flex items-center justify-between">
                            <span x-text="recipientName + ' <' + recipientEmail + '>'"></span>
                        </div>
                    </div>

                    <!-- Subject -->
                    <div>
                        <x-text.label class="block mb-1">{{ $isId ? 'Subjek' : 'Subject' }}</x-text.label>
                        <input type="text" x-model="subject" 
                            class="w-full px-3 py-2 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800"
                            placeholder="{{ $isId ? 'Masukkan subjek surel...' : 'Enter email subject...' }}">
                    </div>

                    <!-- Body -->
                    <div>
                        <x-text.label class="block mb-1">{{ $isId ? 'Isi Pesan' : 'Message Body' }}</x-text.label>
                        <textarea x-model="body" rows="6"
                            class="w-full px-3 py-2 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 resize-y"
                            placeholder="{{ $isId ? 'Ketik pesan Anda di sini...' : 'Type your message here...' }}"></textarea>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-[24px]">
                    <button type="button" @click="emailModalOpen = false"
                        class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 transition-colors">
                        {{ $isId ? 'Batal' : 'Cancel' }}
                    </button>
                    <button type="button" @click="sendEmail()" :disabled="isSubmitting"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors flex items-center gap-2 shadow-sm disabled:opacity-55">
                        <i class="fa-solid fa-paper-plane" x-show="!isSubmitting"></i>
                        <i class="fa-solid fa-spinner animate-spin" x-show="isSubmitting" x-cloak></i>
                        <span x-text="isSubmitting ? '{{ $isId ? 'Mengirim...' : 'Sending...' }}' : '{{ $isId ? 'Kirim Surel' : 'Send Email' }}'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enroll User Modal -->
    <div x-show="enrollModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog" aria-modal="true" aria-labelledby="enroll-modal-title" @keydown.escape.window="enrollModalOpen = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="enrollModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm"
                @click="enrollModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="enrollModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative inline-block w-full max-w-4xl overflow-hidden text-left align-bottom transition-all transform bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:my-8 sm:align-middle ring-1 ring-black ring-opacity-5">

                <form action="{{ route($routePrefix . '.enroll.store', ['journal' => $journal->slug]) }}" method="POST">
                    @csrf

                    <!-- Header -->
                    <div class="relative px-6 py-4 bg-white border-b border-slate-200 rounded-t-[24px]">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-[12px] bg-primary-50 border border-primary-100">
                                    <i class="text-lg text-primary-600 fa-solid fa-user-check"></i>
                                </div>
                                <div>
                                    <x-text.h1 id="enroll-modal-title" class="text-slate-800 font-bold tracking-tight !text-[18px]">
                                        {{ $isId ? 'Daftarkan Pengguna yang Ada' : 'Enroll Existing User' }}
                                    </x-text.h1>
                                    <p class="text-xs text-slate-400 font-medium">{{ $isId ? 'Tambahkan pengguna yang sudah terdaftar ke' : 'Add an existing user to' }} {{ $journal->name }} {{ $isId ? 'dengan peran tertentu.' : 'with specific roles.' }}</p>
                                </div>
                            </div>
                            <button type="button" @click="enrollModalOpen = false"
                                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-all duration-200 focus:outline-none cursor-pointer">
                                <i class="text-lg fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 md:p-8 space-y-6 max-h-[70vh] overflow-y-auto">
                        {{-- User Selection --}}
                        <div class="bg-white rounded-[24px] border border-slate-100 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                            <div class="mb-4">
                                <h2 class="text-base font-semibold text-slate-800 flex items-center">
                                    <i class="fa-solid fa-user-check text-primary-500 mr-2"></i> {{ $isId ? 'Pilih Pengguna' : 'Select User' }}
                                </h2>
                                <p class="text-xs text-slate-400">{{ $isId ? 'Cari pengguna terdaftar untuk didaftarkan.' : 'Search for a registered user to enroll.' }}</p>
                            </div>

                            @if ($availableUsers->isEmpty())
                                <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800 flex items-start">
                                    <i class="fa-solid fa-info-circle mt-0.5 mr-2"></i>
                                    <div>
                                        <p class="font-medium">{{ $isId ? 'Tidak ada pengguna yang tersedia.' : 'No users available.' }}</p>
                                        <p class="mt-1">{{ $isId ? 'Semua pengguna terdaftar sudah terdaftar di jurnal ini.' : 'All registered users are already enrolled in this journal.' }}</p>
                                    </div>
                                </div>
                            @else
                                <div x-data="{
                                    open: false,
                                    search: '',
                                    selectedId: '{{ old('user_id') }}',
                                    selectedName: '',
                                    users: {{ $availableUsers->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email])->toJson() }},
                                    get filteredUsers() {
                                        if (this.search === '') {
                                            return this.users;
                                        }
                                        return this.users.filter(user => {
                                            return user.name.toLowerCase().includes(this.search.toLowerCase()) ||
                                                user.email.toLowerCase().includes(this.search.toLowerCase());
                                        });
                                    },
                                    init() {
                                        if (this.selectedId) {
                                            const user = this.users.find(u => u.id == this.selectedId);
                                            if (user) this.selectedName = user.name + ' (' + user.email + ')';
                                        }
                                    }
                                }" class="relative max-w-xl">
                                    <label for="user_search" class="block text-sm font-medium text-slate-700 mb-1">
                                        {{ $isId ? 'Cari Pengguna' : 'Search User' }} <span class="text-red-500">*</span>
                                    </label>

                                    <input type="hidden" name="user_id" :value="selectedId">

                                    <div class="relative">
                                        <button type="button" @click="open = !open"
                                            class="w-full bg-white border border-slate-200 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                            aria-haspopup="listbox" :aria-expanded="open">
                                            <span class="block truncate text-slate-700"
                                                x-text="selectedName ? selectedName : '{{ $isId ? '-- Pilih pengguna --' : '-- Select a user --' }}'"></span>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
                                            </span>
                                        </button>

                                        <div x-show="open" @click.away="open = false"
                                            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-lg py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                                            style="display: none;">

                                            <div class="sticky top-0 z-10 bg-white px-3 py-2 border-b border-slate-100">
                                                <div class="relative rounded-md shadow-sm">
                                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                        <i class="fa-solid fa-search text-slate-400 text-xs"></i>
                                                    </div>
                                                    <input type="text" x-model="search"
                                                        class="block w-full rounded-md border-slate-300 pl-10 focus:border-primary-500 focus:ring-primary-500 sm:text-xs border p-2"
                                                        style="padding-left: 2.5rem !important;"
                                                        placeholder="{{ $isId ? 'Cari berdasarkan nama atau surel...' : 'Search by name or email...' }}">
                                                </div>
                                            </div>

                                            <template x-for="user in filteredUsers" :key="user.id">
                                                <div @click="selectedId = user.id; selectedName = user.name + ' (' + user.email + ')'; open = false"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-slate-50 text-slate-900 group">
                                                    <div class="flex items-center">
                                                        <span class="truncate font-medium text-slate-700" x-text="user.name"></span>
                                                        <span class="ml-2 truncate text-slate-400 text-xs" x-text="'(' + user.email + ')'"></span>
                                                    </div>

                                                    <span x-show="selectedId == user.id"
                                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-primary-600">
                                                        <i class="fa-solid fa-check"></i>
                                                    </span>
                                                </div>
                                            </template>

                                            <div x-show="filteredUsers.length === 0"
                                                class="py-2 px-3 text-sm text-slate-400 text-center">
                                                {{ $isId ? 'Pengguna tidak ditemukan.' : 'No users found.' }}
                                            </div>
                                        </div>
                                    </div>
                                    @error('user_id')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        {{-- Roles Selection --}}
                        <div class="bg-white rounded-[24px] border border-slate-100 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                            <div class="mb-4">
                                <h2 class="text-base font-semibold text-slate-800 flex items-center">
                                    <i class="fa-solid fa-tags text-primary-500 mr-2"></i> {{ $isId ? 'Tetapkan Peran' : 'Assign Roles' }}
                                </h2>
                                <p class="text-xs text-slate-400">{{ $isId ? 'Pilih peran untuk' : 'Pick roles for' }} {{ $journal->name }}.</p>
                            </div>

                            @error('roles')
                                <div class="mb-4 px-3 py-2 bg-red-50 text-red-600 text-xs rounded border border-red-200">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach ($assignableRoles as $role)
                                    @php
                                        $badgeClass = match ($role->name) {
                                            'Journal Manager' => 'bg-red-50 text-red-700 border-red-200 ring-red-200',
                                            'Editor' => 'bg-blue-50 text-blue-700 border-blue-200 ring-blue-200',
                                            'Section Editor' => 'text-blue-700 border-blue-200 ring-blue-200',
                                            'Reviewer' => 'bg-amber-50 text-amber-700 border-amber-200 ring-amber-200',
                                            'Author' => 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-200',
                                            default => 'bg-gray-50 text-gray-600 border-gray-200 ring-gray-200',
                                        };
                                    @endphp
                                    <label
                                        class="relative flex items-center p-4 rounded-xl border border-slate-200 bg-white hover:border-primary-300 cursor-pointer transition-all shadow-sm group hover:shadow-md">
                                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                            {{ in_array($role->name, old('roles', ['Reader'])) ? 'checked' : '' }}
                                            class="h-5 w-5 text-primary-600 border-slate-300 rounded focus:ring-primary-500 cursor-pointer">

                                        <div class="ml-3 flex-1">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold border {{ $badgeClass }}">
                                                {{ $isId ? ($role->name === 'Journal Manager' ? 'Manajer Jurnal' : ($role->name === 'Section Editor' ? 'Editor Bagian' : ($role->name === 'Reviewer' ? 'Peninjau' : ($role->name === 'Author' ? 'Penulis' : ($role->name === 'Reader' ? 'Pembaca' : ($role->name === 'Admin' ? 'Administrator' : $role->name)))))) : $role->name }}
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @if ($assignableRoles->isEmpty())
                                <p class="text-sm text-slate-400 italic">{{ $isId ? 'Tidak ada peran tersedia yang ditemukan untuk jurnal ini.' : 'No available roles found for this journal.' }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-[24px]">
                        <button type="button" @click="enrollModalOpen = false"
                            class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 transition-colors cursor-pointer">
                            {{ $isId ? 'Batal' : 'Cancel' }}
                        </button>
                        @if (!$availableUsers->isEmpty())
                            <button type="submit"
                                class="px-5 py-2.5 bg-primary-600 text-white rounded-xl text-sm font-semibold hover:bg-primary-700 transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-user-check"></i>
                                {{ $isId ? 'Daftarkan Pengguna' : 'Enroll User' }}
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div x-show="createUserModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog" aria-modal="true" aria-labelledby="create-user-modal-title" @keydown.escape.window="createUserModalOpen = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="createUserModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm"
                @click="createUserModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="createUserModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative inline-block w-full max-w-5xl overflow-hidden text-left align-bottom transition-all transform bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:my-8 sm:align-middle ring-1 ring-black ring-opacity-5">

                <form action="{{ route($routePrefix . '.store', ['journal' => $journal->slug]) }}" method="POST">
                    @csrf

                    <!-- Header -->
                    <div class="relative px-6 py-4 bg-white border-b border-slate-200 rounded-t-[24px]">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-[12px] bg-primary-50 border border-primary-100">
                                    <i class="text-lg text-primary-600 fa-solid fa-user-plus"></i>
                                </div>
                                <div>
                                    <x-text.h1 id="create-user-modal-title" class="text-slate-800 font-bold tracking-tight !text-[18px]">
                                        {{ $isId ? 'Buat Pengguna Baru' : 'Create New User' }}
                                    </x-text.h1>
                                    <p class="text-xs text-slate-400 font-medium">{{ $isId ? 'Buat akun pengguna baru dan daftarkan di' : 'Create a new user account and enroll in' }} {{ $journal->name }}.</p>
                                </div>
                            </div>
                            <button type="button" @click="createUserModalOpen = false"
                                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-all duration-200 focus:outline-none cursor-pointer">
                                <i class="text-lg fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 md:p-8 space-y-6 max-h-[70vh] overflow-y-auto">
                        {{-- Section 1: Identity --}}
                        <div class="bg-white rounded-[24px] border border-slate-100 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                            <h2 class="text-base font-semibold text-slate-800 mb-4 flex items-center">
                                <i class="fa-solid fa-id-card text-primary-500 mr-2"></i> {{ $isId ? 'Identitas' : 'Identity' }}
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Given Name --}}
                                <div>
                                    <label for="given_name" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Nama Depan' : 'Given Name' }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="given_name" id="given_name" value="{{ old('given_name') }}" required
                                        class="mt-1 block w-full rounded-lg border-slate-200 focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5">
                                    @error('given_name')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Family Name --}}
                                <div>
                                    <label for="family_name" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Nama Belakang' : 'Family Name' }}</label>
                                    <input type="text" name="family_name" id="family_name" value="{{ old('family_name') }}"
                                        class="mt-1 block w-full rounded-lg border-slate-200 focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5">
                                    @error('family_name')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Preferred Public Name --}}
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Nama Publik Pilihan' : 'Preferred Public Name' }}</label>
                                    <p class="text-xs text-slate-400 mb-1.5">{{ $isId ? 'Bagaimana pengguna ingin disapa (misal "Dr. Jane Doe").' : 'How the user prefers to be addressed (e.g. "Dr. Jane Doe").' }}</p>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                                        class="mt-1 block w-full rounded-lg border-slate-200 focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5">
                                    @error('name')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Contact --}}
                        <div class="bg-white rounded-[24px] border border-slate-100 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] bg-slate-50/30">
                            <h2 class="text-base font-semibold text-slate-800 mb-4 flex items-center">
                                <i class="fa-solid fa-address-book text-primary-500 mr-2"></i> {{ $isId ? 'Kontak' : 'Contact' }}
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Email --}}
                                <div>
                                    <label for="email" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Surel' : 'Email' }} <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                        class="mt-1 block w-full rounded-lg border-slate-200 focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5">
                                    @error('email')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Phone --}}
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Telepon' : 'Phone' }}</label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                        class="mt-1 block w-full rounded-lg border-slate-200 focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5">
                                    @error('phone')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Affiliation --}}
                                <div class="md:col-span-2">
                                    <label for="affiliation" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Afiliasi' : 'Affiliation' }}</label>
                                    <input type="text" name="affiliation" id="affiliation" value="{{ old('affiliation') }}"
                                        class="mt-1 block w-full rounded-lg border-slate-200 focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5">
                                    @error('affiliation')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Mailing Address --}}
                                <div class="md:col-span-2">
                                    <label for="mailing_address" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Alamat Surat' : 'Mailing Address' }}</label>
                                    <textarea name="mailing_address" id="mailing_address" rows="3"
                                        class="mt-1 block w-full rounded-lg border-slate-200 focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5">{{ old('mailing_address') }}</textarea>
                                    @error('mailing_address')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Country --}}
                                <div>
                                    <label for="country" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Negara' : 'Country' }}</label>
                                    <select name="country" id="country"
                                        class="mt-1 block w-full rounded-lg border-slate-200 focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5 bg-white cursor-pointer">
                                        <option value="">{{ $isId ? 'Pilih Negara...' : 'Select Country...' }}</option>
                                        @foreach (config('countries', []) as $code => $name)
                                            <option value="{{ $code }}" {{ old('country') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Roles --}}
                        <div class="bg-white rounded-[24px] border border-slate-100 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                            <h2 class="text-base font-semibold text-slate-800 mb-2 flex items-center">
                                <i class="fa-solid fa-user-tag text-primary-500 mr-2"></i> {{ $isId ? 'Peran' : 'Roles' }}
                            </h2>
                            <p class="text-xs text-slate-400 mb-4">{{ $isId ? 'Pilih peran untuk' : 'Select roles for' }} <strong>{{ $journal->name }}</strong>.</p>

                            @error('roles')
                                <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
                            @enderror

                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @php
                                    $roleDescriptions = $isId ? [
                                        'Journal Manager' => 'Akses administratif penuh ke jurnal',
                                        'Editor' => 'Dapat mengelola pengajuan naskah dan membuat keputusan editorial',
                                        'Section Editor' => 'Mengelola pengajuan naskah dalam bidang yang ditetapkan',
                                        'Reviewer' => 'Dapat meninjau naskah dan memberikan rekomendasi',
                                        'Author' => 'Dapat mengirimkan naskah ke jurnal',
                                        'Reader' => 'Akses dasar ke konten yang diterbitkan',
                                        'Admin' => 'Akses administratif di seluruh jurnal',
                                    ] : [
                                        'Journal Manager' => 'Full administrative access to the journal',
                                        'Editor' => 'Can manage submissions and make editorial decisions',
                                        'Section Editor' => 'Manages submissions within assigned sections',
                                        'Reviewer' => 'Can review submissions and provide recommendations',
                                        'Author' => 'Can submit manuscripts to the journal',
                                        'Reader' => 'Basic access to published content',
                                        'Admin' => 'Administrative access across all journals',
                                    ];
                                    $roleColors = [
                                        'Journal Manager' => 'border-red-100 bg-red-50/30 hover:bg-red-50',
                                        'Editor' => 'border-blue-100 bg-blue-50/30 hover:bg-blue-50',
                                        'Section Editor' => 'border-blue-100 bg-blue-50/30 hover:bg-blue-50',
                                        'Reviewer' => 'border-amber-100 bg-amber-50/30 hover:bg-amber-50',
                                        'Author' => 'border-emerald-100 bg-emerald-50/30 hover:bg-emerald-50',
                                        'Reader' => 'border-gray-100 bg-gray-50/30 hover:bg-gray-50',
                                        'Admin' => 'border-purple-100 bg-purple-50/30 hover:bg-purple-50',
                                    ];
                                @endphp

                                @foreach ($assignableRoles as $role)
                                    <label class="relative flex items-start p-4 rounded-xl border cursor-pointer transition-all {{ $roleColors[$role->name] ?? 'border-slate-200 bg-slate-50 hover:bg-slate-100' }} {{ in_array($role->id, old('roles', [])) ? 'ring-2 ring-primary-500' : '' }}">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                                {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                                                class="h-5 w-5 rounded text-primary-600 border-slate-300 focus:ring-primary-500 cursor-pointer">
                                        </div>
                                        <div class="ml-3">
                                            <span class="text-sm font-semibold text-slate-800">
                                                {{ $isId ? ($role->name === 'Journal Manager' ? 'Manajer Jurnal' : ($role->name === 'Section Editor' ? 'Editor Bagian' : ($role->name === 'Reviewer' ? 'Peninjau' : ($role->name === 'Author' ? 'Penulis' : ($role->name === 'Reader' ? 'Pembaca' : ($role->name === 'Admin' ? 'Administrator' : $role->name)))))) : $role->name }}
                                            </span>
                                            <p class="text-[11px] text-slate-400 mt-1">
                                                {{ $roleDescriptions[$role->name] ?? ($isId ? 'Peran akses standar' : 'Standard access role') }}
                                            </p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Section 4: Public Profile --}}
                        <div class="bg-white rounded-[24px] border border-slate-100 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] bg-slate-50/30">
                            <h2 class="text-base font-semibold text-slate-800 mb-4 flex items-center">
                                <i class="fa-solid fa-globe text-primary-500 mr-2"></i> {{ $isId ? 'Profil Publik' : 'Public Profile' }}
                            </h2>
                            <div class="grid grid-cols-1 gap-6">
                                {{-- ORCID --}}
                                <div>
                                    <label for="orcid_id" class="block text-sm font-medium text-slate-700">ORCID iD</label>
                                    <div class="mt-1 flex rounded-lg shadow-sm">
                                        <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-slate-200 bg-slate-100 text-slate-500 sm:text-sm">
                                            https://orcid.org/
                                        </span>
                                        <input type="text" name="orcid_id" id="orcid_id" value="{{ old('orcid_id') }}"
                                            placeholder="0000-0000-0000-0000"
                                            class="flex-1 min-w-0 block w-full px-3 py-2.5 rounded-none rounded-r-lg border-slate-200 focus:ring-primary-500 focus:border-primary-500 sm:text-sm border">
                                    </div>
                                </div>

                                {{-- Bio --}}
                                <div>
                                    <label for="bio" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Pernyataan Bio' : 'Bio Statement' }}</label>
                                    <textarea name="bio" id="bio" rows="3"
                                        class="mt-1 block w-full rounded-lg border-slate-200 focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5">{{ old('bio') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Section 5: Account --}}
                        <div class="bg-white rounded-[24px] border border-slate-100 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                            <h2 class="text-base font-semibold text-slate-800 mb-4 flex items-center">
                                <i class="fa-solid fa-lock text-primary-500 mr-2"></i> {{ $isId ? 'Akses Akun' : 'Account Access' }}
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6" x-data="{
                                username: '{{ old('username', '') }}',
                                password: '',
                                get passwordStrength() {
                                    if (this.password.length === 0) {
                                        return { score: 0, color: 'bg-gray-200 w-0', text: '', isStrong: false };
                                    }
                                    let score = 0;
                                    let hasLower = /[a-z]/.test(this.password);
                                    let hasUpper = /[A-Z]/.test(this.password);
                                    let hasNumber = /[0-9]/.test(this.password);
                                    let hasSpecial = /[^A-Za-z0-9]/.test(this.password);
                                    
                                    if (hasLower) score++;
                                    if (hasUpper) score++;
                                    if (hasNumber) score++;
                                    if (hasSpecial) score++;
                                    
                                    if (this.password.length < 8) {
                                        return { score: 1, color: 'bg-red-500 w-1/3', text: '{{ $isId ? 'Lemah' : 'Weak' }}', isStrong: false };
                                    }
                                    
                                    if (score <= 2) {
                                        return { score: 1, color: 'bg-red-500 w-1/3', text: '{{ $isId ? 'Lemah' : 'Weak' }}', isStrong: false };
                                    } else if (score === 3) {
                                        return { score: 2, color: 'bg-yellow-500 w-2/3', text: '{{ $isId ? 'Sedang' : 'Medium' }}', isStrong: false };
                                    } else {
                                        return { score: 3, color: 'bg-emerald-500 w-full', text: '{{ $isId ? 'Kuat' : 'Strong' }}', isStrong: true };
                                    }
                                }
                            }">
                                {{-- Username --}}
                                <div>
                                    <label for="username" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Nama Pengguna' : 'Username' }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="username" id="username" x-model="username" @input="username = username.toLowerCase()" required
                                        class="mt-1 block w-full rounded-lg border-slate-200 focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5">
                                    @error('username')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Password --}}
                                <div>
                                    <label for="password" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Kata Sandi' : 'Password' }} <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="password" name="password" id="password" x-model="password" required
                                            class="mt-1 block w-full rounded-lg border-slate-200 focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5 pr-10">
                                        <i class="fa-solid fa-circle-check text-emerald-500 text-lg absolute right-3 inset-y-0 my-auto h-fit pointer-events-none" x-show="passwordStrength.isStrong" x-cloak></i>
                                    </div>
                                    <div class="mt-1.5 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden" x-show="password.length > 0" x-cloak>
                                        <div class="h-full transition-all duration-300 rounded-full" :class="passwordStrength.color"></div>
                                    </div>
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-1 gap-1">
                                        <span class="text-xs font-semibold" :class="{'text-red-500': passwordStrength.score === 1, 'text-yellow-500': passwordStrength.score === 2, 'text-emerald-500': passwordStrength.score === 3}" x-text="passwordStrength.text" x-show="password.length > 0" x-cloak></span>
                                    </div>
                                    <span class="text-[10px] font-medium text-slate-400 flex items-center gap-1 mt-1"><i class="fa-solid fa-circle-info"></i> {{ $isId ? 'Kombinasi huruf, angka & simbol.' : 'Combination of letters, numbers & symbols.' }}</span>
                                    @error('password')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Confirm Password --}}
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">{{ $isId ? 'Ulangi Kata Sandi' : 'Repeat Password' }} <span class="text-red-500">*</span></label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" required
                                        class="mt-1 block w-full rounded-lg border-slate-200 focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-[24px]">
                        <button type="button" @click="createUserModalOpen = false"
                            class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 transition-colors cursor-pointer">
                            {{ $isId ? 'Batal' : 'Cancel' }}
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-primary-600 text-white rounded-xl text-sm font-semibold hover:bg-primary-700 transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-user-plus"></i>
                            {{ $isId ? 'Buat Pengguna' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
