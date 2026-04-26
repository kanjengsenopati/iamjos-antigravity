@php
  $e = function ($string) {
    if (empty($string)) return '';
    $decoded = html_entity_decode(trim($string), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $plain   = strip_tags($decoded);
    $plain   = preg_replace('/\s+/', ' ', $plain);
    return htmlspecialchars(trim($plain), ENT_XML1 | ENT_QUOTES, 'UTF-8');
  };

  $eRaw = function ($string) {
    if (empty($string)) return '';
    $decoded = htmlspecialchars_decode(trim($string), ENT_QUOTES);
    return htmlspecialchars($decoded, ENT_XML1 | ENT_QUOTES, 'UTF-8');
  };

  $splitPages = function ($pages) {
    $parts = explode('-', (string) $pages, 2);
    return [trim($parts[0] ?? ''), trim($parts[1] ?? '')];
  };

  $totalArticles = $submissions->count();

  // Derive issue publicationDate from first article
  $firstArticle = $submissions->first();
  $issueDate = '';
  if ($firstArticle) {
    if ($firstArticle->currentPublication?->date_published) {
      $issueDate = $firstArticle->currentPublication->date_published->format('Y-m-d');
    } elseif ($firstArticle->published_at) {
      $issueDate = $firstArticle->published_at->format('Y-m-d');
    } elseif ($firstArticle->issue?->published_at) {
      $issueDate = $firstArticle->issue->published_at->format('Y-m-d');
    }
  }
@endphp
<ici-import xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://journals.indexcopernicus.com/ic-import.xsd">
  <journal issn="{{ $journal->issn_online }}" />
  @foreach($submissions as $article)
    @php
      $pub         = $article->currentPublication;
      $artTitle    = $eRaw($pub?->title ?? $article->title ?? '');
      $abstract    = $e($pub?->abstract ?? $article->abstract ?? '');
      $doi         = $eRaw($pub?->doi ?? $article->doi ?? '');
      $lang        = $article->language ?? 'en';
      $pages       = $pub?->pages ?? $article->pages ?? '';
      [$pageFrom, $pageTo] = $splitPages($pages);

      // publicationDate for this article
      $pubDate = '';
      if ($pub?->date_published) {
        $pubDate = $pub->date_published->format('Y-m-d');
      } elseif ($article->published_at) {
        $pubDate = $article->published_at->format('Y-m-d');
      } elseif ($article->issue?->published_at) {
        $pubDate = $article->issue->published_at->format('Y-m-d');
      }

      // Keywords
      if ($pub && !empty($pub->keywords)) {
        $keywordsList = $pub->keywords_array;
      } else {
        $keywordsList = $article->keywords?->pluck('content')->toArray() ?? [];
      }

      // References
      $referencesStr   = $pub?->references ?? $article->references ?? '';
      $referencesArray = $referencesStr
        ? array_values(array_filter(array_map('trim', explode("\n", $referencesStr))))
        : [];

      // Issue attrs
      $issueNumber = $article->issue?->number ?? '';
      $issueVolume = $article->issue?->volume ?? '';
      $issueYear   = $article->issue?->year   ?? '';
      $issueDate2  = '';
      if ($article->issue?->published_at) {
        $issueDate2 = $article->issue->published_at->format('Y-m-d');
      }
    @endphp
    <issue number="{{ $issueNumber }}" volume="{{ $issueVolume }}" year="{{ $issueYear }}" publicationDate="{{ $issueDate2 }}" numberOfArticles="{{ $totalArticles }}" />
    <article>
      <type>ORIGINAL_ARTICLE</type>
      <languageVersion language="{{ $lang }}">
        <title>{!! $artTitle !!}</title>
        <abstract>{!! $abstract !!}</abstract>
        <publicationDate>{{ $pubDate }}</publicationDate>
        <doi>{!! $doi !!}</doi>
        <keywords language="{{ $lang }}">
          @foreach($keywordsList as $keyword)
            <keyword>{!! $e($keyword) !!}</keyword>
          @endforeach
        </keywords>
        @if ($pageFrom)
          <pageFrom>{{ $pageFrom }}</pageFrom>
        @endif
        @if ($pageTo)
          <pageTo>{{ $pageTo }}</pageTo>
        @endif
      </languageVersion>
      <authors>
        @foreach($article->authors as $index => $author)
          @php
            $fullName = trim(($author->first_name ?? $author->given_name ?? '') . ' ' . ($author->last_name ?? $author->family_name ?? ''));
            if (empty($fullName)) $fullName = $author->name ?? '';
          @endphp
          <author>
            <name>{!! $eRaw($fullName) !!}</name>
            <surname/>
            <email>{!! $eRaw($author->email ?? '') !!}</email>
            <order>{{ $index + 1 }}</order>
            <instituteAffiliation>{!! $e($author->affiliation ?? '') !!}</instituteAffiliation>
            <role>AUTHOR</role>
          </author>
        @endforeach
      </authors>
      <references>
        @foreach($referencesArray as $refIndex => $reference)
          <reference>
            <unparsedContent>{!! $e($reference) !!}</unparsedContent>
            <order>{{ $refIndex + 1 }}</order>
            <doi/>
          </reference>
        @endforeach
      </references>
    </article>
  @endforeach
</ici-import>
