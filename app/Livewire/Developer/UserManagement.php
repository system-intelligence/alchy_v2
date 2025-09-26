<?php

namespace App\Livewire\Developer;

use Livewire\Component;
use App\Models\User;
use App\Models\History;
use Illuminate\Support\Facades\Hash;

class UserManagement extends Component
{
    public $users;
    public $showModal = false;
    public $editing = false;
    public $userId;
    public $name, $email, $role, $password, $password_confirmation;

    public function mount()
    {
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $this->users = User::latest()->get();
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
                'model' => 'user',
                'model_id' => $user->id,
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

    public function delete($id)
    {
        $this->ensureDeveloper();

        $user = User::find($id);
        if (!$user) {
            session()->flash('message', 'User not found.');
            return;
        }

        // Prevent deleting self
        if ($user->id === auth()->id()) {
            session()->flash('message', 'You cannot delete your own account.');
            return;
        }

        $user->delete();
        History::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model' => 'user',
            'model_id' => $id,
            'changes' => ['deleted' => true],
        ]);

        $this->loadUsers();
        session()->flash('message', 'User deleted successfully.');
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
