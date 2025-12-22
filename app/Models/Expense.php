<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Expense Model
 *
 * Represents an expense record for materials released to clients.
 *
 * @property int $id
 * @property int $client_id
 * @property int $inventory_id
 * @property int $quantity_used
 * @property float $cost_per_unit
 * @property float $total_cost
 * @property \Carbon\Carbon $released_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Client $client
 * @property-read \App\Models\Inventory $inventory
 */
class Expense extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'inventory_id',
        'project_id',
        'quantity_used',
        'cost_per_unit',
        'total_cost',
        'released_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'released_at' => 'datetime',
            'cost_per_unit' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    /**
     * Get the client relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Client, \App\Models\Expense>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the inventory relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Inventory, \App\Models\Expense>
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Get the project relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Project, \App\Models\Expense>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Calculate and set the total cost based on quantity and cost per unit.
     */
    public function calculateTotalCost(): void
    {
        $this->total_cost = round($this->quantity_used * $this->cost_per_unit, 2);
    }

    /**
     * Check if the expense was released within the last N days.
     */
    public function wasReleasedRecently(int $days = 7): bool
    {
        return $this->released_at->isAfter(now()->subDays($days));
    }
}
