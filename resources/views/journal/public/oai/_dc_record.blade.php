{{--
  OAI-PMH Dublin Core Record Partial
  Shared by list_records.blade.php and get_record.blade.php
  OAI-PMH 2.0 + Dublin Core compliance
  Variables: $record (Submission), $journal (Journal)
--}}
@php
  // BCP47 locale (id_ID → id, en_US → en)
  $oaiLocale = preg_replace('/_[A-Z]{2}$/', '', $record->locale ?? 'en');

  // Stable OAI identifier — includes journal slug to ensure global uniqueness
  $oaiIdentifier = 'oai:' . parse_url(config('app.url'), PHP_URL_HOST)
                 . ':' . $journal->slug . '/article/' . $record->seq_id;

  // Datestamp — use published_at for accuracy; fallback to updated_at
  $datestamp = ($record->published_at ?? $record->updated_at)->utc()->format('Y-m-d\TH:i:s\Z');

  // Publication date (ISO 8601 date only)
  $pubDate = null;
  if ($record->currentPublication && $record->currentPublication->date_published) {
    $pubDate = \Carbon\Carbon::parse($record->currentPublication->date_published)->format('Y-m-d');
  }

  // dc:source with ISSN (best practice for Scopus/BASE harvesting)
  $sourceInfo = $journal->name;
  if ($journal->issn_online) {
    $sourceInfo .= '; e-ISSN: ' . $journal->issn_online;
  } elseif ($journal->issn_print) {
    $sourceInfo .= '; p-ISSN: ' . $journal->issn_print;
  }
  if ($record->issue) {
    $sourceInfo .= '; Vol. ' . ($record->issue->volume ?? '')
                 . ' No. ' . ($record->issue->number ?? '')
                 . ' (' . ($record->issue->year ?? '') . ')';
  }

  // Stable PDF URL — use galley route, not SEO filename route
  $pdfGalley = $record->galleys->first();
  $pdfUrl    = $pdfGalley
    ? route('journal.article.galley', [
        'journal' => $journal->slug,
        'article' => $record->seq_id,
        'galley'  => $pdfGalley->seq_id ?? $pdfGalley->id,
      ])
    : null;

  // Abstract: decode HTML entities, strip tags, normalise whitespace
  $abstractClean = '';
  if ($record->abstract) {
    $decoded       = html_entity_decode($record->abstract, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $abstractClean = trim(preg_replace('/\s+/', ' ', strip_tags($decoded)));
  }
@endphp
<header>
  <identifier>{{ $oaiIdentifier }}</identifier>
  <datestamp>{{ $datestamp }}</datestamp>
  <setSpec>{{ strtoupper($journal->abbreviation ?? 'JRN') }}:ART</setSpec>
</header>
<metadata>
  <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">

    {{-- dc:title --}}
    <dc:title>{{ htmlspecialchars($record->title, ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:title>

    {{-- dc:creator — one element per author --}}
    @if (isset($record->authors) && $record->authors->isNotEmpty())
      @foreach ($record->authors as $author)
        @php $creatorName = trim(($author->first_name ?? '') . ' ' . ($author->last_name ?? '')); @endphp
        @if ($creatorName)
          <dc:creator>{{ htmlspecialchars($creatorName, ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:creator>
        @endif
      @endforeach
    @endif

    {{-- dc:subject — one element per keyword --}}
    @if (isset($record->keywords) && $record->keywords->isNotEmpty())
      @foreach ($record->keywords as $kw)
        @php $kwText = trim($kw->content ?? $kw->keyword ?? $kw->name ?? ''); @endphp
        @if ($kwText)
          <dc:subject>{{ htmlspecialchars($kwText, ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:subject>
        @endif
      @endforeach
    @endif

    {{-- dc:description — abstract, no HTML --}}
    @if ($abstractClean)
      <dc:description>{{ htmlspecialchars($abstractClean, ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:description>
    @endif

    {{-- dc:publisher --}}
    <dc:publisher>{{ htmlspecialchars($journal->publisher ?? $journal->name, ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:publisher>

    {{-- dc:date — ISO 8601, no whitespace inside tag --}}
    @if ($pubDate)
      <dc:date>{{ $pubDate }}</dc:date>
    @endif

    {{-- dc:type — info:eu-repo semantics for BASE/OpenDOAR --}}
    <dc:type>info:eu-repo/semantics/article</dc:type>
    <dc:type>info:eu-repo/semantics/publishedVersion</dc:type>

    {{-- dc:format --}}
    <dc:format>application/pdf</dc:format>
    @if ($record->currentPublication && $record->currentPublication->pages)
      <dc:format>{{ htmlspecialchars($record->currentPublication->pages, ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:format>
    @endif

    {{-- dc:identifier — DOI (preferred), then article URL --}}
    @if ($record->currentPublication && $record->currentPublication->doi)
      <dc:identifier>{{ htmlspecialchars('https://doi.org/' . $record->currentPublication->doi, ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:identifier>
      <dc:identifier>{{ htmlspecialchars('doi:' . $record->currentPublication->doi, ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:identifier>
    @endif
    <dc:identifier>{{ htmlspecialchars(route('journal.public.article', ['journal' => $journal->slug, 'article' => $record->seq_id]), ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:identifier>

    {{-- dc:source — journal name + ISSN + volume/issue --}}
    <dc:source>{{ htmlspecialchars($sourceInfo, ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:source>

    {{-- dc:language — BCP47/ISO 639-1 (id, en, etc.) --}}
    <dc:language>{{ $oaiLocale }}</dc:language>

    {{-- dc:relation — stable PDF URL for Google Scholar / BASE --}}
    @if ($pdfUrl)
      <dc:relation>{{ htmlspecialchars($pdfUrl, ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:relation>
    @endif

    {{-- dc:rights — license URL preferred over terms text --}}
    @if ($journal->license_url)
      <dc:rights>{{ htmlspecialchars($journal->license_url, ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:rights>
    @elseif ($journal->license_terms)
      <dc:rights>{{ htmlspecialchars($journal->license_terms, ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:rights>
    @endif

  </oai_dc:dc>
</metadata>
