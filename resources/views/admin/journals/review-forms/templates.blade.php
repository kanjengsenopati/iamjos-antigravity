@extends('layouts.app')

@section('title', ($isId ? 'Template Formulir Ulasan' : 'Review Form Templates') . ' - ' . ($journal->abbreviation ?? 'IAMJOS'))

@section('content')
    <div x-data="{ selectedTemplate: null }">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('journal.settings.workflow.index', ['journal' => $journal->slug, 'tab' => 'review', 'subtab' => 'forms']) }}"
                    class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $isId ? 'Template Formulir Ulasan' : 'Review Form Templates' }}
                </h1>
            </div>
            <p class="text-sm text-gray-500 ml-10">
                {{ $isId ? 'Pilih template untuk membuat formulir ulasan dengan cepat' : 'Select a template to quickly create a review form' }}
            </p>
        </div>

        <!-- Templates Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($templates as $key => $template)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-100 bg-gradient-to-br from-primary-50 to-white">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-clipboard-list text-primary-600 text-xl"></i>
                            </div>
                            <span class="px-2 py-1 bg-white text-xs font-medium text-primary-600 rounded-full border border-primary-100">
                                {{ count($template['elements']) }} {{ $isId ? 'Pertanyaan' : 'Questions' }}
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">
                            {{ $isId ? $template['name_id'] : $template['name'] }}
                        </h3>
                        <p class="text-sm text-gray-600">
                            {{ $isId ? $template['description_id'] : $template['description'] }}
                        </p>
                    </div>

                    <!-- Elements Preview -->
                    <div class="p-6 bg-gray-50">
                        <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-3">
                            {{ $isId ? 'Elemen Formulir:' : 'Form Elements:' }}
                        </h4>
                        <div class="space-y-2">
                            @foreach (array_slice($template['elements'], 0, 5) as $element)
                                <div class="flex items-start gap-2 text-xs">
                                    <span class="flex-shrink-0 w-4 h-4 bg-primary-100 rounded flex items-center justify-center mt-0.5">
                                        @switch($element['element_type'])
                                            @case('rating')
                                                <i class="fa-solid fa-star text-primary-600 text-[8px]"></i>
                                                @break
                                            @case('textarea')
                                                <i class="fa-solid fa-align-left text-primary-600 text-[8px]"></i>
                                                @break
                                            @case('radio')
                                                <i class="fa-solid fa-circle-dot text-primary-600 text-[8px]"></i>
                                                @break
                                            @case('checkbox')
                                                <i class="fa-solid fa-check-square text-primary-600 text-[8px]"></i>
                                                @break
                                            @default
                                                <i class="fa-solid fa-circle text-primary-600 text-[8px]"></i>
                                        @endswitch
                                    </span>
                                    <span class="text-gray-700 line-clamp-1">
                                        {{ $isId && isset($element['question_id']) ? $element['question_id'] : $element['question'] }}
                                    </span>
                                </div>
                            @endforeach
                            @if (count($template['elements']) > 5)
                                <div class="text-xs text-gray-500 italic pl-6">
                                    {{ $isId ? '+' . (count($template['elements']) - 5) . ' lainnya' : '+' . (count($template['elements']) - 5) . ' more' }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="p-6 bg-white border-t border-gray-100">
                        <button @click="selectedTemplate = '{{ $key }}'; $nextTick(() => $refs.createForm{{ $key }}.submit())"
                            class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                            <i class="fa-solid fa-plus mr-2"></i>
                            {{ $isId ? 'Gunakan Template' : 'Use Template' }}
                        </button>

                        <!-- Hidden Form -->
                        <form x-ref="createForm{{ $key }}"
                            action="{{ route('journal.settings.workflow.review-forms.from-template', ['journal' => $journal->slug]) }}"
                            method="POST"
                            style="display: none;">
                            @csrf
                            <input type="hidden" name="template_key" value="{{ $key }}">
                            <input type="hidden" name="locale" value="{{ app()->getLocale() }}">
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Import Section -->
        <div class="mt-8 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-file-import text-indigo-600 text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                        {{ $isId ? 'Impor Template Kustom' : 'Import Custom Template' }}
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        {{ $isId ? 'Impor formulir ulasan dari file JSON yang telah diekspor sebelumnya' : 'Import a review form from a previously exported JSON file' }}
                    </p>

                    <form action="{{ route('journal.settings.workflow.review-forms.import', ['journal' => $journal->slug]) }}"
                        method="POST" enctype="multipart/form-data"
                        x-data="{ fileName: '' }">
                        @csrf
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <label class="block w-full">
                                    <span class="sr-only">{{ $isId ? 'Pilih File JSON' : 'Choose JSON file' }}</span>
                                    <input type="file" name="import_file" accept=".json"
                                        @change="fileName = $event.target.files[0]?.name || ''"
                                        required
                                        class="block w-full text-sm text-gray-500
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-lg file:border-0
                                            file:text-sm file:font-medium
                                            file:bg-primary-50 file:text-primary-700
                                            hover:file:bg-primary-100
                                            cursor-pointer">
                                </label>
                                <p x-show="fileName" x-text="fileName" class="mt-1 text-xs text-gray-500"></p>
                            </div>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fa-solid fa-upload mr-2"></i>
                                {{ $isId ? 'Impor' : 'Import' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
