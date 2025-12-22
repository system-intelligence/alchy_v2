<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\History;

class ReceiptVerification extends Model
{
    protected $fillable = [
        'project_id',
        'verification_hash',
        'receipt_data',
        'generated_at',
        'verified_count',
        'last_verified_at',
        'generated_by',
    ];

    protected $casts = [
        'receipt_data' => 'array',
        'generated_at' => 'datetime',
        'last_verified_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function incrementVerification(): void
    {
        $this->increment('verified_count');
        $this->update(['last_verified_at' => now()]);
    }
}
