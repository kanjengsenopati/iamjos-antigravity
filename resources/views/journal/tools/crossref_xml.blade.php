<doi_batch xmlns="http://www.crossref.org/schema/4.3.6" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:jats="http://www.ncbi.nlm.nih.gov/JATS1" xmlns:ai="http://www.crossref.org/AccessIndicators.xsd" version="4.3.6"
    xsi:schemaLocation="http://www.crossref.org/schema/4.3.6 https://www.crossref.org/schemas/crossref4.3.6.xsd">

    <head>
        <doi_batch_id>{{ $batchId }}</doi_batch_id>
        <timestamp>{{ time() }}</timestamp>
        <depositor>
            <depositor_name>{{ $journal->name }} Editorial</depositor_name>
            <email_address>{{ $journal->email }}</email_address>
        </depositor>
        <registrant>{{ $journal->publisher ?? $journal->name }}</registrant>
    </head>

    <body>
        <journal>
            <journal_metadata>
                <full_title>{{ $journal->name }}</full_title>
                <abbrev_title>{{ $journal->abbreviation ?? $journal->name }}</abbrev_title>
                @if ($journal->issn)
                    <issn media_type="electronic">{{ $journal->issn }}</issn>
                @endif
            </journal_metadata>

            {{-- ISSUE DATA (Diambil dari artikel pertama yg dipilih) --}}
            @php $firstItem = $submissions->first(); @endphp
            @if ($firstItem && $firstItem->issue)
                <journal_issue>
                    <publication_date media_type="online">
                        <month>{{ $firstItem->issue->month ?? date('m') }}</month>
                        <day>{{ $firstItem->issue->day ?? date('d') }}</day>
                        <year>{{ $firstItem->issue->year ?? date('Y') }}</year>
                    </publication_date>
                    <journal_volume>
                        <volume>{{ $firstItem->issue->volume ?? 1 }}</volume>
                    </journal_volume>
                    <issue>{{ $firstItem->issue->number ?? 1 }}</issue>
                </journal_issue>
            @endif

            {{-- ARTICLES LOOP --}}
            @foreach ($submissions as $article)
                <journal_article publication_type="full_text" metadata_distribution_opts="any">

                    <titles>
                        <title>{{ $article->title }}</title>
                    </titles>

                    <contributors>
                        @foreach ($article->authors as $index => $author)
                            <person_name contributor_role="author"
                                sequence="{{ $index === 0 ? 'first' : 'additional' }}">
                                <given_name>{{ $author->first_name }}</given_name>
                                <surname>{{ $author->last_name ?? $author->first_name }}</surname>
                                @if ($author->orcid)
                                    <ORCID authenticated="true">{{ $author->orcid }}</ORCID>
                                @endif
                            </person_name>
                        @endforeach
                    </contributors>

                    {{-- JATS Namespace for Abstract --}}
                    <jats:abstract>
                        <jats:p>{{ strip_tags($article->abstract) }}</jats:p>
                    </jats:abstract>

                    <publication_date media_type="online">
                        @if ($article->published_at)
                            <month>{{ $article->published_at->format('m') }}</month>
                            <day>{{ $article->published_at->format('d') }}</day>
                            <year>{{ $article->published_at->format('Y') }}</year>
                        @endif
                    </publication_date>

                    {{-- Pages (Optional) --}}
                    @if ($article->pages)
                        <pages>
                            <first_page>{{ explode('-', $article->pages)[0] ?? '' }}</first_page>
                            <last_page>{{ explode('-', $article->pages)[1] ?? '' }}</last_page>
                        </pages>
                    @endif

                    {{-- Access Indicators Namespace --}}
                    <ai:program name="AccessIndicators">
                        <ai:license_ref>https://creativecommons.org/licenses/by/4.0</ai:license_ref>
                    </ai:program>

                    <doi_data>
                        {{-- DOI Logic: Use existing DOI or Generate generic fallback --}}
                        <doi>
                            {{ $article->doi ?? '10.xxxx/' . $journal->path . '.' . ($article->slug ?? $article->id) }}
                        </doi>
                        <resource>
                            {{ route('journal.public.article', ['journal' => $journal->slug, 'article' => $article->slug ?? $article->id]) }}
                        </resource>
                    </doi_data>

                    {{-- REFERENCES / CITATION LIST --}}
                    @if (!empty($article->references))
                        <citation_list>
                            @php
                                // Clean and split references
                                $refs = preg_split('/\r\n|\r|\n/', $article->references);
                                $refs = array_filter($refs, fn($value) => !is_null($value) && trim($value) !== '');
                                $counter = 1;
                            @endphp
                            @foreach ($refs as $ref)
                                @php
                                    $ref = trim($ref);
                                    $key = 'ref' . $counter++;
                                    $doi = null;

                                    // Regex to find a DOI (stand-alone or inside a URL like https://doi.org/...)
                                    // Pattern matches strings starting with '10.' followed by digits, slash, and valid chars
                                    if (preg_match('/(10\.\d{4,9}\/[-._;()\/:\w]+)/', $ref, $matches)) {
                                        $doi = $matches[1];
                                    }
                                @endphp
                                <citation key="{{ $key }}">
                                    @if ($doi)
                                        <doi>{{ $doi }}</doi>
                                    @else
                                        <unstructured_citation>{{ $ref }}</unstructured_citation>
                                    @endif
                                </citation>
                            @endforeach
                        </citation_list>
                    @endif

                </journal_article>
            @endforeach

        </journal>
    </body>
</doi_batch>
