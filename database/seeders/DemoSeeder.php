<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Journal;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Seed demo data untuk development/testing.
     *
     * Jalankan dengan:
     *   php artisan db:seed --class=DemoSeeder
     *
     * JANGAN jalankan di production!
     */
    public function run(): void
    {
        // =====================================================
        // PRODUCTION GUARD — refuse to run in production
        // =====================================================
        if (app()->isProduction()) {
            $this->command->error('❌ DemoSeeder REFUSED: Cannot run in production environment.');
            $this->command->error('   Set APP_ENV=local or APP_ENV=staging to use demo data.');
            return;
        }

        $this->command->warn('');
        $this->command->warn('⚠️  DEMO SEEDER — Hanya untuk development/testing!');
        $this->command->warn('');

        // =====================================================
        // 1. CREATE DEMO JOURNALS
        // =====================================================
        $journals = [
            [
                'name' => 'Journal of Informatics and Technology',
                'abbreviation' => 'JIT',
                'slug' => 'jit',
                'description' => 'JIT is a peer-reviewed journal focusing on the latest research in computer science, information systems, software engineering, and emerging technologies.',
                'publisher' => 'Universitas Akademik Indonesia',
                'issn_print' => '2580-1234',
                'issn_online' => '2580-1235',
                'enabled' => true,
                'visible' => true,
                'settings' => [
                    'contact' => ['email' => 'jit@example.com', 'phone' => '+62 21 1234567'],
                    'policies' => ['peer_review' => 'double_blind', 'review_days' => 30],
                ],
                'sections' => [
                    ['name' => 'Original Research', 'abbreviation' => 'OR', 'sort_order' => 1],
                    ['name' => 'Review Articles', 'abbreviation' => 'RA', 'sort_order' => 2],
                    ['name' => 'Short Communications', 'abbreviation' => 'SC', 'sort_order' => 3],
                    ['name' => 'Case Studies', 'abbreviation' => 'CS', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Medical Science Journal',
                'abbreviation' => 'MSJ',
                'slug' => 'medika',
                'description' => 'Medical Science Journal publishes high-quality research in clinical medicine, public health, biomedical sciences, and healthcare management.',
                'publisher' => 'Fakultas Kedokteran Universitas Akademik',
                'issn_print' => '2581-5678',
                'issn_online' => '2581-5679',
                'enabled' => true,
                'visible' => true,
                'settings' => [
                    'contact' => ['email' => 'medika@example.com', 'phone' => '+62 21 7654321'],
                    'policies' => ['peer_review' => 'double_blind', 'review_days' => 45],
                ],
                'sections' => [
                    ['name' => 'Clinical Research', 'abbreviation' => 'CR', 'sort_order' => 1],
                    ['name' => 'Public Health', 'abbreviation' => 'PH', 'sort_order' => 2],
                    ['name' => 'Medical Education', 'abbreviation' => 'ME', 'sort_order' => 3],
                    ['name' => 'Case Reports', 'abbreviation' => 'CP', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Journal of Business and Economics',
                'abbreviation' => 'JBE',
                'slug' => 'jbe',
                'description' => 'JBE is dedicated to publishing innovative research in business administration, economics, finance, marketing, and organizational behavior.',
                'publisher' => 'Fakultas Ekonomi dan Bisnis',
                'issn_print' => '2582-9012',
                'issn_online' => '2582-9013',
                'enabled' => true,
                'visible' => true,
                'settings' => [
                    'contact' => ['email' => 'jbe@example.com', 'phone' => '+62 21 9876543'],
                    'policies' => ['peer_review' => 'single_blind', 'review_days' => 30],
                ],
                'sections' => [
                    ['name' => 'Business Strategy', 'abbreviation' => 'BS', 'sort_order' => 1],
                    ['name' => 'Financial Economics', 'abbreviation' => 'FE', 'sort_order' => 2],
                    ['name' => 'Marketing Management', 'abbreviation' => 'MM', 'sort_order' => 3],
                    ['name' => 'Human Resources', 'abbreviation' => 'HR', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Engineering and Applied Sciences',
                'abbreviation' => 'EAS',
                'slug' => 'eas',
                'description' => 'EAS covers all aspects of engineering including civil, mechanical, electrical, chemical engineering, as well as applied sciences.',
                'publisher' => 'Fakultas Teknik Universitas Akademik',
                'issn_print' => '2583-3456',
                'issn_online' => '2583-3457',
                'enabled' => true,
                'visible' => true,
                'settings' => [
                    'contact' => ['email' => 'eas@example.com', 'phone' => '+62 21 3456789'],
                    'policies' => ['peer_review' => 'double_blind', 'review_days' => 35],
                ],
                'sections' => [
                    ['name' => 'Civil Engineering', 'abbreviation' => 'CE', 'sort_order' => 1],
                    ['name' => 'Mechanical Engineering', 'abbreviation' => 'ME', 'sort_order' => 2],
                    ['name' => 'Electrical Engineering', 'abbreviation' => 'EE', 'sort_order' => 3],
                    ['name' => 'Environmental Engineering', 'abbreviation' => 'ENV', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'IAMJOS Demo Journal',
                'abbreviation' => 'IAMJOS',
                'slug' => 'iamjos',
                'description' => 'An open-access academic journal platform built for the Indonesian research community.',
                'publisher' => 'IAMJOS Publishing',
                'enabled' => true,
                'visible' => true,
                'settings' => [
                    'contact' => ['email' => 'editor@example.com', 'phone' => '+62 21 1234567'],
                    'policies' => ['open_access' => true, 'peer_review' => 'double-blind'],
                    'appearance' => ['primary_color' => '#0ea5e9', 'font_family' => 'Inter'],
                ],
                'sections' => [
                    ['name' => 'Original Articles', 'abbreviation' => 'OA', 'sort_order' => 1],
                    ['name' => 'Review Articles', 'abbreviation' => 'RA', 'sort_order' => 2],
                    ['name' => 'Case Studies', 'abbreviation' => 'CS', 'sort_order' => 3],
                    ['name' => 'Short Communications', 'abbreviation' => 'SC', 'sort_order' => 4],
                ],
            ],
        ];

        foreach ($journals as $journalData) {
            $sections = $journalData['sections'];
            unset($journalData['sections']);

            $journal = Journal::updateOrCreate(
                ['slug' => $journalData['slug']],
                array_merge($journalData, ['path' => $journalData['slug']])
            );

            foreach ($sections as $sectionData) {
                Section::updateOrCreate(
                    ['journal_id' => $journal->id, 'abbreviation' => $sectionData['abbreviation']],
                    array_merge($sectionData, ['journal_id' => $journal->id, 'is_active' => true])
                );
            }

            $this->command->info("  ✅ Journal: {$journal->name} ({$journal->abbreviation})");
        }

        // =====================================================
        // 2. CREATE DEMO USERS
        // =====================================================
        $demoPassword = env('DEMO_USER_PASSWORD', 'Demo@IamJOS2026!');

        $demoUsers = [
            ['email' => 'admin@demo.iamjos.id',    'name' => 'Journal Administrator', 'role' => 'Admin',    'affiliation' => 'Demo Publishing'],
            ['email' => 'editor@demo.iamjos.id',   'name' => 'Chief Editor',          'role' => 'Editor',   'affiliation' => 'Universitas Demo'],
            ['email' => 'reviewer@demo.iamjos.id', 'name' => 'Dr. Reviewer',          'role' => 'Reviewer', 'affiliation' => 'Institut Demo'],
            ['email' => 'author@demo.iamjos.id',   'name' => 'John Author',           'role' => 'Author',   'affiliation' => 'Universitas Demo'],
        ];

        foreach ($demoUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name'              => $userData['name'],
                    'password'          => Hash::make($demoPassword),
                    'email_verified_at' => now(),
                    'affiliation'       => $userData['affiliation'],
                    'country'           => 'Indonesia',
                ]
            );
            $user->syncRoles([$userData['role']]);
        }

        $this->command->newLine();
        $this->command->info('👥 Demo users created:');
        $this->command->table(
            ['Email', 'Role'],
            collect($demoUsers)->map(fn($u) => [$u['email'], $u['role']])->toArray()
        );
        $this->command->warn("🔑 Password: {$demoPassword}");
        $this->command->warn('⚠️  Ubah password di .env via DEMO_USER_PASSWORD');

        // =====================================================
        // 3. SEED JOURNAL DEPENDENCIES
        // =====================================================
        $this->call([
            EmailTemplateSeeder::class,
            DefaultNavigationSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Demo data seeded! ' . count($journals) . ' journals, ' . count($demoUsers) . ' users.');
        $this->command->newLine();
    }
}
