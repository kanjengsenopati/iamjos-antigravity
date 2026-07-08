@extends('layouts.app')

@php
    $currentLoc = session('app_locale', app()->getLocale());
    $isId = in_array($currentLoc, ['id', 'id_ID']);
@endphp

@section('title', $isId ? 'Kirim Notifikasi' : 'Notify Users')
@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor {
        min-height: 200px;
        font-family: 'Inter', sans-serif;
        font-size: 0.875rem;
    }

    .ql-toolbar.ql-snow {
        border-color: #e5e7eb;
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
        background-color: #f9fafb;
    }

    .ql-container.ql-snow {
        border-color: #e5e7eb;
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        background-color: #fff;
    }
</style>
@endpush

@section('content')
<!-- Header -->
@include('admin.journals.users._header', ['activeTab' => 'notify'])

<!-- Flash Messages -->
@if (session('success'))
<div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800">
    <div class="flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ __(session('success')) }}</span>
    </div>
</div>
@endif

@if (session('error'))
<div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
    <div class="flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span>{{ session('error') }}</span>
    </div>
</div>
@endif

<!-- Notify Form Card -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm" x-data="{ loading: false }">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">{{ $isId ? 'Tulis Notifikasi' : 'Compose Notification' }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $isId ? 'Pilih peran penerima dan tulis pesan Anda di bawah ini.' : 'Select recipient roles and compose your message below.' }}</p>
    </div>

    <form action="{{ route($routePrefix . '.notify.send', ['journal' => $journal->slug]) }}" method="POST"
        @submit="loading = true" class="p-6 space-y-6">
        @csrf

        <!-- Recipient Roles -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">
                {{ $isId ? 'Peran Penerima' : 'Recipient Roles' }} <span class="text-red-500">*</span>
            </label>
            <p class="text-xs text-gray-500 mb-4">{{ $isId ? 'Pilih peran pengguna mana yang harus menerima notifikasi ini.' : 'Select which user roles should receive this notification.' }}</p>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($roles as $role)
                @php
                $roleClass = match ($role->name) {
                'Super Admin' => 'border-purple-200 bg-purple-50 hover:bg-purple-100',
                'Admin', 'Journal Manager' => 'border-red-200 bg-red-50 hover:bg-red-100',
                'Editor', 'Section Editor' => 'border-blue-200 bg-blue-50 hover:bg-blue-100',
                'Reviewer' => 'border-amber-200 bg-amber-50 hover:bg-amber-100',
                'Author' => 'border-emerald-200 bg-emerald-50 hover:bg-emerald-100',
                'Reader' => 'border-gray-200 bg-gray-50 hover:bg-gray-100',
                default => 'border-gray-200 bg-gray-50 hover:bg-gray-100',
                };
                $iconClass = match ($role->name) {
                'Super Admin' => 'fa-shield-halved text-purple-600',
                'Admin', 'Journal Manager' => 'fa-user-tie text-red-600',
                'Editor', 'Section Editor' => 'fa-pen-fancy text-blue-600',
                'Reviewer' => 'fa-magnifying-glass text-amber-600',
                'Author' => 'fa-feather-pointed text-emerald-600',
                'Reader' => 'fa-book-open text-gray-600',
                default => 'fa-user text-gray-600',
                };
                @endphp
                <label
                    class="relative flex items-center gap-3 p-4 rounded-lg border cursor-pointer transition-all {{ $roleClass }} {{ in_array($role->name, old('roles', [])) ? 'ring-2 ring-indigo-500' : '' }}">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid {{ $iconClass }}"></i>
                        <span class="text-sm font-medium text-gray-900">{{ $isId ? ($role->name === 'Super Admin' ? 'Super Administrator' : ($role->name === 'Journal Manager' ? 'Manajer Jurnal' : ($role->name === 'Section Editor' ? 'Editor Bagian' : ($role->name === 'Reviewer' ? 'Peninjau' : ($role->name === 'Author' ? 'Penulis' : ($role->name === 'Reader' ? 'Pembaca' : $role->name)))))) : $role->name }}</span>
                    </div>
                </label>
                @endforeach
            </div>

            @error('roles')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Subject -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ $isId ? 'Subjek' : 'Subject' }} <span class="text-red-500">*</span>
            </label>
            <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                class="block w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white transition-colors @error('subject') border-red-300 @enderror"
                placeholder="{{ $isId ? 'Masukkan subjek surel...' : 'Enter email subject...' }}">
            @error('subject')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Body -->
        <div>
            <label for="editor" class="block text-sm font-medium text-gray-700 mb-2">
                {{ $isId ? 'Isi Pesan' : 'Message Body' }} <span class="text-red-500">*</span>
            </label>
            <p class="text-xs text-gray-500 mb-3">{{ $isId ? 'Anda dapat menggunakan format HTML dasar di dalam isi pesan.' : 'You can use basic HTML formatting in the message body.' }}</p>
            
            <!-- Dynamic Placeholders Guide -->
            <div class="mb-4 p-3 bg-indigo-50 border border-indigo-100 rounded-lg text-xs text-indigo-800">
                <span class="font-semibold block mb-1">{{ $isId ? 'Variabel yang Tersedia:' : 'Available Placeholders:' }}</span>
                <div class="grid grid-cols-2 gap-2">
                    <code>{$name}</code> <span class="text-indigo-600">{{ $isId ? '- Nama Penerima' : '- Recipient Name' }}</span>
                    <code>{$email}</code> <span class="text-indigo-600">{{ $isId ? '- Surel Penerima' : '- Recipient Email' }}</span>
                    <code>{$journal_name}</code> <span class="text-indigo-600">{{ $isId ? '- Nama Jurnal' : '- Journal Name' }}</span>
                    <code>{$site_url}</code> <span class="text-indigo-600">{{ $isId ? '- URL Jurnal' : '- Site URL' }}</span>
                </div>
            </div>

            <div class="mb-2">
                <input type="hidden" name="body" id="body-input" value="{{ old('body') }}">
                <div id="editor-container"></div>
            </div>

            @error('body')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Send Copy Toggle -->
        <div class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 border border-gray-200">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="send_copy" value="1" class="sr-only peer"
                    {{ old('send_copy') ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600">
                </div>
            </label>
            <div>
                <span class="text-sm font-medium text-gray-900">{{ $isId ? 'Kirim salinan ke diri saya sendiri' : 'Send a copy to myself' }}</span>
                <p class="text-xs text-gray-500">{{ $isId ? 'Terima salinan notifikasi ini di' : 'Receive a copy of this notification at' }} {{ auth()->user()->email }}</p>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="{{ route($routePrefix . '.index', ['journal' => $journal->slug]) }}"
                class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                {{ $isId ? 'Batal' : 'Cancel' }}
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 transition-colors shadow-sm shadow-indigo-200 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="loading">
                <template x-if="!loading">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i>
                        {{ $isId ? 'Kirim Notifikasi' : 'Send Notification' }}
                    </span>
                </template>
                <template x-if="loading">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        {{ $isId ? 'Mengirim...' : 'Sending...' }}
                    </span>
                </template>
            </button>
        </div>
    </form>
</div>

<!-- Info Card -->
<div class="mt-6 p-4 rounded-lg bg-blue-50 border border-blue-200">
    <div class="flex gap-3">
        <div class="flex-shrink-0">
            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
        </div>
        <div class="text-sm text-blue-800">
            <p class="font-medium mb-1">{{ $isId ? 'Cara kerja' : 'How it works' }}</p>
            <ul class="list-disc list-inside space-y-1 text-blue-700">
                <li>{{ $isId ? 'Notifikasi dikirim melalui antrean latar belakang (queue) untuk performa optimal.' : 'Notifications are sent via background queue for optimal performance.' }}</li>
                <li>{{ $isId ? 'Daftar penerima yang besar diproses secara bertahap untuk mencegah kegagalan waktu habis (timeout).' : 'Large recipient lists are processed in batches to prevent timeouts.' }}</li>
                <li>{{ $isId ? 'Anda akan melihat konfirmasi setelah notifikasi masuk ke dalam antrean.' : 'You will see a confirmation once the notification is queued.' }}</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('editor-container')) {
            var quill = new Quill('#editor-container', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic'],
                        [{
                            'script': 'sub'
                        }, {
                            'script': 'super'
                        }],
                        ['link']
                    ]
                },
                placeholder: "{{ $isId ? 'Tulis pesan Anda di sini...' : 'Compose your message here...' }}"
            });

            // Update hidden input on change
            var hiddenInput = document.getElementById('body-input');
            quill.on('text-change', function() {
                hiddenInput.value = quill.root.innerHTML;
            });

            // Set initial content
            if (hiddenInput.value) {
                quill.clipboard.dangerouslyPasteHTML(hiddenInput.value);
            }
        }
    });
</script>
@endpush