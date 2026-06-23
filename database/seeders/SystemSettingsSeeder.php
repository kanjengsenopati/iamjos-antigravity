<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Seed the system_settings table with application-wide defaults.
     */
    public function run(): void
    {
        $settings = [
            // ─── Pagination ───────────────────────────────────────────────
            [
                'key'         => 'pagination_submissions',
                'value'       => '10',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of submissions displayed per page.',
            ],
            [
                'key'         => 'pagination_issues',
                'value'       => '10',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of issues displayed per page.',
            ],
            [
                'key'         => 'pagination_journals',
                'value'       => '12',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of journals displayed per page.',
            ],
            [
                'key'         => 'pagination_reviews',
                'value'       => '10',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of review assignments displayed per page.',
            ],
            [
                'key'         => 'pagination_announcements',
                'value'       => '10',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of announcements displayed per page.',
            ],
            [
                'key'         => 'pagination_notifications',
                'value'       => '15',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of notifications displayed per page.',
            ],
            [
                'key'         => 'pagination_search_results',
                'value'       => '20',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of search results displayed per page.',
            ],
            [
                'key'         => 'pagination_portal_journals',
                'value'       => '12',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of journals displayed per page on the portal.',
            ],

            // ─── Homepage / Portal Display Limits ─────────────────────────
            [
                'key'         => 'homepage_latest_articles_count',
                'value'       => '6',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of latest articles shown on the homepage.',
            ],
            [
                'key'         => 'homepage_featured_journals_count',
                'value'       => '6',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of featured journals shown on the homepage.',
            ],
            [
                'key'         => 'homepage_announcements_count',
                'value'       => '3',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of announcements shown on the homepage.',
            ],
            [
                'key'         => 'homepage_editorial_team_count',
                'value'       => '5',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of editorial team members shown on the homepage.',
            ],
            [
                'key'         => 'portal_featured_journals_count',
                'value'       => '10',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of featured journals shown on the portal listing.',
            ],
            [
                'key'         => 'portal_latest_articles_count',
                'value'       => '5',
                'type'        => 'integer',
                'group'       => 'pagination',
                'description' => 'Number of latest articles shown on the portal.',
            ],

            // ─── File Upload Constraints ───────────────────────────────────
            [
                'key'         => 'upload_max_size_manuscript',
                'value'       => '52428800',
                'type'        => 'integer',
                'group'       => 'uploads',
                'description' => 'Maximum manuscript upload size in bytes (default: 50 MB).',
            ],
            [
                'key'         => 'upload_max_size_galley',
                'value'       => '104857600',
                'type'        => 'integer',
                'group'       => 'uploads',
                'description' => 'Maximum galley file upload size in bytes (default: 100 MB).',
            ],
            [
                'key'         => 'upload_max_size_avatar',
                'value'       => '2097152',
                'type'        => 'integer',
                'group'       => 'uploads',
                'description' => 'Maximum avatar image upload size in bytes (default: 2 MB).',
            ],
            [
                'key'         => 'upload_max_size_image',
                'value'       => '5242880',
                'type'        => 'integer',
                'group'       => 'uploads',
                'description' => 'Maximum general image upload size in bytes (default: 5 MB).',
            ],
            [
                'key'         => 'upload_allowed_extensions_manuscript',
                'value'       => 'pdf,doc,docx',
                'type'        => 'string',
                'group'       => 'uploads',
                'description' => 'Comma-separated list of allowed manuscript file extensions.',
            ],
            [
                'key'         => 'upload_allowed_extensions_galley',
                'value'       => 'pdf,epub,html,xml',
                'type'        => 'string',
                'group'       => 'uploads',
                'description' => 'Comma-separated list of allowed galley file extensions.',
            ],
            [
                'key'         => 'upload_allowed_extensions_avatar',
                'value'       => 'jpg,jpeg,png,gif,webp',
                'type'        => 'string',
                'group'       => 'uploads',
                'description' => 'Comma-separated list of allowed avatar image extensions.',
            ],
            [
                'key'         => 'upload_allowed_extensions_image',
                'value'       => 'jpg,jpeg,png,gif,webp,svg',
                'type'        => 'string',
                'group'       => 'uploads',
                'description' => 'Comma-separated list of allowed general image extensions.',
            ],

            // ─── Reviewer Reminders ────────────────────────────────────────
            [
                'key'         => 'reviewer_reminder_days_before',
                'value'       => '7,3,1,0',
                'type'        => 'string',
                'group'       => 'reviewer',
                'description' => 'Comma-separated days before deadline to send reviewer reminders.',
            ],
            [
                'key'         => 'reviewer_reminder_overdue_interval_days',
                'value'       => '3',
                'type'        => 'integer',
                'group'       => 'reviewer',
                'description' => 'Interval in days between overdue reviewer reminder emails.',
            ],

            // ─── External API / Integrations ──────────────────────────────
            [
                'key'         => 'crossref_deposit_url_live',
                'value'       => 'https://doi.crossref.org/servlet/deposit',
                'type'        => 'string',
                'group'       => 'integrations',
                'description' => 'Crossref live deposit endpoint URL.',
            ],
            [
                'key'         => 'crossref_deposit_url_test',
                'value'       => 'https://test.crossref.org/servlet/deposit',
                'type'        => 'string',
                'group'       => 'integrations',
                'description' => 'Crossref test deposit endpoint URL.',
            ],
            [
                'key'         => 'crossref_api_base_url',
                'value'       => 'https://api.crossref.org/works/',
                'type'        => 'string',
                'group'       => 'integrations',
                'description' => 'Crossref REST API base URL for DOI lookups.',
            ],
            [
                'key'         => 'recaptcha_verify_url',
                'value'       => 'https://www.google.com/recaptcha/api/siteverify',
                'type'        => 'string',
                'group'       => 'integrations',
                'description' => 'Google reCAPTCHA server-side verification endpoint.',
            ],
            [
                'key'         => 'google_scholar_search_url',
                'value'       => 'https://scholar.google.com/scholar',
                'type'        => 'string',
                'group'       => 'integrations',
                'description' => 'Google Scholar search base URL.',
            ],

            // ─── Application ──────────────────────────────────────────────
            [
                'key'         => 'maintenance_mode',
                'value'       => 'false',
                'type'        => 'boolean',
                'group'       => 'app',
                'description' => 'When true, the application displays a maintenance page to non-admin visitors.',
            ],
            [
                'key'         => 'app_version',
                'value'       => '1.0.0',
                'type'        => 'string',
                'group'       => 'app',
                'description' => 'Current application version string.',
            ],

            // ─── SMTP / Email Configuration ───────────────────────────────
            [
                'key'         => 'mail_mailer',
                'value'       => 'smtp',
                'type'        => 'string',
                'group'       => 'email',
                'description' => 'Mail driver to use (smtp, phpmail, log).',
            ],
            [
                'key'         => 'mail_host',
                'value'         => '127.0.0.1',
                'type'        => 'string',
                'group'       => 'email',
                'description' => 'SMTP server host address.',
            ],
            [
                'key'         => 'mail_port',
                'value'       => '1025',
                'type'        => 'integer',
                'group'       => 'email',
                'description' => 'SMTP server port (e.g., 25, 465, 587, 1025, 2525).',
            ],
            [
                'key'         => 'mail_username',
                'value'       => '',
                'type'        => 'string',
                'group'       => 'email',
                'description' => 'SMTP username (leave empty if not required).',
            ],
            [
                'key'         => 'mail_password',
                'value'       => '',
                'type'        => 'string',
                'group'       => 'email',
                'description' => 'SMTP password (leave empty if not required).',
            ],
            [
                'key'         => 'mail_encryption',
                'value'       => 'tls',
                'type'        => 'string',
                'group'       => 'email',
                'description' => 'SMTP encryption protocol (none, tls, ssl).',
            ],
            [
                'key'         => 'mail_from_address',
                'value'       => 'noreply@example.com',
                'type'        => 'string',
                'group'       => 'email',
                'description' => 'Email address used as the sender (From).',
            ],
            [
                'key'         => 'mail_from_name',
                'value'       => 'IAMJOS System',
                'type'        => 'string',
                'group'       => 'email',
                'description' => 'Name displayed as the sender (From Name).',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value'       => $setting['value'],
                    'type'        => $setting['type'],
                    'group'       => $setting['group'],
                    'description' => $setting['description'],
                ]
            );
        }

        $this->command->info('✅ SystemSettingsSeeder: ' . count($settings) . ' settings seeded.');
    }
}
