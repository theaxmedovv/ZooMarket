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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('categories')->insert([
            ['name' => 'It', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mushuk', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Qush', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Quyon', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Boshqa', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
