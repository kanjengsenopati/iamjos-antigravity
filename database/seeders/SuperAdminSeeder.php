<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed the Super Admin user.
     *
     * Reads credentials from .env:
     *   SUPER_ADMIN_EMAIL    (default: superadmin@iamjos.id)
     *   SUPER_ADMIN_PASSWORD (default: auto-generated secure password)
     *   SUPER_ADMIN_NAME     (default: Super Administrator)
     *
     * Safe to run multiple times (idempotent).
     */
    public function run(): void
    {
        $email    = env('SUPER_ADMIN_EMAIL', 'superadmin@iamjos.id');
        $name     = env('SUPER_ADMIN_NAME', 'Super Administrator');
        $password = env('SUPER_ADMIN_PASSWORD');

        // Generate secure password if not set in .env
        $passwordWasGenerated = false;
        if (empty($password)) {
            $password = Str::upper(Str::random(4))
                . Str::random(4)
                . rand(1000, 9999)
                . '!@';
            $passwordWasGenerated = true;
        }

        // Idempotent: only create if not already exists
        $superAdmin = User::firstOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'password'          => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        // Always ensure Super Admin role is assigned (even if user already exists)
        if (! $superAdmin->hasRole('Super Admin')) {
            $superAdmin->assignRole('Super Admin');
            $roleAssigned = true;
        } else {
            $roleAssigned = false;
        }

        // Display results
        $this->command->newLine();
        $this->command->info('┌─────────────────────────────────────────────┐');
        $this->command->info('│         SUPER ADMIN CREDENTIALS             │');
        $this->command->info('├─────────────────────────────────────────────┤');
        $this->command->info("│  Email    : {$email}");
        $this->command->info("│  Name     : {$name}");

        if ($superAdmin->wasRecentlyCreated) {
            if ($passwordWasGenerated) {
                $this->command->warn("│  Password : {$password}  ← CATAT INI!");
                $this->command->warn('│  ⚠️  Password ini tidak akan muncul lagi!');
            } else {
                $this->command->info('│  Password : (dari SUPER_ADMIN_PASSWORD di .env)');
            }
            $this->command->info('│  Status   : ✅ User baru berhasil dibuat');
        } else {
            $this->command->info('│  Status   : ℹ️  User sudah ada, dilewati');
            if ($roleAssigned) {
                $this->command->info('│  Role     : ✅ Role Super Admin berhasil dipasang');
            } else {
                $this->command->info('│  Role     : ✅ Role Super Admin sudah terpasang');
            }
        }

        $this->command->info('└─────────────────────────────────────────────┘');
        $this->command->newLine();
    }
}
