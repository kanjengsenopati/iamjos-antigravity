<?php

namespace App\Http\Controllers\Journal;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    /**
     * Display a listing of issues for the current journal
     */
    public function index()
    {
        $journal = current_journal();

        $issues = Issue::where('journal_id', $journal->id)
            ->orderBy('year', 'desc')
            ->orderBy('volume', 'desc')
            ->orderBy('number', 'desc')
            ->withCount('submissions')
            ->paginate(20);

        return view('editor.issues.index', compact('journal', 'issues'));
    }

    /**
     * Store a newly created issue
     */
    public function store(Request $request)
    {
        $journal = current_journal();

        $validated = $request->validate([
            'volume' => 'required|integer|min:1',
            'number' => 'required|integer|min:1',
            'year' => 'required|integer|min:1900|max:2100',
            'title' => 'nullable|string|max:255',
        ]);

        Issue::create([
            'journal_id' => $journal->id,
            'volume' => $validated['volume'],
            'number' => $validated['number'],
            'year' => $validated['year'],
            'title' => $validated['title'] ?? null,
            'is_published' => false,
        ]);

        return back()->with('success', 'Issue created successfully.');
    }

    /**
     * Update the specified issue
     */
    public function update(Request $request, Issue $issue)
    {
        $validated = $request->validate([
            'volume' => 'required|integer|min:1',
            'number' => 'required|integer|min:1',
            'year' => 'required|integer|min:1900|max:2100',
            'title' => 'nullable|string|max:255',
        ]);

        $issue->update($validated);

        return back()->with('success', 'Issue updated successfully.');
    }

    /**
     * Publish the issue
     */
    public function publish(Issue $issue)
    {
        $issue->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        return back()->with('success', "Issue {$issue->identifier} has been published.");
    }

    /**
     * Unpublish the issue
     */
    public function unpublish(Issue $issue)
    {
        $issue->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        return back()->with('success', "Issue {$issue->identifier} has been unpublished.");
    }

    /**
     * Remove the specified issue
     */
    public function destroy(Issue $issue)
    {
        // Check if issue has any submissions
        if ($issue->submissions()->exists()) {
            return back()->with('error', 'Cannot delete issue with assigned submissions.');
        }

        $issue->delete();

        return back()->with('success', 'Issue deleted successfully.');
    }
}
