{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
{!! '<' . '?xml-stylesheet type="text/xsl" href="' . asset('oai2.xsl') . '" ?' . '>' !!}
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
    <responseDate>{{ now()->toIso8601String() }}</responseDate>
    <request verb="ListIdentifiers" metadataPrefix="oai_dc">{{ route('journal.oai', $journal->path) }}</request>

    <ListIdentifiers>
        @foreach ($records as $record)
            <header>
                <identifier>oai:{{ parse_url(config('app.url'), PHP_URL_HOST) }}:article/{{ $record->id }}
                </identifier>
                <datestamp>{{ \Carbon\Carbon::parse($record->pub_date)->format('Y-m-d') }}</datestamp>
                <setSpec>{{ $journal->path }}</setSpec>
            </header>
        @endforeach
    </ListIdentifiers>
</OAI-PMH>
