<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Roles the app relies on: sellers manage listings, users (buyers) browse and request.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = ['create posts', 'read posts', 'edit posts', 'delete posts'];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web'])
            ->syncPermissions($permissions);

        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web'])
            ->syncPermissions(['read posts']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
