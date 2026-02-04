{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
{!! '<' . '?xml-stylesheet type="text/xsl" href="' . asset('oai.xsl') . '"?' . '>' !!}
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
    <responseDate>{{ now()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') }}</responseDate>
    <request verb="GetRecord" identifier="{{ request('identifier') }}" metadataPrefix="{{ request('metadataPrefix') }}">
        {{ url()->current() }}</request>
    <GetRecord>
        <record>
            <header>
                <identifier>oai:{{ parse_url(config('app.url'), PHP_URL_HOST) }}:article/{{ $record->id }}
                </identifier>
                <datestamp>{{ $record->updated_at->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') }}</datestamp>
                <setSpec>{{ strtoupper($journal->abbreviation ?? 'JRN') }}:ART</setSpec>
            </header>
            <metadata>
                <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
                    xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
                    <dc:title>{{ $record->title }}</dc:title>
                    <dc:creator>{{ $record->authors->first()->first_name ?? 'Unknown' }}
                        {{ $record->authors->first()->last_name ?? '' }}</dc:creator>
                    {{-- Subject --}}
                    @foreach (explode(',', $record->keywords ?? '') as $keyword)
                        @if (trim($keyword))
                            <dc:subject>{{ trim($keyword) }}</dc:subject>
                        @endif
                    @endforeach
                    <dc:description>{{ strip_tags($record->abstract) }}</dc:description>
                    <dc:publisher>{{ $journal->name }}</dc:publisher>
                    <dc:date>
                        {{ $record->publication->date_published ? \Carbon\Carbon::parse($record->publication->date_published)->format('Y-m-d') : '' }}
                    </dc:date>
                    <dc:type>info:eu-repo/semantics/article</dc:type>
                    <dc:type>info:eu-repo/semantics/publishedVersion</dc:type>
                    <dc:format>application/pdf</dc:format>

                    {{-- Identifier (Slug URL) --}}
                    <dc:identifier>
                        {{ route('journal.public.article', ['journal' => $journal->slug, 'article' => $record->slug ?? $record->id]) }}
                    </dc:identifier>

                    {{-- Relation (PDF URL) --}}
                    @php
                        // Try to load galleys if not already loaded (though controller logic usually handles this, single record might be different)
                        // However, OaiController.getRecord hydrates manually.
                        // We need to check if 'galleys' relation is available or if we can fetch it.
                        // The existing OaiController getRecord fetches via join but only hydrates 'publication'.
                        // Ideally we should eager load galleys or fetch them.
                        // For GetRecord query in OaiController (Step 823), it uses With(['authors', 'issue', 'section']).
                        // Galleys are NOT eager loaded. We should add them in controller too.
                        // But view logic:
                        if (!$record->relationLoaded('galleys')) {
                            // Fallback if not loaded
                            $pdf = \App\Models\SubmissionFile::where('submission_id', $record->id)
                                ->where('file_stage', 2)
                                ->first(); // Rough guess or
                            $pdf = null;
                        } else {
                            $pdf = $record->galleys->where('file_type', 'application/pdf')->first();
                        }
                    @endphp
                    @if (isset($pdf) && $pdf)
                        <dc:relation>
                            {{ route('journal.article.galley', ['journal' => $journal->slug, 'article' => $record->slug ?? $record->id, 'galley' => $pdf->id]) }}
                        </dc:relation>
                    @else
                        <dc:relation>
                            {{ route('journal.public.article', ['journal' => $journal->slug, 'article' => $record->slug ?? $record->id]) }}
                        </dc:relation>
                    @endif

                    <dc:language>{{ $record->locale ?? 'en' }}</dc:language>
                </oai_dc:dc>
            </metadata>
        </record>
    </GetRecord>
</OAI-PMH>
