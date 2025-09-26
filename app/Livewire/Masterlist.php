<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Inventory;
use App\Models\History;
use App\Models\Client;
use App\Models\Expense;

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

    // Record Release modal
    public $showReleaseModal = false;
    public $showDuplicateModal = false;
    public $duplicateMessage = '';
    public $clients;
    public $inventoryOptions = [];
    public $client_id = null;
    public $releaseItems = [];
    public $selectedInventoryId = null;

    public function mount()
    {
        $this->loadInventories();
        $this->clients = Client::with('expenses')->get();
        $this->inventoryOptions = Inventory::orderBy('brand')->orderBy('description')->get();
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
        $this->showReleaseModal = false;
        $this->showDuplicateModal = false;
        $this->resetForm();
        $this->resetReleaseForm();
    }

    public function closeDuplicateModal()
    {
        $this->showDuplicateModal = false;
        $this->duplicateMessage = '';
    }

    public function openReleaseModal()
    {
        $this->ensureAdmin();
        $this->resetReleaseForm();
        $this->showReleaseModal = true;
    }

    public function addReleaseItem()
    {
        $this->validate([
            'selectedInventoryId' => 'required|exists:inventories,id',
        ]);

        $inventory = Inventory::find($this->selectedInventoryId);
        if (!$inventory) {
            session()->flash('message', 'Selected inventory not found.');
            return;
        }

        // Check if already added
        if (collect($this->releaseItems)->pluck('inventory_id')->contains($this->selectedInventoryId)) {
            $this->showDuplicateModal = true;
            $this->duplicateMessage = 'Oops, you already selected the same material. You may just edit the details for an update.';
            return;
        }

        $this->releaseItems[] = [
            'inventory_id' => $this->selectedInventoryId,
            'quantity_used' => 1,
            'cost_per_unit' => 0,
            'inventory' => $inventory,
        ];

        $this->selectedInventoryId = null;
    }

    public function removeReleaseItem($index)
    {
        unset($this->releaseItems[$index]);
        $this->releaseItems = array_values($this->releaseItems);
    }

    public function updatedReleaseItems($value, $key)
    {
        // Handle updates to nested array
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = $parts[0];
            $field = $parts[1];
            if (isset($this->releaseItems[$index])) {
                $this->releaseItems[$index][$field] = $value;
            }
        }
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

    public function resetReleaseForm()
    {
        $this->client_id = null;
        $this->releaseItems = [];
        $this->selectedInventoryId = null;
    }

    public function saveRelease()
    {
        $this->ensureAdmin();

        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'releaseItems' => 'required|array|min:1',
            'releaseItems.*.inventory_id' => 'required|exists:inventories,id',
            'releaseItems.*.quantity_used' => 'required|integer|min:1',
            'releaseItems.*.cost_per_unit' => 'required|numeric|min:0',
        ]);

        $client = Client::find($this->client_id);
        if (!$client) {
            session()->flash('message', 'Selected client not found.');
            return;
        }

        $createdExpenses = [];
        $updatedInventories = [];

        foreach ($this->releaseItems as $item) {
            $inventory = Inventory::find($item['inventory_id']);
            if (!$inventory) {
                session()->flash('message', 'One of the selected inventories not found.');
                return;
            }

            if ($item['quantity_used'] > $inventory->quantity) {
                $this->addError('releaseItems', 'Quantity for ' . $inventory->brand . ' exceeds available stock (' . $inventory->quantity . ').');
                return;
            }

            $total = round($item['quantity_used'] * (float)$item['cost_per_unit'], 2);

            $expense = Expense::create([
                'client_id' => $this->client_id,
                'inventory_id' => $item['inventory_id'],
                'quantity_used' => $item['quantity_used'],
                'cost_per_unit' => $item['cost_per_unit'],
                'total_cost' => $total,
                'released_at' => now(),
            ]);

            $createdExpenses[] = $expense;

            // Update inventory stock and status
            $inventory->quantity = $inventory->quantity - $item['quantity_used'];
            if ($inventory->quantity <= 0) {
                $inventory->quantity = 0;
                $inventory->status = 'out_of_stock';
            } elseif ($inventory->quantity <= $inventory->min_stock_level) {
                $inventory->status = 'critical';
            } else {
                $inventory->status = 'normal';
            }
            $inventory->save();
            $updatedInventories[] = $inventory;

            // Log history for expense
            History::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model' => 'expense',
                'model_id' => $expense->id,
                'changes' => [
                    'client_id' => $expense->client_id,
                    'inventory_id' => $expense->inventory_id,
                    'quantity_used' => $expense->quantity_used,
                    'total_cost' => $expense->total_cost,
                ],
            ]);
        }

        // Log history for inventories
        foreach ($updatedInventories as $inventory) {
            History::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model' => 'inventory',
                'model_id' => $inventory->id,
                'changes' => [
                    'quantity' => $inventory->quantity,
                    'status' => $inventory->status,
                ],
            ]);
        }

        // Refresh lists
        $this->loadInventories();
        $this->clients = Client::with('expenses')->get();

        $this->closeModal();
        session()->flash('message', count($createdExpenses) . ' release(s) recorded and inventory updated.');
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

    public function performSearch()
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
