<x-mail::message>
    # {{ $journalName }}

    Dear {{ $recipientName }},

    {!! $body !!}

    <x-mail::button :url="config('app.url')">
        Visit Journal
    </x-mail::button>

    Best regards,<br>
    {{ $journalName }}
</x-mail::message>