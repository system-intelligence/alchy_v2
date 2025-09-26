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

    // Edit expense
    public $editingExpenseId = null;
    public $editCostPerUnit = 0;


    // Client Management modal state
    public $showClientModal = false;
    public $editingClient = false;
    public $clientId = null;
    public $clientName = '';
    public $clientBranch = '';
    public $clientLogo = null;
    public $startDate = '';
    public $endDate = '';
    public $jobType = '';

    // Client Deletion modal
    public $showDeleteClientModal = false;
    public $deleteClientId = null;
    public $deletePassword = '';



    public function mount()
    {
        $this->loadClients();
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


    public function closeModal()
    {
        // Close all modals
        $this->selectedClient = null;
        $this->clientExpenses = [];
        $this->showClientModal = false;
        $this->showDeleteClientModal = false;
        $this->resetClientForm();
        $this->resetDeleteForm();
        $this->cancelEditExpense();
    }

    public function openDeleteClientModal($clientId)
    {
        $this->ensureAdmin();
        $this->deleteClientId = $clientId;
        $this->showDeleteClientModal = true;
    }

    public function confirmDeleteClient()
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

        $client = Client::find($this->deleteClientId);
        if (!$client) {
            session()->flash('message', 'Client not found.');
            return;
        }

        $client->delete();
        History::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model' => 'client',
            'model_id' => $this->deleteClientId,
            'changes' => ['deleted' => true],
        ]);

        $this->loadClients();
        $this->closeModal();
        session()->flash('message', 'Client deleted successfully.');
    }

    protected function resetDeleteForm(): void
    {
        $this->deleteClientId = null;
        $this->deletePassword = '';
    }


    public function editExpense($expenseId)
    {
        $expense = Expense::find($expenseId);
        if ($expense) {
            $this->editingExpenseId = $expenseId;
            $this->editCostPerUnit = $expense->cost_per_unit;
        }
    }

    public function saveEditExpense()
    {
        $this->ensureAdmin();

        $this->validate([
            'editCostPerUnit' => 'required|numeric|min:0',
        ]);

        $expense = Expense::find($this->editingExpenseId);
        if (!$expense) {
            session()->flash('message', 'Expense not found.');
            return;
        }

        $oldCost = $expense->cost_per_unit;
        $expense->cost_per_unit = $this->editCostPerUnit;
        $expense->total_cost = round($expense->quantity_used * $this->editCostPerUnit, 2);
        $expense->save();

        History::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model' => 'expense',
            'model_id' => $expense->id,
            'changes' => [
                'cost_per_unit' => $this->editCostPerUnit,
                'total_cost' => $expense->total_cost,
            ],
        ]);

        $this->loadClients();
        if ($this->selectedClient) {
            $this->viewExpenses($this->selectedClient->id);
        }
        $this->cancelEditExpense();
        session()->flash('message', 'Expense updated successfully.');
    }

    public function cancelEditExpense()
    {
        $this->editingExpenseId = null;
        $this->editCostPerUnit = 0;
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
            $this->startDate = $client->start_date ? $client->start_date->format('Y-m-d') : '';
            $this->endDate = $client->end_date ? $client->end_date->format('Y-m-d') : '';
            $this->jobType = $client->job_type;
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
        $this->startDate = '';
        $this->endDate = '';
        $this->jobType = '';
    }

    public function saveClient()
    {
        $this->ensureAdmin();

        $this->validate([
            'clientName' => 'required|string|max:255',
            'clientBranch' => 'required|string|max:255',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'jobType' => 'nullable|in:service,installation',
            'clientLogo' => 'nullable|image|max:2048', // 2MB max
        ]);

        if ($this->editingClient) {
            $client = Client::find($this->clientId);
            if (!$client) {
                session()->flash('message', 'Client not found.');
                return;
            }
            $status = $this->endDate ? 'settled' : 'in_progress';
            $client->update([
                'name' => $this->clientName,
                'branch' => $this->clientBranch,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'job_type' => $this->jobType,
                'status' => $status,
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
                    'start_date' => $this->startDate,
                    'end_date' => $this->endDate,
                    'job_type' => $this->jobType,
                    'status' => $status,
                    'logo_updated' => $this->clientLogo ? true : false,
                ],
            ]);
            $message = 'Client updated successfully.';
        } else {
            $status = $this->endDate ? 'settled' : 'in_progress';
            $client = Client::create([
                'name' => $this->clientName,
                'branch' => $this->clientBranch,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'job_type' => $this->jobType,
                'status' => $status,
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
                    'start_date' => $this->startDate,
                    'end_date' => $this->endDate,
                    'job_type' => $this->jobType,
                    'status' => $status,
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
