@php
    $isId = app()->getLocale() === 'id';
@endphp

@switch($element->element_type->value)
    @case('text')
        <input type="text" disabled
            class="w-full rounded-md border-gray-300 bg-white text-sm"
            placeholder="{{ $isId ? 'Jawaban teks singkat...' : 'Short text answer...' }}">
    @break

    @case('textarea')
        <textarea disabled rows="3"
            class="w-full rounded-md border-gray-300 bg-white text-sm"
            placeholder="{{ $isId ? 'Jawaban teks panjang...' : 'Long text answer...' }}"></textarea>
    @break

    @case('checkbox')
        @if($element->options && count($element->options) > 0)
            <div class="space-y-2">
                @foreach($element->options as $option)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" disabled class="rounded border-gray-300 text-primary-600">
                        <span class="text-sm text-gray-700">{{ is_array($option) ? ($option['label'] ?? $option['value'] ?? '') : $option }}</span>
                    </label>
                @endforeach
            </div>
        @else
            <p class="text-xs text-gray-400 italic">{{ $isId ? 'Belum ada pilihan ditambahkan' : 'No options added yet' }}</p>
        @endif
    @break

    @case('radio')
        @if($element->options && count($element->options) > 0)
            <div class="space-y-2">
                @foreach($element->options as $option)
                    <label class="flex items-center gap-2">
                        <input type="radio" disabled name="preview_{{ $element->id }}" class="border-gray-300 text-primary-600">
                        <span class="text-sm text-gray-700">{{ is_array($option) ? ($option['label'] ?? $option['value'] ?? '') : $option }}</span>
                    </label>
                @endforeach
            </div>
        @else
            <p class="text-xs text-gray-400 italic">{{ $isId ? 'Belum ada pilihan ditambahkan' : 'No options added yet' }}</p>
        @endif
    @break

    @case('select')
        <select disabled class="w-full rounded-md border-gray-300 bg-white text-sm">
            <option>{{ $isId ? '-- Pilih salah satu --' : '-- Select one --' }}</option>
            @if($element->options && count($element->options) > 0)
                @foreach($element->options as $option)
                    <option>{{ is_array($option) ? ($option['label'] ?? $option['value'] ?? '') : $option }}</option>
                @endforeach
            @endif
        </select>
        @if(!$element->options || count($element->options) === 0)
            <p class="text-xs text-gray-400 italic mt-1">{{ $isId ? 'Belum ada pilihan ditambahkan' : 'No options added yet' }}</p>
        @endif
    @break

    @case('rating')
        <div class="flex items-center gap-2">
            @for($i = 1; $i <= 5; $i++)
                <button type="button" disabled class="text-2xl text-gray-300">
                    <i class="fa-solid fa-star"></i>
                </button>
            @endfor
            <span class="text-sm text-gray-500 ml-2">{{ $isId ? '(1-5 bintang)' : '(1-5 stars)' }}</span>
        </div>
    @break
@endswitch
