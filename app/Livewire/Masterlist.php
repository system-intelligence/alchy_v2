<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Inventory;
use App\Models\History;
use App\Models\Client;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection;

/**
 * Masterlist Livewire Component
 *
 * Manages inventory items, releases, and bulk operations.
 * Handles CRUD operations for inventory with role-based access control.
 */
class Masterlist extends Component
{
    use WithFileUploads;

    // Inventory CRUD properties
    public Collection $inventories;
    public bool $showModal = false;
    public bool $editing = false;
    public ?int $inventoryId = null;
    public ?string $brand = '';
    public ?string $description = '';
    public ?string $category = '';
    public ?int $quantity = 0;
    public ?int $min_stock_level = 5;
    public $image;

    // Filters
    public string $search = '';
    public string $statusFilter = '';

    // Bulk operations
    public bool $selectAll = false;
    public array $selectedItems = [];

    // Record Release modal properties
    public bool $showReleaseModal = false;
    public bool $showDuplicateModal = false;
    public string $duplicateMessage = '';
    public Collection $clients;
    public Collection $inventoryOptions;
    public ?int $client_id = null;
    public array $releaseItems = [];
    public ?int $selectedInventoryId = null;

    // Delete Inventory modal properties
    public bool $showDeleteModal = false;
    public ?int $deleteInventoryId = null;
    public string $deletePassword = '';

    // Edit password
    public string $editPassword = '';

    /**
     * Initialize component data.
     */
    public function mount(): void
    {
        $this->loadInventories();
        $this->clients = Client::with('expenses')->get();
        $this->inventoryOptions = Inventory::orderBy('brand')->orderBy('description')->get();
    }

    /**
     * Load inventories with applied filters.
     */
    public function loadInventories(): void
    {
        $query = Inventory::query();

        // Apply search filter
        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('brand', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhere('category', 'like', $searchTerm);
            });
        }

        // Apply status filter
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        $this->inventories = $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Open the inventory modal for creating or editing.
     *
     * @param int|null $id
     */
    public function openModal(?int $id = null): void
    {
        $this->ensureAdmin();
        $this->resetForm();

        if ($id) {
            $inventory = Inventory::find($id);
            if (!$inventory) {
                session()->flash('message', 'Inventory item not found.');
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
        $this->showDeleteModal = false;
        $this->resetForm();
        $this->resetReleaseForm();
        $this->resetDeleteForm();
    }

    public function closeDuplicateModal()
    {
        $this->showDuplicateModal = false;
        $this->duplicateMessage = '';
    }

    public function openReleaseModal()
    {
        if (!auth()->check() || (!auth()->user()->isSystemAdmin() && !auth()->user()->isUser())) {
            abort(403);
        }
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
        $this->image = null;
        $this->editPassword = '';
    }

    public function resetReleaseForm()
    {
        $this->client_id = null;
        $this->releaseItems = [];
        $this->selectedInventoryId = null;
    }

    protected function resetDeleteForm(): void
    {
        $this->deleteInventoryId = null;
        $this->deletePassword = '';
    }

    /**
     * Process inventory release to client.
     * Creates expenses and updates inventory quantities.
     */
    public function saveRelease(): void
    {
        // Check permissions (admin or user can release)
        if (!auth()->check() || (!auth()->user()->isSystemAdmin() && !auth()->user()->isUser())) {
            abort(403, 'Access denied. Insufficient privileges.');
        }

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

        try {
            $createdExpenses = [];
            $updatedInventories = [];

            \DB::beginTransaction();

            foreach ($this->releaseItems as $item) {
                $inventory = Inventory::find($item['inventory_id']);
                if (!$inventory) {
                    \DB::rollBack();
                    session()->flash('message', 'One of the selected inventory items not found.');
                    return;
                }

                if ($item['quantity_used'] > $inventory->quantity) {
                    \DB::rollBack();
                    $this->addError('releaseItems', "Quantity for {$inventory->brand} exceeds available stock ({$inventory->quantity}).");
                    return;
                }

                $totalCost = round($item['quantity_used'] * (float)$item['cost_per_unit'], 2);

                // Create expense
                $expense = Expense::create([
                    'client_id' => $this->client_id,
                    'inventory_id' => $item['inventory_id'],
                    'quantity_used' => $item['quantity_used'],
                    'cost_per_unit' => $item['cost_per_unit'],
                    'total_cost' => $totalCost,
                    'released_at' => now(),
                ]);

                $createdExpenses[] = $expense;

                // Update inventory
                $newQuantity = $inventory->quantity - $item['quantity_used'];
                $newStatus = $newQuantity <= 0 ? 'out_of_stock' :
                           ($newQuantity <= $inventory->min_stock_level ? 'critical' : 'normal');

                $inventory->update([
                    'quantity' => max(0, $newQuantity),
                    'status' => $newStatus,
                ]);

                $updatedInventories[] = $inventory;

                // Log expense creation
                History::create([
                    'user_id' => auth()->id(),
                    'action' => 'create',
                    'model' => 'expense',
                    'model_id' => $expense->id,
                    'changes' => [
                        'client_id' => $expense->client_id,
                        'inventory_id' => $expense->inventory_id,
                        'quantity_used' => $expense->quantity_used,
                        'cost_per_unit' => $expense->cost_per_unit,
                        'total_cost' => $expense->total_cost,
                    ],
                ]);
            }

            // Log inventory updates
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

            \DB::commit();

            // Refresh data
            $this->loadInventories();
            $this->clients = Client::with('expenses')->get();

            $this->closeModal();
            session()->flash('message', count($createdExpenses) . ' release(s) recorded and inventory updated successfully.');

        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('message', 'An error occurred while processing the release.');
            \Log::error('Release save error: ' . $e->getMessage());
        }
    }

    /**
     * Save inventory item (create or update).
     */
    public function save(): void
    {
        $this->ensureAdmin();

        $this->validate([
            'brand' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'category' => 'required|string|max:255|in:' . implode(',', Inventory::CATEGORIES),
            'quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
        ]);

        // Password validation for editing
        if ($this->editing) {
            $this->validate([
                'editPassword' => 'required|string',
            ]);

            if (!\Illuminate\Support\Facades\Hash::check($this->editPassword, auth()->user()->password)) {
                $this->addError('editPassword', 'The password is incorrect.');
                return;
            }
        }

        $wasEditing = $this->editing;

        // Auto-set status based on quantity and min_stock_level
        $status = $this->quantity <= 0 ? 'out_of_stock' :
                 ($this->quantity <= $this->min_stock_level ? 'critical' : 'normal');

        try {
            if ($this->editing) {
                $inventory = Inventory::find($this->inventoryId);
                if (!$inventory) {
                    session()->flash('message', 'Inventory item not found.');
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

                // Log history
                History::create([
                    'user_id' => auth()->id(),
                    'action' => 'update',
                    'model' => 'inventory',
                    'model_id' => $inventory->id,
                    'changes' => [
                        'brand' => $this->brand,
                        'description' => $this->description,
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

                // Log history
                History::create([
                    'user_id' => auth()->id(),
                    'action' => 'create',
                    'model' => 'inventory',
                    'model_id' => $inventory->id,
                    'changes' => [
                        'brand' => $this->brand,
                        'description' => $this->description,
                        'category' => $this->category,
                        'quantity' => $this->quantity,
                        'min_stock_level' => $this->min_stock_level,
                        'status' => $status
                    ],
                ]);
            }

            // Handle image upload
            if ($this->image) {
                $inventory->storeImageAsBlob($this->image->path());
            }

            $this->loadInventories();
            session()->flash('message', $wasEditing ? 'Inventory item updated successfully.' : 'Inventory item created successfully.');
            $this->closeModal();

        } catch (\Exception $e) {
            session()->flash('message', 'An error occurred while saving the inventory item.');
            \Log::error('Inventory save error: ' . $e->getMessage());
        }
    }

    public function openDeleteModal($id)
    {
        $this->ensureAdmin();
        $this->deleteInventoryId = $id;
        $this->showDeleteModal = true;
    }

    public function confirmDelete()
    {
        $this->ensureAdmin();

        $this->validate([
            'deletePassword' => 'required',
        ]);

        // Check password
        if (!\Illuminate\Support\Facades\Hash::check($this->deletePassword, auth()->user()->password)) {
            $this->addError('deletePassword', 'The password is incorrect.');
            return;
        }

        $inventory = Inventory::find($this->deleteInventoryId);
        if (!$inventory) {
            session()->flash('message', 'Inventory not found.');
            return;
        }
        $name = $inventory->brand . ' / ' . $inventory->description;
        $inventory->delete();
        History::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model' => 'inventory',
            'model_id' => $this->deleteInventoryId,
            'changes' => ['name' => $name, 'deleted' => true],
        ]);

        $this->loadInventories();
        $this->closeModal();
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
            $name = $inventory->brand . ' / ' . $inventory->description;
            History::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model' => 'inventory',
                'model_id' => $inventory->id,
                'changes' => ['name' => $name, 'deleted' => true, 'bulk_delete' => true],
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

    /**
     * Ensure the current user has admin privileges.
     *
     * @throws \Symfony\Component\HttpFoundation\Exception\BadRequestException
     */
    protected function ensureAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isSystemAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }
    }

    public function render()
    {
        return view('livewire.masterlist');
    }
}
