@php $title = __('Editorial Team'); @endphp

<x-layouts.public :journal="$journal" :settings="$settings" :title="$title">

    <section class="bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            {{-- BREADCRUMB --}}
            <nav class="text-sm text-slate-500 mb-6">
                <a href="{{ route('journal.public.home', $journal->slug) }}"
                   class="hover:text-primary-600">
                    {{ __('Home') }}
                </a>
                <span class="mx-2">/</span>
                <span class="text-slate-700 font-medium">{{ __('Editorial Team') }}</span>
            </nav>

            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('Editorial Team') }}</h1>

            {{-- Editorial Team Description (HTML from Settings) --}}
            @php
                $editorialTeamContent = $journal?->settings['masthead']['editorial_team'] ?? null;
            @endphp
            @if(!empty($editorialTeamContent))
            <div class="prose prose-lg max-w-none">
                {!! clean($editorialTeamContent) !!}
            </div>
            @else
            {{-- No custom editorial team content set --}}
            <div class="text-center py-12">
                <div class="text-gray-500">
                    <i class="fa-solid fa-info-circle text-4xl mb-4"></i>
                    <p>{{ __('Editorial team content has not been configured yet.') }}</p>
                </div>
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>