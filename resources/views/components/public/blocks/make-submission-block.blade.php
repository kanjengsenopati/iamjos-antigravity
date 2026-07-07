@props(['journal', 'block'])
@php
    $primaryColor = $journal->getWebsiteSettings()['primary_color'] ?? '#0369a1';
@endphp

<div class="text-sm text-slate-600 space-y-3">
    <p>
        {{ __('Interested in submitting to this journal? We recommend that you review the') }} 
        <a href="{{ route('journal.public.about', $journal->slug) }}" class="text-primary-600 hover:underline">{{ __('About the Journal') }}</a> 
        {{ __('page for the journal\'s section policies, as well as the') }} 
        <a href="{{ route('journal.public.author-guidelines', $journal->slug) }}" class="text-primary-600 hover:underline">{{ __('Author Guidelines') }}</a>.
    </p>

    <p>
        {{ __('Authors need to') }} 
        <a href="{{ route('register') }}" class="text-primary-600 hover:underline">{{ __('register') }}</a> 
        {{ __('with the journal prior to submitting or, if already registered, can simply') }} 
        <a href="{{ route('login') }}" class="text-primary-600 hover:underline">{{ __('log in') }}</a> 
        {{ __('and begin the five-step process.') }}
    </p>
    
    <a href="{{ route('journal.submissions.create', $journal->slug) }}" 
       class="block w-full text-center text-white font-medium py-2 px-4 rounded transition shadow-sm mt-2 transition-colors hover:brightness-110"
       style="background: {{ $primaryColor }};">
        {{ __('Submit Article') }}
    </a>
</div>
