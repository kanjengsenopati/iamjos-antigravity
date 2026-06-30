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
        }
    }" class="relative">
        <!-- Header -->
        @include('admin.journals.users._header', ['activeTab' => 'users'])

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <form action="{{ route($routePrefix . '.index', ['journal' => $journal->slug]) }}" method="GET"
            class="flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white transition-colors"
                    placeholder="Search by name, username, or email..."
                    style="padding-left: 2.5rem !important;">
            </div>

            <div class="relative min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-filter text-gray-400"></i>
                </div>
                <select name="role"
                    class="block w-full pl-10 pr-10 py-2 border border-gray-200 rounded-lg sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white appearance-none cursor-pointer"
                    onchange="this.form.submit()"
                    style="padding-left: 2.5rem !important; padding-right: 2.5rem !important;">
                    <option value="">All Roles</option>
                    @foreach ($roles as $roleName)
                        <option value="{{ $roleName }}" {{ request('role') == $roleName ? 'selected' : '' }}>
                            {{ $roleName }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ expandedUser: null }">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Given Name</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Family Name</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Username</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        @php
                            // Extract Given Name & Family Name fallbacks
                            $givenName = $user->given_name ?? strtok($user->name, ' ');
                            $familyName = $user->family_name ?? (substr(strstr($user->name, ' '), 1) ?: '');
                            
                            $userRoles = $user->journal_roles->pluck('name')->toArray();
                            $isSuperAdmin = in_array('Super Admin', $userRoles);
                            if (empty($userRoles)) {
                                $userRoles = ['Reader'];
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors cursor-pointer"
                            @click="expandedUser = (expandedUser === '{{ $user->id }}' ? null : '{{ $user->id }}')">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <!-- Toggle Arrow -->
                                    <span class="mr-3 text-gray-400 transition-transform duration-200"
                                        :class="expandedUser === '{{ $user->id }}' ? 'rotate-90' : ''">
                                        <i class="fa-solid fa-caret-right text-[14px]"></i>
                                    </span>
                                    <div class="text-sm font-medium {{ $user->disabled ? 'text-gray-400 line-through' : 'text-gray-900' }}">
                                        {{ $givenName }}
                                    </div>
                                    @if($user->disabled)
                                        <span class="ml-2 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-red-100 text-red-700 rounded-md">Disabled</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $user->disabled ? 'text-gray-400 line-through' : 'text-gray-500' }}">
                                {{ $familyName }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $user->disabled ? 'text-gray-400' : 'text-gray-500' }}">
                                {{ $user->username ?? Str::slug($user->name) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $user->disabled ? 'text-gray-400' : 'text-gray-500' }}">
                                <div class="inline-flex items-center gap-1.5">
                                    <span>{{ $user->email }}</span>
                                    @if ($user->email_verified_at)
                                        <span class="text-[9px] text-emerald-600 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-100 font-semibold">Verified</span>
                                    @else
                                        <span class="text-[9px] text-amber-600 bg-amber-50 px-1 py-0.2 rounded border border-amber-100 font-semibold">Unverified</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <!-- Expandable Actions Row -->
                        <tr x-show="expandedUser === '{{ $user->id }}'" x-cloak class="bg-slate-50/50">
                            <td colspan="4" class="px-12 py-3 border-t border-slate-100">
                                <!-- Roles Display -->
                                <div class="flex flex-wrap gap-1.5 items-center mb-2.5">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mr-1.5">Roles:</span>
                                    @foreach ($userRoles as $role)
                                        @php
                                            $badgeClass = match ($role) {
                                                'Super Admin'
                                                    => 'bg-purple-100 text-purple-800 border-purple-200 ring-1 ring-purple-500/20',
                                                'Admin', 'Journal Manager' => 'bg-red-50 text-red-700 border-red-100',
                                                'Editor',
                                                'Section Editor'
                                                    => 'bg-blue-50 text-blue-700 border-blue-100',
                                                'Reviewer' => 'bg-amber-50 text-amber-700 border-amber-100',
                                                'Author' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                                'Reader' => 'bg-gray-50 text-gray-600 border-gray-100',
                                                default => 'bg-gray-50 text-gray-600 border-gray-100',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $badgeClass }}">
                                            @if ($role === 'Super Admin')
                                                <i class="fa-solid fa-shield-halved mr-1 text-[8px]"></i>
                                            @endif
                                            {{ $role }}
                                        </span>
                                    @endforeach
                                </div>

                                <!-- Text Link Actions -->
                                <div class="flex flex-wrap gap-4 text-xs font-semibold items-center">
                                    @if ($isSuperAdmin)
                                        <span class="text-xs text-purple-600 font-medium px-2 py-0.5 bg-purple-50 rounded-lg border border-purple-200/50">
                                            <i class="fa-solid fa-shield-halved mr-1"></i> Full Access
                                        </span>
                                    @else
                                        <!-- Email -->
                                        <button type="button"
                                            @click.stop="openEmailModal({{ json_encode(['id' => $user->id, 'name' => $user->name, 'email' => $user->email]) }})"
                                            class="text-blue-600 hover:text-blue-800 hover:underline">
                                            Email
                                        </button>

                                        <!-- Edit User -->
                                        <a href="{{ route($routePrefix . '.edit', ['journal' => $journal->slug, 'user' => $user->id]) }}"
                                            class="text-blue-600 hover:text-blue-800 hover:underline" @click.stop>
                                            Edit User
                                        </a>

                                        <!-- Disable/Enable -->
                                        <form
                                            action="{{ route($routePrefix . ($user->disabled ? '.enable' : '.disable'), ['journal' => $journal->slug, 'user' => $user->id]) }}"
                                            method="POST" class="inline" @click.stop>
                                            @csrf
                                            <button type="submit" class="text-pink-600 hover:text-pink-800 hover:underline">
                                                {{ $user->disabled ? 'Enable' : 'Disable' }}
                                            </button>
                                        </form>

                                        <!-- Remove from Journal -->
                                        <form
                                            action="{{ route($routePrefix . '.destroy', ['journal' => $journal->slug, 'user' => $user->id]) }}"
                                            method="POST" class="inline" @click.stop
                                            onsubmit="return confirm('Remove this user from {{ $journal->name }}? They will no longer have access to this journal.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 hover:underline">
                                                Remove
                                            </button>
                                        </form>

                                        <!-- Login As -->
                                        <form
                                            action="{{ route($routePrefix . '.login-as', ['journal' => $journal->slug, 'user' => $user]) }}"
                                            method="POST" class="inline" @click.stop>
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:text-blue-800 hover:underline">
                                                Login As
                                            </button>
                                        </form>

                                        <!-- Merge User -->
                                        <a href="{{ route($routePrefix . '.merge', ['journal' => $journal->slug, 'user' => $user->id]) }}"
                                            class="text-blue-600 hover:text-blue-800 hover:underline" @click.stop>
                                            Merge User
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fa-solid fa-users-slash text-gray-400"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">No users enrolled in this journal</p>
                                    <p class="text-xs text-gray-500 mt-1">Enroll existing users or create new ones to get started.</p>
                                    <div class="mt-4 flex gap-3">
                                        <a href="{{ route($routePrefix . '.enroll', ['journal' => $journal->slug]) }}"
                                            class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                            <i class="fa-solid fa-user-plus mr-1"></i> Enroll User
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $users->links() }}
            </div>
        @endif
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
