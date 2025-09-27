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

    // Date filter
    public $filterDate = null;
    public $filterByMonth = false;

    // Calendar theme
    public $calendarTheme = 'default';
    public $themeOpen = false;
    public $themes = [
        'default' => ['name' => 'Default', 'class' => 'bg-white dark:bg-gray-800'],
        'blue' => ['name' => 'Blue', 'class' => 'bg-blue-50 dark:bg-blue-900/20'],
        'green' => ['name' => 'Green', 'class' => 'bg-green-50 dark:bg-green-900/20'],
        'purple' => ['name' => 'Purple', 'class' => 'bg-purple-50 dark:bg-purple-900/20'],
        'orange' => ['name' => 'Orange', 'class' => 'bg-orange-50 dark:bg-orange-900/20'],
        'pink' => ['name' => 'Pink', 'class' => 'bg-pink-50 dark:bg-pink-900/20'],
    ];

    // Calendar
    public $calendarMonth;
    public $calendarYear;

    // Custom selects
    public $monthOpen = false;
    public $monthSelected;
    public $yearOpen = false;
    public $yearSelected;
    public $monthItems = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    public $yearItems = [];


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
        $this->calendarMonth = now()->month;
        $this->calendarYear = now()->year;
        $this->monthSelected = $this->calendarMonth - 1;
        $this->yearItems = range(2025, date('Y') + 10);
        $this->yearSelected = array_search($this->calendarYear, $this->yearItems);
    }

    public function loadClients()
    {
        $this->clients = Client::with('expenses')->get();
    }

    public function viewExpenses($clientId)
    {
        $this->selectedClient = Client::with('expenses')->find($clientId);
        $query = Expense::where('client_id', $clientId)->with('inventory');
        if ($this->filterByMonth && $this->calendarYear && $this->calendarMonth) {
            $query->whereYear('released_at', $this->calendarYear)->whereMonth('released_at', $this->calendarMonth);
        } elseif ($this->filterDate) {
            $query->whereDate('released_at', $this->filterDate);
        }
        $this->clientExpenses = $query->latest()->get();
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

        $name = $client->name . ' / ' . $client->branch;
        $client->delete();
        History::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model' => 'client',
            'model_id' => $this->deleteClientId,
            'changes' => ['name' => $name, 'deleted' => true],
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

    public function applyDateFilter()
    {
        $this->filterByMonth = false;
        if ($this->selectedClient) {
            $this->viewExpenses($this->selectedClient->id);
        }
    }

    public function applyMonthFilter()
    {
        $this->filterByMonth = true;
        $this->filterDate = null;
        if ($this->selectedClient) {
            $this->viewExpenses($this->selectedClient->id);
        }
    }

    public function clearDateFilter()
    {
        $this->filterDate = null;
        $this->filterByMonth = false;
        if ($this->selectedClient) {
            $this->viewExpenses($this->selectedClient->id);
        }
    }

    public function selectDate($day)
    {
        $this->filterDate = sprintf('%04d-%02d-%02d', $this->calendarYear, $this->calendarMonth, $day);
        if ($this->selectedClient) {
            $this->viewExpenses($this->selectedClient->id);
        }
    }

    public function changeMonth($direction)
    {
        if ($direction === 'prev') {
            $this->calendarMonth--;
            if ($this->calendarMonth < 1) {
                $this->calendarMonth = 12;
                $this->calendarYear--;
            }
        } elseif ($direction === 'next') {
            $this->calendarMonth++;
            if ($this->calendarMonth > 12) {
                $this->calendarMonth = 1;
                $this->calendarYear++;
            }
        }
        // Update selected indices
        $this->monthSelected = $this->calendarMonth - 1;
        $this->yearSelected = array_search($this->calendarYear, $this->yearItems);
    }

    public function updatedCalendarMonth()
    {
        // Optional: refresh if needed
    }

    public function updatedCalendarYear()
    {
        // Optional: refresh if needed
    }

    public function toggleMonth()
    {
        $this->monthOpen = !$this->monthOpen;
        $this->yearOpen = false;
    }

    public function selectMonth($index)
    {
        $this->monthSelected = $index;
        $this->calendarMonth = $index + 1;
        $this->monthOpen = false;
    }

    public function toggleYear()
    {
        $this->yearOpen = !$this->yearOpen;
        $this->monthOpen = false;
    }

    public function selectYear($index)
    {
        $this->yearSelected = $index;
        $this->calendarYear = $this->yearItems[$index];
        $this->yearOpen = false;
    }

    public function toggleTheme()
    {
        $this->themeOpen = !$this->themeOpen;
        $this->monthOpen = false;
        $this->yearOpen = false;
    }

    public function selectTheme($theme)
    {
        $this->calendarTheme = $theme;
        $this->themeOpen = false;
    }

    public function getDaysInMonthProperty()
    {
        return cal_days_in_month(CAL_GREGORIAN, $this->calendarMonth, $this->calendarYear);
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
                $client->storeImageAsBlob($this->clientLogo->path());
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
                $client->storeImageAsBlob($this->clientLogo->path());
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
