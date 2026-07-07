<!-- Language Switcher Stitched responsif -->
<div class="fixed top-20 right-4 lg:top-6 lg:right-6 z-[60]">
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" type="button"
            class="flex items-center gap-2 px-4 py-2 bg-white/90 hover:bg-white text-slate-800 text-sm font-medium rounded-full shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-slate-100 backdrop-blur-md transition-all duration-200 focus:outline-none">
            <i class="fa-solid fa-globe text-slate-500"></i>
            <span>{{ app()->getLocale() === 'id' ? 'ID' : 'EN' }}</span>
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
            class="absolute right-0 mt-2 w-36 bg-white rounded-[16px] shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-slate-100 py-1.5 focus:outline-none overflow-hidden"
            style="display: none;">
            <a href="{{ route('locale.switch', 'id') }}" 
                class="flex items-center justify-between px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'id' ? 'bg-slate-50 text-indigo-600' : '' }}">
                <span>Bahasa Indonesia</span>
                @if (app()->getLocale() === 'id')
                    <i class="fa-solid fa-check text-emerald-600"></i>
                @endif
            </a>
            <a href="{{ route('locale.switch', 'en') }}" 
                class="flex items-center justify-between px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'en' ? 'bg-slate-50 text-indigo-600' : '' }}">
                <span>English</span>
                @if (app()->getLocale() === 'en')
                    <i class="fa-solid fa-check text-indigo-600"></i>
                @endif
            </a>
        </div>
    </div>
</div>
