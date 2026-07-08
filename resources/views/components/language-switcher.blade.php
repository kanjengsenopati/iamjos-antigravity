@props(['inline' => false])

@php
    $currentLoc = app()->getLocale();
@endphp

@if($inline)
    <div x-data="{ open: false }" class="relative inline-block text-left">
        <button @click="open = !open" type="button"
            class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-full border border-slate-200 transition-all duration-200 focus:outline-none">
            @if($currentLoc === 'id')
                <svg class="w-4 h-2.5 rounded-sm border border-slate-200" viewBox="0 0 3 2">
                    <rect width="3" height="1" fill="#FF0000"/>
                    <rect y="1" width="3" height="1" fill="#FFFFFF"/>
                </svg>
            @else
                <svg class="w-4 h-2.5 rounded-sm border border-slate-200" viewBox="0 0 50 30">
                    <clipPath id="t"><path d="M0,0 v30 h50 v-30 z"/></clipPath>
                    <path d="M0,0 v30 h50 v-30 z" fill="#012169"/>
                    <path d="M0,0 L50,30 M0,30 L50,0" stroke="#fff" stroke-width="6"/>
                    <path d="M0,0 L50,30 M0,30 L50,0" stroke="#C8102E" stroke-width="4" clip-path="url(#t)"/>
                    <path d="M25,0 v30 M0,15 h50" stroke="#fff" stroke-width="10"/>
                    <path d="M25,0 v30 M0,15 h50" stroke="#C8102E" stroke-width="6"/>
                </svg>
            @endif
            <span>{{ $currentLoc === 'id' ? 'ID' : 'EN' }}</span>
            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
        </button>
        
        <!-- Dropdown Menu -->
        <div x-show="open" 
            @click.outside="open = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 mt-2 w-36 bg-white rounded-[16px] shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-slate-100 py-1.5 focus:outline-none overflow-hidden z-[70]"
            style="display: none;">
            <a href="{{ route('locale.switch', 'id') }}" 
                class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors {{ $currentLoc === 'id' ? 'bg-slate-50 text-indigo-600' : '' }}">
                <svg class="w-4 h-2.5 rounded-sm border border-slate-200" viewBox="0 0 3 2">
                    <rect width="3" height="1" fill="#FF0000"/>
                    <rect y="1" width="3" height="1" fill="#FFFFFF"/>
                </svg>
                <span class="flex-1 text-left">Indonesia</span>
                @if ($currentLoc === 'id')
                    <i class="fa-solid fa-check text-emerald-600 text-[10px]"></i>
                @endif
            </a>
            <a href="{{ route('locale.switch', 'en') }}" 
                class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors {{ $currentLoc === 'en' ? 'bg-slate-50 text-indigo-600' : '' }}">
                <svg class="w-4 h-2.5 rounded-sm border border-slate-200" viewBox="0 0 50 30">
                    <clipPath id="u"><path d="M0,0 v30 h50 v-30 z"/></clipPath>
                    <path d="M0,0 v30 h50 v-30 z" fill="#012169"/>
                    <path d="M0,0 L50,30 M0,30 L50,0" stroke="#fff" stroke-width="6"/>
                    <path d="M0,0 L50,30 M0,30 L50,0" stroke="#C8102E" stroke-width="4" clip-path="url(#u)"/>
                    <path d="M25,0 v30 M0,15 h50" stroke="#fff" stroke-width="10"/>
                    <path d="M25,0 v30 M0,15 h50" stroke="#C8102E" stroke-width="6"/>
                </svg>
                <span class="flex-1 text-left">English</span>
                @if ($currentLoc === 'en')
                    <i class="fa-solid fa-check text-indigo-600 text-[10px]"></i>
                @endif
            </a>
        </div>
    </div>
@else
    <!-- Floating mode, but positioned where it won't overlap key header elements (bottom-right) -->
    <div class="fixed bottom-6 right-6 z-[60]">
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" type="button"
                class="flex items-center gap-2 px-4 py-2 bg-white/90 hover:bg-white text-slate-800 text-sm font-medium rounded-full shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-slate-100 backdrop-blur-md transition-all duration-200 focus:outline-none">
                @if($currentLoc === 'id')
                    <svg class="w-4 h-2.5 rounded-sm border border-slate-200" viewBox="0 0 3 2">
                        <rect width="3" height="1" fill="#FF0000"/>
                        <rect y="1" width="3" height="1" fill="#FFFFFF"/>
                    </svg>
                @else
                    <svg class="w-4 h-2.5 rounded-sm border border-slate-200" viewBox="0 0 50 30">
                        <clipPath id="v"><path d="M0,0 v30 h50 v-30 z"/></clipPath>
                        <path d="M0,0 v30 h50 v-30 z" fill="#012169"/>
                        <path d="M0,0 L50,30 M0,30 L50,0" stroke="#fff" stroke-width="6"/>
                        <path d="M0,0 L50,30 M0,30 L50,0" stroke="#C8102E" stroke-width="4" clip-path="url(#v)"/>
                        <path d="M25,0 v30 M0,15 h50" stroke="#fff" stroke-width="10"/>
                        <path d="M25,0 v30 M0,15 h50" stroke="#C8102E" stroke-width="6"/>
                    </svg>
                @endif
                <span>{{ $currentLoc === 'id' ? 'ID' : 'EN' }}</span>
                <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
            </button>
            
            <!-- Dropdown Menu -->
            <div x-show="open" 
                @click.outside="open = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute bottom-full right-0 mb-2 w-36 bg-white rounded-[16px] shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-slate-100 py-1.5 focus:outline-none overflow-hidden"
                style="display: none;">
                <a href="{{ route('locale.switch', 'id') }}" 
                    class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors {{ $currentLoc === 'id' ? 'bg-slate-50 text-indigo-600' : '' }}">
                    <svg class="w-4 h-2.5 rounded-sm border border-slate-200" viewBox="0 0 3 2">
                        <rect width="3" height="1" fill="#FF0000"/>
                        <rect y="1" width="3" height="1" fill="#FFFFFF"/>
                    </svg>
                    <span class="flex-1 text-left">Indonesia</span>
                    @if ($currentLoc === 'id')
                        <i class="fa-solid fa-check text-emerald-600 text-[10px]"></i>
                    @endif
                </a>
                <a href="{{ route('locale.switch', 'en') }}" 
                    class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors {{ $currentLoc === 'en' ? 'bg-slate-50 text-indigo-600' : '' }}">
                    <svg class="w-4 h-2.5 rounded-sm border border-slate-200" viewBox="0 0 50 30">
                        <clipPath id="w"><path d="M0,0 v30 h50 v-30 z"/></clipPath>
                        <path d="M0,0 v30 h50 v-30 z" fill="#012169"/>
                        <path d="M0,0 L50,30 M0,30 L50,0" stroke="#fff" stroke-width="6"/>
                        <path d="M0,0 L50,30 M0,30 L50,0" stroke="#C8102E" stroke-width="4" clip-path="url(#w)"/>
                        <path d="M25,0 v30 M0,15 h50" stroke="#fff" stroke-width="10"/>
                        <path d="M25,0 v30 M0,15 h50" stroke="#C8102E" stroke-width="6"/>
                    </svg>
                    <span class="flex-1 text-left">English</span>
                    @if ($currentLoc === 'en')
                        <i class="fa-solid fa-check text-indigo-600 text-[10px]"></i>
                    @endif
                </a>
            </div>
        </div>
    </div>
@endif
