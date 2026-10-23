<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            // A report is about a listing or about a user, never both
            $table->foreignId('post_id')->nullable()->constrained('posts')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('reason', 30);
            $table->text('comment')->nullable();
            $table->string('status', 20)->default('open')->index();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
