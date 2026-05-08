<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    /**
     * Generate the sitemap.xml
     *
     * Generates XML directly in PHP to avoid Blade <?xml short tag conflicts.
     * This approach is immune to PHP short_open_tag settings.
     */
    public function index()
    {
        $journals = Journal::where('enabled', true)
            ->where('visible', true)
            ->with([
                'issues' => fn($q) => $q->where('is_published', true),
                'submissions' => fn($q) => $q->where('status', 'published'),
                'announcements' => fn($q) => $q->where('is_active', true),
            ])
            ->get();

        $xml = $this->generateXml($journals);

        return response($xml, 200)
            ->header('Content-Type', 'text/xml; charset=UTF-8');
    }

    /**
     * Build the full sitemap XML string.
     */
    protected function generateXml($journals): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // 1. Portal Home
        $xml .= $this->urlEntry(route('portal.home'), null, 'daily', '1.0');

        // 2. Per-Journal Content
        foreach ($journals as $journal) {
            if (empty($journal->slug)) {
                continue;
            }

            // Journal Home
            $xml .= $this->urlEntry(
                route('journal.public.home', ['journal' => $journal->slug]),
                $journal->updated_at?->toAtomString(),
                'weekly',
                '0.9'
            );

            // Static Journal Pages
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

            foreach ($staticPages as $routeName) {
                if (Route::has($routeName)) {
                    try {
                        $xml .= $this->urlEntry(
                            route($routeName, ['journal' => $journal->slug]),
                            null,
                            'monthly',
                            '0.5'
                        );
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            // Individual Announcements
            foreach ($journal->announcements as $announcement) {
                if (empty($announcement->id)) continue;
                $xml .= $this->urlEntry(
                    route('journal.announcement.show', ['journal' => $journal->slug, 'id' => $announcement->id]),
                    $announcement->updated_at?->toAtomString(),
                    'monthly',
                    '0.4'
                );
            }

            // Published Issues
            foreach ($journal->issues as $issue) {
                if (empty($issue->seq_id)) continue;
                $xml .= $this->urlEntry(
                    route('journal.public.issue', ['journal' => $journal->slug, 'issue' => $issue->seq_id]),
                    $issue->published_at?->toAtomString() ?? $issue->updated_at?->toAtomString(),
                    'monthly',
                    '0.8'
                );
            }

            // Published Articles
            foreach ($journal->submissions as $submission) {
                if (empty($submission->seq_id)) continue;
                $xml .= $this->urlEntry(
                    route('journal.public.article', ['journal' => $journal->slug, 'article' => $submission->seq_id]),
                    $submission->published_at?->toAtomString() ?? $submission->updated_at?->toAtomString(),
                    'monthly',
                    '0.7'
                );
            }
        }

        $xml .= '</urlset>' . "\n";

        return $xml;
    }

    /**
     * Generate a single <url> entry.
     */
    protected function urlEntry(string $loc, ?string $lastmod = null, string $changefreq = 'monthly', string $priority = '0.5'): string
    {
        $entry  = "  <url>\n";
        $entry .= "    <loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";

        if ($lastmod) {
            $entry .= "    <lastmod>{$lastmod}</lastmod>\n";
        }

        $entry .= "    <changefreq>{$changefreq}</changefreq>\n";
        $entry .= "    <priority>{$priority}</priority>\n";
        $entry .= "  </url>\n";

        return $entry;
    }
}
