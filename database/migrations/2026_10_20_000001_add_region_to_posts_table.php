<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The region (viloyat) is picked from a fixed list; "location" keeps the exact place typed by the owner.
     * Older listings have no region until they are edited.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('region', 60)->nullable()->after('currency')->index();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['region']);
            $table->dropColumn('region');
        });
    }
};
