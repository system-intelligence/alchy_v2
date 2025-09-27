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
        Schema::table('inventories', function (Blueprint $table) {
            $table->longText('image_blob')->nullable(); // Store base64 encoded image
            $table->string('image_mime_type')->nullable(); // Store MIME type
            $table->string('image_filename')->nullable(); // Store original filename
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['image_blob', 'image_mime_type', 'image_filename']);
        });
    }
};
