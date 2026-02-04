{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
{!! '<' . '?xml-stylesheet type="text/xsl" href="' . asset('oai2.xsl') . '" ?' . '>' !!}
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
    <responseDate>{{ now()->toIso8601String() }}</responseDate>
    <request verb="{{ $verb }}" metadataPrefix="oai_dc">{{ route('journal.oai', $journal->slug) }}</request>

    <{{ $verb }}>
        @foreach ($records as $record)
            <record>
                <header>
                    {{-- ID Unik Global --}}
                    <identifier>oai:{{ parse_url(config('app.url'), PHP_URL_HOST) }}:article/{{ $record->id }}
                    </identifier>
                    <datestamp>{{ \Carbon\Carbon::parse($record->publication->date_published)->format('Y-m-d') }}
                    </datestamp>
                    <setSpec>{{ $journal->slug }}</setSpec>
                </header>
                @if ($verb === 'ListRecords')
                    <metadata>
                        <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
                            xmlns:dc="http://purl.org/dc/elements/1.1/"
                            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                            xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">

                            {{-- 1. TITLE --}}
                            <dc:title>{{ $record->publication->title ?? 'Untitled' }}</dc:title>

                            {{-- 2. CREATOR (Authors) --}}
                            @foreach ($record->authors as $author)
                                <dc:creator>
                                    {{ $author->last_name ? $author->last_name . ', ' . $author->first_name : $author->first_name }}
                                </dc:creator>
                            @endforeach

                            {{-- 3. SUBJECT (Keywords) --}}
                            @if (isset($record->publication->keywords) && is_array($record->publication->keywords))
                                @foreach ($record->publication->keywords as $keyword)
                                    <dc:subject>{{ trim($keyword) }}</dc:subject>
                                @endforeach
                            @elseif(isset($record->publication->keywords))
                                @foreach (explode(',', $record->publication->keywords) as $keyword)
                                    <dc:subject>{{ trim($keyword) }}</dc:subject>
                                @endforeach
                            @endif

                            {{-- 4. DESCRIPTION (Abstract) --}}
                            <dc:description>{{ strip_tags($record->publication->abstract ?? '') }}</dc:description>

                            {{-- 5. PUBLISHER --}}
                            <dc:publisher>{{ $journal->name }}</dc:publisher>

                            {{-- 6. DATE --}}
                            <dc:date>{{ \Carbon\Carbon::parse($record->publication->date_published)->format('Y-m-d') }}
                            </dc:date>

                            {{-- 7. TYPE --}}
                            <dc:type>info:eu-repo/semantics/article</dc:type>
                            <dc:type>info:eu-repo/semantics/publishedVersion</dc:type>

                            {{-- 8. FORMAT --}}
                            <dc:format>application/pdf</dc:format>

                            {{-- 9. IDENTIFIER (URL Publik) --}}
                            <dc:identifier>
                                {{ route('journal.public.article', ['journal' => $journal->slug, 'article' => $record->slug ?? $record->id]) }}
                            </dc:identifier>

                            {{-- 10. SOURCE --}}
                            <dc:source>{{ $journal->name }}</dc:source>

                            {{-- 11. LANGUAGE --}}
                            <dc:language>{{ $record->publication->locale ?? 'en' }}</dc:language>

                            {{-- 12. RIGHTS (Lisensi - Penting untuk Indexing) --}}
                            {{-- Jika ada setting license_url di jurnal/publication --}}
                            <dc:rights>http://creativecommons.org/licenses/by/4.0/</dc:rights>

                            {{-- 13. DOI (Optional but important) --}}
                            @if (isset($record->publication->doi) && $record->publication->doi)
                                <dc:identifier>info:doi/{{ $record->publication->doi }}</dc:identifier>
                            @endif

                        </oai_dc:dc>
                    </metadata>
                @endif
            </record>
        @endforeach
        </{{ $verb }}>
</OAI-PMH>
