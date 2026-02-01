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
use Illuminate\Support\Str;
use App\Jobs\SendDecisionEmailJob;
use App\Services\WaGateway;

class ReviewWorkflowController extends Controller
{
    private function getJournal()
    {
        return current_journal();
    }

    /**
     * Show the assign reviewer page.
     */
    public function assignReviewerPage(string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        return view('submissions.review.assign', compact('journal', 'submission'));
    }

    /**
     * Assign a reviewer to the submission.
     */
    public function assignReviewer(Request $request, string $journalSlug, $id)
    {
        $submission = Submission::query()
        ->when(
            Str::isUuid($id),
            fn ($q) => $q->where('id', $id),
            fn ($q) => $q->where('slug', $id)
        )
        ->firstOrFail();
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        $request->validate([
            'reviewer_id' => 'required',
            'review_method' => 'required',
            'response_due_date' => 'required|date',
            'review_due_date' => 'required|date',
        ]);

        // Prevent duplicate assignment
        $currentRound = $submission->currentReviewRound();
        if ($currentRound) {
            $existing = ReviewAssignment::where('submission_id', $submission->id)
                ->where('review_round_id', $currentRound->id)
                ->where('reviewer_id', $request->reviewer_id)
                ->whereNotIn('status', [ReviewAssignment::STATUS_CANCELLED, ReviewAssignment::STATUS_DECLINED])
                ->exists();

            if ($existing) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'reviewer_id' => ['This reviewer is already assigned to this submission in the current round.']
                ]);
            }
        }

        try {
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

                // Notify reviewer
                $reviewer = User::find($request->reviewer_id);

                if ($reviewer) {
                    try {
                        $reviewer->notify(new \App\Notifications\ReviewInvitation($assignment));
                    } catch (\Throwable $e) {
                        Log::error('Review invitation notification failed', [
                            'submission_id' => $submission->id,
                            'reviewer_id' => $reviewer->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }

                    // Send WhatsApp notification to reviewer
                    try {
                        WaGateway::sendTemplate($reviewer, 'reviewer_assigned', [
                            'name' => $reviewer->name,
                            'title' => $submission->title,
                            'round' => $reviewRound->round,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('WhatsApp notification failed for reviewer assignment', [
                            'submission_id' => $submission->id,
                            'reviewer_id' => $reviewer->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }

                    SubmissionLog::log(
                        $submission,
                        SubmissionLog::EVENT_REVIEWER_ASSIGNED,
                        'Reviewer Assigned',
                        auth()->user()->name . " assigned {$reviewer->name} as peer reviewer (Round {$reviewRound->round}).",
                        [
                            'reviewer_id' => $reviewer->id,
                            'round' => $reviewRound->round,
                        ]
                    );
                }
            });

            return redirect()->route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $submission->slug])
                ->with('success', 'Reviewer assigned successfully.');
        } catch (\Throwable $e) {
            Log::error('Assign reviewer failed', [
                'submission_id' => $submission->id,
                'journal_id' => $submission->journal_id,
                'reviewer_id' => $request->reviewer_id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to assign reviewer. Please check logs.');
        }
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
            // New validation rules for accept decision
            'send_email' => 'sometimes|boolean',
            'email_body' => 'nullable|required_if:send_email,true|string',
            'selected_files' => 'nullable|array',
            'selected_files.*' => 'exists:submission_files,id',
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
                    // Status: queued_for_copyediting
                    $submission->update([
                        'stage_id' => 3, // Copyediting
                        'status' => 'queued_for_copyediting',
                        'accepted_at' => now(),
                    ]);

                    // Promote selected files to Copyediting stage as DRAFT files
                    if ($request->has('selected_files')) {
                        $filesToPromote = SubmissionFile::whereIn('id', $request->selected_files)->get();
                        foreach ($filesToPromote as $file) {
                            $newFile = $file->replicate();
                            $newFile->stage = SubmissionFile::STAGE_COPYEDIT_DRAFT; // Draft files for copyediting
                            $newFile->metadata = array_merge($file->metadata ?? [], [
                                'promoted_from_review_round' => $reviewRound->round ?? 1,
                                'decision_type' => 'accept',
                                'promoted_at' => now()->toIso8601String(),
                            ]);
                            $newFile->save();
                        }
                    }

                    // Email Handling
                    if ($request->boolean('send_email', true)) {
                        SendDecisionEmailJob::dispatch(
                            $submission,
                            $request->email_body,
                            'accepted'
                        );
                    }
                    break;

                case 'decline':
                    if ($reviewRound) {
                        $reviewRound->update(['status' => ReviewRound::STATUS_DECLINED]);
                    }
                    $submission->update(['status' => Submission::STATUS_REJECTED]);
                    break;
            }
        });

        // Send notifications to author (outside transaction for better error handling)
        $author = $submission->author ?? $submission->authors->first()?->user;

        if ($author) {
            // Determine WhatsApp template based on decision
            $waTemplate = match ($request->decision) {
                'accept' => 'submission_accepted',
                'decline' => 'submission_rejected',
                'request_revisions' => 'revision_request',
                default => 'decision_update',
            };

            $statusText = match ($request->decision) {
                'accept' => 'Diterima',
                'decline' => 'Ditolak',
                'request_revisions' => 'Perlu Revisi',
                default => 'Diperbarui',
            };

            // Send WhatsApp notification
            if ($author->phone) {
                try {
                    WaGateway::sendTemplate($author, $waTemplate, [
                        'name' => $author->name,
                        'title' => $submission->title,
                        'status' => $statusText,
                    ]);

                    // Log WhatsApp sent
                    SubmissionLog::log(
                        $submission,
                        'notification_sent',
                        'WhatsApp Sent',
                        "Decision notification WhatsApp sent to {$author->name} ({$request->decision}).",
                        [
                            'recipient' => $author->name,
                            'type' => $request->decision,
                            'channel' => 'whatsapp'
                        ]
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send WhatsApp notification for decision: ' . $e->getMessage(), [
                        'submission_id' => $submission->id,
                        'decision' => $request->decision,
                        'author_id' => $author->id,
                    ]);
                    // Continue even if WhatsApp fails
                }
            }
        }

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
     * Send submission to Production stage (OJS 3.3 Style).
     * Handles email notification and file promotion from Copyediting to Production.
     */
    public function sendToProduction(Request $request, string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        $validated = $request->validate([
            'send_email' => 'sometimes|boolean',
            'email_body' => 'nullable|required_if:send_email,true|string',
            'selected_files' => 'nullable|array',
            'selected_files.*' => 'exists:submission_files,id',
        ]);

        DB::transaction(function () use ($validated, $submission) {
            // 1. Update submission stage to Production and status
            $submission->update([
                'stage_id' => 4, // Production
                'status' => Submission::STATUS_IN_PRODUCTION ?? 'in_production',
            ]);

            // 2. Promote selected files to Production stage (PRODUCTION_READY)
            if (!empty($validated['selected_files'])) {
                $filesToPromote = SubmissionFile::whereIn('id', $validated['selected_files'])->get();
                foreach ($filesToPromote as $file) {
                    // Create a new file record with PRODUCTION stage
                    $newFile = $file->replicate();
                    $newFile->stage = SubmissionFile::STAGE_PRODUCTION_READY ?? 'production';
                    $newFile->metadata = array_merge($file->metadata ?? [], [
                        'promoted_from' => $file->stage,
                        'promoted_from_copyediting' => true,
                        'promoted_at' => now()->toIso8601String(),
                        'promoted_by' => auth()->id(),
                        'original_file_id' => $file->id,
                    ]);
                    $newFile->save();
                }
            }

            // 3. Store decision in submission metadata
            $metadata = $submission->metadata ?? [];
            $metadata['decisions'] = $metadata['decisions'] ?? [];
            $metadata['decisions'][] = [
                'type' => 'send_to_production',
                'email_sent' => $validated['send_email'] ?? false,
                'email_body' => $validated['email_body'] ?? null,
                'files_promoted' => $validated['selected_files'] ?? [],
                'made_by' => auth()->id(),
                'made_at' => now()->toIsoString(),
            ];
            $submission->update(['metadata' => $metadata]);

            // 4. Log the event
            SubmissionLog::log(
                $submission,
                SubmissionLog::EVENT_STAGE_CHANGED,
                'Sent to Production',
                auth()->user()->name . ' sent this submission to the Production stage.',
                [
                    'from_stage' => 3,
                    'to_stage' => 4,
                    'files_promoted' => count($validated['selected_files'] ?? []),
                ]
            );
        });

        // 5. Send email notification (outside transaction, queued)
        if ($validated['send_email'] ?? false) {
            SendDecisionEmailJob::dispatch(
                $submission,
                $validated['email_body'] ?? '',
                'send_to_production'
            );
        }

        return back()->with('success', 'Submission moved to Production stage.');
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
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('affiliation', 'like', "%{$query}%");
        })
        ->limit(10)
        ->get();

    // Transform users to include reviewer stats
    $results = $users->map(function ($user) {
        $assignments = $user->reviewAssignments;

        $completed = $assignments->where('status', ReviewAssignment::STATUS_COMPLETED);
        $active = $assignments->whereIn('status', [ReviewAssignment::STATUS_PENDING, ReviewAssignment::STATUS_ACCEPTED]);
        $declined = $assignments->where('status', ReviewAssignment::STATUS_DECLINED);
        $cancelled = $assignments->where('status', ReviewAssignment::STATUS_CANCELLED);

        // Calculate avg completion days
        $avgCompletionDays = $completed->avg(function ($assignment) {
            if ($assignment->assigned_at && $assignment->completed_at) {
                return $assignment->assigned_at->diffInDays($assignment->completed_at);
            }
            return null;
        });

        // Days since last assignment
        $lastAssignment = $assignments->sortByDesc('assigned_at')->first();
        $daysSinceLast = $lastAssignment && $lastAssignment->assigned_at 
            ? $lastAssignment->assigned_at->diffInDays(now()) 
            : null;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'affiliation' => $user->affiliation,
            'avg_rating' => $completed->avg('quality_rating'),
            'active_count' => $active->count(),
            'completed_count' => $completed->count(),
            'declined_count' => $declined->count(),
            'cancelled_count' => $cancelled->count(),
            'days_since_last' => $daysSinceLast !== null ? $daysSinceLast . ' days' : 'Never',
            'avg_completion_days' => $avgCompletionDays ? round($avgCompletionDays, 1) . ' days' : '-',
        ];
    });

    return response()->json($results);
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

        // Send WhatsApp notification to author
        if ($author && $author->phone) {
            try {
                WaGateway::sendTemplate($author, 'revision_request', [
                    'name' => $author->name,
                    'title' => $submission->title,
                ]);

                // Log WhatsApp sent
                SubmissionLog::log(
                    $submission,
                    'notification_sent',
                    'WhatsApp Sent',
                    "Revision request WhatsApp sent to {$author->name}.",
                    ['recipient' => $author->name, 'type' => 'revision_request', 'channel' => 'whatsapp']
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send revision request WhatsApp: ' . $e->getMessage());
                // Continue even if WhatsApp fails
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

    /**
     * Create a new review round (OJS 3.3 Multi-Round Review).
     * Promotes selected revision files to become review files for the new round.
     */
    public function createNewRound(Request $request, string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        $validated = $request->validate([
            'selected_files' => 'nullable|array',
            'selected_files.*' => 'exists:submission_files,id',
        ]);

        $currentRound = $submission->currentReviewRound();

        // Check if there's already a pending round (created by Request Revisions with new_round_required)
        $existingPendingRound = ReviewRound::where('submission_id', $submission->id)
            ->where('status', ReviewRound::STATUS_PENDING)
            ->where('round', '>', $currentRound?->round ?? 0)
            ->first();

        $newRound = null;

        DB::transaction(function () use ($validated, $submission, $currentRound, $existingPendingRound, &$newRound) {
            // If there's already a pending new round, use it
            if ($existingPendingRound) {
                $newRound = $existingPendingRound;
                $newRoundNumber = $existingPendingRound->round;
            } else {
                // Mark current round as complete
                if ($currentRound) {
                    $currentRound->update([
                        'status' => 'completed',
                    ]);
                }

                // Create new review round
                $newRoundNumber = ($currentRound?->round ?? 0) + 1;
                $newRound = ReviewRound::create([
                    'submission_id' => $submission->id,
                    'round' => $newRoundNumber,
                    'status' => ReviewRound::STATUS_PENDING,
                ]);
            }

            // 3. Promote selected revision files to review files for new round
            if (!empty($validated['selected_files'])) {
                foreach ($validated['selected_files'] as $fileId) {
                    $originalFile = SubmissionFile::find($fileId);
                    if ($originalFile && $originalFile->submission_id === $submission->id) {
                        // Create a copy as a review file for the new round
                        SubmissionFile::create([
                            'submission_id' => $submission->id,
                            'uploaded_by' => auth()->id(),
                            'file_path' => $originalFile->file_path,
                            'file_name' => $originalFile->file_name,
                            'file_type' => SubmissionFile::TYPE_MANUSCRIPT, // Now it's a manuscript for review
                            'mime_type' => $originalFile->mime_type,
                            'file_size' => $originalFile->file_size,
                            'version' => $originalFile->version,
                            'stage' => 'review', // Review stage files
                            'metadata' => [
                                'source_file_id' => $originalFile->id,
                                'promoted_from' => 'revision',
                                'promoted_at' => now()->toISOString(),
                                'promoted_by' => auth()->id(),
                                'review_round' => $newRoundNumber,
                            ],
                        ]);
                    }
                }
            }

            // 4. Update submission status to "queued for review" / "in_review"
            $submission->update([
                'status' => Submission::STATUS_IN_REVIEW,
            ]);

            // 5. Log the event
            SubmissionLog::log(
                $submission,
                'review_new_round',
                'New Review Round Created',
                "Round {$newRound->round} has been created. The submission is now queued for review.",
                [
                    'round' => $newRound->round,
                    'files_promoted' => count($validated['selected_files'] ?? []),
                    'created_by' => auth()->id(),
                ]
            );
        });

        return back()->with('success', "Review Round {$newRound->round} has been created successfully.");
    }

    /**
     * Get revision files for the new round modal.
     * Returns files uploaded by the author as revisions.
     */
    public function getRevisionFiles(Request $request, string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        // Get revision files uploaded by the author
        $files = SubmissionFile::where('submission_id', $submission->id)
            ->where('stage', 'revision')
            ->where('file_type', 'revision')
            ->with('uploader:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->file_name,
                    'size' => $file->file_size,
                    'uploaded_at' => $file->created_at->format('M d, Y'),
                    'uploader' => $file->uploader?->name ?? 'Unknown',
                ];
            });

        return response()->json($files);
    }

    /**
     * Get promotable files (Reviewer Attachments + Revisions) for Accept Decision modal.
     */
    public function getPromotableFiles(string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        // 1. Reviewer Files (stage = review)
        $reviewerFiles = SubmissionFile::where('submission_id', $submission->id)
            ->where('stage', 'review')
            ->with('uploader:id,name')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->file_name,
                    'size' => $file->file_size,
                    'source' => 'Reviewer Attachment (' . ($file->uploader->name ?? 'Unknown') . ')',
                    'created_at' => $file->created_at->format('M d, Y'),
                    'type' => 'reviewer',
                ];
            });

        // 2. Author Revisions (stage = revision)
        $revisionFiles = SubmissionFile::where('submission_id', $submission->id)
            ->where('stage', 'revision')
            ->with('uploader:id,name')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->file_name,
                    'size' => $file->file_size,
                    'source' => 'Author Revision',
                    'created_at' => $file->created_at->format('M d, Y'),
                    'type' => 'revision',
                ];
            });

        return response()->json([
            'files' => $reviewerFiles->merge($revisionFiles)->values(),
        ]);
    }

    /**
     * Get review stage files for copying to Draft Files (Copyediting Stage).
     * Returns files from the review stage that can be promoted to copyedit_draft.
     */
    public function getReviewStageFiles(string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        $reviewFiles = SubmissionFile::where('submission_id', $submission->id)
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

        return response()->json(['files' => $reviewFiles]);
    }

    /**
     * Copy selected review files to Draft Files (copyedit_draft stage).
     */
    public function copyReviewFilesToDraft(Request $request, string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'exists:submission_files,id',
        ]);

        $copiedFiles = [];
        foreach ($request->file_ids as $fileId) {
            $originalFile = SubmissionFile::findOrFail($fileId);

            // Create a copy with copyedit_draft stage
            $newFile = SubmissionFile::create([
                'submission_id' => $submission->id,
                'uploaded_by' => auth()->id(),
                'file_path' => $originalFile->file_path, // Same path (reference)
                'file_name' => $originalFile->file_name,
                'file_type' => $originalFile->file_type,
                'mime_type' => $originalFile->mime_type,
                'file_size' => $originalFile->file_size,
                'version' => $originalFile->version,
                'stage' => SubmissionFile::STAGE_COPYEDIT_DRAFT,
                'metadata' => array_merge(
                    $originalFile->metadata ?? [],
                    ['copied_from_review' => true, 'original_file_id' => $fileId]
                ),
            ]);

            $copiedFiles[] = $newFile;
        }

        return response()->json([
            'success' => true,
            'message' => count($copiedFiles) . ' file(s) copied to Draft Files.',
            'files' => $copiedFiles,
        ]);
    }
}
