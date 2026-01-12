<?php

namespace App\Http\Middleware;

use App\Models\Journal;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JournalContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $journalSlug = $request->route('journal');

        if (!$journalSlug) {
            abort(404, 'Journal not specified.');
        }

        // Find the journal by slug
        $journal = Journal::where('slug', $journalSlug)
            ->where('enabled', true)
            ->first();

        if (!$journal) {
            abort(404, 'Journal not found.');
        }

        // Bind the journal to the service container for global access
        app()->instance('currentJournal', $journal);

        // Also share with all views
        view()->share('currentJournal', $journal);

        // Store in session for convenience
        session()->flash('current_journal_id', $journal->id);

        return $next($request);
    }
}
