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
            ->with([
                'issues' => fn($q) => $q->where('is_published', true),
                'submissions' => fn($q) => $q->where('status', 'published'),
                'announcements' => fn($q) => $q->where('is_active', true)
            ])
            ->get();

        return response()->view('public.sitemap', [
            'journals' => $journals,
        ])->header('Content-Type', 'text/xml');
    }
}
