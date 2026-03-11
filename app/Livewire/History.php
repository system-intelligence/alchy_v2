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

        $query = HistoryModel::with([
            'user',
            'approvalRelation' => function ($q) {
                $q->with(['requester', 'reviewer', 'inventory', 'expense', 'expense.client', 'expense.project']);
            },
            'expenseRelation' => function ($q) {
                $q->with(['client', 'project', 'inventory']);
            },
            'projectRelation' => function ($q) {
                $q->with(['client']);
            },
            'clientRelation',
            'inventoryRelation'
        ]);

        // Apply role-based filtering
        if ($user->role === 'system_admin') {
            // System admins see ALL histories EXCEPT developer history
            $query->whereNotIn('user_id', function ($subQuery) use ($user) {
                $subQuery->select('id')->from('users')->where('role', 'developer');
            });
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
                  // Search in JSON changes fields (case-insensitive)
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.reference_code'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.project'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.project_name'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.project_reference_code'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.client'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.client_name'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.material'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.material_brand'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.material_description'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.material_category'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.status'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.approval_status'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.release_type'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.quantity'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.quantity_requested'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.cost_per_unit'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.total_cost'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.price'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.unit_price'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.amount'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.subtotal'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.grand_total'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.overall_cost'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.budget'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.actual_cost'))) LIKE LOWER(?)", [$searchTerm])
                  // Name fields
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.created_by'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.updated_by'))) LIKE LOWER(?)", [$searchTerm])
                  // Other fields
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.notes'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.description'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.category'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.branch'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.contact_person'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.requester'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.requested_by'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.reviewer'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.approved_by'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.reason'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.review_notes'))) LIKE LOWER(?)", [$searchTerm])
                  // Project-specific fields
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.job_type'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.warranty_until'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.start_date'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(changes, '$.target_date'))) LIKE LOWER(?)", [$searchTerm])
                  // Search in old_values JSON (case-insensitive)
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.reference_code'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.project'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.client'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.status'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.quantity'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.cost_per_unit'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.total_cost'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.price'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.unit_price'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.amount'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.budget'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.overall_cost'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.actual_cost'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.category'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.branch'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.notes'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.description'))) LIKE LOWER(?)", [$searchTerm])
                  // Project-specific fields in old_values
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.job_type'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.warranty_until'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.start_date'))) LIKE LOWER(?)", [$searchTerm])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(old_values, '$.target_date'))) LIKE LOWER(?)", [$searchTerm])
                  // Search by date
                  ->orWhereRaw("DATE_FORMAT(created_at, '%M %d, %Y') LIKE ?", [$searchTerm])
                  ->orWhereRaw("DATE_FORMAT(created_at, '%M %d, %Y - %h:%i %p') LIKE ?", [$searchTerm])
                  ->orWhereRaw("DATE(created_at) LIKE ?", [$searchTerm])
                  // Search by formatted ID (e.g., 01202026-103028)
                  ->orWhereRaw("DATE_FORMAT(created_at, '%m%d%Y-%H%i%s') LIKE ?", [$searchTerm])
                  ->orWhereRaw("DATE_FORMAT(created_at, '%m%d%Y-%H%i') LIKE ?", [$searchTerm])
                  ->orWhereRaw("DATE_FORMAT(created_at, '%m%d%Y') LIKE ?", [$searchTerm])
                  // Search by user name (case-insensitive via collation)
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'like', $searchTerm);
                  })
                  // Search in related project data
                  ->orWhere(function ($projectQuery) use ($searchTerm) {
                      $projectQuery->where('model', 'project')
                          ->whereHas('projectRelation', function ($p) use ($searchTerm) {
                              $p->where('name', 'like', $searchTerm)
                                ->orWhere('reference_code', 'like', $searchTerm)
                                ->orWhere('job_type', 'like', $searchTerm)
                                ->orWhere('status', 'like', $searchTerm);
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
                                ->orWhere('category', 'like', $searchTerm)
                                ->orWhere('unit', 'like', $searchTerm);
                          });
                  })
                  // Search in related expense inventory data
                  ->orWhere(function ($expenseInventoryQuery) use ($searchTerm) {
                      $expenseInventoryQuery->where('model', 'expense')
                          ->whereHas('expenseRelation.inventory', function ($i) use ($searchTerm) {
                              $i->where('brand', 'like', $searchTerm)
                                ->orWhere('description', 'like', $searchTerm)
                                ->orWhere('category', 'like', $searchTerm)
                                ->orWhere('unit', 'like', $searchTerm);
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
                                ->orWhere('description', 'like', $searchTerm)
                                ->orWhere('category', 'like', $searchTerm)
                                ->orWhere('unit', 'like', $searchTerm);
                          })
                          ->orWhereHas('approvalRelation.expense.inventory', function ($i) use ($searchTerm) {
                              $i->where('brand', 'like', $searchTerm)
                                ->orWhere('description', 'like', $searchTerm)
                                ->orWhere('category', 'like', $searchTerm)
                                ->orWhere('unit', 'like', $searchTerm);
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
        
        // Get expense data
        $expensesQuery = \App\Models\Expense::query();
        
        // Get history count data based on user role
        $historiesQuery = HistoryModel::query();

        if ($user->role === 'system_admin') {
            // System admins see ALL histories EXCEPT developer history
            $historiesQuery->whereNotIn('user_id', function ($subQuery) use ($user) {
                $subQuery->select('id')->from('users')->where('role', 'developer');
            });
            // Admins see all expenses
        } elseif ($user->role === 'user') {
            // Users see their own histories + all approval request histories
            $historiesQuery->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('model', 'MaterialReleaseApproval');
            });
            // Users see only their expenses
            $expensesQuery->where('user_id', $user->id);
        } else {
            // Developers only see their own histories
            $historiesQuery->where('user_id', $user->id);
            // Developers see only their own expenses
            $expensesQuery->where('user_id', $user->id);
        }

        // Calculate expense statistics
        $totalSpend = (float) $expensesQuery->sum('total_cost');
        $expensesCount = $expensesQuery->count();
        
        // This month expenses
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $monthSpend = (float) $expensesQuery->clone()
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_cost');
        
        // Average per expense
        $averagePerExpense = $expensesCount > 0 ? round($totalSpend / $expensesCount, 2) : 0;
        
        // Calculate history counts
        $totalCount = $historiesQuery->count();
        $monthCount = (clone $historiesQuery)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        return [
            'total_spend' => $totalSpend,
            'month_spend' => $monthSpend,
            'average_per_expense' => $averagePerExpense,
            'expenses_logged' => $expensesCount,
            'total_logs' => $totalCount,
            'month_logs' => $monthCount,
        ];
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function showChangeDetails($historyId)
    {
        $this->selectedHistory = HistoryModel::with([
            'user',
            'approvalRelation' => function ($q) {
                $q->with(['requester', 'reviewer', 'inventory', 'expense', 'expense.client', 'expense.project']);
            },
            'expenseRelation' => function ($q) {
                $q->with(['client', 'project', 'inventory']);
            },
            'projectRelation' => function ($q) {
                $q->with(['client']);
            },
            'clientRelation',
            'inventoryRelation'
        ])->find($historyId);
        $this->showChangeModal = true;
    }

    public function closeChangeModal()
    {
        $this->showChangeModal = false;
        $this->selectedHistory = null;
    }

    public function openRelatedMovementsModal($approvalId)
    {
        // Load approval with all relationships
        $approval = \App\Models\MaterialReleaseApproval::with('expense.client', 'expense.project', 'client', 'project', 'inventory', 'requester')->find($approvalId);

        if (!$approval) {
            return;
        }

        // Determine client and project from expense or directly from approval
        $clientId = null;
        $projectId = null;
        $clientName = null;
        $projectName = null;

        // First try to get from expense
        if ($approval->expense) {
            $clientId = $approval->expense->client_id;
            $projectId = $approval->expense->project_id;
            $clientName = $approval->expense->client?->name;
            $projectName = $approval->expense->project?->name;
        } else {
            // Try to get directly from approval
            $clientId = $approval->client_id;
            $projectId = $approval->project_id;
            
            if ($approval->client_id) {
                $clientModel = $approval->client()->first();
                $clientName = $clientModel ? $clientModel->name : null;
            }
            
            if ($approval->project_id) {
                $projectModel = $approval->project()->first();
                $projectName = $projectModel ? $projectModel->name : null;
            }
        }

        // If no client found yet, try to get from history changes
        if (empty($clientName)) {
            $history = \App\Models\History::where('model', 'MaterialReleaseApproval')
                ->where('model_id', $approvalId)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($history && $history->changes) {
                $changes = is_array($history->changes) ? $history->changes : 
                    (is_string($history->changes) ? json_decode($history->changes, true) ?? [] : []);
                
                $clientName = $changes['client'] ?? $changes['client_name'] ?? null;
                $projectName = $changes['project'] ?? $changes['project_name'] ?? null;
            }
        }

        // Get requester name as fallback
        $requesterName = $approval->requester ? $approval->requester->name : 'Unknown';

        $movements = collect();

        // If we have expense, get movements by expense
        if ($approval->expense) {
            $relatedExpenses = \App\Models\Expense::where('client_id', $clientId)
                ->where('project_id', $projectId)
                ->pluck('id');

            $movements = \App\Models\StockMovement::whereIn('reference', $relatedExpenses->map(fn($id) => 'expense_' . $id))
                ->with(['inventory', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // If no movements found, try to get by approval reference
        if ($movements->isEmpty()) {
            $movements = \App\Models\StockMovement::where('reference', 'approval_' . $approval->id)
                ->with(['inventory', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // If still no movements, get by inventory and requester
        if ($movements->isEmpty() && $approval->inventory) {
            $movements = \App\Models\StockMovement::where('inventory_id', $approval->inventory_id)
                ->where('user_id', $approval->requested_by)
                ->where('movement_type', 'outbound')
                ->with(['inventory', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        }

        $this->relatedMovements = [
            'client_name' => $clientName ?? $requesterName,
            'project_name' => $projectName,
            'movements' => $movements
        ];

        $this->showRelatedMovementsModal = true;
    }

    public function closeRelatedMovementsModal()
    {
        $this->showRelatedMovementsModal = false;
        $this->relatedMovements = null;
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
