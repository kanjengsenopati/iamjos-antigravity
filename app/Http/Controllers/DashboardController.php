<?php

namespace App\Http\Controllers;

use App\Jobs\SendToWhatsappNotificationJob;
use App\Models\ReviewAssignment;
use App\Models\Submission;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the journal-specific dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal context not found.');
        }

        // Get user's submission stats for this journal
        $submissionStats = [
            'total' => Submission::where('user_id', $user->id)
                ->where('journal_id', $journal->id)
                ->count(),
            'in_review' => Submission::where('user_id', $user->id)
                ->where('journal_id', $journal->id)
                ->whereIn('status', [
                    Submission::STATUS_SUBMITTED,
                    Submission::STATUS_IN_REVIEW,
                ])->count(),
            'published' => Submission::where('user_id', $user->id)
                ->where('journal_id', $journal->id)
                ->where('status', Submission::STATUS_PUBLISHED)
                ->count(),
            'rejected' => Submission::where('user_id', $user->id)
                ->where('journal_id', $journal->id)
                ->where('status', Submission::STATUS_REJECTED)
                ->count(),
        ];

        // Get recent submissions for this journal
        $recentSubmissions = Submission::where('user_id', $user->id)
            ->where('journal_id', $journal->id)
            ->with(['journal', 'section'])
            ->latest()
            ->take(5)
            ->get();

        // For editors: get queue count for this journal
        $queueCount = 0;
        if ($user->hasAnyRole(['Editor', 'Admin', 'Super Admin'])) {
            $queueCount = Submission::where('journal_id', $journal->id)
                ->inQueue()
                ->count();
        }

        // For reviewers: get pending reviews for this journal
        $pendingReviews = 0;
        if ($user->hasRole('Reviewer')) {
            $pendingReviews = ReviewAssignment::where('reviewer_id', $user->id)
                ->whereHas('submission', function ($q) use ($journal) {
                    $q->where('journal_id', $journal->id);
                })
                ->pending()
                ->count();
        }

        return view('dashboard', compact(
            'journal',
            'submissionStats',
            'recentSubmissions',
            'queueCount',
            'pendingReviews'
        ));
    }
}
