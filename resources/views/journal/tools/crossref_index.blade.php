@extends('layouts.app')

@section('title', ($isId ? 'Plugin Ekspor XML Crossref' : 'Crossref XML Export Plugin') . ' - ' . $journal->name)

@section('content')
    <div class="space-y-6">

        {{-- Header Area --}}
        <div
            class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('journal.settings.tools.index', ['journal' => $journal->slug]) }}"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text">
                        {{ $isId ? 'Plugin Ekspor XML Crossref' : 'Crossref XML Export Plugin' }}
                    </h1>
                </div>
                <p class="text-sm text-slate-500 mt-1 ml-8">
                    {{ $isId ? 'Ekspor metadata artikel untuk registrasi DOI.' : 'Export article metadata for DOI registration.' }}
                </p>
            </div>
        </div>

        {{-- TABS NAVIGATION (Flat Underline Style like website settings) --}}
        <div class="border-b border-slate-200 mb-6">
            <nav class="flex space-x-8 overflow-x-auto no-scrollbar" aria-label="Tabs">
                <a href="?tab=settings"
                    class="flex-shrink-0 py-4 px-1 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer {{ $tab === 'settings' ? 'border-primary-600 text-primary-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    {{ $isId ? 'Pengaturan' : 'Settings' }}
                </a>
                <a href="?tab=articles"
                    class="flex-shrink-0 py-4 px-1 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer {{ $tab === 'articles' ? 'border-primary-600 text-primary-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    {{ $isId ? 'Artikel' : 'Articles' }}
                </a>
            </nav>
        </div>

        {{-- MAIN CONTENT CARD --}}
        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-8">
            @if ($tab == 'settings')
                <div class="max-w-4xl">
                    <div class="mb-6 flex items-center justify-between">
                        <a href="{{ route('journal.settings.doi.edit', $journal->slug) }}" class="text-blue-600 hover:underline text-sm font-medium">
                            {{ $isId ? 'Pengaturan Plugin DOI' : 'DOI Plugin Settings' }}
                        </a>
                    </div>
                    
                    @if(empty($journal->doi_prefix))
                        <div class="mb-8 p-4 bg-orange-50 border-l-4 border-orange-500 rounded-r-lg shadow-sm">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-orange-800">{{ $isId ? 'Awalan DOI Belum Dikonfigurasi' : 'DOI Prefix Not Configured' }}</h3>
                                    <div class="mt-2 text-sm text-orange-700">
                                        <p>
                                            {{ $isId ? 'Anda harus mengonfigurasi Awalan DOI yang valid sebelum dapat mendaftarkan DOI ke Crossref. Silakan kunjungi ' : 'You must configure a valid DOI Prefix before you can register DOIs with Crossref. Please visit the ' }}
                                            <a href="{{ route('journal.settings.doi.edit', $journal->slug) }}" class="font-bold underline hover:text-orange-900">
                                                {{ $isId ? 'Pengaturan Plugin DOI' : 'DOI Plugin Settings' }}
                                            </a>
                                            {{ $isId ? ' untuk mengaturnya.' : ' to set it up.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('journal.settings.tools.crossref.save', $journal->slug) }}" method="POST">
                        @csrf
                        
                        {{-- Depositor Info --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">{{ $isId ? 'Pendaftar (Depositor)' : 'Depositor' }}</h3>
                            
                            <div class="grid gap-6 mb-6 md:grid-cols-2">
                                <div>
                                    <label for="depositor_name" class="block mb-2 text-sm font-bold text-gray-700">{{ $isId ? 'Nama pendaftar *' : 'Depositor name *' }}</label>
                                    <input type="text" id="depositor_name" name="depositor_name" required
                                        value="{{ old('depositor_name', $journal->getSetting('crossref_depositor_name') ?? 'Siswanto') }}"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                </div>
                                <div>
                                    <label for="depositor_email" class="block mb-2 text-sm font-bold text-gray-700">{{ $isId ? 'Surel pendaftar *' : 'Depositor email *' }}</label>
                                    <input type="email" id="depositor_email" name="depositor_email" required
                                        value="{{ old('depositor_email', $journal->getSetting('crossref_depositor_email') ?? 'syswebcosmg@gmail.com') }}"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                </div>
                            </div>
                        </div>

                        {{-- API Credentials --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">{{ $isId ? 'Kredensial Crossref' : 'Crossref Credentials' }}</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                {{ $isId ? 'Gunakan kredensial (nama pengguna/kata sandi) untuk akun Crossref Anda. Kredensial ini digunakan untuk mengautentikasi deposit.' : 'Use the credentials (username/password) for your Crossref account. These are used to authenticate deposits.' }}
                            </p>

                            <div class="grid gap-6 mb-6 md:grid-cols-2">
                                <div>
                                    <label for="username" class="block mb-2 text-sm font-bold text-gray-700">{{ $isId ? 'Nama Pengguna' : 'Username' }}</label>
                                    <input type="text" id="username" name="username"
                                        value="{{ old('username', $journal->getSetting('crossref_username')) }}"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                </div>
                                <div>
                                    <label for="password" class="block mb-2 text-sm font-bold text-gray-700">{{ $isId ? 'Kata Sandi' : 'Password' }}</label>
                                    <div class="relative">
                                        <input type="password" id="password" name="password"
                                            placeholder="{{ $journal->getSetting('crossref_password') ? '••••••••' : '' }}"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 pr-10">
                                        <button type="button" onclick="togglePassword()" 
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                                            tabindex="-1">
                                            {{-- Eye icon (visible when password is hidden) --}}
                                            <svg id="eyeIcon" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            {{-- Eye-off icon (visible when password is shown) --}}
                                            <svg id="eyeOffIcon" class="w-[18px] h-[18px] hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="mt-1 text-xs text-green-600">{{ $isId ? 'Kata sandi akan dienkripsi sebelum disimpan.' : 'Password will be encrypted before storage.' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Automation --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">{{ $isId ? 'Otomatisasi' : 'Automation' }}</h3>
                            
                            <div class="flex items-start mb-4">
                                <div class="flex items-center h-5">
                                    <input id="automatic_deposit" name="automatic_deposit" type="checkbox" value="1"
                                        {{ $journal->getSetting('crossref_automatic_deposit') ? 'checked' : '' }}
                                        class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 text-blue-600">
                                </div>
                                <label for="automatic_deposit" class="ml-2 text-sm font-medium text-gray-900">
                                    {{ $isId ? 'IAMJOS akan mendepositkan DOI yang ditetapkan secara otomatis ke CrossRef.' : 'IAMJOS will deposit assigned DOIs automatically to CrossRef.' }}
                                </label>
                            </div>

                            <div class="flex items-start mb-4">
                                <div class="flex items-center h-5">
                                    <input id="auto_poll_status" name="auto_poll_status" type="checkbox" value="1"
                                        {{ $journal->getSetting('crossref_auto_poll_status') ? 'checked' : '' }}
                                        class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 text-blue-600">
                                </div>
                                <label for="auto_poll_status" class="ml-2 text-sm font-medium text-gray-900">
                                    {{ $isId ? 'Periksa dan perbarui status DOI yang didepositkan secara otomatis (Auto-polling).' : 'Automatically check and update the status of deposited DOIs (Auto-polling).' }}
                                </label>
                            </div>

                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="test_mode" name="test_mode" type="checkbox" value="1"
                                        {{ $journal->getSetting('crossref_test_mode') ? 'checked' : '' }}
                                        class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 text-blue-600">
                                </div>
                                <label for="test_mode" class="ml-2 text-sm font-medium text-gray-900">
                                    {{ $isId ? 'Gunakan API pengujian CrossRef (lingkungan pengujian).' : 'Use the CrossRef test API (testing environment).' }}
                                </label>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex gap-4 border-t pt-6">
                            <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 font-semibold rounded-xl text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none transition shadow-sm cursor-pointer">{{ $isId ? 'Simpan' : 'Save' }}</button>
                            <button type="button" onclick="window.history.back()" class="py-2.5 px-5 mr-2 mb-2 text-sm font-semibold text-slate-700 focus:outline-none bg-white rounded-xl border border-slate-200 hover:bg-slate-50 transition cursor-pointer">{{ $isId ? 'Batal' : 'Cancel' }}</button>
                        </div>
                    </form>
                </div>
            @endif

            @if ($tab == 'articles')
                {{-- OJS 3.3 STYLE FILTERS --}}
                <div class="flex flex-wrap gap-4 text-sm mb-6 text-slate-600 items-center">
                    <span class="font-semibold text-slate-700">{{ $isId ? 'Status:' : 'Status:' }}</span>

                    <a href="?status=all&tab={{ $tab }}"
                        class="{{ $status == 'all' ? 'font-bold text-black bg-slate-100 px-2 py-1 rounded' : 'text-blue-600 hover:underline px-2 py-1' }}">{{ $isId ? 'Semua' : 'All' }}</a>
                    <span class="text-slate-300">|</span>

                    <a href="?status=not_deposited&tab={{ $tab }}"
                        class="{{ $status == 'not_deposited' ? 'font-bold text-black bg-slate-100 px-2 py-1 rounded' : 'text-blue-600 hover:underline px-2 py-1' }}">{{ $isId ? 'Belum Didepositkan' : 'Not Deposited' }}</a>
                    <span class="text-slate-300">|</span>

                    <a href="?status=marked&tab={{ $tab }}"
                        class="{{ $status == 'marked' ? 'font-bold text-black bg-slate-100 px-2 py-1 rounded' : 'text-blue-600 hover:underline px-2 py-1' }}">{{ $isId ? 'Ditandai Aktif' : 'Marked Active' }}</a>
                    <span class="text-slate-300">|</span>

                    <a href="?status=active&tab={{ $tab }}"
                        class="{{ $status == 'active' ? 'font-bold text-black bg-slate-100 px-2 py-1 rounded' : 'text-blue-600 hover:underline px-2 py-1' }}">{{ $isId ? 'Aktif' : 'Active' }}</a>
                    <span class="text-slate-300">|</span>

                    <a href="?status=submitted&tab={{ $tab }}"
                        class="{{ $status == 'submitted' ? 'font-bold text-black bg-slate-100 px-2 py-1 rounded' : 'text-blue-600 hover:underline px-2 py-1' }}">{{ $isId ? 'Diajukan' : 'Submitted' }}</a>
                    <span class="text-slate-300">|</span>

                    <a href="?status=failed&tab={{ $tab }}"
                        class="{{ $status == 'failed' ? 'font-bold text-black bg-slate-100 px-2 py-1 rounded' : 'text-blue-600 hover:underline px-2 py-1' }}">{{ $isId ? 'Gagal' : 'Failed' }}</a>
                </div>

                @php
                    $hasDepositorInfo = $journal->getSetting('crossref_depositor_name') && $journal->getSetting('crossref_depositor_email') && $journal->getSetting('crossref_username');
                @endphp

                @if(!$hasDepositorInfo)
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">{{ $isId ? 'Pengaturan Crossref Belum Lengkap' : 'Incomplete Crossref Settings' }}</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>
                                        {{ $isId ? 'Anda harus mengonfigurasi Nama Pendaftar, Surel Pendaftar, dan Nama Pengguna Crossref di tab ' : 'You must configure your Depositor Name, Depositor Email, and Crossref Username in the ' }}
                                        <a href="?tab=settings" class="font-bold underline hover:text-red-900">{{ $isId ? 'Pengaturan' : 'Settings tab' }}</a>
                                        {{ $isId ? ' sebelum dapat mengekspor atau mendepositkan artikel.' : ' before you can export or deposit articles.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- CONTENT FORM --}}
                <form action="{{ route('journal.settings.tools.crossref.download', $journal->slug) }}" method="POST">
                    @csrf

                    <div class="bg-white rounded-[24px] border border-slate-200 overflow-hidden mb-6 shadow-sm">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead class="bg-slate-50 text-slate-700 border-b border-slate-200 uppercase text-xs font-bold">
                                <tr>
                                    <th class="p-4 w-10 text-center">
                                        <input type="checkbox" id="selectAll" onclick="toggleAll(this)"
                                            class="rounded border-gray-300 focus:ring-blue-500 text-blue-600">
                                    </th>
                                    <th class="p-4 w-1/2">{{ $isId ? 'Judul' : 'Title' }}</th>
                                    <th class="p-4">{{ $isId ? 'Penulis' : 'Author' }}</th>
                                    <th class="p-4">{{ $isId ? 'Terbitan' : 'Issue' }}</th>
                                    <th class="p-4">{{ $isId ? 'Status' : 'Status' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($submissions as $sub)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="p-4 text-center">
                                            @php
                                                $hasDoi = $sub->currentPublication && !empty($sub->currentPublication->doi);
                                            @endphp
                                            <input type="checkbox" name="submission_ids[]" value="{{ $sub->id }}"
                                                class="sub-checkbox rounded border-gray-300 {{ $hasDoi ? 'text-blue-600 focus:ring-blue-500' : 'text-gray-300 cursor-not-allowed bg-gray-100' }}"
                                                @if(!$hasDoi) disabled title="{{ $isId ? 'DOI belum ditetapkan' : 'DOI has not been assigned' }}" @endif>
                                        </td>
                                        <td class="p-4">
                                            <div class="font-medium text-blue-600 mb-1">
                                                <a href="{{ route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $sub->url_slug]) }}"
                                                    target="_blank" class="hover:underline">
                                                    {{ $sub->title }}
                                                </a>
                                            </div>
                                            {{-- DOI Info --}}
                                            @if($hasDoi)
                                                <div class="text-xs text-gray-700 font-mono bg-blue-50 border border-blue-100 inline-flex items-center px-1.5 py-0.5 rounded gap-1">
                                                    <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path><path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path></svg>
                                                    {{ $sub->currentPublication->doi }}
                                                </div>
                                            @else
                                                <div class="text-xs text-orange-700 font-medium bg-orange-50 border border-orange-200 inline-flex items-center px-1.5 py-0.5 rounded gap-1">
                                                    <svg class="w-3 h-3 text-orange-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                                    {{ $isId ? 'DOI belum ditetapkan' : 'DOI not assigned' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="p-4 text-gray-600">
                                            {{ $sub->authors->first()->last_name ?? $sub->authors->first()->first_name }}
                                            @if ($sub->authors->count() > 1)
                                                et al.
                                            @endif
                                        </td>
                                        <td class="p-4 text-gray-600">
                                            Vol {{ $sub->issue->volume ?? '-' }}, No {{ $sub->issue->number ?? '-' }}
                                            ({{ $sub->issue->year ?? '-' }})
                                        </td>
                                        <td class="p-4">
                                            @if (isset($sub->currentPublication->doi_status) && $sub->currentPublication->doi_status == 'active')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    {{ $isId ? 'Aktif' : 'Active' }}
                                                </span>
                                            @elseif (isset($sub->currentPublication->doi_status) && $sub->currentPublication->doi_status == 'submitted')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $isId ? 'Diajukan' : 'Submitted' }}
                                                </span>
                                            @elseif (isset($sub->currentPublication->doi_status) && $sub->currentPublication->doi_status == 'failed')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                    {{ $isId ? 'Gagal' : 'Failed' }}
                                                </span>
                                            @elseif (isset($sub->currentPublication->doi_status) && $sub->currentPublication->doi_status == 'marked')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                    {{ $isId ? 'Ditandai Aktif' : 'Marked Active' }}
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    {{ $isId ? 'Belum Dideposit' : 'Not Deposited' }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-gray-500 italic">
                                            {{ $isId ? 'Tidak ada artikel yang ditemukan cocok dengan filter ini.' : 'No articles found matching this filter.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- ACTION BUTTONS BAR --}}
                    <div class="flex items-center justify-between bg-slate-50/50 p-4 rounded-[24px] border border-slate-200 shadow-sm">
                        <div class="text-xs text-slate-500 font-bold">
                            {{ $isId ? 'Menampilkan' : 'Showing' }} {{ $submissions->firstItem() ?? 0 }} {{ $isId ? 'sampai' : 'to' }} {{ $submissions->lastItem() ?? 0 }} {{ $isId ? 'dari' : 'of' }}
                            {{ $submissions->total() }} {{ $isId ? 'item' : 'items' }}
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" 
                                name="action" value="deposit"
                                formaction="{{ route('journal.settings.tools.crossref.deposit', $journal->slug) }}"
                                @if(!$hasDepositorInfo) disabled @endif
                                class="{{ !$hasDepositorInfo ? 'bg-gray-200 text-gray-400 cursor-not-allowed border-gray-300' : 'bg-green-600 hover:bg-green-700 text-white border-green-600 cursor-pointer' }} font-medium py-2 px-4 rounded-xl border transition text-sm">
                                Deposit
                            </button>

                            <button type="submit"
                                @if(!$hasDepositorInfo) disabled @endif
                                class="{{ !$hasDepositorInfo ? 'bg-blue-300 cursor-not-allowed border-blue-300' : 'bg-blue-600 hover:bg-blue-700 text-white border-blue-600 cursor-pointer' }} font-medium py-2 px-4 rounded-xl border transition text-sm flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                {{ $isId ? 'Unduh XML' : 'Download XML' }}
                            </button>

                            <button type="submit"
                                name="action" value="markActive"
                                formaction="{{ route('journal.settings.tools.crossref.mark_active', $journal->slug) }}"
                                class="bg-white text-gray-700 font-medium py-2 px-4 rounded-xl border border-gray-300 hover:bg-gray-50 transition text-sm cursor-pointer">
                                {{ $isId ? 'Tandai Aktif' : 'Mark Active' }}
                            </button>
                        </div>
                    </div>

                    <div class="mt-4">
                        {{ $submissions->appends(request()->query())->links() }}
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
        function toggleAll(source) {
            checkboxes = document.querySelectorAll('.sub-checkbox');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                if (!checkboxes[i].disabled) {
                    checkboxes[i].checked = source.checked;
                }
            }
        }

        function togglePassword() {
            const input = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }
    </script>
@endsection
