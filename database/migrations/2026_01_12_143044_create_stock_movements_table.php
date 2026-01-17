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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('movement_type', ['inbound', 'outbound', 'transfer', 'adjustment']);
            $table->integer('quantity');
            $table->integer('previous_quantity');
            $table->integer('new_quantity');
            $table->string('location')->nullable();
            $table->string('supplier')->nullable();
            $table->date('date_received')->nullable();
            $table->text('notes')->nullable();
            $table->string('reference')->nullable(); // e.g., expense_id, transfer_id, etc.
            $table->timestamps();

            $table->index(['inventory_id', 'created_at']);
            $table->index(['movement_type']);
            $table->index(['location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
