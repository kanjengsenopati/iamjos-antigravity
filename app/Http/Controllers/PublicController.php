<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Journal;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    /**
     * Resolve journal from route parameter or return null for portal pages.
     */
    protected function resolveJournal(string $journalSlug): ?Journal
    {
        $journal = Journal::where('slug', $journalSlug)
            ->where('enabled', true)
            ->first();

        if (!$journal) {
            abort(404, 'Journal not found.');
        }

        return $journal;
    }

    // =====================================================
    // PORTAL PAGES (Multi-Journal Landing)
    // =====================================================

    /**
     * Display the portal homepage (list of journals).
     */
    public function portalHome(): View
    {
        $journals = Journal::where('enabled', true)
            ->where('visible', true)
            ->withCount(['submissions' => fn($q) => $q->published()])
            ->withCount(['issues' => fn($q) => $q->published()])
            ->orderBy('name')
            ->get();

        // Featured journals (first 4)
        $featuredJournals = $journals->take(4);

        // Latest articles across all journals
        $latestArticles = Submission::published()
            ->with(['authors', 'journal', 'section'])
            ->latest('published_at')
            ->take(6)
            ->get();

        // Stats
        $totalJournals = $journals->count();
        $totalArticles = Submission::published()->count();
        $totalIssues = Issue::published()->count();

        return view('public.portal-home', compact(
            'journals',
            'featuredJournals',
            'latestArticles',
            'totalJournals',
            'totalArticles',
            'totalIssues'
        ));
    }

    /**
     * Display full list of journals.
     */
    public function journalList(Request $request): View
    {
        $search = $request->get('search');

        $query = Journal::where('enabled', true)
            ->where('visible', true)
            ->withCount(['submissions' => fn($q) => $q->published()])
            ->withCount(['issues' => fn($q) => $q->published()]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('abbreviation', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        $journals = $query->orderBy('name')->paginate(12);

        return view('public.journals', compact('journals', 'search'));
    }

    // =====================================================
    // JOURNAL-SPECIFIC PAGES
    // =====================================================

    /**
     * Display the journal homepage.
     */
    public function journalHome(string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // Latest published issue
        $latestIssue = Issue::where('journal_id', $journal->id)
            ->published()
            ->latest()
            ->first();

        // Latest published articles
        $latestArticles = Submission::where('journal_id', $journal->id)
            ->published()
            ->with(['authors', 'section', 'issue'])
            ->latest('published_at')
            ->take(6)
            ->get();

        // Popular articles (placeholder - based on most recent for now)
        $popularArticles = Submission::where('journal_id', $journal->id)
            ->published()
            ->with(['authors', 'section'])
            ->latest('published_at')
            ->take(5)
            ->get();

        return view('public.home', compact('journal', 'latestIssue', 'latestArticles', 'popularArticles'));
    }

    /**
     * Display current issue for a journal.
     */
    public function currentIssue(string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);

        $issue = Issue::where('journal_id', $journal->id)
            ->published()
            ->latest()
            ->first();

        if (!$issue) {
            return view('public.no-current-issue', compact('journal'));
        }

        $articles = Submission::where('issue_id', $issue->id)
            ->published()
            ->with(['authors', 'section'])
            ->orderBy('created_at')
            ->get();

        return view('public.issue', compact('journal', 'issue', 'articles'));
    }

    /**
     * Display archives (list of all issues) for a journal.
     */
    public function archives(Request $request, string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);
        $year = $request->get('year');

        $query = Issue::where('journal_id', $journal->id)
            ->published()
            ->withCount(['submissions' => function ($q) {
                $q->published();
            }]);

        if ($year) {
            $query->where('year', $year);
        }

        $issues = $query->latest()->paginate(12);

        // Get available years for filter
        $years = Issue::where('journal_id', $journal->id)
            ->published()
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('public.archives', compact('journal', 'issues', 'years', 'year'));
    }

    /**
     * Display a specific issue.
     */
    public function issue(string $journalSlug, Issue $issue): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // Ensure issue belongs to this journal
        if ($issue->journal_id !== $journal->id) {
            abort(404);
        }

        if (!$issue->is_published) {
            abort(404);
        }

        $articles = Submission::where('issue_id', $issue->id)
            ->published()
            ->with(['authors', 'section'])
            ->orderBy('created_at')
            ->get();

        // Group by section
        $articlesBySection = $articles->groupBy(fn($article) => $article->section?->name ?? 'Uncategorized');

        return view('public.issue', compact('journal', 'issue', 'articles', 'articlesBySection'));
    }

    /**
     * Display a single article.
     */
    public function article(string $journalSlug, Submission $submission): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // Ensure submission belongs to this journal
        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        if ($submission->status !== Submission::STATUS_PUBLISHED) {
            abort(404);
        }

        $submission->load([
            'authors',
            'section',
            'issue',
            'journal',
            'files' => function ($q) {
                $q->where('file_type', 'galley');
            }
        ]);

        // Related articles from same section in same journal
        $relatedArticles = Submission::where('journal_id', $journal->id)
            ->where('section_id', $submission->section_id)
            ->where('id', '!=', $submission->id)
            ->published()
            ->with(['authors'])
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('public.article', compact('journal', 'submission', 'relatedArticles'));
    }

    /**
     * About the journal.
     */
    public function about(string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);

        return view('public.about', compact('journal'));
    }

    /**
     * Author guidelines.
     */
    public function authorGuidelines(string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);

        return view('public.author-guidelines', compact('journal'));
    }

    /**
     * Editorial team.
     */
    public function editorialTeam(string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);

        return view('public.editorial-team', compact('journal'));
    }

    /**
     * Display article in full-screen PDF reader.
     */
    public function articleReader(string $journalSlug, Submission $submission): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // Ensure submission belongs to this journal
        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        if ($submission->status !== Submission::STATUS_PUBLISHED) {
            abort(404);
        }

        $submission->load([
            'authors',
            'section',
            'issue',
            'journal',
            'files' => function ($q) {
                $q->where('file_type', 'galley')
                    ->orderBy('version', 'desc');
            }
        ]);

        // Get the galley file (PDF)
        $galleyFile = $submission->files->first();

        return view('public.article-reader', compact('journal', 'submission', 'galleyFile'));
    }
}
