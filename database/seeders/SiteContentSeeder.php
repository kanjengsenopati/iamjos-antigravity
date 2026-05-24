<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    /**
     * Seed portal-level site content with neutral, installation-agnostic defaults.
     *
     * All values here are generic placeholders that the site administrator
     * should update via the admin UI after installation.
     * No organisation-specific names, addresses, or social media URLs are seeded.
     */
    public function run(): void
    {
        $appName = config('app.name', 'Academic Journal System');

        $contents = [
            // ─── Hero Section ─────────────────────────────────────────────
            [
                'key'   => 'hero_title',
                'value' => '',
                'group' => 'hero',
                'type'  => 'text',
                'label' => 'Hero Title',
            ],
            [
                'key'   => 'hero_subtitle',
                'value' => '',
                'group' => 'hero',
                'type'  => 'textarea',
                'label' => 'Hero Subtitle',
            ],
            [
                'key'   => 'hero_search_placeholder',
                'value' => 'Search journals, articles, or authors...',
                'group' => 'hero',
                'type'  => 'text',
                'label' => 'Search Placeholder',
            ],
            [
                'key'   => 'hero_popular_tags',
                'value' => json_encode([]),   // Empty — admin configures per-installation
                'group' => 'hero',
                'type'  => 'json',
                'label' => 'Popular Search Tags',
            ],

            // ─── Featured Section ──────────────────────────────────────────
            [
                'key'   => 'featured_title',
                'value' => '',
                'group' => 'featured',
                'type'  => 'text',
                'label' => 'Featured Section Title',
            ],
            [
                'key'   => 'featured_subtitle',
                'value' => '',
                'group' => 'featured',
                'type'  => 'text',
                'label' => 'Featured Section Subtitle',
            ],
            [
                'key'   => 'featured_journal_ids',
                'value' => json_encode([]),
                'group' => 'featured',
                'type'  => 'json',
                'label' => 'Featured Journal IDs',
            ],

            // ─── Footer Section ────────────────────────────────────────────
            // All contact details are empty — admin must fill these in
            [
                'key'   => 'footer_about',
                'value' => '',
                'group' => 'footer',
                'type'  => 'textarea',
                'label' => 'Footer About Text',
            ],
            [
                'key'   => 'footer_address',
                'value' => '',
                'group' => 'footer',
                'type'  => 'text',
                'label' => 'Footer Address',
            ],
            [
                'key'   => 'footer_email',
                'value' => '',
                'group' => 'footer',
                'type'  => 'text',
                'label' => 'Footer Email',
            ],
            [
                'key'   => 'footer_phone',
                'value' => '',
                'group' => 'footer',
                'type'  => 'text',
                'label' => 'Footer Phone',
            ],

            // ─── Social Media ──────────────────────────────────────────────
            // All empty — admin configures per-installation
            [
                'key'   => 'social_facebook',
                'value' => '',
                'group' => 'social',
                'type'  => 'text',
                'label' => 'Facebook URL',
            ],
            [
                'key'   => 'social_twitter',
                'value' => '',
                'group' => 'social',
                'type'  => 'text',
                'label' => 'Twitter/X URL',
            ],
            [
                'key'   => 'social_instagram',
                'value' => '',
                'group' => 'social',
                'type'  => 'text',
                'label' => 'Instagram URL',
            ],
            [
                'key'   => 'social_youtube',
                'value' => '',
                'group' => 'social',
                'type'  => 'text',
                'label' => 'YouTube URL',
            ],

            // ─── Browse Subjects ───────────────────────────────────────────
            // Empty by default — admin configures subject categories per-installation
            [
                'key'   => 'browse_subjects',
                'value' => json_encode([]),
                'group' => 'browse',
                'type'  => 'json',
                'label' => 'Browse Subjects',
            ],

            // ─── About Page ────────────────────────────────────────────────
            [
                'key'   => 'about_title',
                'value' => 'About ' . $appName,
                'group' => 'about',
                'type'  => 'text',
                'label' => 'About Page Title',
            ],
            [
                'key'   => 'about_content',
                'value' => '',   // Empty — admin writes their own about page content
                'group' => 'about',
                'type'  => 'html',
                'label' => 'About Page Content',
            ],
        ];

        foreach ($contents as $content) {
            SiteContent::updateOrCreate(
                ['key' => $content['key']],
                $content
            );
        }

        $this->command->info('✅ SiteContentSeeder: ' . count($contents) . ' content keys seeded (neutral defaults).');
    }
}
