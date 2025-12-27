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
        Schema::table('users', function (Blueprint $table) {
            $table->json('unread_private_counts')->nullable()->after('avatar_filename');
            $table->unsignedInteger('unread_group_count')->default(0)->after('unread_private_counts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['unread_private_counts', 'unread_group_count']);
        });
    }
};
