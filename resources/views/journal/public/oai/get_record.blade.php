{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
{!! '<' . '?xml-stylesheet type="text/xsl" href="' . asset('oai.xsl') . '"?' . '>' !!}
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate>{{ now()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') }}</responseDate>
  <request{!! \App\Http\Controllers\Public\OaiController::getRequestAttributes() !!}>{{ url()->current() }}</request>
  <GetRecord>
    <record>
      @if (($metadataPrefix ?? 'oai_dc') === 'marc21')
        @include('journal.public.oai.formats.marcxml', ['record' => $record, 'journal' => $journal])
      @elseif (($metadataPrefix ?? 'oai_dc') === 'rfc1807')
        @include('journal.public.oai.formats.rfc1807', ['record' => $record, 'journal' => $journal])
      @else
        @include('journal.public.oai._dc_record', ['record' => $record, 'journal' => $journal])
      @endif
    </record>
  </GetRecord>
</OAI-PMH>
