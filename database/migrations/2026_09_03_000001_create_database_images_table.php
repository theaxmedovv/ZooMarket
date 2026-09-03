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
        Schema::create('database_images', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->default('image/jpeg');
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `database_images` ADD `data` LONGBLOB NOT NULL AFTER `size`');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_images');
    }
};
