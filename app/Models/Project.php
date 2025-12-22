<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Project Model
 *
 * Represents a client project used to scope expenses, receipts, and warranty tracking.
 */
class Project extends Model
{
    /**
     * Status options for a project lifecycle.
     */
    public const STATUSES = [
        'planning',
        'in_progress',
        'completed',
        'warranty',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'name',
        'reference_code',
        'job_type',
        'status',
        'start_date',
        'target_date',
        'warranty_until',
        'notes',
    ];

    /**
     * Attribute casting configuration.
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_date' => 'date',
            'warranty_until' => 'date',
        ];
    }

    /**
     * Project belongs to a client.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Project has many expenses.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Project has many notes.
     */
    public function projectNotes(): HasMany
    {
        return $this->hasMany(ProjectNote::class)->orderBy('created_at', 'desc');
    }

    /**
     * Determine if the project is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['planning', 'in_progress'], true);
    }

    /**
     * Determine if the project is still under warranty.
     */
    public function isUnderWarranty(): bool
    {
        return $this->warranty_until && $this->warranty_until->isFuture();
    }
}
    