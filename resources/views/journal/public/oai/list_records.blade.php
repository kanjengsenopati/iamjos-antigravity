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
        }

        .nav-links a {
            margin-right: 10px;
            color: #0000CC;
            text-decoration: underline;
        }

        /* Info Box */
        .info-box {
            margin-bottom: 20px;
            font-size: 14px;
        }

        /* Request Info Table */
        table.request-info {
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.request-info td {
            padding: 3px 5px;
        }

        .label-bg {
            background-color: #e0e0ff;
            font-weight: bold;
        }

        /* OAI Record Container */
        .oai-record {
            border: 1px solid #ccc;
            margin-bottom: 30px;
        }

        /* Purple Header Bar */
        .record-header-bar {
            background-color: #e0e0ff;
            padding: 5px 10px;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
        }

        /* Inner Headers */
        h2 {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 10px 5px 10px;
        }

        h3 {
            font-size: 14px;
            font-weight: bold;
            margin: 10px 10px 5px 10px;
        }

        /* Data Tables */
        table.data-table {
            width: 98%;
            margin: 10px auto;
            border-collapse: separate;
            border-spacing: 1px;
        }

        table.data-table td {
            padding: 4px;
            vertical-align: top;
            font-size: 14px;
        }

        /* OJS Style Specifics */
        .label-cell {
            background-color: #e0e0ff;
            /* Purple for Header info */
            font-weight: bold;
            text-align: right;
            width: 150px;
            white-space: nowrap;
        }

        .dc-label-cell {
            background-color: #ffffcc;
            /* Yellow/Cream for Metadata */
            font-weight: bold;
            text-align: right;
            width: 180px;
            vertical-align: top;
            color: #000;
        }

        .value-cell {
            background-color: #fff;
            text-align: left;
        }

        .xml-link {
            color: #0000CC;
            text-decoration: underline;
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

    <div class="info-box">
        You are viewing an HTML version of the XML OAI response. To see the underlying XML use your web browsers view
        source option.
    </div>

    {{-- Request Info --}}
    <table class="request-info">
        <tr>
            <td class="label-bg">Datestamp of response</td>
            <td>{{ now()->format('Y-m-d\TH:i:s\Z') }}</td>
        </tr>
        <tr>
            <td class="label-bg" style="text-align: right;">Request URL</td>
            <td>{{ url()->current() }}</td>
        </tr>
    </table>

    <p>Request was of type ListRecords.</p>

    {{-- LOOP RECORDS --}}
    @foreach ($records as $record)
        <div class="oai-record">

            {{-- 1. PURPLE HEADER BAR --}}
            <div class="record-header-bar">
                OAI Record: oai:{{ parse_url(config('app.url'), PHP_URL_HOST) }}:article/{{ $record->slug }}
            </div>

            {{-- 2. OAI RECORD HEADER --}}
            <h2>OAI Record Header</h2>
            <table class="data-table">
                <tr>
                    <td class="label-cell">OAI Identifier</td>
                    <td class="value-cell">
                        oai:{{ parse_url(config('app.url'), PHP_URL_HOST) }}:article/{{ $record->slug }}
                        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'GetRecord', 'metadataPrefix' => 'oai_dc', 'identifier' => 'oai:' . parse_url(config('app.url'), PHP_URL_HOST) . ':article/' . $record->id]) }}"
                            style="background:#e0e0ff; padding:0 3px; text-decoration:none; color:#000;">oai_dc</a>
                        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListMetadataFormats', 'identifier' => 'oai:' . parse_url(config('app.url'), PHP_URL_HOST) . ':article/' . $record->id]) }}"
                            style="background:#e0e0ff; padding:0 3px; text-decoration:none; color:#000;">formats</a>
                    </td>
                </tr>
                <tr>
                    <td class="label-cell">Datestamp</td>
                    <td class="value-cell">{{ $record->updated_at->format('Y-m-d\TH:i:s\Z') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">setSpec</td>
                    <td class="value-cell">
                        {{ $record->journal->abbreviation ?? 'JRN' }}:ART
                        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListIdentifiers', 'metadataPrefix' => 'oai_dc', 'set' => ($record->journal->abbreviation ?? 'JRN') . 'ART']) }}"
                            style="background:#e0e0ff; padding:0 3px; font-weight:normal; text-decoration:none; color:#000;">Identifiers</a>
                        <a href="{{ route('journal.oai', ['journal' => $journal->slug, 'verb' => 'ListRecords', 'metadataPrefix' => 'oai_dc', 'set' => ($record->journal->abbreviation ?? 'JRN') . ':ART']) }}"
                            style="background:#e0e0ff; padding:0 3px; font-weight:normal; text-decoration:none; color:#000;">Records</a>
                    </td>
                </tr>
            </table>

            {{-- 3. DUBLIN CORE METADATA (YELLOW LABELS) --}}
            <h3>Dublin Core Metadata (oai_dc)</h3>
            <table class="data-table">

                {{-- Title --}}
                <tr>
                    <td class="dc-label-cell">Title</td>
                    <td class="value-cell">{{ $record->title }}</td>
                </tr>

                {{-- Creators / Authors --}}
                @foreach ($record->authors as $author)
                    <tr>
                        <td class="dc-label-cell">Author or Creator</td>
                        <td class="value-cell">{{ $author->last_name }}, {{ $author->first_name }}</td>
                    </tr>
                @endforeach

                {{-- Subjects --}}
                @if ($record->keywords)
                    @foreach (explode(',', $record->keywords) as $keyword)
                        <tr>
                            <td class="dc-label-cell">Subject and Keywords</td>
                            <td class="value-cell">{{ trim($keyword) }}</td>
                        </tr>
                    @endforeach
                @endif

                {{-- Description (Abstract) --}}
                <tr>
                    <td class="dc-label-cell">Description</td>
                    <td class="value-cell">{!! strip_tags($record->abstract) !!}</td>
                </tr>

                {{-- Publisher --}}
                <tr>
                    <td class="dc-label-cell">Publisher</td>
                    <td class="value-cell">{{ $record->journal->publisher ?? $record->journal->name }}</td>
                </tr>

                {{-- Date --}}
                <tr>
                    <td class="dc-label-cell">Date</td>
                    <td class="value-cell">{{ \Carbon\Carbon::parse($record->date_published)->format('Y-m-d') }}</td>
                </tr>

                {{-- Resource Type --}}
                <tr>
                    <td class="dc-label-cell">Resource Type</td>
                    <td class="value-cell">info:eu-repo/semantics/article</td>
                </tr>
                <tr>
                    <td class="dc-label-cell">Resource Type</td>
                    <td class="value-cell">info:eu-repo/semantics/publishedVersion</td>
                </tr>

                {{-- Format --}}
                <tr>
                    <td class="dc-label-cell">Format</td>
                    <td class="value-cell">application/pdf</td>
                </tr>

                {{-- Identifier --}}
                <tr>
                    <td class="dc-label-cell">Resource Identifier</td>
                    <td class="value-cell">
                        <a href="{{ route('journal.public.article', ['journal' => $record->journal->slug, 'article' => $record->slug ?? $record->id]) }}"
                            class="xml-link">
                            {{ route('journal.public.article', ['journal' => $record->journal->slug, 'article' => $record->slug ?? $record->id]) }}
                        </a>
                    </td>
                </tr>

                {{-- Source --}}
                <tr>
                    <td class="dc-label-cell">Source</td>
                    <td class="value-cell">
                        {{ $record->journal->name }}; Vol. {{ $record->issue->volume ?? 1 }} No.
                        {{ $record->issue->number ?? 1 }} ({{ $record->issue->year ?? date('Y') }})
                    </td>
                </tr>

                {{-- Language --}}
                <tr>
                    <td class="dc-label-cell">Language</td>
                    <td class="value-cell">{{ $record->locale ?? 'en' }}</td>
                </tr>

                {{-- Relation (Biasanya OJS menyembunyikan URL panjang di sini) --}}
                <td class="dc-label-cell">Relation</td>
                <td class="value-cell">
                    <a href="{{ route('journal.public.article', ['journal' => $record->journal->slug, 'article' => $record->slug ?? $record->id]) }}"
                        class="xml-link">
                        {{ route('journal.public.article', ['journal' => $record->journal->slug, 'article' => $record->slug ?? $record->id]) }}
                    </a>
                </td>

                {{-- Rights --}}
                <tr>
                    <td class="dc-label-cell">Rights Management</td>
                    <td class="value-cell">Copyright (c) {{ $record->issue->year ?? date('Y') }}
                        {{ $record->journal->name }}</td>
                </tr>
                <tr>
                    <td class="dc-label-cell">Rights Management</td>
                    <td class="value-cell">
                        <a href="https://creativecommons.org/licenses/by/4.0"
                            class="xml-link">https://creativecommons.org/licenses/by/4.0</a>
                    </td>
                </tr>

            </table>

        </div>
    @endforeach

</body>

</html>
