<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles/permissions (important when seeding)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        | Keep these feature-based (not overly granular)
        */

        $permissions = [
            // Admin access
            'access admin dashboard',

            // Pokémon management (core admin feature)
            'manage pokemon',

            // User management
            'manage users',

            // Role management
            'manage roles'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $userRole = Role::firstOrCreate(['name' => 'user']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        /*
        |--------------------------------------------------------------------------
        | Assign permissions to roles
        |--------------------------------------------------------------------------
        */

        // Users: no admin permissions (read-only app access)
        $userRole->syncPermissions([]);

        // Admins: full admin access for your app
        $adminRole->syncPermissions([
            'access admin dashboard',
            'manage pokemon',
            'manage users',
            'manage roles'
        ]);
    }
}