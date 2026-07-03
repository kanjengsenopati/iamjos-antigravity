{{-- Language Block - Language selector (used in public sidebar) --}}
@props(['journal' => null, 'settings' => [], 'block' => null])

@php
    // Use global $currentLocale shared by AppServiceProvider View::composer('*')
    $loc = $currentLocale ?? session('app_locale', app()->getLocale());
    $availableLocales = [
        'en' => 'English',
        'id' => 'Bahasa Indonesia',
    ];
@endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open"
        class="w-full flex items-center justify-between px-3 py-2 text-sm bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
        <span class="flex items-center gap-2">
            <i class="fa-solid fa-globe text-gray-400"></i>
            {{ $availableLocales[$loc] ?? 'English' }}
        </span>
        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false"
        class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
        @foreach($availableLocales as $code => $name)
        <a href="{{ route('locale.switch', $code) }}"
            class="flex items-center justify-between px-3 py-2 text-sm hover:bg-gray-50 {{ $code === $loc ? 'bg-gray-50 font-medium text-primary-600' : '' }}">
            <span>{{ $name }}</span>
            @if ($code === $loc)
                <i class="fa-solid fa-check text-primary-500 text-xs"></i>
            @endif
        </a>
        @endforeach
    </div>
</div>