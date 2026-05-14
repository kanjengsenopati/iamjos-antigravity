@php
  $escape = function ($string) {
    if (empty($string)) return '';
    $decoded = htmlspecialchars_decode(trim($string), ENT_QUOTES);
    return htmlspecialchars($decoded, ENT_XML1 | ENT_QUOTES, 'UTF-8');
  };
@endphp
<ArticleSet>
@foreach($submissions as $article)
    @php
        $pub = $article->currentPublication;
    @endphp
    @if($pub)
    <Article>
        <Journal>
            <PublisherName>{!! $escape($journal->publisher ?? $journal->name) !!}</PublisherName>
            <JournalTitle>{!! $escape($journal->name) !!}</JournalTitle>
            <Issn>{{ $journal->issn_online ?: $journal->issn_print }}</Issn>
            <Volume>{{ $article->issue->volume ?? '' }}</Volume>
            <Issue>{{ $article->issue->number ?? '' }}</Issue>
            <PubDate PubStatus="ppublish">
                <Year>{{ $article->issue->year ?? $article->published_at?->year }}</Year>
                <Month>{{ $article->issue->month ?? $article->published_at?->month }}</Month>
            </PubDate>
        </Journal>
        <ArticleTitle>{!! $escape(strip_tags($pub->title)) !!}</ArticleTitle>
        <FirstPage>{{ explode('-', $pub->pages)[0] ?? '' }}</FirstPage>
        <LastPage>{{ explode('-', $pub->pages)[1] ?? '' }}</LastPage>
        <Language>{{ $pub->locale ?? 'EN' }}</Language>
        <AuthorList>
            @foreach($pub->authors as $author)
            <Author>
                <FirstName>{!! $escape($author->given_name) !!}</FirstName>
                <LastName>{!! $escape($author->family_name) !!}</LastName>
                <Affiliation>{!! $escape($author->affiliation) !!}</Affiliation>
            </Author>
            @endforeach
        </AuthorList>
        <Abstract>{!! $escape(strip_tags($pub->abstract)) !!}</Abstract>
        @if($pub->doi)
        <OtherID Source="doi">{{ $pub->doi }}</OtherID>
        @endif
    </Article>
    @endif
@endforeach
</ArticleSet>
