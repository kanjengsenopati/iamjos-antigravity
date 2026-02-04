<!DOCTYPE html>
<html>

<head>
    <title>OAI 2.0 Request Results</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 16px;
            margin: 20px;
            color: #000;
        }

        h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Navigation Links */
        .nav-links {
            margin-bottom: 20px;
            font-size: 14px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .nav-links a {
            margin-right: 10px;
            color: #0000CC;
            text-decoration: underline;
        }

        /* Info Text */
        .info-text {
            margin: 15px 0;
            font-size: 14px;
        }

        .bottom-link {
            color: #0000CC;
            text-decoration: underline;
        }

        /* Request Info Table */
        table.request-info {
            border-collapse: separate;
            border-spacing: 2px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .req-label {
            background-color: #e0e0ff;
            font-weight: bold;
            padding: 3px 5px;
            text-align: right;
            width: 160px;
            white-space: nowrap;
        }

        .req-value {
            background-color: #fff;
            padding: 3px 5px;
        }

        /* Format Section Header */
        h2.format-header {
            font-size: 18px;
            font-weight: bold;
            margin-top: 30px;
            margin-bottom: 5px;
        }

        /* Data Table */
        table.format-table {
            border-collapse: separate;
            border-spacing: 2px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .label-cell {
            background-color: #e0e0ff;
            /* Purple for Labels */
            font-weight: bold;
            text-align: right;
            padding: 3px 5px;
            white-space: nowrap;
            width: 140px;
            vertical-align: top;
        }

        .value-cell {
            padding: 3px 5px;
            vertical-align: top;
        }

        /* Purple Badge for Prefix */
        .prefix-badge {
            background-color: #e0e0ff;
            border: 1px solid #aaa;
            padding: 1px 5px;
            font-size: 13px;
            text-decoration: none;
            color: #000;
            display: inline-block;
        }

        .xml-link {
            color: #0000CC;
            text-decoration: underline;
            word-break: break-all;
        }

        /* Footer */
        .footer-info {
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <h1>OAI 2.0 Request Results</h1>

    {{-- Top Navigation --}}
    <div class="nav-links">
        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'Identify']) }}">Identify</a> |
        <a
            href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListRecords', 'metadataPrefix' => 'oai_dc']) }}">ListRecords</a>
        |
        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListSets']) }}">ListSets</a> |
        <a
            href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListMetadataFormats']) }}">ListMetadataFormats</a>
        |
        <a
            href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListIdentifiers', 'metadataPrefix' => 'oai_dc']) }}">ListIdentifiers</a>
    </div>

    <div class="info-text">
        You are viewing an HTML version of the XML OAI response. To see the underlying XML use your web browsers view
        source option. More information about this XSLT is at the <a href="#footer" class="bottom-link">bottom of the
            page</a>.
    </div>

    {{-- Request Info Table --}}
    <table class="request-info">
        <tr>
            <td class="req-label">Datestamp of response</td>
            <td class="req-value">{{ now()->format('Y-m-d\TH:i:s\Z') }}</td>
        </tr>
        <tr>
            <td class="req-label">Request URL</td>
            <td class="req-value">{{ url()->full() }}</td>
        </tr>
    </table>

    <p style="font-size: 14px;">Request was of type ListMetadataFormats.</p>
    <p style="font-size: 14px;">This is a list of metadata formats available from this archive.</p>

    {{-- LOOP Metadata Formats --}}
    {{-- Asumsi Controller mengirim array $formats, misal: ['oai_dc' => [...], 'marcxml' => [...]] --}}
    @php
        // Jika data dari controller belum ada, kita pakai Dummy Data agar tampilan sesuai screenshot dulu
        $displayFormats = $formats ?? [
            'oai_dc' => [
                'namespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/',
                'schema' => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
            ],
            'oai_marc' => [
                'namespace' => 'http://www.openarchives.org/OAI/1.1/oai_marc',
                'schema' => 'http://www.openarchives.org/OAI/1.1/oai_marc.xsd',
            ],
            'marcxml' => [
                'namespace' => 'http://www.loc.gov/MARC21/slim',
                'schema' => 'https://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd',
            ],
            'rfc1807' => [
                'namespace' => 'http://info.internet.isi.edu:80/in-notes/rfc/files/rfc1807.txt',
                'schema' => 'http://www.openarchives.org/OAI/1.1/rfc1807.xsd',
            ],
        ];
    @endphp

    @foreach ($displayFormats as $prefix => $data)
        <h2 class="format-header">Metadata Format</h2>

        <table class="format-table">
            <tr>
                <td class="label-cell">metadataPrefix</td>
                <td class="value-cell">
                    <span class="prefix-badge">{{ $prefix }}</span>
                </td>
            </tr>
            <tr>
                <td class="label-cell">metadataNamespace</td>
                <td class="value-cell">
                    {{ $data['namespace'] }}
                </td>
            </tr>
            <tr>
                <td class="label-cell">schema</td>
                <td class="value-cell">
                    <a href="{{ $data['schema'] }}" class="xml-link">{{ $data['schema'] }}</a>
                </td>
            </tr>
        </table>
    @endforeach

    {{-- Footer Navigation --}}
    <div class="nav-links" style="margin-top: 30px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'Identify']) }}">Identify</a> |
        <a
            href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListRecords', 'metadataPrefix' => 'oai_dc']) }}">ListRecords</a>
        |
        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListSets']) }}">ListSets</a> |
        <a
            href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListMetadataFormats']) }}">ListMetadataFormats</a>
        |
        <a
            href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListIdentifiers', 'metadataPrefix' => 'oai_dc']) }}">ListIdentifiers</a>
    </div>

    {{-- About XSLT Footer --}}
    <div id="footer" class="footer-info">
        <h2>About the XSLT</h2>
        <p>An XSLT file has converted the <a href="#" class="bottom-link">OAI-PMH 2.0</a> responses into XHTML
            which looks nice in a browser which supports XSLT such as Mozilla, Firebird and Internet Explorer. The XSLT
            file was created by Christopher Gutteridge at the University of Southampton as part of the <a href="#"
                class="bottom-link">GNU EPrints system</a>, and is freely redistributable under the <a href="#"
                class="bottom-link">GPL</a>.</p>
        <p>If you want to use the XSL file on your own OAI interface you may but due to the way XSLT works you must
            install the XSL file on the same server as the OAI script, you can't just link to this copy.</p>
    </div>

</body>

</html>
