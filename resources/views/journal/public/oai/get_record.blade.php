{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
{!! '<' . '?xml-stylesheet type="text/xsl" href="' . asset('oai2.xsl') . '" ?' . '>' !!}
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
    <responseDate>{{ now()->toIso8601String() }}</responseDate>
    <request verb="GetRecord"
        identifier="oai:{{ parse_url(config('app.url'), PHP_URL_HOST) }}:article/{{ $record->id }}"
        metadataPrefix="oai_dc">{{ route('journal.oai', $journal->slug) }}</request>
    <GetRecord>
        <record>
            <header>
                <identifier>oai:{{ parse_url(config('app.url'), PHP_URL_HOST) }}:article/{{ $record->id }}
                </identifier>
                <datestamp>{{ \Carbon\Carbon::parse($record->date_published)->format('Y-m-d') }}</datestamp>
                <setSpec>{{ $journal->slug }}</setSpec>
                @if ($record->section)
                    <setSpec>{{ Str::slug($record->section->name) }}</setSpec>
                @endif
            </header>
            <metadata>
                <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
                    xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
                    <dc:title>{{ $record->title }}</dc:title>
                    @foreach ($record->authors as $author)
                        <dc:creator>{{ $author->last_name }}, {{ $author->first_name }}</dc:creator>
                    @endforeach
                    @if (is_array($record->keywords))
                        @foreach ($record->keywords as $keyword)
                            <dc:subject>{{ $keyword }}</dc:subject>
                        @endforeach
                    @elseif($record->keywords)
                        <dc:subject>{{ $record->keywords }}</dc:subject>
                    @endif
                    <dc:description>{{ strip_tags($record->abstract) }}</dc:description>
                    <dc:publisher>{{ $journal->name }}</dc:publisher>
                    <dc:date>{{ \Carbon\Carbon::parse($record->date_published)->format('Y-m-d') }}</dc:date>
                    <dc:type>info:eu-repo/semantics/article</dc:type>
                    <dc:type>Text</dc:type>
                    <dc:format>application/pdf</dc:format>
                    <dc:identifier>
                        {{ route('journal.public.article', ['journal' => $journal->slug, 'article' => $record->slug ?? $record->id]) }}
                    </dc:identifier>
                    <dc:source>{{ $journal->name }}</dc:source>
                    <dc:language>{{ $record->locale ?? 'en' }}</dc:language>
                    @if ($record->doi)
                        <dc:identifier>info:doi/{{ $record->doi }}</dc:identifier>
                    @endif
                </oai_dc:dc>
            </metadata>
        </record>
    </GetRecord>
</OAI-PMH>
