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
        Schema::create('receipt_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('verification_hash', 64)->unique();
            $table->json('receipt_data'); // Store snapshot of receipt data for comparison
            $table->timestamp('generated_at');
            $table->integer('verified_count')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            $table->string('generated_by')->nullable(); // User who generated
            $table->timestamps();
            
            $table->index(['verification_hash']);
            $table->index(['project_id', 'generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_verifications');
    }
};
