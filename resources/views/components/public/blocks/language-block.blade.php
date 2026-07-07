@props(['journal', 'block'])
<div class="text-sm text-slate-600">
    <div class="flex items-center gap-2 mb-2">
        <i class="fa-solid fa-globe text-slate-400"></i>
        <span class="font-semibold">{{ __('Select Language') }}</span>
    </div>
    <x-language-switcher :inline="true" />
</div>
