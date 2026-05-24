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

        // Get settings with defaults
        $settings = $this->getSettingsWithDefaults($journal);

        $title = 'Search';

        $query = $request->get('q');
        $type = $request->get('type', 'all'); // all, title, author, keywords, abstract
        $year = $request->get('year');
        $results = collect();
        $totalFound = 0;

        if ($query && strlen(trim($query)) >= 2) {
            $searchQuery = Submission::where('journal_id', $journal->id)
                ->published()
                ->with(['authors', 'section', 'issue', 'currentPublication', 'files' => function ($q) {
                    $q->where('file_type', 'galley');
                }]);

            // Apply search based on type
            if ($type === 'author') {
                // Search by author name within this journal
                $submissionIds = SubmissionAuthor::whereHas('submission', function ($q) use ($journal) {
                    $q->where('journal_id', $journal->id);
                })
                    ->where('name', 'like', "%{$query}%")
                    ->pluck('submission_id');

                $searchQuery->whereIn('id', $submissionIds);
            } elseif ($type === 'title') {
                $searchQuery->where('title', 'like', "%{$query}%");
            } elseif ($type === 'keywords') {
                $searchQuery->whereHas('keywords', function ($q) use ($query) {
                    $q->where('content', 'like', "%{$query}%");
                });
            } elseif ($type === 'abstract') {
                $searchQuery->where('abstract', 'like', "%{$query}%");
            } else {
                // Search in all fields including author names
                $authorSubmissionIds = SubmissionAuthor::whereHas('submission', function ($q) use ($journal) {
                    $q->where('journal_id', $journal->id);
                })
                    ->where('name', 'like', "%{$query}%")
                    ->pluck('submission_id');

                $searchQuery->where(function ($q) use ($query, $authorSubmissionIds) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('abstract', 'like', "%{$query}%")
                        ->orWhereHas('keywords', function ($subQ) use ($query) {
                            $subQ->where('content', 'like', "%{$query}%");
                        })
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

        return view('public.search', compact('journal', 'settings', 'query', 'type', 'year', 'results', 'totalFound', 'years', 'title'));
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
                $q->where('title', 'like', "%{$query}%")
                    ->orWhereHas('keywords', function ($subQ) use ($query) {
                        $subQ->where('content', 'like', "%{$query}%");
                    });
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
                    'url' => route('journal.public.article', ['journal' => $journal->slug, 'article' => $article->seq_id]),
                ];
            });

        return response()->json($results);
    }

    /**
     * Get settings with defaults merged.
     */
    private function getSettingsWithDefaults(Journal $journal): array
    {
        $defaults = [
            // Content
            'about'    => '',
            'masthead' => ['about' => '', 'editorial_team' => ''],

            // Appearance
            'hero_image'      => null,
            'primary_color'   => '#4F46E5',
            'secondary_color' => '#7C3AED',

            // Hero Content — use journal data, never fake taglines
            'hero_title'       => $journal->name,
            'hero_description' => $journal->description ?? '',
            'hero_tagline'     => '',

            // Stats — empty by default; journal must set real values
            'stat_acceptance_rate' => '',
            'stat_review_time'     => '',
            'stat_impact_factor'   => '',
            'stat_citations'       => '',

            // Section Visibility
            'show_announcements'  => true,
            'show_editorial_team' => true,
            'show_indexed_in'     => true,
            'show_stats'          => false,

            // Indexed In
            'indexed_in_images' => [],

            // Footer
            'footer_description' => $journal->description ?? '',
            'social_facebook'    => '',
            'social_twitter'     => '',
            'social_linkedin'    => '',
            'social_instagram'   => '',
            'contact_email'      => '',
            'contact_phone'      => '',
            'contact_address'    => '',
        ];

        $actual = $journal->getWebsiteSettings();

        return array_merge($defaults, $actual);
    }
}
