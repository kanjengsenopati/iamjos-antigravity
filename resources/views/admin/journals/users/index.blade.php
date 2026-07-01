@extends('layouts.app')

@section('title', 'User Management')

@section('content')
    <div x-data="{
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
                            placeholder="Search by name, username, or email..."
                            style="padding-left: 2.5rem !important;">
                    </div>
                    <button type="button" @click="triggerSearch()"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition-all duration-155 flex items-center gap-1.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i> Search
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
                    <option value="">All Roles</option>
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
                <span class="text-xs font-semibold text-gray-600">Searching...</span>
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
                <div class="relative px-6 py-4 bg-blue-600 border-b border-blue-500/30 rounded-t-[24px]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-[12px] bg-white/20 backdrop-blur-sm shadow-inner border border-white/10">
                                <i class="text-lg text-white fa-solid fa-paper-plane"></i>
                            </div>
                            <div>
                                <x-text.h1 id="email-modal-title" class="text-white font-bold tracking-tight !text-[18px]">
                                    Send Email
                                </x-text.h1>
                                <p class="text-xs text-blue-100 font-medium">Compose an email to user</p>
                            </div>
                        </div>
                        <button @click="emailModalOpen = false"
                            class="p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-full transition-all duration-200 focus:outline-none">
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
                        <x-text.label class="block mb-1">To Recipient</x-text.label>
                        <div class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 flex items-center justify-between">
                            <span x-text="recipientName + ' <' + recipientEmail + '>'"></span>
                        </div>
                    </div>

                    <!-- Subject -->
                    <div>
                        <x-text.label class="block mb-1">Subject</x-text.label>
                        <input type="text" x-model="subject" 
                            class="w-full px-3 py-2 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800"
                            placeholder="Enter email subject...">
                    </div>

                    <!-- Body -->
                    <div>
                        <x-text.label class="block mb-1">Message Body</x-text.label>
                        <textarea x-model="body" rows="6"
                            class="w-full px-3 py-2 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 resize-y"
                            placeholder="Type your message here..."></textarea>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-[24px]">
                    <button type="button" @click="emailModalOpen = false"
                        class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 transition-colors">
                        Cancel
                    </button>
                    <button type="button" @click="sendEmail()" :disabled="isSubmitting"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors flex items-center gap-2 shadow-sm disabled:opacity-55">
                        <i class="fa-solid fa-paper-plane" x-show="!isSubmitting"></i>
                        <i class="fa-solid fa-spinner animate-spin" x-show="isSubmitting" x-cloak></i>
                        <span x-text="isSubmitting ? 'Sending...' : 'Send Email'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
