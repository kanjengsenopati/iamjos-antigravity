@props(['journal', 'publication'])

@php
    $licenseUrl = $journal->license_url;
    $licenseTerms = $journal->license_terms;
    $holderType = $journal->copyright_holder_type;
    $holderOther = $journal->copyright_holder_other;

    // Determine Copyright Holder
    $copyrightHolder = $journal->name; // Default fallback
    if ($holderType === 'author' && $publication && $publication->authors->isNotEmpty()) {
        $firstAuthor = $publication->authors->first();
        $copyrightHolder = trim(($firstAuthor->first_name ?? '') . ' ' . ($firstAuthor->last_name ?? ''));
    } elseif ($holderType === 'context') {
        $copyrightHolder = $journal->name;
    } elseif ($holderType === 'other' && !empty($holderOther)) {
        $copyrightHolder = $holderOther;
    }

    // Determine Copyright Year
    $copyrightYear = date('Y');
    if ($publication && $publication->date_published) {
        $copyrightYear = date('Y', strtotime($publication->date_published));
    }

    // Determine CC Badge
    $ccBadgeUrl = null;
    $ccTitle = "Creative Commons License";
    if ($licenseUrl && str_contains(strtolower($licenseUrl), 'creativecommons.org/licenses')) {
        if (preg_match('/licenses\/(by-nc-nd|by-nc-sa|by-nc|by-nd|by-sa|by)\/(\d\.\d)/i', $licenseUrl, $matches)) {
            $licenseType = strtolower($matches[1]);
            $licenseVersion = $matches[2];
            $ccBadgeUrl = "https://licensebuttons.net/l/{$licenseType}/{$licenseVersion}/88x31.png";
            $ccTitle = "Creative Commons Attribution " . strtoupper($licenseType) . " {$licenseVersion}";
        }
    }
@endphp

@if($licenseUrl || $licenseTerms)
<div class="article-license mt-8 p-4 bg-slate-50 border border-slate-200 rounded-lg">
    <div class="flex items-start space-x-4">
        @if($ccBadgeUrl)
        <div class="flex-shrink-0 mt-1">
            <a rel="license" href="{{ $licenseUrl }}" target="_blank">
                <img src="{{ $ccBadgeUrl }}" alt="{{ $ccTitle }}" class="h-8 shadow-sm">
            </a>
        </div>
        @endif
        
        <div class="text-sm text-slate-700">
            <p class="font-medium mb-1">
                Copyright &copy; {{ $copyrightYear }} {{ $copyrightHolder }}
            </p>
            
            @if($licenseUrl)
                <p>
                    This work is licensed under a 
                    <a rel="license" href="{{ $licenseUrl }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                        {{ $ccTitle }}
                    </a>.
                </p>
            @endif
            
            @if($licenseTerms)
                <div class="mt-2 prose prose-sm text-slate-600">
                    {!! nl2br(htmlspecialchars($licenseTerms)) !!}
                </div>
            @endif
        </div>
    </div>
</div>
@endif
