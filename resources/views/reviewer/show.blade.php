@php
    $journal = current_journal();
    $isId = app()->getLocale() === 'id';
    $status = $assignment->status; // pending, accepted, completed, declined, cancelled
@endphp

<x-app-layout>
    <x-slot name="title">{{ $isId ? 'Ulasan Naskah' : 'Review Submission' }}</x-slot>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

    <div class="max-w-7xl mx-auto py-8 px-5">
        <!-- Breadcrumb / Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('journal.reviewer.index', ['journal' => $journal->slug]) }}"
                    class="p-2.5 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <x-text.h1 class="text-slate-900">{{ $isId ? 'Ulasan Naskah' : 'Review Submission' }}</x-text.h1>
                    <x-text.caption class="mt-1 text-slate-400 block">
                        {{ $isId ? 'Putaran' : 'Round' }} {{ $assignment->round }} • 
                        {{ $isId ? 'Batas Waktu Ulasan' : 'Review Due' }}: 
                        <span class="font-medium text-slate-600">{{ $assignment->due_date?->translatedFormat($isId ? 'j M Y' : 'M j, Y') ?? ($isId ? 'Tanpa batas waktu' : 'No deadline') }}</span>
                    </x-text.caption>
                </div>
            </div>
            
            <!-- Quick Status Badge -->
            <div class="flex items-center gap-2">
                @if($status === 'pending')
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-yellow-50 text-yellow-700 border border-yellow-100">
                        <i class="fa-solid fa-hourglass-half mr-1.5"></i>{{ $isId ? 'Menunggu Tanggapan' : 'Pending Response' }}
                    </span>
                @elseif($status === 'accepted')
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                        <i class="fa-solid fa-spinner fa-spin mr-1.5"></i>{{ $isId ? 'Sedang Berjalan' : 'In Progress' }}
                    </span>
                @elseif($status === 'completed')
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                        <i class="fa-solid fa-circle-check mr-1.5"></i>{{ $isId ? 'Selesai' : 'Completed' }}
                    </span>
                @elseif($status === 'declined')
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-100">
                        <i class="fa-solid fa-circle-xmark mr-1.5"></i>{{ $isId ? 'Ditolak' : 'Declined' }}
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-slate-50 text-slate-700 border border-slate-100">
                        {{ ucfirst($status) }}
                    </span>
                @endif
            </div>
        </div>

        <!-- AlpineJS Tabs Wrapper -->
        <div x-data="{ 
            status: '{{ $status }}',
            activeStep: {{ $status === 'completed' ? 4 : ($status === 'accepted' ? 3 : 1) }},
            declineReason: '',
            showDeclineModal: false,
            
            isTabDisabled(step) {
                if (this.status === 'pending') {
                    return step > 1;
                }
                if (this.status === 'accepted') {
                    return step > 3;
                }
                return false; // completed or declined can click all
            },
            
            setStep(step) {
                if (!this.isTabDisabled(step)) {
                    this.activeStep = step;
                }
            }
        }" class="space-y-6">

            <!-- 4-Step Navigation Tab Bar -->
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-2">
                <nav class="flex flex-col md:flex-row gap-1" aria-label="Steps">
                    <!-- Step 1: Request -->
                    <button type="button" @click="setStep(1)"
                        :class="{ 
                            'bg-blue-50 text-blue-700 font-bold': activeStep === 1,
                            'text-slate-500 hover:text-slate-700 hover:bg-slate-50': activeStep !== 1,
                            'opacity-50 cursor-not-allowed': isTabDisabled(1)
                        }"
                        class="flex-1 inline-flex items-center justify-center py-3.5 px-4 rounded-2xl text-sm font-semibold transition-all">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center bg-current bg-opacity-10 mr-2 text-xs">1</span>
                        {{ $isId ? '1. Permintaan' : '1. Request' }}
                    </button>

                    <!-- Step 2: Guidelines -->
                    <button type="button" @click="setStep(2)"
                        :disabled="isTabDisabled(2)"
                        :class="{ 
                            'bg-blue-50 text-blue-700 font-bold': activeStep === 2,
                            'text-slate-500 hover:text-slate-700 hover:bg-slate-50': activeStep !== 2,
                            'opacity-50 cursor-not-allowed': isTabDisabled(2)
                        }"
                        class="flex-1 inline-flex items-center justify-center py-3.5 px-4 rounded-2xl text-sm font-semibold transition-all">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center bg-current bg-opacity-10 mr-2 text-xs">2</span>
                        {{ $isId ? '2. Panduan' : '2. Guidelines' }}
                    </button>

                    <!-- Step 3: Download & Review -->
                    <button type="button" @click="setStep(3)"
                        :disabled="isTabDisabled(3)"
                        :class="{ 
                            'bg-blue-50 text-blue-700 font-bold': activeStep === 3,
                            'text-slate-500 hover:text-slate-700 hover:bg-slate-50': activeStep !== 3,
                            'opacity-50 cursor-not-allowed': isTabDisabled(3)
                        }"
                        class="flex-1 inline-flex items-center justify-center py-3.5 px-4 rounded-2xl text-sm font-semibold transition-all">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center bg-current bg-opacity-10 mr-2 text-xs">3</span>
                        {{ $isId ? '3. Unduh & Ulas' : '3. Download & Review' }}
                    </button>

                    <!-- Step 4: Completion -->
                    <button type="button" @click="setStep(4)"
                        :disabled="isTabDisabled(4)"
                        :class="{ 
                            'bg-blue-50 text-blue-700 font-bold': activeStep === 4,
                            'text-slate-500 hover:text-slate-700 hover:bg-slate-50': activeStep !== 4,
                            'opacity-50 cursor-not-allowed': isTabDisabled(4)
                        }"
                        class="flex-1 inline-flex items-center justify-center py-3.5 px-4 rounded-2xl text-sm font-semibold transition-all">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center bg-current bg-opacity-10 mr-2 text-xs">4</span>
                        {{ $isId ? '4. Selesai' : '4. Completion' }}
                    </button>
                </nav>
            </div>

            <!-- Tab Panels -->
            <div class="space-y-6">

                <!-- STEP 1: REQUEST PANEL -->
                <div x-show="activeStep === 1" x-cloak class="grid lg:grid-cols-3 gap-6">
                    <!-- Left: Manuscript Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8">
                            <div class="flex items-center justify-between mb-5">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600">
                                    {{ $submission->section->name ?? ($isId ? 'Tanpa Kategori' : 'Uncategorized') }}
                                </span>
                                <x-text.caption>
                                    {{ $isId ? 'Dikirim' : 'Submitted' }} {{ $submission->submitted_at?->translatedFormat($isId ? 'j M Y' : 'M j, Y') }}
                                </x-text.caption>
                            </div>

                            <x-text.h2 class=" mb-5">{{ $submission->title }}</x-text.h2>

                            <!-- Meta Info Box -->
                            <div class="mb-6 bg-slate-50 rounded-[20px] p-5 grid grid-cols-2 gap-4">
                                <div>
                                    <x-text.label class="block mb-1">{{ $isId ? 'Bahasa' : 'Language' }}</x-text.label>
                                    <x-text.body class="text-slate-800 font-semibold">{{ $submission->metadata['language'] ?? 'English' }}</x-text.body>
                                </div>
                                <div>
                                    <x-text.label class="block mb-1">{{ $isId ? 'Tipe Naskah' : 'Submission Type' }}</x-text.label>
                                    <x-text.body class="text-slate-800 font-semibold">{{ $submission->section->name ?? ($isId ? 'Artikel' : 'Article') }}</x-text.body>
                                </div>
                                <div>
                                    <x-text.label class="block mb-1">{{ $isId ? 'ID Naskah' : 'Manuscript ID' }}</x-text.label>
                                    <x-text.body class="text-slate-800 font-semibold font-mono">{{ $submission->submission_code ?? 'N/A' }}</x-text.body>
                                </div>
                                <div>
                                    <x-text.label class="block mb-1">{{ $isId ? 'Hak Cipta' : 'Copyright' }}</x-text.label>
                                    <x-text.body class="text-slate-800 font-semibold">© {{ now()->year }} {{ $journal->name }}</x-text.body>
                                </div>
                            </div>

                            <!-- Abstract -->
                            <div class="mb-6">
                                <x-text.label class="block mb-2">{{ $isId ? 'Abstrak' : 'Abstract' }}</x-text.label>
                                <div class="prose prose-slate text-sm max-w-none text-slate-600 leading-relaxed">
                                    {!! clean($submission->abstract) !!}
                                </div>
                            </div>

                            <!-- Keywords -->
                            @if ($submission->keywords)
                                <div>
                                    <x-text.label class="block mb-2">{{ $isId ? 'Kata Kunci' : 'Keywords' }}</x-text.label>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($submission->keywords_array as $keyword)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                                {{ $keyword }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Accept / Decline Action Section (if pending) -->
                        @if($status === 'pending')
                            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8">
                                <x-text.h2 class=" mb-2">{{ $isId ? 'Tanggapan Undangan Ulasan' : 'Response to Review Invitation' }}</x-text.h2>
                                <x-text.body class="text-slate-500 mb-6 block">
                                    {{ $isId ? 'Silakan tentukan apakah Anda bersedia mengulas naskah ini. Anda dapat menerima undangan untuk masuk ke langkah berikutnya, atau menolaknya.' 
                                             : 'Please indicate whether you are willing to review this manuscript. You can accept the invitation to proceed to the next step, or decline it.' }}
                                </x-text.body>

                                <div class="flex flex-col sm:flex-row items-center gap-3">
                                    <form action="{{ route('journal.reviewer.accept', ['journal' => $journal->slug, 'assignment' => $assignment]) }}" method="POST" class="w-full sm:w-auto">
                                        @csrf
                                        <button type="submit"
                                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl transition-all shadow-md shadow-blue-100">
                                            <i class="fa-solid fa-check mr-2"></i>
                                            {{ $isId ? 'Terima Undangan, Lanjut ke Step 2' : 'Accept Invitation, Go to Step 2' }}
                                        </button>
                                    </form>
                                    
                                    <button type="button" @click="showDeclineModal = true"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold rounded-2xl transition-all border border-rose-100">
                                        <i class="fa-solid fa-xmark mr-2"></i>
                                        {{ $isId ? 'Tolak Undangan Ulasan' : 'Decline Review Invitation' }}
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="bg-emerald-50/60 border border-emerald-100 rounded-[24px] p-6 md:p-8 flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-circle-check text-lg"></i>
                                </div>
                                <div class="space-y-1">
                                    <x-text.h2 class="text-emerald-800 font-bold">{{ $isId ? 'Undangan Disetujui' : 'Invitation Accepted' }}</x-text.h2>
                                    <x-text.body class="text-emerald-700 block">
                                        {{ $isId ? 'Anda telah menyetujui untuk mengulas naskah ini. Silakan lanjutkan ke panduan pengulas.' 
                                                 : 'You have agreed to review this manuscript. Please proceed to the reviewer guidelines.' }}
                                    </x-text.body>
                                    <div class="pt-3">
                                        <button type="button" @click="setStep(2)"
                                            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all">
                                            {{ $isId ? 'Lanjut ke Step 2: Panduan' : 'Go to Step 2: Guidelines' }} <i class="fa-solid fa-arrow-right ml-1.5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Right: Schedule & Info -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6">
                            <x-text.h2 class=" mb-4 flex items-center">
                                <i class="fa-solid fa-calendar-days text-slate-400 mr-2"></i>
                                {{ $isId ? 'Jadwal Ulasan' : 'Review Schedule' }}
                            </x-text.h2>
                            
                            <div class="space-y-4">
                                <div class="p-3.5 bg-slate-50 rounded-2xl flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100/60 text-blue-600 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-envelope"></i>
                                    </div>
                                    <div>
                                        <x-text.caption class="block">{{ $isId ? 'Tanggal Penugasan' : 'Assigned Date' }}</x-text.caption>
                                        <x-text.body class="font-bold text-slate-800">{{ $assignment->assigned_at?->translatedFormat('M j, Y') ?? '-' }}</x-text.body>
                                    </div>
                                </div>

                                <div class="p-3.5 bg-slate-50 rounded-2xl flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100/60 text-amber-600 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-reply"></i>
                                    </div>
                                    <div>
                                        <x-text.caption class="block">{{ $isId ? 'Batas Waktu Respon' : 'Response Due Date' }}</x-text.caption>
                                        <x-text.body class="font-bold text-slate-800">{{ $assignment->response_due_date?->translatedFormat('M j, Y') ?? '-' }}</x-text.body>
                                    </div>
                                </div>

                                <div class="p-3.5 bg-slate-50 rounded-2xl flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-rose-100/60 text-rose-600 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-clock"></i>
                                    </div>
                                    <div>
                                        <x-text.caption class="block">{{ $isId ? 'Batas Waktu Ulasan' : 'Review Due Date' }}</x-text.caption>
                                        <x-text.body class="font-bold text-slate-800 {{ $assignment->isOverdue() ? 'text-rose-600' : '' }}">
                                            {{ $assignment->due_date?->translatedFormat('M j, Y') ?? '-' }}
                                        </x-text.body>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Need Help Box -->
                        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6">
                            <h4 class="font-bold text-slate-900 mb-2 flex items-center">
                                <i class="fa-regular fa-circle-question text-slate-400 mr-2"></i>
                                {{ $isId ? 'Butuh Bantuan?' : 'Need Help?' }}
                            </h4>
                            <x-text.caption class="text-slate-500 block leading-relaxed mb-4">
                                {{ $isId ? 'Hubungi tim redaksi jika Anda memiliki konflik kepentingan atau masalah teknis.' : 'Contact the editorial team if you have conflict of interest or technical issues.' }}
                            </x-text.caption>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: GUIDELINES PANEL -->
                <div x-show="activeStep === 2" x-cloak class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8 space-y-6">
                        <x-text.h2 class=" flex items-center pb-4 border-b border-slate-100">
                            <i class="fa-solid fa-scale-balanced mr-3 text-blue-600"></i>
                            {{ $isId ? 'Panduan Penilaian / Ulasan' : 'Reviewer Guidelines' }}
                        </x-text.h2>

                        <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed text-sm">
                            @if($journal->reviewer_guidelines)
                                {!! clean($journal->reviewer_guidelines) !!}
                            @else
                                <p class="text-slate-400 italic">{{ $isId ? 'Tidak ada panduan pengulas yang tersedia.' : 'No reviewer guidelines available.' }}</p>
                            @endif
                        </div>

                        <!-- Policy Checkbox and Navigation Button -->
                        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4" x-data="{ checked: false }">
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <input type="checkbox" x-model="checked" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-5 h-5 transition-all">
                                <x-text.body class="text-slate-500">{{ $isId ? 'Saya telah membaca dan memahami panduan ini.' : 'I have read and understood these guidelines.' }}</x-text.body>
                            </label>

                            <button type="button" @click="setStep(3)" :disabled="!checked"
                                :class="checked ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-100 cursor-pointer' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 font-bold text-sm rounded-2xl transition-all gap-2">
                                <span>{{ $isId ? 'Lanjut' : 'Next' }}</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: DOWNLOAD & REVIEW PANEL -->
                <div x-show="activeStep === 3" x-cloak class="space-y-6 max-w-none">
                    <!-- SECTION 1: Review Files (OJS Data Table dengan Fitur Search) -->
                    <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8 space-y-4"
                        x-data="{ search: '' }">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <x-text.h2 class="">{{ $isId ? 'File Ulasan' : 'Review Files' }}</x-text.h2>
                                <x-text.caption class="text-slate-400 block mt-0.5">
                                    {{ $isId ? 'Daftar naskah dan dokumen ulasan yang perlu ditinjau.' : 'List of manuscript files and documents to be reviewed.' }}
                                </x-text.caption>
                            </div>
                            
                            <!-- Search Bar (Fitur Search OJS Style) -->
                            <div class="relative w-full sm:w-64">
                                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" x-model="search" placeholder="{{ $isId ? 'Cari file...' : 'Search files...' }}"
                                    class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-all">
                            </div>
                        </div>

                        <!-- Data Table (OJS Model) -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-slate-50/70 font-bold uppercase tracking-wider text-slate-400 text-[11px]">
                                        <th class="py-4 px-4">{{ $isId ? 'Review Files' : 'Review Files' }}</th>
                                        <th class="py-4 px-4">{{ $isId ? 'Tanggal' : 'Date' }}</th>
                                        <th class="py-4 px-4">{{ $isId ? 'Type File' : 'File Type' }}</th>
                                        <th class="py-4 px-4 text-right">{{ $isId ? 'Aksi' : 'Action' }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    @if ($manuscriptFiles->isEmpty())
                                        <tr>
                                            <td colspan="4" class="py-6 text-center text-slate-400 italic">
                                                {{ $isId ? 'Tidak ada file ulasan tersedia.' : 'No review files available.' }}
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($manuscriptFiles as $file)
                                            <tr x-show="!search || '{{ strtolower(addslashes($file->file_name)) }}'.includes(search.toLowerCase())" class="hover:bg-slate-50/80 transition-colors">
                                                <td class="py-4 px-4">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
                                                            <i class="fa-solid fa-file-pdf text-sm"></i>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="font-bold text-slate-800 text-sm truncate max-w-md" title="{{ $file->file_name }}">
                                                                {{ $file->file_name }}
                                                            </p>
                                                            <p class="text-xs text-slate-400">
                                                                {{ $isId ? 'Versi' : 'Version' }} {{ $file->version }} • {{ $file->file_size_formatted }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-4 px-4 text-slate-500 whitespace-nowrap">
                                                    {{ $file->created_at?->translatedFormat('M j, Y') ?? $assignment->assigned_at?->translatedFormat('M j, Y') ?? '-' }}
                                                </td>
                                                <td class="py-4 px-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                        {{ $file->file_type_label ?? 'Manuscript' }}
                                                    </span>
                                                </td>
                                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                                    <a href="{{ route('files.download', $file) }}"
                                                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                                                        <i class="fa-solid fa-download mr-1.5"></i>
                                                        {{ $isId ? 'Unduh' : 'Download' }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 2: Reviewer Guidelines (Di Bawah Review Files) -->
                    <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <x-text.h2 class=" flex items-center">
                                <i class="fa-solid fa-scale-balanced mr-2.5 text-blue-600"></i>
                                {{ $isId ? 'Panduan Penilaian / Pengulas' : 'Reviewer Guidelines' }}
                            </x-text.h2>
                            <span class="text-xs text-slate-400 font-medium hidden sm:inline-block">
                                {{ $isId ? 'Harap baca panduan sebelum mengisi formulir' : 'Please review guidelines before completing form' }}
                            </span>
                        </div>

                        <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed text-sm">
                            @if($journal->reviewer_guidelines)
                                {!! clean($journal->reviewer_guidelines) !!}
                            @else
                                <p class="text-slate-400 italic">{{ $isId ? 'Tidak ada panduan pengulas yang tersedia.' : 'No reviewer guidelines available.' }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- SECTION 3: Active Review Form / Formulir Ulasan Anda -->
                    @if ($status !== 'completed')
                        <form id="reviewerSubmitForm" action="{{ route('journal.reviewer.submit', ['journal' => $journal->slug, 'assignment' => $assignment]) }}"
                            method="POST" x-data="{ recommendation: '{{ old('recommendation') }}' }">
                            @csrf

                            <!-- 1. bagian review form (Review Form / Comments) -->
                            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8 space-y-6">
                                <div class="border-b border-slate-100 pb-4 space-y-2">
                                    <div>
                                        <x-text.h2 class="">{{ $isId ? 'Formulir Ulasan Anda' : 'Your Review' }}</x-text.h2>
                                        @if($reviewForm)
                                            <p class="text-xs text-blue-600 font-bold mt-1 flex items-center gap-1.5">
                                                <i class="fa-solid fa-clipboard-list"></i>
                                                {{ $reviewForm->title }}
                                            </p>
                                        @endif
                                    </div>
                                    @if($reviewForm && $reviewForm->description)
                                        <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl text-xs text-slate-500 leading-relaxed block w-full">
                                            {{ $reviewForm->description }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Active Review Form Elements (Structured Questions) -->
                                @if($reviewForm && $reviewForm->elements->isNotEmpty())
                                    <div class="space-y-6 pb-6 border-b border-slate-100">
                                        @foreach($reviewForm->elements as $index => $element)
                                            <div class="p-5 bg-slate-50/70 border border-slate-100 rounded-2xl space-y-3">
                                                <div class="flex items-start gap-3">
                                                    <span class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-xl bg-blue-600 text-white text-xs font-bold shadow-sm shadow-blue-200">
                                                        {{ $index + 1 }}
                                                    </span>
                                                    <div class="flex-1">
                                                        <label class="text-sm font-bold text-slate-900 block leading-snug">
                                                            {{ $element->question }}
                                                            @if($element->required)
                                                                <span class="text-rose-500">*</span>
                                                            @endif
                                                        </label>
                                                        @if($element->description)
                                                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $element->description }}</p>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="pl-10">
                                                    @php
                                                        $existingValue = $existingResponses->get($element->id)?->response_value;
                                                        $fieldName = "responses[{$element->id}]";
                                                    @endphp

                                                    @switch($element->element_type->value)
                                                        @case('text')
                                                            <input type="text" name="{{ $fieldName }}" 
                                                                value="{{ old($fieldName, $existingValue) }}"
                                                                {{ $element->required ? 'required' : '' }}
                                                                class="w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm bg-white"
                                                                placeholder="{{ $isId ? 'Masukkan jawaban Anda...' : 'Enter your answer...' }}">
                                                            @break

                                                        @case('textarea')
                                                            <textarea name="{{ $fieldName }}" rows="4"
                                                                {{ $element->required ? 'required' : '' }}
                                                                class="w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm bg-white"
                                                                placeholder="{{ $isId ? 'Masukkan jawaban Anda...' : 'Enter your answer...' }}">{{ old($fieldName, $existingValue) }}</textarea>
                                                            @break

                                                        @case('checkbox')
                                                            @php
                                                                $selectedValues = old($fieldName, json_decode($existingValue, true) ?? []);
                                                            @endphp
                                                            @if($element->options)
                                                                <div class="space-y-2">
                                                                    @foreach($element->options as $option)
                                                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-white hover:border-blue-400 hover:bg-blue-50/20 cursor-pointer transition-all">
                                                                            <input type="checkbox" name="{{ $fieldName }}[]" value="{{ $option['value'] }}"
                                                                                {{ in_array($option['value'], (array)$selectedValues) ? 'checked' : '' }}
                                                                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                                                                            <span class="text-sm font-medium text-slate-700">{{ $option['label'] }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                            @break

                                                        @case('radio')
                                                            @if($element->options)
                                                                <div class="space-y-2">
                                                                    @foreach($element->options as $option)
                                                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-white hover:border-blue-400 hover:bg-blue-50/20 cursor-pointer transition-all">
                                                                            <input type="radio" name="{{ $fieldName }}" value="{{ $option['value'] }}"
                                                                                {{ old($fieldName, $existingValue) == $option['value'] ? 'checked' : '' }}
                                                                                {{ $element->required ? 'required' : '' }}
                                                                                class="border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                                                                            <span class="text-sm font-medium text-slate-700">{{ $option['label'] }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                            @break

                                                        @case('select')
                                                            <select name="{{ $fieldName }}" {{ $element->required ? 'required' : '' }}
                                                                class="w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm bg-white">
                                                                <option value="">{{ $isId ? '-- Pilih Jawaban --' : '-- Select Answer --' }}</option>
                                                                @if($element->options)
                                                                    @foreach($element->options as $option)
                                                                        <option value="{{ $option['value'] }}" 
                                                                            {{ old($fieldName, $existingValue) == $option['value'] ? 'selected' : '' }}>
                                                                            {{ $option['label'] }}
                                                                        </option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                            @break

                                                        @case('rating')
                                                            @php
                                                                $config = $element->getRatingConfig();
                                                                $selectedRating = old($fieldName, $existingValue);
                                                            @endphp
                                                            <div class="flex items-center gap-2" x-data="{ rating: {{ $selectedRating ?? 0 }} }">
                                                                @for($i = $config['min']; $i <= $config['max']; $i++)
                                                                    <button type="button" @click="rating = {{ $i }}"
                                                                        class="text-2xl transition-colors"
                                                                        :class="rating >= {{ $i }} ? 'text-amber-400' : 'text-slate-300'">
                                                                        <i class="fa-solid fa-star"></i>
                                                                    </button>
                                                                @endfor
                                                                <input type="hidden" name="{{ $fieldName }}" :value="rating" {{ $element->required ? 'required' : '' }}>
                                                                <span class="text-xs font-bold text-slate-500 ml-2" x-text="rating > 0 ? rating + '/{{ $config['max'] }}' : '{{ $isId ? 'Belum dinilai' : 'Not rated' }}'"></span>
                                                            </div>
                                                            @break
                                                    @endswitch

                                                    @error($fieldName)
                                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Comments for Author -->
                                <div>
                                    <label for="comments_for_author" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                                        {{ $isId ? 'Komentar untuk Penulis' : 'Comments for Author' }} <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea name="comments_for_author" id="comments_for_author" rows="6" placeholder="{{ $isId ? 'Berikan umpan balik terperinci...' : 'Provide detailed feedback...' }}"
                                        class="w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('comments_for_author') }}</textarea>
                                    @error('comments_for_author')
                                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                    <x-text.caption class="mt-1.5 block text-slate-400">
                                        {{ $isId ? 'Komentar ini akan dapat dilihat oleh penulis naskah.' : 'These comments will be visible to the submission author.' }}
                                    </x-text.caption>
                                </div>

                                <!-- Comments for Editor (Confidential) -->
                                <div>
                                    <label for="comments_for_editor" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                                        {{ $isId ? 'Komentar Rahasia untuk Editor' : 'Confidential Comments for Editor' }}
                                    </label>
                                    <textarea name="comments_for_editor" id="comments_for_editor" rows="4"
                                        placeholder="{{ $isId ? 'Opsional: Bagikan pengamatan rahasia apa pun...' : 'Optional: Share any confidential observations...' }}"
                                        class="w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('comments_for_editor') }}</textarea>
                                    <x-text.caption class="mt-1.5 text-slate-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $isId ? 'Komentar ini rahasia dan hanya akan dapat dilihat oleh tim editor.' : 'These comments are confidential and will only be visible to the editorial team.' }}
                                    </x-text.caption>
                                </div>
                            </div>

                            <!-- 2. Upload file hasil review (Reviewer Attachments Uploader) -->
                            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8 mt-6" x-data="reviewerAttachments()">
                                <x-text.h2 class=" mb-2">{{ $isId ? 'Unggah File Hasil Review' : 'Upload' }}</x-text.h2>
                                <p class="text-xs text-slate-400 mb-4 leading-relaxed">
                                    {{ $isId ? 'Unggah file yang ingin Anda konsultasikan dengan editor dan/atau penulis, termasuk versi revisi dari file ulasan asli.' 
                                             : 'Upload files you would like the editor and/or author to consult, including revised versions of the original review file(s).' }}
                                </p>
                                
                                <div class="border-2 border-dashed border-slate-200 rounded-[20px] p-6 text-center hover:bg-slate-50 transition-colors"
                                    @dragover.prevent="$el.classList.add('border-blue-500', 'bg-blue-50/50')"
                                    @dragleave.prevent="$el.classList.remove('border-blue-500', 'bg-blue-50/50')"
                                    @drop.prevent="handleDrop($event); $el.classList.remove('border-blue-500', 'bg-blue-50/50')">
                                    
                                    <input type="file" id="attachmentInput" class="hidden" @change="handleFileSelect($event)" accept=".doc,.docx,.pdf,.rtf">
                                    
                                    <label for="attachmentInput" class="cursor-pointer block">
                                        <div class="text-slate-400">
                                            <i class="fa-solid fa-cloud-arrow-up text-3xl mb-2 text-blue-500"></i>
                                            <p class="font-bold text-slate-800 text-sm">{{ $isId ? 'Klik untuk mengunggah atau seret file ke sini' : 'Click to upload or drag and drop' }}</p>
                                            <p class="text-[11px] mt-1">DOC, DOCX, PDF, RTF (Max 10MB)</p>
                                        </div>
                                    </label>

                                    <!-- Upload Progress -->
                                    <div x-show="isUploading" class="mt-4" style="display: none;">
                                        <div class="h-1 w-full bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-blue-600 transition-all duration-300" :style="`width: ${uploadProgress}%`"></div>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-1.5" x-text="isId ? `Mengunggah... ${uploadProgress}%` : `Uploading... ${uploadProgress}%`"></p>
                                    </div>
                                </div>

                                <!-- Uploaded Files List -->
                                <div class="mt-4 space-y-2" x-show="files.length > 0" style="display: none;">
                                    <x-text.label class="block mb-2">{{ $isId ? 'File Terunggah' : 'Uploaded Files' }}</x-text.label>
                                    <template x-for="file in files" :key="file.id">
                                        <div class="flex items-center justify-between p-3.5 bg-slate-50 border border-slate-100 rounded-xl">
                                            <div class="flex items-center space-x-3 min-w-0">
                                                <i class="fa-solid fa-file-arrow-up text-slate-400 text-base"></i>
                                                <div class="min-w-0">
                                                    <p class="text-xs font-bold text-slate-800 truncate" x-text="file.name"></p>
                                                    <p class="text-[10px] text-slate-400" x-text="file.size"></p>
                                                </div>
                                            </div>
                                            <button type="button" @click="deleteFile(file.id)" class="text-rose-500 hover:text-rose-700 p-1" :title="isId ? 'Hapus File' : 'Delete File'">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </form>
                    @else
                        <!-- Completed Review (Form Review State Locked - Read-Only) -->
                        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8 space-y-6">
                            
                            <!-- Header & Locked Status Badge -->
                            <div class="border-b border-slate-100 pb-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <x-text.h2 class="">{{ $isId ? 'Formulir Ulasan Anda (Terkunci)' : 'Summary of Your Review' }}</x-text.h2>
                                        @if($reviewForm)
                                            <p class="text-xs text-blue-600 font-bold mt-1 flex items-center gap-1.5">
                                                <i class="fa-solid fa-clipboard-list"></i>
                                                {{ $reviewForm->title }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 gap-1.5">
                                        <i class="fa-solid fa-lock text-slate-500"></i>
                                        {{ $isId ? 'Terkunci / Selesai' : 'Locked / Completed' }}
                                    </span>
                                </div>

                                @if($reviewForm && $reviewForm->description)
                                    <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl text-xs text-slate-500 leading-relaxed block w-full">
                                        {{ $reviewForm->description }}
                                    </div>
                                @endif
                            </div>

                            <!-- Recommendation Badge -->
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100/80 flex items-center justify-between">
                                <div>
                                    <x-text.label class="block mb-1">{{ $isId ? 'Rekomendasi Ulasan Anda' : 'Your Recommendation' }}</x-text.label>
                                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-bold bg-{{ $assignment->recommendation_color === 'green' ? 'emerald' : ($assignment->recommendation_color === 'red' ? 'rose' : $assignment->recommendation_color) }}-50 text-{{ $assignment->recommendation_color === 'green' ? 'emerald' : ($assignment->recommendation_color === 'red' ? 'rose' : $assignment->recommendation_color) }}-700 border border-{{ $assignment->recommendation_color === 'green' ? 'emerald' : ($assignment->recommendation_color === 'red' ? 'rose' : $assignment->recommendation_color) }}-100">
                                        <i class="fa-solid fa-circle-nodes mr-1.5"></i>
                                        {{ $assignment->recommendation_label }}
                                    </span>
                                </div>
                                <span class="text-xs text-slate-400">
                                    {{ $assignment->date_completed?->translatedFormat('d M Y H:i') ?? '' }}
                                </span>
                            </div>

                            <!-- Active Review Form Questions (State Locked / Read-Only Elements) -->
                            @php
                                $cleanHtml = function($val) {
                                    if (empty($val)) return '';
                                    $str = html_entity_decode((string)$val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    $str = strip_tags($str);
                                    return trim($str);
                                };
                            @endphp

                            @if($reviewForm && $reviewForm->elements->isNotEmpty())
                                <div class="space-y-6 pb-6 border-b border-slate-100">
                                    <x-text.label class="block text-slate-400 font-bold uppercase tracking-wider text-[11px] mb-3">
                                        {{ $isId ? 'Hasil Formulir Evaluasi' : 'Evaluation Form Answers' }}
                                    </x-text.label>

                                    @foreach($reviewForm->elements as $index => $element)
                                        @php
                                            $resp = $existingResponses->first(function($r) use ($element) {
                                                return (string)$r->review_form_element_id === (string)$element->id;
                                            });
                                            $existingValue = $resp ? $resp->response_value : $existingResponses->get($element->id)?->response_value;
                                        @endphp
                                        <div class="p-5 bg-slate-50/70 border border-slate-100 rounded-2xl space-y-3">
                                            <div class="flex items-start gap-3">
                                                <span class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-xl bg-slate-700 text-white text-xs font-bold shadow-sm">
                                                    {{ $index + 1 }}
                                                </span>
                                                <div class="flex-1">
                                                    <label class="text-sm font-bold text-slate-900 block leading-snug">
                                                        {{ $element->question }}
                                                        @if($element->required)
                                                            <span class="text-rose-500">*</span>
                                                        @endif
                                                    </label>
                                                    @if($element->description)
                                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $element->description }}</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="pl-10">
                                                @switch($element->element_type->value)
                                                    @case('text')
                                                        <input type="text" disabled readonly 
                                                            value="{{ $cleanHtml($existingValue) }}"
                                                            placeholder="{{ $isId ? '(Tidak ada jawaban)' : '(No response provided)' }}"
                                                            class="w-full rounded-xl border-slate-200 shadow-sm text-sm bg-slate-100/80 text-slate-800 font-medium cursor-not-allowed">
                                                        @break

                                                    @case('textarea')
                                                        <textarea disabled readonly rows="4"
                                                            placeholder="{{ $isId ? '(Tidak ada jawaban)' : '(No response provided)' }}"
                                                            class="w-full rounded-xl border-slate-200 shadow-sm text-sm bg-slate-100/80 text-slate-800 font-medium cursor-not-allowed leading-relaxed">{{ $cleanHtml($existingValue) }}</textarea>
                                                        @break

                                                    @case('checkbox')
                                                        @php
                                                            $selectedValues = json_decode($existingValue, true);
                                                            if (!is_array($selectedValues)) {
                                                                $selectedValues = is_null($existingValue) || $existingValue === '' ? [] : [$existingValue];
                                                            }
                                                        @endphp
                                                        @if($element->options)
                                                            <div class="space-y-2">
                                                                @foreach($element->options as $option)
                                                                    @php
                                                                        $isSelected = false;
                                                                        if (!empty($selectedValues)) {
                                                                            foreach ((array)$selectedValues as $sv) {
                                                                                $svStr = strtolower(trim((string)$sv));
                                                                                $optValStr = strtolower(trim((string)($option['value'] ?? '')));
                                                                                $optLabelStr = strtolower(trim((string)($option['label'] ?? '')));
                                                                                if ($svStr !== '' && ($svStr === $optValStr || $svStr === $optLabelStr)) {
                                                                                    $isSelected = true;
                                                                                    break;
                                                                                }
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <div class="flex items-center gap-3 p-3.5 rounded-xl border transition-all {{ $isSelected ? 'border-blue-500 bg-blue-50/70 text-slate-900 shadow-xs ring-1 ring-blue-500/20' : 'border-slate-200 bg-slate-50/50 text-slate-400' }}">
                                                                        <input type="checkbox" disabled {{ $isSelected ? 'checked' : '' }}
                                                                            class="rounded border-slate-300 text-blue-600 w-4 h-4 cursor-not-allowed {{ $isSelected ? 'accent-blue-600' : 'opacity-40' }}">
                                                                        <span class="text-sm leading-snug {{ $isSelected ? 'text-slate-900 font-semibold' : 'text-slate-400 font-normal' }}">{{ $option['label'] }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @break

                                                    @case('radio')
                                                        @if($element->options)
                                                            <div class="space-y-2">
                                                                @foreach($element->options as $option)
                                                                    @php
                                                                        $exStr = strtolower(trim((string)$existingValue));
                                                                        $optValStr = strtolower(trim((string)($option['value'] ?? '')));
                                                                        $optLabelStr = strtolower(trim((string)($option['label'] ?? '')));
                                                                        $isSelected = !is_null($existingValue) && $existingValue !== '' && $exStr !== '' && (
                                                                            $exStr === $optValStr || 
                                                                            $exStr === $optLabelStr
                                                                        );
                                                                    @endphp
                                                                    <div class="flex items-center gap-3 p-3.5 rounded-xl border transition-all {{ $isSelected ? 'border-blue-500 bg-blue-50/70 text-slate-900 shadow-xs ring-1 ring-blue-500/20' : 'border-slate-200 bg-slate-50/50 text-slate-400' }}">
                                                                        <input type="radio" disabled {{ $isSelected ? 'checked' : '' }}
                                                                            class="border-slate-300 text-blue-600 w-4 h-4 cursor-not-allowed {{ $isSelected ? 'accent-blue-600' : 'opacity-40' }}">
                                                                        <span class="text-sm leading-snug {{ $isSelected ? 'text-slate-900 font-semibold' : 'text-slate-400 font-normal' }}">{{ $option['label'] }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @break

                                                    @case('select')
                                                        @php
                                                            $selectedLabel = '-';
                                                            if($element->options) {
                                                                foreach($element->options as $opt) {
                                                                    $exStr = strtolower(trim((string)$existingValue));
                                                                    $optValStr = strtolower(trim((string)($opt['value'] ?? '')));
                                                                    $optLabelStr = strtolower(trim((string)($opt['label'] ?? '')));
                                                                    if($exStr !== '' && ($exStr === $optValStr || $exStr === $optLabelStr)) {
                                                                        $selectedLabel = $opt['label'];
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        <div class="p-3.5 bg-blue-50/60 border border-blue-200 rounded-xl text-sm font-semibold text-slate-900 flex items-center justify-between">
                                                            <span>{{ $selectedLabel }}</span>
                                                            @if($selectedLabel !== '-')
                                                                <span class="text-xs text-blue-700 bg-blue-100 px-2.5 py-1 rounded-full font-bold flex items-center gap-1">
                                                                    <i class="fa-solid fa-check text-[11px]"></i> {{ $isId ? 'Dipilih' : 'Selected' }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        @break

                                                    @case('rating')
                                                        @php
                                                            $config = $element->getRatingConfig();
                                                            preg_match('/\d+/', (string)$existingValue, $matches);
                                                            $selectedRating = isset($matches[0]) ? (int)$matches[0] : (int)$existingValue;
                                                        @endphp
                                                        <div class="flex items-center gap-2 p-3 bg-slate-100/80 border border-slate-200 rounded-xl">
                                                            @for($i = $config['min']; $i <= $config['max']; $i++)
                                                                <i class="fa-solid fa-star text-xl {{ $selectedRating >= $i ? 'text-amber-400' : 'text-slate-300' }}"></i>
                                                            @endfor
                                                            <span class="text-xs font-bold text-slate-700 ml-2">
                                                                {{ $selectedRating > 0 ? $selectedRating . '/' . $config['max'] : '-' }}
                                                            </span>
                                                        </div>
                                                        @break
                                                @endswitch
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Comments for Author (State Locked) -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                                    {{ $isId ? 'Komentar untuk Penulis' : 'Comments for Author' }}
                                </label>
                                <textarea disabled readonly rows="6"
                                    class="w-full rounded-xl border-slate-200 shadow-sm text-sm bg-slate-100/80 text-slate-800 font-medium cursor-not-allowed leading-relaxed">{{ $cleanHtml($assignment->comments_for_author) }}</textarea>
                            </div>

                            <!-- Comments for Editor (State Locked) -->
                            @if ($assignment->comments_for_editor)
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                                        {{ $isId ? 'Komentar Rahasia untuk Editor' : 'Confidential Comments for Editor' }}
                                    </label>
                                    <textarea disabled readonly rows="4"
                                        class="w-full rounded-xl border-amber-200/80 shadow-sm text-sm bg-amber-50/50 text-amber-900 font-medium cursor-not-allowed leading-relaxed">{{ $cleanHtml($assignment->comments_for_editor) }}</textarea>
                                </div>
                            @endif

                            <!-- Reviewer Attachments -->
                            @if ($reviewerAttachments->isNotEmpty())
                                <div>
                                    <x-text.label class="block mb-2.5">{{ $isId ? 'Lampiran Ulasan Anda' : 'Your Attachments' }}</x-text.label>
                                    <div class="space-y-2">
                                        @foreach($reviewerAttachments as $file)
                                            <div class="flex items-center justify-between p-3.5 bg-slate-50 border border-slate-100 rounded-xl">
                                                <div class="flex items-center space-x-3">
                                                    <i class="fa-solid fa-paperclip text-slate-400 text-base"></i>
                                                    <div>
                                                        <p class="text-xs font-bold text-slate-800">{{ $file->file_name }}</p>
                                                        <p class="text-[10px] text-slate-400">{{ $file->file_size_formatted }}</p>
                                                    </div>
                                                </div>
                                                <a href="{{ route('files.download', ['file' => $file->id]) }}" 
                                                    class="text-blue-600 hover:text-blue-800 text-xs font-bold flex items-center">
                                                    <i class="fa-solid fa-download mr-1"></i> {{ $isId ? 'Unduh' : 'Download' }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- SECTION 4: Review Discussions (Active Review State Only) -->
                    @if ($status !== 'completed')
                        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8">
                            <x-discussion-panel :submission="$submission" :stageId="2" stageName="Review" :discussions="$submission->discussions"
                                :participants="$participants" :journal="$journal" />
                        </div>
                    @endif

                    <!-- SECTION 5: Keputusan Review (Recommendation) -->
                    @if ($status !== 'completed')
                        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8" x-data="{ recommendation: '{{ old('recommendation') }}' }">
                            <x-text.h2 class=" mb-2">{{ $isId ? 'Keputusan Review' : 'Recommendation' }}</x-text.h2>
                            <p class="text-xs text-slate-400 mb-6 leading-relaxed">
                                {{ $isId ? 'Pilih rekomendasi dan kirimkan ulasan untuk menyelesaikan proses. Anda harus memasukkan ulasan atau mengunggah file sebelum memilih rekomendasi.' 
                                         : 'Select a recommendation and submit the review to complete the process. You must enter a review or upload a file before selecting a recommendation.' }}
                            </p>

                            <!-- Recommendation Select Dropdown -->
                            <div class="mb-6">
                                <label for="recommendation" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                                    {{ $isId ? 'Rekomendasi' : 'Recommendation' }} <span class="text-rose-500">*</span>
                                </label>
                                <select form="reviewerSubmitForm" name="recommendation" id="recommendation" x-model="recommendation" required
                                    class="w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <option value="">{{ $isId ? 'Pilih rekomendasi Anda...' : 'Select your recommendation...' }}</option>
                                    <option value="accept">{{ $isId ? 'Terima - Siap untuk publikasi' : 'Accept - Ready for publication' }}</option>
                                    <option value="minor_revision">{{ $isId ? 'Revisi Minor - Terima dengan perubahan kecil' : 'Minor Revision - Accept with minor changes' }}</option>
                                    <option value="major_revision">{{ $isId ? 'Revisi Mayor - Perubahan signifikan diperlukan' : 'Major Revision - Significant changes required' }}</option>
                                    <option value="resubmit">{{ $isId ? 'Kirim Ulang untuk Ulasan - Butuh pengerjaan ulang yang substansial' : 'Resubmit for Review - Needs substantial rework' }}</option>
                                    <option value="reject">{{ $isId ? 'Tolak - Tidak cocok untuk publikasi' : 'Reject - Not suitable for publication' }}</option>
                                </select>
                                @error('recommendation')
                                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Submit & Action Buttons -->
                            <div class="flex items-center justify-end space-x-4 pt-5 border-t border-slate-100">
                                <a href="{{ route('journal.reviewer.index', ['journal' => $journal->slug]) }}"
                                    class="text-sm font-bold text-slate-500 hover:text-slate-700 transition-all">
                                    {{ $isId ? 'Simpan sebagai Draf' : 'Save as Draft' }}
                                </a>
                                <button type="submit" form="reviewerSubmitForm"
                                    class="inline-flex items-center px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl transition-all shadow-md shadow-blue-100">
                                    <i class="fa-solid fa-paper-plane mr-2"></i>
                                    {{ $isId ? 'Kirim Ulasan' : 'Submit Review' }}
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- STEP 4: COMPLETION PANEL -->
                <div x-show="activeStep === 4" x-cloak class="space-y-6 w-full">
                    <!-- Top Section: 2 Columns Grid (Kolom Kiri: Apresiasi, Kolom Kanan: Summary) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                        
                        <!-- 1. Kolom Kiri: Header Ucapan Apresiasi -->
                        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8 text-center flex flex-col items-center justify-center space-y-4 h-full border border-slate-100/60">
                            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center border border-emerald-100 shadow-inner">
                                <i class="fa-solid fa-circle-check text-3xl"></i>
                            </div>

                            <div class="space-y-2">
                                <x-text.h1 class="text-slate-900 text-xl md:text-2xl">{{ $isId ? 'Ulasan Berhasil Dikirim!' : 'Review Submitted Successfully!' }}</x-text.h1>
                                <x-text.body class="text-slate-500 text-xs md:text-sm max-w-sm mx-auto block leading-relaxed">
                                    {{ $isId ? 'Terima kasih banyak atas kontribusi dan dedikasi Anda dalam meninjau naskah ini. Ulasan Anda sangat berharga bagi tim redaksi dan penulis.' 
                                             : 'Thank you very much for your contribution and dedication in reviewing this manuscript. Your review is invaluable to the editorial team and the author.' }}
                                </x-text.body>
                            </div>
                        </div>

                        <!-- 2. Kolom Kanan: Box Ringkasan Informasi (Review Summary) -->
                        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8 flex flex-col justify-between space-y-4 h-full border border-slate-100/60">
                            <div>
                                <div class="flex items-center space-x-2 mb-3 pb-3 border-b border-slate-100">
                                    <i class="fa-solid fa-clipboard-check text-blue-600 text-sm"></i>
                                    <x-text.h2 class=" text-sm">{{ $isId ? 'Ringkasan Informasi' : 'Review Summary' }}</x-text.h2>
                                </div>
                                <div class="space-y-2.5 text-xs text-slate-700">
                                    <div>
                                        <span class="text-slate-400 block text-[11px] uppercase font-semibold tracking-wider mb-0.5">{{ $isId ? 'Judul Naskah' : 'Manuscript Title' }}</span>
                                        <p class="font-bold text-slate-800 leading-snug">{{ $submission->title }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-50">
                                        <div>
                                            <span class="text-slate-400 block text-[11px] uppercase font-semibold tracking-wider mb-0.5">{{ $isId ? 'Metode Ulasan' : 'Review Method' }}</span>
                                            <p class="font-semibold text-slate-800">{{ ucfirst(str_replace('_', ' ', $assignment->review_method)) }}</p>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[11px] uppercase font-semibold tracking-wider mb-0.5">{{ $isId ? 'Rekomendasi Anda' : 'Your Recommendation' }}</span>
                                            <span class="font-bold text-emerald-600 inline-block bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100/80">
                                                {{ $assignment->recommendation_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Section: Review Discussions Panel (Full Width) -->
                    <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8 space-y-6">
                        <x-discussion-panel :submission="$submission" :stageId="2" stageName="Review" :discussions="$submission->discussions"
                            :participants="$participants" :journal="$journal" />

                        <!-- Tombol Aksi Navigasi (At bottom) -->
                        <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-center gap-3">
                            <a href="{{ route('journal.reviewer.index', ['journal' => $journal->slug]) }}"
                                class="inline-flex items-center justify-center px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl transition-all shadow-md shadow-blue-100 text-sm">
                                <i class="fa-solid fa-list mr-2"></i>
                                {{ $isId ? 'Kembali ke Ulasan Saya' : 'Back to My Reviews' }}
                            </a>
                            
                            <button type="button" @click="setStep(3)"
                                class="inline-flex items-center justify-center px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl transition-all text-sm">
                                <i class="fa-solid fa-file-invoice mr-2"></i>
                                {{ $isId ? 'Lihat Lembar Ulasan' : 'View Submitted Review' }}
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Decline Review Modal (Pending State Only) -->
            <div x-show="showDeclineModal" x-cloak
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                
                <div @click.away="showDeclineModal = false"
                    class="bg-white w-full max-w-lg rounded-[24px] shadow-2xl p-6 md:p-8 animate-in fade-in zoom-in-95 duration-200 space-y-6">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <x-text.h2 class="">{{ $isId ? 'Tolak Undangan Ulasan' : 'Decline Review Request' }}</x-text.h2>
                        <button type="button" @click="showDeclineModal = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form action="{{ route('journal.reviewer.decline', ['journal' => $journal->slug, 'assignment' => $assignment]) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="reason" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                                {{ $isId ? 'Alasan Penolakan (Opsional)' : 'Reason for Decline (Optional)' }}
                            </label>
                            <textarea name="reason" id="reason" rows="4" x-model="declineReason"
                                placeholder="{{ $isId ? 'Berikan alasan mengapa Anda tidak dapat mengulas naskah ini...' : 'Provide a reason why you cannot review this manuscript...' }}"
                                class="w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                        </div>

                        <div class="flex items-center justify-end space-x-3 pt-4">
                            <button type="button" @click="showDeclineModal = false"
                                class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-all text-xs">
                                {{ $isId ? 'Batal' : 'Cancel' }}
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition-all shadow-md shadow-rose-100 text-xs">
                                {{ $isId ? 'Tolak Undangan' : 'Decline Request' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('assets/js/vendors/plugins/tinymce/tinymce.min.js') }}"></script>
        <script>
            tinymce.init({
                selector: '#comments_for_editor, #comments_for_author',
                height: 350,
                menubar: false,
                plugins: 'lists link image table code autoresize',
                toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | table link image | code',
                branding: false,
                license_key: 'gpl',
                images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', '{{ route('journal.profile.upload.image', $journal->slug) }}');
                    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                    xhr.upload.onprogress = (e) => {
                        progress(e.loaded / e.total * 100);
                    };

                    xhr.onload = () => {
                        if (xhr.status === 403) {
                            reject({
                                message: 'HTTP Error: ' + xhr.status,
                                remove: true
                            });
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

                    xhr.onerror = () => {
                        reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
                    };

                    const formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());

                    xhr.send(formData);
                })
            });
        </script>
        
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('reviewerAttachments', () => ({
                    files: @json($reviewerAttachments->map(function($f) {
                        return [
                            'id' => $f->id,
                            'name' => $f->file_name,
                            'size' => $f->file_size_formatted
                        ];
                    })),
                    isUploading: false,
                    uploadProgress: 0,

                    handleDrop(e) {
                        if (e.dataTransfer.files.length > 0) {
                            this.uploadFile(e.dataTransfer.files[0]);
                        }
                    },

                    handleFileSelect(e) {
                        if (e.target.files.length > 0) {
                            this.uploadFile(e.target.files[0]);
                        }
                    },

                    uploadFile(file) {
                        const allowedTypes = ['.doc', '.docx', '.pdf', '.rtf'];
                        const extension = '.' + file.name.split('.').pop().toLowerCase();
                        
                        if (!allowedTypes.includes(extension)) {
                            Swal.fire({
                                icon: 'error',
                                title: "{{ $isId ? 'Tipe File Tidak Valid' : 'Invalid File Type' }}",
                                text: "{{ $isId ? 'Hanya file DOC, DOCX, PDF, dan RTF yang diizinkan.' : 'Only DOC, DOCX, PDF, and RTF files are allowed.' }}",
                                confirmButtonColor: '#2563eb'
                            });
                            return;
                        }

                        if (file.size > 10 * 1024 * 1024) {
                            Swal.fire({
                                icon: 'error',
                                title: "{{ $isId ? 'Ukuran File Terlalu Besar' : 'File Too Large' }}",
                                text: "{{ $isId ? 'Ukuran file maksimum adalah 10MB.' : 'Maximum file size is 10MB.' }}",
                                confirmButtonColor: '#2563eb'
                            });
                            return;
                        }

                        const formData = new FormData();
                        formData.append('file', file);
                        
                        this.isUploading = true;
                        this.uploadProgress = 0;

                        axios.post('{{ route("journal.reviewer.upload-attachment", ["journal" => $journal->slug, "assignment" => $assignment]) }}', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            },
                            onUploadProgress: (progressEvent) => {
                                this.uploadProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                            }
                        })
                        .then(response => {
                            this.files.unshift({
                                id: response.data.file.id,
                                name: response.data.file.file_name,
                                size: (response.data.file.file_size / 1024).toFixed(2) + ' KB'
                            });
                            
                            document.getElementById('attachmentInput').value = '';
                            
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            Toast.fire({
                                icon: 'success',
                                title: "{{ $isId ? 'Lampiran berhasil diunggah' : 'Attachment uploaded successfully' }}"
                            });
                        })
                        .catch(error => {
                            const message = error.response?.data?.message || "{{ $isId ? 'Terjadi kesalahan saat mengunggah' : 'An error occurred during upload' }}";
                            Swal.fire({
                                icon: 'error',
                                title: "{{ $isId ? 'Unggahan Gagal' : 'Upload Failed' }}",
                                text: message,
                                confirmButtonColor: '#2563eb'
                            });
                        })
                        .finally(() => {
                            this.isUploading = false;
                            this.uploadProgress = 0;
                        });
                    },

                    deleteFile(id) {
                        Swal.fire({
                            title: "{{ $isId ? 'Hapus Lampiran?' : 'Delete Attachment?' }}",
                            text: "{{ $isId ? 'Anda tidak akan dapat mengembalikan tindakan ini!' : 'You won\'t be able to revert this!' }}",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: "{{ $isId ? 'Ya, hapus!' : 'Yes, delete it!' }}",
                            cancelButtonText: "{{ $isId ? 'Batal' : 'Cancel' }}"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                axios.delete(`/${this.journalSlug}/reviewer/{{ $assignment->slug }}/attachment/${id}`)
                                    .then(response => {
                                        this.files = this.files.filter(f => f.id !== id);
                                        
                                        const Toast = Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000,
                                            timerProgressBar: true
                                        });
                                        Toast.fire({
                                            icon: 'success',
                                            title: "{{ $isId ? 'Lampiran berhasil dihapus' : 'Attachment deleted successfully' }}"
                                        });
                                    })
                                    .catch(error => {
                                        Swal.fire({
                                            icon: 'error',
                                            title: "{{ $isId ? 'Kesalahan' : 'Error' }}",
                                            text: "{{ $isId ? 'Gagal menghapus lampiran.' : 'Failed to delete attachment.' }}",
                                            confirmButtonColor: '#2563eb'
                                        });
                                    });
                            }
                        });
                    },
                    
                    get journalSlug() {
                        return '{{ $journal->slug }}';
                    }
                }));
            });
        </script>
    @endpush
</x-app-layout>
