<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Inventory;

class ImageUpload extends Component
{
    use WithFileUploads;

    public $image;
    public $inventoryId;
    public $showUpload = false;
    public $hasImage = false;

    protected $rules = [
        'image' => 'required|image|mimes:png,jpg,jpeg|max:5120', // 5MB max, accept PNG and JPG
    ];

    public function mount($inventoryId = null)
    {
        $this->inventoryId = $inventoryId;
        $this->hasImage = Inventory::find($this->inventoryId)?->hasImageBlob() ?? false;
    }

    public function saveImage()
    {
        $this->validate();

        $inventory = Inventory::find($this->inventoryId);

        if ($inventory && $this->image) {
            try {
                // Store temporary uploaded file
                $tempPath = $this->image->store('', 'temp');
                $fullTempPath = storage_path('app/temp/' . $tempPath);

                // Debug: Check if file exists
                if (!file_exists($fullTempPath)) {
                    session()->flash('error', 'Temporary file not found: ' . $fullTempPath);
                    return;
                }

                // Debug: Check file size
                $fileSize = filesize($fullTempPath);
                if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
                    session()->flash('error', 'File too large: ' . $fileSize . ' bytes');
                    return;
                }

                // Store as BLOB in database
                $result = $inventory->storeImageAsBlob($fullTempPath);

                if (!$result) {
                    session()->flash('error', 'Failed to save image to database');
                    return;
                }

                // Clean up temp file
                if (file_exists($fullTempPath)) {
                    unlink($fullTempPath);
                }

                session()->flash('message', 'Image uploaded successfully! Size: ' . $fileSize . ' bytes');
                $this->showUpload = false;
                $this->image = null;

            } catch (\Exception $e) {
                session()->flash('error', 'Upload failed: ' . $e->getMessage());
            }
        } else {
            session()->flash('error', 'Inventory item not found or no image selected');
        }
    }

    public function render()
    {
        return view('livewire.image-upload');
    }
}
