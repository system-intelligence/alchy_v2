<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\History;

class Expenses extends Component
{
    public $clients;
    public $selectedClient = null;
    public $clientExpenses = [];

    // Create Release (Expense) modal state
    public $showModal = false;

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
        $this->clients = Client::with('expenses')->get()->map(function ($client) {
            $client->total_expenses = $client->expenses->sum('total_cost');
            return $client;
        });
    }

    public function viewExpenses($clientId)
    {
        $this->selectedClient = Client::find($clientId);
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
        // Close both types of modals
        $this->selectedClient = null;
        $this->clientExpenses = [];
        $this->showModal = false;
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
        } elseif ($inventory->quantity <= 5) {
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
