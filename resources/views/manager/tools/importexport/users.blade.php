@extends('layouts.app')

@section('title', 'Users XML Plugin - ' . $journal->name)

@section('content')
    <div x-data="userXmlPlugin()" class="space-y-6">

        {{-- HEADER --}}
        <div
            class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
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
                        Users XML Plugin
                    </h1>
                </div>
                <p class="text-sm text-slate-500 mt-1 ml-8">Import and export user accounts and roles in XML format.</p>
            </div>
        </div>

        {{-- ALERTS --}}
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <p class="text-sm text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-red-500 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </div>
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        {{-- MAIN CONTENT --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">

            {{-- TABS NAVIGATION --}}
            <div class="border-b border-slate-200">
                <nav class="flex">
                    <button @click="tab = 'import'"
                        :class="tab === 'import' ?
                            'border-indigo-600 text-indigo-600 bg-white -mb-[1px] rounded-t-2xl border-t border-l border-r shadow-[0_-4px_10px_rgba(0,0,0,0.02)]' :
                            'border-transparent text-slate-400 hover:text-slate-600 hover:bg-slate-50/50'"
                        class="flex-1 md:flex-none whitespace-nowrap py-4 px-8 border-b-2 font-bold text-sm flex items-center justify-center gap-2 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Import Users
                    </button>
                    <button @click="tab = 'export'"
                        :class="tab === 'export' ?
                            'border-indigo-600 text-indigo-600 bg-white -mb-[1px] rounded-t-2xl border-t border-l border-r shadow-[0_-4px_10px_rgba(0,0,0,0.02)]' :
                            'border-transparent text-slate-400 hover:text-slate-600 hover:bg-slate-50/50'"
                        class="flex-1 md:flex-none whitespace-nowrap py-4 px-8 border-b-2 font-bold text-sm flex items-center justify-center gap-2 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        Export Users
                    </button>
                </nav>
            </div>

            {{-- TAB: IMPORT --}}
            <div x-show="tab === 'import'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="p-6">

                <div class="max-w-2xl">
                    <h3 class="font-bold text-lg text-slate-800 mb-2">Import User Accounts</h3>
                    <p class="text-sm text-slate-500 mb-6">
                        Upload a compatible XML file to import users and their role assignments. Accounts will be linked by email.
                    </p>

                    <form action="{{ route('journal.settings.tools.users.import', ['journal' => $journal->slug]) }}"
                        method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <div class="border-2 border-dashed border-slate-200 rounded-xl p-8 text-center hover:border-indigo-400 transition-colors"
                            x-data="{ dragging: false }" @dragover.prevent="dragging = true"
                            @dragleave.prevent="dragging = false" @drop.prevent="dragging = false; handleDrop($event)"
                            :class="{ 'border-indigo-400 bg-indigo-50/50': dragging }">

                            <input type="file" name="xml_file" id="xml_file" accept=".xml" class="hidden"
                                @change="fileName = $event.target.files[0]?.name || ''">

                            <label for="xml_file" class="cursor-pointer">
                                <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <p class="text-slate-600 mb-2"><span class="font-medium">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-slate-400">XML files only (max. 10MB)</p>
                            </label>
                            <p x-show="fileName" x-text="fileName" class="text-sm text-indigo-600 font-medium mt-3"></p>
                        </div>

                        <button type="submit" class="w-full px-5 py-3 rounded-xl text-sm font-medium text-white shadow-lg shadow-indigo-500/25 transition-all flex items-center justify-center gap-2"
                            style="background: linear-gradient(to right, #4f46e5, #9333ea);">
                            Process Import
                        </button>
                    </form>
                </div>
            </div>

            {{-- TAB: EXPORT --}}
            <div x-show="tab === 'export'" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="p-6">

                <form action="{{ route('journal.settings.tools.users.export', ['journal' => $journal->slug]) }}" method="POST">
                    @csrf
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-lg text-slate-800">Select Users to Export</h3>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-medium text-white shadow-lg shadow-emerald-500/25 transition-all"
                            style="background: linear-gradient(to right, #10b981, #14b8a6);">
                            Export Selected
                        </button>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="p-4 w-12">
                                        <input type="checkbox" @change="toggleAll($event)" class="rounded border-slate-300">
                                    </th>
                                    <th class="p-4">Name</th>
                                    <th class="p-4">Email</th>
                                    <th class="p-4">Roles</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($users as $user)
                                <tr>
                                    <td class="p-4">
                                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox rounded border-slate-300">
                                    </td>
                                    <td class="p-4">
                                        <div class="font-medium text-slate-800">{{ $user->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $user->username }}</div>
                                    </td>
                                    <td class="p-4 text-slate-600">{{ $user->email }}</td>
                                    <td class="p-4">
                                        @foreach($user->roles as $role)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 uppercase">
                                            {{ $role->name }}
                                        </span>
                                        @endforeach
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function userXmlPlugin() {
            return {
                tab: 'import',
                fileName: '',
                handleDrop(e) {
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        document.getElementById('xml_file').files = files;
                        this.fileName = files[0].name;
                    }
                },
                toggleAll(e) {
                    const checked = e.target.checked;
                    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = checked);
                }
            }
        }
    </script>
@endsection
