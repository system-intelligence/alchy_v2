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
use App\Models\MaterialReleaseApproval;
use App\Models\User;
use App\Models\Chat;
use App\Events\ApprovalRequestCreated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

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
    public $clientsSearch = '';
    public $projectsSearch = '';
    public $receiptsSearch = '';
    public $dateFrom = null;
    public $dateTo = null;
    public $clientFilter = '';
    public $projectFilter = '';
    public $clientTypeFilter = '';
    public $clientStatusFilter = '';
    public $projectStatusFilter = '';

    // Edit expense
    public $editingExpenseId = null;
    public $editCostPerUnit = 0;

    // Edit release
    public $editingReleaseId = null;
    public $editReleaseCostPerUnit = 0;

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
    public $clientType = 'non_banking';
    public $clientLogo = null;
    public $calendarEvents = [];

    // Client Deletion modal
    public $showDeleteClientModal = false;
    public $deleteClientId = null;
    public $deletePassword = '';

    // Project Deletion modal
    public $showDeleteProjectModal = false;
    public $deleteProjectId = null;
    public $deleteProjectPassword = '';
    public $deleteProjectData = null;

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
    public $manageProjectUpdateMessage = '';

    // Release processing state
    public $isReleasingMaterials = false;
    public $releaseProcessingMessage = '';

    // Project notes tab
    public $manageProjectNotesList = [];
    public $newNoteContent = '';
    public $newNoteImages = [];
    public $editingNoteId = null;
    public $editNoteContent = '';

    protected static ?bool $expenseNotesSupported = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'clientsSearch' => ['except' => ''],
        'projectsSearch' => ['except' => ''],
        'receiptsSearch' => ['except' => ''],
        'dateFrom' => ['except' => null],
        'dateTo' => ['except' => null],
        'clientFilter' => ['except' => ''],
        'projectFilter' => ['except' => ''],
        'clientTypeFilter' => ['except' => ''],
        'clientStatusFilter' => ['except' => ''],
        'projectStatusFilter' => ['except' => ''],
    ];



    public function mount()
    {
        $this->ensureAdmin();
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
        $this->showDeleteProjectModal = false;
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
        $this->resetDeleteProjectForm();
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

        // Get comprehensive deletion details from the Client model
        $clientDetails = $client->getHistoryDeletionDetails();

        $client->delete();
        History::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model' => 'client',
            'model_id' => $this->deleteClientId,
            'changes' => $clientDetails,
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

    public function openDeleteProjectModal($projectId)
    {
        $this->ensureAdmin();
        $this->deleteProjectId = $projectId;
        $this->showProjectManageModal = false; // Close the manage modal

        // Load project data for display
        $project = Project::with(['expenses', 'projectNotes'])->find($projectId);
        if ($project) {
            $this->deleteProjectData = [
                'name' => $project->name,
                'expense_count' => $project->expenses()->count(),
                'note_count' => $project->projectNotes()->count(),
                'total_expenses' => $project->expenses()->sum('total_cost'),
            ];
        }

        $this->showDeleteProjectModal = true;
    }

    public function confirmDeleteProject()
    {
        $this->ensureAdmin();

        $this->validate([
            'deleteProjectPassword' => 'required',
        ]);

        // Check password
        if (!\Illuminate\Support\Facades\Hash::check($this->deleteProjectPassword, auth()->user()->password)) {
            $this->addError('deleteProjectPassword', 'The password is incorrect.');
            return;
        }

        $project = Project::find($this->deleteProjectId);
        if (!$project) {
            session()->flash('message', 'Project not found.');
            return;
        }

        // Capture all project details before deletion
        $projectDetails = [
            'name' => $project->name,
            'reference_code' => $project->reference_code,
            'client_id' => $project->client_id,
            'status' => $project->status,
            'job_type' => $project->job_type,
            'start_date' => $project->start_date?->format('Y-m-d'),
            'target_date' => $project->target_date?->format('Y-m-d'),
            'warranty_until' => $project->warranty_until?->format('Y-m-d'),
            'notes' => $project->notes,
            'total_expenses' => $project->expenses()->sum('total_cost'),
            'expense_count' => $project->expenses()->count(),
            'note_count' => $project->projectNotes()->count(),
            'deleted' => true
        ];

        $project->delete();
        History::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model' => 'project',
            'model_id' => $this->deleteProjectId,
            'changes' => $projectDetails,
        ]);

        $this->loadClients();
        $this->closeModal();
        session()->flash('message', 'Project deleted successfully.');
    }

    protected function resetDeleteProjectForm(): void
    {
        $this->deleteProjectId = null;
        $this->deleteProjectPassword = '';
        $this->deleteProjectData = null;
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

        // Generate reference code for new projects
        if (!$this->editingProject) {
            $this->generateProjectReference();
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
        $this->editingReleaseId = null;
        $this->editReleaseCostPerUnit = 0;
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
        $this->isReleasingMaterials = false;
        $this->releaseProcessingMessage = '';
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
        // Get today's date in MMDDYYYY format
        $today = now()->format('mdY'); // mdY gives MMDDYYYY

        // Find the highest sequence number for projects created today
        $latestProject = Project::whereDate('created_at', today())
            ->where('reference_code', 'like', $today . '-%')
            ->orderByRaw("CAST(SUBSTRING_INDEX(reference_code, '-', -1) AS UNSIGNED) DESC")
            ->first();

        // Extract the sequence number and increment it
        $sequence = 1; // Default to 001
        if ($latestProject && $latestProject->reference_code) {
            $parts = explode('-', $latestProject->reference_code);
            if (count($parts) >= 2) {
                $lastSequence = (int) end($parts);
                $sequence = $lastSequence + 1;
            }
        }

        // Format sequence as 3-digit number with leading zeros
        $sequenceFormatted = str_pad($sequence, 3, '0', STR_PAD_LEFT);

        $this->projectReference = sprintf('%s-%s', $today, $sequenceFormatted);
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

        // Get inventory items filtered by client/project usage and availability
        $clientId = $project->client_id;

        // Get inventory IDs that have been used for this client before
        $previouslyUsedInventoryIds = Expense::where('client_id', $clientId)
            ->whereNotNull('inventory_id')
            ->pluck('inventory_id')
            ->unique()
            ->toArray();

        // Get inventory items ordered by: previously used for this client first, then by brand
        $query = Inventory::query();

        if (!empty($previouslyUsedInventoryIds)) {
            $query->orderByRaw("FIELD(id, " . implode(',', $previouslyUsedInventoryIds) . ") DESC");
        }

        $this->manageInventoryOptions = $query->orderBy('brand')
            ->get()
            ->map(function (Inventory $inventory) use ($previouslyUsedInventoryIds) {
                $status = $inventory->status instanceof InventoryStatus
                    ? $inventory->status->value
                    : $inventory->status;

                return [
                    'id' => $inventory->id,
                    'label' => trim(sprintf('%s — %s', $inventory->brand, $inventory->description ?? '')),
                    'quantity' => $inventory->quantity,
                    'status' => $status,
                    'previously_used' => in_array($inventory->id, $previouslyUsedInventoryIds),
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

    protected function submitApprovalRequest(): void
    {
        if (!$this->managingProject) {
            session()->flash('message', 'Select a project before releasing materials.');
            return;
        }

        try {
            $this->validateReleaseForm();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return;
        }

        $project = Project::with('client')->find($this->managingProject['id']);
        if (!$project) {
            session()->flash('message', 'Project not found.');
            return;
        }

        $items = $this->normalizeReleaseItems();
        if ($items->isEmpty()) {
            $this->addError('manageReleaseItems', 'Add at least one material before saving.');
            return;
        }

        try {
            $inventories = $this->validateInventoryAvailability($items);
        } catch (\Exception $e) {
            $this->addError('manageReleaseItems', $e->getMessage());
            return;
        }

        $this->processApprovalWorkflow($project, $items, $inventories);
    }

    public function recordProjectRelease(): void
    {
        // Prevent double submission
        if ($this->isReleasingMaterials) {
            return;
        }

        // Validate project and items
        if (!$this->managingProject) {
            session()->flash('message', 'Select a project before releasing materials.');
            return;
        }

        try {
            $this->validateReleaseForm();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return;
        }

        // Load project and prepare items
        $project = Project::with('client')->find($this->managingProject['id']);
        if (!$project) {
            session()->flash('message', 'Project not found.');
            return;
        }

        $items = $this->normalizeReleaseItems();
        if ($items->isEmpty()) {
            $this->addError('manageReleaseItems', 'Add at least one material before saving.');
            return;
        }

        // Validate inventory availability
        try {
            $inventories = $this->validateInventoryAvailability($items);
        } catch (\Exception $e) {
            $this->addError('manageReleaseItems', $e->getMessage());
            return;
        }

        // Route to appropriate workflow
        $user = auth()->user();
        if ($user->role === 'user' || !($user->isSystemAdmin() || $user->isDeveloper())) {
            $this->processApprovalWorkflow($project, $items, $inventories);
        } else {
            $this->processDirectRelease($project, $items, $inventories);
        }
    }

    private function validateReleaseForm(): void
    {
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
    }

    private function normalizeReleaseItems(): Collection
    {
        return collect($this->manageReleaseItems)
            ->map(fn ($item) => [
                'inventory_id' => (int) ($item['inventory_id'] ?? 0),
                'quantity' => (int) ($item['quantity'] ?? 0),
                'cost_per_unit' => (float) ($item['cost_per_unit'] ?? 0.0),
            ])
            ->filter(fn ($item) => $item['inventory_id'] > 0)
            ->values();
    }

    private function validateInventoryAvailability(Collection $items): Collection
    {
        $inventories = Inventory::whereIn('id', $items->pluck('inventory_id'))
            ->get()
            ->keyBy('id');

        $requestedTotals = [];

        foreach ($items as $index => $item) {
            $inventory = $inventories->get($item['inventory_id']);
            if (!$inventory) {
                throw new \Exception('Selected inventory item not found.');
            }

            $requested = ($requestedTotals[$inventory->id] ?? 0) + $item['quantity'];
            $requestedTotals[$inventory->id] = $requested;

            if ($requested > $inventory->quantity) {
                throw new \Exception(
                    "Insufficient stock for {$inventory->brand}. Available: {$inventory->quantity}, Requested: {$requested}"
                );
            }
        }

        return $inventories;
    }

    private function processApprovalWorkflow(Project $project, Collection $items, Collection $inventories): void
    {
        $this->isReleasingMaterials = true;
        $this->releaseProcessingMessage = 'Submitting approval request...';
        
        DB::beginTransaction();
        try {
            $systemAdmins = User::where('role', 'system_admin')->get();
            
            if ($systemAdmins->isEmpty()) {
                DB::rollBack();
                $this->isReleasingMaterials = false;
                $this->releaseProcessingMessage = '';
                session()->flash('message', 'No system administrators available to approve your request.');
                return;
            }

            $itemCount = 0;
            foreach ($items as $item) {
                $itemCount++;
                $this->releaseProcessingMessage = "Processing item $itemCount of {$items->count()}...";
                
                $inventory = $inventories->get($item['inventory_id']);
                
                $approval = MaterialReleaseApproval::create([
                    'requested_by' => auth()->id(),
                    'inventory_id' => $inventory->id,
                    'quantity_requested' => $item['quantity'],
                    'reason' => $this->manageReleaseNotes ?: "Material release for project {$project->reference_code}",
                    'status' => 'pending',
                ]);

                $this->notifyAdminsOfApprovalRequest($approval, $project, $inventory, $item, $systemAdmins);
                
                History::create([
                    'user_id' => auth()->id(),
                    'action' => 'Material Release Request Created',
                    'model' => 'MaterialReleaseApproval',
                    'model_id' => $approval->id,
                    'changes' => json_encode([
                        'status' => 'pending',
                        'project' => $project->reference_code,
                        'material' => $inventory->material_name,
                        'quantity' => $item['quantity'],
                        'cost_per_unit' => $item['cost_per_unit'],
                        'reason' => $this->manageReleaseNotes,
                        'requested_at' => now()->toDateTimeString(),
                    ]),
                ]);
            }

            DB::commit();
            
            $this->isReleasingMaterials = false;
            $this->releaseProcessingMessage = '';
            $this->resetManageReleaseForm();
            $this->hydrateManageProject($project->id, preserveTab: true);
            
            session()->flash('message', '✅ Material release request sent for approval. Please wait for admin approval.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->isReleasingMaterials = false;
            $this->releaseProcessingMessage = '';
            \Log::error('Approval workflow failed', ['error' => $e->getMessage()]);
            $this->addError('manageReleaseItems', 'Unable to submit approval request.');
        }
    }

    private function notifyAdminsOfApprovalRequest(
        MaterialReleaseApproval $approval,
        Project $project,
        Inventory $inventory,
        array $item,
        Collection $systemAdmins
    ): void {
        foreach ($systemAdmins as $admin) {
            try {
                $message = $this->buildApprovalRequestMessage($approval, $project, $inventory, $item);
                $chat = Chat::create([
                    'user_id' => auth()->id(),
                    'recipient_id' => $admin->id,
                    'message' => $message,
                ]);

                event(new \App\Events\MessageSent($chat));
                
                if ($approval->chat_id === null) {
                    $approval->update(['chat_id' => $chat->id]);
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to notify admin {$admin->id}", ['error' => $e->getMessage()]);
            }
        }

        try {
            event(new ApprovalRequestCreated($approval));
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast approval event', ['error' => $e->getMessage()]);
        }
    }

    private function buildApprovalRequestMessage(
        MaterialReleaseApproval $approval,
        Project $project,
        Inventory $inventory,
        array $item
    ): string {
        return "📋 Material Release Request\n\n"
            . "Project: {$project->reference_code}\n"
            . "Item: {$inventory->brand} - {$inventory->description}\n"
            . "Quantity: {$item['quantity']}\n"
            . "Reason: " . ($this->manageReleaseNotes ?: 'No reason provided') . "\n\n"
            . "Approval ID: {$approval->id}";
    }

    private function processDirectRelease(Project $project, Collection $items, Collection $inventories): void
    {
        $this->isReleasingMaterials = true;
        $this->releaseProcessingMessage = 'Processing material release...';
        
        $releasedAt = Carbon::parse(
            $this->manageReleaseDate . ' ' . $this->manageReleaseTime,
            'Asia/Manila'
        )->setTimezone('UTC');

        DB::beginTransaction();
        try {
            $itemCount = 0;
            foreach ($items as $item) {
                $itemCount++;
                $this->releaseProcessingMessage = "Releasing item $itemCount of {$items->count()}...";
                
                $inventory = $inventories->get($item['inventory_id']);
                $this->releaseInventoryItem($project, $inventory, $item, $releasedAt);
            }

            DB::commit();

            $this->isReleasingMaterials = false;
            $this->releaseProcessingMessage = '';
            $this->resetManageReleaseForm();
            $this->hydrateManageProject($project->id, preserveTab: true);
            $this->loadClients();

            if ($this->selectedClient?->id === $project->client_id) {
                $this->viewExpenses($project->client_id);
            }

            session()->flash('message', $items->count() . ' material(s) released and inventory updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->isReleasingMaterials = false;
            $this->releaseProcessingMessage = '';
            \Log::error('Direct release failed', ['error' => $e->getMessage()]);
            $this->addError('manageReleaseItems', 'Unable to record releases at this time.');
        }
    }

    private function releaseInventoryItem(
        Project $project,
        Inventory $inventory,
        array $item,
        Carbon $releasedAt
    ): void {
        $totalCost = round($item['quantity'] * $item['cost_per_unit'], 2);

        $expense = Expense::create([
            'client_id' => $project->client_id,
            'inventory_id' => $inventory->id,
            'project_id' => $project->id,
            'quantity_used' => $item['quantity'],
            'cost_per_unit' => $item['cost_per_unit'],
            'total_cost' => $totalCost,
            'released_at' => $releasedAt,
            'notes' => $this->manageReleaseNotes && $this->expenseNotesEnabled() ? $this->manageReleaseNotes : null,
        ]);

        $oldInventoryValues = [
            'quantity' => $inventory->quantity,
            'status' => $inventory->status,
        ];

        $newQuantity = max(0, $inventory->quantity - $item['quantity']);
        $newStatus = $this->determineInventoryStatus($newQuantity, $inventory->min_stock_level);

        $inventory->update([
            'quantity' => $newQuantity,
            'status' => $newStatus->value,
        ]);

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
                'notes' => $expense->notes,
            ], fn ($value) => $value !== null),
        ]);

        History::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model' => 'inventory',
            'model_id' => $inventory->id,
            'old_values' => $oldInventoryValues,
            'changes' => [
                'quantity' => $newQuantity,
                'status' => $newStatus->value,
            ],
        ]);
    }

    private function determineInventoryStatus(int $quantity, int $minStockLevel): InventoryStatus
    {
        return match (true) {
            $quantity <= 0 => InventoryStatus::OUT_OF_STOCK,
            $quantity <= $minStockLevel => InventoryStatus::CRITICAL,
            default => InventoryStatus::NORMAL,
        };
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

        // Capture old values before update
        $oldValues = [
            'status' => $project->status,
            'start_date' => $project->start_date?->format('Y-m-d'),
            'target_date' => $project->target_date?->format('Y-m-d'),
            'warranty_until' => $project->warranty_until?->format('Y-m-d'),
            'notes' => $project->notes,
        ];

        // Prepare new values
        $newValues = [
            'status' => $this->manageProjectStatus,
            'start_date' => $this->manageProjectStartDate ?: null,
            'target_date' => $this->manageProjectTargetDate ?: null,
            'warranty_until' => $this->manageProjectWarrantyUntil ?: null,
            'notes' => $this->manageProjectNotesInput ?: null,
        ];

        // Check for actual changes
        $changes = [];
        foreach ($newValues as $field => $newValue) {
            if ($oldValues[$field] != $newValue) {
                $changes[$field] = $newValue;
            }
        }

        // Only proceed if there are actual changes
        if (empty($changes)) {
            $message = 'No changes detected. Project details remain unchanged.';
            session()->flash('message', $message);
            $this->dispatch('showNotification', $message, 'info', 4000);
            return;
        }

        $project->update($newValues);

        History::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model' => 'project',
            'model_id' => $project->id,
            'old_values' => $oldValues,
            'changes' => $changes, // Only actual changes
        ]);

        $this->hydrateManageProject($project->id, preserveTab: true);
        $this->loadClients();

        if ($this->selectedClient && $this->selectedClient->id === $project->client_id) {
            $this->viewExpenses($project->client_id);
        }

        $changeCount = count($changes);
        $changedFields = array_keys($changes);
        $fieldsText = implode(', ', array_map(function($field) {
            return ucwords(str_replace('_', ' ', $field));
        }, $changedFields));

        // Build detailed changes text
        $changesDetails = [];
        foreach ($changes as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? 'N/A';
            $fieldName = ucwords(str_replace('_', ' ', $field));
            $changesDetails[] = "{$fieldName}: {$oldValue} → {$newValue}";
        }
        $changesText = implode(', ', $changesDetails);

        $message = "Project '{$this->managingProject['name']}' updated successfully! ({$changeCount} field" . ($changeCount > 1 ? 's' : '') . " updated: {$changesText})";

        // Show both session flash and toast notification
        session()->flash('message', $message);
        $this->dispatch('showNotification', $message, 'success', 5000);
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

        // Capture note details before deletion
        $noteDetails = [
            'project_id' => $note->project_id,
            'content' => $note->content,
            'has_images' => !empty($note->images),
            'created_by' => $note->user->name,
            'created_at' => $note->created_at->format('Y-m-d H:i:s'),
            'deleted' => true
        ];

        $note->delete();

        History::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model' => 'project_note',
            'model_id' => $noteId,
            'changes' => $noteDetails,
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

        // Generate unique verification hash
        $receiptData = [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'reference_code' => $project->reference_code,
            'client_name' => $project->client->name,
            'total_expenses' => $project->expenses->sum('total_cost'),
            'expense_count' => $project->expenses->count(),
            'generated_at' => now()->toIso8601String(),
        ];
        
        $verificationHash = hash('sha256', json_encode($receiptData) . config('app.key'));
        
        // Store verification record
        $verification = \App\Models\ReceiptVerification::create([
            'project_id' => $project->id,
            'verification_hash' => $verificationHash,
            'receipt_data' => $receiptData,
            'generated_at' => now(),
            'generated_by' => auth()->user()->name,
        ]);

        History::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model' => 'receipt_verification',
            'model_id' => $verification->id,
            'changes' => [
                'project_id' => $verification->project_id,
                'verification_hash' => $verification->verification_hash,
                'generated_at' => $verification->generated_at->toDateTimeString(),
                'generated_by' => $verification->generated_by,
            ],
        ]);

        $filename = Str::slug($project->reference_code ?: $project->name ?: 'project')
            . '-receipt-' . now()->format('Ymd_His') . '.pdf';

        // Generate verification URL for QR code
        $verificationUrl = route('verify-receipt', ['hash' => $verificationHash]);

        // Generate QR code as SVG (works without imagick/gd)
        $qrCodeSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(75)
            ->errorCorrection('H')
            ->generate($verificationUrl);

        // Generate PDF with verification data
        $pdf = \PDF::loadView('pdf.project-receipt', compact('project', 'verificationHash', 'verificationUrl', 'qrCodeSvg'));
        
        // Set PDF options for security
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    protected function expenseNotesEnabled(): bool
    {
        if (static::$expenseNotesSupported !== null) {
            return static::$expenseNotesSupported;
        }

        return static::$expenseNotesSupported = Schema::hasColumn('expenses', 'notes');
    }

    public function isReleaseFormDisabled(): bool
    {
        return $this->isReleasingMaterials;
    }

    public function isSearchDisabled(): bool
    {
        return false; // Search is NEVER disabled
    }

    public function isReleaseFieldDisabled(string $fieldName = ''): bool
    {
        // Only disable if actively releasing AND it's a release form field
        if (!$this->isReleasingMaterials) {
            return false;
        }

        // These fields belong to the release form
        $releaseFormFields = [
            'manageReleaseItems',
            'manageReleaseDate',
            'manageReleaseTime',
            'manageReleaseNotes',
        ];

        return in_array($fieldName, $releaseFormFields);
    }

    public function getFilteredManageInventoryOptions(): array
    {
        // Use the material search term for the improved selection interface
        $searchTerm = $this->manageReleaseMaterialSearch;
        
        // If no search term, return all options
        if (empty($searchTerm)) {
            return $this->manageInventoryOptions;
        }

        $searchTermLower = strtolower($searchTerm);

        return array_filter($this->manageInventoryOptions, function ($option) use ($searchTermLower) {
            $label = strtolower($option['label'] ?? '');
            $brand = strtolower($option['brand'] ?? '');
            $description = strtolower($option['description'] ?? '');
            
            return str_contains($label, $searchTermLower) 
                || str_contains($brand, $searchTermLower)
                || str_contains($description, $searchTermLower);
        });
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

        // Auto-generate reference code if not provided
        if (empty($this->projectReference)) {
            $this->generateProjectReference();
        }

        $validated = $this->validate([
            'projectClientId' => 'required|exists:clients,id',
            'projectName' => 'required|string|max:255',
            'projectReference' => 'required|string|max:100',
            'projectJobType' => 'required|in:installation,service',
            'projectStatus' => 'required|in:' . implode(',', Project::STATUSES),
            'projectStartDate' => 'nullable|date',
            'projectTargetDate' => 'nullable|date|after_or_equal:projectStartDate',
            'projectWarrantyUntil' => 'nullable|date|after_or_equal:projectTargetDate',
            'projectNotes' => 'nullable|string|max:2000',
        ]);

        $projectData = [
            'client_id' => (int) $validated['projectClientId'],
            'name' => $validated['projectName'],
            'reference_code' => $this->projectReference ?: null,
            'job_type' => $validated['projectJobType'],
            'status' => $validated['projectStatus'],
            'start_date' => $this->projectStartDate ?: null,
            'target_date' => $this->projectTargetDate ?: null,
            'warranty_until' => $this->projectWarrantyUntil ?: null,
            'notes' => $this->projectNotes ?: null,
        ];

        if ($this->editingProject) {
            $project = Project::find($this->projectId);
            if (!$project) {
                session()->flash('message', 'Project not found.');
                return;
            }

            // Capture old values before update
            $oldValues = [
                'client_id' => $project->client_id,
                'name' => $project->name,
                'reference_code' => $project->reference_code,
                'job_type' => $project->job_type,
                'status' => $project->status,
                'start_date' => $project->start_date?->format('Y-m-d'),
                'target_date' => $project->target_date?->format('Y-m-d'),
                'warranty_until' => $project->warranty_until?->format('Y-m-d'),
                'notes' => $project->notes,
            ];

            // Check for actual changes
            $changes = [];
            foreach ($projectData as $field => $newValue) {
                if ($oldValues[$field] != $newValue) {
                    $changes[$field] = $newValue;
                }
            }

            // Only proceed if there are actual changes
            if (empty($changes)) {
                session()->flash('message', 'No changes detected. Project details remain unchanged.');
                $this->closeProjectModal();
                return;
            }

            $project->update($projectData);
            $changeCount = count($changes);
            $changedFields = array_keys($changes);
            $fieldsText = implode(', ', array_map(function($field) {
                return ucwords(str_replace('_', ' ', $field));
            }, $changedFields));

            // Build detailed changes text
            $changesDetails = [];
            foreach ($changes as $field => $newValue) {
                $oldValue = $oldValues[$field] ?? 'N/A';
                $fieldName = ucwords(str_replace('_', ' ', $field));
                $changesDetails[] = "{$fieldName}: {$oldValue} → {$newValue}";
            }
            $changesText = implode(', ', $changesDetails);

            $message = "Changes made successfully! ({$changeCount} field" . ($changeCount > 1 ? 's' : '') . " updated: {$changesText})";
        } else {
            $project = Project::create($projectData);
            $message = 'Project created successfully.';
        }

        History::create([
            'user_id' => auth()->id(),
            'action' => $this->editingProject ? 'update' : 'create',
            'model' => 'project',
            'model_id' => $project->id,
            'old_values' => $this->editingProject ? $oldValues : null,
            'changes' => $projectData,
        ]);

        $this->loadClients();
        if ($this->selectedClient?->id === $project->client_id) {
            $this->viewExpenses($project->client_id);
        }
        $this->closeProjectModal();

        // Show both session flash and toast notification
        session()->flash('message', $message);
        $this->dispatch('showNotification', $message, 'success', 5000);
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

        $this->validate(['editCostPerUnit' => 'required|numeric|min:0']);

        $expense = Expense::find($this->editingExpenseId);
        if (!$expense) {
            session()->flash('message', 'Expense not found.');
            return;
        }

        $oldValues = [
            'cost_per_unit' => $expense->cost_per_unit,
            'total_cost' => $expense->total_cost,
        ];

        $newTotalCost = round($expense->quantity_used * $this->editCostPerUnit, 2);

        $expense->update([
            'cost_per_unit' => $this->editCostPerUnit,
            'total_cost' => $newTotalCost,
        ]);

        History::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model' => 'expense',
            'model_id' => $expense->id,
            'old_values' => $oldValues,
            'changes' => [
                'cost_per_unit' => $this->editCostPerUnit,
                // total_cost is calculated, don't include in changes
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

    public function editReleaseCost($releaseId)
    {
        $this->editingReleaseId = $releaseId;
        // Find the release and set the current cost per unit
        foreach ($this->manageRecentReleases as $release) {
            if ($release['id'] == $releaseId) {
                $this->editReleaseCostPerUnit = $release['cost_per_unit'];
                break;
            }
        }
    }

    public function saveEditRelease()
    {
        $this->ensureAdmin();

        // Clean the input value by removing commas
        $this->editReleaseCostPerUnit = str_replace(',', '', $this->editReleaseCostPerUnit);

        $this->validate(['editReleaseCostPerUnit' => 'required|numeric|min:0']);

        $expense = Expense::find($this->editingReleaseId);
        if (!$expense) {
            session()->flash('message', 'Expense not found.');
            return;
        }

        $oldValues = [
            'cost_per_unit' => $expense->cost_per_unit,
            'total_cost' => $expense->total_cost,
        ];

        $newTotalCost = round($expense->quantity_used * $this->editReleaseCostPerUnit, 2);

        $expense->update([
            'cost_per_unit' => $this->editReleaseCostPerUnit,
            'total_cost' => $newTotalCost,
        ]);

        History::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model' => 'expense',
            'model_id' => $expense->id,
            'old_values' => $oldValues,
            'changes' => [
                'cost_per_unit' => $this->editReleaseCostPerUnit,
                // total_cost is calculated, don't include in changes
            ],
        ]);

        $this->hydrateManageProject($expense->project_id, preserveTab: true);
        $this->loadClients();
        if ($this->selectedClient) {
            $this->viewExpenses($this->selectedClient->id);
        }
        $this->cancelEditRelease();
        session()->flash('message', 'Release cost updated successfully.');
    }

    public function cancelEditRelease()
    {
        $this->editingReleaseId = null;
        $this->editReleaseCostPerUnit = 0;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->clientFilter = '';
        $this->projectFilter = '';
        $this->clientTypeFilter = '';
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

    public function changeMonth($direction): void
    {
        match ($direction) {
            'prev' => $this->moveToPreviousMonth(),
            'next' => $this->moveToNextMonth(),
            default => null,
        };

        $this->monthSelected = $this->calendarMonth - 1;
        $this->yearSelected = array_search($this->calendarYear, $this->yearItems);

        if ($this->selectedClient) {
            $this->refreshCalendarEvents();
        }
    }

    private function moveToPreviousMonth(): void
    {
        $this->calendarMonth--;
        if ($this->calendarMonth < 1) {
            $this->calendarMonth = 12;
            $this->calendarYear--;
        }
    }

    private function moveToNextMonth(): void
    {
        $this->calendarMonth++;
        if ($this->calendarMonth > 12) {
            $this->calendarMonth = 1;
            $this->calendarYear++;
        }
    }

    public function toggleMonth(): void
    {
        $this->monthOpen = !$this->monthOpen;
        $this->yearOpen = false;
    }

    public function selectMonth(int $index): void
    {
        $this->monthSelected = $index;
        $this->calendarMonth = $index + 1;
        $this->monthOpen = false;

        if ($this->selectedClient) {
            $this->refreshCalendarEvents();
        }
    }

    public function toggleYear(): void
    {
        $this->yearOpen = !$this->yearOpen;
        $this->monthOpen = false;
    }

    public function selectYear(int $index): void
    {
        $this->yearSelected = $index;
        $this->calendarYear = $this->yearItems[$index];
        $this->yearOpen = false;

        if ($this->selectedClient) {
            $this->refreshCalendarEvents();
        }
    }

    public function toggleTheme(): void
    {
        $this->themeOpen = !$this->themeOpen;
        $this->monthOpen = false;
        $this->yearOpen = false;
    }

    public function selectTheme(string $theme): void
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
            $this->clientType = $client->client_type ?? 'non_banking';
        }

        $this->showClientModal = true;
    }

    protected function resetClientForm(): void
    {
        $this->editingClient = false;
        $this->clientId = null;
        $this->clientName = '';
        $this->clientBranch = '';
        $this->clientType = 'non_banking';
        $this->clientLogo = null;
    }

    public function saveClient()
    {
        $this->ensureAdmin();

        $this->validate([
            'clientName' => 'required|string|max:255',
            'clientBranch' => 'required|string|max:255',
            'clientType' => 'required|in:banking,non_banking',
            'clientLogo' => 'nullable|image|max:1024',
        ]);

        if ($this->editingClient) {
            $client = Client::find($this->clientId);
            if (!$client) {
                session()->flash('message', 'Client not found.');
                return;
            }

            $oldValues = ['name' => $client->name, 'branch' => $client->branch, 'client_type' => $client->client_type];

            // Prepare new values
            $newValues = [
                'name' => $this->clientName,
                'branch' => $this->clientBranch,
                'client_type' => $this->clientType,
            ];

            $client->update($newValues);

            if ($this->clientLogo) {
                if (!$client->storeImageAsBlob($this->clientLogo->path())) {
                    session()->flash('message', 'Client updated successfully, but logo upload failed.');
                }
            }

            // Get detailed change information using the Client model's method
            $changeDetails = $client->getHistoryChangeDetails($oldValues, $newValues);

            // Check if logo is being uploaded and add to change details
            $logoUpdated = (bool) $this->clientLogo;
            if ($logoUpdated) {
                $changeDetails['logo'] = [
                    'old' => $client->hasImageBlob() ? 'Has logo' : 'No logo',
                    'new' => 'Logo updated',
                    'field_name' => 'Logo',
                ];
            }

            // Only proceed if there are actual changes
            if (empty($changeDetails)) {
                $this->closeModal();
                return;
            }

            // Log history with detailed change information
            History::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model' => 'client',
                'model_id' => $client->id,
                'old_values' => $oldValues,
                'changes' => $changeDetails, // Use detailed change information from model
            ]);
            $message = 'Client updated successfully.';
        } else {
            $client = Client::create([
                'name' => $this->clientName,
                'branch' => $this->clientBranch,
                'client_type' => $this->clientType,
                'status' => 'in_progress',
            ]);

            if ($this->clientLogo) {
                if (!$client->storeImageAsBlob($this->clientLogo->path())) {
                    session()->flash('message', 'Client created successfully, but logo upload failed.');
                }
            }

            History::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model' => 'client',
                'model_id' => $client->id,
                'changes' => [
                    'name' => $this->clientName,
                    'branch' => $this->clientBranch,
                    'client_type' => $this->clientType,
                    'status' => 'in_progress',
                    'logo_uploaded' => (bool) $this->clientLogo,
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

        // Get comprehensive deletion details from the Client model
        $clientDetails = $client->getHistoryDeletionDetails();

        $client->delete();
        History::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model' => 'client',
            'model_id' => $clientId,
            'changes' => $clientDetails,
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
                    'start_date' => $project?->start_date,
                    'target_date' => $project?->target_date,
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
        $filteredExpenses = $this->getFilteredExpenses();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $monthlyTotal = $filteredExpenses->filter(
            fn ($expense) => $expense->released_at?->between($startOfMonth, $endOfMonth, true)
        )->sum('total_cost');

        // Filter clients based on search and type
        $filteredClients = $this->clients;
        if ($this->clientsSearch || $this->search) {
            $searchTerm = strtolower($this->clientsSearch ?: $this->search);
            $filteredClients = $filteredClients->filter(function ($client) use ($searchTerm) {
                return str_contains(strtolower($client->name), $searchTerm) ||
                       str_contains(strtolower($client->branch), $searchTerm);
            });
        }
        if ($this->clientTypeFilter) {
            $filteredClients = $filteredClients->filter(function ($client) {
                return $client->client_type === $this->clientTypeFilter;
            });
        }

        // Filter project summaries based on search and client type
        $filteredProjectSummaries = $this->getProjectSummaries();
        if ($this->projectsSearch || $this->search) {
            $searchTerm = strtolower($this->projectsSearch ?: $this->search);
            $filteredProjectSummaries = $filteredProjectSummaries->filter(function ($project) use ($searchTerm) {
                return str_contains(strtolower($project->name), $searchTerm) ||
                       str_contains(strtolower($project->reference_code ?? ''), $searchTerm) ||
                       str_contains(strtolower($project->client->name), $searchTerm) ||
                       str_contains(strtolower($project->client->branch), $searchTerm);
            });
        }
        if ($this->clientTypeFilter) {
            $filteredProjectSummaries = $filteredProjectSummaries->filter(function ($project) {
                return $project->client->client_type === $this->clientTypeFilter;
            });
        }

        return view('livewire.expenses', [
            'filteredExpenses' => $filteredExpenses,
            'filteredClients' => $filteredClients,
            'filteredProjectSummaries' => $filteredProjectSummaries,
            'projectOptions' => $this->getProjectOptions(),
            'summary' => $this->buildSummary($filteredExpenses, $monthlyTotal),
            'receiptGroups' => $this->buildReceiptGroups($filteredExpenses),
            'projectSummaries' => $this->getProjectSummaries(),
        ]);
    }

    private function getFilteredExpenses(): Collection
    {
        $searchTerm = $this->receiptsSearch ?: $this->search;
        return Expense::with(['client', 'project', 'inventory'])
            ->when($this->clientFilter, fn ($q) => $q->where('client_id', $this->clientFilter))
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter))
            ->when($this->clientTypeFilter, fn ($q) => $q->whereHas('client', fn ($cq) => $cq->where('client_type', $this->clientTypeFilter)))
            ->when($this->clientStatusFilter, fn ($q) => $q->whereHas('client', fn ($cq) => $cq->where('status', $this->clientStatusFilter)))
            ->when($this->projectStatusFilter, fn ($q) => $q->whereHas('project', fn ($pq) => $pq->where('status', $this->projectStatusFilter)))
            ->when($searchTerm, fn ($q) => $this->applySearchFilter($q, $searchTerm))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('released_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('released_at', '<=', $this->dateTo))
            ->orderByDesc('released_at')
            ->get();
    }

    private function applySearchFilter($query, $searchTerm)
    {
        $term = '%' . str_replace(' ', '%', $searchTerm) . '%';

        return $query->where(function ($q) use ($term) {
            $q->whereHas('client', fn ($cq) => $cq->where('name', 'like', $term)->orWhere('branch', 'like', $term))
                ->orWhereHas('project', fn ($pq) => $pq->where('name', 'like', $term)->orWhere('reference_code', 'like', $term))
                ->orWhereHas('inventory', fn ($iq) => $iq->where('brand', 'like', $term)->orWhere('description', 'like', $term)->orWhere('category', 'like', $term));
        });
    }

    private function getProjectOptions(): Collection
    {
        return Project::with('client:id,name')->orderBy('name')->get();
    }

    private function getProjectSummaries(): Collection
    {
        return Project::with([
            'client:id,name,branch,client_type',
            'expenses' => fn ($q) => $q->with('inventory')->latest('released_at'),
        ])
            ->withCount('expenses')
            ->withSum('expenses as expenses_total', 'total_cost')
            ->orderByDesc('created_at')
            ->get();
    }

    private function buildSummary(Collection $expenses, float $monthlyTotal): array
    {
        return [
            'total' => $expenses->sum('total_cost'),
            'month' => $monthlyTotal,
            'average' => $expenses->count() ? round($expenses->avg('total_cost'), 2) : 0,
            'count' => $expenses->count(),
        ];
    }

    private function buildReceiptGroups(Collection $expenses): Collection
    {
        return $expenses
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
    }
}
