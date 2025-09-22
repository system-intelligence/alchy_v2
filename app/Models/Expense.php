<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['client_id', 'inventory_id', 'quantity_used', 'cost_per_unit', 'total_cost', 'released_at'];

    protected $casts = [
        'released_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
