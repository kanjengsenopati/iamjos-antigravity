<?php

namespace App\Http\Middleware;

use App\Models\Journal;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectJournalContext
{
    /**
     * Handle an incoming request.
     *
     * This middleware detects the journal context from the route parameter
     * and binds it to the service container for global access.
     * Unlike JournalContextMiddleware, this does NOT abort if no journal is found,
     * allowing the request to continue for portal-level authentication.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $journalParam = $request->route('journal');
        $journal = null;

        if ($journalParam instanceof Journal) {
            $journal = $journalParam;
        } elseif (is_string($journalParam)) {
            $journal = Journal::where('slug', $journalParam)->first();
        }

        if ($journal) {
            // For disabled journals on public auth routes, show generic message
            // Authenticated staff can still access via main dashboard routes
            if (!$journal->enabled && !$request->user()) {
                abort(404, 'Journal not found.');
            }

            // Bind the journal to the service container for global access
            app()->instance('currentJournal', $journal);

            // Share with all views
            view()->share('currentJournal', $journal);

            // Store in session for redirect after login
            session()->put('login_journal_slug', $journal->slug);
        } else {
            // No journal context - portal level (or invalid slug if param was provided)
            if ($journalParam && !$journal) {
                 abort(404, 'Journal not found.');
            }
            
            app()->instance('currentJournal', null);
            view()->share('currentJournal', null);
            
            // Clear any lingering journal context so it doesn't leak into global portal actions
            session()->forget('login_journal_slug');
        }

        // Set application locale based on journal primary locale, session, cookie, or fallback config
        $locale = null;
        if ($journal) {
            $journalSettings = $journal->getWebsiteSettings();
            $locale = $journalSettings['primary_locale'] ?? 'en';
            
            // Sync to session & cookie for consistency
            session(['app_locale' => $locale]);
            cookie()->queue('app_locale', $locale, 525600);
        } elseif (session()->has('app_locale')) {
            $locale = session('app_locale');
        } elseif ($request->hasCookie('app_locale')) {
            $locale = $request->cookie('app_locale');
            session(['app_locale' => $locale]); // Sync back to session
        } else {
            $locale = config('app.locale', 'en');
        }
        app()->setLocale($locale);
        if (class_exists(\Carbon\Carbon::class)) {
            \Carbon\Carbon::setLocale(in_array($locale, ['id', 'id_ID']) ? 'id' : $locale);
        }

        return $next($request);
    }
}
