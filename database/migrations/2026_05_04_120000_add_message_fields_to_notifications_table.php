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
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type')->nullable()->after('body');
            $table->foreignId('related_user_id')
                ->nullable()
                ->after('type')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('action_url')->nullable()->after('related_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('related_user_id');
            $table->dropColumn(['type', 'action_url']);
        });
    }
};
