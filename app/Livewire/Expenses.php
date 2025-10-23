<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Enums\InventoryStatus;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\History;
use App\Models\Project;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Expenses extends Component
{
    use WithFileUploads;

    public $clients;
    public $selectedClient = null;
    public $clientExpenses = [];

    // Main tab navigation
    public $activeMainTab = 'clients';

    // Filters & search
    public $search = '';
    public $dateFrom = null;
    public $dateTo = null;
    public $clientFilter = '';
    public $projectFilter = '';
    public $clientStatusFilter = '';
    public $projectStatusFilter = '';

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
    public $calendarEvents = [];

    // Client Deletion modal
    public $showDeleteClientModal = false;
    public $deleteClientId = null;
    public $deletePassword = '';

    // Project Management modal state
    public $showProjectModal = false;
    public $editingProject = false;
    public $projectId = null;
    public $projectClientName = '';
    public $projectClientId = '';
    public $projectName = '';
    public $projectReference = '';
    public $projectJobType = '';
    public $projectStatus = 'planning';
    public $projectStartDate = '';
    public $projectTargetDate = '';
    public $projectWarrantyUntil = '';
    public $projectNotes = '';

    // Project detail viewer
    public $showProjectDetailModal = false;
    public $selectedProject = null;
    public $selectedProjectMetrics = [];
    public $selectedProjectExpenses = [];
    public $selectedProjectBreakdown = [];

    // Project manage modal
    public $showProjectManageModal = false;
    public $managingProject = null;
    public $manageProjectMetrics = [];
    public $manageProjectNotesInput = '';
    public $manageProjectStatus = 'planning';
    public $manageProjectStartDate = '';
    public $manageProjectTargetDate = '';
    public $manageProjectWarrantyUntil = '';
    public $manageInventoryOptions = [];
    public $manageRecentReleases = [];
    public $manageActiveTab = 'release';
    public $manageExpenseNotesSupported = false;
    public $manageReleaseItems = [];
    public $manageReleaseDate = '';
    public $manageReleaseTime = '';
    public $manageReleaseNotes = '';
    public $manageReleaseDuplicateNotice = '';

    // Project notes tab
    public $manageProjectNotesList = [];
    public $newNoteContent = '';
    public $newNoteImages = [];
    public $editingNoteId = null;
    public $editNoteContent = '';

    protected static ?bool $expenseNotesSupported = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => null],
        'dateTo' => ['except' => null],
        'clientFilter' => ['except' => ''],
        'projectFilter' => ['except' => ''],
        'clientStatusFilter' => ['except' => ''],
        'projectStatusFilter' => ['except' => ''],
    ];



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
        $this->clients = Client::with(['expenses', 'projects'])->orderBy('name')->get();
    }

    public function viewExpenses($clientId)
    {
        $this->selectedClient = Client::with(['expenses.project', 'projects'])->find($clientId);
        $query = Expense::where('client_id', $clientId)->with(['inventory', 'project']);
        if ($this->filterByMonth && $this->calendarYear && $this->calendarMonth) {
            $query->whereYear('released_at', $this->calendarYear)->whereMonth('released_at', $this->calendarMonth);
        } elseif ($this->filterDate) {
            $query->whereDate('released_at', $this->filterDate);
        }
        $this->clientExpenses = $query->latest()->get();
        $this->refreshCalendarEvents();
    }


    public function closeModal()
    {
        // Close all modals
        $this->selectedClient = null;
        $this->clientExpenses = [];
        $this->showClientModal = false;
        $this->showDeleteClientModal = false;
        $this->showProjectModal = false;
        $this->showProjectDetailModal = false;
        $this->showProjectManageModal = false;
        $this->selectedProject = null;
        $this->selectedProjectMetrics = [];
        $this->selectedProjectExpenses = [];
        $this->selectedProjectBreakdown = [];
        $this->calendarEvents = [];
        $this->resetClientForm();
        $this->resetDeleteForm();
        $this->resetProjectForm();
        $this->resetProjectManageState();
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

    public function openProjectModal(?int $projectId = null, ?int $clientId = null): void
    {
        $this->ensureAdmin();
        $this->resetProjectForm();

        if ($clientId) {
            $this->projectClientId = (string) $clientId;
            $client = $this->clients->firstWhere('id', $clientId);
            $this->projectClientName = $client->name ?? '';
        }

        if ($projectId) {
            $project = Project::with('client')->find($projectId);
            if (!$project) {
                session()->flash('message', 'Project not found.');
                return;
            }

            $this->editingProject = true;
            $this->projectId = $projectId;
            $this->projectClientId = (string) $project->client_id;
            $this->projectClientName = $project->client?->name ?? '';
            $this->projectName = $project->name;
            $this->projectReference = $project->reference_code ?? '';
            $this->projectJobType = $project->job_type ?? '';
            $this->projectStatus = in_array($project->status, Project::STATUSES, true) ? $project->status : 'planning';
            $this->projectStartDate = $project->start_date?->format('Y-m-d') ?? '';
            $this->projectTargetDate = $project->target_date?->format('Y-m-d') ?? '';
            $this->projectWarrantyUntil = $project->warranty_until?->format('Y-m-d') ?? '';
            $this->projectNotes = $project->notes ?? '';
        } elseif (!$this->projectClientId && $this->clients && $this->clients->count()) {
            $defaultClient = optional($this->clients->first());
            $this->projectClientId = (string) $defaultClient?->id;
            $this->projectClientName = $defaultClient?->name ?? '';
        }

        $this->showProjectModal = true;
    }

    public function closeProjectModal(): void
    {
        $this->showProjectModal = false;
        $this->resetProjectForm();
    }

    protected function resetProjectForm(): void
    {
        $this->editingProject = false;
        $this->projectId = null;
        $this->projectClientName = '';
        $this->projectClientId = '';
        $this->projectName = '';
        $this->projectReference = '';
        $this->projectJobType = '';
        $this->projectStatus = 'planning';
        $this->projectStartDate = '';
        $this->projectTargetDate = '';
        $this->projectWarrantyUntil = '';
        $this->projectNotes = '';
    }

    public function closeProjectManageModal(): void
    {
        $this->showProjectManageModal = false;
        $this->resetProjectManageState();
    }

    protected function resetProjectManageState(): void
    {
        $this->managingProject = null;
    $this->manageProjectMetrics = [];
        $this->manageProjectNotesInput = '';
        $this->manageProjectStatus = 'planning';
        $this->manageProjectStartDate = '';
        $this->manageProjectTargetDate = '';
        $this->manageProjectWarrantyUntil = '';
        $this->manageInventoryOptions = [];
        $this->manageRecentReleases = [];
        $this->manageActiveTab = 'release';
        $this->manageExpenseNotesSupported = false;
        $this->manageReleaseDuplicateNotice = '';
        $this->resetManageReleaseForm();
    }

    protected function resetManageReleaseForm(): void
    {
        $now = now()->setTimezone('Asia/Manila');
        $this->manageReleaseItems = [[
            'inventory_id' => '',
            'quantity' => 1,
            'cost_per_unit' => '',
        ]];
        $this->manageReleaseDate = $now->format('Y-m-d');
        $this->manageReleaseTime = $now->format('H:i');
        $this->manageReleaseNotes = '';
        $this->manageReleaseDuplicateNotice = '';
    }

    public function addManageReleaseItem(): void
    {
        $this->manageReleaseItems[] = [
            'inventory_id' => '',
            'quantity' => 1,
            'cost_per_unit' => '',
        ];
    }

    public function removeManageReleaseItem(int $index): void
    {
        if (count($this->manageReleaseItems) <= 1) {
            return;
        }

        unset($this->manageReleaseItems[$index]);
        $this->manageReleaseItems = array_values($this->manageReleaseItems);
    }

    public function clearManageReleaseNotice(): void
    {
        $this->manageReleaseDuplicateNotice = '';
    }

    public function updatedManageReleaseItems($value, $key): void
    {
        if (!Str::endsWith($key, '.inventory_id')) {
            return;
        }

        $segments = explode('.', $key);
        if (count($segments) < 3) {
            return;
        }

        $currentIndex = (int) ($segments[1] ?? 0);
        $currentItem = $this->manageReleaseItems[$currentIndex] ?? null;

        if (!$currentItem) {
            return;
        }

        $inventoryId = (int) ($currentItem['inventory_id'] ?? 0);
        if ($inventoryId <= 0) {
            return;
        }

        $mergedQuantity = max(1, (int) ($currentItem['quantity'] ?? 1));
        $costPerUnit = $currentItem['cost_per_unit'] ?? '';
        $duplicatesFound = false;

        foreach ($this->manageReleaseItems as $index => $item) {
            if ($index === $currentIndex) {
                continue;
            }

            if ((int) ($item['inventory_id'] ?? 0) === $inventoryId) {
                $duplicatesFound = true;
                $mergedQuantity += max(0, (int) ($item['quantity'] ?? 0));

                if ($costPerUnit === '' && $item['cost_per_unit'] !== '') {
                    $costPerUnit = $item['cost_per_unit'];
                }

                unset($this->manageReleaseItems[$index]);
            }
        }

        if ($duplicatesFound) {
            $this->manageReleaseItems[$currentIndex]['quantity'] = $mergedQuantity;
            $this->manageReleaseItems[$currentIndex]['cost_per_unit'] = $costPerUnit;
            $this->manageReleaseItems = array_values(array_map(function ($item) {
                return [
                    'inventory_id' => $item['inventory_id'] ?? '',
                    'quantity' => isset($item['quantity']) ? max(1, (int) $item['quantity']) : 1,
                    'cost_per_unit' => $item['cost_per_unit'] ?? '',
                ];
            }, $this->manageReleaseItems));

            $this->manageReleaseDuplicateNotice = 'Merged duplicate materials. Adjust the quantity instead of adding the same item twice.';
        }
    }

    public function closeProjectDetailModal(): void
    {
        $this->showProjectDetailModal = false;
        $this->selectedProject = null;
        $this->selectedProjectMetrics = [];
        $this->selectedProjectExpenses = [];
        $this->selectedProjectBreakdown = [];
    }

    public function updatedProjectClientName($value): void
    {
        if (!$value) {
            $this->projectClientId = '';
            return;
        }

        $currentClient = $this->clients instanceof Collection
            ? $this->clients->firstWhere('id', (int) $this->projectClientId)
            : collect($this->clients)->firstWhere('id', (int) $this->projectClientId);

        if ($currentClient && $currentClient->name === $value) {
            return;
        }

        $matchingClients = $this->clients instanceof Collection
            ? $this->clients->where('name', $value)
            : collect($this->clients)->where('name', $value);

        $this->projectClientId = '';

        if ($matchingClients->count() === 1) {
            $this->projectClientId = (string) $matchingClients->first()->id;
        }
    }

    public function updatedProjectClientId($value): void
    {
        if (!$value) {
            return;
        }

        $client = $this->clients instanceof Collection
            ? $this->clients->firstWhere('id', (int) $value)
            : collect($this->clients)->firstWhere('id', (int) $value);

        if ($client && $client->name !== $this->projectClientName) {
            $this->projectClientName = $client->name;
        }
    }

    public function generateProjectReference(): void
    {
        $client = null;

        if ($this->projectClientId) {
            $client = $this->clients instanceof Collection
                ? $this->clients->firstWhere('id', (int) $this->projectClientId)
                : collect($this->clients)->firstWhere('id', (int) $this->projectClientId);
        } elseif ($this->projectClientName) {
            $client = $this->clients instanceof Collection
                ? $this->clients->where('name', $this->projectClientName)->first()
                : collect($this->clients)->where('name', $this->projectClientName)->first();
        }

        $nameSegment = 'PRJ';
        $branchSegment = 'GEN';

        if ($client) {
            $nameSegment = (string) Str::of($client->name)
                ->upper()
                ->replaceMatches('/[^A-Z0-9]/', '')
                ->substr(0, 4)
                ->padRight(3, 'X');

            if ($client->branch) {
                $branchSegment = (string) Str::of($client->branch)
                    ->upper()
                    ->replaceMatches('/[^A-Z0-9]/', '')
                    ->substr(0, 4)
                    ->padRight(3, 'X');
            }
        }

        $timestamp = now()->format('ymd');
        $sequence = random_int(100, 999);

        $this->projectReference = sprintf('%s-%s-%s-%s', $nameSegment, $branchSegment, $timestamp, $sequence);
    }

    public function viewProject(int $projectId): void
    {
        $project = Project::with(['client', 'expenses' => function ($query) {
            $query->with('inventory')->orderByDesc('released_at');
        }])->find($projectId);

        if (!$project) {
            session()->flash('message', 'Project not found.');
            return;
        }

        $total = $project->expenses->sum('total_cost');
        $count = $project->expenses->count();
        $firstRelease = $project->expenses->min('released_at');
        $lastRelease = $project->expenses->max('released_at');

        $this->selectedProject = [
            'id' => $project->id,
            'name' => $project->name,
            'reference_code' => $project->reference_code,
            'status' => $project->status,
            'notes' => $project->notes,
            'start_date' => $project->start_date?->toDateString(),
            'target_date' => $project->target_date?->toDateString(),
            'warranty_until' => $project->warranty_until?->toDateString(),
            'client' => [
                'id' => $project->client->id,
                'name' => $project->client->name,
                'branch' => $project->client->branch,
            ],
        ];

        $this->selectedProjectMetrics = [
            'total' => $total,
            'count' => $count,
            'average' => $count ? round($total / $count, 2) : 0,
            'first_release' => $firstRelease?->toDateTimeString(),
            'last_release' => $lastRelease?->toDateTimeString(),
        ];

        $this->selectedProjectExpenses = $project->expenses->map(function ($expense) {
            return [
                'id' => $expense->id,
                'released_at' => $expense->released_at?->toIso8601String(),
                'total_cost' => $expense->total_cost,
                'quantity_used' => $expense->quantity_used,
                'cost_per_unit' => $expense->cost_per_unit,
                'notes' => $expense->notes,
                'inventory' => [
                    'brand' => optional($expense->inventory)->brand ?? 'Unknown Item',
                    'description' => optional($expense->inventory)->description,
                    'category' => optional($expense->inventory)->category,
                ],
            ];
        })->toArray();

        $this->selectedProjectBreakdown = $project->expenses
            ->groupBy(fn ($expense) => optional($expense->inventory)->category ?? 'Uncategorized')
            ->map(function ($group, $category) {
                return [
                    'category' => $category,
                    'total' => $group->sum('total_cost'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->toArray();

        $this->showProjectDetailModal = true;
    }

    public function openProjectManager(int $projectId): void
    {
        $this->ensureAdmin();

        if (!$this->hydrateManageProject($projectId)) {
            session()->flash('message', 'Project not found.');
            return;
        }

        $this->resetManageReleaseForm();
        $this->showProjectManageModal = true;
    }

    protected function hydrateManageProject(int $projectId, bool $preserveTab = false): bool
    {
        $project = Project::with(['client', 'expenses.inventory'])->find($projectId);
        if (!$project) {
            return false;
        }

        $previousTab = $this->manageActiveTab;

        $total = $project->expenses->sum('total_cost');
        $count = $project->expenses->count();
        $firstRelease = $project->expenses->min('released_at');
        $lastRelease = $project->expenses->max('released_at');

        $this->managingProject = [
            'id' => $project->id,
            'name' => $project->name,
            'reference_code' => $project->reference_code,
            'status' => $project->status,
            'client' => [
                'id' => $project->client->id,
                'name' => $project->client->name,
                'branch' => $project->client->branch,
            ],
        ];

        $this->manageProjectMetrics = [
            'total' => $total,
            'count' => $count,
            'average' => $count ? round($total / $count, 2) : 0,
            'first_release' => $firstRelease?->toDateTimeString(),
            'last_release' => $lastRelease?->toDateTimeString(),
        ];

        $this->manageProjectNotesInput = $project->notes ?? '';
        $this->manageProjectStatus = in_array($project->status, Project::STATUSES, true) ? $project->status : 'planning';
        $this->manageProjectStartDate = $project->start_date?->format('Y-m-d') ?? '';
        $this->manageProjectTargetDate = $project->target_date?->format('Y-m-d') ?? '';
        $this->manageProjectWarrantyUntil = $project->warranty_until?->format('Y-m-d') ?? '';
        $this->manageExpenseNotesSupported = $this->expenseNotesEnabled();

        $this->manageInventoryOptions = Inventory::orderBy('brand')
            ->get()
            ->map(function (Inventory $inventory) {
                $status = $inventory->status instanceof InventoryStatus
                    ? $inventory->status->value
                    : $inventory->status;

                return [
                    'id' => $inventory->id,
                    'label' => trim(sprintf('%s — %s', $inventory->brand, $inventory->description ?? '')),
                    'quantity' => $inventory->quantity,
                    'status' => $status,
                ];
            })
            ->toArray();

        $this->manageRecentReleases = $project->expenses
            ->sortByDesc(fn ($expense) => $expense->released_at?->timestamp ?? 0)
            ->take(10)
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'released_at' => $expense->released_at?->toIso8601String(),
                    'quantity' => $expense->quantity_used,
                    'total' => $expense->total_cost,
                    'cost_per_unit' => $expense->cost_per_unit,
                    'inventory' => [
                        'brand' => $expense->inventory->brand ?? 'Unknown Item',
                        'description' => $expense->inventory->description ?? null,
                    ],
                ];
            })
            ->values()
            ->toArray();

        if (!$preserveTab) {
            $this->manageActiveTab = 'release';
        } else {
            $this->manageActiveTab = $previousTab;
        }

        $this->loadProjectNotes();

        return true;
    }

    public function recordProjectRelease(): void
    {
        $this->ensureAdmin();

        if (!$this->managingProject) {
            session()->flash('message', 'Select a project before releasing materials.');
            return;
        }

        $this->validate([
            'manageReleaseItems' => 'required|array|min:1',
            'manageReleaseItems.*.inventory_id' => 'required|exists:inventories,id',
            'manageReleaseItems.*.quantity' => 'required|integer|min:1',
            'manageReleaseItems.*.cost_per_unit' => 'required|numeric|min:0',
            'manageReleaseDate' => 'required|date',
            'manageReleaseTime' => 'required',
            'manageReleaseNotes' => 'nullable|string|max:2000',
        ], [], [
            'manageReleaseItems.*.inventory_id' => 'inventory item',
            'manageReleaseItems.*.quantity' => 'quantity',
            'manageReleaseItems.*.cost_per_unit' => 'cost per unit',
            'manageReleaseDate' => 'release date',
            'manageReleaseTime' => 'release time',
        ]);

        $project = Project::with('client')->find($this->managingProject['id']);
        if (!$project) {
            session()->flash('message', 'Project not found.');
            return;
        }

        $items = collect($this->manageReleaseItems)
            ->map(fn ($item) => [
                'inventory_id' => isset($item['inventory_id']) ? (int) $item['inventory_id'] : 0,
                'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : 0,
                'cost_per_unit' => isset($item['cost_per_unit']) ? (float) $item['cost_per_unit'] : 0.0,
            ])
            ->filter(fn ($item) => $item['inventory_id'] > 0)
            ->values();

        if ($items->isEmpty()) {
            $this->addError('manageReleaseItems', 'Add at least one material before saving.');
            return;
        }

        $inventories = Inventory::whereIn('id', $items->pluck('inventory_id'))->get()->keyBy('id');
        $requestedTotals = [];

        foreach ($items as $index => $item) {
            $inventory = $inventories->get($item['inventory_id']);
            if (!$inventory) {
                $this->addError('manageReleaseItems.' . $index . '.inventory_id', 'Selected inventory item not found.');
                return;
            }

            $requestedTotals[$inventory->id] = ($requestedTotals[$inventory->id] ?? 0) + $item['quantity'];

            if ($requestedTotals[$inventory->id] > $inventory->quantity) {
                $this->addError('manageReleaseItems.' . $index . '.quantity', 'Quantity exceeds available stock (' . $inventory->quantity . ').');
                return;
            }
        }

        $releasedAt = Carbon::parse(
            $this->manageReleaseDate . ' ' . $this->manageReleaseTime,
            'Asia/Manila'
        )->setTimezone('UTC');

        DB::beginTransaction();

        try {
            foreach ($items as $index => $item) {
                $inventory = $inventories->get($item['inventory_id']);
                if (!$inventory) {
                    throw new \RuntimeException('Inventory not found during release.');
                }

                $totalCost = round($item['quantity'] * $item['cost_per_unit'], 2);

                $expense = Expense::create([
                    'client_id' => $project->client_id,
                    'inventory_id' => $inventory->id,
                    'project_id' => $project->id,
                    'quantity_used' => $item['quantity'],
                    'cost_per_unit' => $item['cost_per_unit'],
                    'total_cost' => $totalCost,
                    'released_at' => $releasedAt,
                ]);

                if ($this->manageReleaseNotes && $this->expenseNotesEnabled()) {
                    $expense->notes = $this->manageReleaseNotes;
                    $expense->save();
                }

                $newQuantity = max(0, $inventory->quantity - $item['quantity']);
                $newStatus = $newQuantity <= 0
                    ? InventoryStatus::OUT_OF_STOCK
                    : ($newQuantity <= $inventory->min_stock_level
                        ? InventoryStatus::CRITICAL
                        : InventoryStatus::NORMAL);

                $inventory->update([
                    'quantity' => $newQuantity,
                    'status' => $newStatus->value,
                ]);

                // Update in-memory snapshot for subsequent iterations when same inventory repeats.
                $inventory->quantity = $newQuantity;
                $inventory->status = $newStatus->value;

                History::create([
                    'user_id' => auth()->id(),
                    'action' => 'create',
                    'model' => 'expense',
                    'model_id' => $expense->id,
                    'changes' => array_filter([
                        'client_id' => $expense->client_id,
                        'inventory_id' => $expense->inventory_id,
                        'project_id' => $expense->project_id,
                        'quantity_used' => $expense->quantity_used,
                        'cost_per_unit' => $expense->cost_per_unit,
                        'total_cost' => $expense->total_cost,
                        'notes' => $this->manageReleaseNotes ? $this->manageReleaseNotes : null,
                    ], fn ($value) => $value !== null),
                ]);

                History::create([
                    'user_id' => auth()->id(),
                    'action' => 'update',
                    'model' => 'inventory',
                    'model_id' => $inventory->id,
                    'changes' => [
                        'quantity' => $newQuantity,
                        'status' => $newStatus->value,
                    ],
                ]);
            }

            DB::commit();

            $this->resetManageReleaseForm();
            $this->hydrateManageProject($project->id, preserveTab: true);
            $this->loadClients();

            if ($this->selectedClient && $this->selectedClient->id === $project->client_id) {
                $this->viewExpenses($project->client_id);
            }

            session()->flash('message', $items->count() . ' material release(s) recorded successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();
            \Log::error('Project release failed: ' . $exception->getMessage());
            $this->addError('manageReleaseItems', 'Unable to record releases at this time.');
        }
    }

    public function updateManageProjectDetails(): void
    {
        $this->ensureAdmin();

        if (!$this->managingProject) {
            session()->flash('message', 'Select a project before saving changes.');
            return;
        }

        $this->validate([
            'manageProjectStatus' => 'required|in:' . implode(',', Project::STATUSES),
            'manageProjectStartDate' => 'nullable|date',
            'manageProjectTargetDate' => 'nullable|date|after_or_equal:manageProjectStartDate',
            'manageProjectWarrantyUntil' => 'nullable|date|after_or_equal:manageProjectTargetDate',
            'manageProjectNotesInput' => 'nullable|string|max:2000',
        ]);

        $project = Project::find($this->managingProject['id']);
        if (!$project) {
            session()->flash('message', 'Project not found.');
            return;
        }

        $project->update([
            'status' => $this->manageProjectStatus,
            'start_date' => $this->manageProjectStartDate ?: null,
            'target_date' => $this->manageProjectTargetDate ?: null,
            'warranty_until' => $this->manageProjectWarrantyUntil ?: null,
            'notes' => $this->manageProjectNotesInput ?: null,
        ]);

        History::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model' => 'project',
            'model_id' => $project->id,
            'changes' => [
                'status' => $this->manageProjectStatus,
                'start_date' => $this->manageProjectStartDate ?: null,
                'target_date' => $this->manageProjectTargetDate ?: null,
                'warranty_until' => $this->manageProjectWarrantyUntil ?: null,
                'notes' => $this->manageProjectNotesInput ?: null,
            ],
        ]);

        $this->hydrateManageProject($project->id, preserveTab: true);
        $this->loadClients();

        if ($this->selectedClient && $this->selectedClient->id === $project->client_id) {
            $this->viewExpenses($project->client_id);
        }

        session()->flash('message', 'Project details updated successfully.');
    }

    public function loadProjectNotes(): void
    {
        if (!$this->managingProject) {
            $this->manageProjectNotesList = [];
            return;
        }

        $notes = \App\Models\ProjectNote::where('project_id', $this->managingProject['id'])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'content' => $note->content,
                    'images' => $note->images ?? [],
                    'user_name' => $note->user->name,
                    'created_at' => $note->created_at->setTimezone('Asia/Manila')->format('M d, Y · h:i A'),
                    'created_at_human' => $note->created_at->diffForHumans(),
                ];
            })->toArray();

        $this->manageProjectNotesList = $notes;
    }

    public function saveProjectNote(): void
    {
        $this->ensureAdmin();

        if (!$this->managingProject) {
            session()->flash('message', 'Select a project before adding notes.');
            return;
        }

        $this->validate([
            'newNoteContent' => 'required|string|max:5000',
        ]);

        \App\Models\ProjectNote::create([
            'project_id' => $this->managingProject['id'],
            'user_id' => auth()->id(),
            'content' => $this->newNoteContent,
            'images' => $this->newNoteImages,
        ]);

        History::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model' => 'project_note',
            'model_id' => $this->managingProject['id'],
            'changes' => [
                'content' => $this->newNoteContent,
                'has_images' => !empty($this->newNoteImages),
            ],
        ]);

        $this->newNoteContent = '';
        $this->newNoteImages = [];
        $this->loadProjectNotes();

        session()->flash('message', 'Note added successfully.');
    }

    public function deleteProjectNote(int $noteId): void
    {
        $this->ensureAdmin();

        $note = \App\Models\ProjectNote::find($noteId);
        if (!$note) {
            session()->flash('message', 'Note not found.');
            return;
        }

        if ($note->project_id !== $this->managingProject['id']) {
            session()->flash('message', 'Note does not belong to this project.');
            return;
        }

        $note->delete();

        History::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model' => 'project_note',
            'model_id' => $noteId,
            'changes' => [
                'project_id' => $note->project_id,
            ],
        ]);

        $this->loadProjectNotes();
        session()->flash('message', 'Note deleted successfully.');
    }

    public function downloadProjectReceipt(int $projectId)
    {
        $this->ensureAdmin();

        $project = Project::with(['client', 'expenses.inventory', 'projectNotes.user'])->find($projectId);
        if (!$project) {
            session()->flash('message', 'Project not found.');
            return null;
        }

        $filename = Str::slug($project->reference_code ?: $project->name ?: 'project')
            . '-receipt-' . now()->format('Ymd_His') . '.csv';
        $includeExpenseNotes = $this->expenseNotesEnabled();

        return response()->streamDownload(function () use ($project, $includeExpenseNotes) {
            $handle = fopen('php://output', 'w');
            
            // Use UTF-8 BOM for proper encoding in Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Company Header
            fputcsv($handle, ['ALCHY ENTERPRISE INC.']);
            fputcsv($handle, ['Smart Inventory System']);
            fputcsv($handle, []);
            fputcsv($handle, ['PROJECT RECEIPT & MATERIAL RELEASE REPORT']);
            fputcsv($handle, []);
            fputcsv($handle, []);
            
            // Project Information Section
            fputcsv($handle, ['PROJECT DETAILS', '', '', '', '', '']);
            fputcsv($handle, ['Project Name', $project->name, '', '', '', '']);
            fputcsv($handle, ['Reference Code', $project->reference_code ?: 'N/A', '', '', '', '']);
            fputcsv($handle, ['Status', strtoupper(str_replace('_', ' ', $project->status)), '', '', '', '']);
            fputcsv($handle, []);
            
            // Client Information
            fputcsv($handle, ['CLIENT INFORMATION', '', '', '', '', '']);
            fputcsv($handle, ['Company', $project->client->name, '', '', '', '']);
            fputcsv($handle, ['Branch', $project->client->branch, '', '', '', '']);
            fputcsv($handle, []);
            
            // Project Timeline
            fputcsv($handle, ['PROJECT TIMELINE', '', '', '', '', '']);
            fputcsv($handle, ['Start Date', $project->start_date ? Carbon::parse($project->start_date)->format('F d, Y') : 'Not Set', '', '', '', '']);
            fputcsv($handle, ['Target Completion', $project->target_date ? Carbon::parse($project->target_date)->format('F d, Y') : 'Not Set', '', '', '', '']);
            fputcsv($handle, ['Warranty Until', $project->warranty_until ? Carbon::parse($project->warranty_until)->format('F d, Y') : 'Not Set', '', '', '', '']);
            fputcsv($handle, []);
            
            // Project Notes Section
            if ($project->notes) {
                fputcsv($handle, ['PROJECT OVERVIEW', '', '', '', '', '']);
                $notesLines = explode("\n", $project->notes);
                foreach ($notesLines as $line) {
                    fputcsv($handle, [trim($line), '', '', '', '', '']);
                }
                fputcsv($handle, []);
            }
            
            // Documentation Notes
            if ($project->projectNotes && $project->projectNotes->count() > 0) {
                fputcsv($handle, ['DOCUMENTATION HISTORY', '', '', '', '', '']);
                fputcsv($handle, []);
                fputcsv($handle, ['Date & Time', 'Documented By', 'Notes', '', '', '']);
                
                foreach ($project->projectNotes->sortBy('created_at') as $note) {
                    fputcsv($handle, [
                        $note->created_at->setTimezone('Asia/Manila')->format('M d, Y h:i A'),
                        $note->user->name ?? 'Unknown User',
                        str_replace(["\r\n", "\n", "\r"], ' | ', $note->content),
                        '',
                        '',
                        ''
                    ]);
                }
                fputcsv($handle, []);
                fputcsv($handle, []);
            }

            // Material Releases Section
            fputcsv($handle, ['MATERIAL RELEASES & EXPENSES BREAKDOWN', '', '', '', '', '']);
            fputcsv($handle, []);
            
            // Table header with proper column structure
            $header = ['Date & Time', 'Item Brand', 'Description', 'Quantity', 'Unit Price (₱)', 'Total (₱)'];
            if ($includeExpenseNotes) {
                $header[] = 'Release Notes';
            }
            fputcsv($handle, $header);

            $totalAmount = 0;
            $itemCount = 0;
            
            foreach ($project->expenses->sortBy('released_at') as $expense) {
                $releasedAt = $expense->released_at
                    ? $expense->released_at->setTimezone('Asia/Manila')->format('M d, Y h:i A')
                    : 'Not Specified';

                $row = [
                    $releasedAt,
                    $expense->inventory->brand ?? 'Unknown Item',
                    $expense->inventory->description ?? 'No Description',
                    number_format($expense->quantity_used, 2),
                    number_format($expense->cost_per_unit, 2),
                    number_format($expense->total_cost, 2),
                ];

                if ($includeExpenseNotes) {
                    $row[] = $expense->notes ? str_replace(["\r\n", "\n", "\r"], ' | ', $expense->notes) : '';
                }

                fputcsv($handle, $row);
                $totalAmount += $expense->total_cost;
                $itemCount++;
            }
            
            // Summary Section with spacing
            fputcsv($handle, []);
            fputcsv($handle, []);
            fputcsv($handle, ['FINANCIAL SUMMARY', '', '', '', '', '']);
            fputcsv($handle, []);
            fputcsv($handle, ['Total Line Items', $itemCount, '', '', '', '']);
            fputcsv($handle, ['Total Units Released', number_format($project->expenses->sum('quantity_used'), 2), '', '', '', '']);
            fputcsv($handle, []);
            fputcsv($handle, ['GRAND TOTAL', '', '', '', '', number_format($totalAmount, 2)]);
            fputcsv($handle, []);
            fputcsv($handle, []);
            fputcsv($handle, []);
            
            // Footer Section
            fputcsv($handle, ['Document Information', '', '', '', '', '']);
            fputcsv($handle, ['Generated On', now()->setTimezone('Asia/Manila')->format('F d, Y - h:i A'), '', '', '', '']);
            fputcsv($handle, ['Generated By', auth()->user()->name ?? 'System', '', '', '', '']);
            fputcsv($handle, ['System', 'Alchy Enterprise Inc. - Smart Inventory System', '', '', '', '']);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function expenseNotesEnabled(): bool
    {
        if (static::$expenseNotesSupported !== null) {
            return static::$expenseNotesSupported;
        }

        return static::$expenseNotesSupported = Schema::hasColumn('expenses', 'notes');
    }

    protected function refreshCalendarEvents(): void
    {
        if (!$this->selectedClient) {
            $this->calendarEvents = [];
            return;
        }

        $monthStart = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->startOfMonth();
        $monthEnd = (clone $monthStart)->endOfMonth();

        $events = $this->selectedClient->expenses
            ->filter(fn ($expense) => $expense->released_at && $expense->released_at->between($monthStart, $monthEnd))
            ->sortByDesc('released_at')
            ->groupBy(fn ($expense) => $expense->released_at->day)
            ->map(function ($dayExpenses) {
                return $dayExpenses->map(function ($expense) {
                    return [
                        'id' => $expense->id,
                        'time' => $expense->released_at?->setTimezone('Asia/Manila')->format('h:i A'),
                        'project' => $expense->project?->name,
                        'reference' => $expense->project?->reference_code,
                        'inventory' => $expense->inventory->brand ?? 'Unknown Item',
                        'total' => $expense->total_cost,
                    ];
                })->values();
            });

        $this->calendarEvents = $events->toArray();
    }

    public function saveProject(): void
    {
        $this->ensureAdmin();

        $validated = $this->validate([
            'projectClientId' => 'required|exists:clients,id',
            'projectName' => 'required|string|max:255',
            'projectReference' => 'nullable|string|max:100',
            'projectJobType' => 'required|in:installation,service',
            'projectStatus' => 'required|in:' . implode(',', Project::STATUSES),
            'projectStartDate' => 'nullable|date',
            'projectTargetDate' => 'nullable|date|after_or_equal:projectStartDate',
            'projectWarrantyUntil' => 'nullable|date|after_or_equal:projectTargetDate',
            'projectNotes' => 'nullable|string|max:2000',
        ]);

        $startDate = $this->projectStartDate ?: null;
        $targetDate = $this->projectTargetDate ?: null;
        $warrantyUntil = $this->projectWarrantyUntil ?: null;

        $data = [
            'client_id' => (int) $validated['projectClientId'],
            'name' => $validated['projectName'],
            'reference_code' => $this->projectReference ?: null,
            'job_type' => $validated['projectJobType'],
            'status' => $validated['projectStatus'],
            'start_date' => $startDate,
            'target_date' => $targetDate,
            'warranty_until' => $warrantyUntil,
            'notes' => $this->projectNotes ?: null,
        ];

        if ($this->editingProject) {
            $project = Project::find($this->projectId);
            if (!$project) {
                session()->flash('message', 'Project not found.');
                return;
            }

            $project->update($data);
            $message = 'Project updated successfully.';
        } else {
            $project = Project::create($data);
            $message = 'Project created successfully.';
        }

        History::create([
            'user_id' => auth()->id(),
            'action' => $this->editingProject ? 'update' : 'create',
            'model' => 'project',
            'model_id' => $project->id,
            'changes' => [
                'client_id' => $data['client_id'],
                'name' => $data['name'],
                'reference_code' => $data['reference_code'],
                'job_type' => $data['job_type'],
                'status' => $data['status'],
                'start_date' => $startDate,
                'target_date' => $targetDate,
                'warranty_until' => $warrantyUntil,
            ],
        ]);

        $this->loadClients();
        if ($this->selectedClient && $this->selectedClient->id === $project->client_id) {
            $this->viewExpenses($project->client_id);
        }
        $this->closeProjectModal();
        session()->flash('message', $message);
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

    public function clearFilters(): void
    {
        $this->search = '';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->clientFilter = '';
        $this->projectFilter = '';
        $this->clientStatusFilter = '';
        $this->projectStatusFilter = '';
    }

    public function updatedDateFrom($value): void
    {
        if ($this->dateTo && $value && $value > $this->dateTo) {
            $this->dateTo = $value;
        }
    }

    public function updatedDateTo($value): void
    {
        if ($this->dateFrom && $value && $value < $this->dateFrom) {
            $this->dateFrom = $value;
        }
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

        if ($this->selectedClient) {
            $this->refreshCalendarEvents();
        }
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

        if ($this->selectedClient) {
            $this->refreshCalendarEvents();
        }
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

        if ($this->selectedClient) {
            $this->refreshCalendarEvents();
        }
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
                    'logo_updated' => $this->clientLogo ? true : false,
                ],
            ]);
            $message = 'Client updated successfully.';
        } else {
            $client = Client::create([
                'name' => $this->clientName,
                'branch' => $this->clientBranch,
                'status' => 'in_progress',
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
                    'status' => 'in_progress',
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

    protected function buildProjectBreakdown(Collection $expenses): Collection
    {
        return $expenses
            ->groupBy(fn ($expense) => $expense->project_id ?: 'unassigned')
            ->map(function (Collection $projectExpenses) {
                $project = $projectExpenses->first()->project;
                $sortedExpenses = $projectExpenses->sortByDesc(fn ($expense) => $expense->released_at?->timestamp ?? 0);

                return [
                    'project' => $project,
                    'project_name' => $project?->name ?? 'General Expenses',
                    'reference_code' => $project?->reference_code,
                    'status' => $project?->status,
                    'warranty_until' => $project?->warranty_until,
                    'expenses' => $sortedExpenses,
                    'subtotal' => $sortedExpenses->sum('total_cost'),
                    'item_count' => $sortedExpenses->count(),
                ];
            })
            ->sortByDesc('subtotal')
            ->values();
    }

    public function render()
    {
        $expensesQuery = Expense::with(['client', 'project', 'inventory'])
            ->when($this->clientFilter, fn ($query) => $query->where('client_id', $this->clientFilter))
            ->when($this->projectFilter, fn ($query) => $query->where('project_id', $this->projectFilter))
            ->when($this->clientStatusFilter, fn ($query) => $query->whereHas('client', fn ($clientQuery) => $clientQuery->where('status', $this->clientStatusFilter)))
            ->when($this->projectStatusFilter, fn ($query) => $query->whereHas('project', fn ($projectQuery) => $projectQuery->where('status', $this->projectStatusFilter)))
            ->when($this->search, function ($query) {
                $term = '%' . str_replace(' ', '%', $this->search) . '%';

                $query->where(function ($subQuery) use ($term) {
                    $subQuery
                        ->whereHas('client', function ($clientQuery) use ($term) {
                            $clientQuery->where('name', 'like', $term)
                                ->orWhere('branch', 'like', $term);
                        })
                        ->orWhereHas('project', function ($projectQuery) use ($term) {
                            $projectQuery->where('name', 'like', $term)
                                ->orWhere('reference_code', 'like', $term);
                        })
                        ->orWhereHas('inventory', function ($inventoryQuery) use ($term) {
                            $inventoryQuery->where('brand', 'like', $term)
                                ->orWhere('description', 'like', $term)
                                ->orWhere('category', 'like', $term);
                        });
                });
            })
            ->when($this->dateFrom, fn ($query) => $query->whereDate('released_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('released_at', '<=', $this->dateTo));

        $filteredExpenses = $expensesQuery->orderByDesc('released_at')->get();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $monthlyTotal = $filteredExpenses->filter(fn ($expense) => $expense->released_at?->between($startOfMonth, $endOfMonth, true))->sum('total_cost');

        $summary = [
            'total' => $filteredExpenses->sum('total_cost'),
            'month' => $monthlyTotal,
            'average' => $filteredExpenses->count() ? round($filteredExpenses->avg('total_cost'), 2) : 0,
            'count' => $filteredExpenses->count(),
        ];

        $projectOptions = Project::with('client:id,name')->orderBy('name')->get();

        $projectSummaries = Project::with([
            'client:id,name,branch',
            'expenses' => fn ($query) => $query->with('inventory')->latest('released_at'),
        ])
            ->withCount('expenses')
            ->withSum('expenses as expenses_total', 'total_cost')
            ->orderByDesc('created_at')
            ->get();

        $receiptGroups = $filteredExpenses
            ->groupBy(fn ($expense) => $expense->client_id)
            ->map(function (Collection $clientExpenses) {
                $client = $clientExpenses->first()->client;

                return [
                    'client' => $client,
                    'branch' => $client?->branch,
                    'total' => $clientExpenses->sum('total_cost'),
                    'count' => $clientExpenses->count(),
                    'projects' => $this->buildProjectBreakdown($clientExpenses),
                ];
            })
            ->sortBy(fn ($group) => strtolower($group['client']->name ?? ''))
            ->values();

        return view('livewire.expenses', [
            'filteredExpenses' => $filteredExpenses,
            'projectOptions' => $projectOptions,
            'summary' => $summary,
            'receiptGroups' => $receiptGroups,
            'projectSummaries' => $projectSummaries,
        ]);
    }
}
