{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
{!! '<' . '?xml-stylesheet type="text/xsl" href="' . asset('oai.xsl') . '"?' . '>' !!}
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
    <responseDate>{{ now()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') }}</responseDate>
    <request{!! \App\Http\Controllers\Public\OaiController::getRequestAttributes() !!}>{{ url()->current() }}</request>
    <ListRecords>
        @foreach ($records as $record)
            <record>
                <header>
                    <identifier>oai:{{ parse_url(config('app.url'), PHP_URL_HOST) }}:article/{{ $record->id }}</identifier>
                    <datestamp>{{ $record->updated_at->utc()->format('Y-m-d\TH:i:s\Z') }}</datestamp>
                    <setSpec>{{ strtoupper($journal->abbreviation ?? 'JRN') }}:ART</setSpec>
                </header>
                <metadata>
                    <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
                        xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                        xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
                        <dc:title>{!! htmlspecialchars($record->title, ENT_XML1 | ENT_QUOTES, 'UTF-8') !!}</dc:title>
                        <dc:creator>{!! htmlspecialchars(trim(($record->authors->first()->first_name ?? 'Unknown') . ' ' . ($record->authors->first()->last_name ?? '')), ENT_XML1 | ENT_QUOTES, 'UTF-8') !!}</dc:creator>
                        {{-- Subject --}}
                        @if(isset($record->keywords) && is_iterable($record->keywords))
                            @foreach ($record->keywords as $keywordModel)
                                @php
                                    $keyword = trim($keywordModel->content ?? $keywordModel->keyword ?? $keywordModel->name ?? '');
                                @endphp
                                @if ($keyword)
                                    <dc:subject>{{ htmlspecialchars($keyword, ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</dc:subject>
                                @endif
                            @endforeach
                        @endif
                        <dc:description>{!! htmlspecialchars(strip_tags(html_entity_decode($record->abstract)), ENT_XML1 | ENT_QUOTES, 'UTF-8') !!}</dc:description>
                        <dc:publisher>{!! htmlspecialchars($journal->name, ENT_XML1 | ENT_QUOTES, 'UTF-8') !!}</dc:publisher>
                        @if ($record->publication && $record->publication->date_published)
                            <dc:date>{{ \Carbon\Carbon::parse($record->publication->date_published)->format('Y-m-d') }}
                            </dc:date>
                        @endif
                        <dc:type>info:eu-repo/semantics/article</dc:type>
                        <dc:type>info:eu-repo/semantics/publishedVersion</dc:type>
                        <dc:format>application/pdf</dc:format>

                        {{-- Identifier (Slug URL) --}}
                        <dc:identifier>
                            {{ route('journal.public.article', ['journal' => $journal->slug, 'article' => $record->seq_id]) }}
                        </dc:identifier>

                        {{-- Relation (PDF URL) --}}
                        @php
                            $pdf = $record->galleys->first();
                        @endphp
                        @if ($pdf)
                            <dc:relation>
                                {{ route('journal.article.galley', ['journal' => $journal->slug, 'article' => $record->slug ?? $record->id, 'galley' => $pdf->id]) }}
                            </dc:relation>
                        @else
                            <dc:relation>
                                {{ route('journal.public.article', ['journal' => $journal->slug, 'article' => $record->seq_id]) }}
                            </dc:relation>
                        @endif

                        <dc:language>{{ $record->locale ?? 'en' }}</dc:language>
                    </oai_dc:dc>
                </metadata>
            </record>
        @endforeach
    </ListRecords>
</OAI-PMH>
