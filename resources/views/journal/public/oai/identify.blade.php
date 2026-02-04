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

        h2 {
            font-size: 18px;
            font-weight: bold;
            margin-top: 25px;
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

        /* Tables */
        table.info-table {
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
            padding: 3px 10px;
            white-space: nowrap;
            vertical-align: top;
            width: 180px;
        }

        .value-cell {
            background-color: #fff;
            padding: 3px 5px;
            vertical-align: top;
        }

        /* Yellow XML Box */
        .xml-box {
            background-color: #ffffcc;
            /* Yellow/Cream */
            border: 1px solid #ccc;
            padding: 15px;
            font-family: monospace;
            font-size: 12px;
            margin-top: 10px;
            white-space: pre-wrap;
        }

        /* XML Tags styling */
        .tag {
            color: maroon;
            font-weight: bold;
        }

        .attr {
            color: red;
        }

        .val {
            color: blue;
        }

        .content {
            color: black;
            font-weight: bold;
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

    {{-- Request Info --}}
    <table class="info-table">
        <tr>
            <td class="label-cell">Datestamp of response</td>
            <td class="value-cell">{{ now()->format('Y-m-d\TH:i:s\Z') }}</td>
        </tr>
        <tr>
            <td class="label-cell">Request URL</td>
            <td class="value-cell">{{ url()->full() }}</td>
        </tr>
    </table>

    <p style="font-size: 14px;">Request was of type Identify.</p>

    {{-- REPOSITORY IDENTIFICATION --}}
    <table class="info-table">
        <tr>
            <td class="label-cell">Repository Name</td>
            <td class="value-cell">{{ $journal->name ?? 'IAMJOS Journal' }}</td>
        </tr>
        <tr>
            <td class="label-cell">Base URL</td>
            <td class="value-cell">{{ route('journal.oai', $journal->slug) }}</td>
        </tr>
        <tr>
            <td class="label-cell">Protocol Version</td>
            <td class="value-cell">2.0</td>
        </tr>
        <tr>
            <td class="label-cell">Earliest Datestamp</td>
            <td class="value-cell">
                {{ \App\Models\Submission::min('updated_at') ? \Carbon\Carbon::parse(\App\Models\Submission::min('updated_at'))->format('Y-m-d\TH:i:s\Z') : now()->format('Y-m-d\TH:i:s\Z') }}
            </td>
        </tr>
        <tr>
            <td class="label-cell">Deleted Record Policy</td>
            <td class="value-cell">persistent</td>
        </tr>
        <tr>
            <td class="label-cell">Granularity</td>
            <td class="value-cell">YYYY-MM-DDThh:mm:ssZ</td>
        </tr>
        <tr>
            <td class="label-cell">Admin Email</td>
            <td class="value-cell">{{ $journal->email ?? 'admin@iamjos.test' }}</td>
        </tr>
    </table>

    {{-- OAI IDENTIFIER SECTION --}}
    <h2>OAI-Identifier</h2>
    <table class="info-table">
        <tr>
            <td class="label-cell">Scheme</td>
            <td class="value-cell">oai</td>
        </tr>
        <tr>
            <td class="label-cell">Repository Identifier</td>
            <td class="value-cell">{{ parse_url(config('app.url'), PHP_URL_HOST) }}</td>
        </tr>
        <tr>
            <td class="label-cell">Delimiter</td>
            <td class="value-cell">:</td>
        </tr>
        <tr>
            <td class="label-cell">Sample OAI Identifier</td>
            <td class="value-cell">oai:{{ parse_url(config('app.url'), PHP_URL_HOST) }}:article/1</td>
        </tr>
    </table>

    {{-- UNSUPPORTED DESCRIPTION (Branding Box) --}}
    <h2>Unsupported Description Type</h2>
    <p style="font-size: 14px;">The XSL currently does not support this type of description.</p>

    <div class="xml-box">
        &lt;<span class="tag">toolkit</span> <span class="attr">xsi:schemaLocation</span>="<span
            class="val">http://oai.dlib.vt.edu/OAI/metadata/toolkit
            http://oai.dlib.vt.edu/OAI/metadata/toolkit.xsd</span>"&gt;
        &lt;<span class="tag">title</span>&gt;<span class="content">IAMJOS Journal System</span>&lt;/<span
            class="tag">title</span>&gt;
        &lt;<span class="tag">author</span>&gt;
        &lt;<span class="tag">name</span>&gt;<span class="content">IAMJOS Development Team</span>&lt;/<span
            class="tag">name</span>&gt;
        &lt;<span class="tag">email</span>&gt;<span class="content">dev@iamjos.test</span>&lt;/<span
            class="tag">email</span>&gt;
        &lt;/<span class="tag">author</span>&gt;
        &lt;<span class="tag">version</span>&gt;<span class="content">1.0.0</span>&lt;/<span
            class="tag">version</span>&gt;
        &lt;<span class="tag">URL</span>&gt;<span class="content">{{ config('app.url') }}</span>&lt;/<span
            class="tag">URL</span>&gt;
        &lt;/<span class="tag">toolkit</span>&gt;
    </div>

    {{-- Footer Navigation --}}
    <div class="nav-links" style="margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
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

    <div id="footer" class="footer-info">
        <h2>About the XSLT</h2>
        <p>An XSLT file has converted the <a href="#" class="bottom-link">OAI-PMH 2.0</a> responses into XHTML
            which looks nice in a browser which supports XSLT such as Mozilla, Firebird and Internet Explorer. The XSLT
            file was created by Christopher Gutteridge at the University of Southampton as part of the GNU EPrints
            system, and is freely redistributable under the GPL.</p>
        <p>If you want to use the XSL file on your own OAI interface you may but due to the way XSLT works you must
            install the XSL file on the same server as the OAI script, you can't just link to this copy.</p>
    </div>

</body>

</html>
