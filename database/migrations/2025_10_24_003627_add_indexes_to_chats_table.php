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
        Schema::table('chats', function (Blueprint $table) {
            // Add composite index for conversation queries
            $table->index(['user_id', 'recipient_id', 'created_at'], 'idx_user_recipient_created');
            $table->index(['recipient_id', 'user_id', 'created_at'], 'idx_recipient_user_created');
            
            // Add individual indexes for foreign keys if not already present
            if (!Schema::hasColumn('chats', 'user_id') || !$this->hasIndex('chats', 'chats_user_id_index')) {
                $table->index('user_id');
            }
            if (!Schema::hasColumn('chats', 'recipient_id') || !$this->hasIndex('chats', 'chats_recipient_id_index')) {
                $table->index('recipient_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropIndex('idx_user_recipient_created');
            $table->dropIndex('idx_recipient_user_created');
        });
    }
    
    /**
     * Check if an index exists on a table
     */
    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $indexData) {
            if ($indexData['name'] === $index) {
                return true;
            }
        }
        return false;
    }
};
