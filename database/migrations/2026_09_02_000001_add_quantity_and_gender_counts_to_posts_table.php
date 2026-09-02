<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('gender');
            $table->unsignedInteger('male_quantity')->default(0)->after('quantity');
            $table->unsignedInteger('female_quantity')->default(0)->after('male_quantity');
        });

        // Backfill existing posts
        DB::table('posts')->where('gender', 'male')->update([
            'quantity' => 1,
            'male_quantity' => 1,
            'female_quantity' => 0,
        ]);

        DB::table('posts')->where('gender', 'female')->update([
            'quantity' => 1,
            'male_quantity' => 0,
            'female_quantity' => 1,
        ]);

        DB::table('posts')->whereNotIn('gender', ['male', 'female'])->update([
            'quantity' => 1,
            'male_quantity' => 1,
            'female_quantity' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'male_quantity', 'female_quantity']);
        });
    }
};
