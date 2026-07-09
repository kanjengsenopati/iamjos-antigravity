<div x-show="participantModalOpen" x-cloak class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        {{-- Background overlay --}}
        <div x-show="participantModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"
             @click="participantModalOpen = false"></div>

        {{-- Modal Panel --}}
        <div x-show="participantModalOpen" x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-[24px] text-left overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] transform transition-all sm:my-8 sm:max-w-lg w-full max-h-[90vh] flex flex-col z-50">

            {{-- Header --}}
            <div class="px-6 pt-6 pb-4 flex items-start justify-between shrink-0">
                <div class="flex items-start space-x-4 flex-1">
                    <div class="shrink-0 flex items-center justify-center h-10 w-10 rounded-full"
                         :class="{
                            'bg-blue-100': participantModalStage === 'review',
                            'bg-purple-100': participantModalStage === 'copyediting',
                            'bg-green-100': participantModalStage === 'production'
                         }">
                        <i class="fa-solid"
                           :class="{
                              'fa-user-check text-blue-600': participantModalStage === 'review',
                              'fa-pen-to-square text-purple-600': participantModalStage === 'copyediting',
                              'fa-file-export text-green-600': participantModalStage === 'production'
                           }"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg leading-6 font-semibold text-gray-900" id="modal-title">
                            <span x-show="participantModalStage === 'review'">
                                {{ $isId ? 'Tugaskan Reviewer' : 'Assign Reviewer' }}
                            </span>
                            <span x-show="participantModalStage === 'copyediting'">
                                {{ $isId ? 'Tugaskan Copyeditor' : 'Assign Copyeditor' }}
                            </span>
                            <span x-show="participantModalStage === 'production'">
                                {{ $isId ? 'Tugaskan Staf Produksi' : 'Assign Production Staff' }}
                            </span>
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            <span x-show="participantModalStage === 'review'">
                                {{ $isId ? 'Cari dan tugaskan reviewer untuk meninjau naskah ini.' : 'Search and assign a reviewer to review this submission.' }}
                            </span>
                            <span x-show="participantModalStage === 'copyediting'">
                                {{ $isId ? 'Cari dan tugaskan copyeditor untuk mengedit naskah ini.' : 'Search and assign a copyeditor to edit this submission.' }}
                            </span>
                            <span x-show="participantModalStage === 'production'">
                                {{ $isId ? 'Cari dan tugaskan staf produksi untuk memproses naskah ini.' : 'Search and assign production staff to process this submission.' }}
                            </span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="participantModalOpen = false"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1.5 rounded-full hover:bg-gray-100 focus:outline-none">
                    <i class="fa-solid fa-times text-lg"></i>
                </button>
            </div>

            <div class="flex-1 overflow-hidden flex flex-col min-h-0">
                <form id="assignParticipantForm"
                    :action="participantModalStage === 'review' ? '{{ Route::has('journal.workflow.assign-reviewer') ? route('journal.workflow.assign-reviewer', ['journal' => $journal->slug, 'submission' => $submission->seq_id]) : '' }}' : 
                             participantModalStage === 'copyediting' ? '{{ Route::has('journal.workflow.assign-copyeditor') ? route('journal.workflow.assign-copyeditor', ['journal' => $journal->slug, 'submission' => $submission->seq_id]) : '' }}' :
                             participantModalStage === 'production' ? '{{ Route::has('journal.workflow.assign-production') ? route('journal.workflow.assign-production', ['journal' => $journal->slug, 'submission' => $submission->seq_id]) : '' }}' : ''"
                    method="POST" class="flex-1 min-h-0 flex flex-col">
                    @csrf
                    <input type="hidden" name="user_id" :value="selectedParticipant?.id">

                    {{-- Search & Filter --}}
                    <div class="p-4 border-b border-gray-100 shrink-0 space-y-3">
                        {{-- Search Input --}}
                        <div class="relative">
                            <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" x-model="participantSearch" placeholder="{{ $isId ? 'Cari berdasarkan nama atau email...' : 'Search by name or email...' }}"
                                class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                                style="padding-left: 2.5rem !important;"
                                autocomplete="off">
                        </div>

                        {{-- Role Filter --}}
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">{{ $isId ? 'Filter Peran:' : 'Filter Role:' }}</span>
                            <select x-model="participantRoleFilter"
                                class="form-select flex-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option x-show="participantModalStage === 'review'" value="Reviewer">{{ $isId ? 'Reviewer' : 'Reviewer' }}</option>
                                <option x-show="participantModalStage === 'copyediting'" value="Copyeditor">{{ $isId ? 'Copyeditor' : 'Copyeditor' }}</option>
                                <option x-show="participantModalStage === 'copyediting'" value="Layout Editor">{{ $isId ? 'Editor Tata Letak' : 'Layout Editor' }}</option>
                                <option x-show="participantModalStage === 'production'" value="Layout Editor">{{ $isId ? 'Editor Tata Letak' : 'Layout Editor' }}</option>
                                <option x-show="participantModalStage === 'production'" value="Proofreader">{{ $isId ? 'Proofreader' : 'Proofreader' }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Participant List --}}
                    <div class="flex-1 overflow-y-auto p-0 min-h-0">
                        @if (isset($potentialParticipants) && (is_array($potentialParticipants) ? empty($potentialParticipants) : $potentialParticipants->isEmpty()))
                            <div class="p-8 text-center text-gray-500">
                                <i class="fa-solid fa-users-slash text-4xl mb-3 text-gray-300"></i>
                                <p>{{ $isId ? 'Tidak ada peserta yang memenuhi syarat.' : 'No eligible participants found.' }}</p>
                            </div>
                        @else
                            {{-- Empty Search/Filter Results --}}
                            <div x-show="filteredParticipants.length === 0" class="p-8 text-center text-gray-500"
                                style="display: none;">
                                <p>{{ $isId ? 'Tidak ada peserta yang cocok dengan pencarian atau filter Anda.' : 'No participants match your search or filter.' }}</p>
                            </div>

                            <template x-for="participant in filteredParticipants" :key="participant.id">
                                <div @click="selectParticipant(participant)"
                                    class="flex items-center px-6 py-4 cursor-pointer border-b border-gray-50 last:border-0 transition-colors"
                                    :class="selectedParticipant?.id === participant.id ? 'bg-indigo-50/60' : 'hover:bg-gray-50'">

                                    {{-- Avatar --}}
                                    <div
                                        class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-sm shrink-0 mr-4">
                                        <span x-text="(participant.name || 'P').charAt(0).toUpperCase()"></span>
                                    </div>
 
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 truncate" x-text="participant.name"></p>
                                        <p class="text-xs text-gray-500 truncate" x-text="participant.email"></p>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            {{-- Display Role Badges --}}
                                            <template x-if="participant.role_names">
                                                <template x-for="role in participant.role_names">
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800"
                                                        x-text="{'reviewer': '{{ $isId ? 'Reviewer' : 'Reviewer' }}', 'copyeditor': '{{ $isId ? 'Copyeditor' : 'Copyeditor' }}', 'layout editor': '{{ $isId ? 'Editor Tata Letak' : 'Layout Editor' }}', 'proofreader': '{{ $isId ? 'Proofreader' : 'Proofreader' }}'}[(role || '').toLowerCase()] || role"></span>
                                                </template>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Checkmark --}}
                                    <div class="w-6 h-6 rounded-full border flex items-center justify-center transition-all"
                                        :class="selectedParticipant?.id === participant.id ? 'bg-indigo-600 border-indigo-600' :
                                            'border-gray-300 bg-white'">
                                        <i x-show="selectedParticipant?.id === participant.id"
                                            class="fa-solid fa-check text-white text-xs"></i>
                                    </div>
                                </div>
                            </template>
                        @endif
                    </div>

                    {{-- Footer actions --}}
                    <div
                        class="p-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3 rounded-b-xl shrink-0">
                        <button type="button" @click="participantModalOpen = false"
                            class="px-4 py-2 text-gray-600 font-medium hover:bg-gray-200 rounded-lg transition-colors">
                            {{ $isId ? 'Batal' : 'Cancel' }}
                        </button>
                        <button type="submit" :disabled="!selectedParticipant"
                            class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm hover:shadow">
                            {{ $isId ? 'Tugaskan' : 'Assign' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
