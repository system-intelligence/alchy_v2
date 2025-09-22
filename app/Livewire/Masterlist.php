<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Inventory;
use App\Models\History;

class Masterlist extends Component
{
    public $inventories;
    public $showModal = false;
    public $editing = false;
    public $inventoryId;
    public $brand, $description, $category, $quantity, $status;

    // Filters
    public $search = '';
    public $statusFilter = '';

    public function mount()
    {
        $this->loadInventories();
    }

    public function loadInventories()
    {
        $query = Inventory::query();

        if (trim($this->search) !== '') {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('brand', 'like', $s)
                    ->orWhere('description', 'like', $s)
                    ->orWhere('category', 'like', $s);
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        $this->inventories = $query->orderBy('created_at', 'desc')->get();
    }

    public function openModal($id = null)
    {
        $this->ensureAdmin();

        $this->resetForm();
        if ($id) {
            $inventory = Inventory::find($id);
            if (!$inventory) {
                session()->flash('message', 'Inventory not found.');
                return;
            }
            $this->editing = true;
            $this->inventoryId = $id;
            $this->brand = $inventory->brand;
            $this->description = $inventory->description;
            $this->category = $inventory->category;
            $this->quantity = $inventory->quantity;
            $this->status = $inventory->status;
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
        $this->inventoryId = null;
        $this->brand = '';
        $this->description = '';
        $this->category = '';
        $this->quantity = 0;
        $this->status = 'normal';
    }

    public function save()
    {
        $this->ensureAdmin();

        $this->validate([
            'brand' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'status' => 'required|in:normal,critical,out_of_stock',
        ]);

        $wasEditing = $this->editing;

        if ($this->editing) {
            $inventory = Inventory::find($this->inventoryId);
            if (!$inventory) {
                session()->flash('message', 'Inventory not found.');
                return;
            }
            $inventory->update([
                'brand' => $this->brand,
                'description' => $this->description,
                'category' => $this->category,
                'quantity' => $this->quantity,
                'status' => $this->status,
            ]);
            History::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model' => 'inventory',
                'model_id' => $inventory->id,
                'changes' => [
                    'brand' => $this->brand,
                    'category' => $this->category,
                    'quantity' => $this->quantity,
                    'status' => $this->status
                ],
            ]);
        } else {
            $inventory = Inventory::create([
                'brand' => $this->brand,
                'description' => $this->description,
                'category' => $this->category,
                'quantity' => $this->quantity,
                'status' => $this->status,
            ]);
            History::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model' => 'inventory',
                'model_id' => $inventory->id,
                'changes' => [
                    'brand' => $this->brand,
                    'category' => $this->category,
                    'quantity' => $this->quantity,
                    'status' => $this->status
                ],
            ]);
        }

        $this->loadInventories();
        session()->flash('message', $wasEditing ? 'Inventory updated successfully.' : 'Inventory created successfully.');
        $this->closeModal();
    }

    public function delete($id)
    {
        $this->ensureAdmin();

        $inventory = Inventory::find($id);
        if (!$inventory) {
            session()->flash('message', 'Inventory not found.');
            return;
        }
        $inventory->delete();
        History::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model' => 'inventory',
            'model_id' => $id,
            'changes' => ['deleted' => true],
        ]);
        $this->loadInventories();
        session()->flash('message', 'Inventory deleted successfully.');
    }

    public function updatedSearch()
    {
        $this->loadInventories();
    }

    public function updatedStatusFilter()
    {
        $this->loadInventories();
    }

    protected function ensureAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isSystemAdmin()) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.masterlist');
    }
}
