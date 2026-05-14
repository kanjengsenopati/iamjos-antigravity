@php
  $escape = function ($string) {
    if (empty($string)) return '';
    $decoded = htmlspecialchars_decode(trim($string), ENT_QUOTES);
    return htmlspecialchars($decoded, ENT_XML1 | ENT_QUOTES, 'UTF-8');
  };
@endphp
<resource xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://datacite.org/schema/kernel-4" xsi:schemaLocation="http://datacite.org/schema/kernel-4 http://schema.datacite.org/meta/kernel-4.3/metadata.xsd">
@foreach($submissions as $article)
    @php
        $pub = $article->currentPublication;
    @endphp
    @if($pub && $pub->doi)
    <identifier identifierType="DOI">{{ $pub->doi }}</identifier>
    <creators>
        @foreach($pub->authors as $author)
        <creator>
            <creatorName>{!! $escape($author->family_name . ', ' . $author->given_name) !!}</creatorName>
            @if($author->orcid)
            <nameIdentifier schemeURI="http://orcid.org/" nameIdentifierScheme="ORCID">{{ preg_replace('/^https?:\/\/orcid.org\//', '', $author->orcid) }}</nameIdentifier>
            @endif
            <affiliation>{!! $escape($author->affiliation) !!}</affiliation>
        </creator>
        @endforeach
    </creators>
    <titles>
        <title>{!! $escape(strip_tags($pub->title)) !!}</title>
    </titles>
    <publisher>{!! $escape($journal->publisher ?? $journal->name) !!}</publisher>
    <publicationYear>{{ $article->published_at?->year }}</publicationYear>
    <resourceType resourceTypeGeneral="Text">Journal Article</resourceType>
    <descriptions>
        <description descriptionType="Abstract">{!! $escape(strip_tags($pub->abstract)) !!}</description>
    </descriptions>
    @endif
@endforeach
</resource>
