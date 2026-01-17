<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Client Model
 *
 * Represents a client entity with project information and logo management.
 *
 * @property int $id
 * @property string $name
 * @property string $branch
 * @property \Carbon\Carbon|null $start_date
 * @property \Carbon\Carbon|null $end_date
 * @property string|null $job_type
 * @property string $status
 * @property string|null $image_blob
 * @property string|null $image_mime_type
 * @property string|null $image_filename
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Expense> $expenses
 */
class Client extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'branch',
        'client_type',
        'start_date',
        'end_date',
        'job_type',
        'status',
        'image_blob',
        'image_mime_type',
        'image_filename'
    ];
    
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ]; 
    }

    /**
     * Valid client types.
     */
    public const CLIENT_TYPES = ['banking', 'non_banking'];

    /**
     * Valid job types for clients.
     */
    public const JOB_TYPES = ['service', 'installation'];

    /**
     * Valid status values for clients.
     */
    public const STATUSES = ['in_progress', 'settled', 'cancelled'];

    /**
     * Set the name attribute with auto uppercase conversion.
     *
     * @param string|null $value
     */
    public function setNameAttribute(?string $value): void
    {
        $this->attributes['name'] = is_string($value) ? strtoupper($value) : $value;
    }

    /**
     * Set the branch attribute with auto uppercase conversion.
     *
     * @param string|null $value
     */
    public function setBranchAttribute(?string $value): void
    {
        $this->attributes['branch'] = is_string($value) ? strtoupper($value) : $value;
    }

    /**
     * Set the job type attribute with validation.
     *
     * @param string|null $value
     */
    public function setJobTypeAttribute(?string $value): void
    {
        if (is_string($value) && in_array(strtolower($value), self::JOB_TYPES)) {
            $this->attributes['job_type'] = strtolower($value);
        } else {                
            $this->attributes['job_type'] = $value;
        }
    }

    /**
     * Get the expenses relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Expense>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the projects relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Project>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Store an image as base64 blob.
     *
     * @param string $imagePath
     * @return bool
     */
    public function storeImageAsBlob(string $imagePath): bool
     {
         if (!file_exists($imagePath)) {
             \Log::error('Client image upload failed: file does not exist', ['path' => $imagePath]);
             return false;
         }

         $imageData = file_get_contents($imagePath);
         if ($imageData === false) {
             \Log::error('Client image upload failed: could not read file', ['path' => $imagePath]);
             return false;
         }

         $mimeType = mime_content_type($imagePath);
         if (!$mimeType || !str_starts_with($mimeType, 'image/')) {
             \Log::error('Client image upload failed: invalid mime type', ['path' => $imagePath, 'mime' => $mimeType]);
             return false;
         }

         $this->image_blob = base64_encode($imageData);
         $this->image_mime_type = $mimeType;
         $this->image_filename = basename($imagePath);

         if (!$this->save()) {
             \Log::error('Client image upload failed: could not save to database', ['client_id' => $this->id]);
             return false;
         }

         return true;
     }

    /**
     * Get the image blob as data URL.
     *
     * @param string|null $value
     * @return string|null
     */
    public function getImageBlobAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $mimeType = $this->image_mime_type ?: 'image/jpeg';
        return 'data:' . $mimeType . ';base64,' . $value;
    }

    /**
     * Get the logo URL (blob or fallback).
     *
     * @return string
     */
    public function getLogoUrlAttribute(): string
    {
        return $this->image_blob ?: asset('images/no-image.png');
    }

    /**
     * Check if the client has an image blob.
     *
     * @return bool
     */
    public function hasImageBlob(): bool
    {
        return !empty($this->image_blob);
    }

    /**
     * Delete the image blob.
     *
     * @return bool
     */
    public function deleteImageBlob(): bool
    {
        $this->image_blob = null;
        $this->image_mime_type = null;
        $this->image_filename = null;
        return $this->save();
    }

    /**
     * Get the total expenses for this client.
     *
     * @return float
     */
    public function getTotalExpensesAttribute(): float
    {
        return $this->expenses->sum('total_cost');
    }

    /**
     * Count active projects linked with the client.
     */
    public function getActiveProjectsCountAttribute(): int
    {
        return $this->projects->filter(fn (Project $project) => $project->isActive())->count();
    }

    /**
     * Check if the client project is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the client project is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'settled';
    }

    /**
     * Get detailed change information for history logging.
     *
     * @param array $oldValues
     * @param array $newValues
     * @return array
     */
    public function getHistoryChangeDetails(array $oldValues = [], array $newValues = []): array
    {
        $changes = [];

        // Compare basic fields
        $fieldsToCheck = ['name', 'branch', 'client_type', 'status'];

        foreach ($fieldsToCheck as $field) {
            if (!array_key_exists($field, $newValues)) {
                continue; // Only check fields that are being updated
            }

            $oldValue = $oldValues[$field] ?? null;
            $newValue = $newValues[$field];

            if ($oldValue !== $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                    'field_name' => ucfirst(str_replace('_', ' ', $field)),
                ];
            }
        }

        // Handle logo changes
        $logoChanged = isset($newValues['logo_updated']) && $newValues['logo_updated'];
        if ($logoChanged) {
            $changes['logo'] = [
                'old' => $this->hasImageBlob() ? 'Has logo' : 'No logo',
                'new' => 'Logo updated',
                'field_name' => 'Logo',
            ];
        }

        return $changes;
    }

    /**
     * Get comprehensive details for history logging on deletion.
     *
     * @return array
     */
    public function getHistoryDeletionDetails(): array
    {
        return [
            'name' => $this->name,
            'branch' => $this->branch,
            'client_type' => $this->client_type,
            'status' => $this->status,
            'has_logo' => $this->hasImageBlob(),
            'total_expenses' => $this->expenses()->sum('total_cost'),
            'expense_count' => $this->expenses()->count(),
            'project_count' => $this->projects()->count(),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'job_type' => $this->job_type,
            'deleted' => true,
        ];
    }

    /**
     * Get formatted change summary for notifications.
     *
     * @param array $changes
     * @return string
     */
    public function getChangeSummary(array $changes): string
    {
        if (empty($changes)) {
            return 'No changes detected';
        }

        $changeDescriptions = [];
        foreach ($changes as $field => $changeData) {
            $fieldName = $changeData['field_name'] ?? ucfirst(str_replace('_', ' ', $field));
            $oldValue = $changeData['old'] ?? 'N/A';
            $newValue = $changeData['new'] ?? 'N/A';

            $changeDescriptions[] = "{$fieldName}: {$oldValue} → {$newValue}";
        }

        return implode(', ', $changeDescriptions);
    }
}
