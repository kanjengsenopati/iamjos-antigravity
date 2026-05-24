<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $roles = [
            'Super Admin',
            'Admin',
            'Journal Manager',
            'Editor',
            'Section Editor',
            'Reviewer',
            'Author',
            'Reader',
        ];

        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        // Create basic permissions
        $permissions = [
            'view submissions',
            'create submissions',
            'edit submissions',
            'delete submissions',
            'publish submissions',
            'manage users',
            'manage journals',
            'manage settings',
            'review submissions',
            'edit articles',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        // Assign permissions to roles
        $superAdmin = Role::findByName('Super Admin', 'web');
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::findByName('Admin', 'web');
        $admin->givePermissionTo([
            'view submissions',
            'create submissions',
            'edit submissions',
            'delete submissions',
            'publish submissions',
            'manage users',
            'manage journals',
            'manage settings',
        ]);

        $journalManager = Role::findByName('Journal Manager', 'web');
        $journalManager->givePermissionTo([
            'view submissions',
            'create submissions',
            'edit submissions',
            'delete submissions',
            'publish submissions',
            'manage users',
            'manage settings',
        ]);

        $editor = Role::findByName('Editor', 'web');
        $editor->givePermissionTo([
            'view submissions',
            'edit submissions',
            'publish submissions',
            'edit articles',
        ]);

        $sectionEditor = Role::findByName('Section Editor', 'web');
        $sectionEditor->givePermissionTo([
            'view submissions',
            'edit submissions',
        ]);

        $reviewer = Role::findByName('Reviewer', 'web');
        $reviewer->givePermissionTo([
            'view submissions',
            'review submissions',
        ]);

        $author = Role::findByName('Author', 'web');
        $author->givePermissionTo([
            'view submissions',
            'create submissions',
        ]);
    }
}
