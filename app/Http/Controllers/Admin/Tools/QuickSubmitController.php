<?php
/**
 * Controller for Quick Submit plugin.
 * Allows editors to quickly add published articles.
 */

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Submission;
use App\Models\SubmissionAuthor;
use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuickSubmitController extends Controller
{
    public function index()
    {
        $journal = current_journal();
        $issues = Issue::where('journal_id', $journal->id)->latest()->get();
        $sections = $journal->sections;

        return view('manager.tools.quicksubmit.index', compact('journal', 'issues', 'sections'));
    }

    public function store(Request $request)
    {
        $journal = current_journal();

        $request->validate([
            'title' => 'required|string|max:500',
            'abstract' => 'required|string',
            'section_id' => 'required|exists:sections,id',
            'issue_id' => 'required|exists:issues,id',
            'authors' => 'required|array|min:1',
            'authors.*.given_name' => 'required|string',
            'authors.*.family_name' => 'nullable|string',
            'authors.*.email' => 'required|email',
            'file' => 'required|file|mimes:pdf|max:20480',
        ]);

        try {
            DB::beginTransaction();

            // 1. Create Submission
            $submission = Submission::create([
                'journal_id' => $journal->id,
                'issue_id' => $request->issue_id,
                'section_id' => $request->section_id,
                'user_id' => auth()->id(),
                'title' => $request->title,
                'abstract' => $request->abstract,
                'status' => Submission::STATUS_PUBLISHED,
                'stage' => Submission::STAGE_PRODUCTION,
                'submitted_at' => now(),
                'published_at' => now(),
            ]);

            // 2. Add Authors
            foreach ($request->authors as $index => $authorData) {
                SubmissionAuthor::create([
                    'submission_id' => $submission->id,
                    'given_name' => $authorData['given_name'],
                    'family_name' => $authorData['family_name'] ?? '',
                    'email' => $authorData['email'],
                    'affiliation' => $authorData['affiliation'] ?? '',
                    'is_primary' => $index === 0,
                    'seq' => $index,
                ]);
            }

            // 3. Handle File
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = Str::slug($request->title) . '.pdf';
                $path = "journals/{$journal->id}/submissions/{$submission->id}/{$filename}";
                
                Storage::disk('public')->put($path, file_get_contents($file));

                SubmissionFile::create([
                    'submission_id' => $submission->id,
                    'uploaded_by' => auth()->id(),
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => SubmissionFile::TYPE_MANUSCRIPT,
                    'mime_type' => 'application/pdf',
                    'file_size' => $file->getSize(),
                    'version' => 1,
                    'stage' => SubmissionFile::STAGE_PRODUCTION_READY,
                ]);
            }

            DB::commit();
            return redirect()->route('journal.settings.tools.index', ['journal' => $journal->slug])
                ->with('success', 'Article quickly submitted and published successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Quick Submit Failed: ' . $e->getMessage())->withInput();
        }
    }
}
