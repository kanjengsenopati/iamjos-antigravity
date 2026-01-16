<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\ReviewRound;
use App\Models\ReviewAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
}
