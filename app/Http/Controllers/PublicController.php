<?php

namespace App\Http\Controllers;

use App\Jobs\RecordArticleMetricJob;
use App\Models\Issue;
use App\Models\Journal;
use Illuminate\View\View;
use App\Models\Submission;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;

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

    /**
     * Resolve GeoIP data from an IP address.
     *
     * Uses stevebauman/location if installed; falls back to null values.
     * To enable real GeoIP: composer require stevebauman/location
     *
     * @return array{0: string|null, 1: string|null} [countryCode, city]
     */
    protected function resolveGeoIp(string $ip): array
    {
        // Use stevebauman/location if available
        if (class_exists(\Stevebauman\Location\Facades\Location::class)) {
            try {
                $position = \Stevebauman\Location\Facades\Location::get($ip);
                if ($position) {
                    return [
                        $position->countryCode ?: null,
                        $position->cityName    ?: null,
                    ];
                }
            } catch (\Throwable) {
                // GeoIP lookup failed — fall through to null
            }
        }

        // No GeoIP package installed — store null rather than a fake country code
        return [null, null];
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
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('abbreviation', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
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

        // Get settings with defaults
        $settings = $this->getSettingsWithDefaults($journal);

        $issue = Issue::where('journal_id', $journal->id)
            ->published()
            ->latest()
            ->first();

        if (!$issue) {
            $title = 'No Current Issue';
            return view('public.no-current-issue', compact('journal', 'settings', 'title'));
        }

        $articles = Submission::where('issue_id', $issue->id)
            ->published()
            ->with(['authors', 'section', 'galleys' => function ($q) {
                $q->ordered();
            }])
            ->orderBy('created_at')
            ->get();

        // Group by section
        $articlesBySection = $articles->groupBy(fn($article) => $article->section?->name ?? 'Uncategorized');

        return view('public.issue', compact('journal', 'settings', 'issue', 'articles', 'articlesBySection'));
    }

    /**
     * Display archives (list of all issues) for a journal.
     */
    public function archives(Request $request, string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);
        $year = $request->get('year');

        // Get settings with defaults (similar to JournalHomepageController)
        $settings = $this->getSettingsWithDefaults($journal);

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

        return view('public.archives', compact('journal', 'settings', 'issues', 'years', 'year'));
    }

    /**
     * Display a specific issue.
     */
    public function issue(string $journalSlug, $issueSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // Get settings with defaults
        $settings = $this->getSettingsWithDefaults($journal);

        // =============================================
        // LOGIKA BARU: HANDLER SEQ_ID, UUID, AND URL_PATH
        // =============================================
        $issue = null;

        if (Str::isUuid($issueSlug)) {
            $issue = Issue::where('id', $issueSlug)
                ->where('journal_id', $journal->id)
                ->first();
        } elseif (is_numeric($issueSlug)) {
            $issue = Issue::where('seq_id', $issueSlug)
                ->where('journal_id', $journal->id)
                ->first();
                
            if (!$issue) {
                // Fallback for numeric slugs
                $issue = Issue::where('url_path', $issueSlug)
                    ->where('journal_id', $journal->id)
                    ->first();
            }
        } else {
            $issue = Issue::where('url_path', $issueSlug)
                ->where('journal_id', $journal->id)
                ->first();
        }

        if (!$issue) {
            abort(404);
        }

        if (!$issue->is_published) {
            abort(404);
        }

        $articles = Submission::where('issue_id', $issue->id)
            ->published()
            ->with(['authors', 'section', 'galleys'])
            ->orderBy('created_at')
            ->get();

        // Group by section
        $articlesBySection = $articles->groupBy(fn($article) => $article->section?->name ?? 'Uncategorized');

        return view('public.issue', compact('journal', 'settings', 'issue', 'articles', 'articlesBySection'));
    }

    public function article(string $journalSlug, $slug): \Illuminate\View\View|RedirectResponse
    {
        // 1. Resolve Journal Terlebih Dahulu (Penting untuk validasi)
        $journal = $this->resolveJournal($journalSlug);

        // =============================================
        // LOGIKA BARU: HANDLER SEQ_ID, UUID, AND SLUG
        // =============================================
        $article = null;

        if (Str::isUuid($slug)) {
            // Cari artikel berdasarkan ID (UUID)
            $article = Submission::published()
                ->where('id', $slug)
                ->where('journal_id', $journal->id)
                ->first();
        } elseif (is_numeric($slug)) {
            // Cari artikel berdasarkan seq_id
            $article = Submission::published()
                ->where('seq_id', $slug)
                ->where('journal_id', $journal->id)
                ->first();
                
            // Fallback jika ternyata slug dibuat dari angka (jarang terjadi tapi mungkin)
            if (!$article) {
                $article = Submission::published()
                    ->where('slug', $slug)
                    ->where('journal_id', $journal->id)
                    ->first();
            }
        } else {
            // Cari artikel berdasarkan Slug
            $article = Submission::published()
                ->where('slug', $slug)
                ->where('journal_id', $journal->id)
                ->first();
        }

        if (!$article) {
            abort(404);
        }

        // Eager load all required relationships
        $article->load([
            'authors' => function ($q) {
                $q->orderBy('sort_order');
            },
            'section',
            'issue',
            'journal',
            'galleys' => function ($q) {
                $q->ordered();
            },
            'currentPublication',
        ]);

        // Get the issue for additional metadata
        $issue = $article->issue;
        $ip = request()->ip();
        
        // Simple bot detection (check user agent)
        $userAgent = strtolower(request()->userAgent() ?? '');
        $isBot = str_contains($userAgent, 'bot') || 
                str_contains($userAgent, 'crawler') || 
                str_contains($userAgent, 'spider');

        if (!$isBot) {
            // GeoIP resolution — use IP-based lookup if available, otherwise null
            // Install stevebauman/location for real GeoIP: composer require stevebauman/location
            [$countryCode, $city] = $this->resolveGeoIp($ip);

            // Log the view asynchronously via queued job
            RecordArticleMetricJob::dispatch(
                $article->id,
                'view',
                $ip,
                $countryCode,
                $city,
                now()->toDateString()
            );
        }

        // =============================================
        // ANALYTICS: Prepare Chart Data (Last 12 Months)
        // Handling multi-database drivers for month grouping
        // =============================================
        $driver = DB::connection()->getDriverName();
        $monthExpr = "DATE_FORMAT(date, '%Y-%m')"; // Default MySQL/MariaDB
        
        if ($driver === 'pgsql') {
            $monthExpr = "TO_CHAR(date, 'YYYY-MM')";
        } elseif ($driver === 'sqlite') {
            $monthExpr = "strftime('%Y-%m', date)";
        }

        $stats = DB::table('article_metrics')
            ->selectRaw("{$monthExpr} as month, type, count(*) as total")
            ->where('submission_id', $article->id)
            ->where('date', '>=', now()->subYear())
            ->groupBy(DB::raw($monthExpr), 'type')
            ->orderBy('month')
            ->get();
        
        // Build complete month range for last 12 months
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }
        
        // Map data to months (fill missing months with 0)
        $chartLabels = $months->values();
        $viewsData = $months->mapWithKeys(function($month) use ($stats) {
            $stat = $stats->where('month', $month)->where('type', 'view')->first();
            return [$month => $stat ? $stat->total : 0];
        })->values();
        
        $downloadsData = $months->mapWithKeys(function($month) use ($stats) {
            $stat = $stats->where('month', $month)->where('type', 'download')->first();
            return [$month => $stat ? $stat->total : 0];
        })->values();

        // =============================================
        // ANALYTICS: Country Stats (Admin Only)
        // =============================================
        $countryStats = collect();
        if (auth()->check() && auth()->user()->hasAnyRole(['admin', 'journal manager', 'editor'])) {
            $countryStats = DB::table('article_metrics')
                ->select('country_code', DB::raw('count(*) as total'))
                ->where('submission_id', $article->id)
                ->where('type', 'view')
                ->whereNotNull('country_code')
                ->groupBy('country_code')
                ->orderByDesc('total')
                ->limit(10)
                ->get();
        }

        // Related articles from same section in same journal
        $relatedArticles = Submission::where('journal_id', $journal->id)
            ->where('section_id', $article->section_id)
            ->where('id', '!=', $article->id)
            ->published()
            ->with(['authors', 'section'])
            ->latest('published_at')
            ->take(5)
            ->get();

        // Get journal website settings for theming
        $settings = $journal->getWebsiteSettings();

        return view('journal.public.article', compact(
            'journal',
            'article',
            'issue',
            'relatedArticles',
            'settings',
            'chartLabels',
            'viewsData',
            'downloadsData',
            'countryStats'
        ));
    }


    /**
     * About the journal.
     */
    public function about(string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);
        $about = $journal->settings['masthead']['about'] ?? '';

        // Get settings with defaults
        $settings = $this->getSettingsWithDefaults($journal);

        return view('public.about', compact('journal', 'settings', 'about'));
    }

    /**
     * Author guidelines.
     */
    public function authorGuidelines(string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // Get settings with defaults
        $settings = $this->getSettingsWithDefaults($journal);

        return view('public.author-guidelines', compact('journal', 'settings'));
    }

    /**
     * Editorial team.
     */
    public function editorialTeam(string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // Get settings with defaults
        $settings = $this->getSettingsWithDefaults($journal);

        return view('public.editorial-team', compact('journal', 'settings'));
    }

    /**
     * Contact page.
     */
    public function contact(string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // Get settings with defaults
        $settings = $this->getSettingsWithDefaults($journal);
        
        $contactSettings = $journal->settings['contact'] ?? [];

        return view('public.contact', compact('journal', 'settings', 'contactSettings'));
    }

    /**
     * Redirect OJS legacy and relative links.
     */
    public function redirectLegacyOjsAbout(Request $request, string $target, ?string $journal = null): RedirectResponse
    {
        $journalSlug = $journal;
        if (!$journalSlug) {
            $referer = $request->headers->get('referer');
            if ($referer) {
                $path = parse_url($referer, PHP_URL_PATH);
                $segments = explode('/', trim($path, '/'));
                if (!empty($segments[0]) && $segments[0] !== 'index.php') {
                    $journalSlug = $segments[0];
                } elseif (count($segments) > 1 && $segments[0] === 'index.php') {
                    $journalSlug = $segments[1];
                }
            }

            if (!$journalSlug) {
                $journalSlug = session('login_journal_slug');
            }

            if (!$journalSlug || !Journal::where('slug', $journalSlug)->exists()) {
                $journalModel = Journal::where('enabled', true)->first();
                $journalSlug = $journalModel ? $journalModel->slug : null;
            }
        }

        if (!$journalSlug) {
            return redirect()->route('portal.home');
        }

        switch ($target) {
            case 'editorial-team':
                return redirect()->route('journal.public.editorial-team', ['journal' => $journalSlug]);
            case 'contact':
                return redirect()->route('journal.public.contact', ['journal' => $journalSlug]);
            case 'login':
                return redirect()->route('journal.login', ['journal' => $journalSlug]);
            case 'author-guidelines':
                return redirect()->route('journal.public.author-guidelines', ['journal' => $journalSlug]);
            default:
                return redirect()->route('journal.public.home', ['journal' => $journalSlug]);
        }
    }

    /**
     * Information for Readers.
     */
    public function infoReaders(string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // Get settings with defaults
        $settings = $this->getSettingsWithDefaults($journal);

        $title = 'For Readers';
        $content = $journal->info_readers;

        return view('public.information', compact('journal', 'settings', 'title', 'content'));
    }

    /**
     * Information for Authors.
     */
    public function infoAuthors(string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // Get settings with defaults
        $settings = $this->getSettingsWithDefaults($journal);

        $title = 'For Authors';
        $content = $journal->info_authors;

        return view('public.information', compact('journal', 'settings', 'title', 'content'));
    }

    /**
     * Information for Librarians.
     */
    public function infoLibrarians(string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // Get settings with defaults
        $settings = $this->getSettingsWithDefaults($journal);

        $title = 'For Librarians';
        $content = $journal->info_librarians;

        return view('public.information', compact('journal', 'settings', 'title', 'content'));
    }

    /**
     * Display list of announcements.
     */
    public function announcements(string $journalSlug): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // Get settings with defaults
        $settings = $this->getSettingsWithDefaults($journal);

        if (!$journal->enable_announcements) {
             abort(404);
        }

        $announcements = \App\Models\Announcement::where('journal_id', $journal->id)
            ->where('is_active', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        $title = 'Announcements';

        return view('public.announcement.index', compact('journal', 'settings', 'announcements', 'title'));
    }

    /**
     * Display a specific announcement.
     */
    public function announcement(string $journalSlug, $id): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // Get settings with defaults
        $settings = $this->getSettingsWithDefaults($journal);

        if (!$journal->enable_announcements) {
             abort(404);
        }

        $announcement = \App\Models\Announcement::where('journal_id', $journal->id)
            ->where('is_active', true)
            ->where('published_at', '<=', now())
            ->findOrFail($id);

        $title = $announcement->title;

        return view('public.announcement.show', compact('journal', 'settings', 'announcement', 'title'));
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

    /**
     * Download a publication galley file.
     * CRITICAL for Google Scholar indexing - must stream the actual file.
     */
    public function downloadGalley(string $journalSlug, $article, $galley)
    {
        $journal = $this->resolveJournal($journalSlug);

        // Resolve article by ID or slug
        $submissionQuery = Submission::where('journal_id', $journal->id)
            ->published();

        if (\Str::isUuid($article)) {
            $submissionQuery->where('id', $article);
        } elseif (is_numeric($article)) {
            $submissionQuery->where('seq_id', $article);
        } else {
            $submissionQuery->where('slug', $article);
        }

        $submission = $submissionQuery->firstOrFail();

        // Load galleys
        $submission->load('galleys');

        // Find the galley
        $publicationGalley = $submission->galleys->where('id', $galley)->first();

        if (!$publicationGalley) {
            abort(404, 'Galley not found');
        }

        // If it's a remote galley, redirect to URL
        if ($publicationGalley->is_remote && $publicationGalley->url_remote) {
            return redirect($publicationGalley->url_remote);
        }

        // Get the associated file
        if (!$publicationGalley->file_id) {
            abort(404, 'File not found');
        }

        $file = \App\Models\SubmissionFile::find($publicationGalley->file_id);

        if (!$file) {
            abort(404, 'File not found');
        }

        // Stream the file for download
        $disk = Storage::disk('local');

        if (!$disk->exists($file->file_path)) {
            abort(404, 'File not found on disk');
        }

        // =============================================
        // ANALYTICS: Log Download
        // =============================================
        $ip = request()->ip();
        $userAgent = strtolower(request()->userAgent() ?? '');
        $isBot = str_contains($userAgent, 'bot') || 
                 str_contains($userAgent, 'crawler') || 
                 str_contains($userAgent, 'spider');

        if (!$isBot) {
            [$countryCode, $city] = $this->resolveGeoIp($ip);

            RecordArticleMetricJob::dispatch(
                $submission->id,
                'download',
                $ip,
                $countryCode,
                $city,
                now()->toDateString()
            );
        }

        // Increment download counter
        // $publicationGalley->increment('views'); // Column 'views' does not exist

        // Return file stream with proper headers for Google Scholar
        return response()->stream(
            function () use ($disk, $file) {
                $stream = $disk->readStream($file->file_path);
                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type'        => $file->mime_type ?? 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $file->file_name . '"',
                'Content-Length'      => $disk->size($file->file_path),
                'Cache-Control'       => 'public, max-age=86400',
            ]
        );
    }

    /**
     * Download an article PDF directly via SEO-friendly route.
     * Pattern: /{journal}/article/download/{seq_id}/{filename}.pdf
     * CRITICAL for Google Scholar indexing.
     */
    public function downloadPdf(string $journalSlug, string $seqId, string $filename)
    {
        $journal = $this->resolveJournal($journalSlug);

        // Resolve article by seq_id
        $submission = Submission::where('journal_id', $journal->id)
            ->where('seq_id', $seqId)
            ->published()
            ->firstOrFail();

        // Load galleys
        $submission->load('galleys');

        // Find the PDF galley
        $pdfGalley = $submission->galleys->firstWhere('label', 'PDF') 
            ?? $submission->galleys->where('file_type', 'galley')->first();

        if (!$pdfGalley) {
            abort(404, 'PDF Galley not found');
        }

        if ($pdfGalley->is_remote && $pdfGalley->url_remote) {
            return redirect($pdfGalley->url_remote);
        }

        if (!$pdfGalley->file_id) {
            abort(404, 'File not found');
        }

        $file = \App\Models\SubmissionFile::find($pdfGalley->file_id);

        if (!$file) {
            abort(404, 'File not found');
        }

        $disk = Storage::disk('local');

        if (!$disk->exists($file->file_path)) {
            abort(404, 'File not found on disk');
        }

        // Analytics
        if (!preg_match('/bot|crawler|spider/i', request()->userAgent() ?? '')) {
            [$countryCode, $city] = $this->resolveGeoIp(request()->ip());
            RecordArticleMetricJob::dispatch(
                $submission->id,
                'download',
                request()->ip(),
                $countryCode,
                $city,
                now()->toDateString()
            );
        }

        // Return file stream with inline disposition for browser preview but with correct filename
        return response()->stream(
            function () use ($disk, $file) {
                $stream = $disk->readStream($file->file_path);
                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '.pdf"',
                'Content-Length' => $disk->size($file->file_path),
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }

    /**
     * View a galley inline (for HTML galleys or embedded PDF viewer).
     */
    public function viewGalley(string $journalSlug, $article, $galley)
    {
        $journal = $this->resolveJournal($journalSlug);

        // Resolve article by ID or slug
        $submissionQuery = Submission::where('journal_id', $journal->id)
            ->published();

        if (Str::isUuid($article)) {
            $submissionQuery->where('id', $article);
        } elseif (is_numeric($article)) {
            $submissionQuery->where('seq_id', $article);
        } else {
            $submissionQuery->where('slug', $article);
        }

        $submission = $submissionQuery->firstOrFail();

        // Load galleys
        $submission->load(['galleys', 'authors', 'section', 'issue']);

        // Find the galley (Try ID first for safety, then seq_id)
        $publicationGalley = $submission->galleys->where('id', $galley)->first() 
            ?? $submission->galleys->where('seq_id', $galley)->first();

        if (!$publicationGalley) {
            abort(404, 'Galley not found');
        }

        // If it's a remote galley, redirect
        if ($publicationGalley->is_remote && $publicationGalley->url_remote) {
            return redirect($publicationGalley->url_remote);
        }

        // =============================================
        // PDF STREAMING (OJS Native Style & GS Compliance)
        // =============================================
        // If it's a PDF, stream it directly in the browser
        $isPdf = \Str::contains(strtolower($publicationGalley->label), 'pdf') || 
                 \Str::contains(strtolower($publicationGalley->file_type ?? ''), 'pdf') ||
                 ($publicationGalley->file && str_contains($publicationGalley->file->mime_type, 'pdf'));

        if ($isPdf && $publicationGalley->file_id) {
            $file = \App\Models\SubmissionFile::find($publicationGalley->file_id);
            if ($file) {
                $disk = Storage::disk('local');
                if ($disk->exists($file->file_path)) {
                    // Analytics: Log View/Download
                    if (!preg_match('/bot|crawler|spider/i', request()->userAgent() ?? '')) {
                        [$countryCode, $city] = $this->resolveGeoIp(request()->ip());
                        RecordArticleMetricJob::dispatch(
                            $submission->id,
                            'download',
                            request()->ip(),
                            $countryCode,
                            $city,
                            now()->toDateString()
                        );
                    }

                    return response()->stream(
                        function () use ($disk, $file) {
                            $stream = $disk->readStream($file->file_path);
                            fpassthru($stream);
                            fclose($stream);
                        },
                        200,
                        [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'inline; filename="' . $file->file_name . '"',
                            'Content-Length' => $disk->size($file->file_path),
                            'Cache-Control' => 'public, max-age=86400',
                        ]
                    );
                }
            }
        }

        // For non-PDF galleys (HTML, etc.), use the viewer
        $settings = $journal->getWebsiteSettings();

        return view('journal.public.galley-viewer', [
            'journal' => $journal,
            'article' => $submission,
            'galley' => $publicationGalley,
            'issue' => $submission->issue,
            'settings' => $settings,
        ]);
    }

    /**
     * Export citation in RIS format (for EndNote, Zotero, Mendeley)
     * RIS 2001 spec: https://en.wikipedia.org/wiki/RIS_(file_format)
     */
    public function exportCitationRIS(string $journalSlug, $article)
    {
        $journal = $this->resolveJournal($journalSlug);

        $submission = Submission::where('journal_id', $journal->id)
            ->where(function ($q) use ($article) {
                $q->Where('slug', $article);
            })
            ->with(['authors', 'issue', 'section', 'currentPublication', 'keywords'])
            ->published()
            ->firstOrFail();

        $pub            = $submission->currentPublication;
        $issue          = $submission->issue;
        $publicationDate = $pub?->date_published ?? ($issue?->published_at ?? $submission->published_at);
        $doi            = $pub?->doi ?? null;
        $pages          = $pub?->pages ?? null;
        $abstract       = $pub?->abstract ?? $submission->abstract ?? null;

        $ris  = "TY  - JOUR\n";
        $ris .= "TI  - " . ($pub?->title ?? $submission->title) . "\n";

        // Authors: Last, First (RIS standard)
        foreach ($submission->authors as $author) {
            $lastName  = $author->family_name ?? $author->last_name ?? '';
            $firstName = $author->given_name  ?? $author->first_name ?? '';
            if ($lastName || $firstName) {
                $ris .= "AU  - {$lastName}, {$firstName}\n";
            }
        }

        $ris .= "T2  - {$journal->name}\n";

        // ISSN (SN field)
        $issn = $journal->issn_online ?? $journal->issn_print ?? null;
        if ($issn) {
            $ris .= "SN  - {$issn}\n";
        }

        if ($publicationDate) {
            $ris .= "PY  - {$publicationDate->year}\n";
            $ris .= "DA  - {$publicationDate->format('Y/m/d')}\n";
        }

        if ($issue?->volume) {
            $ris .= "VL  - {$issue->volume}\n";
        }
        if ($issue?->number) {
            $ris .= "IS  - {$issue->number}\n";
        }

        // Pages: SP (start) and EP (end)
        if ($pages) {
            $pageParts = explode('-', $pages, 2);
            $ris .= "SP  - " . trim($pageParts[0]) . "\n";
            if (isset($pageParts[1])) {
                $ris .= "EP  - " . trim($pageParts[1]) . "\n";
            }
        }

        if ($doi) {
            $ris .= "DO  - {$doi}\n";
            $ris .= "UR  - https://doi.org/{$doi}\n";
        }

        if ($abstract) {
            $ris .= "AB  - " . trim(strip_tags($abstract)) . "\n";
        }

        // Keywords — use keywords relation (BelongsToMany), not is_array check
        foreach ($submission->keywords as $keyword) {
            $kwText = $keyword->content ?? $keyword->name ?? null;
            if ($kwText) {
                $ris .= "KW  - {$kwText}\n";
            }
        }

        if ($journal->publisher) {
            $ris .= "PB  - {$journal->publisher}\n";
        }

        // Language (LA field)
        $lang = preg_replace('/_[A-Z]{2}$/', '', $submission->locale ?? 'en');
        $ris .= "LA  - {$lang}\n";

        $ris .= "ER  - \n";

        return response($ris)
            ->header('Content-Type', 'application/x-research-info-systems')
            ->header('Content-Disposition', 'attachment; filename="' . \Str::slug($submission->title) . '.ris"');
    }

    /**
     * Export citation in BibTeX format
     * BibTeX spec: https://www.bibtex.org/Format/
     */
    public function exportCitationBibTeX(string $journalSlug, $article)
    {
        $journal = $this->resolveJournal($journalSlug);

        $submission = Submission::where('journal_id', $journal->id)
            ->where(function ($q) use ($article) {
                $q->Where('slug', $article);
            })
            ->with(['authors', 'issue', 'section', 'currentPublication', 'keywords'])
            ->published()
            ->firstOrFail();

        $pub            = $submission->currentPublication;
        $issue          = $submission->issue;
        $publicationDate = $pub?->date_published ?? ($issue?->published_at ?? $submission->published_at);
        $doi            = $pub?->doi ?? null;
        $pages          = $pub?->pages ?? null;
        $abstract       = $pub?->abstract ?? $submission->abstract ?? null;

        // BibTeX special character escaping
        // Escapes: { } \ % $ # & _ ^ ~ < >
        $escapeBibtex = function (string $str): string {
            // Protect existing braces first, then escape special chars
            $str = str_replace('\\', '\\\\', $str);
            $str = str_replace('%',  '\\%',  $str);
            $str = str_replace('$',  '\\$',  $str);
            $str = str_replace('#',  '\\#',  $str);
            $str = str_replace('&',  '\\&',  $str);
            $str = str_replace('_',  '\\_',  $str);
            $str = str_replace('^',  '\\^{}', $str);
            $str = str_replace('~',  '\\~{}', $str);
            return $str;
        };

        // Citation key: FirstAuthorLastName + Year + seq_id suffix for uniqueness
        $firstAuthor = $submission->authors->first();
        $lastName    = $firstAuthor ? ($firstAuthor->family_name ?? $firstAuthor->last_name ?? 'Author') : 'Author';
        $year        = $publicationDate?->year ?? now()->year;
        $citationKey = Str::slug($lastName) . $year . '-' . $submission->seq_id;

        $bibtex  = "@article{{$citationKey},\n";
        $bibtex .= "  title     = {{" . $escapeBibtex($pub?->title ?? $submission->title) . "}},\n";

        // Authors: First Last and First Last (BibTeX standard)
        $authors = $submission->authors->map(function ($a) {
            $first = $a->given_name  ?? $a->first_name  ?? '';
            $last  = $a->family_name ?? $a->last_name   ?? '';
            return trim("{$first} {$last}");
        })->filter()->implode(' and ');
        $bibtex .= "  author    = {{" . $escapeBibtex($authors) . "}},\n";

        $bibtex .= "  journal   = {{" . $escapeBibtex($journal->name) . "}},\n";

        if ($publicationDate) {
            $bibtex .= "  year      = {{{$publicationDate->year}}},\n";
            $bibtex .= "  month     = {{{$publicationDate->format('m')}}},\n";
        }
        if ($issue?->volume) {
            $bibtex .= "  volume    = {{{$issue->volume}}},\n";
        }
        if ($issue?->number) {
            $bibtex .= "  number    = {{{$issue->number}}},\n";
        }
        if ($pages) {
            $bibtex .= "  pages     = {{" . str_replace('-', '--', $pages) . "}},\n";
        }
        if ($doi) {
            $bibtex .= "  doi       = {{{$doi}}},\n";
            $bibtex .= "  url       = {{https://doi.org/{$doi}}},\n";
        }
        if ($journal->publisher) {
            $bibtex .= "  publisher = {{" . $escapeBibtex($journal->publisher) . "}},\n";
        }

        // ISSN
        $issn = $journal->issn_online ?? $journal->issn_print ?? null;
        if ($issn) {
            $bibtex .= "  issn      = {{{$issn}}},\n";
        }

        // Abstract
        if ($abstract) {
            $bibtex .= "  abstract  = {{" . $escapeBibtex(trim(strip_tags($abstract))) . "}},\n";
        }

        // Keywords
        $kwList = $submission->keywords->map(fn($k) => $k->content ?? $k->name ?? '')->filter()->implode(', ');
        if ($kwList) {
            $bibtex .= "  keywords  = {{" . $escapeBibtex($kwList) . "}},\n";
        }

        $bibtex .= "}\n";

        return response($bibtex)
            ->header('Content-Type', 'application/x-bibtex')
            ->header('Content-Disposition', 'attachment; filename="' . \Str::slug($submission->title) . '.bib"');
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

    /**
     * Display a custom page created via Navigation Menu Items or Sidebar Blocks (Custom Pages).
     */
    public function customPage(string $journalSlug, string $path): View
    {
        $journal = $this->resolveJournal($journalSlug);

        // 1. Try Navigation Menu Item
        $page = \App\Models\NavigationMenuItem::where('journal_id', $journal->id)
            ->where('type', 'page')
            ->where('path', $path)
            ->where('is_active', true)
            ->first();

        if ($page) {
            return view('journal.public.custom-page', [
                'journal' => $journal,
                'page' => $page,
                'content' => $page->content, // Assuming content field exists
                'title' => $page->title,
            ]);
        }

        // 2. Try Sidebar Block (Custom Page)
        $blockPage = \App\Models\SidebarBlock::where('journal_id', $journal->id)
            ->where('type', 'page')
            ->where('slug', $path)
            ->where('is_active', true)
            ->firstOrFail();

        return view('journal.public.custom-page', [
            'journal' => $journal,
            'page' => $blockPage, // Duck typing or separate variable
            'content' => $blockPage->content,
            'title' => $blockPage->show_title ? $blockPage->title : null,
        ]);
    }
}


