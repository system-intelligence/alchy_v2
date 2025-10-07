<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * History Model
 *
 * Tracks all user actions and changes in the system for audit purposes.
 *
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property string $model
 * @property int $model_id
 * @property array|null $changes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \App\Models\User $user
 */
class History extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'changes'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    /**
     * Valid action types for history records.
     */
    public const ACTIONS = [
        'create',
        'update',
        'delete',
        'login',
        'logout',
        'view',
        'export'
    ];

    /**
     * Get the user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\History>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a human-readable description of the action.
     *
     * @return string
     */
    public function getActionDescriptionAttribute(): string
    {
        return match ($this->action) {
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'login' => 'Logged in',
            'logout' => 'Logged out',
            'view' => 'Viewed',
            'export' => 'Exported',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get the model name in a readable format.
     *
     * @return string
     */
    public function getModelNameAttribute(): string
    {
        return match ($this->model) {
            'inventory' => 'Inventory Item',
            'client' => 'Client',
            'expense' => 'Expense',
            'user' => 'User',
            default => ucfirst($this->model),
        };
    }

    /**
     * Scope to filter by action type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by model type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByModel($query, string $model)
    {
        return $query->where('model', $model);
    }

    /**
     * Scope to filter by user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
