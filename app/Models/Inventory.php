<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = ['brand', 'description', 'category', 'quantity', 'status', 'min_stock_level', 'image_blob', 'image_mime_type', 'image_filename'];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    // Auto caps lock mutators for text fields
    public function setBrandAttribute($value)
    {
        $this->attributes['brand'] = is_string($value) ? strtoupper($value) : $value;
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = is_string($value) ? strtoupper($value) : $value;
    }

    public function setCategoryAttribute($value)
    {
        $this->attributes['category'] = is_string($value) ? strtoupper($value) : $value;
    }

    public function setStatusAttribute($value)
    {
        // Only allow valid enum values for status
        $allowedValues = ['normal', 'critical', 'out_of_stock'];
        if (is_string($value) && in_array(strtolower($value), $allowedValues)) {
            $this->attributes['status'] = strtolower($value); // Keep as lowercase to match enum
        } else {
            $this->attributes['status'] = $value; // Keep original value if not in allowed list
        }
    }

    // BLOB Image Methods
    public function storeImageAsBlob($imagePath)
    {
        if (!file_exists($imagePath)) {
            return false;
        }

        $imageData = file_get_contents($imagePath);
        $base64Image = base64_encode($imageData);

        $this->image_blob = $base64Image;
        $this->image_mime_type = mime_content_type($imagePath);
        $this->image_filename = basename($imagePath);

        return $this->save();
    }

    public function getImageBlobAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // Ensure we have MIME type
        $mimeType = $this->image_mime_type ?: 'image/jpeg';

        return 'data:' . $mimeType . ';base64,' . $value;
    }

    public function getImageUrlAttribute()
    {
        if ($this->image_blob) {
            return $this->image_blob;
        }

        // Fallback to no-image placeholder
        return asset('images/no-image.png');
    }

    public function hasImageBlob()
    {
        return !empty($this->image_blob);
    }

    public function deleteImageBlob()
    {
        $this->image_blob = null;
        $this->image_mime_type = null;
        $this->image_filename = null;
        return $this->save();
    }
}
