<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed only the reference data the app needs to run (no demo users or listings).
     * Safe to run repeatedly: every seeder uses firstOrCreate.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
