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
            width: 150px;
            white-space: nowrap;
        }

        .req-value {
            background-color: #fff;
            padding: 3px 5px;
        }

        /* Set Section Header */
        h2.set-header {
            font-size: 18px;
            font-weight: bold;
            margin-top: 30px;
            margin-bottom: 5px;
        }

        /* Set Data Table */
        table.set-table {
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
            width: 100px;
        }

        .value-cell {
            padding: 3px 5px;
            vertical-align: top;
        }

        /* Small Buttons (Identifiers, Records) */
        .mini-btn {
            background-color: #e0e0ff;
            font-size: 13px;
            padding: 1px 4px;
            margin-left: 5px;
            color: #000;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid #ccc;
        }

        .mini-btn:hover {
            text-decoration: underline;
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

    {{-- CORRECTED NAVIGATION LINKS --}}
    <div class="nav-links"
        style="margin-bottom: 20px; font-size: 14px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
        {{-- Identify: No Params needed --}}
        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'Identify']) }}"
            style="margin-right: 10px; color: #0000CC; text-decoration: underline;">Identify</a> |

        {{-- ListRecords: REQUIRES metadataPrefix --}}
        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListRecords', 'metadataPrefix' => 'oai_dc']) }}"
            style="margin-right: 10px; color: #0000CC; text-decoration: underline;">ListRecords</a> |

        {{-- ListSets: No Params needed --}}
        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListSets']) }}"
            style="margin-right: 10px; color: #0000CC; text-decoration: underline;">ListSets</a> |

        {{-- ListMetadataFormats: No Params needed --}}
        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListMetadataFormats']) }}"
            style="margin-right: 10px; color: #0000CC; text-decoration: underline;">ListMetadataFormats</a> |

        {{-- ListIdentifiers: REQUIRES metadataPrefix --}}
        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListIdentifiers', 'metadataPrefix' => 'oai_dc']) }}"
            style="margin-right: 10px; color: #0000CC; text-decoration: underline;">ListIdentifiers</a>
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

    <p style="font-size: 14px;">Request was of type ListSets.</p>

    {{-- LOOP SETS --}}
    @foreach ($sets as $set)
        {{-- Header "Set" --}}
        <h2 class="set-header">Set</h2>

        <table class="set-table">
            {{-- Row 1: setName --}}
            <tr>
                <td class="label-cell">setName</td>
                <td class="value-cell">{{ $set->name }}</td>
            </tr>

            {{-- Row 2: setSpec + Buttons --}}
            <tr>
                <td class="label-cell">setSpec</td>
                <td class="value-cell">
                    {{ $set->spec }}

                    {{-- Tombol "Identifiers" menunjuk ke ListIdentifiers dengan filter set --}}
                    <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListIdentifiers', 'metadataPrefix' => 'oai_dc', 'set' => $set->spec]) }}"
                        class="mini-btn">Identifiers</a>

                    {{-- Tombol "Records" menunjuk ke ListRecords dengan filter set --}}
                    <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListRecords', 'metadataPrefix' => 'oai_dc', 'set' => $set->spec]) }}"
                        class="mini-btn">Records</a>
                </td>
            </tr>
        </table>
    @endforeach

    {{-- Footer Navigation (Repeated) --}}
    <div class="nav-links" style="margin-top: 30px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
        {{-- Identify --}}
        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'Identify']) }}">Identify</a> |

        {{-- ListRecords --}}
        <a
            href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListRecords', 'metadataPrefix' => 'oai_dc']) }}">ListRecords</a>
        |

        {{-- ListSets --}}
        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListSets']) }}">ListSets</a> |

        {{-- ListMetadataFormats --}}
        <a
            href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListMetadataFormats']) }}">ListMetadataFormats</a>
        |

        {{-- ListIdentifiers --}}
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
