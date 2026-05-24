@php
  /**
   * Crossref XML Deposit — Schema 5.3.1
   * Reference: https://www.crossref.org/schemas/crossref5.3.1.xsd
   * Markup guide: https://www.crossref.org/documentation/schema-library/markup-guide-record-types/journals-and-articles/
   */

  // XML-safe escape: decode HTML entities first, then re-encode for XML
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

  // Clean abstract: decode HTML entities, strip tags, normalise whitespace, XML-escape
  $cleanAbstract = function ($string) use ($escape) {
    if (empty($string)) return '';
    $decoded = html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $plain   = strip_tags($decoded);
    $plain   = preg_replace('/\s+/', ' ', $plain);
    return $escape(trim($plain));
  };

  // BCP47 locale (id_ID → id)
  $toBcp47 = fn($locale) => preg_replace('/_[A-Z]{2}$/', '', $locale ?? 'en');

  // Detect DOI in a reference string and extract it
  $extractDoi = function ($ref) {
    if (preg_match('/\b(10\.\d{4,}\/\S+)/i', $ref, $m)) {
      return rtrim($m[1], '.,;)');
    }
    return null;
  };
@endphp
<doi_batch xmlns="http://www.crossref.org/schema/5.3.1"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  version="5.3.1"
  xsi:schemaLocation="http://www.crossref.org/schema/5.3.1 https://www.crossref.org/schemas/crossref5.3.1.xsd">

  <head>
    <doi_batch_id>{{ $batchId }}</doi_batch_id>
    {{-- timestamp must be incremented with each deposit; use microseconds for uniqueness --}}
    <timestamp>{{ now()->format('YmdHis') }}{{ str_pad(now()->micro, 6, '0', STR_PAD_LEFT) }}</timestamp>
    <depositor>
      <depositor_name>{!! $escape($journal->getSetting('crossref_depositor_name') ?: $journal->name) !!}</depositor_name>
      <email_address>{!! $escape($journal->getSetting('crossref_depositor_email') ?: ($journal->email ?? 'admin@example.com')) !!}</email_address>
    </depositor>
    <registrant>{!! $escape($journal->publisher ?? $journal->name) !!}</registrant>
  </head>

  <body>
    <journal>
      {{-- language attribute recommended by Crossref for multilingual journals --}}
      <journal_metadata language="{{ $toBcp47($submissions->first()?->locale ?? 'en') }}">
        <full_title>{!! $escape($journal->name) !!}</full_title>
        @if ($journal->abbreviation)
          <abbrev_title>{!! $escape($journal->abbreviation) !!}</abbrev_title>
        @endif
        @if ($journal->issn_online)
          <issn media_type="electronic">{{ $journal->issn_online }}</issn>
        @endif
        @if ($journal->issn_print)
          <issn media_type="print">{{ $journal->issn_print }}</issn>
        @endif
      </journal_metadata>

      {{-- ISSUE DATA — only year is required; month/day are optional --}}
      @php $firstItem = $submissions->first(); @endphp
      @if ($firstItem && $firstItem->issue)
        @php
          $issueDate = $firstItem->issue->published_at ?? null;
        @endphp
        <journal_issue>
          <publication_date media_type="online">
            @if ($issueDate)
              <month>{{ $issueDate->format('m') }}</month>
              <day>{{ $issueDate->format('d') }}</day>
            @endif
            <year>{{ $firstItem->issue->year ?? ($issueDate ? $issueDate->format('Y') : date('Y')) }}</year>
          </publication_date>
          @if ($firstItem->issue->volume)
            <journal_volume>
              <volume>{{ $firstItem->issue->volume }}</volume>
            </journal_volume>
          @endif
          @if ($firstItem->issue->number)
            <issue>{{ $firstItem->issue->number }}</issue>
          @endif
          @if (!empty($firstItem->issue->doi))
            <doi_data>
              <doi>{{ $firstItem->issue->doi }}</doi>
              <resource>{{ route('journal.public.issue', ['journal' => $journal->slug, 'issue' => $firstItem->issue->seq_id ?? $firstItem->issue->id]) }}</resource>
            </doi_data>
          @endif
        </journal_issue>
      @endif

      {{-- ARTICLES LOOP --}}
      @foreach ($submissions as $article)
        @php $pub = $article->currentPublication; @endphp
        @if ($pub)
          <journal_article
            xmlns:jats="http://www.ncbi.nlm.nih.gov/JATS1"
            xmlns:ai="http://www.crossref.org/AccessIndicators.xsd"
            publication_type="full_text"
            metadata_distribution_opts="any">

            {{-- TITLES: include subtitle if available --}}
            <titles>
              <title>{!! $escape(strip_tags($pub->title)) !!}</title>
              @if ($pub->subtitle)
                <subtitle>{!! $escape(strip_tags($pub->subtitle)) !!}</subtitle>
              @endif
            </titles>

            {{-- CONTRIBUTORS: include affiliation for Scopus/WoS disambiguation --}}
            <contributors>
              @foreach ($pub->authors as $index => $author)
                @php
                  $givenName  = trim($author->first_name ?? '');
                  $familyName = trim($author->last_name ?? $author->first_name ?? '');
                  $cleanOrcid = null;
                  $rawOrcid   = $author->orcid ?? ($author->user->orcid_id ?? null);
                  if ($rawOrcid) {
                    $cleanOrcid = preg_replace('/^https?:\/\/(www\.)?orcid\.org\//', '', trim($rawOrcid));
                  }
                  $orcidVerified = !empty($author->orcid_verified) || !empty($author->user->orcid_verified);
                @endphp
                <person_name contributor_role="author" sequence="{{ $index === 0 ? 'first' : 'additional' }}">
                  @if ($givenName)
                    <given_name>{!! $escape($givenName) !!}</given_name>
                  @endif
                  <surname>{!! $escape($familyName) !!}</surname>
                  @if ($author->affiliation)
                    <affiliation>{!! $escape($author->affiliation) !!}</affiliation>
                  @endif
                  @if ($cleanOrcid)
                    <ORCID authenticated="{{ $orcidVerified ? 'true' : 'false' }}">https://orcid.org/{{ $cleanOrcid }}</ORCID>
                  @endif
                </person_name>
              @endforeach
            </contributors>

            {{-- ABSTRACT (JATS) --}}
            @if ($pub->abstract)
              <jats:abstract>
                <jats:p>{!! $cleanAbstract($pub->abstract) !!}</jats:p>
              </jats:abstract>
            @endif

            {{-- PUBLICATION DATE --}}
            @php $pubDateResolved = $pub->date_published ?? ($article->issue?->published_at ?? $article->published_at); @endphp
            @if ($pubDateResolved)
              <publication_date media_type="online">
                <month>{{ $pubDateResolved->format('m') }}</month>
                <day>{{ $pubDateResolved->format('d') }}</day>
                <year>{{ $pubDateResolved->format('Y') }}</year>
              </publication_date>
            @endif

            {{-- PAGES --}}
            @if ($pub->pages)
              @php [$firstPage, $lastPage] = $splitPages($pub->pages); @endphp
              <pages>
                <first_page>{{ $firstPage }}</first_page>
                @if ($lastPage)
                  <last_page>{{ $lastPage }}</last_page>
                @endif
              </pages>
            @endif

            {{-- ACCESS INDICATORS (license) --}}
            @php $licenseUrl = $pub->license_url ?? ($journal->license_url ?? 'https://creativecommons.org/licenses/by/4.0'); @endphp
            <ai:program name="AccessIndicators">
              <ai:license_ref>{{ $licenseUrl }}</ai:license_ref>
            </ai:program>

            {{-- FUNDING INFORMATION (Crossref Funder Registry) --}}
            {{-- Dibutuhkan oleh banyak funder (Kemendikbud, BRIN, dll.) untuk compliance --}}
            @php
              $fundingInfo = $pub->funding_info ?? [];
              // Pastikan array (bukan null atau string)
              if (!is_array($fundingInfo)) {
                $fundingInfo = [];
              }
            @endphp
            @if (!empty($fundingInfo))
              <fr:program xmlns:fr="http://www.crossref.org/fundref.xsd" name="fundref">
                @foreach ($fundingInfo as $funder)
                  @if (!empty($funder['funder_name']))
                    <fr:assertion name="fundgroup">
                      <fr:assertion name="funder_name">
                        {!! $escape($funder['funder_name']) !!}
                        @if (!empty($funder['funder_doi']))
                          <fr:assertion name="funder_identifier">{{ $escape($funder['funder_doi']) }}</fr:assertion>
                        @endif
                      </fr:assertion>
                      @if (!empty($funder['award_number']))
                        <fr:assertion name="award_number">{{ $escape($funder['award_number']) }}</fr:assertion>
                      @endif
                    </fr:assertion>
                  @endif
                @endforeach
              </fr:program>
            @endif

            {{-- DOI DATA --}}
            @php
              $doi = null;
              if (!empty($pub->doi)) {
                $doi = trim($pub->doi);
              } elseif (!empty($journal->doi_prefix) && str_starts_with($journal->doi_prefix, '10.')) {
                // Only auto-generate if prefix is valid — never use placeholder
                $doi = trim($journal->doi_prefix) . '/' . trim($journal->path)
                     . '.v' . ($article->issue->volume ?? '0')
                     . 'i' . ($article->issue->number ?? '0')
                     . '.' . $article->seq_id;
              }
              // Use $article->seq_id (Submission) — Publication does not have seq_id
              $articleUrl = route('journal.public.article', [
                'journal' => $journal->slug,
                'article' => $article->seq_id,
              ]);
              // PDF galley URL — use stable galley route
              $galleys    = $article->galleys ?? collect();
              $pdfGalley  = $galleys->first(fn($g) => strtolower($g->label ?? '') === 'pdf')
                          ?? $galleys->first();
              $pdfUrl     = $pdfGalley
                ? route('journal.article.galley', [
                    'journal' => $journal->slug,
                    'article' => $article->seq_id,
                    'galley'  => $pdfGalley->seq_id ?? $pdfGalley->id,
                  ])
                : $articleUrl;
            @endphp
            @if ($doi)
              <doi_data>
                <doi>{{ $doi }}</doi>
                <resource>{{ $articleUrl }}</resource>
                <collection property="crawler-based">
                  <item crawler="iParadigms">
                    <resource>{{ $pdfUrl }}</resource>
                  </item>
                </collection>
              </doi_data>
            @endif

            {{-- CITATION LIST --}}
            {{-- Key format: key-{doi}-{index} for uniqueness across deposits --}}
            {{-- Structured citations preferred; DOI extracted when present --}}
            @php
              $parsedRefs = $pub->parsed_references ?? [];
              $doiForKey  = $doi ?? $batchId;
            @endphp
            @if (count($parsedRefs) > 0)
              <citation_list>
                @foreach ($parsedRefs as $index => $ref)
                  @php
                    $refText    = trim($ref);
                    $refDoi     = $extractDoi($refText);
                    $citationKey = 'key-' . preg_replace('/[^a-zA-Z0-9\-]/', '-', $doiForKey) . '-' . ($index + 1);
                  @endphp
                  @if ($refText)
                    <citation key="{{ $citationKey }}">
                      @if ($refDoi)
                        {{-- Structured: DOI extracted — Crossref can match this precisely --}}
                        <doi>{{ $refDoi }}</doi>
                        <unstructured_citation>{!! $escape(strip_tags($refText)) !!}</unstructured_citation>
                      @else
                        {{-- Unstructured fallback — use $escape() only, no html_entity_decode() --}}
                        <unstructured_citation>{!! $escape(strip_tags($refText)) !!}</unstructured_citation>
                      @endif
                    </citation>
                  @endif
                @endforeach
              </citation_list>
            @endif

          </journal_article>
        @endif
      @endforeach

    </journal>
  </body>
</doi_batch>
