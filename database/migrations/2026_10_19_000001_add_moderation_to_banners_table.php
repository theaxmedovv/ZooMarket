<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Any user can place a banner now, but it only shows on the home page once the main admin
     * approves it. Banners that already exist were the admin's own, so they stay approved.
     */
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('moderation_status', 20)->default('approved')->after('is_active');
            $table->text('moderation_reason')->nullable()->after('moderation_status');
            $table->timestamp('moderated_at')->nullable()->after('moderation_reason');
            $table->foreignId('moderated_by')->nullable()->after('moderated_at')->constrained('users')->nullOnDelete();

            $table->index('moderation_status');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropConstrainedForeignId('moderated_by');
            $table->dropIndex(['moderation_status']);
            $table->dropColumn(['moderation_status', 'moderation_reason', 'moderated_at']);
        });
    }
};
