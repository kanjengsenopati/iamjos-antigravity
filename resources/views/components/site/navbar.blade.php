{{--
    Dynamic Portal Navigation Component (OJS 3.3 Style)
    Uses $publicMenu from PublicNavigationComposer
--}}
@props(['publicMenu' => null, 'settings' => []])

{{-- NAVBAR CONTAINER --}}
<header class="bg-white border-b border-slate-200 sticky top-0 z-50 font-sans" x-data="{ mobileMenuOpen: false }">
    <div class="container mx-auto px-4 h-16 flex items-center justify-between">

        {{-- 1. LOGO --}}
        <div class="flex-shrink-0 flex items-center gap-3">
            <a href="{{ route('portal.home') }}" class="flex items-center gap-2">
                @if(isset($settings['site_logo']) && $settings['site_logo'])
                    <img src="{{ Storage::url($settings['site_logo']) }}" alt="{{ $settings['site_name'] ?? 'IAMJOS' }}" class="h-8 w-auto">
                @else
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                        IJ
                    </div>
                @endif
                <span class="text-sm font-bold text-slate-800 tracking-tight hidden sm:block">{{ $settings['site_name'] ?? 'IAMJOS' }}</span>
            </a>
        </div>

        {{-- 2. DYNAMIC MENU ITEMS (Center/Right) --}}
        <nav class="hidden md:flex items-center gap-1">
            @foreach($publicMenu ?? [] as $item)

                {{-- CASE A: DROPDOWN (Has Children) --}}
                @if($item->children->isNotEmpty())
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open"
                            class="flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium transition
                            {{ $item->is_active ? 'text-blue-600 bg-blue-50' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50' }}">

                            {{-- Icon (If exists) --}}
                            @if(!empty($item->icon)) <i class="{{ $item->icon }}"></i> @endif

                            {{ $item->label }}

                            {{-- Arrow Icon --}}
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        {{-- Dropdown Body --}}
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             class="absolute top-full right-0 mt-2 w-48 bg-white border border-slate-100 rounded-md shadow-lg py-1 z-50"
                             style="display: none;">
                            @foreach($item->children as $child)
                                <a href="{{ $child->url }}" target="{{ $child->target }}"
                                   class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-blue-600">
                                   {{ $child->label }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                {{-- CASE B: SINGLE LINK --}}
                @else
                    <a href="{{ $item->url }}" target="{{ $item->target }}"
                       class="flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium transition
                       {{ $item->is_active ? 'text-blue-600 bg-blue-50' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50' }}">

                        {{-- Icon (If exists) --}}
                        @if(!empty($item->icon)) <i class="{{ $item->icon }}"></i> @endif

                        {{ $item->label }}
                    </a>
                @endif

            @endforeach
        </nav>

        {{-- 3. USER ACTIONS (Far Right) --}}
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="hidden md:inline-flex items-center px-4 py-2 text-sm font-medium text-slate-600 hover:text-blue-600">
                    <i class="fa-solid fa-gauge-high mr-2"></i>
                    Dashboard
                </a>

                {{-- User Avatar / Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2">
                        @if(auth()->user()->avatar)
                            <img src="{{ Storage::url(auth()->user()->avatar) }}" class="w-8 h-8 rounded-full object-cover">
                        @else
                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-medium">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fa-solid fa-user mr-2 text-gray-400"></i>
                            Profile
                        </a>
                        <hr class="my-1 border-gray-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-right-from-bracket mr-2 text-gray-400"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-blue-600">Login</a>
                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700">Register</a>
            @endauth

            {{-- Mobile Menu Toggle --}}
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>

    </div>
</header>

{{-- Mobile Menu --}}
<div x-show="mobileMenuOpen" x-cloak @click.away="mobileMenuOpen = false"
     class="md:hidden bg-white border-t border-slate-200 py-4">
    <div class="container mx-auto px-4 space-y-3">
        @foreach($publicMenu ?? [] as $item)
            @if($item->children->isNotEmpty())
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full text-left text-slate-700 hover:text-blue-600 py-2 font-medium">
                        @if(!empty($item->icon)) <i class="{{ $item->icon }} mr-2"></i> @endif
                        {{ $item->label }}
                        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="ml-4 space-y-1" x-transition>
                        @foreach($item->children as $child)
                            <a href="{{ $child->url }}" target="{{ $child->target }}" class="block text-slate-600 hover:text-blue-600 py-1">
                                {{ $child->label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <a href="{{ $item->url }}" target="{{ $item->target }}" class="block text-slate-700 hover:text-blue-600 py-2 font-medium">
                    @if(!empty($item->icon)) <i class="{{ $item->icon }} mr-2"></i> @endif
                    {{ $item->label }}
                </a>
            @endif
        @endforeach

        @guest
            <hr class="border-slate-200 my-2">
            <a href="{{ route('login') }}" class="block text-slate-700 hover:text-blue-600 py-2">Login</a>
        @endguest
    </div>
</div>
