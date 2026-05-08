{{ '<?xml version="1.0" encoding="UTF-8"?>' }}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {{-- 1. Portal Home --}}
    <url>
        <loc>{{ route('portal.home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    {{-- 2. Journal Content --}}
    @foreach($journals as $journal)
        {{-- Journal Home --}}
        <url>
            <loc>{{ route('journal.public.home', ['journal' => $journal->slug]) }}</loc>
            <lastmod>{{ $journal->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.9</priority>
        </url>

        {{-- Static Journal Pages --}}
        @php
            $staticPages = [
                'journal.public.current',
                'journal.public.archives',
                'journal.public.about',
                'journal.public.editorial-team',
                'journal.public.author-guidelines',
                'journal.public.search',
                'journal.announcement.index',
                'journal.register',
                'journal.login',
            ];
        @endphp

        @foreach($staticPages as $route)
            @if(Route::has($route))
                <url>
                    <loc>{{ route($route, ['journal' => $journal->slug]) }}</loc>
                    <changefreq>monthly</changefreq>
                    <priority>0.5</priority>
                </url>
            @endif
        @endforeach

        {{-- Individual Announcements --}}
        @foreach($journal->announcements as $announcement)
            <url>
                <loc>{{ route('journal.announcement.show', ['journal' => $journal->slug, 'id' => $announcement->id]) }}</loc>
                <lastmod>{{ $announcement->updated_at->toAtomString() }}</lastmod>
                <changefreq>monthly</changefreq>
                <priority>0.4</priority>
            </url>
        @endforeach

        {{-- Issues of this Journal --}}
        @foreach($journal->issues as $issue)
            <url>
                <loc>{{ route('journal.public.issue', ['journal' => $journal->slug, 'issue' => $issue->seq_id]) }}</loc>
                <lastmod>{{ $issue->published_at ? $issue->published_at->toAtomString() : $issue->updated_at->toAtomString() }}</lastmod>
                <changefreq>monthly</changefreq>
                <priority>0.8</priority>
            </url>
        @endforeach

        {{-- Articles of this Journal --}}
        @foreach($journal->submissions as $submission)
            <url>
                <loc>{{ route('journal.public.article', ['journal' => $journal->slug, 'article' => $submission->seq_id]) }}</loc>
                <lastmod>{{ $submission->published_at ? $submission->published_at->toAtomString() : $submission->updated_at->toAtomString() }}</lastmod>
                <changefreq>monthly</changefreq>
                <priority>0.7</priority>
            </url>
        @endforeach
    @endforeach
</urlset>
