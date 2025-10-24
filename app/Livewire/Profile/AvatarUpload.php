<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;

class AvatarUpload extends Component
{
    use WithFileUploads;

    public $photo;
    public $user;
    public $isDragOver = false;

    public function mount()
    {
        $this->user = auth()->user();
    }

    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:1024', // 1MB Max
        ]);
    }

    public function save()
    {
        $this->validate([
            'photo' => 'image|max:1024', // 1MB Max
        ]);

        // Store avatar as BLOB in database
        $result = $this->user->storeAvatarAsBlob($this->photo->getRealPath());

        if ($result) {
            session()->flash('message', 'Avatar updated successfully.');
            $this->photo = null;
            $this->user = $this->user->fresh(); // Reload user data
        } else {
            session()->flash('error', 'Failed to save avatar.');
        }
    }

    public function removeAvatar()
    {
        $this->user->deleteAvatarBlob();
        session()->flash('message', 'Avatar removed successfully.');
        $this->user = $this->user->fresh(); // Reload user data
    }

    public function setDragOver($value)
    {
        $this->isDragOver = $value;
    }

    public function handleDrop($event)
    {
        $this->isDragOver = false;

        if (isset($event['files']) && count($event['files']) > 0) {
            $this->photo = $event['files'][0];
            $this->updatedPhoto();
        }
    }

    public function cancelUpload()
    {
        $this->photo = null;
        $this->isDragOver = false;
    }

    public function render()
    {
        return view('livewire.profile.avatar-upload');
    }
}
