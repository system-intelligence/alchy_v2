<?php

namespace App\Livewire\Developer;

use Livewire\Component;
use App\Models\User;
use App\Models\History;
use Illuminate\Support\Facades\Hash;

class UserManagement extends Component
{
    public $users;
    public $onlineUsers = [];
    public $showModal = false;
    public $editing = false;
    public $userId;
    public $name, $email, $role, $password, $password_confirmation;
    public $showDeleteModal = false;
    public $deletePassword = '';
    public $deletingUserId = null;

    public function mount()
    {
        $this->loadUsers();
        $this->loadOnlineUsers();
    }

    public function loadUsers()
    {
        $this->users = User::latest()->get();
    }

    public function loadOnlineUsers()
    {
        // Get users who have been active in the last 2 minutes
        $this->onlineUsers = User::where('last_seen', '>=', now()->subMinutes(2))
            ->pluck('id')
            ->toArray();
    }

    #[\Livewire\Attributes\On('user-status-changed')]
    public function updateUserStatus($userId, $isOnline)
    {
        if ($isOnline) {
            if (!in_array($userId, $this->onlineUsers)) {
                $this->onlineUsers[] = $userId;
            }
        } else {
            $this->onlineUsers = array_filter($this->onlineUsers, fn($id) => $id != $userId);
        }
    }

    public function openModal($id = null)
    {
        $this->ensureDeveloper();

        $this->resetForm();
        if ($id) {
            $user = User::find($id);
            if (!$user) {
                session()->flash('message', 'User not found.');
                return;
            }
            $this->editing = true;
            $this->userId = $id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->role;
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function openDeleteModal($id)
    {
        $this->ensureDeveloper();
        $this->deletingUserId = $id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deletePassword = '';
        $this->deletingUserId = null;
    }

    public function resetForm()
    {
        $this->editing = false;
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->role = 'user';
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function save()
    {
        $this->ensureDeveloper();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:developer,system_admin,user',
        ];

        if (!$this->editing) {
            $rules['email'] .= '|unique:users';
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['email'] .= '|unique:users,email,' . $this->userId;
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        $this->validate($rules);

        if ($this->editing) {
            $user = User::find($this->userId);
            if (!$user) {
                session()->flash('message', 'User not found.');
                return;
            }
            $oldValues = [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ];
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
            ]);
            if ($this->password) {
                $user->update(['password' => Hash::make($this->password)]);
            }
            History::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model' => 'User',
                'model_id' => $user->id,
                'old_values' => $oldValues,
                'changes' => [
                    'name' => $this->name,
                    'email' => $this->email,
                    'role' => $this->role,
                    'password_changed' => $this->password ? true : false,
                ],
            ]);
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'role' => $this->role,
                'email_verified_at' => now(),
            ]);
            History::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model' => 'user',
                'model_id' => $user->id,
                'changes' => [
                    'name' => $this->name,
                    'email' => $this->email,
                    'role' => $this->role,
                ],
            ]);
        }

        $this->loadUsers();
        session()->flash('message', $this->editing ? 'User updated successfully.' : 'User created successfully.');
        $this->closeModal();
    }

    public function delete()
    {
        $this->ensureDeveloper();

        $this->validate([
            'deletePassword' => 'required',
        ]);

        // Verify password
        if (!Hash::check($this->deletePassword, auth()->user()->password)) {
            $this->addError('deletePassword', 'Incorrect password.');
            return;
        }

        $user = User::find($this->deletingUserId);
        if (!$user) {
            session()->flash('message', 'User not found.');
            $this->closeDeleteModal();
            return;
        }

        // Prevent deleting self
        if ($user->id === auth()->id()) {
            session()->flash('message', 'You cannot delete your own account.');
            $this->closeDeleteModal();
            return;
        }

        // Capture all user details before deletion
        $userDetails = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'has_avatar' => $user->hasImageBlob(),
            'last_seen' => $user->last_seen?->format('Y-m-d H:i:s'),
            'email_verified' => $user->email_verified_at ? true : false,
            'deleted' => true
        ];

        $user->delete();
        History::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model' => 'user',
            'model_id' => $this->deletingUserId,
            'changes' => $userDetails,
        ]);

        $this->loadUsers();
        session()->flash('message', 'User deleted successfully.');
        $this->closeDeleteModal();
    }

    protected function ensureDeveloper(): void
    {
        if (!auth()->check() || !auth()->user()->isDeveloper()) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.developer.user-management');
    }
}
