<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Journal;
use App\Models\SiteContent;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    /**
     * Display the portal landing page.
     */
    public function index(): View
    {
        // Get all dynamic content
        $content = SiteContent::getAll();

        // Statistics
        $stats = [
            'journals' => Journal::where('enabled', true)->count(),
            'articles' => Submission::where('status', Submission::STATUS_PUBLISHED)->count(),
            'authors' => User::role('Author')->count(),
        ];

        // Featured Journals
        $featuredIds = $content['featured_journal_ids'] ?? [];
        $featuredJournals = collect();

        if (!empty($featuredIds)) {
            $featuredJournals = Journal::whereIn('id', $featuredIds)
                ->where('enabled', true)
                ->withCount(['submissions' => fn($q) => $q->where('status', Submission::STATUS_PUBLISHED)])
                ->get();
        }

        // If no featured journals selected, get the most active ones
        if ($featuredJournals->isEmpty()) {
            $featuredJournals = Journal::where('enabled', true)
                ->where('visible', true)
                ->withCount(['submissions' => fn($q) => $q->where('status', Submission::STATUS_PUBLISHED)])
                ->orderByDesc('submissions_count')
                ->take(6)
                ->get();
        }

        // Latest Articles (Published)
        $latestArticles = Submission::where('status', Submission::STATUS_PUBLISHED)
            ->with(['authors', 'journal', 'section'])
            ->latest('published_at')
            ->take(6)
            ->get();

        // Browse Subjects
        $subjects = $content['browse_subjects'] ?? [];

        // All Journals for subject browsing
        $allJournals = Journal::where('enabled', true)
            ->where('visible', true)
            ->withCount(['submissions' => fn($q) => $q->where('status', Submission::STATUS_PUBLISHED)])
            ->orderBy('name')
            ->get();

        return view('welcome', compact(
            'content',
            'stats',
            'featuredJournals',
            'latestArticles',
            'subjects',
            'allJournals'
        ));
    }

    /**
     * Handle search from portal.
     */
    public function search(Request $request): View
    {
        $query = $request->get('q', '');

        $journals = collect();
        $articles = collect();

        if (strlen($query) >= 2) {
            // Search journals
            $journals = Journal::where('enabled', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('abbreviation', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->withCount(['submissions' => fn($q) => $q->where('status', Submission::STATUS_PUBLISHED)])
                ->take(10)
                ->get();

            // Search articles
            $articles = Submission::where('status', Submission::STATUS_PUBLISHED)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('abstract', 'like', "%{$query}%");
                })
                ->with(['authors', 'journal', 'section'])
                ->latest('published_at')
                ->take(20)
                ->get();
        }

        return view('public.search-results', compact('query', 'journals', 'articles'));
    }

    /**
     * Display all journals page with filtering.
     */
    public function journals(Request $request): View
    {
        $search = $request->get('search', '');
        $alpha = $request->get('alpha', '');
        $topics = $request->get('topics', []);
        $access = $request->get('access', []);

        // Define available topics (could be from DB or config)
        $availableTopics = [
            'medicine' => 'Kedokteran & Kesehatan',
            'engineering' => 'Teknik & Teknologi',
            'economics' => 'Ekonomi & Bisnis',
            'law' => 'Hukum',
            'education' => 'Pendidikan',
            'agriculture' => 'Pertanian',
            'social' => 'Ilmu Sosial',
            'science' => 'Sains & Matematika',
        ];

        $journals = Journal::where('enabled', true)
            ->where('visible', true)
            // Search by name, abbreviation, or description
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('abbreviation', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            // Filter by first letter (A-Z)
            ->when($alpha && $alpha !== 'all', function ($query) use ($alpha) {
                $query->where('name', 'like', "{$alpha}%");
            })
            // Filter by access type (open_access field check - assuming journals have this)
            ->when(!empty($access), function ($query) use ($access) {
                if (in_array('open_access', $access) && !in_array('subscription', $access)) {
                    // Only open access - filter by settings or a dedicated column
                    $query->where(function($q) {
                        $q->whereJsonContains('settings->open_access', true)
                          ->orWhereNull('settings->open_access');
                    });
                } elseif (in_array('subscription', $access) && !in_array('open_access', $access)) {
                    $query->whereJsonContains('settings->open_access', false);
                }
            })
            // Filter by topics (using settings->topic or dedicated field)
            ->when(!empty($topics), function ($query) use ($topics) {
                $query->where(function ($q) use ($topics) {
                    foreach ($topics as $topic) {
                        $q->orWhereJsonContains('settings->topics', $topic)
                          ->orWhere('description', 'like', "%{$topic}%");
                    }
                });
            })
            ->withCount(['submissions' => fn($q) => $q->where('status', Submission::STATUS_PUBLISHED)])
            ->with(['issues' => fn($q) => $q->where('is_published', true)->latest('year')->latest('volume')->limit(1)])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // Generate alphabet array
        $alphabet = array_merge(['all'], range('A', 'Z'));

        // Get total count for display
        $totalJournals = Journal::where('enabled', true)->where('visible', true)->count();

        return view('public.journals.index', compact(
            'journals',
            'search',
            'alpha',
            'topics',
            'access',
            'availableTopics',
            'alphabet',
            'totalJournals'
        ));
    }

    /**
     * Display the About Us page.
     */
    public function about(): View
    {
        $content = SiteContent::getAll();

        return view('public.portal-about', compact('content'));
    }
}
