@extends('layouts.app')

@section('title', 'DOAJ Export Plugin - ' . $journal->name)

@section('content')
    <div x-data="doajPlugin()" class="space-y-6">

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
                        DOAJ Export Plugin
                    </h1>
                </div>
                <p class="text-sm text-slate-500 mt-1 ml-8">Export journal metadata for the Directory of Open Access Journals (DOAJ) indexing.</p>
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

        {{-- MAIN CONTENT --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden p-6">
            <form action="{{ route('journal.settings.tools.doaj.export', ['journal' => $journal->slug]) }}" method="POST">
                @csrf
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800">Select Articles</h3>
                        <p class="text-sm text-slate-500">Only published articles are listed here.</p>
                    </div>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-medium text-white shadow-lg shadow-amber-500/25 transition-all"
                        style="background: linear-gradient(to right, #f59e0b, #d97706);">
                        Export to XML
                    </button>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="p-4 w-12">
                                    <input type="checkbox" @change="toggleAll($event)" class="rounded border-slate-300">
                                </th>
                                <th class="p-4">Article Title</th>
                                <th class="p-4">Issue</th>
                                <th class="p-4">Date Published</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($submissions as $submission)
                            <tr>
                                <td class="p-4">
                                    <input type="checkbox" name="submission_ids[]" value="{{ $submission->id }}" class="article-checkbox rounded border-slate-300">
                                </td>
                                <td class="p-4">
                                    <div class="font-medium text-slate-800">{{ Str::limit($submission->title, 100) }}</div>
                                    <div class="text-xs text-slate-400">
                                        {{ $submission->authors->pluck('family_name')->join(', ') }}
                                    </div>
                                </td>
                                <td class="p-4 text-slate-600">
                                    {{ $submission->issue?->identifier ?? '-' }}
                                </td>
                                <td class="p-4 text-slate-600">
                                    {{ $submission->published_at?->format('Y-m-d') ?? '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <script>
        function doajPlugin() {
            return {
                toggleAll(e) {
                    const checked = e.target.checked;
                    document.querySelectorAll('.article-checkbox').forEach(cb => cb.checked = checked);
                }
            }
        }
    </script>
@endsection
