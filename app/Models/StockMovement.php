<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StockMovement Model
 *
 * Tracks all inventory stock movements including inbound, outbound, transfers, and adjustments.
 */
class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'user_id',
        'movement_type',
        'quantity',
        'cost_per_unit',
        'total_cost',
        'previous_quantity',
        'new_quantity',
        'location',
        'supplier',
        'date_received',
        'notes',
        'reference',
    ];

    protected $casts = [
        'date_received' => 'date',
        'quantity' => 'integer',
        'cost_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'previous_quantity' => 'integer',
        'new_quantity' => 'integer',
    ];

    /**
     * Valid movement types.
     */
    public const MOVEMENT_TYPES = [
        'inbound',
        'outbound',
        'transfer',
        'adjustment',
    ];

    /**
     * Valid locations for stock movements.
     */
    public const LOCATIONS = [
        'Laser Room',
        'Bodega Room',
        'LED room',
        'IT room',
        'Office Material Room',
    ];

    /**
     * Get the inventory relationship.
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Get the user who performed the movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted movement type for display.
     */
    public function getMovementTypeDisplayAttribute(): string
    {
        return match ($this->movement_type) {
            'inbound' => 'Inbound',
            'outbound' => 'Outbound',
            'transfer' => 'Transfer',
            'adjustment' => 'Adjustment',
            default => ucfirst($this->movement_type),
        };
    }

    /**
     * Get the quantity change (positive for inbound, negative for outbound).
     */
    public function getQuantityChangeAttribute(): int
    {
        return $this->quantity; // Now stored as signed value
    }

}
