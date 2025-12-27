<?php

namespace App\Livewire;

use App\Enums\InventoryStatus;
use App\Models\Client;
use App\Models\Expense;
use App\Models\History;
use App\Models\Inventory;
use App\Models\MaterialReleaseApproval;
use App\Models\Project;
use App\Models\User;
use App\Models\Chat;
use App\Notifications\MaterialReleaseApprovalRequest;
use App\Events\ApprovalRequestCreated;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;

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
    public $projects = [];
    public Collection $inventoryOptions;
    public ?int $client_id = null;
    public ?int $project_id = null;
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
        $this->projects = [];
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

    public function updatedClientId()
    {
        $this->project_id = null;
        if ($this->client_id) {
            $projects = Project::where('client_id', $this->client_id)->get();
            \Log::info('Projects found for client ' . $this->client_id . ': ' . count($projects));
            $this->projects = $projects->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'reference_code' => $p->reference_code,
            ])->toArray();
            \Log::info('Projects array: ', $this->projects);
        } else {
            $this->projects = [];
        }
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
        $this->project_id = null;
        $this->releaseItems = [];
        $this->selectedInventoryId = null;
    }

    protected function resetDeleteForm(): void
    {
        $this->deleteInventoryId = null;
        $this->deletePassword = '';
    }

    /**
     * Submit material release approval request for regular users
     */
    protected function submitMasterlistApprovalRequest(Client $client): void
    {
        \DB::beginTransaction();
        try {
            // Get all system admins
            $systemAdmins = User::where('role', 'system_admin')->get();
            
            if ($systemAdmins->isEmpty()) {
                \Log::warning('No system administrators found for masterlist approval');
                session()->flash('error', 'No system administrators available to approve your request.');
                $this->closeModal();
                \DB::rollBack();
                return;
            }

            \Log::info('Masterlist approval request from user: ' . auth()->user()->name);

            // Validate inventory availability
            $inventories = Inventory::whereIn('id', collect($this->releaseItems)->pluck('inventory_id'))->get()->keyBy('id');
            
            foreach ($this->releaseItems as $item) {
                $inventory = $inventories->get($item['inventory_id']);
                if (!$inventory) {
                    session()->flash('error', 'One of the selected inventory items not found.');
                    $this->closeModal();
                    \DB::rollBack();
                    return;
                }
                
                if ($item['quantity_used'] > $inventory->quantity) {
                    session()->flash('error', "Quantity for {$inventory->brand} exceeds available stock ({$inventory->quantity}).");
                    $this->closeModal();
                    \DB::rollBack();
                    return;
                }
            }

            // Create approval requests for each item
            foreach ($this->releaseItems as $item) {
                $inventory = $inventories->get($item['inventory_id']);

                // Create approval request first
                $projectName = $this->project_id ? Project::find($this->project_id)?->name : null;
                $approval = MaterialReleaseApproval::create([
                    'requested_by' => auth()->id(),
                    'inventory_id' => $inventory->id,
                    'quantity_requested' => $item['quantity_used'],
                    'reason' => "Material release for client: {$client->name}",
                    'status' => 'pending',
                    'chat_id' => null, // Will be set after creating chat
                    'client' => $client->name,
                    'project' => $projectName,
                ]);

                \Log::info('Masterlist approval request created with ID: ' . $approval->id);

                // Create chat messages to ALL system admins (private messages)
                $chatIds = [];
                foreach ($systemAdmins as $admin) {
                    try {
                        $projectInfo = $this->project_id ? "\nProject: " . Project::find($this->project_id)?->name : '';
                        $chat = Chat::create([
                            'user_id' => auth()->id(),
                            'recipient_id' => $admin->id,
                            'message' => "📋 Material Release Request from Masterlist\n\nClient: {$client->name}{$projectInfo}\nItem: {$inventory->brand} - {$inventory->description}\nQuantity: {$item['quantity_used']}\nCost per unit: {$item['cost_per_unit']}\n\nApproval ID: {$approval->id}",
                        ]);
                        $chatIds[] = $chat->id;

                        // Broadcast the chat message
                        event(new \App\Events\MessageSent($chat));
                    } catch (\Exception $e) {
                        \Log::error('Failed to create chat for admin ' . $admin->id . ': ' . $e->getMessage());
                    }
                }

                // Update approval with first chat ID (for reference)
                if (!empty($chatIds)) {
                    $approval->update(['chat_id' => $chatIds[0]]);
                }

                // Create history entry for the request
                \App\Models\History::create([
                    'user_id' => auth()->id(),
                    'action' => 'Material Release Request Created',
                    'model' => 'MaterialReleaseApproval',
                    'model_id' => $approval->id,
                    'changes' => json_encode([
                        'status' => 'pending',
                        'client' => $approval->client,
                        'project' => $approval->project,
                        'material' => $inventory->material_name,
                        'quantity' => $item['quantity_used'],
                        'cost_per_unit' => $item['cost_per_unit'],
                        'reason' => "Material release for client: {$client->name}",
                        'requested_at' => now()->toDateTimeString(),
                    ]),
                    'old_values' => null,
                ]);

                // Broadcast history creation event
                try {
                    $historyEntry = \App\Models\History::where('user_id', auth()->id())
                        ->where('model', 'MaterialReleaseApproval')
                        ->where('model_id', $approval->id)
                        ->latest()
                        ->first();
                    if ($historyEntry) {
                        event(new \App\Events\HistoryEntryCreated($historyEntry));
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to broadcast history event: ' . $e->getMessage());
                }

                // Broadcast real-time event
                try {
                    event(new ApprovalRequestCreated($approval));
                } catch (\Exception $e) {
                    \Log::error('Failed to broadcast masterlist approval event: ' . $e->getMessage());
                }

                // Notify all system admins
                foreach ($systemAdmins as $admin) {
                    try {
                        $admin->notify(new MaterialReleaseApprovalRequest($approval));
                    } catch (\Exception $e) {
                        \Log::error('Failed to notify admin for masterlist approval: ' . $e->getMessage());
                    }
                }
            }

            \DB::commit();
            
            $this->closeModal();
            $this->resetReleaseForm();
            
            session()->flash('message', '✅ Material release request sent to System Admin for approval. Materials will NOT be released until approved.');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Masterlist approval request failed: ' . $e->getMessage());
            session()->flash('error', 'Unable to submit approval request: ' . $e->getMessage());
            $this->closeModal();
        }
    }

    /**
     * Process inventory release to client.
     * Creates expenses and updates inventory quantities.
     */
    public function saveRelease(): void
    {
        // Check permissions
        if (!auth()->check()) {
            abort(403, 'Access denied. You must be logged in.');
        }

        $user = auth()->user();
        
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'required|exists:projects,id',
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

        // Regular users must request approval
        if ($user->role === 'user') {
            $this->submitMasterlistApprovalRequest($client);
            return;
        }
        
        // System Admin only: Direct release
        if (!$user->isSystemAdmin()) {
            abort(403, 'Access denied. Insufficient privileges.');
        }

        // Continue with direct release for admins
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
                    'project_id' => $this->project_id,
                    'inventory_id' => $item['inventory_id'],
                    'quantity_used' => $item['quantity_used'],
                    'cost_per_unit' => $item['cost_per_unit'],
                    'total_cost' => $totalCost,
                    'released_at' => now(),
                ]);

                $createdExpenses[] = $expense;

                // Update inventory
                $newQuantity = $inventory->quantity - $item['quantity_used'];
                $newStatus = $newQuantity <= 0
                    ? InventoryStatus::OUT_OF_STOCK
                    : ($newQuantity <= $inventory->min_stock_level
                        ? InventoryStatus::CRITICAL
                        : InventoryStatus::NORMAL);

                $inventory->update([
                    'quantity' => max(0, $newQuantity),
                    'status' => $newStatus->value,
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
                        'status' => $inventory->status instanceof InventoryStatus
                            ? $inventory->status->value
                            : $inventory->status,
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
        $status = $this->quantity <= 0
            ? InventoryStatus::OUT_OF_STOCK
            : ($this->quantity <= $this->min_stock_level
                ? InventoryStatus::CRITICAL
                : InventoryStatus::NORMAL);

        try {
            if ($this->editing) {
                $inventory = Inventory::find($this->inventoryId);
                if (!$inventory) {
                    session()->flash('message', 'Inventory item not found.');
                    return;
                }

                // Capture old values before update
                $oldValues = [
                    'brand' => $inventory->brand,
                    'description' => $inventory->description,
                    'category' => $inventory->category,
                    'quantity' => $inventory->quantity,
                    'min_stock_level' => $inventory->min_stock_level,
                    'status' => $inventory->status
                ];

                $inventory->update([
                    'brand' => $this->brand,
                    'description' => $this->description,
                    'category' => $this->category,
                    'quantity' => $this->quantity,
                    'min_stock_level' => $this->min_stock_level,
                    'status' => $status->value,
                ]);

                // Log history with old and new values
                History::create([
                    'user_id' => auth()->id(),
                    'action' => 'update',
                    'model' => 'inventory',
                    'model_id' => $inventory->id,
                    'old_values' => $oldValues,
                    'changes' => [
                        'brand' => $this->brand,
                        'description' => $this->description,
                        'category' => $this->category,
                        'quantity' => $this->quantity,
                        'min_stock_level' => $this->min_stock_level,
                        'status' => $status->value
                    ],
                ]);
            } else {
                $inventory = Inventory::create([
                    'brand' => $this->brand,
                    'description' => $this->description,
                    'category' => $this->category,
                    'quantity' => $this->quantity,
                    'min_stock_level' => $this->min_stock_level,
                    'status' => $status->value,
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
                        'status' => $status->value
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

        // Capture all inventory details before deletion
        $inventoryDetails = [
            'brand' => $inventory->brand,
            'description' => $inventory->description,
            'category' => $inventory->category,
            'quantity' => $inventory->quantity,
            'min_stock_level' => $inventory->min_stock_level,
            'status' => $inventory->status instanceof InventoryStatus ? $inventory->status->value : $inventory->status,
            'has_image' => $inventory->hasImageBlob(),
            'deleted' => true
        ];

        $inventory->delete();
        History::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model' => 'inventory',
            'model_id' => $this->deleteInventoryId,
            'changes' => $inventoryDetails,
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
            // Capture all inventory details before deletion
            $inventoryDetails = [
                'brand' => $inventory->brand,
                'description' => $inventory->description,
                'category' => $inventory->category,
                'quantity' => $inventory->quantity,
                'min_stock_level' => $inventory->min_stock_level,
                'status' => $inventory->status instanceof InventoryStatus ? $inventory->status->value : $inventory->status,
                'has_image' => $inventory->hasImageBlob(),
                'deleted' => true,
                'bulk_delete' => true
            ];

            History::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model' => 'inventory',
                'model_id' => $inventory->id,
                'changes' => $inventoryDetails,
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
