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
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->unsignedInteger('male_quantity')->default(0)->after('quantity');
            $table->unsignedInteger('female_quantity')->default(0)->after('male_quantity');
        });

        // Backfill existing single-gender requests
        DB::table('purchase_requests')->where('gender', 'female')->update([
            'female_quantity' => DB::raw('quantity'),
        ]);

        DB::table('purchase_requests')->where(function ($q) {
            $q->where('gender', '!=', 'female')->orWhereNull('gender');
        })->update([
            'male_quantity' => DB::raw('quantity'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['male_quantity', 'female_quantity']);
        });
    }
};
