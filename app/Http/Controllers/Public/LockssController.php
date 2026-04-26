<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Issue;
use Illuminate\Http\Request;

class LockssController extends Controller
{
    /**
     * LOCKSS Manifest Page
     */
    public function manifest(Journal $journal)
    {
        if (!$journal->enable_lockss) {
            abort(404);
        }

        $issues = Issue::where('journal_id', $journal->id)
            ->where('is_published', true)
            ->with(['submissions' => fn($q) => $q->where('status', \App\Models\Submission::STATUS_PUBLISHED)])
            ->orderBy('year', 'desc')
            ->orderBy('volume', 'desc')
            ->get();

        return response()->view('public.lockss.manifest', compact('journal', 'issues'))
            ->header('Content-Type', 'text/html');
    }

    /**
     * CLOCKSS Manifest Page
     */
    public function clockssManifest(Journal $journal)
    {
        if (!$journal->enable_clockss) {
            abort(404);
        }

        $issues = Issue::where('journal_id', $journal->id)
            ->where('is_published', true)
            ->with(['submissions' => fn($q) => $q->where('status', \App\Models\Submission::STATUS_PUBLISHED)])
            ->orderBy('year', 'desc')
            ->orderBy('volume', 'desc')
            ->get();

        return response()->view('public.lockss.clockss_manifest', compact('journal', 'issues'))
            ->header('Content-Type', 'text/html');
    }
}
