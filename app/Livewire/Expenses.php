<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\History;

class Expenses extends Component
{
    use WithFileUploads;

    public $clients;
    public $selectedClient = null;
    public $clientExpenses = [];

    // Create Release (Expense) modal state
    public $showModal = false;

    // Client Management modal state
    public $showClientModal = false;
    public $editingClient = false;
    public $clientId = null;
    public $clientName = '';
    public $clientBranch = '';
    public $clientLogo = null;

    // Create form fields
    public $client_id = null;
    public $inventory_id = null;
    public $quantity_used = 1;
    public $cost_per_unit = 0;

    // Options
    public $inventoryOptions = [];

    public function mount()
    {
        $this->loadClients();
        $this->inventoryOptions = Inventory::orderBy('brand')->orderBy('description')->get();
    }

    public function loadClients()
    {
        $this->clients = Client::with('expenses')->get();
    }

    public function viewExpenses($clientId)
    {
        $this->selectedClient = Client::with('expenses')->find($clientId);
        $this->clientExpenses = Expense::where('client_id', $clientId)->with('inventory')->latest()->get();
    }

    public function openCreateModal()
    {
        $this->ensureAdmin();
        $this->resetCreateForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        // Close all modals
        $this->selectedClient = null;
        $this->clientExpenses = [];
        $this->showModal = false;
        $this->showClientModal = false;
        $this->resetClientForm();
    }

    public function openClientModal($clientId = null)
    {
        $this->ensureAdmin();
        $this->resetClientForm();

        if ($clientId) {
            $client = Client::find($clientId);
            if (!$client) {
                session()->flash('message', 'Client not found.');
                return;
            }
            $this->editingClient = true;
            $this->clientId = $clientId;
            $this->clientName = $client->name;
            $this->clientBranch = $client->branch;
        }

        $this->showClientModal = true;
    }

    protected function resetClientForm(): void
    {
        $this->editingClient = false;
        $this->clientId = null;
        $this->clientName = '';
        $this->clientBranch = '';
        $this->clientLogo = null;
    }

    public function saveClient()
    {
        $this->ensureAdmin();

        $this->validate([
            'clientName' => 'required|string|max:255',
            'clientBranch' => 'required|string|max:255',
            'clientLogo' => 'nullable|image|max:2048', // 2MB max
        ]);

        if ($this->editingClient) {
            $client = Client::find($this->clientId);
            if (!$client) {
                session()->flash('message', 'Client not found.');
                return;
            }
            $client->update([
                'name' => $this->clientName,
                'branch' => $this->clientBranch,
            ]);

            // Handle logo upload
            if ($this->clientLogo) {
                $client->addMedia($this->clientLogo->getRealPath())
                    ->usingName($this->clientLogo->getClientOriginalName())
                    ->usingFileName($this->clientLogo->getClientOriginalName())
                    ->toMediaCollection('logo');
            }

            History::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model' => 'client',
                'model_id' => $client->id,
                'changes' => [
                    'name' => $this->clientName,
                    'branch' => $this->clientBranch,
                    'logo_updated' => $this->clientLogo ? true : false,
                ],
            ]);
            $message = 'Client updated successfully.';
        } else {
            $client = Client::create([
                'name' => $this->clientName,
                'branch' => $this->clientBranch,
            ]);

            // Handle logo upload for new client
            if ($this->clientLogo) {
                $client->addMedia($this->clientLogo->getRealPath())
                    ->usingName($this->clientLogo->getClientOriginalName())
                    ->usingFileName($this->clientLogo->getClientOriginalName())
                    ->toMediaCollection('logo');
            }

            History::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model' => 'client',
                'model_id' => $client->id,
                'changes' => [
                    'name' => $this->clientName,
                    'branch' => $this->clientBranch,
                    'logo_uploaded' => $this->clientLogo ? true : false,
                ],
            ]);
            $message = 'Client created successfully.';
        }

        $this->loadClients();
        $this->closeModal();
        session()->flash('message', $message);
    }

    public function deleteClient($clientId)
    {
        $this->ensureAdmin();

        $client = Client::find($clientId);
        if (!$client) {
            session()->flash('message', 'Client not found.');
            return;
        }

        // Check if client has expenses
        if ($client->expenses()->count() > 0) {
            session()->flash('message', 'Cannot delete client with existing expenses.');
            return;
        }

        $client->delete();
        History::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model' => 'client',
            'model_id' => $clientId,
            'changes' => ['deleted' => true],
        ]);

        $this->loadClients();
        session()->flash('message', 'Client deleted successfully.');
    }

    protected function resetCreateForm(): void
    {
        $this->client_id = null;
        $this->inventory_id = null;
        $this->quantity_used = 1;
        $this->cost_per_unit = 0;
    }

    public function save()
    {
        $this->ensureAdmin();

        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'inventory_id' => 'required|exists:inventories,id',
            'quantity_used' => 'required|integer|min:1',
            'cost_per_unit' => 'required|numeric|min:0',
        ]);

        $inventory = Inventory::find($this->inventory_id);
        if (!$inventory) {
            session()->flash('message', 'Selected inventory not found.');
            return;
        }

        if ($this->quantity_used > $inventory->quantity) {
            $this->addError('quantity_used', 'Quantity exceeds available stock ('.$inventory->quantity.').');
            return;
        }

        $total = round($this->quantity_used * (float)$this->cost_per_unit, 2);

        $expense = Expense::create([
            'client_id' => $this->client_id,
            'inventory_id' => $this->inventory_id,
            'quantity_used' => $this->quantity_used,
            'cost_per_unit' => $this->cost_per_unit,
            'total_cost' => $total,
            'released_at' => now(),
        ]);

        // Update inventory stock and status
        $inventory->quantity = $inventory->quantity - $this->quantity_used;
        if ($inventory->quantity <= 0) {
            $inventory->quantity = 0;
            $inventory->status = 'out_of_stock';
        } elseif ($inventory->quantity <= $inventory->min_stock_level) {
            $inventory->status = 'critical';
        } else {
            $inventory->status = 'normal';
        }
        $inventory->save();

        // Log history for expense and inventory
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

        // Refresh lists
        $this->loadClients();
        if ($this->selectedClient && $this->selectedClient->id === (int)$this->client_id) {
            $this->viewExpenses($this->client_id);
        }

        $this->closeModal();
        session()->flash('message', 'Release recorded and inventory updated.');
    }

    protected function ensureAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isSystemAdmin()) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.expenses');
    }
}
