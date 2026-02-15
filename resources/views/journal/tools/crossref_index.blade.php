@extends('layouts.app')

@section('title', 'Crossref XML Export Plugin - ' . $journal->name)

@section('content')
    <div class="bg-white rounded-lg shadow-sm min-h-screen">

        {{-- Header Area --}}
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Crossref XML Export Plugin</h1>
                <p class="text-slate-500 text-sm mt-1">Export article metadata for DOI registration.</p>
            </div>
            <a href="{{ route('journal.settings.tools.index', $journal->path) }}"
                class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2001/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Tools
            </a>
        </div>

        <div class="p-6">

            {{-- OJS 3.3 STYLE TABS --}}
            <div class="border-b border-gray-200 mb-6">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                    <li class="mr-2">
                        <a href="?tab=settings"
                            class="inline-block p-4 rounded-t-lg border-b-2 {{ $tab == 'settings' ? 'text-blue-600 border-blue-600 active' : 'text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                            Settings
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="?tab=articles"
                            class="inline-block p-4 rounded-t-lg border-b-2 {{ $tab == 'articles' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                            Articles
                        </a>
                    </li>
                </ul>
            </div>

            @if ($tab == 'settings')
                <div class="max-w-4xl">
                    <div class="mb-6">
                        <a href="#" class="text-blue-600 hover:underline text-sm font-medium">DOI Plugin Settings</a>
                    </div>

                    <form action="{{ route('journal.settings.tools.crossref.save', $journal->path) }}" method="POST">
                        @csrf
                        
                        {{-- Depositor Info --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Depositor</h3>
                            
                            <div class="grid gap-6 mb-6 md:grid-cols-2">
                                <div>
                                    <label for="depositor_name" class="block mb-2 text-sm font-bold text-gray-700">Depositor name *</label>
                                    <input type="text" id="depositor_name" name="depositor_name" required
                                        value="{{ old('depositor_name', $journal->getSetting('crossref_depositor_name')) }}"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                </div>
                                <div>
                                    <label for="depositor_email" class="block mb-2 text-sm font-bold text-gray-700">Depositor email *</label>
                                    <input type="email" id="depositor_email" name="depositor_email" required
                                        value="{{ old('depositor_email', $journal->getSetting('crossref_depositor_email')) }}"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                </div>
                            </div>
                        </div>

                        {{-- API Credentials --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Crossref Credentials</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Use the credentials (username/password) for your Crossref account. These are used to authenticate deposits.
                            </p>

                            <div class="grid gap-6 mb-6 md:grid-cols-2">
                                <div>
                                    <label for="username" class="block mb-2 text-sm font-bold text-gray-700">Username</label>
                                    <input type="text" id="username" name="username"
                                        value="{{ old('username', $journal->getSetting('crossref_username')) }}"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                </div>
                                <div>
                                    <label for="password" class="block mb-2 text-sm font-bold text-gray-700">Password</label>
                                    <input type="password" id="password" name="password"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <p class="mt-1 text-xs text-orange-600">Please note that the password will be saved as plain text, i.e. not encrypted.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Automation --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Automation</h3>
                            
                            <div class="flex items-start mb-4">
                                <div class="flex items-center h-5">
                                    <input id="automatic_deposit" name="automatic_deposit" type="checkbox" value="1"
                                        {{ $journal->getSetting('crossref_automatic_deposit') ? 'checked' : '' }}
                                        class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 text-blue-600">
                                </div>
                                <label for="automatic_deposit" class="ml-2 text-sm font-medium text-gray-900">
                                    IAMJOS will deposit assigned DOIs automatically to CrossRef.
                                </label>
                            </div>

                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="test_mode" name="test_mode" type="checkbox" value="1"
                                        {{ $journal->getSetting('crossref_test_mode') ? 'checked' : '' }}
                                        class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 text-blue-600">
                                </div>
                                <label for="test_mode" class="ml-2 text-sm font-medium text-gray-900">
                                    Use the CrossRef test API (testing environment).
                                </label>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex gap-4 border-t pt-6">
                             <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none">Save</button>
                             <button type="button" onclick="window.history.back()" class="py-2.5 px-5 mr-2 mb-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200">Cancel</button>
                        </div>
                    </form>
                </div>
            @endif

            @if ($tab == 'articles')
            {{-- OJS 3.3 STYLE FILTERS --}}
            <div class="flex flex-wrap gap-4 text-sm mb-6 text-gray-600 items-center">
                <span class="font-semibold text-gray-700">Status:</span>

                <a href="?status=all&tab={{ $tab }}"
                    class="{{ $status == 'all' ? 'font-bold text-black bg-gray-100 px-2 py-1 rounded' : 'text-blue-600 hover:underline px-2 py-1' }}">All</a>
                <span class="text-gray-300">|</span>

                <a href="?status=not_deposited&tab={{ $tab }}"
                    class="{{ $status == 'not_deposited' ? 'font-bold text-black bg-gray-100 px-2 py-1 rounded' : 'text-blue-600 hover:underline px-2 py-1' }}">Not
                    Deposited</a>
                <span class="text-gray-300">|</span>

                <a href="?status=active&tab={{ $tab }}"
                    class="{{ $status == 'active' ? 'font-bold text-black bg-gray-100 px-2 py-1 rounded' : 'text-blue-600 hover:underline px-2 py-1' }}">Active</a>
                <span class="text-gray-300">|</span>

                <a href="?status=marked&tab={{ $tab }}"
                    class="{{ $status == 'marked' ? 'font-bold text-black bg-gray-100 px-2 py-1 rounded' : 'text-blue-600 hover:underline px-2 py-1' }}">Marked
                    Registered</a>
            </div>

            {{-- CONTENT FORM --}}
            <form action="{{ route('journal.settings.tools.crossref.download', $journal->path) }}" method="POST">
                @csrf

                <div class="bg-white rounded border border-gray-200 overflow-hidden mb-6">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead class="bg-gray-50 text-gray-700 border-b border-gray-200 uppercase text-xs">
                            <tr>
                                <th class="p-4 w-10 text-center">
                                    <input type="checkbox" id="selectAll" onclick="toggleAll(this)"
                                        class="rounded border-gray-300 focus:ring-blue-500 text-blue-600">
                                </th>
                                <th class="p-4 w-1/2">Title</th>
                                <th class="p-4">Author</th>
                                <th class="p-4">Issue</th>
                                <th class="p-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($submissions as $sub)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 text-center">
                                        <input type="checkbox" name="submission_ids[]" value="{{ $sub->id }}"
                                            class="sub-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="p-4">
                                        <div class="font-medium text-blue-600 mb-1">
                                            <a href="{{ route('journal.public.article', ['journal' => $journal->slug, 'article' => $sub->id]) }}"
                                                target="_blank" class="hover:underline">
                                                {{ $sub->title }}
                                            </a>
                                        </div>
                                        {{-- DOI Info --}}
                                        @if($sub->currentPublication && $sub->currentPublication->doi)
                                            <div class="text-xs text-gray-500 font-mono bg-gray-100 inline-block px-1 rounded">
                                                DOI: {{ $sub->currentPublication->doi }}
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
                                        @if (isset($sub->doi_status) && $sub->doi_status == 'active')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Not Deposited
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-500 italic">
                                        No articles found matching this filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ACTION BUTTONS BAR --}}
                <div class="flex items-center justify-between bg-gray-50 p-4 rounded border border-gray-200">
                    <div class="text-xs text-gray-500">
                        Showing {{ $submissions->firstItem() ?? 0 }} to {{ $submissions->lastItem() ?? 0 }} of
                        {{ $submissions->total() }} items
                    </div>

                    <div class="flex gap-2">
                        <button type="button" disabled
                            class="bg-gray-200 text-gray-400 font-medium py-2 px-4 rounded border border-gray-300 cursor-not-allowed text-sm">
                            Deposit
                        </button>

                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded shadow-sm border border-blue-600 transition text-sm flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2001/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download XML
                        </button>

                        <button type="button"
                            class="bg-white text-gray-700 font-medium py-2 px-4 rounded border border-gray-300 hover:bg-gray-50 transition text-sm">
                            Mark Active
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
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
@endsection
