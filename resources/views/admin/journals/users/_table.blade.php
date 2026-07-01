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
                            <div class="flex flex-wrap gap-2.5 text-xs font-semibold items-center">
                                @if ($isSuperAdmin)
                                    <span class="text-xs text-purple-600 font-medium px-2 py-0.5 bg-purple-50 rounded-lg border border-purple-200/50">
                                        <i class="fa-solid fa-shield-halved mr-1"></i> Full Access
                                    </span>
                                @else
                                    <!-- Email -->
                                    <button type="button"
                                        @click.stop="openEmailModal({{ json_encode(['id' => $user->id, 'name' => $user->name, 'email' => $user->email]) }})"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border transition-all duration-150 shadow-sm text-blue-600 bg-blue-50 border-blue-200/60 hover:bg-blue-100/80 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                        <i class="fa-solid fa-envelope text-[11px]"></i> Email
                                    </button>

                                    <!-- Edit User -->
                                    <a href="{{ route($routePrefix . '.edit', ['journal' => $journal->slug, 'user' => $user->id]) }}"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border transition-all duration-150 shadow-sm text-indigo-600 bg-indigo-50 border-indigo-200/60 hover:bg-indigo-100/80 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" @click.stop>
                                        <i class="fa-solid fa-user-pen text-[11px]"></i> Edit User
                                    </a>

                                    <!-- Disable/Enable -->
                                    <form
                                        action="{{ route($routePrefix . ($user->disabled ? '.enable' : '.disable'), ['journal' => $journal->slug, 'user' => $user->id]) }}"
                                        method="POST" class="inline" @click.stop>
                                        @csrf
                                        @if ($user->disabled)
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border transition-all duration-150 shadow-sm text-emerald-600 bg-emerald-50 border-emerald-200/60 hover:bg-emerald-100/80 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                                <i class="fa-solid fa-circle-check text-[11px]"></i> Enable
                                            </button>
                                        @else
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border transition-all duration-150 shadow-sm text-red-600 bg-red-50 border-red-200/60 hover:bg-red-100/80 focus:outline-none focus:ring-2 focus:ring-red-500/20">
                                                <i class="fa-solid fa-ban text-[11px]"></i> Disable
                                            </button>
                                        @endif
                                    </form>

                                    <!-- Remove from Journal -->
                                    <form
                                        action="{{ route($routePrefix . '.destroy', ['journal' => $journal->slug, 'user' => $user->id]) }}"
                                        method="POST" class="inline" @click.stop
                                        onsubmit="return confirm('Remove this user from {{ $journal->name }}? They will no longer have access to this journal.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border transition-all duration-150 shadow-sm text-red-600 bg-red-50 border-red-200/60 hover:bg-red-100/80 focus:outline-none focus:ring-2 focus:ring-red-500/20">
                                            <i class="fa-solid fa-trash-can text-[11px]"></i> Remove
                                        </button>
                                    </form>

                                    <!-- Login As -->
                                    <form
                                        action="{{ route($routePrefix . '.login-as', ['journal' => $journal->slug, 'user' => $user]) }}"
                                        method="POST" class="inline" @click.stop>
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border transition-all duration-150 shadow-sm text-violet-600 bg-violet-50 border-violet-200/60 hover:bg-violet-100/80 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                                            <i class="fa-solid fa-user-secret text-[11px]"></i> Login As
                                        </button>
                                    </form>

                                    <!-- Merge User -->
                                    <a href="{{ route($routePrefix . '.merge', ['journal' => $journal->slug, 'user' => $user->id]) }}"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border transition-all duration-150 shadow-sm text-slate-600 bg-slate-50 border-slate-200/60 hover:bg-slate-100/80 focus:outline-none focus:ring-2 focus:ring-slate-500/20" @click.stop>
                                        <i class="fa-solid fa-code-merge text-[11px]"></i> Merge User
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
