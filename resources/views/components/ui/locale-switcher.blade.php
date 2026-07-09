{{--
    Global Language Switcher Component (Sidebar Variant Only)
    Usage: <x-ui.locale-switcher />
    Depends on $currentLocale shared via AppServiceProvider View::composer('*')
--}}
@props(['variant' => 'sidebar'])

@php
    $loc = $currentLocale ?? session('app_locale', app()->getLocale());
    $isIdActive = in_array($loc, ['id', 'id_ID']);
    $locales = [
        'en' => ['label' => 'English',   'tag' => 'en_US', 'flag' => 'EN'],
        'id' => ['label' => 'Indonesia', 'tag' => 'id_ID', 'flag' => 'ID'],
    ];
    $activeKey   = $isIdActive ? 'id' : 'en';
@endphp

<div class="relative" x-data="{ open: false }">
    {{-- Expanded button --}}
    <button
        type="button"
        @click="open = !open"
        x-show="!sidebarCollapsed"
        class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-600 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors"
    >
        <span class="flex items-center gap-2">
            <i class="fa-solid fa-globe text-slate-400 text-sm"></i>
            <span>{{ $isIdActive ? 'Indonesia' : 'English' }}</span>
        </span>
        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
    </button>

    {{-- Collapsed icon-only --}}
    <button
        type="button"
        @click="open = !open"
        x-show="sidebarCollapsed"
        class="w-full flex items-center justify-center p-2 text-slate-400 hover:bg-slate-50 rounded-lg transition-colors"
        title="{{ $isIdActive ? 'Ganti Bahasa' : 'Switch Language' }}"
    >
        <i class="fa-solid fa-globe text-sm"></i>
    </button>

    <div
        x-show="open"
        @click.away="open = false"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="absolute bottom-full left-0 right-0 mb-1 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 z-50"
    >
        @foreach ($locales as $code => $info)
            <a
                href="{{ route('locale.switch', $code) }}"
                class="flex items-center justify-between px-3 py-2 text-xs transition-colors {{ $code === $activeKey ? 'font-bold text-primary-600 bg-primary-50/60' : 'text-slate-700 hover:bg-slate-50' }}"
            >
                <span class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-5 h-4 rounded text-[9px] font-bold bg-slate-100 text-slate-500">{{ $info['flag'] }}</span>
                    <span>{{ $info['label'] }}</span>
                </span>
                @if ($code === $activeKey)
                    <i class="fa-solid fa-check text-primary-500 text-[10px]"></i>
                @endif
            </a>
        @endforeach
    </div>
</div>
