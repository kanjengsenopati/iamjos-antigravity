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
        // IAMJOS Production Seeders (Clean Install)
        // =====================================================
        // Seperti OJS: fresh install = kosong, tapi RBAC matrix
        // sudah siap. Journals dibuat manual via Admin Dashboard.
        //
        // Untuk data demo (dev/testing):
        //   php artisan db:seed --class=DemoSeeder
        // =====================================================

        $this->command->info('🚀 Starting IAMJOS Database Seeding...');
        $this->command->newLine();

        $this->call([
            // 1. Setup Roles & Permissions (RBAC Matrix)
            RolesAndPermissionsSeeder::class,

            // 2. Super Admin (dari .env — SUPER_ADMIN_EMAIL/PASSWORD)
            SuperAdminSeeder::class,

            // 3. Portal & Site Content (Public Pages Templates)
            SiteContentSeeder::class,
            PortalSeeder::class,

            // 4. Email & Notification Templates
            EmailTemplateSeeder::class,
            NotificationTemplateSeeder::class,

            // 5. System Settings (application-wide technical configuration)
            SystemSettingsSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ IAMJOS Database seeded successfully!');
        $this->command->info('📋 Journals: 0 (buat via Admin Dashboard)');
        $this->command->newLine();
        $this->command->warn('⚠️  Pastikan password Super Admin sudah dicatat!');
        $this->command->warn('💡 Untuk data demo: php artisan db:seed --class=DemoSeeder');
        $this->command->newLine();
    }
}
