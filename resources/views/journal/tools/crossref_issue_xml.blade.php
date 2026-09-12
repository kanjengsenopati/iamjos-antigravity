@php
  /**
   * Crossref XML Deposit for Issues — Schema 5.3.1
   * Reference: https://www.crossref.org/schemas/crossref5.3.1.xsd
   */

  $escape = function ($string) {
    if (empty($string)) return '';
    $decoded = htmlspecialchars_decode(trim($string), ENT_QUOTES);
    return htmlspecialchars($decoded, ENT_XML1 | ENT_QUOTES, 'UTF-8');
  };
@endphp
<doi_batch xmlns="http://www.crossref.org/schema/5.3.1"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  version="5.3.1"
  xsi:schemaLocation="http://www.crossref.org/schema/5.3.1 https://www.crossref.org/schemas/crossref5.3.1.xsd">

  <head>
    <doi_batch_id>{{ $batchId }}</doi_batch_id>
    <timestamp>{{ now()->format('YmdHis') }}{{ substr(str_pad(now()->format('u'), 6, '0', STR_PAD_LEFT), 0, 3) }}</timestamp>
    <depositor>
      <depositor_name>{!! $escape($journal->getSetting('crossref_depositor_name') ?: $journal->name) !!}</depositor_name>
      <email_address>{!! $escape($journal->getSetting('crossref_depositor_email') ?: ($journal->email ?? 'admin@example.com')) !!}</email_address>
    </depositor>
    <registrant>{!! $escape($journal->publisher ?? $journal->name) !!}</registrant>
  </head>

  <body>
    @foreach ($issues as $issue)
      @php
        $issueDate = $issue->published_at ?? null;
      @endphp
      <journal>
        <journal_metadata>
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

        <journal_issue>
          <publication_date media_type="online">
            @if ($issueDate)
              <month>{{ $issueDate->format('m') }}</month>
              <day>{{ $issueDate->format('d') }}</day>
            @endif
            <year>{{ $issue->year ?? ($issueDate ? $issueDate->format('Y') : date('Y')) }}</year>
          </publication_date>
          @if ($issue->volume)
            <journal_volume>
              <volume>{{ $issue->volume }}</volume>
            </journal_volume>
          @endif
          @if ($issue->number)
            <issue>{{ $issue->number }}</issue>
          @endif
          @if (!empty($issue->doi))
            <doi_data>
              <doi>{{ $issue->doi }}</doi>
              <resource>{{ route('journal.public.issue', ['journal' => $journal->slug, 'issue' => $issue->seq_id ?? $issue->id]) }}</resource>
            </doi_data>
          @endif
        </journal_issue>
      </journal>
    @endforeach
  </body>
</doi_batch>
