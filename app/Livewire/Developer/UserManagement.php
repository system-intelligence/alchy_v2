<?php

namespace App\Livewire\Developer;

use Livewire\Component;
use App\Models\User;

class UserManagement extends Component
{
    public $users;

    public function mount()
    {
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $this->users = User::latest()->get();
    }

    public function render()
    {
        return view('livewire.developer.user-management');
    }
}
