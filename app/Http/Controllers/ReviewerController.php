<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\ReviewAssignment;
use App\Models\SubmissionFile;
use App\Notifications\ReviewCompleted;
use App\Services\WaGateway;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ReviewerController extends Controller
{
    /**
     * Get the current journal from context.
     */
    protected function getJournal(): Journal
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal context not found.');
        }

        return $journal;
    }

    /**
     * Display list of submissions assigned to current reviewer for current journal.
     */
    public function index(): View
    {
        $user = auth()->user();
        $journal = $this->getJournal();

        // Filter assignments based on simple tab status
        $status = request('status', 'myqueue');

        $assignments = ReviewAssignment::where('reviewer_id', $user->id)
            ->whereHas('submission', function ($query) use ($journal) {
                $query->where('journal_id', $journal->id);
            });

        // Apply filters
        if ($status === 'myqueue') {
            $assignments->whereIn('status', [ReviewAssignment::STATUS_PENDING, ReviewAssignment::STATUS_ACCEPTED]);
        } elseif ($status === 'archives') {
            $assignments->where('status', ReviewAssignment::STATUS_COMPLETED);
        }

        $assignments = $assignments->with(['submission' => function ($query) {
            $query->with(['journal', 'section']);
        }])
            ->orderBy('due_date', 'asc') // Consistent sorting
            ->paginate(15)
            ->withQueryString();

        // Count by status for this journal (for tabs)
        $statusCounts = [
            'myqueue' => ReviewAssignment::where('reviewer_id', $user->id)
                ->whereHas('submission', fn($q) => $q->where('journal_id', $journal->id))
                ->whereIn('status', ['pending', 'accepted'])->count(),
            'archives' => ReviewAssignment::where('reviewer_id', $user->id)
                ->whereHas('submission', fn($q) => $q->where('journal_id', $journal->id))
                ->where('status', 'completed')->count(),
        ];

        return view('reviewer.index', compact('assignments', 'statusCounts', 'journal'));
    }

    /**
     * Accept review invitation.
     */
    public function accept(string $journalSlug, ReviewAssignment $assignment): RedirectResponse
    {
        $journal = $this->getJournal();
        $this->authorizeReviewer($assignment, $journal);

        if ($assignment->status !== ReviewAssignment::STATUS_PENDING) {
            return back()->with('error', 'This invitation has already been responded to.');
        }

        $assignment->update([
            'status' => ReviewAssignment::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);

        // Notify editors via WhatsApp
        try {
            $assignment->load(['reviewer', 'submission']);
            $editors = \App\Models\User::role(['Editor', 'Admin', 'Super Admin'])->get();
            foreach ($editors as $editor) {
                WaGateway::sendTemplate($editor, 'reviewer_accepted', [
                    'name' => $editor->name,
                    'reviewer_name' => $assignment->reviewer->name,
                    'title' => $assignment->submission->title ?? 'Naskah',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification for reviewer acceptance: ' . $e->getMessage());
        }

        return redirect()->route('journal.reviewer.show', ['journal' => $journal->slug, 'assignment' => $assignment])
            ->with('success', 'Review invitation accepted. You can now submit your review.');
    }

    /**
     * Decline review invitation.
     */
    public function decline(Request $request, string $journalSlug, ReviewAssignment $assignment): RedirectResponse
    {
        $journal = $this->getJournal();
        $this->authorizeReviewer($assignment, $journal);

        if ($assignment->status !== ReviewAssignment::STATUS_PENDING) {
            return back()->with('error', 'This invitation has already been responded to.');
        }

        $metadata = $assignment->metadata ?? [];
        $metadata['decline_reason'] = $request->input('reason');

        $assignment->update([
            'status' => ReviewAssignment::STATUS_DECLINED,
            'responded_at' => now(),
            'metadata' => $metadata,
        ]);

        // Notify editors via WhatsApp
        try {
            $assignment->load(['reviewer', 'submission']);
            $editors = \App\Models\User::role(['Editor', 'Admin', 'Super Admin'])->get();
            foreach ($editors as $editor) {
                WaGateway::sendTemplate($editor, 'reviewer_declined', [
                    'name' => $editor->name,
                    'reviewer_name' => $assignment->reviewer->name,
                    'title' => $assignment->submission->title ?? 'Naskah',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification for reviewer decline: ' . $e->getMessage());
        }

        return redirect()->route('journal.reviewer.index', ['journal' => $journal->slug])
            ->with('success', 'Review invitation declined.');
    }

    /**
     * Show review form for a specific submission.
     */
    public function show(string $journalSlug, ReviewAssignment $assignment): View
    {
        $journal = $this->getJournal();
        $this->authorizeReviewer($assignment, $journal);

        // Load submission with blind review (hide author info)
        $submission = $assignment->submission;
        $submission->load(['journal', 'section']);

        // Get manuscript files (latest version only)
        $manuscriptFiles = SubmissionFile::where('submission_id', $submission->id)
            ->whereIn('file_type', ['manuscript', 'revision'])
            ->orderBy('version', 'desc')
            ->get();

        return view('reviewer.show', compact('assignment', 'submission', 'manuscriptFiles', 'journal'));
    }

    /**
     * Submit the review.
     */
    public function submit(Request $request, string $journalSlug, ReviewAssignment $assignment): RedirectResponse
    {
        $journal = $this->getJournal();
        $this->authorizeReviewer($assignment, $journal);

        if (!in_array($assignment->status, [ReviewAssignment::STATUS_PENDING, ReviewAssignment::STATUS_ACCEPTED])) {
            return back()->with('error', 'This review cannot be submitted.');
        }

        $validated = $request->validate([
            'recommendation' => 'required|in:accept,minor_revision,major_revision,resubmit,reject',
            'comments_for_author' => 'required|string|min:10',
            'comments_for_editor' => 'nullable|string',
        ]);

        $assignment->update([
            'status' => ReviewAssignment::STATUS_COMPLETED,
            'recommendation' => $validated['recommendation'],
            'comments_for_author' => $validated['comments_for_author'],
            'comments_for_editor' => $validated['comments_for_editor'] ?? null,
            'completed_at' => now(),
        ]);

        // Notify editors that review is completed
        try {
            $this->notifyEditorsReviewCompleted($assignment);
        } catch (\Exception $e) {
            Log::error('Failed to notify editors about completed review: ' . $e->getMessage());
        }

        return redirect()->route('journal.reviewer.index', ['journal' => $journal->slug])
            ->with('success', 'Review submitted successfully. Thank you for your contribution!');
    }

    /**
     * Check if current user is the assigned reviewer and submission belongs to journal.
     */
    private function authorizeReviewer(ReviewAssignment $assignment, Journal $journal): void
    {
        if ($assignment->reviewer_id !== auth()->id()) {
            abort(403, 'You are not authorized to access this review.');
        }

        // Ensure the submission belongs to this journal
        if ($assignment->submission->journal_id !== $journal->id) {
            abort(404);
        }
    }

    /**
     * Notify editors when review is completed.
     */
    private function notifyEditorsReviewCompleted(ReviewAssignment $assignment): void
    {
        // Get editors with permission to make decisions
        $editors = \App\Models\User::role(['Editor', 'Admin', 'Super Admin'])->get();

        foreach ($editors as $editor) {
            // Send email notification
            $editor->notify(new ReviewCompleted($assignment));

            // Send WhatsApp notification
            WaGateway::sendTemplate($editor, 'review_submitted', [
                'name' => $editor->name,
                'title' => $assignment->submission->title ?? 'Naskah',
            ]);
        }
    }
}
