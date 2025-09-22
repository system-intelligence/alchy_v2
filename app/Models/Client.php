<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'branch'];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
