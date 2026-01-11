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
        // IAMJOS OJS Clone Seeders
        // =====================================================

        $this->call([
            // 1. Setup Roles & Permissions (Spatie)
            RolesAndPermissionsSeeder::class,

            // 2. Create Initial Data (Journal, Sections, Users)
            InitialDataSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ IAMJOS Database seeded successfully!');
        $this->command->info('');
        $this->command->info('You can now login with:');
        $this->command->info('  Email: superadmin@iamjos.id');
        $this->command->info('  Password: password');
        $this->command->info('');
    }
}
