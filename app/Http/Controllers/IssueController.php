<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Journal;
use App\Models\Submission;
use App\Models\SubmissionLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class IssueController extends Controller
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
     * Display a listing of issues for editors.
     */
    public function index(Request $request): View
    {
        $journal = $this->getJournal();

        // Get all issues with counts for this journal
        $allIssues = Issue::where('journal_id', $journal->id)
            ->withCount('submissions')
            ->latest()
            ->get();

        // Stats
        $totalIssues = $allIssues->count();
        $publishedCount = $allIssues->where('is_published', true)->count();
        $upcomingCount = $allIssues->where('is_published', false)->count();
        $totalArticles = Submission::where('journal_id', $journal->id)
            ->whereNotNull('issue_id')
            ->count();

        // Separate into future (unpublished) and back (published) issues
        $futureIssues = $allIssues->where('is_published', false);

        // Available years for Back Issues filter
        $availableYears = Issue::where('journal_id', $journal->id)
            ->where('is_published', true)
            ->distinct()
            ->pluck('year')
            ->filter()
            ->sortDesc()
            ->values();

        $selectedYear = $request->query('year');

        // Back Issues (Published, paginated)
        $backIssuesQuery = Issue::where('journal_id', $journal->id)
            ->where('is_published', true);

        if (!empty($selectedYear)) {
            $backIssuesQuery->where('year', $selectedYear);
        }

        $backIssues = $backIssuesQuery
            ->orderBy('published_at', 'desc')
            ->orderBy('year', 'desc')
            ->orderBy('volume', 'desc')
            ->orderBy('number', 'desc')
            ->withCount('submissions')
            ->paginate(12)
            ->withQueryString();

        $currentIssue = $journal->currentIssue;

        return view('editor.issues.index', compact(
            'futureIssues',
            'backIssues',
            'journal',
            'totalIssues',
            'publishedCount',
            'upcomingCount',
            'totalArticles',
            'availableYears',
            'selectedYear',
            'currentIssue'
        ));
    }

    /**
     * Show the form for creating a new issue.
     */
    public function create(): View
    {
        $journal = $this->getJournal();

        // Get next suggested volume/number
        $latestIssue = Issue::where('journal_id', $journal->id)
            ->latest()
            ->first();

        $suggestedVolume = $latestIssue ? $latestIssue->volume : 1;
        $suggestedNumber = $latestIssue ? $latestIssue->number + 1 : 1;
        $suggestedYear = now()->year;

        // Reset number if year changed
        if ($latestIssue && $latestIssue->year < $suggestedYear) {
            $suggestedVolume++;
            $suggestedNumber = 1;
        }

        return view('editor.issues.create', compact(
            'journal',
            'suggestedVolume',
            'suggestedNumber',
            'suggestedYear'
        ));
    }

    /**
     * Store a newly created issue.
     */
    public function store(Request $request): RedirectResponse
    {
        $journal = $this->getJournal();

        $validated = $request->validate([
            'volume' => 'required|integer|min:1',
            'number' => 'required|integer|min:1',
            'year' => 'required|integer|min:2000|max:2100',
            'title' => 'nullable|string|max:255',
            'show_volume' => 'nullable|boolean',
            'show_number' => 'nullable|boolean',
            'show_year' => 'nullable|boolean',
            'show_title' => 'nullable|boolean',
            'description' => 'nullable|string',
            'url_path' => ['nullable', 'string', 'alpha_dash', 'unique:issues,url_path,NULL,id,journal_id,' . $journal->id],
            'cover' => 'nullable|image|max:2048', // 2MB max
        ]);

        // Check for duplicate
        $exists = Issue::where('journal_id', $journal->id)
            ->where('volume', $validated['volume'])
            ->where('number', $validated['number'])
            ->where('year', $validated['year'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', 'An issue with this volume, number, and year already exists.');
        }

        $issueData = [
            'journal_id' => $journal->id,
            'volume' => $validated['volume'],
            'number' => $validated['number'],
            'year' => $validated['year'],
            'title' => $validated['title'] ?? null,
            'show_volume' => $request->boolean('show_volume', true),
            'show_number' => $request->boolean('show_number', true),
            'show_year' => $request->boolean('show_year', true),
            'show_title' => $request->boolean('show_title', false),
            'description' => $validated['description'] ?? null,
            'url_path' => $validated['url_path'] ?? null,
            'is_published' => false,
        ];

        $issue = Issue::create($issueData);

        // Upload cover
        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store("issues/{$issue->id}", 'public');
            $issue->update(['cover_path' => $path]);
        }
        // Refresh to get auto-generated seq_id for route model binding
        $issue->refresh();

        return redirect()->route('journal.issues.show', ['journal' => $journal->slug, 'issue' => $issue])
            ->with('success', 'Issue created successfully.');
    }

    /**
     * Display the specified issue with management options.
     */
    public function show(string $journalSlug, Issue $issue): View
    {
        $journal = $this->getJournal();

        // Ensure issue belongs to this journal
        if ($issue->journal_id !== $journal->id) {
            abort(404);
        }

        $issue->load([
            'submissions' => function ($query) {
                $query->with(['authors', 'section', 'currentPublication']);
            }
        ]);

        // Sort submissions by section sort_order, submission sort_order, then created_at
        $submissions = $issue->submissions->sort(function ($a, $b) {
            $secA = $a->section?->sort_order ?? 0;
            $secB = $b->section?->sort_order ?? 0;
            if ($secA !== $secB) {
                return $secA <=> $secB;
            }
            $sortA = $a->sort_order ?? 0;
            $sortB = $b->sort_order ?? 0;
            if ($sortA !== $sortB) {
                return $sortA <=> $sortB;
            }
            $timeA = $a->created_at?->timestamp ?? 0;
            $timeB = $b->created_at?->timestamp ?? 0;
            return $timeA <=> $timeB;
        });

        // Group articles by section for table of contents
        $articlesBySection = $submissions->groupBy(
            fn($article) => $article->section?->name ?? 'Uncategorized'
        );

        // Get available submissions (accepted but not assigned to any issue) for this journal
        $availableSubmissions = Submission::where('journal_id', $journal->id)
            ->where('status', Submission::STATUS_ACCEPTED)
            ->whereNull('issue_id')
            ->with(['authors', 'section'])
            ->orderBy('accepted_at', 'desc')
            ->get();

        return view('editor.issues.show', compact('issue', 'articlesBySection', 'availableSubmissions', 'journal'));
    }

    /**
     * Show the form for editing the issue.
     */
    public function edit(string $journalSlug, Issue $issue): View
    {
        $journal = $this->getJournal();

        // Ensure issue belongs to this journal
        if ($issue->journal_id !== $journal->id) {
            abort(404);
        }

        return view('editor.issues.edit', compact('issue', 'journal'));
    }

    /**
     * Update the specified issue.
     */
    public function update(Request $request, string $journalSlug, Issue $issue): RedirectResponse
    {
        $journal = $this->getJournal();

        // Ensure issue belongs to this journal
        if ($issue->journal_id !== $journal->id) {
            abort(404);
        }

        $validated = $request->validate([
            'volume' => 'required|integer|min:1',
            'number' => 'required|integer|min:1',
            'year' => 'required|integer|min:2000|max:2100',
            'title' => 'nullable|string|max:255',
            'show_volume' => 'nullable|boolean',
            'show_number' => 'nullable|boolean',
            'show_year' => 'nullable|boolean',
            'show_title' => 'nullable|boolean',
            'description' => 'nullable|string',
            'url_path' => ['nullable', 'string', 'alpha_dash', 'unique:issues,url_path,' . $issue->id . ',id,journal_id,' . $journal->id],
            'cover' => 'nullable|image|max:2048',
        ]);

        $issueData = [
            'volume' => $validated['volume'],
            'number' => $validated['number'],
            'year' => $validated['year'],
            'title' => $validated['title'] ?? null,
            'show_volume' => $request->boolean('show_volume', true),
            'show_number' => $request->boolean('show_number', true),
            'show_year' => $request->boolean('show_year', true),
            'show_title' => $request->boolean('show_title', false),
            'description' => $validated['description'] ?? null,
            'url_path' => $validated['url_path'] ?? null,
        ];

        $issue->update($issueData);

        // Upload new cover
        if ($request->hasFile('cover')) {
            // Delete old cover
            if ($issue->cover_path) {
                Storage::disk('public')->delete($issue->cover_path);
            }

            $path = $request->file('cover')->store("issues/{$issue->id}", 'public');
            $issue->update(['cover_path' => $path]);
        }

        return redirect()->route('journal.issues.show', ['journal' => $journal->slug, 'issue' => $issue])
            ->with('success', 'Issue updated successfully.');
    }

    /**
     * Publish the issue and all its articles.
     */
    public function publish(string $journalSlug, Issue $issue): RedirectResponse
    {
        $journal = $this->getJournal();

        // Ensure issue belongs to this journal
        if ($issue->journal_id !== $journal->id) {
            abort(404);
        }

        $issue->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Also publish all assigned submissions and log activity
        foreach ($issue->submissions as $sub) {
            $sub->update([
                'status' => Submission::STATUS_PUBLISHED,
                'published_at' => now(),
            ]);
            SubmissionLog::log(
                submission: $sub,
                eventType: SubmissionLog::EVENT_PUBLISHED,
                title: 'Article Published',
                description: "Published via issue publication ({$issue->identifier}).",
                stage: $sub->stage
            );
        }

        return back()->with('success', 'Issue published successfully. ' . $issue->submissions()->count() . ' article(s) are now live.');
    }

    /**
     * Unpublish the issue.
     */
    public function unpublish(string $journalSlug, Issue $issue): RedirectResponse
    {
        $journal = $this->getJournal();

        // Ensure issue belongs to this journal
        if ($issue->journal_id !== $journal->id) {
            abort(404);
        }

        $issue->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        // Revert submissions to accepted status and log activity
        foreach ($issue->submissions as $sub) {
            $sub->update([
                'status' => Submission::STATUS_ACCEPTED,
                'published_at' => null,
            ]);
            SubmissionLog::log(
                submission: $sub,
                eventType: SubmissionLog::EVENT_UNPUBLISHED,
                title: 'Article Unpublished',
                description: "Unpublished due to issue unpublish ({$issue->identifier}).",
                stage: $sub->stage
            );
        }

        return back()->with('success', 'Issue unpublished. Articles have been reverted to accepted status.');
    }

    /**
     * Add articles to the issue.
     */
    public function addArticles(Request $request, string $journalSlug, Issue $issue): RedirectResponse
    {
        $journal = $this->getJournal();

        // Ensure issue belongs to this journal
        if ($issue->journal_id !== $journal->id) {
            abort(404);
        }

        $validated = $request->validate([
            'submission_ids' => 'required|array|min:1',
            'submission_ids.*' => 'string|uuid',
        ]);

        $addedCount = 0;
        $maxSortOrder = Submission::where('issue_id', $issue->id)->max('sort_order') ?? 0;

        foreach ($validated['submission_ids'] as $submissionId) {
            $submission = Submission::where('id', $submissionId)
                ->where('journal_id', $journal->id)
                ->where('status', Submission::STATUS_ACCEPTED)
                ->whereNull('issue_id')
                ->first();

            if ($submission) {
                $maxSortOrder++;
                $submission->update([
                    'issue_id' => $issue->id,
                    'sort_order' => $maxSortOrder,
                ]);
                $addedCount++;
            }
        }

        return back()->with('success', "{$addedCount} article(s) added to the issue.");
    }

    /**
     * Remove an article from the issue.
     */
    public function removeArticle(string $journalSlug, Issue $issue, Submission $submission): RedirectResponse
    {
        $journal = $this->getJournal();

        // Ensure issue belongs to this journal
        if ($issue->journal_id !== $journal->id) {
            abort(404);
        }

        if ($submission->issue_id !== $issue->id) {
            return back()->with('error', 'This article does not belong to this issue.');
        }

        $submission->update([
            'issue_id' => null,
            'status' => Submission::STATUS_ACCEPTED, // Revert to accepted if it was published
            'published_at' => null,
            'sort_order' => 0,
        ]);

        return back()->with('success', 'Article removed from the issue.');
    }

    /**
     * Reorder articles in the issue.
     */
    public function reorderArticles(Request $request, string $journalSlug, Issue $issue): RedirectResponse
    {
        $journal = $this->getJournal();

        // Ensure issue belongs to this journal
        if ($issue->journal_id !== $journal->id) {
            abort(404);
        }

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'string|uuid',
        ]);

        foreach ($validated['order'] as $index => $submissionId) {
            Submission::where('id', $submissionId)
                ->where('issue_id', $issue->id)
                ->update(['sort_order' => $index + 1]);
        }

        return back()->with('success', 'Urutan artikel berhasil diperbarui.');
    }

    /**
     * Remove the specified issue.
     */
    public function destroy(string $journalSlug, Issue $issue): RedirectResponse
    {
        $journal = $this->getJournal();

        // Ensure issue belongs to this journal
        if ($issue->journal_id !== $journal->id) {
            abort(404);
        }

        if ($issue->submissions()->exists()) {
            return back()->with('error', 'Cannot delete issue with assigned submissions. Remove all articles first.');
        }

        // Delete cover
        if ($issue->cover_path) {
            Storage::disk('public')->delete($issue->cover_path);
        }

        $issue->delete();

        return redirect()->route('journal.issues.index', ['journal' => $journal->slug])
            ->with('success', 'Issue deleted successfully.');
    }

    /**
     * Set a published issue as the Current Issue for the journal.
     */
    public function setCurrent(Request $request, string $journalSlug, Issue $issue): RedirectResponse
    {
        $journal = $this->getJournal();
        if ($issue->journal_id !== $journal->id) abort(404);
        if (!$issue->is_published) {
            return back()->with('error', 'Only published issues can be set as current issue.');
        }

        $issue->update(['published_at' => now()]);

        return back()->with('success', "Issue {$issue->identifier} is now designated as the Current Issue.");
    }
}
