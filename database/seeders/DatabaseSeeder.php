<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // =====================================================
        // IAMJOS Production Seeders
        // =====================================================

        $this->command->info('🚀 Starting IAMJOS Database Seeding...');
        $this->command->newLine();

        $this->call([
            // 1. Setup Roles & Permissions (WAJIB PERTAMA)
            RolesAndPermissionsSeeder::class,

            // 2. Super Admin (WAJIB KEDUA - butuh roles sudah ada)
            SuperAdminSeeder::class,

            // 3. Portal & Site Content (Public Pages)
            SiteContentSeeder::class,
            PortalSeeder::class,

            // 4. Email Templates
            EmailTemplateSeeder::class,

            // 5. Sample Journals & Initial Data (Dev/Staging Only)
            JournalSeeder::class,
            InitialDataSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ IAMJOS Database seeded successfully!');
        $this->command->newLine();
        $this->command->warn('⚠️  Pastikan password Super Admin sudah dicatat!');
        $this->command->newLine();
    }
}
