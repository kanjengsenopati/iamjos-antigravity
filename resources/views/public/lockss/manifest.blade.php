<!DOCTYPE html>
<html>
<head>
    <title>LOCKSS Manifest — {{ $journal->name }}</title>
</head>
<body>
    <h1>LOCKSS Manifest Page</h1>
    <p>Journal: {{ $journal->name }}</p>
    @if($journal->issn_print)
    <p>ISSN (Print): {{ $journal->issn_print }}</p>
    @endif
    @if($journal->issn_online)
    <p>ISSN (Online): {{ $journal->issn_online }}</p>
    @endif
    <ul>
    @foreach($issues as $issue)
        <li>
            <a href="{{ route('journal.public.issue', [$journal->path, $issue->id]) }}">
                Vol. {{ $issue->volume }}, No. {{ $issue->number }} ({{ $issue->year }})
            </a>
        </li>
    @endforeach
    </ul>
    <p>
        <a href="{{ route('journal.oai', $journal->path) }}?verb=Identify">
            OAI-PMH Endpoint
        </a>
    </p>
</body>
</html>
