{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
{!! '<' . '?xml-stylesheet type="text/xsl" href="' . asset('oai.xsl') . '"?' . '>' !!}
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
    <responseDate>{{ now()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') }}</responseDate>
    <request{!! \App\Http\Controllers\Public\OaiController::getRequestAttributes() !!}>{{ url()->current() }}</request>
    <ListMetadataFormats>
        {{-- Dublin Core — format wajib OAI-PMH --}}
        <metadataFormat>
            <metadataPrefix>oai_dc</metadataPrefix>
            <schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>
            <metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>
        </metadataFormat>
        {{-- MARC 21 XML — digunakan oleh perpustakaan dan agregator --}}
        <metadataFormat>
            <metadataPrefix>marc21</metadataPrefix>
            <schema>http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd</schema>
            <metadataNamespace>http://www.loc.gov/MARC21/slim</metadataNamespace>
        </metadataFormat>
        {{-- RFC 1807 — format teknis untuk laporan dan preprint --}}
        <metadataFormat>
            <metadataPrefix>rfc1807</metadataPrefix>
            <schema>http://info.internet.isi.edu:80/in-notes/rfc/files/rfc1807.txt</schema>
            <metadataNamespace>http://info.internet.isi.edu:80/in-notes/rfc/files/rfc1807.txt</metadataNamespace>
        </metadataFormat>
    </ListMetadataFormats>
</OAI-PMH>
