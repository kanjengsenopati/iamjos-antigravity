@php
    $isId = app()->getLocale() === 'id';
    $roleOrder = ['Author' => 1, 'Reader' => 2, 'Reviewer' => 3, 'Translator' => 4];
    $sortedAvailableRoles = $availableRoles->sortBy(function($role) use ($roleOrder) {
        return $roleOrder[$role->name] ?? 99;
    });
@endphp
<form action="{{ route('journal.profile.roles.update', $journal->slug) }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="border-b-2 border-[#DAD8F4] pb-6 mb-8">
        <x-text.h1>{{ $isId ? 'Peran Jurnal' : 'Journal Roles' }}</x-text.h1>
        <x-text.body class="mt-1 text-slate-500">{{ $isId ? 'Pilih peran yang ingin Anda ambil dalam jurnal ini.' : 'Select the roles you wish to assume in this journal.' }}</x-text.body>
    </div>

    @if (!empty($userJournalAdminRoles[$journal->id]))
        <div class="mb-6 p-5 bg-indigo-50/30 rounded-[24px] border border-indigo-100/50 flex items-start gap-4 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
            <div class="p-2 bg-indigo-100/80 text-indigo-700 rounded-full shrink-0 flex items-center justify-center">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <x-text.h2 class="text-indigo-900 font-semibold">{{ $isId ? 'Peran Administratif Aktif' : 'Active Administrative Roles' }}</x-text.h2>
                <x-text.body class="text-indigo-700 mt-1">
                    {{ $isId ? 'Anda terdaftar dengan peran staf berikut: ' : 'You are registered with the following staff role(s): ' }}<strong class="font-bold text-indigo-900">{{ implode(', ', $userJournalAdminRoles[$journal->id]) }}</strong>. {{ $isId ? 'Peran administratif tidak dapat diubah secara mandiri.' : 'Administrative roles cannot be self-modified.' }}
                </x-text.body>
            </div>
        </div>
    @endif

    @if ($availableRoles->isEmpty())
        <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        {{ $isId ? 'Tidak ada peran yang dapat didaftarkan sendiri untuk jurnal ini.' : 'No self-registerable roles are available for this journal.' }}
                    </p>
                </div>
            </div>
        </div>
    @else
        <div x-data="{ selected: @js($userRolesIds) }" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">

            @foreach ($sortedAvailableRoles as $role)
                <label class="relative flex cursor-pointer rounded-[24px] border-2 p-5 shadow-sm transition-all duration-200"
                    :class="selected.includes('{{ $role->id }}') ?
                        'border-emerald-600 ring-1 ring-emerald-600 bg-emerald-50/30' :
                        'border-slate-100 bg-white hover:border-indigo-300 hover:shadow-md'">

                    <input type="checkbox" name="selected_roles[]" value="{{ $role->id }}" class="sr-only"
                        x-model="selected">

                    <div class="flex flex-1 gap-4">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full"
                                :class="selected.includes('{{ $role->id }}') ? 'bg-emerald-100 text-emerald-600' :
                                    'bg-slate-100 text-slate-400'">
                                @if ($role->name === 'Author')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                        </path>
                                    </svg>
                                @elseif($role->name === 'Reviewer')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                        </path>
                                    </svg>
                                @endif
                            </span>
                        </div>

                        <div class="flex flex-col">
                            <x-text.h2 x-bind:class="selected.includes('{{ $role->id }}') ? 'text-emerald-900' : 'text-slate-800'">
                                {{ $isId ? ($role->name === 'Author' ? 'Penulis' : ($role->name === 'Reviewer' ? 'Mitra Bestari' : ($role->name === 'Reader' ? 'Pembaca' : ($role->name === 'Translator' ? 'Penerjemah' : $role->name)))) : $role->name }}
                            </x-text.h2>
                            <x-text.body class="mt-1" x-bind:class="selected.includes('{{ $role->id }}') ? 'text-emerald-700' : 'text-slate-500'">
                                @if ($role->name === 'Author')
                                    {{ $isId ? 'Kirimkan naskah dan lacak pekerjaan Anda.' : 'Submit manuscripts and track your work.' }}
                                @elseif($role->name === 'Reviewer')
                                    {{ $isId ? 'Tinjau naskah yang ditugaskan kepada Anda.' : 'Review submissions assigned to you.' }}
                                @else
                                    {{ $isId ? 'Baca konten dan terima pemberitahuan.' : 'Read content and receive notifications.' }}
                                @endif
                            </x-text.body>
                        </div>
                    </div>

                    <div x-show="selected.includes('{{ $role->id }}')" x-cloak
                        class="absolute top-4 right-4 bg-emerald-600 text-white rounded-full p-1 shadow-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </label>
            @endforeach
        </div>

        <div class="flex justify-end pt-6 border-t mt-8">
            <button type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm font-medium">
                {{ $isId ? 'Simpan Peran' : 'Save Roles' }}
            </button>
        </div>
    @endif
</form>

<div class="relative py-12">
    <div class="absolute inset-0 flex items-center" aria-hidden="true">
        <div class="w-full border-t-2 border-slate-100"></div>
    </div>
    <div class="relative flex justify-center">
        <x-text.h1 class="bg-white px-6">{{ $isId ? 'Mendaftar di Jurnal Lain' : 'Enroll in Other Journals' }}</x-text.h1>
    </div>
</div>

<div class="grid grid-cols-1 gap-4">
    @foreach ($otherJournals as $otherJournal)
        @php
            $sortedOtherRoles = $otherJournal->roles->sortBy(function($role) use ($roleOrder) {
                return $roleOrder[$role->name] ?? 99;
            })->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->values();
        @endphp
        <div x-data="journalRolesHandler({
            journalId: '{{ $otherJournal->id }}',
            journalName: '{{ addslashes($otherJournal->name) }}',
            journalSlug: '{{ $otherJournal->slug }}',
            journalAbbreviation: '{{ addslashes($otherJournal->abbreviation ?? $otherJournal->name) }}',
            syncUrl: '{{ route('journal.profile.sync-roles', $otherJournal->slug) }}',
            initialRoles: @js($userJournalRoles[$otherJournal->id] ?? []),
            availableRoles: @js($sortedOtherRoles),
            isId: @js($isId)
        })" class="rounded-[24px] bg-white p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-200 group mb-4">
            
            <!-- Row Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="flex-shrink-0 h-14 w-14 bg-slate-50 rounded-[18px] flex items-center justify-center text-indigo-600 font-bold text-2xl uppercase border-2 border-white shadow-sm group-hover:bg-indigo-50 transition-colors">
                        {{ substr($otherJournal->name, 0, 1) }}
                    </div>

                    <div>
                        <x-text.h2>{{ $otherJournal->name }}</x-text.h2>
                        <x-text.body class="text-slate-500 truncate max-w-md mt-1">
                            {{ $otherJournal->description ?? ($isId ? 'Jurnal Akses Terbuka' : 'Open Access Journal') }}
                        </x-text.body>
                        @if (!empty($userJournalAdminRoles[$otherJournal->id]))
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @foreach ($userJournalAdminRoles[$otherJournal->id] as $adminRole)
                                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-[11px] font-semibold text-indigo-700 border border-indigo-100/50">
                                        <svg class="mr-1 h-3 w-3 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        {{ $adminRole }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Dynamic Status Badge -->
                    <span x-show="isEnrolled"
                        class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800" x-cloak>
                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-emerald-500" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        {{ $isId ? 'Terdaftar' : 'Enrolled' }}
                    </span>
                    <span x-show="!isEnrolled"
                        class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600" x-cloak>
                        {{ $isId ? 'Tidak Terdaftar' : 'Not Enrolled' }}
                    </span>

                    <!-- Toggle Button -->
                    <button type="button" @click="isExpanded = !isExpanded"
                        class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-semibold text-blue-600 shadow-sm ring-1 ring-inset ring-blue-300 hover:bg-blue-50 transition-colors">
                        <span x-text="isExpanded ? (isId ? 'Sembunyikan Peran' : 'Hide Roles') : (isId ? 'Kelola Peran' : 'Manage Roles')"></span>
                        <svg class="ml-1.5 h-4 w-4 transform transition-transform duration-200" :class="isExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Expandable Panel -->
            <div x-show="isExpanded" x-collapse x-cloak class="mt-5 pt-5 border-t border-slate-100">
                <div class="relative">
                    <!-- Loading overlay -->
                    <div x-show="isLoading" class="absolute inset-0 bg-white/70 backdrop-blur-[1px] flex items-center justify-center z-10" x-cloak>
                        <div class="flex items-center space-x-2 text-blue-600">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <x-text.body class="text-blue-600 font-semibold">Mengubah peran...</x-text.body>
                        </div>
                    </div>

                    <!-- Role Checkboxes -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <template x-for="role in availableRoles" :key="role.id">
                            <label class="relative flex cursor-pointer rounded-[24px] p-4 shadow-sm transition-all duration-200 border-2"
                                :class="selectedRoles.includes(role.id) ?
                                    'border-emerald-600 ring-1 ring-emerald-600 bg-emerald-50/30' :
                                    'border-slate-50 bg-white hover:border-indigo-300 hover:shadow-md'">
                                
                                <input type="checkbox" :value="role.id" class="sr-only"
                                    :checked="selectedRoles.includes(role.id)"
                                    @change="toggleRole(role.id)">

                                <div class="flex flex-1 gap-3 items-center">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full"
                                            :class="selectedRoles.includes(role.id) ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400'">
                                            <!-- Icons -->
                                            <template x-if="role.name === 'Author'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                    </path>
                                                </svg>
                                            </template>
                                            <template x-if="role.name === 'Reviewer'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                            </template>
                                            <template x-if="role.name !== 'Author' && role.name !== 'Reviewer'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                                    </path>
                                                </svg>
                                            </template>
                                        </span>
                                    </div>

                                    <div class="flex flex-col">
                                        <x-text.h2 x-bind:class="selectedRoles.includes(role.id) ? 'text-emerald-900' : 'text-slate-800'" x-text="isId ? (role.name === 'Author' ? 'Penulis' : (role.name === 'Reviewer' ? 'Mitra Bestari' : (role.name === 'Reader' ? 'Pembaca' : (role.name === 'Translator' ? 'Penerjemah' : role.name)))) : role.name"></x-text.h2>
                                        <x-text.caption x-text="role.name === 'Author' ? (isId ? 'Kirimkan naskah' : 'Submit manuscripts') : (role.name === 'Reviewer' ? (isId ? 'Tinjau naskah' : 'Review submissions') : (isId ? 'Baca konten' : 'Read content'))"></x-text.caption>
                                    </div>
                                </div>

                                <div x-show="selectedRoles.includes(role.id)" x-cloak
                                    class="absolute top-3 right-3 bg-emerald-600 text-white rounded-full p-0.5 shadow-sm">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </label>
                        </template>
                    </div>

                    <!-- Dynamic Notification / Status Message -->
                    <div x-show="statusMessage" x-transition class="mt-4 text-xs font-semibold text-center"
                        :class="statusType === 'success' ? 'text-emerald-600' : 'text-red-600'" x-text="statusMessage" x-cloak>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if ($otherJournals->hasPages())
    <div class="mt-6">
        {{ $otherJournals->links() }}
    </div>
@endif

<script>
    window.journalRolesHandler = function(config) {
        return {
            journalId: config.journalId,
            journalName: config.journalName,
            journalSlug: config.journalSlug,
            journalAbbreviation: config.journalAbbreviation,
            syncUrl: config.syncUrl,
            availableRoles: config.availableRoles,
            selectedRoles: config.initialRoles,
            isId: config.isId,
            isExpanded: false,
            isLoading: false,
            statusMessage: '',
            statusType: '',

            get isEnrolled() {
                return this.selectedRoles.length > 0;
            },

            async toggleRole(roleId) {
                if (this.isLoading) return;

                // Optimistic UI update
                let newRoles = [...this.selectedRoles];
                const index = newRoles.indexOf(roleId);
                if (index > -1) {
                    newRoles.splice(index, 1);
                } else {
                    newRoles.push(roleId);
                }

                this.isLoading = true;
                this.statusMessage = '';

                try {
                    const response = await axios.post(this.syncUrl, {
                        role_ids: newRoles
                    });

                    if (response.data.success) {
                        this.selectedRoles = response.data.roles;
                        this.statusType = 'success';
                        this.statusMessage = this.isId ? 'Peran berhasil diperbarui!' : 'Roles successfully updated!';
                        
                        // Dispatch custom event to dynamically update layouts' switchers
                        window.dispatchEvent(new CustomEvent('journal-enrollment-updated', {
                            detail: {
                                journalId: this.journalId,
                                journalName: this.journalName,
                                journalSlug: this.journalSlug,
                                journalAbbreviation: this.journalAbbreviation,
                                enrolled: response.data.enrolled
                            }
                        }));
                        
                        setTimeout(() => {
                            if (this.statusMessage === 'Peran berhasil diperbarui!' || this.statusMessage === 'Roles successfully updated!') {
                                this.statusMessage = '';
                            }
                        }, 3000);
                    } else {
                        throw new Error(response.data.message || (this.isId ? 'Gagal memperbarui peran.' : 'Failed to update roles.'));
                    }
                } catch (error) {
                    console.error(error);
                    this.statusType = 'error';
                    this.statusMessage = error.response?.data?.message || (this.isId ? 'Terjadi kesalahan saat memperbarui peran.' : 'An error occurred while updating roles.');
                } finally {
                    this.isLoading = false;
                }
            }
        };
    };
</script>
