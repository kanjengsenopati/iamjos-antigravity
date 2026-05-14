<?php
/**
 * Controller for exporting metadata in DataCite format.
 */

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataciteExportController extends Controller
{
    /**
     * Display the DataCite Export page.
     */
    public function index()
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found');
        }

        $submissions = Submission::where('journal_id', $journal->id)
            ->where('status', Submission::STATUS_PUBLISHED)
            ->with(['authors', 'issue', 'currentPublication'])
            ->latest('published_at')
            ->get();

        return view('manager.tools.importexport.datacite', compact('journal', 'submissions'));
    }

    /**
     * Export selected articles as DataCite XML.
     */
    public function export(Request $request): StreamedResponse
    {
        $journal = current_journal();
        $ids = $request->input('submission_ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Please select at least one article to export.');
        }

        $submissions = Submission::whereIn('id', $ids)
            ->where('journal_id', $journal->id)
            ->with(['authors', 'issue', 'currentPublication'])
            ->get();

        $filename = 'datacite-' . date('Ymd-His') . '.xml';

        return response()->streamDownload(function () use ($submissions, $journal) {
            echo view('manager.tools.importexport.datacite_xml', compact('submissions', 'journal'))->render();
        }, $filename, ['Content-Type' => 'text/xml']);
    }
}
