@php
  $escape = function ($string) {
    if (empty($string)) return '';
    $decoded = htmlspecialchars_decode(trim($string), ENT_QUOTES);
    return htmlspecialchars($decoded, ENT_XML1 | ENT_QUOTES, 'UTF-8');
  };
@endphp
<records>
@foreach($submissions as $article)
    @php
        $pub = $article->currentPublication;
    @endphp
    @if($pub)
    <record>
        <language>{{ $pub->locale ?? 'eng' }}</language>
        <publisher>{!! $escape($journal->publisher ?? $journal->name) !!}</publisher>
        <journalTitle>{!! $escape($journal->name) !!}</journalTitle>
        <issn>{{ $journal->issn_online ?: $journal->issn_print }}</issn>
        <publicationDate>{{ $article->published_at?->format('Y-m-d') }}</publicationDate>
        <volume>{{ $article->issue->volume ?? '' }}</volume>
        <issue>{{ $article->issue->number ?? '' }}</issue>
        <startPage>{{ explode('-', $pub->pages)[0] ?? '' }}</startPage>
        <endPage>{{ explode('-', $pub->pages)[1] ?? '' }}</endPage>
        <doi>{{ $pub->doi ?? '' }}</doi>
        <publisherRecordId>{{ $article->id }}</publisherRecordId>
        <documentType>article</documentType>
        <title language="{{ $pub->locale ?? 'eng' }}">{!! $escape(strip_tags($pub->title)) !!}</title>
        <authors>
            @foreach($pub->authors as $author)
            <author>
                <name>{!! $escape($author->name) !!}</name>
                <affiliationId>0</affiliationId>
            </author>
            @endforeach
        </authors>
        <affiliationsList>
            <affiliationName affinityId="0">{!! $escape($pub->authors->first()->affiliation ?? '') !!}</affiliationName>
        </affiliationsList>
        <abstract language="{{ $pub->locale ?? 'eng' }}">{!! $escape(strip_tags($pub->abstract)) !!}</abstract>
        <fullTextUrl format="pdf">{{ route('journal.public.article', ['journal' => $journal->slug, 'article' => $article->id]) }}</fullTextUrl>
    </record>
    @endif
@endforeach
</records>
