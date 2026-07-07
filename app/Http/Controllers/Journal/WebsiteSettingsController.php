<?php

namespace App\Http\Controllers\Journal;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\SitePage;
use App\Facades\Settings;
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

        // Parse json settings if they come as strings
        foreach (['supported_locales', 'supported_form_locales', 'supported_submission_locales'] as $jsonKey) {
            if (isset($settings[$jsonKey]) && is_string($settings[$jsonKey])) {
                $settings[$jsonKey] = json_decode($settings[$jsonKey], true) ?? ['en', 'id'];
            }
        }

        // Fetch dynamic static pages from database
        $staticPages = SitePage::ordered()->get();

        // Build dynamic plugins list with active statuses and routes
        $plugins = $this->getPluginsData($journal);

        // Get Site Setting to access global reCAPTCHA keys
        $recaptchaSiteKey   = Settings::site('recaptcha_site_key');
        $recaptchaSecretKey = Settings::site('recaptcha_secret_key');

        return view('journal.admin.settings.website', compact(
            'journal',
            'settings',
            'staticPages',
            'plugins',
            'recaptchaSiteKey',
            'recaptchaSecretKey'
        ));
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

        // Validate file uploads
        $request->validate([
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'favicon' => 'nullable|file|mimes:ico,png,jpg,jpeg,svg,webp|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'homepage_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'indexed_in_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,svg|max:2048',
        ]);

        // Handle Logo Upload (stored in journals table)
        if ($request->hasFile('logo')) {
            if ($journal->logo_path) {
                Storage::disk('public')->delete($journal->logo_path);
            }
            $journal->logo_path = $request->file('logo')->store("journals/{$journal->id}/appearance", 'public');
        }

        // Handle Favicon Upload (stored in journals table)
        if ($request->hasFile('favicon')) {
            if ($journal->favicon_path) {
                Storage::disk('public')->delete($journal->favicon_path);
            }
            $journal->favicon_path = $request->file('favicon')->store("journals/{$journal->id}/appearance", 'public');
        }

        // Handle Thumbnail Upload (stored in journals table)
        if ($request->hasFile('thumbnail')) {
            if ($journal->thumbnail_path) {
                Storage::disk('public')->delete($journal->thumbnail_path);
            }
            $journal->thumbnail_path = $request->file('thumbnail')->store("journals/{$journal->id}/appearance", 'public');
        }

        // Handle Homepage Image Upload (stored in journals table)
        if ($request->hasFile('homepage_image')) {
            if ($journal->homepage_image_path) {
                Storage::disk('public')->delete($journal->homepage_image_path);
            }
            $journal->homepage_image_path = $request->file('homepage_image')->store("journals/{$journal->id}/appearance", 'public');
        }

        // Handle Header Background Toggle (stored in journals table)
        $journal->show_homepage_image_in_header = $request->boolean('show_homepage_image_in_header');

        // Handle Page Footer (stored in journals table)
        if ($request->has('page_footer')) {
            $journal->page_footer = $request->input('page_footer');
        }

        // Handle Additional Content (stored in journals table)
        if ($request->has('additional_content')) {
            $journal->additional_content = $request->input('additional_content');
        }

        // Handle Information Content (stored in journals table)
        if ($request->has('info_readers')) {
            $journal->info_readers = $request->input('info_readers');
        }
        if ($request->has('info_authors')) {
            $journal->info_authors = $request->input('info_authors');
        }
        if ($request->has('info_librarians')) {
            $journal->info_librarians = $request->input('info_librarians');
        }

        // Handle Announcement Settings (stored in journals table)
        $journal->enable_announcements = $request->boolean('enable_announcements');
        if ($request->has('announcements_introduction')) {
            $journal->announcements_introduction = $request->input('announcements_introduction');
        }
        $journal->show_announcements_on_homepage = $request->boolean('show_announcements_on_homepage');
        if ($request->has('num_announcements_homepage')) {
            $journal->num_announcements_homepage = $request->input('num_announcements_homepage');
        }

        // Handle Security/Recaptcha Toggle
        if ($request->input('tab') === 'security' || $request->has('is_recaptcha_enabled')) {
             $journal->is_recaptcha_enabled = $request->boolean('is_recaptcha_enabled');
        }

        // Save journal model changes
        $journal->save();

        // Handle other settings (stored in journal_settings table)
        $settingsConfig = $this->getSettingsConfig();

        foreach ($settingsConfig as $name => $config) {
            $value = $request->input($name);

            if ($config['type'] === 'file') {
                continue;
            }

            // Handle multi-file uploads (indexed_in_images)
            if ($config['type'] === 'json' && $name === 'indexed_in_images') {
                $existingSetting = Settings::journal($journal->id, 'indexed_in_images', []);
                if (is_array($existingSetting)) {
                    $existingImages = $existingSetting;
                } elseif (is_string($existingSetting)) {
                    $existingImages = json_decode($existingSetting, true) ?? [];
                } else {
                    $existingImages = [];
                }

                if ($request->hasFile('indexed_in_images')) {
                    foreach ($request->file('indexed_in_images') as $file) {
                        $path = $file->store("journals/{$journal->id}/website/indexers", 'public');
                        $existingImages[] = $path;
                    }
                }

                $value = json_encode(array_values($existingImages));
            } elseif ($config['type'] === 'json' && is_array($value)) {
                $value = json_encode(array_values($value));
            }

            // Handle boolean toggles
            if ($config['type'] === 'boolean') {
                $value = $request->boolean($name);
            }

            // Skip if value is null and not explicitly set
            if ($value === null && !$request->has($name)) {
                continue;
            }

            Settings::setJournal(
                $journal->id,
                $name,
                $value,
                $config['type'],
                $config['group']
            );
        }

        if ($request->has('primary_locale')) {
            $prim = $request->input('primary_locale');
            session(['app_locale' => $prim]);
            app()->setLocale($prim);
            cookie()->queue('app_locale', $prim, 525600);
        }

        $tab = $request->input('tab', 'setup');
        $setupTab = $request->input('setup_tab', 'languages');
        $appearanceTab = $request->input('appearance_tab', 'theme');

        return redirect()
            ->route('journal.settings.website.edit', [
                'journal' => $journal->slug,
                'tab' => $tab,
                'setup_tab' => $setupTab,
                'appearance_tab' => $appearanceTab
            ])
            ->with('success', 'Website settings updated successfully.');
    }

    /**
     * Delete an indexed image.
     */
    public function deleteIndexedImage(Request $request)
    {
        $journal = current_journal();

        if (!$journal) {
            return response()->json(['error' => 'Journal not found'], 404);
        }

        $path = $request->input('path');
        if ($path) {
            Storage::disk('public')->delete($path);

            $existingSetting = Settings::journal($journal->id, 'indexed_in_images', []);
            $images = is_array($existingSetting) ? $existingSetting : (json_decode($existingSetting, true) ?? []);

            $images = array_filter($images, fn($img) => $img !== $path);

            Settings::setJournal($journal->id, 'indexed_in_images', json_encode(array_values($images)), 'json', 'content');
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete logo.
     */
    public function deleteLogo()
    {
        $journal = current_journal();
        if ($journal && $journal->logo_path) {
            Storage::disk('public')->delete($journal->logo_path);
            $journal->logo_path = null;
            $journal->save();
        }
        return response()->json(['success' => true]);
    }

    /**
     * Delete favicon.
     */
    public function deleteFavicon()
    {
        $journal = current_journal();
        if ($journal && $journal->favicon_path) {
            Storage::disk('public')->delete($journal->favicon_path);
            $journal->favicon_path = null;
            $journal->save();
        }
        return response()->json(['success' => true]);
    }

    /**
     * Delete thumbnail.
     */
    public function deleteThumbnail()
    {
        $journal = current_journal();
        if ($journal && $journal->thumbnail_path) {
            Storage::disk('public')->delete($journal->thumbnail_path);
            $journal->thumbnail_path = null;
            $journal->save();
        }
        return response()->json(['success' => true]);
    }

    /**
     * Delete homepage image.
     */
    public function deleteHomepageImage()
    {
        $journal = current_journal();
        if ($journal && $journal->homepage_image_path) {
            Storage::disk('public')->delete($journal->homepage_image_path);
            $journal->homepage_image_path = null;
            $journal->show_homepage_image_in_header = false;
            $journal->save();
        }
        return response()->json(['success' => true]);
    }

    /**
     * Get dynamic list of plugins.
     */
    private function getPluginsData(Journal $journal): array
    {
        return [
            [
                'key' => 'quicksubmit',
                'name' => 'Quick Submit Plugin',
                'description' => 'Quickly add published articles directly to issues without going through editorial workflows.',
                'icon' => 'fa-solid fa-bolt',
                'color' => 'text-amber-500',
                'status' => 'Active',
                'route' => route('journal.settings.tools.quicksubmit.index', $journal->slug),
            ],
            [
                'key' => 'crossref',
                'name' => 'CrossRef XML Export & DOI Plugin',
                'description' => 'Export article metadata in CrossRef XML format and configure DOI registration settings.',
                'icon' => 'fa-solid fa-link',
                'color' => 'text-blue-500',
                'status' => 'Active',
                'route' => route('journal.settings.tools.crossref.index', $journal->slug),
            ],
            [
                'key' => 'native',
                'name' => 'Native XML Plugin',
                'description' => 'Import and export articles and issues in IAMJOS native XML format for backup or migration.',
                'icon' => 'fa-solid fa-code-bracket',
                'color' => 'text-indigo-500',
                'status' => 'Active',
                'route' => route('journal.settings.tools.native.index', $journal->slug),
            ],
            [
                'key' => 'users',
                'name' => 'Users XML Plugin',
                'description' => 'Import and export user accounts and roles in XML format for bulk management.',
                'icon' => 'fa-solid fa-users',
                'color' => 'text-purple-500',
                'status' => 'Active',
                'route' => route('journal.settings.tools.users.index', $journal->slug),
            ],
            [
                'key' => 'doaj',
                'name' => 'DOAJ Export Plugin',
                'description' => 'Export journal metadata for the Directory of Open Access Journals (DOAJ) indexing.',
                'icon' => 'fa-solid fa-globe',
                'color' => 'text-emerald-500',
                'status' => 'Active',
                'route' => route('journal.settings.tools.doaj.index', $journal->slug),
            ],
            [
                'key' => 'recaptcha',
                'name' => 'reCAPTCHA Protection',
                'description' => 'Bot protection plugin for user login and registration pages.',
                'icon' => 'fa-solid fa-robot',
                'color' => 'text-cyan-500',
                'status' => $journal->is_recaptcha_enabled ? 'Active' : 'Installed',
                'route' => route('journal.settings.website.edit', $journal->slug) . '?tab=security',
            ],
        ];
    }

    /**
     * Get default settings.
     */
    private function getDefaultSettings(Journal $journal): array
    {
        return [
            // Content
            'about' => '',
            'masthead' => ['about' => '', 'editorial_team' => ''],

            // Appearance
            'primary_color' => '#4F46E5',
            'secondary_color' => '#7C3AED',

            // Section Visibility
            'show_announcements' => true,
            'show_editorial_team' => true,
            'show_indexed_in' => true,

            // Indexed In
            'indexed_in_images' => [],

            // Footer
            'footer_description' => $journal->description ?? 'A leading academic journal publishing cutting-edge research.',
            'social_facebook' => '',
            'social_twitter' => '',
            'social_linkedin' => '',
            'social_instagram' => '',
            'contact_email' => '',
            'contact_phone' => '',
            'contact_address' => '',

            // Setup & Locales
            'primary_locale' => 'en',
            'supported_locales' => ['en', 'id'],
            'supported_form_locales' => ['en', 'id'],
            'supported_submission_locales' => ['en', 'id'],
            'items_per_page' => 25,
            'privacy_statement' => 'The names and email addresses entered in this journal site will be used exclusively for the stated purposes of this journal and will not be made available for any other purpose or to any other party.',
            'date_format' => 'F j, Y',
            'time_zone' => 'Asia/Jakarta',
        ];
    }

    /**
     * Get settings configuration (name => type, group).
     */
    private function getSettingsConfig(): array
    {
        return [
            // Content
            'about' => ['type' => 'string', 'group' => 'content'],
            'masthead' => ['type' => 'json', 'group' => 'content'],

            // Appearance (colors only - images now in journals table)
            'primary_color' => ['type' => 'string', 'group' => 'appearance'],
            'secondary_color' => ['type' => 'string', 'group' => 'appearance'],

            // Section Visibility
            'show_announcements' => ['type' => 'boolean', 'group' => 'content'],
            'show_editorial_team' => ['type' => 'boolean', 'group' => 'content'],
            'show_indexed_in' => ['type' => 'boolean', 'group' => 'content'],

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

            // Setup & Locales
            'primary_locale' => ['type' => 'string', 'group' => 'setup'],
            'supported_locales' => ['type' => 'json', 'group' => 'setup'],
            'supported_form_locales' => ['type' => 'json', 'group' => 'setup'],
            'supported_submission_locales' => ['type' => 'json', 'group' => 'setup'],
            'items_per_page' => ['type' => 'integer', 'group' => 'setup'],
            'privacy_statement' => ['type' => 'string', 'group' => 'setup'],
            'date_format' => ['type' => 'string', 'group' => 'setup'],
            'time_zone' => ['type' => 'string', 'group' => 'setup'],
        ];
    }
}
