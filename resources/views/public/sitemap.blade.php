<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {{-- 1. Portal Home --}}
    <url>
        <loc>{{ route('portal.home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    {{-- 2. Journal Homepages --}}
    @foreach($journals as $journal)
        <url>
            <loc>{{ route('journal.public.home', ['journal' => $journal->slug]) }}</loc>
            <lastmod>{{ $journal->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.9</priority>
        </url>
    @endforeach

    {{-- 3. Published Issues --}}
    @foreach($issues as $issue)
        @if($issue->journal)
            <url>
                <loc>{{ route('journal.public.issue', ['journal' => $issue->journal->slug, 'issue' => $issue->seq_id]) }}</loc>
                <lastmod>{{ $issue->published_at ? $issue->published_at->toAtomString() : $issue->updated_at->toAtomString() }}</lastmod>
                <changefreq>monthly</changefreq>
                <priority>0.8</priority>
            </url>
        @endif
    @endforeach

    {{-- 4. Published Articles --}}
    @foreach($submissions as $submission)
        @if($submission->journal)
            <url>
                <loc>{{ route('journal.public.article', ['journal' => $submission->journal->slug, 'article' => $submission->seq_id]) }}</loc>
                <lastmod>{{ $submission->published_at ? $submission->published_at->toAtomString() : $submission->updated_at->toAtomString() }}</lastmod>
                <changefreq>monthly</changefreq>
                <priority>0.7</priority>
            </url>
        @endif
    @endforeach
</urlset>
