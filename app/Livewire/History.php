<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\History as HistoryModel;

class History extends Component
{
    use WithPagination;
    public $viewMode = 'grid'; // 'grid' or 'table'
    public $showChangeModal = false;
    public $selectedHistory = null;
    public $showRelatedMovementsModal = false;
    public $relatedMovements = null;
    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $listeners = [
        'historyCreated' => '$refresh',
        'refreshHistory' => '$refresh',
    ];

    public function mount()
    {
        // WithPagination handles loading automatically
    }

    public function getHistoriesProperty()
    {
        $user = auth()->user();

        $query = HistoryModel::with('user');

        // Apply role-based filtering
        if ($user->role === 'system_admin') {
            // System admins see ALL histories across the system
        } elseif ($user->role === 'user') {
            // Users see their own histories + all approval request histories
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('model', 'MaterialReleaseApproval');
            });
        } else {
            // Developers only see their own histories
            $query->where('user_id', $user->id);
        }

        // Apply search filter
        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('action', 'like', $searchTerm)
                  ->orWhere('model', 'like', $searchTerm)
                  ->orWhere('model_id', 'like', $searchTerm)
                  ->orWhere('changes', 'like', $searchTerm)
                  ->orWhere('old_values', 'like', $searchTerm)
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(changes, '$.reference_code')) LIKE ?", [$searchTerm])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(changes, '$.project')) LIKE ?", [$searchTerm])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(changes, '$.client')) LIKE ?", [$searchTerm])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.reference_code')) LIKE ?", [$searchTerm])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.project')) LIKE ?", [$searchTerm])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.client')) LIKE ?", [$searchTerm])
                  ->orWhereRaw("DATE_FORMAT(created_at, '%M %d, %Y') LIKE ?", [$searchTerm])
                  ->orWhereRaw("DATE_FORMAT(created_at, '%M %d, %Y - %h:%i %p') LIKE ?", [$searchTerm])
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'like', $searchTerm);
                  })
                  // Search in related project data
                  ->orWhere(function ($projectQuery) use ($searchTerm) {
                      $projectQuery->where('model', 'project')
                          ->whereHas('projectRelation', function ($p) use ($searchTerm) {
                              $p->where('name', 'like', $searchTerm)
                                ->orWhere('reference_code', 'like', $searchTerm);
                          });
                  })
                  // Search in related expense project data
                  ->orWhere(function ($expenseQuery) use ($searchTerm) {
                      $expenseQuery->where('model', 'expense')
                          ->whereHas('expenseRelation.project', function ($p) use ($searchTerm) {
                              $p->where('name', 'like', $searchTerm)
                                ->orWhere('reference_code', 'like', $searchTerm);
                          });
                  })
                  // Search in related client data
                  ->orWhere(function ($clientQuery) use ($searchTerm) {
                      $clientQuery->where('model', 'client')
                          ->whereHas('clientRelation', function ($c) use ($searchTerm) {
                              $c->where('name', 'like', $searchTerm)
                                ->orWhere('branch', 'like', $searchTerm);
                          });
                  })
                  // Search in related expense client data
                  ->orWhere(function ($expenseClientQuery) use ($searchTerm) {
                      $expenseClientQuery->where('model', 'expense')
                          ->whereHas('expenseRelation.client', function ($c) use ($searchTerm) {
                              $c->where('name', 'like', $searchTerm)
                                ->orWhere('branch', 'like', $searchTerm);
                          });
                  })
                  // Search in related inventory data
                  ->orWhere(function ($inventoryQuery) use ($searchTerm) {
                      $inventoryQuery->where('model', 'inventory')
                          ->whereHas('inventoryRelation', function ($i) use ($searchTerm) {
                              $i->where('brand', 'like', $searchTerm)
                                ->orWhere('description', 'like', $searchTerm)
                                ->orWhere('category', 'like', $searchTerm);
                          });
                  })
                  // Search in related expense inventory data
                  ->orWhere(function ($expenseInventoryQuery) use ($searchTerm) {
                      $expenseInventoryQuery->where('model', 'expense')
                          ->whereHas('expenseRelation.inventory', function ($i) use ($searchTerm) {
                              $i->where('brand', 'like', $searchTerm)
                                ->orWhere('description', 'like', $searchTerm)
                                ->orWhere('category', 'like', $searchTerm);
                          });
                  })
                  // Search in approval-related data
                  ->orWhere(function ($approvalQuery) use ($searchTerm) {
                      $approvalQuery->where('model', 'MaterialReleaseApproval')
                          ->whereHas('approvalRelation.expense.project', function ($p) use ($searchTerm) {
                              $p->where('name', 'like', $searchTerm)
                                ->orWhere('reference_code', 'like', $searchTerm);
                          })
                          ->orWhereHas('approvalRelation.expense.client', function ($c) use ($searchTerm) {
                              $c->where('name', 'like', $searchTerm)
                                ->orWhere('branch', 'like', $searchTerm);
                          })
                          ->orWhereHas('approvalRelation.inventory', function ($i) use ($searchTerm) {
                              $i->where('brand', 'like', $searchTerm)
                                ->orWhere('description', 'like', $searchTerm);
                          });
                  });
            });
        }

        // Apply date filters
        if (!empty($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query->latest()->paginate(20);
    }

    public function getSummaryProperty()
    {
        $user = auth()->user();
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Get history entries based on user role
        $historiesQuery = HistoryModel::query();

        if ($user->role === 'system_admin') {
            // System admins see ALL histories across the system
        } elseif ($user->role === 'user') {
            // Users see their own histories + all approval request histories
            $historiesQuery->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('model', 'MaterialReleaseApproval');
            });
        } else {
            // Developers only see their own histories
            $historiesQuery->where('user_id', $user->id);
        }

        $totalCount = $historiesQuery->count();
        $monthCount = (clone $historiesQuery)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        // Calculate average per day for the current month
        $daysInMonth = now()->daysInMonth;
        $averagePerDay = $daysInMonth > 0 ? round($monthCount / $daysInMonth, 1) : 0;

        return [
            'total' => $totalCount,
            'month' => $monthCount,
            'average' => $averagePerDay,
            'count' => $totalCount,
        ];
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function showChangeDetails($historyId)
    {
        $this->selectedHistory = HistoryModel::find($historyId);
        $this->showChangeModal = true;
    }

    public function closeChangeModal()
    {
        $this->showChangeModal = false;
        $this->selectedHistory = null;
    }

    public function openRelatedMovementsModal($approvalId)
    {
        $approval = \App\Models\MaterialReleaseApproval::with('expense.client', 'expense.project')->find($approvalId);

        if (!$approval || !$approval->expense) {
            return;
        }

        // Get all expenses for this client/project combination
        $query = \App\Models\Expense::query();

        if ($approval->expense->client_id) {
            $query->where('client_id', $approval->expense->client_id);
        }

        if ($approval->expense->project_id) {
            $query->where('project_id', $approval->expense->project_id);
        }

        $relatedExpenses = $query->pluck('id');

        // Get all stock movements for these expenses
        $movements = \App\Models\StockMovement::whereIn('reference', $relatedExpenses->map(fn($id) => 'expense_' . $id))
            ->with(['inventory', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $this->relatedMovements = [
            'client_name' => $approval->expense->client?->name ?? 'N/A',
            'project_name' => $approval->expense->project?->name ?? null,
            'movements' => $movements->toArray()
        ];

        $this->showRelatedMovementsModal = true;
    }

    public function closeRelatedMovementsModal()
    {
        $this->showRelatedMovementsModal = false;
        $this->relatedMovements = null;
    }

    public function addTestInboundStock()
    {
        // Create or get test inventory
        $inventory = \App\Models\Inventory::where('brand', 'TEST INVENTORY')
            ->where('description', 'FOR HISTORY TESTING')
            ->first();

        if (!$inventory) {
            $inventory = \App\Models\Inventory::create([
                'brand' => 'TEST INVENTORY',
                'description' => 'FOR HISTORY TESTING',
                'category' => 'Laser Room',
                'quantity' => 0,
                'min_stock_level' => 5,
            ]);
        }

        // Add test inbound stock
        $quantity = rand(5, 20);
        $costPerUnit = rand(10, 50) + 0.99;

        $result = $inventory->addInboundStock($quantity, auth()->id(), [
            'cost_per_unit' => $costPerUnit,
            'total_cost' => $quantity * $costPerUnit,
            'supplier' => 'Test Supplier Inc.',
            'location' => 'Laser Room',
            'notes' => 'Test inbound stock addition from History page',
        ]);

        if ($result) {
            session()->flash('success', "✅ Test stock added successfully! {$quantity} units added to {$inventory->brand}. Check the new 'Inbound Added Stock' entry below.");
            // Close any open modal
            $this->showChangeModal = false;
            $this->selectedHistory = null;
        } else {
            session()->flash('error', '❌ Failed to add test stock.');
        }

        // Refresh the component
        $this->dispatch('refreshHistory');
    }

    public function createStockMovementFromHistory($historyId)
    {
        $history = HistoryModel::find($historyId);

        if (!$history || !in_array($history->action, ['Inbound Stock Added', 'Outbound Stock Removed'])) {
            session()->flash('error', 'Invalid history entry for stock movement creation.');
            return;
        }

        // Check if stock movement already exists for this history entry
        $existingMovement = \App\Models\StockMovement::where('inventory_id', $history->model_id)
            ->where('user_id', $history->user_id)
            ->where('created_at', '>=', $history->created_at->subSeconds(5))
            ->where('created_at', '<=', $history->created_at->addSeconds(5))
            ->first();

        if ($existingMovement) {
            session()->flash('error', 'Stock movement already exists for this history entry.');
            return;
        }

        $inventory = \App\Models\Inventory::find($history->model_id);
        if (!$inventory) {
            session()->flash('error', 'Inventory item not found.');
            return;
        }

        $changes = is_array($history->changes) ? $history->changes : json_decode($history->changes ?? '[]', true);
        $oldValues = is_array($history->old_values) ? $history->old_values : json_decode($history->old_values ?? '[]', true);

        $movementType = $history->action === 'Inbound Stock Added' ? 'inbound' : 'outbound';
        $quantity = abs($changes['quantity'] ?? 0);

        if ($quantity <= 0) {
            session()->flash('error', 'Invalid quantity for stock movement.');
            return;
        }

        $previousQuantity = $oldValues['quantity'] ?? 0;
        $newQuantity = $changes['quantity'] ?? 0;

        // For outbound, new_quantity should be the remaining quantity
        if ($movementType === 'outbound') {
            $newQuantity = $previousQuantity - $quantity;
        }

        \App\Models\StockMovement::create([
            'inventory_id' => $inventory->id,
            'user_id' => $history->user_id,
            'movement_type' => $movementType,
            'quantity' => $movementType === 'outbound' ? -$quantity : $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'location' => $inventory->category,
            'supplier' => $changes['supplier'] ?? null,
            'date_received' => isset($changes['date_received']) ? \Carbon\Carbon::parse($changes['date_received']) : null,
            'notes' => $changes['notes'] ?? null,
            'reference' => $changes['reference'] ?? "Created from history #{$history->id}",
        ]);

        session()->flash('success', 'Stock movement created successfully from history entry.');
        $this->dispatch('refreshHistory');
    }

    public function getHighlightedDiff($oldValue, $newValue)
    {
        if (is_array($oldValue) || is_object($oldValue) || is_array($newValue) || is_object($newValue)) {
            return htmlspecialchars($oldValue ?? 'N/A') . ' → ' . htmlspecialchars($newValue ?? 'N/A');
        }

        if (!is_string($oldValue) || !is_string($newValue)) {
            return htmlspecialchars($oldValue ?? 'N/A') . ' → ' . htmlspecialchars($newValue ?? 'N/A');
        }

        // Simple word-based diff for strings
        $oldWords = explode(' ', $oldValue);
        $newWords = explode(' ', $newValue);

        $diff = [];
        $i = 0;
        $j = 0;

        while ($i < count($oldWords) || $j < count($newWords)) {
            if ($i < count($oldWords) && $j < count($newWords) && $oldWords[$i] === $newWords[$j]) {
                $diff[] = htmlspecialchars($oldWords[$i]);
                $i++;
                $j++;
            } elseif ($i < count($oldWords)) {
                // Removed word
                $diff[] = '<strike class="bg-red-100 text-red-800 px-1 rounded border border-red-300">' . htmlspecialchars($oldWords[$i]) . '</strike>';
                $i++;
            } else {
                // Added word
                $diff[] = '<span class="bg-green-100 text-green-800 px-1 rounded border border-green-300">' . htmlspecialchars($newWords[$j]) . '</span>';
                $j++;
            }
        }

        return implode(' ', $diff);
    }

    public function render()
    {
        return view('livewire.history', [
            'histories' => $this->getHistoriesProperty(),
            'summary' => $this->getSummaryProperty()
        ]);
    }
}
