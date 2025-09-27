<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'branch', 'start_date', 'end_date', 'job_type', 'status', 'image_blob', 'image_mime_type', 'image_filename'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    // Auto caps lock mutators for text fields
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = is_string($value) ? strtoupper($value) : $value;
    }

    public function setBranchAttribute($value)
    {
        $this->attributes['branch'] = is_string($value) ? strtoupper($value) : $value;
    }

    public function setJobTypeAttribute($value)
    {
        // Only allow valid enum values for job_type
        $allowedValues = ['service', 'installation'];
        if (is_string($value) && in_array(strtolower($value), $allowedValues)) {
            $this->attributes['job_type'] = strtolower($value); // Keep as lowercase to match enum
        } else {
            $this->attributes['job_type'] = $value; // Keep original value if not in allowed list
        }
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
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

    public function getLogoUrlAttribute()
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
