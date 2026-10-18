<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Category-specific answers from the "Yangi e'lon" form (weight, vaccinated, hive type, ...),
     * see config/listing_types.php.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->json('details')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('details');
        });
    }
};
