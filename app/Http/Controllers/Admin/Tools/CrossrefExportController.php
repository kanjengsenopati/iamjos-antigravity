<?php

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Submission;
use Illuminate\Http\Request;

class CrossrefExportController extends Controller
{
    /**
     * Display the OJS 3.3 Style Interface
     */
    public function index(Request $request, $journalPath)
    {
        $journal = Journal::where('path', $journalPath)->firstOrFail();
        
        // 1. Filter Logic (OJS Style)
        $status = $request->input('status', 'not_deposited'); // Default OJS usually shows 'not_deposited'
        $tab = $request->input('tab', 'articles');

        // 2. Base Query
        $query = Submission::where('journal_id', $journal->id)
            ->where('status', Submission::STATUS_PUBLISHED) // Only Published Articles
            ->with(['authors', 'issue']);

        // 3. Apply Status Filter (Simulated Logic)
        if ($status == 'not_deposited') {
            // Logic: Articles that don't have a 'registered' flag yet
            // $query->whereNull('doi_status'); 
        } elseif ($status == 'active') {
            // $query->where('doi_status', 'active');
        }

        // 4. Pagination
        $submissions = $query->latest('published_at')->paginate(20);

        return view('journal.tools.crossref_index', compact('journal', 'submissions', 'status', 'tab'));
    }

    // 2. XML Export Logic
    public function export(Request $request, $journalPath)
    {
        // 1. Fetch Data
        $journal = \App\Models\Journal::where('path', $journalPath)->firstOrFail();
        $ids = $request->input('submission_ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Please select at least one article.');
        }

        $submissions = \App\Models\Submission::whereIn('id', $ids)
            ->where('journal_id', $journal->id)
            ->with(['authors', 'issue'])
            ->get();

        $batchId = '_' . time();
        $filename = 'crossref-' . $journal->path . '-' . date('YmdHis') . '.xml';

        // 2. Render View (Without XML Header)
        $content = view('journal.tools.crossref_xml', compact('submissions', 'journal', 'batchId'))->render();

        // 3. Aggressive Cleaning (Remove BOM & Whitespace)
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // Remove BOM if present
        $content = trim($content);

        // 4. Construct Final XML
        $xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . $content;

        // 5. Clean System Buffer
        // Discard unwanted whitespace/newlines from system/config files
        if (ob_get_length()) ob_clean();

        // 6. Return Response
        return response($xml, 200, [
            'Content-Type' => 'text/xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
