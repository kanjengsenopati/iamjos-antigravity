@php
  $escape = function ($string) {
    if (empty($string)) return '';
    $decoded = htmlspecialchars_decode(trim($string), ENT_QUOTES);
    return htmlspecialchars($decoded, ENT_XML1 | ENT_QUOTES, 'UTF-8');
  };

  $splitPages = function ($pages) {
    if (empty($pages)) return ['', ''];
    $parts = explode('-', $pages, 2);
    return [trim($parts[0] ?? ''), trim($parts[1] ?? '')];
  };

  // Intensive abstract cleaning: decode HTML entities first, then strip tags,
  // then normalise whitespace before XML-escaping.
  $cleanAbstract = function ($string) use ($escape) {
    if (empty($string)) return '';
    // 1. Decode all HTML entities (e.g. &nbsp; -> space, &amp; -> &)
    $decoded = html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // 2. Strip any remaining HTML/XML tags
    $plain = strip_tags($decoded);
    // 3. Normalise whitespace (replace multiple spaces/newlines with a single space)
    $plain = preg_replace('/\s+/', ' ', $plain);
    // 4. XML-encode for safe embedding
    return $escape(trim($plain));
  };
@endphp
<doi_batch xmlns="http://www.crossref.org/schema/4.3.6" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  version="4.3.6"
  xsi:schemaLocation="http://www.crossref.org/schema/4.3.6 https://www.crossref.org/schemas/crossref4.3.6.xsd">

  <head>
    <doi_batch_id>{{ $batchId }}</doi_batch_id>
    <timestamp>{{ now()->format('YmdHis') . '000' }}</timestamp>
    <depositor>
      <depositor_name>{!! $escape($journal->getSetting('crossref_depositor_name') ?: $journal->name) !!}</depositor_name>
      <email_address>{!! $escape($journal->getSetting('crossref_depositor_email') ?: ($journal->email ?: 'admin@example.com')) !!}</email_address>
    </depositor>
    <registrant>{!! $escape($journal->publisher ?? $journal->name) !!}</registrant>
  </head>

  <body>
    <journal>
      <journal_metadata>
        <full_title>{!! $escape($journal->name) !!}</full_title>
        <abbrev_title>{!! $escape($journal->abbreviation ?? $journal->name) !!}</abbrev_title>
        @if ($journal->issn_online)
          <issn media_type="electronic">{{ $journal->issn_online }}</issn>
        @endif
        @if ($journal->issn_print)
          <issn media_type="print">{{ $journal->issn_print }}</issn>
        @endif
      </journal_metadata>

      {{-- ISSUE DATA (taken from first selected article) --}}
      @php $firstItem = $submissions->first(); @endphp
      @if ($firstItem && $firstItem->issue)
        <journal_issue>
          <publication_date media_type="online">
            <month>{{ $firstItem->issue->month ?? date('m') }}</month>
            <day>{{ $firstItem->issue->day ?? date('d') }}</day>
            <year>{{ $firstItem->issue->year ?? date('Y') }}</year>
          </publication_date>
          @if ($firstItem->issue->volume)
            <journal_volume>
              <volume>{{ $firstItem->issue->volume }}</volume>
            </journal_volume>
          @endif
          @if ($firstItem->issue->number)
            <issue>{{ $firstItem->issue->number }}</issue>
          @endif
        </journal_issue>
      @endif

      {{-- ARTICLES LOOP --}}
      @foreach ($submissions as $article)
        @php
          $pub = $article->currentPublication;
        @endphp
        @if ($pub)
          <journal_article
            xmlns:jats="http://www.ncbi.nlm.nih.gov/JATS1"
            xmlns:ai="http://www.crossref.org/AccessIndicators.xsd"
            publication_type="full_text"
            metadata_distribution_opts="any">

            <titles>
              <title>{!! $escape(strip_tags($pub->title)) !!}</title>
            </titles>

            <contributors>
              @foreach ($pub->authors as $index => $author)
                @php
                  $fullName = trim(($author->first_name ?? '') . ' ' . ($author->last_name ?? ''));
                @endphp
                <person_name contributor_role="author" sequence="{{ $index === 0 ? 'first' : 'additional' }}">
                  @if (!empty($author->first_name))
                    <given_name>{!! $escape($author->first_name) !!}</given_name>
                  @endif
                  <surname>{!! $escape($author->last_name ?: $author->first_name) !!}</surname>
                  @if ($author->orcid)
                    @php $cleanOrcid = preg_replace('/^https?:\/\/(www\.)?orcid\.org\//', '', trim($author->orcid)); @endphp
                    <ORCID authenticated="true">https://orcid.org/{{ $cleanOrcid }}</ORCID>
                  @elseif ($author->user && $author->user->orcid_id)
                    @php $cleanOrcid = preg_replace('/^https?:\/\/(www\.)?orcid\.org\//', '', trim($author->user->orcid_id)); @endphp
                    <ORCID authenticated="true">https://orcid.org/{{ $cleanOrcid }}</ORCID>
                  @endif
                </person_name>
              @endforeach
            </contributors>

            @if ($pub->abstract)
              <jats:abstract>
                <jats:p>{!! $cleanAbstract($pub->abstract) !!}</jats:p>
              </jats:abstract>
            @endif

            <publication_date media_type="online">
              @if ($pub->date_published)
                <month>{{ $pub->date_published->format('m') }}</month>
                <day>{{ $pub->date_published->format('d') }}</day>
                <year>{{ $pub->date_published->format('Y') }}</year>
              @endif
            </publication_date>

            {{-- Pages: split into first_page / last_page --}}
            @if ($pub->pages)
              @php [$firstPage, $lastPage] = $splitPages($pub->pages); @endphp
              <pages>
                <first_page>{{ $firstPage }}</first_page>
                @if ($lastPage)
                  <last_page>{{ $lastPage }}</last_page>
                @endif
              </pages>
            @endif

            {{-- Access Indicators --}}
            <ai:program name="AccessIndicators">
              <ai:license_ref>https://creativecommons.org/licenses/by/4.0</ai:license_ref>
            </ai:program>

            <doi_data>
              @php
                $doi = $pub->doi
                  ? trim($pub->doi)
                  : ($journal->doi_prefix
                      ? trim($journal->doi_prefix) . '/' . trim($journal->path) . '.v' . ($article->issue->volume ?? '0') . 'i' . ($article->issue->number ?? '0') . '.' . $article->id
                      : '10.xxxx/' . trim($journal->path) . '.v' . ($article->issue->volume ?? '0') . 'i' . ($article->issue->number ?? '0') . '.' . $article->id);
                $articleUrl = trim(route('journal.public.article', ['journal' => $journal->slug, 'article' => $pub->seq_id ?? $article->seq_id]));

                // Resolve PDF galley URL for iParadigms crawler.
                // Prefer a galley with label 'PDF'; fall back to the first available galley.
                $galleys = $article->galleys ?? collect();
                $pdfGalley = $galleys->first(fn($g) => strtolower($g->label) === 'pdf')
                           ?? $galleys->first();
                $pdfUrl = $pdfGalley?->seo_download_url ?? $articleUrl;
              @endphp
              <doi>{{ $doi }}</doi>
              <resource>{{ $articleUrl }}</resource>
              <collection property="crawler-based">
                <item crawler="iParadigms">
                  <resource>{{ $pdfUrl }}</resource>
                </item>
              </collection>
            </doi_data>

            @if ($pub->parsed_references && count($pub->parsed_references) > 0)
              <citation_list>
                @foreach ($pub->parsed_references as $index => $ref)
                  <citation key="ref{{ $index + 1 }}">
                    <unstructured_citation>{!! $escape(strip_tags(html_entity_decode($ref))) !!}</unstructured_citation>
                  </citation>
                @endforeach
              </citation_list>
            @endif

          </journal_article>
        @endif
      @endforeach

    </journal>
  </body>
</doi_batch>
