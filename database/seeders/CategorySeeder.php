<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Pet store categories. Existing rows are kept, missing ones are added.
     */
    public function run(): void
    {
        $categories = [
            'It',
            'Mushuk',
            'Qush',
            'Baliq',
            'Quyon',
            'Kemiruvchilar',
            'Sudralib yuruvchilar',
            'Boshqa',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
