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
            $table->longText('avatar_blob')->nullable()->after('password');
            $table->string('avatar_mime_type')->nullable()->after('avatar_blob');
            $table->string('avatar_filename')->nullable()->after('avatar_mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_blob', 'avatar_mime_type', 'avatar_filename']);
        });
    }
};
