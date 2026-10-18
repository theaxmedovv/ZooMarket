<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Listing categories, in the order the site shows them. "Boshqa" always stays last.
     * Safe to run again: existing rows are kept (listings stay attached), missing ones are added,
     * and the order is refreshed.
     */
    public const CATEGORIES = [
        'It',
        'Mushuk',
        'Kemiruvchilar',
        'Quyon',
        'Qushlar',
        'Kaptar',
        'Parrandalar',
        'Baliq',
        'Sudralib yuruvchilar',
        'Ekzotik hayvonlar',
        'Qoramol',
        "Qo'y va echki",
        'Otlar',
        'Tuya',
        'Asalarichilik',
        'Boshqa',
    ];

    public function run(): void
    {
        // The old "Qush" category becomes "Qushlar" in place, so its listings keep their category
        if (! Category::where('name', 'Qushlar')->exists()) {
            Category::where('name', 'Qush')->update(['name' => 'Qushlar']);
        }

        foreach (self::CATEGORIES as $position => $name) {
            Category::updateOrCreate(['name' => $name], ['sort_order' => $position + 1]);
        }
    }
}
