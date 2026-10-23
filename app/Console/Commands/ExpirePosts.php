<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class ExpirePosts extends Command
{
    protected $signature = 'posts:expire';

    protected $description = "Muddati tugagan e'lonlarni arxivga o'tkazadi (egasi ularni arxivdan yangilashi mumkin)";

    public function handle(): int
    {
        $count = Post::query()
            ->whereNotIn('status', ['sold', 'archived'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'archived']);

        $this->info("{$count} ta e'lon arxivga o'tkazildi.");

        return self::SUCCESS;
    }
}
