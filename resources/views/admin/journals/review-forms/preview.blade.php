@extends('layouts.app')

@section('title', 'Preview Form - ' . ($journal->abbreviation ?? 'IAMJOS'))

@section('content')
    @php
        $isId = app()->getLocale() === 'id';
    @endphp

    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('journal.settings.workflow.review-forms.builder', ['journal' => $journal->slug, 'reviewForm' => $reviewForm->id]) }}"
                    class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $isId ? 'Pratinjau Formulir' : 'Form Preview' }}
                </h1>
            </div>
            <p class="text-sm text-gray-500 ml-10">
                {{ $isId ? 'Tampilan formulir seperti yang akan dilihat reviewer' : 'How reviewers will see this form' }}
            </p>
        </div>

        <!-- Preview Card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <!-- Form Header -->
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-primary-50 to-blue-50">
                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $reviewForm->title }}</h2>
                @if ($reviewForm->description)
                    <p class="text-sm text-gray-600">{{ $reviewForm->description }}</p>
                @endif
                <div class="mt-3 flex items-center gap-4 text-xs text-gray-500">
                    <span>
                        <i class="fa-solid fa-list-check mr-1"></i>
                        {{ count($reviewForm->elements ?? []) }} {{ $isId ? 'pertanyaan' : 'questions' }}
                    </span>
                    <span>
                        <i class="fa-solid fa-clock mr-1"></i>
                        {{ $isId ? 'Estimasi: ~' . (count($reviewForm->elements ?? []) * 2) . ' menit' : 'Estimated: ~' . (count($reviewForm->elements ?? []) * 2) . ' minutes' }}
                    </span>
                </div>
            </div>

            <!-- Form Body -->
            <div class="p-6">
                @if (count($reviewForm->elements ?? []) === 0)
                    <div class="text-center py-12">
                        <i class="fa-solid fa-file-circle-exclamation text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            {{ $isId ? 'Formulir Kosong' : 'Empty Form' }}
                        </h3>
                        <p class="text-sm text-gray-500 mb-6">
                            {{ $isId ? 'Formulir ini belum memiliki pertanyaan. Tambahkan pertanyaan di Form Builder.' : 'This form has no questions yet. Add questions in the Form Builder.' }}
                        </p>
                        <a href="{{ route('journal.settings.workflow.review-forms.builder', ['journal' => $journal->slug, 'reviewForm' => $reviewForm->id]) }}"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
                            <i class="fa-solid fa-plus mr-2"></i>
                            {{ $isId ? 'Tambah Pertanyaan' : 'Add Questions' }}
                        </a>
                    </div>
                @else
                    <form class="space-y-8">
                        @foreach ($reviewForm->elements as $index => $element)
                            <div class="pb-6 border-b border-gray-100 last:border-0">
                                <!-- Question Header -->
                                <div class="flex items-start gap-3 mb-4">
                                    <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 text-primary-700 text-sm font-semibold">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="flex-1">
                                        <h3 class="text-base font-medium text-gray-900">
                                            {{ $element->question }}
                                            @if ($element->required)
                                                <span class="text-red-600">*</span>
                                            @endif
                                        </h3>
                                        @if ($element->description)
                                            <p class="text-sm text-gray-500 mt-1">{{ $element->description }}</p>
                                        @endif
                                        <span class="inline-block mt-2 px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                                            {{ $element->element_type->label() }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Answer Area -->
                                <div class="ml-11">
                                    @switch($element->element_type->value)
                                        @case('text')
                                            <input type="text" disabled
                                                class="w-full rounded-lg border-gray-300 bg-gray-50"
                                                placeholder="{{ $isId ? 'Masukkan jawaban Anda di sini...' : 'Enter your answer here...' }}">
                                        @break

                                        @case('textarea')
                                            <textarea disabled rows="4"
                                                class="w-full rounded-lg border-gray-300 bg-gray-50"
                                                placeholder="{{ $isId ? 'Masukkan jawaban Anda di sini...' : 'Enter your answer here...' }}"></textarea>
                                        @break

                                        @case('checkbox')
                                            @if ($element->options && count($element->options) > 0)
                                                <div class="space-y-3">
                                                    @foreach ($element->options as $option)
                                                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50/30 cursor-pointer transition-all">
                                                            <input type="checkbox" disabled class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                                            <span class="text-sm text-gray-700">{{ $option['label'] ?? $option['value'] }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @break

                                        @case('radio')
                                            @if ($element->options && count($element->options) > 0)
                                                <div class="space-y-3">
                                                    @foreach ($element->options as $option)
                                                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50/30 cursor-pointer transition-all">
                                                            <input type="radio" disabled name="preview_{{ $element->id }}" class="border-gray-300 text-primary-600 focus:ring-primary-500">
                                                            <span class="text-sm text-gray-700">{{ $option['label'] ?? $option['value'] }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @break

                                        @case('select')
                                            <select disabled class="w-full rounded-lg border-gray-300 bg-gray-50">
                                                <option>{{ $isId ? '-- Pilih salah satu --' : '-- Select one --' }}</option>
                                                @if ($element->options && count($element->options) > 0)
                                                    @foreach ($element->options as $option)
                                                        <option>{{ $option['label'] ?? $option['value'] }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @break

                                        @case('rating')
                                            <div class="flex items-center gap-2">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <button type="button" disabled class="text-3xl text-gray-300 hover:text-yellow-400 transition-colors">
                                                        <i class="fa-solid fa-star"></i>
                                                    </button>
                                                @endfor
                                                <span class="text-sm text-gray-500 ml-3">{{ $isId ? '1 = Sangat Buruk, 5 = Sangat Baik' : '1 = Very Poor, 5 = Excellent' }}</span>
                                            </div>
                                        @break
                                    @endswitch
                                </div>
                            </div>
                        @endforeach

                        <!-- Form Actions (Preview Only) -->
                        <div class="pt-6 border-t border-gray-200 flex justify-between">
                            <button type="button" disabled
                                class="px-4 py-2 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">
                                <i class="fa-solid fa-save mr-2"></i>
                                {{ $isId ? 'Simpan Draft' : 'Save Draft' }}
                            </button>
                            <button type="button" disabled
                                class="px-6 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed">
                                {{ $isId ? 'Kirim Ulasan' : 'Submit Review' }}
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-between">
            <a href="{{ route('journal.settings.workflow.review-forms.builder', ['journal' => $journal->slug, 'reviewForm' => $reviewForm->id]) }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                {{ $isId ? 'Kembali ke Builder' : 'Back to Builder' }}
            </a>
            <a href="{{ route('journal.settings.workflow.index', ['journal' => $journal->slug, 'tab' => 'review']) }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50">
                {{ $isId ? 'Selesai' : 'Done' }}
            </a>
        </div>
    </div>
@endsection
