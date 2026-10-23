<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('moderated_at')->index();
        });

        // Listings already on sale get a fresh period instead of vanishing the day this ships
        DB::table('posts')
            ->whereNotIn('status', ['sold', 'archived'])
            ->update(['expires_at' => now()->addDays((int) config('zoomarket.listing_days', 30))]);
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
