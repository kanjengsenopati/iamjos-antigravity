{{--
    Dynamic Portal Footer Component (Dark Theme & Grid Layout)
    Uses $footerMenu from PublicNavigationComposer
--}}
@php
    use App\Facades\Settings;

    $footer    = Settings::site('footer_content');
    $siteTitle = Settings::site('site_title', config('app.name'));
    $siteIntro = Settings::site('site_intro', '');
@endphp

<footer class="bg-slate-900 text-slate-300 font-sans border-t border-slate-800">

    {{-- MAIN FOOTER CONTENT --}}
    <div class="container mx-auto px-4 py-12">
        @if($footer)
            {{-- CUSTOM FOOTER CONTENT --}}
            <div class="prose prose-invert max-w-none">
                {!! $footer !!}
            </div>
        @endif
    </div>
    </div>

    {{-- BOTTOM BAR: COPYRIGHT --}}
    <div class="bg-slate-950 py-6 border-t border-slate-800">
        <div class="container mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500">
            <div>
                &copy; {{ date('Y') }}
                @if($siteTitle)
                    <strong>{{ $siteTitle }}</strong>.
                @endif
                All rights reserved.
            </div>
            @if($siteTitle)
            <div class="flex items-center gap-1">
                Powered by <a href="{{ config('app.url') }}" class="text-slate-400 hover:text-white font-bold ml-1">{{ $siteTitle }}</a>
                @if($siteIntro)
                    <span class="ml-1">- {{ $siteIntro }}</span>
                @endif
            </div>
            @endif
        </div>
    </div>
</footer>
