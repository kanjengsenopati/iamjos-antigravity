<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\ReviewRound;
use App\Models\ReviewAssignment;
use App\Models\SubmissionFile;
use App\Models\SubmissionLog;
use App\Models\User;
use App\Mail\RevisionRequestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ReviewWorkflowController extends Controller
{
    private function getJournal()
    {
        return current_journal();
    }

    /**
     * Assign a reviewer to the submission.
     */
    public function assignReviewer(Request $request, string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        $request->validate([
            'reviewer_id' => 'required|exists:users,id',
            'review_method' => 'required|in:double_blind,blind,open',
            'response_due_date' => 'required|date|after_or_equal:today',
            'review_due_date' => 'required|date|after:response_due_date',
        ]);

        DB::transaction(function () use ($request, $submission) {
            // Ensure a review round exists
            $reviewRound = $submission->currentReviewRound();
            if (!$reviewRound) {
                $reviewRound = ReviewRound::create([
                    'submission_id' => $submission->id,
                    'round' => 1,
                    'status' => ReviewRound::STATUS_PENDING,
                ]);
            }

            $assignment = ReviewAssignment::create([
                'submission_id' => $submission->id,
                'review_round_id' => $reviewRound->id,
                'reviewer_id' => $request->reviewer_id,
                'review_method' => $request->review_method,
                'response_due_date' => $request->response_due_date,
                'due_date' => $request->review_due_date,
                'assigned_at' => now(),
                'round' => $reviewRound->round,
                'status' => ReviewAssignment::STATUS_PENDING,
            ]);

            // Notify the reviewer about the assignment
            $reviewer = User::find($request->reviewer_id);
            if ($reviewer) {
                $reviewer->notify(new \App\Notifications\ReviewInvitation($assignment));

                // Log the event
                \App\Models\SubmissionLog::log(
                    $submission,
                    \App\Models\SubmissionLog::EVENT_REVIEWER_ASSIGNED,
                    'Reviewer Assigned',
                    auth()->user()->name . " assigned {$reviewer->name} as peer reviewer (Round {$reviewRound->round}).",
                    ['reviewer_id' => $reviewer->id, 'round' => $reviewRound->round]
                );
            }
        });

        return back()->with('success', 'Reviewer assigned successfully.');
    }

    /**
     * Unassign (soft delete) a reviewer.
     */
    public function unassignReviewer(string $journalSlug, Submission $submission, ReviewAssignment $assignment)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);
        if ($assignment->submission_id !== $submission->id) abort(404);

        $assignment->update(['status' => ReviewAssignment::STATUS_CANCELLED]);
        $assignment->delete();

        return back()->with('success', 'Reviewer unassigned.');
    }

    /**
     * Record the editor's decision.
     */
    public function recordDecision(Request $request, string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        $request->validate([
            'decision' => 'required|in:request_revisions,resubmit_for_review,accept,decline',
            'comments' => 'nullable|string',
        ]);

        $reviewRound = $submission->currentReviewRound();

        DB::transaction(function () use ($request, $submission, $reviewRound) {
            switch ($request->decision) {
                case 'request_revisions':
                    if ($reviewRound) {
                        $reviewRound->update(['status' => ReviewRound::STATUS_REVISIONS_REQUESTED]);
                    }
                    $submission->update(['status' => Submission::STATUS_REVISION_REQUIRED]);
                    break;

                case 'resubmit_for_review':
                    if ($reviewRound) {
                        $reviewRound->update(['status' => ReviewRound::STATUS_RESUBMIT_FOR_REVIEW]);
                    }
                    // Create new review round
                    ReviewRound::create([
                        'submission_id' => $submission->id,
                        'round' => ($reviewRound?->round ?? 0) + 1,
                        'status' => ReviewRound::STATUS_PENDING,
                    ]);
                    break;

                case 'accept':
                    if ($reviewRound) {
                        $reviewRound->update(['status' => ReviewRound::STATUS_APPROVED]);
                    }
                    // Move to Copyediting stage (stage_id = 3)
                    $submission->update(['stage_id' => 3]);
                    break;

                case 'decline':
                    if ($reviewRound) {
                        $reviewRound->update(['status' => ReviewRound::STATUS_DECLINED]);
                    }
                    $submission->update(['status' => Submission::STATUS_REJECTED]);
                    break;
            }
        });

        $messages = [
            'request_revisions' => 'Revisions requested from author.',
            'resubmit_for_review' => 'New review round created.',
            'accept' => 'Submission accepted and moved to Copyediting.',
            'decline' => 'Submission declined.',
        ];

        return back()->with('success', $messages[$request->decision] ?? 'Decision recorded.');
    }

    /**
     * Promote submission to Copyediting stage.
     */
    public function promoteToCopyediting(string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        $submission->update(['stage_id' => 3]);

        return back()->with('success', 'Submission moved to Copyediting.');
    }

    /**
     * Send submission to Production stage.
     */
    public function sendToProduction(string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        $submission->update(['stage_id' => 4]);

        return back()->with('success', 'Submission moved to Production.');
    }

    /**
     * Search reviewers/editors for assignment modals.
     * Supports ?role=editor to search for editors instead of reviewers.
     */
    public function searchReviewers(Request $request, string $journalSlug)
    {
        $query = $request->get('q', '');
        $roleFilter = $request->get('role', 'reviewer'); // Default to reviewer

        // Determine which roles to search for
        $searchRoles = match ($roleFilter) {
            'editor' => ['Editor', 'Section Editor', 'Journal Manager'],
            default => ['Reviewer'],
        };

        $users = User::whereHas('roles', function ($q) use ($searchRoles) {
            $q->whereIn('name', $searchRoles);
        })
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }

    /**
     * Request revisions from author (OJS 3.3 style).
     * Handles new review round creation, file promotion, and email notification.
     */
    public function requestRevisions(Request $request, string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        $validated = $request->validate([
            'new_review_round' => 'required|boolean',
            'send_email' => 'required|boolean',
            'email_body' => 'nullable|required_if:send_email,true|string',
            'selected_files' => 'nullable|array',
            'selected_files.*' => 'uuid|exists:submission_files,id',
        ]);

        $reviewRound = $submission->currentReviewRound();

        DB::transaction(function () use ($validated, $submission, $reviewRound, $journal) {
            // 1. Update submission status
            $submission->update([
                'status' => Submission::STATUS_REVISION_REQUIRED,
            ]);

            // 2. Update current review round status
            if ($reviewRound) {
                $reviewRound->update([
                    'status' => $validated['new_review_round']
                        ? ReviewRound::STATUS_RESUBMIT_FOR_REVIEW
                        : ReviewRound::STATUS_REVISIONS_REQUESTED,
                ]);
            }

            // 3. Create new review round if required
            if ($validated['new_review_round']) {
                ReviewRound::create([
                    'submission_id' => $submission->id,
                    'round' => ($reviewRound?->round ?? 0) + 1,
                    'status' => ReviewRound::STATUS_PENDING,
                ]);

                // Log new round creation
                SubmissionLog::log(
                    $submission,
                    'review_new_round',
                    'New Review Round Created',
                    'A new review round has been initiated for this submission.',
                    ['round' => ($reviewRound?->round ?? 0) + 1]
                );
            }

            // 4. Promote selected files to "revision" stage (author-visible)
            if (!empty($validated['selected_files'])) {
                foreach ($validated['selected_files'] as $fileId) {
                    $originalFile = SubmissionFile::find($fileId);
                    if ($originalFile && $originalFile->submission_id === $submission->id) {
                        // Create a copy with 'revision' stage for author visibility
                        SubmissionFile::create([
                            'submission_id' => $submission->id,
                            'uploaded_by' => auth()->id(),
                            'file_path' => $originalFile->file_path,
                            'file_name' => $originalFile->file_name,
                            'file_type' => SubmissionFile::TYPE_REVISION,
                            'mime_type' => $originalFile->mime_type,
                            'file_size' => $originalFile->file_size,
                            'version' => 1,
                            'stage' => 'revision', // Author-visible revision stage
                            'metadata' => [
                                'source_file_id' => $originalFile->id,
                                'shared_at' => now()->toISOString(),
                                'shared_by' => auth()->id(),
                                'decision_type' => 'revision_request',
                            ],
                        ]);
                    }
                }
            }

            // 5. Store decision in submission metadata
            $metadata = $submission->metadata ?? [];
            $metadata['decisions'] = $metadata['decisions'] ?? [];
            $metadata['decisions'][] = [
                'type' => 'revision_request',
                'new_review_round' => $validated['new_review_round'],
                'email_sent' => $validated['send_email'],
                'email_body' => $validated['email_body'] ?? null,
                'files_shared' => $validated['selected_files'] ?? [],
                'made_by' => auth()->id(),
                'made_at' => now()->toISOString(),
                'round' => $reviewRound?->round ?? 1,
            ];
            $submission->update(['metadata' => $metadata]);

            // 6. Log the decision
            SubmissionLog::log(
                $submission,
                SubmissionLog::EVENT_DECISION_MADE,
                'Revisions Requested',
                'Editor requested revisions from the author.' . ($validated['new_review_round'] ? ' A new review round will be required.' : ''),
                [
                    'decision' => 'revision_request',
                    'new_round' => $validated['new_review_round'],
                    'files_shared' => count($validated['selected_files'] ?? []),
                ]
            );
        });

        // 7. Send email notification (outside transaction for better error handling)
        if ($validated['send_email'] && !empty($validated['email_body'])) {
            $author = $submission->author ?? $submission->authors->first()?->user;

            if ($author && $author->email) {
                // Prepare attachments info for the email
                $attachmentFiles = [];
                if (!empty($validated['selected_files'])) {
                    $files = SubmissionFile::whereIn('id', $validated['selected_files'])->get();
                    foreach ($files as $file) {
                        $attachmentFiles[] = [
                            'path' => $file->file_path,
                            'name' => $file->file_name,
                            'mime' => $file->mime_type,
                        ];
                    }
                }

                try {
                    Mail::to($author->email)
                        ->send(new RevisionRequestMail(
                            $submission,
                            $validated['email_body'],
                            $attachmentFiles,
                            $validated['new_review_round']
                        ));

                    // Log email sent
                    SubmissionLog::log(
                        $submission,
                        'notification_sent',
                        'Email Sent',
                        "Revision request email sent to {$author->email}.",
                        ['recipient' => $author->email, 'type' => 'revision_request']
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send revision request email: ' . $e->getMessage());
                    // Continue even if email fails
                }
            }
        }

        return back()->with('success', 'Revisions requested successfully.' .
            ($validated['new_review_round'] ? ' A new review round has been created.' : ''));
    }

    /**
     * Get reviewer attachments for a submission.
     * Returns files uploaded by reviewers during their review.
     */
    public function getReviewerAttachments(string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        // Get files from completed reviews
        $reviewerFiles = SubmissionFile::where('submission_id', $submission->id)
            ->where('stage', 'review')
            ->with('uploader:id,name,email')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->file_name,
                    'size' => $file->file_size,
                    'uploader' => $file->uploader?->name ?? 'Unknown',
                    'uploaded_at' => $file->created_at->format('M d, Y'),
                    'type' => $file->file_type,
                ];
            });

        return response()->json(['files' => $reviewerFiles]);
    }

    /**
     * Upload a file specifically for the revision decision.
     */
    public function uploadDecisionFile(Request $request, string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        $request->validate([
            'file' => 'required|file|max:20480', // 20MB max
        ]);

        $file = $request->file('file');
        $path = $file->store("submissions/{$submission->id}/decision-files", 'local');

        $submissionFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'uploaded_by' => auth()->id(),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => 'decision_attachment',
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'version' => 1,
            'stage' => 'review',
            'metadata' => [
                'purpose' => 'revision_decision',
                'uploaded_at' => now()->toISOString(),
            ],
        ]);

        return response()->json([
            'id' => $submissionFile->id,
            'name' => $submissionFile->file_name,
            'size' => $submissionFile->file_size,
            'uploader' => auth()->user()->name,
        ]);
    }
}
