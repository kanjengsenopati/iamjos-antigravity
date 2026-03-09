<ici-import>
    <journal issn="{{ $journal->issn }}" />
    @foreach($issues as $issue)
        <issue number="{{ $issue->number }}" volume="{{ $issue->volume }}" year="{{ $issue->year }}" />
        @foreach($issue->submissions as $article)
            <article>
                <type>Original article</type>
                <languageVersion language="{{ $article->language ?? 'en' }}">
                    <title><![CDATA[{{ $article->title }}]]></title>
                    <abstract><![CDATA[{{ trim(html_entity_decode(strip_tags(str_replace('&nbsp;', ' ', $article->abstract)), ENT_QUOTES, 'UTF-8')) }}]]></abstract>
                    <pdfFileUrl><![CDATA[{{ route('journal.article.download.pdf', ['journal' => $journal->slug, 'seq_id' => $article->seq_id, 'filename' => \Str::slug($article->title)]) }}]]></pdfFileUrl>
                    <publicationDate>{{ $article->published_at ? $article->published_at->format('Y-m-d') : '' }}</publicationDate>
                </languageVersion>
                <authors>
                    @foreach($article->authors as $index => $author)
                        <author>
                            @php
                                $nameParts = explode(' ', trim($author->name));
                                $surname = count($nameParts) > 1 ? array_pop($nameParts) : '';
                                $name = count($nameParts) > 0 ? implode(' ', $nameParts) : $author->name;
                            @endphp
                            <name><![CDATA[{{ $name }}]]></name>
                            <surname><![CDATA[{{ $surname }}]]></surname>
                            <email><![CDATA[{{ $author->email }}]]></email>
                            <order>{{ $index + 1 }}</order>
                            <instituteAffiliation><![CDATA[{{ $author->affiliation }}]]></instituteAffiliation>
                            <role>AUTHOR</role>
                        </author>
                    @endforeach
                </authors>
                <keywords language="{{ $article->language ?? 'en' }}">
                    @foreach($article->keywords as $keyword)
                        <keyword><![CDATA[{{ trim($keyword->content) }}]]></keyword>
                    @endforeach
                </keywords>
                <references>
                    @php
                        $referencesStr = $article->currentPublication->references ?? $article->references;
                        $referencesArray = $referencesStr ? array_filter(array_map('trim', explode("\n", $referencesStr))) : [];
                    @endphp
                    @foreach($referencesArray as $reference)
                        <reference><![CDATA[{{ trim(strip_tags(html_entity_decode($reference))) }}]]></reference>
                    @endforeach
                </references>
                <doi><![CDATA[{{ $article->currentPublication?->doi ?? '' }}]]></doi>
            </article>
        @endforeach
    @endforeach
</ici-import>
