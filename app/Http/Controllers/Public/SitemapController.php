<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Submission;
use App\Models\Issue;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    /**
     * Generate the sitemap.xml
     */
    public function index()
    {
        $journals = \App\Models\Journal::where('enabled', true)
            ->where('visible', true)
            ->get();

        $issues = \App\Models\Issue::where('is_published', true)
            ->with('journal')
            ->get();

        $submissions = \App\Models\Submission::where('status', 'published')
            ->with(['journal', 'authors'])
            ->get();

        return response()->view('public.sitemap', [
            'journals' => $journals,
            'issues' => $issues,
            'submissions' => $submissions,
        ])->header('Content-Type', 'text/xml');
    }
}
