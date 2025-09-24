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
    public $brand, $description, $category, $quantity, $min_stock_level;

    // Filters
    public $search = '';
    public $statusFilter = '';

    // Bulk operations
    public $selectAll = false;
    public $selectedItems = [];

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
            $this->min_stock_level = $inventory->min_stock_level;
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
        $this->min_stock_level = 5;
    }

    public function save()
    {
        $this->ensureAdmin();

        $this->validate([
            'brand' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
        ]);

        $wasEditing = $this->editing;

        // Auto-set status based on quantity and min_stock_level
        $status = $this->quantity <= 0 ? 'out_of_stock' : ($this->quantity <= $this->min_stock_level ? 'critical' : 'normal');

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
                'min_stock_level' => $this->min_stock_level,
                'status' => $status,
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
                    'min_stock_level' => $this->min_stock_level,
                    'status' => $status
                ],
            ]);
        } else {
            $inventory = Inventory::create([
                'brand' => $this->brand,
                'description' => $this->description,
                'category' => $this->category,
                'quantity' => $this->quantity,
                'min_stock_level' => $this->min_stock_level,
                'status' => $status,
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
                    'min_stock_level' => $this->min_stock_level,
                    'status' => $status
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
        $this->resetSelection();
    }

    public function filterByStatus($status)
    {
        $this->statusFilter = $status;
        $this->loadInventories();
        $this->resetSelection();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedItems = $this->inventories->pluck('id')->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    public function updatedSelectedItems()
    {
        $this->selectAll = count($this->selectedItems) === $this->inventories->count();
    }

    public function bulkDelete()
    {
        $this->ensureAdmin();

        if (empty($this->selectedItems)) {
            session()->flash('message', 'No items selected for deletion.');
            return;
        }

        $inventories = Inventory::whereIn('id', $this->selectedItems)->get();

        foreach ($inventories as $inventory) {
            History::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model' => 'inventory',
                'model_id' => $inventory->id,
                'changes' => ['deleted' => true, 'bulk_delete' => true],
            ]);
            $inventory->delete();
        }

        $this->loadInventories();
        $this->resetSelection();
        session()->flash('message', count($this->selectedItems) . ' items deleted successfully.');
    }

    protected function resetSelection()
    {
        $this->selectAll = false;
        $this->selectedItems = [];
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
