<?php

namespace App\Http\Controllers\Journal;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\PublicationGalley;
use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductionWorkflowController extends Controller
{
    /**
     * Store a new galley for the submission
     */
    public function storeGalley(Request $request, $journal, Submission $submission)
    {
        $request->validate([
            'label' => 'required|string|max:50',
            'file' => 'required|file|max:51200', // 50MB max
            'locale' => 'nullable|string|max:5',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

        // Generate unique filename
        $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME))
            . '-' . Str::random(8)
            . '.' . $extension;

        // Store file in galleys directory
        $path = $file->storeAs(
            "journals/{$submission->journal_id}/galleys/{$submission->id}",
            $filename,
            'public'
        );

        // Create SubmissionFile record
        $submissionFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'user_id' => auth()->id(),
            'file_name' => $originalName,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
            'stage' => 'production',
            'type' => 'galley',
        ]);

        // Create PublicationGalley record
        $galley = PublicationGalley::create([
            'submission_id' => $submission->id,
            'file_id' => $submissionFile->id,
            'label' => strtoupper($request->label),
            'locale' => $request->locale ?? 'en',
            'seq' => $submission->galleys()->count(),
        ]);

        return back()->with('success', "Galley '{$galley->label}' has been uploaded successfully.");
    }

    /**
     * Update galley details
     */
    public function updateGalley(Request $request, $journal, Submission $submission, PublicationGalley $galley)
    {
        $request->validate([
            'label' => 'required|string|max:50',
            'locale' => 'nullable|string|max:5',
        ]);

        $galley->update([
            'label' => strtoupper($request->label),
            'locale' => $request->locale ?? $galley->locale,
        ]);

        return back()->with('success', 'Galley updated successfully.');
    }

    /**
     * Delete a galley
     */
    public function destroyGalley($journal, Submission $submission, PublicationGalley $galley)
    {
        // Delete the associated file from storage
        if ($galley->file) {
            Storage::disk('public')->delete($galley->file->file_path);
            $galley->file->delete();
        }

        $galley->delete();

        return back()->with('success', 'Galley has been removed.');
    }

    /**
     * Assign submission to an issue
     */
    public function assignToIssue(Request $request, $journal, Submission $submission)
    {
        $request->validate([
            'issue_id' => 'required|uuid|exists:issues,id',
        ]);

        $issue = Issue::findOrFail($request->issue_id);

        $submission->update([
            'issue_id' => $issue->id,
        ]);

        return back()->with('success', "Submission scheduled for {$issue->identifier}.");
    }

    /**
     * Unschedule (remove from issue)
     */
    public function unschedule($journal, Submission $submission)
    {
        $submission->update([
            'issue_id' => null,
        ]);

        return back()->with('success', 'Submission has been unscheduled.');
    }

    /**
     * Publish the submission
     */
    public function publish(Request $request, $journal, Submission $submission)
    {
        // Validate prerequisites
        if (!$submission->hasGalleys()) {
            return back()->with('error', 'Cannot publish: No galley files uploaded. Please upload at least one galley (PDF) first.');
        }

        if (!$submission->issue_id) {
            return back()->with('error', 'Cannot publish: Submission is not assigned to an issue. Please schedule for publication first.');
        }

        // Update submission status
        $submission->update([
            'status' => Submission::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        // TODO: Trigger SubmissionPublished event to notify author
        // event(new SubmissionPublished($submission));

        return back()->with('success', 'Submission has been published successfully!');
    }

    /**
     * Unpublish the submission
     */
    public function unpublish($journal, Submission $submission)
    {
        $submission->update([
            'status' => Submission::STATUS_ACCEPTED, // Revert to accepted
            'published_at' => null,
        ]);

        return back()->with('success', 'Submission has been unpublished.');
    }

    /**
     * Get issues for dropdown (API endpoint)
     */
    public function getIssues($journal)
    {
        $journalModel = current_journal();

        $issues = Issue::where('journal_id', $journalModel->id)
            ->orderBy('year', 'desc')
            ->orderBy('volume', 'desc')
            ->orderBy('number', 'desc')
            ->get()
            ->map(function ($issue) {
                return [
                    'id' => $issue->id,
                    'label' => $issue->identifier,
                    'title' => $issue->title,
                    'is_published' => $issue->is_published,
                    'status_label' => $issue->is_published ? 'Published' : 'Future',
                ];
            });

        return response()->json($issues);
    }
}
