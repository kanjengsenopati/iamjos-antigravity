<?php

namespace App\Http\Controllers\Journal;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Announcement;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class JournalHomepageController extends Controller
{
    /**
     * Display the journal public homepage.
     */
    public function index(string $journalSlug)
    {
        $journal = Journal::where('slug', $journalSlug)
            ->where('enabled', true)
            ->firstOrFail();

        // Get all website settings with defaults
        $settings = $this->getSettingsWithDefaults($journal);

        // Get announcements if enabled (New logic using journals table columns)
        $announcements = collect();
        // Check both the global enable switch AND the homepage display switch
        if ($journal->enable_announcements && $journal->show_announcements_on_homepage) {
            $count = $journal->num_announcements_homepage ?? 5; // Default to 5 if null
            if ($count > 0) {
                $announcements = Announcement::where('journal_id', $journal->id)
                    ->where('is_active', true)
                    ->where('published_at', '<=', now())
                    ->orderBy('published_at', 'desc')
                    ->take($count)
                    ->get();
            }
        } elseif (!empty($settings['show_announcements'])) {
            // Fallback to legacy setting if new columns are not yet in use or populated
             $announcements = $this->getAnnouncements($journal);
        }

        // Get editorial team if enabled
        $editorialTeam = collect();
        if ($settings['show_editorial_team']) {
            $editorialTeam = $this->getEditorialTeam($journal);
        }

        // Get indexed in images
        $val = $settings['indexed_in_images'] ?? [];
        $indexedInImages = is_array($val) ? $val : json_decode($val, true) ?? [];

        // Get current issue
        $currentIssue = $journal->issues()
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->first();

        // Get latest articles (Fallback or for sidebar/sections)
        $latestArticles = $journal->submissions()
            ->where('status', 'published')
            ->with(['authors', 'galleys', 'currentPublication'])
            ->orderBy('published_at', 'desc')
            ->take(10)
            ->get();

        // Get published articles count
        $publishedCount = $journal->submissions()
            ->where('status', 'published')
            ->count();

        return view('journal.public.home', compact(
            'journal',
            'settings',
            'announcements',
            'editorialTeam',
            'indexedInImages',
            'currentIssue',
            'latestArticles',
            'publishedCount'
        ));
    }

    /**
     * Get settings with defaults merged.
     * Defaults are neutral/empty — no fake stats or misleading claims.
     */
    private function getSettingsWithDefaults(Journal $journal): array
    {
        $defaults = [
            // Content
            'about'    => '',
            'masthead' => ['about' => '', 'editorial_team' => ''],

            // Appearance — neutral defaults, overridden by journal settings
            'hero_image'      => null,
            'primary_color'   => '#4F46E5',
            'secondary_color' => '#7C3AED',

            // Hero Content — use journal data, never fake taglines
            'hero_title'       => $journal->name,
            'hero_description' => $journal->description ?? '',
            'hero_tagline'     => '',   // Empty — journal must configure this explicitly

            // Stats — empty by default; journal must set real values
            // Never show fake numbers like "25%", "4 Weeks", "1000+"
            'stat_acceptance_rate' => '',
            'stat_review_time'     => '',
            'stat_impact_factor'   => '',
            'stat_citations'       => '',

            // Section Visibility
            'show_announcements'  => true,
            'show_editorial_team' => true,
            'show_indexed_in'     => true,
            'show_stats'          => false, // Hidden by default until real stats are configured

            // Indexed In
            'indexed_in_images' => [],

            // Footer — empty by default
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
     * Get announcements for the journal.
     * Returns empty collection if none exist — never shows fake data.
     */
    private function getAnnouncements(Journal $journal)
    {
        return Announcement::where('journal_id', $journal->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('is_urgent', 'desc')
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();
        // No placeholder fallback — empty state is handled in the view
    }

    /**
     * Get editorial team for the journal.
     * Returns real editors from DB only — never shows fake placeholder people.
     */
    private function getEditorialTeam(Journal $journal)
    {
        $editorRoles = ['Editor', 'Editor-in-Chief', 'Section Editor', 'Journal Manager'];

        $editorialTeam = collect();

        foreach ($editorRoles as $roleName) {
            $users = $journal->usersWithRole($roleName);
            foreach ($users as $user) {
                $editorialTeam->push((object) [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'role'        => $roleName,
                    'affiliation' => $user->affiliation ?? null, // null — not 'Institution'
                    'email'       => $user->email,
                    'avatar'      => $user->avatar_url ?? null,
                    'orcid'       => $user->orcid ?? null,
                ]);
            }
        }

        // Return real editors only — empty collection if none assigned
        // The view handles the empty state with a proper message
        return $editorialTeam->take(6);
    }
}
