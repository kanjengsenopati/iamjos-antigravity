<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\Submission;
use App\Models\SubmissionAuthor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Resolve journal from route parameter.
     */
    protected function resolveJournal(string $journalSlug): Journal
    {
        $journal = Journal::where('slug', $journalSlug)
            ->where('enabled', true)
            ->first();

        if (!$journal) {
            abort(404, 'Journal not found.');
        }

        return $journal;
    }

    /**
     * Display advanced search page for a journal.
     */
    public function index(Request $request, string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);
        $query = $request->get('q');
        $type = $request->get('type', 'all'); // all, title, author, keywords, abstract
        $year = $request->get('year');
        $results = collect();
        $totalFound = 0;

        if ($query && strlen(trim($query)) >= 2) {
            $searchQuery = Submission::where('journal_id', $journal->id)
                ->published()
                ->with(['authors', 'section', 'issue', 'files' => function ($q) {
                    $q->where('file_type', 'galley');
                }]);

            // Apply search based on type
            if ($type === 'author') {
                // Search by author name within this journal
                $submissionIds = SubmissionAuthor::whereHas('submission', function ($q) use ($journal) {
                    $q->where('journal_id', $journal->id);
                })
                    ->where('name', 'ilike', "%{$query}%")
                    ->pluck('submission_id');

                $searchQuery->whereIn('id', $submissionIds);
            } elseif ($type === 'title') {
                $searchQuery->where('title', 'ilike', "%{$query}%");
            } elseif ($type === 'keywords') {
                $searchQuery->where('keywords', 'ilike', "%{$query}%");
            } elseif ($type === 'abstract') {
                $searchQuery->where('abstract', 'ilike', "%{$query}%");
            } else {
                // Search in all fields including author names
                $authorSubmissionIds = SubmissionAuthor::whereHas('submission', function ($q) use ($journal) {
                    $q->where('journal_id', $journal->id);
                })
                    ->where('name', 'ilike', "%{$query}%")
                    ->pluck('submission_id');

                $searchQuery->where(function ($q) use ($query, $authorSubmissionIds) {
                    $q->where('title', 'ilike', "%{$query}%")
                        ->orWhere('abstract', 'ilike', "%{$query}%")
                        ->orWhere('keywords', 'ilike', "%{$query}%")
                        ->orWhereIn('id', $authorSubmissionIds);
                });
            }

            // Filter by year if specified
            if ($year) {
                $searchQuery->whereHas('issue', function ($q) use ($year) {
                    $q->where('year', $year);
                });
            }

            $results = $searchQuery->latest('published_at')->paginate(15)->withQueryString();
            $totalFound = $results->total();
        }

        // Get available years for filter dropdown (for this journal)
        $years = Submission::where('journal_id', $journal->id)
            ->published()
            ->whereHas('issue')
            ->with('issue')
            ->get()
            ->pluck('issue.year')
            ->unique()
            ->filter()
            ->sortDesc()
            ->values();

        return view('public.search', compact('journal', 'query', 'type', 'year', 'results', 'totalFound', 'years'));
    }

    /**
     * Quick search API for navbar autocomplete (per-journal).
     */
    public function quickSearch(Request $request, string $journalSlug)
    {
        $journal = $this->resolveJournal($journalSlug);
        $query = $request->get('q');

        if (!$query || strlen(trim($query)) < 2) {
            return response()->json([]);
        }

        $results = Submission::where('journal_id', $journal->id)
            ->published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'ilike', "%{$query}%")
                    ->orWhere('keywords', 'ilike', "%{$query}%");
            })
            ->with('authors:id,submission_id,name')
            ->select('id', 'title', 'published_at', 'journal_id')
            ->latest('published_at')
            ->take(5)
            ->get()
            ->map(function ($article) use ($journal) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'authors' => $article->authors->pluck('name')->join(', '),
                    'url' => route('journal.public.article', ['journal' => $journal->slug, 'submission' => $article]),
                ];
            });

        return response()->json($results);
    }
}
