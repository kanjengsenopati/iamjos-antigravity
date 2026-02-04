<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Submission;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate the sitemap.xml
     */
    public function index()
    {
        // 1. Fetch Enabled Journals
        $journals = Journal::where('enabled', true)->get();
        
        // 2. Fetch Published Articles (Status 3) with JOIN to publications
        // We need date_published for sorting and filtering
        $articles = Submission::join('publications', 'submissions.id', '=', 'publications.submission_id')
            ->where('submissions.status', 3) // Status Published
            ->whereNotNull('publications.date_published')
            ->select(
                'submissions.*', 
                'publications.date_published as pub_date' // Alias for easy access
            )
            ->with(['journal']) // Eager load journal for URL
            ->orderBy('publications.date_published', 'desc')
            ->limit(1000)
            ->get();

        return response()->view('public.sitemap', compact('journals', 'articles'))
            ->header('Content-Type', 'text/xml');
    }
}
