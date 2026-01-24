{{-- 
    Dynamic Portal Navigation Component
    Uses navigation_menus with journal_id = NULL (site-level menus)
--}}
@props(['primaryMenu' => null, 'settings' => []])

<nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-200/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            {{-- Logo --}}
            <a href="{{ route('portal.home') }}" class="flex items-center gap-3">
                @if(isset($settings['site_logo']) && $settings['site_logo'])
                    <img src="{{ Storage::url($settings['site_logo']) }}" alt="{{ $settings['site_name'] ?? 'IAMJOS' }}" class="h-10">
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold text-lg">
                        IJ
                    </div>
                @endif
                <span class="font-bold text-gray-900 hidden sm:block">{{ $settings['site_name'] ?? 'IAMJOS' }}</span>
            </a>

            {{-- Desktop Menu --}}
            <div class="hidden md:flex items-center gap-6">
                @if($primaryMenu && $primaryMenu->items->count() > 0)
                    @foreach($primaryMenu->items->where('is_active', true)->sortBy('order') as $item)
                        @php
                            $url = $item->resolved_url;
                            $isActive = request()->url() === $url;
                        @endphp
                        
                        @if($item->type === 'divider')
                            <span class="text-gray-300">|</span>
                        @else
                            <a href="{{ $url }}" 
                               target="{{ $item->target }}"
                               class="{{ $isActive ? 'text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900' }} font-medium transition-colors">
                                @if($item->icon)
                                    <i class="{{ $item->icon }} mr-1"></i>
                                @endif
                                {{ $item->label }}
                            </a>
                        @endif
                    @endforeach
                @else
                    {{-- Fallback static menu if no menu items configured --}}
                    <a href="{{ route('portal.home') }}" class="{{ request()->routeIs('portal.home') ? 'text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900' }} font-medium">Home</a>
                    <a href="{{ route('portal.journals') }}" class="{{ request()->routeIs('portal.journals') ? 'text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900' }} font-medium">Journals</a>
                    <a href="{{ route('portal.about') }}" class="{{ request()->routeIs('portal.about') ? 'text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900' }} font-medium">About</a>
                @endif
            </div>

            {{-- Auth Buttons --}}
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="hidden md:inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                        <i class="fa-solid fa-gauge-high mr-2"></i>
                        Dashboard
                    </a>
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
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2">
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
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center px-6 py-2 text-sm font-medium text-gray-700 bg-white border-2 border-gray-200 hover:border-blue-600 hover:text-blue-600 rounded-xl transition-all mr-2">
                        Login
                    </a>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700">
                        Register
                    </a>
                @endauth

                {{-- Mobile Menu Toggle --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-gray-600">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileMenuOpen" x-cloak @click.away="mobileMenuOpen = false"
         class="md:hidden bg-white border-t border-gray-200 py-4">
        <div class="max-w-7xl mx-auto px-4 space-y-3">
            @if($primaryMenu && $primaryMenu->items->count() > 0)
                @foreach($primaryMenu->items->where('is_active', true)->sortBy('order') as $item)
                    @if($item->type !== 'divider')
                        <a href="{{ $item->resolved_url }}" 
                           target="{{ $item->target }}"
                           class="block text-gray-700 hover:text-blue-600 py-2 font-medium">
                            @if($item->icon)
                                <i class="{{ $item->icon }} mr-2"></i>
                            @endif
                            {{ $item->label }}
                        </a>
                    @endif
                @endforeach
            @else
                <a href="{{ route('portal.home') }}" class="block text-gray-700 hover:text-blue-600 py-2">Home</a>
                <a href="{{ route('portal.journals') }}" class="block text-gray-700 hover:text-blue-600 py-2">Journals</a>
                <a href="{{ route('portal.about') }}" class="block text-gray-700 hover:text-blue-600 py-2">About</a>
                <a href="{{ route('portal.search') }}" class="block text-gray-700 hover:text-blue-600 py-2">Search</a>
            @endif
            
            @guest
                <hr class="border-gray-200 my-2">
                <a href="{{ route('login') }}" class="block text-gray-700 hover:text-blue-600 py-2">Login</a>
            @endguest
        </div>
    </div>
</nav>
