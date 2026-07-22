@php
    $responseWeeks = (int) ($journal->review_response_weeks ?? 2);
    $completionWeeks = (int) ($journal->review_completion_weeks ?? 4);
    $defaultReviewMode = $journal->review_mode ?? 'double_blind';
    $defaultResponseDate = \Carbon\Carbon::now()->addWeeks($responseWeeks)->format('Y-m-d');
    $defaultReviewDate = \Carbon\Carbon::now()->addWeeks($completionWeeks)->format('Y-m-d');
@endphp

<div x-show="assignReviewerModalOpen" x-cloak class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="assign-reviewer-modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        {{-- Background overlay --}}
        <div x-show="assignReviewerModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"
             @click="assignReviewerModalOpen = false"></div>

        {{-- Wide Modal Panel --}}
        <div x-show="assignReviewerModalOpen" x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-[24px] text-left overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] transform transition-all sm:my-8 sm:max-w-4xl w-full max-h-[90vh] flex flex-col z-50">

            {{-- Header --}}
            <div class="px-6 pt-6 pb-4 flex items-start justify-between flex-shrink-0 border-b border-gray-100 bg-gray-50/50">
                <div class="flex items-start space-x-4 flex-1">
                    <div class="flex-shrink-0 flex items-center justify-center h-11 w-11 rounded-full bg-indigo-100 text-indigo-600">
                        <i class="fa-solid fa-user-plus text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl leading-6 font-bold text-gray-900" id="assign-reviewer-modal-title">
                            {{ $isId ? 'Tugaskan Reviewer (Mitra Bestari)' : 'Assign Reviewer' }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $isId ? 'Cari reviewer, pilih metode ulasan, dan tentukan batas waktu penugasan naskah ini.' : 'Search reviewer, select review method, and set due dates for this submission.' }}
                        </p>
                    </div>
                </div>
                <button type="button" @click="assignReviewerModalOpen = false"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1.5 rounded-full hover:bg-gray-100 focus:outline-none">
                    <i class="fa-solid fa-times text-lg"></i>
                </button>
            </div>

            {{-- Body Form --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <form id="assignReviewerForm"
                    action="{{ route('journal.workflow.assign-reviewer', ['journal' => $journal->slug, 'submission' => $submission->url_slug]) }}"
                    method="POST" @submit="assignReviewerSubmitting = true">
                    @csrf
                    <input type="hidden" name="reviewer_id" :value="selectedReviewerForAssign?.id || ''">
                    <input type="hidden" name="round" :value="selectedRoundNumber || {{ $selectedRoundNumber }}">

                    {{-- Reviewer Selection Section --}}
                    <div class="space-y-4">
                        {{-- Search Input (Hidden when reviewer selected) --}}
                        <div x-show="!selectedReviewerForAssign">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                {{ $isId ? 'Cari & Pilih Reviewer' : 'Search & Select Reviewer' }}
                            </label>
                            <div class="relative">
                                <input type="text" x-model.debounce.400ms="assignReviewerSearch"
                                    @input="searchReviewersForAssign()"
                                    placeholder="{{ $isId ? 'Cari berdasarkan nama, email, atau afiliasi...' : 'Search by name, email, or affiliation...' }}"
                                    style="padding-left: 2.75rem !important;"
                                    class="block w-full pr-10 py-2.5 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-search"></i>
                                </div>
                                <div x-show="assignReviewerIsSearching" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-indigo-500">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Results Table --}}
                        <div x-show="!selectedReviewerForAssign && assignReviewerResults.length > 0"
                            class="border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    {{ $isId ? 'Daftar Reviewer Tersedia' : 'Available Reviewers' }}
                                </h4>
                                <span class="text-xs font-medium text-gray-500" x-text="assignReviewerResults.length + ' {{ $isId ? 'ditemukan' : 'found' }}'"></span>
                            </div>
                            <div class="max-h-60 overflow-y-auto">
                                <table class="min-w-full text-left border-collapse">
                                    <thead class="bg-gray-50/80 text-[11px] font-bold uppercase text-gray-400 sticky top-0 bg-white shadow-xs">
                                        <tr>
                                            <th class="pl-4 pr-2 py-2.5 w-8"></th>
                                            <th class="px-2 py-2.5">{{ $isId ? 'Reviewer' : 'Reviewer' }}</th>
                                            <th class="px-4 py-2.5">{{ $isId ? 'Statistik' : 'Stats' }}</th>
                                            <th class="px-4 py-2.5 text-right">{{ $isId ? 'Aksi' : 'Action' }}</th>
                                        </tr>
                                    </thead>
                                    <template x-for="reviewer in assignReviewerResults" :key="reviewer.id">
                                        <tbody x-data="{ expanded: false }" class="border-b border-gray-100 last:border-0 hover:bg-slate-50/80 transition">
                                            <tr>
                                                <td class="pl-4 pr-2 py-3 align-top">
                                                    <button type="button" @click="expanded = !expanded"
                                                        class="text-gray-400 hover:text-indigo-600 transition p-1">
                                                        <i class="fa-solid fa-chevron-down transition-transform duration-200 text-xs"
                                                            :class="expanded ? 'rotate-180' : ''"></i>
                                                    </button>
                                                </td>
                                                <td class="px-2 py-3 align-top">
                                                    <div class="font-bold text-gray-900 text-sm" x-text="reviewer.name"></div>
                                                    <div class="text-xs text-gray-500 italic" x-text="reviewer.affiliation || '-'"></div>
                                                    <div class="flex items-center mt-1">
                                                        <template x-for="i in 5">
                                                            <i class="fa-solid fa-star text-[10px]"
                                                                :class="i <= Math.round(reviewer.avg_rating || 0) ? 'text-yellow-400' : 'text-gray-200'"></i>
                                                        </template>
                                                        <span class="ml-1 text-[10px] text-gray-400 font-medium"
                                                            x-text="reviewer.avg_rating ? Number(reviewer.avg_rating).toFixed(1) : ''"></span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 align-top text-xs">
                                                    <div class="flex flex-wrap gap-1">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                                            <span x-text="reviewer.active_count" class="mr-1 font-bold"></span> {{ $isId ? 'Aktif' : 'Active' }}
                                                        </span>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                            <span x-text="reviewer.completed_count" class="mr-1 font-bold"></span> {{ $isId ? 'Selesai' : 'Completed' }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 align-top text-right">
                                                    <button type="button" @click="selectReviewerForAssign(reviewer)"
                                                        class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors">
                                                        {{ $isId ? 'Pilih' : 'Select' }}
                                                    </button>
                                                </td>
                                            </tr>
                                            {{-- Expanded details --}}
                                            <tr x-show="expanded" x-transition style="display: none;">
                                                <td colspan="4" class="px-4 pb-4 pt-0">
                                                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs grid grid-cols-2 md:grid-cols-3 gap-2 mt-1">
                                                        <div class="flex justify-between border-b border-gray-200 pb-1">
                                                            <span class="text-gray-500">{{ $isId ? 'Ulasan Aktif:' : 'Active reviews:' }}</span>
                                                            <span class="font-bold text-gray-800" x-text="reviewer.active_count"></span>
                                                        </div>
                                                        <div class="flex justify-between border-b border-gray-200 pb-1">
                                                            <span class="text-gray-500">{{ $isId ? 'Selesai:' : 'Completed reviews:' }}</span>
                                                            <span class="font-bold text-gray-800" x-text="reviewer.completed_count"></span>
                                                        </div>
                                                        <div class="flex justify-between border-b border-gray-200 pb-1">
                                                            <span class="text-gray-500">{{ $isId ? 'Ditolak:' : 'Declined reviews:' }}</span>
                                                            <span class="font-bold text-gray-800" x-text="reviewer.declined_count"></span>
                                                        </div>
                                                        <div class="flex justify-between border-b border-gray-200 pb-1">
                                                            <span class="text-gray-500">{{ $isId ? 'Dibatalkan:' : 'Cancelled reviews:' }}</span>
                                                            <span class="font-bold text-gray-800" x-text="reviewer.cancelled_count"></span>
                                                        </div>
                                                        <div class="flex justify-between border-b border-gray-200 pb-1">
                                                            <span class="text-gray-500">{{ $isId ? 'Hari Sejak Penugasan Terakhir:' : 'Days since last assigned:' }}</span>
                                                            <span class="font-bold text-gray-800" x-text="reviewer.days_since_last"></span>
                                                        </div>
                                                        <div class="flex justify-between border-b border-gray-200 pb-1">
                                                            <span class="text-gray-500">{{ $isId ? 'Rata-rata Penyelesaian:' : 'Avg days to complete:' }}</span>
                                                            <span class="font-bold text-gray-800" x-text="reviewer.avg_completion_days"></span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </template>
                                </table>
                            </div>
                        </div>

                        {{-- No Results --}}
                        <div x-show="!selectedReviewerForAssign && !assignReviewerIsSearching && assignReviewerResults.length === 0"
                            class="text-center py-6 text-gray-500 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <i class="fa-solid fa-user-slash text-2xl mb-1 text-gray-300"></i>
                            <p class="text-xs" x-show="assignReviewerSearch.length > 0">
                                {{ $isId ? 'Tidak ada reviewer yang cocok dengan' : 'No reviewers found matching' }} "<span x-text="assignReviewerSearch"></span>"
                            </p>
                            <p class="text-xs" x-show="assignReviewerSearch.length === 0">
                                {{ $isId ? 'Ketik nama atau kata kunci untuk mencari reviewer.' : 'Type a name or keyword to search for reviewers.' }}
                            </p>
                        </div>

                        {{-- Selected Reviewer Display Card --}}
                        <template x-if="selectedReviewerForAssign">
                            <div class="bg-indigo-50/70 p-4 rounded-2xl border border-indigo-200 flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-full bg-indigo-600 text-white font-bold text-lg flex items-center justify-center shadow-xs">
                                        <span x-text="selectedReviewerForAssign.name.charAt(0).toUpperCase()"></span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-indigo-950" x-text="selectedReviewerForAssign.name"></h4>
                                        <p class="text-xs text-indigo-700" x-text="selectedReviewerForAssign.email"></p>
                                        <p class="text-xs text-indigo-600 italic" x-text="selectedReviewerForAssign.affiliation || 'No affiliation'"></p>
                                        <div class="flex gap-3 mt-1.5">
                                            <span class="inline-flex items-center text-[11px] font-semibold text-indigo-800">
                                                <i class="fa-solid fa-circle-check mr-1 text-indigo-500"></i>
                                                <span x-text="selectedReviewerForAssign.completed_count"></span> {{ $isId ? 'Selesai' : 'Completed' }}
                                            </span>
                                            <span class="inline-flex items-center text-[11px] font-semibold text-indigo-800">
                                                <i class="fa-solid fa-star mr-1 text-yellow-500"></i>
                                                <span x-text="selectedReviewerForAssign.avg_rating ? Number(selectedReviewerForAssign.avg_rating).toFixed(1) : '-'"></span> Rating
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" @click="selectedReviewerForAssign = null; assignReviewerSearch = ''"
                                    class="text-indigo-400 hover:text-indigo-700 p-2 rounded-lg hover:bg-indigo-100/50 transition-colors">
                                    <span class="sr-only">Remove</span>
                                    <i class="fa-solid fa-xmark text-lg"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Review Method Selection --}}
                    <div class="pt-4 border-t border-gray-100">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            {{ $isId ? 'Metode Ulasan' : 'Review Method' }}
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="relative flex cursor-pointer rounded-xl border bg-white p-3.5 shadow-2xs focus:outline-none transition-all"
                                :class="assignReviewMethod === 'open' ? 'border-indigo-600 ring-2 ring-indigo-600/20 bg-indigo-50/30' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="review_method" value="open" x-model="assignReviewMethod" class="sr-only">
                                <div class="flex flex-1 flex-col text-center">
                                    <i class="fa-solid fa-eye text-indigo-500 text-xl mb-1"></i>
                                    <span class="block text-xs font-bold text-gray-900">Open</span>
                                    <span class="mt-0.5 text-[11px] text-gray-500">{{ $isId ? 'Identitas terbuka' : 'Identity is visible' }}</span>
                                </div>
                            </label>

                            <label class="relative flex cursor-pointer rounded-xl border bg-white p-3.5 shadow-2xs focus:outline-none transition-all"
                                :class="assignReviewMethod === 'blind' ? 'border-indigo-600 ring-2 ring-indigo-600/20 bg-indigo-50/30' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="review_method" value="blind" x-model="assignReviewMethod" class="sr-only">
                                <div class="flex flex-1 flex-col text-center">
                                    <i class="fa-solid fa-user-secret text-indigo-500 text-xl mb-1"></i>
                                    <span class="block text-xs font-bold text-gray-900">Blind</span>
                                    <span class="mt-0.5 text-[11px] text-gray-500">{{ $isId ? 'Reviewer anonim' : 'Reviewer is anonymous' }}</span>
                                </div>
                            </label>

                            <label class="relative flex cursor-pointer rounded-xl border bg-white p-3.5 shadow-2xs focus:outline-none transition-all"
                                :class="assignReviewMethod === 'double_blind' ? 'border-indigo-600 ring-2 ring-indigo-600/20 bg-indigo-50/30' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="review_method" value="double_blind" x-model="assignReviewMethod" class="sr-only">
                                <div class="flex flex-1 flex-col text-center">
                                    <i class="fa-solid fa-eye-slash text-indigo-500 text-xl mb-1"></i>
                                    <span class="block text-xs font-bold text-gray-900">Double Blind</span>
                                    <span class="mt-0.5 text-[11px] text-gray-500">{{ $isId ? 'Keduanya anonim' : 'Both anonymous' }}</span>
                                </div>
                            </label>
                        </div>

                        {{-- Double Blind Warning --}}
                        <div x-show="assignReviewMethod === 'double_blind'" x-transition
                            class="mt-3 bg-amber-50 border-l-4 border-amber-400 p-3 rounded-r-xl text-xs text-amber-800 flex items-start space-x-2">
                            <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 flex-shrink-0"></i>
                            <span>
                                <strong>{{ $isId ? 'Peringatan:' : 'Warning:' }}</strong> {{ $isId ? 'Dalam mode Double Blind, pastikan file naskah telah dianonimkan dari identitas penulis sebelum dikirimkan ke reviewer.' : 'In Double-blind mode, please ensure that the manuscript file has been anonymized before sending it to the reviewer.' }}
                            </span>
                        </div>
                    </div>

                    {{-- Due Dates Section --}}
                    <div class="pt-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                {{ $isId ? 'Tanggal Batas Respons' : 'Response Due Date' }}
                            </label>
                            <p class="text-[11px] text-gray-500 mb-1.5">
                                {{ $isId ? 'Batas tanggal reviewer menerima/menolak penugasan.' : 'Date by which the reviewer must accept or decline.' }}
                            </p>
                            <input type="date" name="response_due_date" x-model="assignResponseDueDate"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-xs focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                {{ $isId ? 'Tanggal Batas Ulasan' : 'Review Due Date' }}
                            </label>
                            <p class="text-[11px] text-gray-500 mb-1.5">
                                {{ $isId ? 'Batas tanggal ulasan harus diselesaikan.' : 'Date by which the review must be completed.' }}
                            </p>
                            <input type="date" name="review_due_date" x-model="assignReviewDueDate"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-xs focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                        </div>
                    </div>

                    {{-- Footer actions --}}
                    <div class="pt-5 border-t border-gray-100 flex justify-end items-center gap-3">
                        <button type="button" @click="assignReviewerModalOpen = false"
                            class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                            {{ $isId ? 'Batal' : 'Cancel' }}
                        </button>
                        <button type="submit" :disabled="!selectedReviewerForAssign || assignReviewerSubmitting"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center">
                            <i x-show="!assignReviewerSubmitting" class="fa-solid fa-paper-plane mr-2"></i>
                            <i x-show="assignReviewerSubmitting" class="fa-solid fa-spinner fa-spin mr-2" style="display: none;"></i>
                            <span x-text="assignReviewerSubmitting ? '{{ $isId ? 'Menugaskan...' : 'Assigning...' }}' : '{{ $isId ? 'Tugaskan Reviewer' : 'Assign Reviewer' }}'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
