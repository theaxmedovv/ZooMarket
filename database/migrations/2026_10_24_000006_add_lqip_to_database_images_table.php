<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('database_images', function (Blueprint $table) {
            // Tiny blurred preview (base64 of a small JPEG) shown while the real image loads
            $table->text('lqip')->nullable()->after('size');
            $table->unsignedInteger('width')->nullable()->after('lqip');
            $table->unsignedInteger('height')->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('database_images', function (Blueprint $table) {
            $table->dropColumn(['lqip', 'width', 'height']);
        });
    }
};
