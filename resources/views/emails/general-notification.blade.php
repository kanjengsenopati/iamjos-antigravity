<x-mail::message>
    # {{ $journalName }}

    Dear {{ $recipientName }},

    <div class="email-body">
        {!! $body !!}
    </div>

    <x-mail::button :url="config('app.url')">
        Visit Journal
    </x-mail::button>

    Best regards,<br>
    {{ $journalName }}

    {{-- Inline CSS for HTML Content --}}
    <style>
        .email-body code {
            background-color: #f3f4f6; /* gray-100 */
            padding: 0.2em 0.4em;
            border-radius: 0.25rem;
            font-family: monospace;
            font-size: 0.875em;
            color: #ef4444; /* red-500 */
        }
    </style>
</x-mail::message>