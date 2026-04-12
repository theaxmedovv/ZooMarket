<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('title')->constrained()->nullOnDelete();
            $table->string('breed')->nullable()->after('category_id');
            $table->string('gender', 10)->nullable()->after('breed');
            $table->string('age', 50)->nullable()->after('gender');
            $table->string('color', 100)->nullable()->after('age');
            $table->text('description')->nullable()->after('color');
            $table->decimal('price', 12, 2)->nullable()->after('description');
            $table->string('currency', 10)->default('UZS')->after('price');
            $table->boolean('is_negotiable')->default(false)->after('currency');
            $table->string('location')->nullable()->after('is_negotiable');
            $table->string('status', 20)->default('active')->after('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn([
                'breed',
                'gender',
                'age',
                'color',
                'description',
                'price',
                'currency',
                'is_negotiable',
                'location',
                'status',
            ]);
        });
    }
};
