<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = ['brand', 'description', 'category', 'quantity', 'status', 'min_stock_level'];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
