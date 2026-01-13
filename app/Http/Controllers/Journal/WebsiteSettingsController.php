<?php

namespace App\Http\Controllers\Journal;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\JournalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebsiteSettingsController extends Controller
{
    /**
     * Show the website settings form.
     */
    public function edit()
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found');
        }

        // Get all current settings for this journal
        $settings = $journal->getWebsiteSettings();

        // Define defaults for missing settings
        $defaults = $this->getDefaultSettings($journal);

        // Merge with actual settings (actual takes precedence)
        $settings = array_merge($defaults, $settings);

        return view('journal.admin.settings.website', compact('journal', 'settings'));
    }

    /**
     * Update website settings.
     */
    public function update(Request $request)
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found');
        }

        // Define which settings belong to which group and type
        $settingsConfig = $this->getSettingsConfig();

        foreach ($settingsConfig as $name => $config) {
            $value = $request->input($name);

            // Handle file uploads
            if ($config['type'] === 'file' && $request->hasFile($name)) {
                $file = $request->file($name);
                $path = $file->store("journals/{$journal->id}/website", 'public');
                $value = $path;
            } elseif ($config['type'] === 'file') {
                // Don't update if no new file uploaded
                continue;
            }

            // Handle multi-file uploads (indexed_in_images)
            if ($config['type'] === 'json' && $name === 'indexed_in_images') {
                $existingImages = json_decode($journal->getWebsiteSetting('indexed_in_images', '[]'), true) ?? [];

                if ($request->hasFile('indexed_in_images')) {
                    foreach ($request->file('indexed_in_images') as $file) {
                        $path = $file->store("journals/{$journal->id}/website/indexers", 'public');
                        $existingImages[] = $path;
                    }
                }

                // Handle removals
                if ($request->has('remove_indexed_images')) {
                    $toRemove = $request->input('remove_indexed_images', []);
                    foreach ($toRemove as $path) {
                        Storage::disk('public')->delete($path);
                        $existingImages = array_filter($existingImages, fn($img) => $img !== $path);
                    }
                }

                $value = json_encode(array_values($existingImages));
            }

            // Handle boolean toggles
            if ($config['type'] === 'boolean') {
                $value = $request->boolean($name);
            }

            // Skip if value is null and not explicitly set
            if ($value === null && !$request->has($name)) {
                continue;
            }

            $journal->setWebsiteSetting(
                $name,
                $value,
                $config['type'],
                $config['group']
            );
        }

        return redirect()
            ->route('journal.settings.website.edit', ['journal' => $journal->slug])
            ->with('success', 'Website settings updated successfully.');
    }

    /**
     * Delete an indexed image.
     */
    public function deleteIndexedImage(Request $request)
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found');
        }

        $path = $request->input('path');
        $existingImages = json_decode($journal->getWebsiteSetting('indexed_in_images', '[]'), true) ?? [];

        // Remove from storage
        Storage::disk('public')->delete($path);

        // Remove from array
        $existingImages = array_filter($existingImages, fn($img) => $img !== $path);

        $journal->setWebsiteSetting('indexed_in_images', json_encode(array_values($existingImages)), 'json', 'content');

        return response()->json(['success' => true]);
    }

    /**
     * Get default settings.
     */
    private function getDefaultSettings(Journal $journal): array
    {
        return [
            // Appearance
            'hero_image' => null,
            'primary_color' => '#4F46E5',
            'secondary_color' => '#7C3AED',

            // Hero Content
            'hero_title' => $journal->name,
            'hero_description' => $journal->description ?? 'A peer-reviewed scholarly journal dedicated to advancing knowledge and research.',
            'hero_tagline' => 'Peer-Reviewed • Open Access • Indexed',

            // Stats
            'stat_acceptance_rate' => '25%',
            'stat_review_time' => '4 Weeks',
            'stat_impact_factor' => 'N/A',
            'stat_citations' => '1000+',

            // Section Visibility
            'show_announcements' => true,
            'show_editorial_team' => true,
            'show_indexed_in' => true,
            'show_stats' => true,

            // Indexed In
            'indexed_in_images' => '[]',

            // Footer
            'footer_description' => $journal->description ?? 'A leading academic journal publishing cutting-edge research.',
            'social_facebook' => '',
            'social_twitter' => '',
            'social_linkedin' => '',
            'social_instagram' => '',
            'contact_email' => '',
            'contact_phone' => '',
            'contact_address' => '',
        ];
    }

    /**
     * Get settings configuration (name => type, group).
     */
    private function getSettingsConfig(): array
    {
        return [
            // Appearance
            'hero_image' => ['type' => 'file', 'group' => 'appearance'],
            'primary_color' => ['type' => 'string', 'group' => 'appearance'],
            'secondary_color' => ['type' => 'string', 'group' => 'appearance'],

            // Hero Content
            'hero_title' => ['type' => 'string', 'group' => 'content'],
            'hero_description' => ['type' => 'string', 'group' => 'content'],
            'hero_tagline' => ['type' => 'string', 'group' => 'content'],

            // Stats
            'stat_acceptance_rate' => ['type' => 'string', 'group' => 'content'],
            'stat_review_time' => ['type' => 'string', 'group' => 'content'],
            'stat_impact_factor' => ['type' => 'string', 'group' => 'content'],
            'stat_citations' => ['type' => 'string', 'group' => 'content'],

            // Section Visibility
            'show_announcements' => ['type' => 'boolean', 'group' => 'content'],
            'show_editorial_team' => ['type' => 'boolean', 'group' => 'content'],
            'show_indexed_in' => ['type' => 'boolean', 'group' => 'content'],
            'show_stats' => ['type' => 'boolean', 'group' => 'content'],

            // Indexed In
            'indexed_in_images' => ['type' => 'json', 'group' => 'content'],

            // Footer
            'footer_description' => ['type' => 'string', 'group' => 'footer'],
            'social_facebook' => ['type' => 'string', 'group' => 'footer'],
            'social_twitter' => ['type' => 'string', 'group' => 'footer'],
            'social_linkedin' => ['type' => 'string', 'group' => 'footer'],
            'social_instagram' => ['type' => 'string', 'group' => 'footer'],
            'contact_email' => ['type' => 'string', 'group' => 'footer'],
            'contact_phone' => ['type' => 'string', 'group' => 'footer'],
            'contact_address' => ['type' => 'string', 'group' => 'footer'],
        ];
    }
}
