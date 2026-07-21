@props(['journal', 'publication'])

@php
    // Priority: Publication level license_url -> Journal level license_url -> Default CC BY 4.0
    $licenseUrl = $publication?->license_url ?? $journal->license_url ?? 'https://creativecommons.org/licenses/by/4.0/';
    $licenseTerms = $journal->license_terms;
    $holderType = $journal->copyright_holder_type ?? 'author';
    $holderOther = $journal->copyright_holder_other;

    // Determine Copyright Holder
    $copyrightHolder = null;
    if ($publication && !empty($publication->copyright_holder)) {
        $copyrightHolder = $publication->copyright_holder;
    } elseif ($holderType === 'author' && $publication && $publication->authors->isNotEmpty()) {
        $authorNames = $publication->authors->map(function ($author) {
            $name = trim(($author->first_name ?? '') . ' ' . ($author->last_name ?? ''));
            return !empty($name) ? $name : ($author->preferred_public_name ?? $author->name);
        })->filter()->values();
        
        $copyrightHolder = $authorNames->isNotEmpty() ? $authorNames->join(', ') : ($journal->publisher ?? $journal->name);
    } elseif ($holderType === 'context') {
        $copyrightHolder = $journal->publisher ?? $journal->name;
    } elseif ($holderType === 'other' && !empty($holderOther)) {
        $copyrightHolder = $holderOther;
    }

    if (empty($copyrightHolder)) {
        $copyrightHolder = $journal->publisher ?? $journal->name;
    }

    // Determine Copyright Year
    $copyrightYear = null;
    if ($publication && !empty($publication->copyright_year)) {
        $copyrightYear = $publication->copyright_year;
    } elseif (($journal->copyright_year_basis ?? 'issue') === 'issue' && $publication?->issue?->year) {
        $copyrightYear = $publication->issue->year;
    } elseif ($publication && $publication->date_published) {
        $copyrightYear = date('Y', strtotime($publication->date_published));
    } else {
        $copyrightYear = date('Y');
    }

    // Determine CC Badge & Proper Title Formatting
    $ccBadgeUrl = null;
    $ccTitle = "Creative Commons License";
    if ($licenseUrl && str_contains(strtolower($licenseUrl), 'creativecommons.org')) {
        if (preg_match('/licenses\/(by-nc-nd|by-nc-sa|by-nc|by-nd|by-sa|by)\/(\d\.\d)/i', $licenseUrl, $matches)) {
            $licenseType = strtolower($matches[1]);
            $licenseVersion = $matches[2];
            $ccBadgeUrl = "https://i.creativecommons.org/l/{$licenseType}/{$licenseVersion}/88x31.png";
            
            $ccMap = [
                'by' => 'Creative Commons Attribution',
                'by-sa' => 'Creative Commons Attribution-ShareAlike',
                'by-nc' => 'Creative Commons Attribution-NonCommercial',
                'by-nc-sa' => 'Creative Commons Attribution-NonCommercial-ShareAlike',
                'by-nc-nd' => 'Creative Commons Attribution-NonCommercial-NoDerivatives',
                'by-nd' => 'Creative Commons Attribution-NoDerivatives',
            ];
            
            $typeName = $ccMap[$licenseType] ?? ("Creative Commons " . strtoupper($licenseType));
            $ccTitle = "{$typeName} {$licenseVersion} International License";
        } elseif (str_contains(strtolower($licenseUrl), 'publicdomain/zero')) {
            $ccBadgeUrl = "https://i.creativecommons.org/p/zero/1.0/88x31.png";
            $ccTitle = "Creative Commons CC0 1.0 Universal Public Domain Dedication";
        }
    }
@endphp

<div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 shadow-sm">
    <h4 class="font-bold text-slate-700 text-xs uppercase mb-3 tracking-wider flex items-center gap-2">
        <i class="fa-solid fa-certificate text-slate-400"></i>
        License
    </h4>
    
    <div class="text-sm text-slate-700 space-y-3">
        <p class="font-medium text-slate-800">
            Copyright (c) {{ $copyrightYear }} {{ $copyrightHolder }}
        </p>

        @if($ccBadgeUrl)
            <div class="my-2">
                <a rel="license" href="{{ $licenseUrl }}" target="_blank" class="inline-block">
                    <img src="{{ $ccBadgeUrl }}" alt="{{ $ccTitle }}" class="h-8 rounded shadow-xs hover:opacity-90 transition-opacity">
                </a>
            </div>
        @endif
        
        @if($licenseUrl)
            <p class="text-xs leading-relaxed text-slate-600">
                This work is licensed under a 
                <a rel="license" href="{{ $licenseUrl }}" target="_blank" class="text-primary-600 hover:text-primary-800 hover:underline font-semibold">
                    {{ $ccTitle }}
                </a>.
            </p>
        @endif
        
        @if($licenseTerms)
            <div class="mt-2 text-xs text-slate-500 leading-relaxed border-t border-slate-200 pt-2">
                {!! nl2br(e($licenseTerms)) !!}
            </div>
        @endif
    </div>
</div>

