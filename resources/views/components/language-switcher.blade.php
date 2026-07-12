@props(['inline' => false])

@php
    $loc = session('app_locale', app()->getLocale());
    $availableLocales = [
        'en' => 'English',
        'id' => 'Bahasa Indonesia',
    ];
@endphp

@if($inline)
    {{-- Inline Dropdown for Navbar/Headers --}}
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" type="button" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:text-gray-900 transition-colors focus:outline-none">
            <i class="fa-solid fa-globe text-gray-400"></i>
            <span>{{ $availableLocales[$loc] ?? 'English' }}</span>
            <i class="fa-solid fa-chevron-down text-[10px] text-gray-400"></i>
        </button>
        <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-1 w-40 bg-white border border-gray-100 rounded-xl shadow-lg py-1 z-50">
            @foreach($availableLocales as $code => $name)
                <a href="{{ route('locale.switch', $code) }}" class="flex items-center justify-between px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 {{ $code === $loc ? 'bg-gray-50 font-bold text-indigo-600' : '' }}">
                    <span>{{ $name }}</span>
                    @if ($code === $loc)
                        <i class="fa-solid fa-check text-indigo-500 text-[10px]"></i>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
@else
    {{-- Floating/Fixed Layout for Auth Pages --}}
    <div class="fixed top-4 right-4 z-50" x-data="{ open: false }">
        <button @click="open = !open" type="button" class="flex items-center gap-2 bg-white border border-slate-200 rounded-full px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none">
            <i class="fa-solid fa-globe text-slate-400"></i>
            <span>{{ $availableLocales[$loc] ?? 'English' }}</span>
            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
        </button>
        <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-1 w-40 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-50">
            @foreach($availableLocales as $code => $name)
                <a href="{{ route('locale.switch', $code) }}" class="flex items-center justify-between px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 {{ $code === $loc ? 'bg-slate-50 font-bold text-indigo-600' : '' }}">
                    <span>{{ $name }}</span>
                    @if ($code === $loc)
                        <i class="fa-solid fa-check text-indigo-500 text-[10px]"></i>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
@endif
