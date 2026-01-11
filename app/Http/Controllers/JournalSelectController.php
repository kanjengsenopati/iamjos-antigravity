<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JournalSelectController extends Controller
{
    /**
     * Display journal selection page.
     */
    public function index(): View|RedirectResponse
    {
        $journals = Journal::where('enabled', true)
            ->orderBy('name')
            ->get();

        // If only one journal exists, redirect directly to its dashboard
        if ($journals->count() === 1) {
            return redirect()->route('journal.dashboard', ['journal' => $journals->first()->slug]);
        }

        // If no journals exist, show error
        if ($journals->isEmpty()) {
            abort(404, 'No journals available.');
        }

        return view('journal-select', compact('journals'));
    }

    /**
     * Redirect from old /dashboard to journal select or first journal.
     */
    public function redirectToDashboard(): RedirectResponse
    {
        $user = auth()->user();

        // Get journals accessible to user (for now, all enabled journals)
        $journals = Journal::where('enabled', true)->orderBy('name')->get();

        if ($journals->isEmpty()) {
            abort(404, 'No journals available.');
        }

        // If only one journal, redirect to it
        if ($journals->count() === 1) {
            return redirect()->route('journal.dashboard', ['journal' => $journals->first()->slug]);
        }

        // Otherwise show selection page
        return redirect()->route('journal.select');
    }
}
