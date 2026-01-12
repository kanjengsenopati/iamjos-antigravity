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

        // If only one journal exists, redirect directly to its dashboard
        if ($journals->count() === 1) {
            return redirect()->route('journal.dashboard', ['journal' => $journals->first()->slug]);
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

        // If only one journal, redirect to it
        if ($journals->count() === 1) {
            return redirect()->route('journal.dashboard', ['journal' => $journals->first()->slug]);
        }

        // Otherwise show selection page
        return redirect()->route('journal.select');
    }
}
