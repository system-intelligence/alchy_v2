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
            return false;
        }

        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            return false;
        }

        $this->image_blob = base64_encode($imageData);
        $this->image_mime_type = mime_content_type($imagePath);
        $this->image_filename = basename($imagePath);

        return $this->save();
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
}
