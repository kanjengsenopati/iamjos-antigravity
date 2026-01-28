<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\JournalUserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JournalSelectController extends Controller
{
    /**
     * Display journal selection page.
     * Shows only journals the user is registered with.
     */
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        // Get journals the user is registered with
        $journals = JournalUserRole::getUserJournals($user);

        // If user has no journals, show all enabled journals with option to join
        if ($journals->isEmpty()) {
            $journals = Journal::where('enabled', true)
                ->orderBy('name')
                ->get();
                
            return view('journal-select', [
                'journals' => $journals,
                'userJournals' => collect([]),
                'showJoinOption' => true,
            ]);
        }

        // If only one journal exists, redirect based on user role (OJS 3.3 style)
        if ($journals->count() === 1) {
            $journal = $journals->first();

            // Reviewers go to their review assignments
            if ($user->hasRole('Reviewer')) {
                return redirect()->route('journal.reviewer.index', ['journal' => $journal->slug]);
            }

            // All other users go to submissions
            return redirect()->route('journal.submissions.index', ['journal' => $journal->slug]);
        }

        return view('journal-select', [
            'journals' => $journals,
            'userJournals' => $journals,
            'showJoinOption' => false,
        ]);
    }

    /**
     * Redirect from old /dashboard to journal select or first journal.
     */
    public function redirectToDashboard(): RedirectResponse
    {
        $user = auth()->user();

        // Get journals the user is registered with
        $journals = JournalUserRole::getUserJournals($user);

        if ($journals->isEmpty()) {
            // User has no journals, redirect to selection page
            return redirect()->route('journal.select');
        }

        // If only one journal, redirect based on user role (OJS 3.3 style)
        if ($journals->count() === 1) {
            $journal = $journals->first();

            // Reviewers go to their review assignments
            if ($user->hasRole('Reviewer')) {
                return redirect()->route('journal.reviewer.index', ['journal' => $journal->slug]);
            }

            // All other users go to submissions
            return redirect()->route('journal.submissions.index', ['journal' => $journal->slug]);
        }

        // Otherwise show selection page
        return redirect()->route('journal.select');
    }
}
