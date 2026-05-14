@extends('layouts.app')

@section('title', 'Quick Submit Plugin - ' . $journal->name)

@section('content')
    <div class="max-w-4xl mx-auto space-y-6" x-data="quickSubmit()">

        {{-- HEADER --}}
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 p-6 flex items-center gap-4">
            <a href="{{ route('journal.settings.tools.index', ['journal' => $journal->slug]) }}" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-slate-800">Quick Submit</h1>
        </div>

        <form action="{{ route('journal.settings.tools.quicksubmit.store', ['journal' => $journal->slug]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Metadata Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 space-y-4">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Article Metadata
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Section</label>
                        <select name="section_id" class="w-full rounded-xl border-slate-200 focus:ring-indigo-500">
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Issue</label>
                        <select name="issue_id" class="w-full rounded-xl border-slate-200 focus:ring-indigo-500">
                            @foreach($issues as $issue)
                                <option value="{{ $issue->id }}">{{ $issue->identifier }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                    <input type="text" name="title" required class="w-full rounded-xl border-slate-200 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Abstract</label>
                    <textarea name="abstract" rows="4" required class="w-full rounded-xl border-slate-200 focus:ring-indigo-500"></textarea>
                </div>
            </div>

            {{-- Authors Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Authors
                    </h3>
                    <button type="button" @click="addAuthor()" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">+ Add Author</button>
                </div>

                <div class="space-y-4">
                    <template x-for="(author, index) in authors" :key="index">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 relative">
                            <button type="button" @click="removeAuthor(index)" x-show="authors.length > 1" class="absolute top-2 right-2 text-slate-400 hover:text-red-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <input type="text" :name="'authors['+index+'][given_name]'" placeholder="Given Name" required class="rounded-lg border-slate-200 text-sm">
                                <input type="text" :name="'authors['+index+'][family_name]'" placeholder="Family Name" class="rounded-lg border-slate-200 text-sm">
                                <input type="email" :name="'authors['+index+'][email]'" placeholder="Email" required class="rounded-lg border-slate-200 text-sm">
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- File Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 space-y-4">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Full-Text File (PDF)
                </h3>
                <input type="file" name="file" accept=".pdf" required class="w-full p-2 border border-slate-200 rounded-xl text-sm">
            </div>

            <button type="submit" class="w-full py-4 rounded-2xl text-white font-bold shadow-lg shadow-indigo-500/30 transition-all hover:scale-[1.01]" style="background: linear-gradient(to right, #4f46e5, #7c3aed);">
                Publish Now
            </button>
        </form>
    </div>

    <script>
        function quickSubmit() {
            return {
                authors: [{ given_name: '', family_name: '', email: '' }],
                addAuthor() { this.authors.push({ given_name: '', family_name: '', email: '' }); },
                removeAuthor(index) { this.authors.splice(index, 1); }
            }
        }
    </script>
@endsection
