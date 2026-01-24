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
        $journalSlug = $request->route('journal');

        if ($journalSlug) {
            // Find the journal by slug
            $journal = Journal::where('slug', $journalSlug)->first();

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
                // Journal slug provided but not found
                abort(404, 'Journal not found.');
            }
        } else {
            // No journal context - portal level
            app()->instance('currentJournal', null);
            view()->share('currentJournal', null);
        }

        return $next($request);
    }
}
