<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Submission;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    /**
     * Generate the sitemap.xml
     */
    public function index()
    {
        // 1. Fetch Enabled Journals
        $journals = Journal::where('enabled', true)->get();

        // 2. Fetch Published Issues
        $issues = Issue::where('is_published', true)
            ->with('journal')
            ->orderBy('published_at', 'desc')
            ->get();
        
        // 3. Ambil Artikel (Submission)
        $articles = Submission::leftJoin('publications', 'submissions.id', '=', 'publications.submission_id')
            ->where('submissions.status', Submission::STATUS_PUBLISHED)
            ->select(
                'submissions.id',
                'submissions.seq_id',
                'submissions.updated_at',
                'submissions.journal_id',
                'submissions.slug',
                'submissions.title',
                'publications.date_published',
                DB::raw("COALESCE(publications.date_published, submissions.updated_at) as last_mod_date")
            )
            ->with(['journal', 'authors', 'galleys'])
            ->orderBy('last_mod_date', 'desc')
            ->limit(5000) // Increased limit
            ->get();

        return response()->view('public.sitemap', compact('journals', 'issues', 'articles'))
            ->header('Content-Type', 'text/xml');
    }
}
