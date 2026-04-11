<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions = [
            'create posts',
            'read posts',
            'edit posts',
            'delete posts',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $sellerRole = Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web']);
        $sellerRole->syncPermissions($permissions);

        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $userRole->syncPermissions(['read posts']);

        $seller = User::firstOrCreate([
            'email' => 'seller@example.com',
        ], [
            'name' => 'Seller User',
            'password' => bcrypt('password'),
        ]);
        $seller->syncRoles(['seller']);

        $user = User::firstOrCreate([
            'email' => 'user@example.com',
        ], [
            'name' => 'Regular User',
            'password' => bcrypt('password'),
        ]);
        $user->syncRoles(['user']);
    }
}
