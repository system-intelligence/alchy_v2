<?php

namespace App\Models;

use App\Enums\InventoryStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Inventory Model
 *
 * Represents inventory items with stock management and image storage.
 *
 * @property int $id
 * @property string $brand
 * @property string $description
 * @property string $category
 * @property int $quantity
 * @property \App\Enums\InventoryStatus|string $status
 * @property int $min_stock_level
 * @property string|null $image_blob
 * @property string|null $image_mime_type
 * @property string|null $image_filename
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Expense> $expenses
 */
class Inventory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'brand',
        'description',
        'category',
        'quantity',
        'status',
        'min_stock_level',
        'image_blob',
        'image_mime_type',
        'image_filename'
    ];

    /**
     * Valid status values for inventory items.
     */
    public const STATUSES = [
        InventoryStatus::NORMAL->value,
        InventoryStatus::CRITICAL->value,
        InventoryStatus::OUT_OF_STOCK->value,
    ];

    /**
     * Valid category values for inventory items.
     */
    public const CATEGORIES = ['Bodega Room', 'Alchy Room'];

    /**
     * Attribute casting configuration.
     *
     * @var array<string, mixed>
     */
    protected $casts = [
        'status' => InventoryStatus::class,
    ];

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
     * Set the brand attribute with auto uppercase conversion.
     *
     * @param string|null $value
     */
    public function setBrandAttribute(?string $value): void
    {
        $this->attributes['brand'] = is_string($value) ? strtoupper($value) : $value;
    }

    /**
     * Set the description attribute with auto uppercase conversion.
     *
     * @param string|null $value
     */
    public function setDescriptionAttribute(?string $value): void
    {
        $this->attributes['description'] = is_string($value) ? strtoupper($value) : $value;
    }

    /**
     * Set the category attribute with auto uppercase conversion.
     *
     * @param string|null $value
     */
    public function setCategoryAttribute(?string $value): void
    {
        $this->attributes['category'] = is_string($value) ? strtoupper($value) : $value;
    }

    /**
     * Set the status attribute with validation.
     *
     * @param \App\Enums\InventoryStatus|string|null $value
     */
    public function setStatusAttribute(InventoryStatus|string|null $value): void
    {
        if ($value instanceof InventoryStatus) {
            $this->attributes['status'] = $value->value;

            return;
        }

        if (is_string($value)) {
            $normalized = strtolower($value);
            $status = InventoryStatus::tryFrom($normalized);

            if ($status instanceof InventoryStatus) {
                $this->attributes['status'] = $status->value;

                return;
            }

            $this->attributes['status'] = $normalized;

            return;
        }

        $this->attributes['status'] = $value;
    }

    /**
     * Scope by status enum value.
     */
    public function scopeStatus(Builder $query, InventoryStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    /**
     * Scope inventory with critical status.
     */
    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('status', InventoryStatus::CRITICAL->value);
    }

    /**
     * Scope inventory that is out of stock.
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('status', InventoryStatus::OUT_OF_STOCK->value);
    }

    /**
     * Scope inventory requiring attention.
     */
    public function scopeAttention(Builder $query): Builder
    {
        return $query->whereIn('status', InventoryStatus::attentionValues());
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
     * Get the image URL (blob or fallback).
     *
     * @return string
     */
    public function getImageUrlAttribute(): string
    {
        return $this->image_blob ?: asset('images/no-image.png');
    }

    /**
     * Check if the inventory item has an image blob.
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
     * Check if the inventory item is in stock.
     *
     * @return bool
     */
    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Check if the inventory item is low on stock.
     *
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->quantity > 0 && $this->quantity <= $this->min_stock_level;
    }

    /**
     * Check if the inventory item is out of stock.
     *
     * @return bool
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Update the status based on current quantity.
     *
     * @return void
     */
    public function updateStatus(): void
    {
        if ($this->isOutOfStock()) {
            $this->status = InventoryStatus::OUT_OF_STOCK;
        } elseif ($this->isLowStock()) {
            $this->status = InventoryStatus::CRITICAL;
        } else {
            $this->status = InventoryStatus::NORMAL;
        }
        $this->save();
    }

    /**
     * Get the available quantity for release.
     *
     * @return int
     */
    public function getAvailableQuantity(): int
    {
        return max(0, $this->quantity);
    }
}
