<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalSettingsController extends Controller
{
    /**
     * Display portal settings form.
     */
    public function edit(): View
    {
        // Get all site content by groups
        $hero = SiteContent::getGroup('hero');
        $featured = SiteContent::getGroup('featured');
        $footer = SiteContent::getGroup('footer');
        $social = SiteContent::getGroup('social');
        $browse = SiteContent::getGroup('browse');

        // Get all journals for featured selection
        $journals = Journal::where('enabled', true)
            ->orderBy('name')
            ->get();

        // Get currently selected featured journal IDs
        $featuredIds = $featured['featured_journal_ids'] ?? [];

        return view('admin.site.portal-settings', compact(
            'hero',
            'featured',
            'footer',
            'social',
            'browse',
            'journals',
            'featuredIds'
        ));
    }

    /**
     * Update portal settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'required|string|max:500',
            'hero_search_placeholder' => 'nullable|string|max:100',
            'hero_popular_tags' => 'nullable|string',
            'featured_title' => 'nullable|string|max:255',
            'featured_subtitle' => 'nullable|string|max:255',
            'featured_journal_ids' => 'nullable|array',
            'featured_journal_ids.*' => 'exists:journals,id',
            'footer_about' => 'nullable|string|max:1000',
            'footer_address' => 'nullable|string|max:255',
            'footer_email' => 'nullable|email|max:255',
            'footer_phone' => 'nullable|string|max:50',
            'social_facebook' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
        ]);

        // Process popular tags (comma-separated to JSON array)
        $popularTags = [];
        if ($request->hero_popular_tags) {
            $popularTags = array_map('trim', explode(',', $request->hero_popular_tags));
        }

        // Bulk update
        SiteContent::bulkUpdate([
            // Hero
            'hero_title' => $request->hero_title,
            'hero_subtitle' => $request->hero_subtitle,
            'hero_search_placeholder' => $request->hero_search_placeholder ?? 'Cari Jurnal, Artikel, atau Penulis...',
            'hero_popular_tags' => $popularTags,

            // Featured
            'featured_title' => $request->featured_title ?? 'Jurnal Pilihan',
            'featured_subtitle' => $request->featured_subtitle ?? '',
            'featured_journal_ids' => $request->featured_journal_ids ?? [],

            // Footer
            'footer_about' => $request->footer_about ?? '',
            'footer_address' => $request->footer_address ?? '',
            'footer_email' => $request->footer_email ?? '',
            'footer_phone' => $request->footer_phone ?? '',

            // Social
            'social_facebook' => $request->social_facebook ?? '',
            'social_twitter' => $request->social_twitter ?? '',
            'social_instagram' => $request->social_instagram ?? '',
            'social_youtube' => $request->social_youtube ?? '',
        ]);

        // Clear all caches
        SiteContent::clearCache();

        return redirect()->route('admin.portal.edit')
            ->with('success', 'Portal settings updated successfully.');
    }
}
