<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Sellers and buyers used to be separate roles. Every account can now both sell and buy,
 * so existing sellers move to the single "user" role, which gets the listing permissions.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $seller = Role::where('name', 'seller')->where('guard_name', 'web')->first();
        $user = Role::where('name', 'user')->where('guard_name', 'web')->first();

        // Fresh database: the seeder creates the roles.
        if (! $seller && ! $user) {
            return;
        }

        $permissions = collect(['create posts', 'read posts', 'edit posts', 'delete posts'])
            ->map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));

        $user ??= Role::create(['name' => 'user', 'guard_name' => 'web']);
        $user->syncPermissions($permissions);

        if ($seller) {
            User::whereHas('roles', fn ($q) => $q->whereKey($seller->id))
                ->each(fn (User $account) => $account->assignRole($user));

            $seller->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Irreversible: there is no record of which users were sellers.
    }
};
