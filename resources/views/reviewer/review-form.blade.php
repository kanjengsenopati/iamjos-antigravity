@extends('layouts.app')

@section('title', 'Review Form - ' . ($assignment->submission->title ?? 'Review'))

@section('content')
    @php
        $isId = app()->getLocale() === 'id';
    @endphp

    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('journal.reviewer.show', $assignment->slug) }}" class="text-primary-600 hover:text-primary-700 text-sm">
                <i class="fa-solid fa-arrow-left mr-1"></i>
                {{ $isId ? 'Kembali ke Review' : 'Back to Review' }}
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $reviewForm->title }}</h1>
            @if($reviewForm->description)
                <p class="text-sm text-gray-600 mt-1">{{ $reviewForm->description }}</p>
            @endif
        </div>

        <!-- Progress -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">{{ $isId ? 'Progress' : 'Progress' }}</span>
                <span class="font-medium text-gray-900">{{ $assignment->getFormCompletionPercentage() }}%</span>
            </div>
            <div class="mt-2 bg-gray-200 rounded-full h-2">
                <div class="bg-primary-600 h-2 rounded-full transition-all" style="width: {{ $assignment->getFormCompletionPercentage() }}%"></div>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('journal.reviewer.form.store', $assignment->slug) }}" method="POST">
            @csrf
            
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6 space-y-8">
                    @foreach($reviewForm->elements as $index => $element)
                        <div class="pb-6 border-b border-gray-100 last:border-0">
                            <div class="flex items-start gap-3 mb-4">
                                <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 text-primary-700 text-sm font-semibold">
                                    {{ $index + 1 }}
                                </span>
                                <div class="flex-1">
                                    <label class="text-base font-medium text-gray-900 block">
                                        {{ $element->question }}
                                        @if($element->required)
                                            <span class="text-red-600">*</span>
                                        @endif
                                    </label>
                                    @if($element->description)
                                        <p class="text-sm text-gray-500 mt-1">{{ $element->description }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="ml-11">
                                @php
                                    $existingValue = $existingResponses->get($element->id)?->response_value;
                                    $fieldName = "responses[{$element->id}]";
                                @endphp

                                @switch($element->element_type->value)
                                    @case('text')
                                        <input type="text" name="{{ $fieldName }}" 
                                            value="{{ old($fieldName, $existingValue) }}"
                                            {{ $element->required ? 'required' : '' }}
                                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                            placeholder="{{ $isId ? 'Masukkan jawaban Anda...' : 'Enter your answer...' }}">
                                        @break

                                    @case('textarea')
                                        <textarea name="{{ $fieldName }}" rows="4"
                                            {{ $element->required ? 'required' : '' }}
                                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                            placeholder="{{ $isId ? 'Masukkan jawaban Anda...' : 'Enter your answer...' }}">{{ old($fieldName, $existingValue) }}</textarea>
                                        @break

                                    @case('checkbox')
                                        @php
                                            $selectedValues = old($fieldName, json_decode($existingValue, true) ?? []);
                                        @endphp
                                        @if($element->options)
                                            <div class="space-y-3">
                                                @foreach($element->options as $option)
                                                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50/30 cursor-pointer transition-all">
                                                        <input type="checkbox" name="{{ $fieldName }}[]" value="{{ $option['value'] }}"
                                                            {{ in_array($option['value'], $selectedValues) ? 'checked' : '' }}
                                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                                        <span class="text-sm text-gray-700">{{ $option['label'] }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                        @break

                                    @case('radio')
                                        @if($element->options)
                                            <div class="space-y-3">
                                                @foreach($element->options as $option)
                                                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50/30 cursor-pointer transition-all">
                                                        <input type="radio" name="{{ $fieldName }}" value="{{ $option['value'] }}"
                                                            {{ old($fieldName, $existingValue) == $option['value'] ? 'checked' : '' }}
                                                            {{ $element->required ? 'required' : '' }}
                                                            class="border-gray-300 text-primary-600 focus:ring-primary-500">
                                                        <span class="text-sm text-gray-700">{{ $option['label'] }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                        @break

                                    @case('select')
                                        <select name="{{ $fieldName }}" {{ $element->required ? 'required' : '' }}
                                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                                            <option value="">{{ $isId ? '-- Pilih --' : '-- Select --' }}</option>
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
                                                    class="text-3xl transition-colors"
                                                    :class="rating >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'">
                                                    <i class="fa-solid fa-star"></i>
                                                </button>
                                            @endfor
                                            <input type="hidden" name="{{ $fieldName }}" :value="rating" {{ $element->required ? 'required' : '' }}>
                                            <span class="text-sm text-gray-500 ml-2" x-text="rating > 0 ? rating + '/{{ $config['max'] }}' : '{{ $isId ? 'Belum dinilai' : 'Not rated' }}'"></span>
                                        </div>
                                        @break
                                @endswitch

                                @error($fieldName)
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between rounded-b-xl">
                    <button type="submit" name="save_draft" value="1"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fa-solid fa-save mr-2"></i>
                        {{ $isId ? 'Simpan Draft' : 'Save Draft' }}
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        {{ $isId ? 'Kirim Formulir' : 'Submit Form' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
