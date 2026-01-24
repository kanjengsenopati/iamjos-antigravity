<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\AuthRequest;
use App\Models\Admin;
use App\Models\Journal;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Display the login page with context-aware branding.
     *
     * Portal Context: /login - Generic IAMJOS branding
     * Journal Context: /journal/{slug}/login - Journal-specific branding
     */
    public function index(Request $request)
    {
        // Get journal context from service container (set by DetectJournalContext middleware)
        $journal = app()->bound('currentJournal') ? app('currentJournal') : null;

        // Store intended journal for post-login redirect (backward compatibility)
        if ($request->has('intended_journal')) {
            session(['intended_journal' => $request->query('intended_journal')]);
        }

        // Prepare branding data based on context
        $branding = $this->getBrandingData($journal);

        return view('admins.auth.login', compact('journal', 'branding'));
    }

    /**
     * Handle authentication with context-aware redirects.
     */
    public function authenticate(AuthRequest $request)
    {
        $credentials = $request->validated();

        // Get journal context
        $journal = app()->bound('currentJournal') ? app('currentJournal') : null;

        // Determine if input is email or username
        $loginInput = $credentials['email'];
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Find user by either email or username
        $user = User::where($fieldType, $loginInput)->first();

        // Guard Clause: User not found
        if (!$user) {
            return back()->with('warning', 'Email/Username atau password tidak sesuai.')
                ->withInput($request->only('email'));
        }

        // Check if user account is disabled
        if ($user->disabled) {
            return back()->with('warning', 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.')
                ->withInput($request->only('email'));
        }

        // Prepare credentials for authentication
        $authCredentials = [
            $fieldType => $loginInput,
            'password' => $credentials['password']
        ];

        // Attempt authentication
        if (Auth::guard('web')->attempt($authCredentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::guard('web')->user();

            // Update last login timestamp
            $user->update(['date_last_login' => now()]);

            // Determine redirect based on context
            return $this->handlePostLoginRedirect($user, $journal);
        }

        return back()->with('warning', 'Email/Username atau password tidak sesuai.')
            ->withInput($request->only('email'));
    }

    /**
     * Handle logout with context-aware redirects.
     */
    public function logout(Request $request)
    {
        // Capture current journal context before session invalidation
        $journalSlug = $request->route('journal');

        // Also check session for journal context as fallback
        if (!$journalSlug) {
            $journalSlug = session('login_journal_slug');
        }

        // Logout the user
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect based on context
        if ($journalSlug) {
            // Verify the journal still exists
            $journal = Journal::where('slug', $journalSlug)->where('enabled', true)->first();
            if ($journal) {
                return redirect()->route('journal.public.home', ['journal' => $journalSlug])
                    ->with('success', 'Anda berhasil logout.');
            }
        }

        // Default: redirect to portal home
        return redirect()->route('portal.home')
            ->with('success', 'Anda berhasil logout.');
    }

    /**
     * Handle post-login redirect based on user role and context.
     */
    protected function handlePostLoginRedirect(User $user, ?Journal $journal)
    {
        // Super Admin always goes to site admin dashboard
        if ($user->hasRole('Super Admin')) {
            session()->forget(['intended_journal', 'login_journal_slug']);
            return redirect()->route('admin.site.index');
        }

        // Priority 1: Journal context from current route
        if ($journal) {
            session()->forget(['intended_journal', 'login_journal_slug']);
            return redirect()->route('journal.dashboard', ['journal' => $journal->slug]);
        }

        // Priority 2: Intended journal from query parameter or session
        $intendedJournal = session('intended_journal');
        if ($intendedJournal) {
            session()->forget('intended_journal');
            return redirect()->route('journal.dashboard', ['journal' => $intendedJournal]);
        }

        // Priority 3: Journal stored in session from context middleware
        $loginJournalSlug = session('login_journal_slug');
        if ($loginJournalSlug) {
            session()->forget('login_journal_slug');
            return redirect()->route('journal.dashboard', ['journal' => $loginJournalSlug]);
        }

        // Priority 4: If user has only one journal, go directly to it
        $userJournals = $user->registeredJournals();
        if ($userJournals->count() === 1) {
            return redirect()->route('journal.dashboard', ['journal' => $userJournals->first()->slug]);
        }

        // Default: Go to journal selection page
        return redirect()->route('journal.select');
    }

    /**
     * Get branding data based on context.
     */
    protected function getBrandingData(?Journal $journal): array
    {
        if ($journal) {
            return [
                'name' => $journal->name,
                'acronym' => $journal->abbreviation ?? $journal->name,
                'description' => $journal->description ?? $journal->summary ?? 'Academic Journal Publishing Platform',
                'logo_url' => $journal->logo_path ? asset('storage/' . $journal->logo_path) : null,
                'cover_url' => $journal->homepage_image_path ? asset('storage/' . $journal->homepage_image_path) : null,
                'headline' => $journal->name,
                'tagline' => $journal->description ?? 'Submit, Review, and Publish Academic Research',
            ];
        }

        // Default portal branding
        return [
            'name' => config('app.name', 'IAMJOS'),
            'acronym' => 'IAMJOS',
            'description' => 'Indonesian Academic Journal System',
            'logo_url' => null,
            'cover_url' => null,
            'headline' => 'Advance Your Academic Research',
            'tagline' => 'A modern platform for managing academic journal submissions, peer reviews, and publications with streamlined workflows.',
        ];
    }

    /**
     * Legacy method for accessing from office (kept for backward compatibility).
     */
    public function accessFromOffice($id)
    {
        // Find the admin by ID
        $admin = Admin::find($id);

        // Check if admin exists
        if (! $admin && $admin->is_operational == 0) {
            return redirect()->route('login')->with('error', 'Maaf, Anda tidak memiliki akses ke CMS');
        }

        // Log in the admin using their ID
        Auth::guard('web')->loginUsingId($id);

        // Redirect to the dashboard with a success message
        return redirect()->route('dashboard.index')->with('success', 'Berhasil Masuk ke Operational CMS');
    }
}
