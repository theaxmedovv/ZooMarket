<?php

namespace App\Support;

use App\Models\Post;

/**
 * What the Katalog menu and the filter sidebar offer: for each category, the most common
 * breeds and regions (viloyatlar) among listings on sale (with how many listings each has).
 */
class Catalog
{
    /**
     * @return array<int, array{breeds?: list<array{name: string, total: int}>, regions?: list<array{name: string, total: int}>}>
     */
    public static function stats(int $limit = 12): array
    {
        $stats = [];

        // Breeds are capped; regions are a short fixed list, so all of them are shown
        foreach (['breed' => ['breeds', $limit], 'region' => ['regions', null]] as $column => [$key, $max]) {
            Post::listed()
                ->whereNotNull($column)->where($column, '!=', '')
                ->selectRaw("category_id, {$column} as name, count(*) as total")
                ->groupBy('category_id', $column)
                ->orderByDesc('total')->orderBy($column)
                ->get()
                ->groupBy('category_id')
                ->each(function ($rows, $categoryId) use (&$stats, $key, $max) {
                    $stats[$categoryId][$key] = ($max ? $rows->take($max) : $rows)
                        ->map(fn ($r) => ['name' => $r->name, 'total' => (int) $r->total])
                        ->all();
                });
        }

        return $stats;
    }
}
