{{-- 
    Dynamic Portal Footer Component
    Uses navigation_menus with journal_id = NULL (site-level menus)
--}}
@props(['footerMenu' => null, 'settings' => []])

@php
// footerMenu is now a collection of navigation items
$footerMenuItems = $footerMenu ?? collect();
$hasFooterMenu = $footerMenuItems->isNotEmpty();
@endphp
            <div class="md:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    @if(isset($settings['site_logo']) && $settings['site_logo'])
                        <img src="{{ Storage::url($settings['site_logo']) }}" alt="{{ $settings['site_name'] ?? 'IAMJOS' }}" class="h-10">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold">
                            IJ
                        </div>
                    @endif
                    <span class="font-bold text-white">{{ $settings['site_name'] ?? 'IAMJOS' }}</span>
                </div>
                <p class="text-slate-400 text-sm mb-4 max-w-md">
                    {{ $settings['footer_description'] ?? 'Indonesian Academic Journal System - A modern platform for hosting and managing academic journals with OJS 3.3 feature parity.' }}
                </p>
            </div>

            {{-- Quick Links (Dynamic from Footer Menu) --}}
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Quick Links</h4>
                <ul class="space-y-2 text-sm">
                    @if($hasFooterMenu)
                        @foreach($footerMenuItems as $item)
                            @if(!($item->is_divider ?? false))
                                <li>
                                    <a href="{{ $item->resolved_url }}"
                                       target="{{ $item->target }}"
                                       class="text-slate-400 hover:text-white transition-colors">
                                        @if($item->icon ?? false)
                                            <i class="{{ $item->icon }} mr-2"></i>
                                        @endif
                                        {{ $item->label }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    @else
                        {{-- Fallback static links --}}
                        <li><a href="{{ route('portal.journals') }}" class="text-slate-400 hover:text-white transition-colors">All Journals</a></li>
                        <li><a href="{{ route('portal.search') }}" class="text-slate-400 hover:text-white transition-colors">Search Articles</a></li>
                        <li><a href="{{ route('portal.about') }}" class="text-slate-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="{{ route('login') }}" class="text-slate-400 hover:text-white transition-colors">Author Login</a></li>
                    @endif
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Contact</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    @if($settings['contact_email'] ?? false)
                        <li>
                            <i class="fa-solid fa-envelope mr-2 text-slate-500"></i>
                            <a href="mailto:{{ $settings['contact_email'] }}" class="hover:text-white transition-colors">
                                {{ $settings['contact_email'] }}
                            </a>
                        </li>
                    @endif
                    @if($settings['contact_phone'] ?? false)
                        <li>
                            <i class="fa-solid fa-phone mr-2 text-slate-500"></i>
                            <a href="tel:{{ $settings['contact_phone'] }}" class="hover:text-white transition-colors">
                                {{ $settings['contact_phone'] }}
                            </a>
                        </li>
                    @endif
                    @if($settings['contact_address'] ?? false)
                        <li>
                            <i class="fa-solid fa-location-dot mr-2 text-slate-500"></i>
                            {{ $settings['contact_address'] }}
                        </li>
                    @endif
                </ul>

                {{-- Social Links --}}
                @if(($settings['social_facebook'] ?? false) || ($settings['social_twitter'] ?? false) || ($settings['social_instagram'] ?? false))
                    <div class="flex items-center gap-3 mt-4">
                        @if($settings['social_facebook'] ?? false)
                            <a href="{{ $settings['social_facebook'] }}" target="_blank" class="text-slate-400 hover:text-white transition-colors">
                                <i class="fa-brands fa-facebook text-lg"></i>
                            </a>
                        @endif
                        @if($settings['social_twitter'] ?? false)
                            <a href="{{ $settings['social_twitter'] }}" target="_blank" class="text-slate-400 hover:text-white transition-colors">
                                <i class="fa-brands fa-twitter text-lg"></i>
                            </a>
                        @endif
                        @if($settings['social_instagram'] ?? false)
                            <a href="{{ $settings['social_instagram'] }}" target="_blank" class="text-slate-400 hover:text-white transition-colors">
                                <i class="fa-brands fa-instagram text-lg"></i>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Bottom Bar --}}
        <div class="border-t border-slate-800 mt-8 pt-8 flex flex-col md:flex-row items-center justify-between text-sm text-slate-500">
            <p>© {{ date('Y') }} {{ $settings['site_name'] ?? 'IAMJOS' }}. All rights reserved.</p>
            <p class="mt-4 md:mt-0">
                Powered by <strong class="text-slate-400">IAMJOS</strong> - Indonesian Academic Journal System
            </p>
        </div>
    </div>
</footer>
