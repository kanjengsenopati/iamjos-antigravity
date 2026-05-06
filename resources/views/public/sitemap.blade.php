{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {{-- 1. Main Landing Page --}}
    <url>
        <loc>{{ route('portal.home') }}</loc>
        <lastmod>{{ now()->format('Y-m-d') }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    {{-- 2. Journal Homepages --}}
    @foreach ($journals as $journal)
        <url>
            <loc>{{ route('journal.public.home', $journal->slug) }}</loc>
            <lastmod>{{ now()->format('Y-m-d') }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.9</priority>
        </url>
    @endforeach

    {{-- 3. Issues (ToC Pages) --}}
    @foreach ($issues as $issue)
        @if ($issue->journal)
            <url>
                <loc>{{ route('journal.public.issue', ['journal' => $issue->journal->slug, 'issue' => $issue->seq_id]) }}</loc>
                <lastmod>{{ $issue->published_at ? $issue->published_at->format('Y-m-d') : $issue->updated_at->format('Y-m-d') }}</lastmod>
                <changefreq>monthly</changefreq>
                <priority>0.8</priority>
            </url>
        @endif
    @endforeach

    {{-- 4. Articles --}}
    @foreach ($articles as $article)
        @if ($article->journal)
            {{-- Landing Page --}}
            <url>
                <loc>{{ route('journal.public.article', ['journal' => $article->journal->slug, 'article' => $article->seq_id]) }}</loc>
                <lastmod>{{ \Carbon\Carbon::parse($article->last_mod_date)->format('Y-m-d') }}</lastmod>
                <changefreq>monthly</changefreq>
                <priority>0.7</priority>
            </url>

            {{-- Direct PDF Link (Google Scholar Recommendation) --}}
            @php
                $pdfGalley = $article->galleys->first();
                $safeAuthor = \Illuminate\Support\Str::slug($article->authors?->first()?->last_name ?? 'article');
                $safeTitle = \Illuminate\Support\Str::slug(\Illuminate\Support\Str::limit($article->title, 30, ''));
                $safeYear = \Carbon\Carbon::parse($article->last_mod_date)->format('Y');
                $seoFilename = "{$safeAuthor}-{$safeTitle}-{$safeYear}";
            @endphp
            @if ($pdfGalley)
                <url>
                    <loc>{{ route('journal.article.download.pdf', ['journal' => $article->journal->slug, 'seq_id' => $article->seq_id, 'filename' => $seoFilename]) }}</loc>
                    <lastmod>{{ \Carbon\Carbon::parse($article->last_mod_date)->format('Y-m-d') }}</lastmod>
                    <changefreq>monthly</changefreq>
                    <priority>0.6</priority>
                </url>
            @endif
        @endif
    @endforeach
</urlset>
