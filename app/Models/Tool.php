<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    protected $fillable = [
        'image_blob',
        'image_mime_type',
        'image_filename',
        'quantity',
        'brand',
        'model',
        'description',
        'ownership_type',
        'released_to',
        'release_date',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
        ];
    }

    /**
     * Set the brand attribute with auto uppercase conversion.
     */
    public function setBrandAttribute(?string $value): void
    {
        $this->attributes['brand'] = is_string($value) ? strtoupper($value) : $value;
    }

    /**
     * Set the model attribute with auto uppercase conversion.
     */
    public function setModelAttribute(?string $value): void
    {
        $this->attributes['model'] = is_string($value) ? strtoupper($value) : $value;
    }

    /**
     * Set the description attribute with auto uppercase conversion.
     */
    public function setDescriptionAttribute(?string $value): void
    {
        $this->attributes['description'] = is_string($value) ? strtoupper($value) : $value;
    }

    /**
     * Get the image blob as data URL.
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
     * Get the image URL (blob or fallback).
     */
    public function getImageUrlAttribute(): string
    {
        return $this->image_blob ?: asset('images/no-image.png');
    }

    /**
     * Check if the tool has an image blob.
     */
    public function hasImageBlob(): bool
    {
        return !empty($this->image_blob);
    }
}
