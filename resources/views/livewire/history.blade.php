    <!-- livewire:multiple-root-elements -->
<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Activity History</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">View all user actions and changes in the system</p>

        {{-- TEST BUTTON: Add sample inbound stock for testing --}}
        @if(app()->environment(['local', 'development']))
        <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
            <div class="flex items-center justify-between">
                <div class="text-sm text-yellow-800 dark:text-yellow-200">
                    <strong>🧪 Test Inbound Stock Recording:</strong> Click to add sample stock and see it in history
                </div>
                <button wire:click="addTestInboundStock"
                        class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded">
                    Add Test Stock
                </button>
            </div>
        </div>
        @endif
    </div>

    {{-- Summary Stats --}}
    <div class="mb-10 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl border border-primary-500/40 bg-primary-500/10 p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-primary-200">Total Logs</p>
            <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($summary['total'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-500/40 bg-amber-500/10 p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-200">This Month</p>
            <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($summary['month'] ?? 0) }}</p>
        </div>  
        <div class="rounded-2xl border border-sky-500/40 bg-sky-500/10 p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-sky-200">Avg / Day</p>
            <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($summary['average'] ?? 0, 1) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-500/40 bg-emerald-500/10 p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-200">Activities Logged</p>
            <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($summary['count'] ?? 0) }}</p>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="mb-6 space-y-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search by action, model, user, content..."
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
            </div>
            <div class="flex gap-2">
                <input wire:model.live="dateFrom"
                    type="date"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                <input wire:model.live="dateTo"
                    type="date"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
            </div>
        </div>
        <div class="flex gap-2">
            <button wire:click="setViewMode('grid')"
                class="px-4 py-2 font-medium transition-colors {{ $viewMode === 'grid' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 dark:text-gray-400 hover:text-blue-600' }}">
                <x-heroicon-o-squares-2x2 class="w-4 h-4 inline mr-1" />
                Grid View
            </button>
            <button wire:click="setViewMode('table')"
                class="px-4 py-2 font-medium transition-colors {{ $viewMode === 'table' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 dark:text-gray-400 hover:text-blue-600' }}">
                <x-heroicon-o-table-cells class="w-4 h-4 inline mr-1" />
                Table View
            </button>
        </div>
    </div>

    {{-- History Entries --}}
    @if($histories->isEmpty())
        <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <x-heroicon-o-clock class="w-16 h-16 mx-auto text-gray-400 mb-4" />
            <p class="text-gray-600 dark:text-gray-400">No history entries found.</p>
        </div>
    @else
        @if($viewMode === 'grid')
            {{-- Grid View --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($histories as $history)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        {{-- Header --}}
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                @switch($history->action)
                                    @case('create')
                                        <x-heroicon-o-plus-circle class="w-5 h-5 text-green-500" />
                                        @break
                                    @case('update')
                                        <x-heroicon-o-pencil class="w-5 h-5 text-blue-500" />
                                        @break
                                    @case('delete')
                                        <x-heroicon-o-trash class="w-5 h-5 text-red-500" />
                                        @break
                                    @case('login')
                                        <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 text-purple-500" />
                                        @break
                                    @case('logout')
                                        <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5 text-gray-500" />
                                        @break
                                    @default
                                        <x-heroicon-o-document-text class="w-5 h-5 text-gray-500" />
                                @endswitch
                                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $history->action_description }}</span>
                            </div>
                            <span class="text-xs text-gray-500">{{ $history->created_at->diffForHumans() }}</span>
                        </div>

                        {{-- User Info --}}
                        <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2 mb-2">
                                <x-heroicon-o-user class="w-4 h-4 text-gray-500" />
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $history->user->name }}</span>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                {{ $history->created_at->format('M d, Y - h:i A') }}
                            </div>
                        </div>


                        {{-- Action Details --}}
                        <div class="space-y-2 mb-4">
                            <div class="bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Model:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $history->model_name }}</span>
                            </div>
                            @if($history->model_id)
                                <div class="bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">ID:</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $history->model_id }}</span>
                                </div>
                            @endif
                            @if($history->model === 'MaterialReleaseApproval')
                                @php
                                    $approval = App\Models\MaterialReleaseApproval::with('requester', 'reviewer', 'inventory', 'expense', 'expense.client', 'expense.project')->find($history->model_id);
                                    // Properly decode history changes - handle both string and array
                                    $historyChanges = [];
                                    if (is_string($history->changes)) {
                                        $historyChanges = json_decode($history->changes, true) ?? [];
                                    } elseif (is_array($history->changes)) {
                                        $historyChanges = $history->changes;
                                    }

                                    $historyCost = $historyChanges['cost_per_unit'] ?? $approval->expense->cost_per_unit ?? 0;
                                @endphp
                                @if($approval)
                                    <div class="{{ $approval->requester->role === 'system_admin' ? 'bg-gradient-to-r from-orange-50 to-yellow-50 dark:from-orange-900/20 dark:to-yellow-900/20' : 'bg-gray-50 dark:bg-gray-900' }} px-3 py-2 rounded">
                                        <span class="text-xs font-semibold {{ $approval->requester->role === 'system_admin' ? 'text-orange-700 dark:text-orange-300' : 'text-gray-700 dark:text-gray-300' }}">Request by:</span>
                                        <span class="text-sm font-medium {{ $approval->requester->role === 'system_admin' ? 'text-orange-900 dark:text-orange-100' : 'text-gray-900 dark:text-white' }}">{{ $approval->requester->name }}</span>
                                    </div>
                                    @php
                                        $clientName = null;
                                        $projectName = null;

                                        // Try to get from relationship first
                                        if ($approval->expense && $approval->expense->client) {
                                            $clientName = $approval->expense->client->name;
                                        }
                                        // Fallback to history changes
                                        if (!$clientName && !empty($historyChanges['client']) && $historyChanges['client'] !== 'N/A') {
                                            $clientName = $historyChanges['client'];
                                        }

                                        // Try to get project from relationship first
                                        if ($approval->expense && $approval->expense->project) {
                                            $projectName = $approval->expense->project->name;
                                        }
                                        // Fallback to history changes
                                        if (!$projectName && !empty($historyChanges['project']) && $historyChanges['project'] !== 'N/A') {
                                            $projectName = $historyChanges['project'];
                                        }

                                        // Check user role - visible to user and system_admin
                                        $userRole = auth()->user()?->role ?? '';
                                    @endphp
                                    @if(in_array($userRole, ['user', 'system_admin']) && $clientName)
                                        <div class="bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded border border-blue-200 dark:border-blue-800">
                                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Client:</span>
                                            <span class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ $clientName }}</span>
                                        </div>
                                    @endif
                                    @if(in_array($userRole, ['user', 'system_admin']) && $projectName)
                                        <div class="bg-purple-50 dark:bg-purple-900/20 px-3 py-2 rounded border border-purple-200 dark:border-purple-800">
                                            <span class="text-xs font-semibold text-purple-700 dark:text-purple-300">Project:</span>
                                            <span class="text-sm font-medium text-purple-900 dark:text-purple-100">{{ $projectName }}</span>
                                        </div>
                                    @endif
                                    <div class="bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Materials:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $approval->inventory->brand }} - {{ $approval->inventory->description }} ({{ number_format($approval->quantity_requested) }} units @ ₱{{ number_format($historyCost, 2) }})</span>
                                    </div>
                                    @if($approval->status === 'approved' && $approval->reviewer)
                                        <div class="bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Approve by:</span>
                                            <span class="text-sm font-medium text-green-600">{{ $approval->reviewer->name }}</span>
                                        </div>
                                    @elseif($approval->status === 'declined' && $approval->reviewer)
                                        <div class="bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Decline by:</span>
                                            <span class="text-sm font-medium text-red-600">{{ $approval->reviewer->name }}</span>
                                        </div>
                                    @endif
                                @endif
                            @elseif($history->model === 'expense' && $history->action === 'update')
                                @php
                                    $expense = App\Models\Expense::with('client', 'project', 'inventory')->find($history->model_id);
                                    // Decode old values and changes
                                    $oldValues = is_array($history->old_values) ? $history->old_values : (is_string($history->old_values) ? json_decode($history->old_values, true) ?? [] : []);
                                    $changes = is_array($history->changes) ? $history->changes : (is_string($history->changes) ? json_decode($history->changes, true) ?? [] : []);
                                    // Check user role - visible to user and system_admin
                                    $userRole = auth()->user()?->role ?? '';
                                @endphp
                                @if($expense)
                                    @if(in_array($userRole, ['user', 'system_admin']) && $expense->client)
                                        <div class="bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded border border-blue-200 dark:border-blue-800">
                                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Client:</span>
                                            <span class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ $expense->client->name }}</span>
                                        </div>
                                    @endif
                                    @if(in_array($userRole, ['user', 'system_admin']) && $expense->project)
                                        <div class="bg-purple-50 dark:bg-purple-900/20 px-3 py-2 rounded border border-purple-200 dark:border-purple-800">
                                            <span class="text-xs font-semibold text-purple-700 dark:text-purple-300">Project:</span>
                                            <span class="text-sm font-medium text-purple-900 dark:text-purple-100">{{ $expense->project->name }} ({{ $expense->project->reference_code }})</span>
                                        </div>
                                    @endif
                                    <div class="bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Item:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $expense->inventory->brand }} {{ $expense->inventory->description }}</span>
                                    </div>
                                    @if(isset($oldValues['cost_per_unit']) && isset($changes['cost_per_unit']))
                                        <div class="bg-green-50 dark:bg-green-900/20 px-3 py-2 rounded border border-green-200 dark:border-green-800">
                                            <span class="text-xs font-semibold text-green-700 dark:text-green-300">Cost per Unit:</span>
                                            <span class="text-sm font-medium text-green-900 dark:text-green-100">₱{{ number_format($oldValues['cost_per_unit'], 2) }} → ₱{{ number_format($changes['cost_per_unit'], 2) }}</span>
                                        </div>
                                        @if($expense->quantity_used > 1)
                                            <div class="bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded border border-blue-200 dark:border-blue-800">
                                                <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Quantity:</span>
                                                <span class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ number_format($expense->quantity_used) }} units</span>
                                            </div>
                                        @endif
                                    @endif
                                @endif
                            @elseif($history->model === 'project' && $history->action === 'update')
                                @php
                                    $project = App\Models\Project::with('client')->find($history->model_id);
                                    // Check user role - visible to user and system_admin
                                    $userRole = auth()->user()?->role ?? '';
                                @endphp
                                @if($project)
                                    @if(in_array($userRole, ['user', 'system_admin']) && $project->client)
                                        <div class="bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded border border-blue-200 dark:border-blue-800">
                                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Client:</span>
                                            <span class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ $project->client->name }}</span>
                                        </div>
                                    @endif
                                    <div class="bg-purple-50 dark:bg-purple-900/20 px-3 py-2 rounded border border-purple-200 dark:border-purple-800">
                                        <span class="text-xs font-semibold text-purple-700 dark:text-purple-300">Project:</span>
                                        <span class="text-sm font-medium text-purple-900 dark:text-purple-100">{{ $project->name }} ({{ $project->reference_code }})</span>
                                    </div>
                                @endif
                            @elseif($history->action === 'Inbound Stock Added')
                                @php
                                    $changes = is_array($history->changes) ? $history->changes : (is_string($history->changes) ? json_decode($history->changes, true) ?? [] : []);
                                    $reference = $changes['reference'] ?? null;
                                    $isReleaseRelated = $reference && str_starts_with($reference, 'expense_');
                                @endphp
                                <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 px-3 py-2 rounded border border-green-200 dark:border-green-800">
                                    <div class="text-xs text-green-700 dark:text-green-300 mb-1 font-semibold">{{ $isReleaseRelated ? 'Inbound Release Stock' : 'Stock Addition Details' }}</div>
                                    <div class="text-sm text-green-900 dark:text-green-100 space-y-1">
                                        <div><strong>Quantity Added:</strong> {{ number_format($changes['quantity'] ?? 0) }} units</div>
                                        <div><strong>Item:</strong> {{ $changes['inventory_item'] ?? 'Unknown Item' }}</div>
                                        @if(!empty($changes['supplier']))
                                            <div><strong>Supplier:</strong> {{ $changes['supplier'] }}</div>
                                        @endif
                                        @if(!empty($changes['location']))
                                            <div><strong>Location:</strong> {{ $changes['location'] }}</div>
                                        @endif
                                        @if($isReleaseRelated)
                                            <div><strong>Related to Release</strong></div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Changes Summary --}}
                        @if($history->action === 'create')
                            @if($history->model === 'MaterialReleaseApproval')
                                @php
                                    $approval = App\Models\MaterialReleaseApproval::with('expense.client', 'expense.project', 'inventory')->find($history->model_id);
                                    // Decode changes from create action
                                    $createChanges = [];
                                    if (is_string($history->changes)) {
                                        $createChanges = json_decode($history->changes, true) ?? [];
                                    } elseif (is_array($history->changes)) {
                                        $createChanges = $history->changes;
                                    }
                                @endphp
                                @if($approval && $approval->expense)
                                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded">
                                        <div class="text-xs text-blue-700 dark:text-blue-300 mb-2 font-semibold">Material Release Request from Masterlist</div>
                                        <div class="text-sm text-blue-900 dark:text-blue-100 space-y-1">
                                            <div><strong>Client:</strong> {{ $approval->expense->client->name ?? $createChanges['client'] ?? 'N/A' }}</div>
                                            @if($approval->expense->project || (!empty($createChanges['project']) && $createChanges['project'] !== 'N/A'))
                                                <div><strong>Project:</strong> {{ $approval->expense->project->name ?? $createChanges['project'] ?? 'N/A' }}</div>
                                            @endif
                                            <div><strong>Item:</strong> {{ $approval->inventory->brand }} - {{ $approval->inventory->description }}</div>
                                            <div><strong>Quantity:</strong> {{ number_format($approval->quantity_requested) }}</div>
                                            @php
                                                $historyCost = null;
                                                if (is_string($selectedHistory->changes)) {
                                                    $historyChanges = json_decode($selectedHistory->changes, true) ?? [];
                                                } elseif (is_array($selectedHistory->changes)) {
                                                    $historyChanges = $selectedHistory->changes;
                                                }
                                                $historyCost = $historyChanges['cost_per_unit'] ?? $approval->expense->cost_per_unit ?? 0;
                                            @endphp
                                            <div><strong>Cost per unit:</strong> ₱{{ number_format($historyCost, 2) }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 rounded">
                                        <div class="text-xs text-green-700 dark:text-green-300 mb-1">Details:</div>
                                        <div class="text-sm text-green-900 dark:text-green-100">
                                            New {{ $history->model_name }} created
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 rounded">
                                    <div class="text-xs text-green-700 dark:text-green-300 mb-1">Details:</div>
                                    <div class="text-sm text-green-900 dark:text-green-100">
                                        New {{ $history->model_name }} created
                                    </div>
                                </div>
                            @endif
                        @elseif(($history->action === 'update' || $history->action === 'delete') && ($history->changes || $history->old_values) && $history->action !== 'Stock Movement Recorded')
                            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded">
                                <div class="text-xs text-blue-700 dark:text-blue-300 mb-1">Details:</div>
                                <div class="text-sm text-blue-900 dark:text-blue-100">
                                    @if($history->action === 'update')
                                        @php
                                            $changes = is_array($history->changes) ? $history->changes : (is_string($history->changes) ? json_decode($history->changes, true) ?? [] : []);
                                            $oldValues = is_array($history->old_values) ? $history->old_values : (is_string($history->old_values) ? json_decode($history->old_values, true) ?? [] : []);
                                            $changeCount = 0;
                                            $detailsCaptured = 0;

                                            if ($history->model === 'client') {
                                                // For client updates, changes is structured array with old/new/field_name
                                                $changeCount = count($changes);
                                                $detailsCaptured = $changeCount;
                                            } elseif ($history->model === 'expense') {
                                                // For expense updates, changes contains only user-editable fields
                                                $changeCount = count($changes);
                                                $detailsCaptured = $changeCount; // All changes are captured
                                            } elseif ($history->model === 'tool') {
                                                // For tools, always show 7 details captured (user-editable fields)
                                                $detailsCaptured = 7;
                                                $changeCount = $history->action === 'create' ? 7 : count($changes);
                                            } else {
                                                // For other models, changes contains only actual changes
                                                $changeCount = count($changes);
                                                $detailsCaptured = $changeCount;
                                            }
                                        @endphp
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            <div><strong>Details Captured:</strong> {{ number_format($detailsCaptured) }}</div>
                                            <div><strong>Details Changed:</strong> {{ number_format($changeCount) }}</div>
                                        </div>
                                    @else
                                        Delete action details
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Action Button --}}
                        @if(($history->action === 'create' && $history->model !== 'MaterialReleaseApproval') || (($history->action === 'update' || $history->action === 'edit' || $history->action === 'delete') && ($history->changes || $history->old_values)) || $history->action === 'Approval Request Approved' || $history->action === 'Approval Request Declined' || $history->action === 'Material Release Completed' || ($history->action === 'Inbound Stock Added' && ($history->changes || $history->old_values)) || ($history->action === 'Outbound Stock Removed' && ($history->changes || $history->old_values)) || $history->action === 'Stock Movement Recorded')
                            <button wire:click="showChangeDetails({{ $history->id }})"
                                class="w-full px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors">
                                <x-heroicon-o-eye class="w-4 h-4 inline mr-1" />
                                View Details
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            {{-- Table View --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Model</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date/Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($histories as $history)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        @switch($history->action)
                                            @case('create')
                                                <x-heroicon-o-plus-circle class="w-5 h-5 text-green-500" />
                                                @break
                                            @case('update')
                                                <x-heroicon-o-pencil class="w-5 h-5 text-blue-500" />
                                                @break
                                            @case('delete')
                                                <x-heroicon-o-trash class="w-5 h-5 text-red-500" />
                                                @break
                                            @case('login')
                                                <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 text-purple-500" />
                                                @break
                                            @case('logout')
                                                <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5 text-gray-500" />
                                                @break
                                            @default
                                                <x-heroicon-o-document-text class="w-5 h-5 text-gray-500" />
                                        @endswitch
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $history->action_description }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $history->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $history->model_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $history->created_at->format('M d, Y - h:i A') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if(($history->action === 'create' && $history->model !== 'MaterialReleaseApproval') || (($history->action === 'update' || $history->action === 'edit' || $history->action === 'delete') && ($history->changes || $history->old_values)) || $history->action === 'Approval Request Approved' || $history->action === 'Approval Request Declined' || $history->action === 'Material Release Completed' || ($history->action === 'Inbound Stock Added' && ($history->changes || $history->old_values)) || ($history->action === 'Outbound Stock Removed' && ($history->changes || $history->old_values)) || $history->action === 'Stock Movement Recorded')
                                        <button wire:click="showChangeDetails({{ $history->id }})"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <x-heroicon-o-eye class="w-4 h-4 inline mr-1" />
                                            View Details
                                        </button>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    {{-- Pagination --}}
    @if($histories->hasPages())
        <div class="mt-6 mb-20 text-center">
            {{ $histories->links() }}
        </div>
    @endif

    {{-- Change Details Modal --}}
    @if($showChangeModal && $selectedHistory)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click="closeChangeModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-y-auto"
                @click.stop="$event.stopPropagation()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Change Details</h2>
                        <button wire:click="closeChangeModal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    {{-- History Info --}}
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Action:</span>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedHistory->action_description }}</div>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">User:</span>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedHistory->user->name }}</div>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Model:</span>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedHistory->model_name }}</div>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Date/Time:</span>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedHistory->created_at->format('M d, Y - h:i A') }}</div>
                            </div>
                        </div>
                        @if($selectedHistory->model === 'MaterialReleaseApproval')
                            @php $approval = App\Models\MaterialReleaseApproval::with('requester', 'reviewer', 'inventory', 'expense', 'expense.client', 'expense.project')->find($selectedHistory->model_id); @endphp
                            @if($approval)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div>
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Request by:</span>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $approval->requester->name }}</div>
                                    </div>
                                    <div>
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Materials:</span>
                                        @php
                                            $historyCost = null;
                                            if (is_string($selectedHistory->changes)) {
                                                $historyChanges = json_decode($selectedHistory->changes, true) ?? [];
                                            } elseif (is_array($selectedHistory->changes)) {
                                                $historyChanges = $selectedHistory->changes;
                                            }
                                            $historyCost = $historyChanges['cost_per_unit'] ?? $approval->expense->cost_per_unit ?? 0;
                                        @endphp
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $approval->inventory->brand }} - {{ $approval->inventory->description }} ({{ number_format($approval->quantity_requested) }} units @ ₱{{ number_format($historyCost, 2) }})</div>
                                    </div>
                                    @if($approval->status === 'approved' && $approval->expense && $approval->expense->client)
                                        <div>
                                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Client:</span>
                                            <div class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ $approval->expense->client->name }}</div>
                                        </div>
                                    @endif
                                    @if($approval->status === 'approved' && $approval->expense && $approval->expense->project)
                                        <div>
                                            <span class="text-xs font-semibold text-purple-700 dark:text-purple-300">Project:</span>
                                            <div class="text-sm font-medium text-purple-900 dark:text-purple-100">{{ $approval->expense->project->name }}</div>
                                        </div>
                                    @endif
                                    @if($approval->status === 'approved' && $approval->reviewer)
                                        <div>
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Approve by:</span>
                                            <div class="text-sm font-medium text-green-600">{{ $approval->reviewer->name }}</div>
                                        </div>
                                    @elseif($approval->status === 'declined' && $approval->reviewer)
                                        <div>
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Decline by:</span>
                                            <div class="text-sm font-medium text-red-600">{{ $approval->reviewer->name }}</div>
                                        </div>
                                    @endif

                                    {{-- Related Stock Movements Modal --}}
                                    @if($showRelatedMovementsModal && $relatedMovements)
                                        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click="closeRelatedMovementsModal">
                                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-y-auto"
                                                @click.stop="$event.stopPropagation()">
                                                <div class="p-6">
                                                    <div class="flex items-center justify-between mb-6">
                                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Related Stock Movements</h2>
                                                        <button wire:click="closeRelatedMovementsModal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                                            <x-heroicon-o-x-mark class="w-6 h-6" />
                                                        </button>
                                                    </div>

                                                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-lg">
                                                        <div class="text-sm text-blue-900 dark:text-blue-100">
                                                            <div><strong>Client:</strong> {{ $relatedMovements['client_name'] ?? 'N/A' }}</div>
                                                            @if(!empty($relatedMovements['project_name']))
                                                                <div><strong>Project:</strong> {{ $relatedMovements['project_name'] }}</div>
                                                            @endif
                                                            <div><strong>Total Movements:</strong> {{ count($relatedMovements['movements'] ?? []) }}</div>
                                                        </div>
                                                    </div>

                                                    @if(!empty($relatedMovements['movements']))
                                                        <div class="overflow-x-auto">
                                                            <table class="min-w-full divide-y divide-blue-200 dark:divide-blue-700">
                                                                <thead class="bg-blue-50 dark:bg-blue-900/20">
                                                                    <tr>
                                                                        <th class="px-4 py-3 text-left text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">Type</th>
                                                                        <th class="px-4 py-3 text-left text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">Item</th>
                                                                        <th class="px-4 py-3 text-left text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">Quantity</th>
                                                                        <th class="px-4 py-3 text-left text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">Previous Stock</th>
                                                                        <th class="px-4 py-3 text-left text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">New Stock</th>
                                                                        <th class="px-4 py-3 text-left text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">Cost/Unit</th>
                                                                        <th class="px-4 py-3 text-left text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">Total Cost</th>
                                                                        <th class="px-4 py-3 text-left text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">User</th>
                                                                        <th class="px-4 py-3 text-left text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">Date/Time</th>
                                                                        <th class="px-4 py-3 text-left text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">Notes</th>
                                                                        <th class="px-4 py-3 text-left text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">Supplier</th>
                                                                        <th class="px-4 py-3 text-left text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">Date Received</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-blue-200 dark:divide-blue-700">
                                                                    @foreach($relatedMovements['movements'] as $movement)
                                                                        <tr>
                                                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-900 dark:text-blue-100">{{ $movement->movement_type_display }}</td>
                                                                            <td class="px-4 py-4 text-sm text-blue-900 dark:text-blue-100">{{ $movement->inventory->brand }} {{ $movement->inventory->description }}</td>
                                                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-900 dark:text-blue-100">{{ number_format(abs($movement->quantity)) }} units</td>
                                                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-900 dark:text-blue-100">{{ number_format($movement->previous_quantity) }}</td>
                                                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-900 dark:text-blue-100">{{ number_format($movement->new_quantity) }}</td>
                                                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-600 dark:text-blue-400">
                                                                                @if($movement->cost_per_unit)
                                                                                    ₱{{ number_format($movement->cost_per_unit, 2) }}
                                                                                @else
                                                                                    -
                                                                                @endif
                                                                            </td>
                                                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-purple-600 dark:text-purple-400">
                                                                                @if($movement->total_cost)
                                                                                    ₱{{ number_format($movement->total_cost, 2) }}
                                                                                @else
                                                                                    -
                                                                                @endif
                                                                            </td>
                                                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-900 dark:text-blue-100">{{ $movement->user->name }}</td>
                                                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-900 dark:text-blue-100">{{ $movement->created_at->format('M d, Y - h:i A') }}</td>
                                                                            <td class="px-4 py-4 text-sm text-blue-900 dark:text-blue-100">
                                                                                @if($movement->notes)
                                                                                    {!! nl2br(str_replace(' - ', '<br>', $movement->notes)) !!}
                                                                                @else
                                                                                    -
                                                                                @endif
                                                                            </td>
                                                                            <td class="px-4 py-4 text-sm text-blue-900 dark:text-blue-100">{{ $movement->supplier ?: '-' }}</td>
                                                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-900 dark:text-blue-100">{{ $movement->date_received ? $movement->date_received->format('M d, Y') : '-' }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                                            <x-heroicon-o-queue-list class="w-12 h-12 mx-auto mb-4 opacity-50" />
                                                            <p>No related stock movements found for this project/client.</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @elseif($selectedHistory->model === 'project')
                            @php $project = App\Models\Project::with('client')->find($selectedHistory->model_id); @endphp
                            @if($project)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div>
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Project:</span>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $project->name }}</div>
                                    </div>
                                    <div>
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Reference Code:</span>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $project->reference_code }}</div>
                                    </div>
                                    @if($project->client)
                                        <div>
                                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Client:</span>
                                            <div class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ $project->client->name }}</div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @elseif($selectedHistory->model === 'expense')
                            @php $expense = App\Models\Expense::with('project.client')->find($selectedHistory->model_id); @endphp
                            @if($expense && $expense->project)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div>
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Project:</span>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $expense->project->name }}</div>
                                    </div>
                                    <div>
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Reference Code:</span>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $expense->project->reference_code }}</div>
                                    </div>
                                    @if($expense->project->client)
                                        <div>
                                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Client:</span>
                                            <div class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ $expense->project->client->name }}</div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endif
                    </div>


                    {{-- Header for Material Release Completed and Inbound Stock Added --}}
                    @if($selectedHistory->action === 'Material Release Completed' || $selectedHistory->action === 'Inbound Stock Added')
                        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $selectedHistory->action === 'Inbound Stock Added' ? 'Inbound Release Details' : 'Release Details' }}</h3>
                        </div>
                    @endif

                    {{-- Release Details for Material Release Completed and Inbound Stock Added --}}
                    @if($selectedHistory->action === 'Material Release Completed' || $selectedHistory->action === 'Inbound Stock Added')
                        @if($selectedHistory->action === 'Material Release Completed')
                            @php
                                $approval = App\Models\MaterialReleaseApproval::with('expense.client', 'expense.project', 'inventory', 'reviewer')->find($selectedHistory->model_id);
                                $expense = $approval ? $approval->expense : null;
                            @endphp
                        @else
                            @php
                                $changes = is_array($selectedHistory->changes) ? $selectedHistory->changes : (is_string($selectedHistory->changes) ? json_decode($selectedHistory->changes, true) ?? [] : []);
                                $reference = $changes['reference'] ?? null;
                                $expense = null;
                                $approval = null;

                                if ($reference && str_starts_with($reference, 'expense_')) {
                                    $expenseId = str_replace('expense_', '', $reference);
                                    $expense = App\Models\Expense::with('client', 'project', 'inventory')->find($expenseId);
                                }
                            @endphp
                        @endif
                        @if($expense)
                            <div class="mb-6">
                                <div class="bg-{{ $selectedHistory->action === 'Inbound Stock Added' ? 'green' : 'blue' }}-50 dark:bg-{{ $selectedHistory->action === 'Inbound Stock Added' ? 'green' : 'blue' }}-900/10 border border-{{ $selectedHistory->action === 'Inbound Stock Added' ? 'green' : 'blue' }}-200 dark:border-{{ $selectedHistory->action === 'Inbound Stock Added' ? 'green' : 'blue' }}-800 rounded-lg p-6">
                                    <div class="text-sm text-{{ $selectedHistory->action === 'Inbound Stock Added' ? 'green' : 'blue' }}-900 dark:text-{{ $selectedHistory->action === 'Inbound Stock Added' ? 'green' : 'blue' }}-100 space-y-2">
                                        @php
                                            $changes = [];
                                            if (is_string($selectedHistory->changes)) {
                                                $changes = json_decode($selectedHistory->changes, true) ?? [];
                                            } elseif (is_array($selectedHistory->changes)) {
                                                $changes = $selectedHistory->changes;
                                            }

                                            $clientName = null;
                                            if ($expense && $expense->client) {
                                                $clientName = $expense->client->name;
                                            } elseif (!empty($changes['client']) && $changes['client'] !== 'N/A') {
                                                $clientName = $changes['client'];
                                            }

                                            $projectName = null;
                                            if ($expense && $expense->project) {
                                                $projectName = $expense->project->name;
                                            } elseif (!empty($changes['project']) && $changes['project'] !== 'N/A') {
                                                $projectName = $changes['project'];
                                            }

                                            $userRole = auth()->user()?->role ?? '';
                                        @endphp
                                        @if(in_array($userRole, ['user', 'system_admin']) && $clientName)
                                            <div><strong>Client:</strong> {{ $clientName }}</div>
                                        @endif
                                        @if(in_array($userRole, ['user', 'system_admin']) && $projectName)
                                            <div><strong>Project:</strong> {{ $projectName }}</div>
                                        @endif
                                        <div><strong>Item:</strong> {{ $expense->inventory->brand }} - {{ $expense->inventory->description }}</div>
                                        <div><strong>Quantity:</strong> {{ $selectedHistory->action === 'Inbound Stock Added' ? number_format($changes['quantity'] ?? 0) : number_format($approval->quantity_requested) }}</div>
                                        @php
                                            $historyCost = null;
                                            if (is_string($selectedHistory->changes)) {
                                                $historyChanges = json_decode($selectedHistory->changes, true) ?? [];
                                            } elseif (is_array($selectedHistory->changes)) {
                                                $historyChanges = $selectedHistory->changes;
                                            }
                                            $historyCost = $historyChanges['cost_per_unit'] ?? $expense->cost_per_unit ?? 0;
                                        @endphp
                                        <div><strong>Cost per unit:</strong> ₱{{ number_format($historyCost, 2) }}</div>
                                        @if($expense->notes)
                                            <div><strong>Notes:</strong></div>
                                            <div class="ml-4 mt-1 text-sm">{!! nl2br(str_replace([' - ', ' ('], ['<br>', '<br>('], $expense->notes)) !!}</div>
                                        @endif
                                        @if($selectedHistory->action === 'Material Release Completed' && $approval && $approval->reviewer)
                                            @if($selectedHistory->action === 'Material Release Approved' && $approval->reviewer)
                                                <div><strong>Approved by:</strong> {{ $approval->reviewer->name }}</div>
                                            @elseif($selectedHistory->action === 'Approval Request Declined' && $approval->reviewer)
                                                <div><strong>Declined by:</strong> {{ $approval->reviewer->name }}</div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- Request Details --}}
                    @if($selectedHistory->model === 'MaterialReleaseApproval')
                        @php $approval = App\Models\MaterialReleaseApproval::with('expense.client', 'expense.project', 'inventory', 'reviewer')->find($selectedHistory->model_id); @endphp
                        @if($approval && $approval->expense)
                            <div class="mb-6">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Request Details</h3>
                                <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                                    <div class="text-sm text-blue-900 dark:text-blue-100 space-y-2">
                                        @php
                                            // Properly decode changes
                                            $changes = [];
                                            if (is_string($selectedHistory->changes)) {
                                                $changes = json_decode($selectedHistory->changes, true) ?? [];
                                            } elseif (is_array($selectedHistory->changes)) {
                                                $changes = $selectedHistory->changes;
                                            }
                                            
                                            // Get client name
                                            $clientName = null;
                                            if ($approval->expense && $approval->expense->client) {
                                                $clientName = $approval->expense->client->name;
                                            } elseif (!empty($changes['client']) && $changes['client'] !== 'N/A') {
                                                $clientName = $changes['client'];
                                            }
                                            
                                            // Get project name
                                            $projectName = null;
                                            if ($approval->expense && $approval->expense->project) {
                                                $projectName = $approval->expense->project->name;
                                            } elseif (!empty($changes['project']) && $changes['project'] !== 'N/A') {
                                                $projectName = $changes['project'];
                                            }
                                            
                                            // Check user role - visible to user and system_admin
                                            $userRole = auth()->user()?->role;
                                        @endphp
                                        @if(in_array($userRole, ['user', 'system_admin']) && $clientName)
                                            <div><strong>Client:</strong> {{ $clientName }}</div>
                                        @endif
                                        @if(in_array($userRole, ['user', 'system_admin']) && $projectName)
                                            <div><strong>Project:</strong> {{ $projectName }}</div>
                                        @endif
                                        <div><strong>Item:</strong> {{ $approval->inventory->brand }} - {{ $approval->inventory->description }}</div>
                                        <div><strong>Quantity:</strong> {{ number_format($approval->quantity_requested) }}</div>
                                        @php
                                            $historyCost = null;
                                            if (is_string($selectedHistory->changes)) {
                                                $historyChanges = json_decode($selectedHistory->changes, true) ?? [];
                                            } elseif (is_array($selectedHistory->changes)) {
                                                $historyChanges = $selectedHistory->changes;
                                            }
                                            $historyCost = $historyChanges['cost_per_unit'] ?? $approval->expense->cost_per_unit ?? 0;
                                        @endphp
                                        <div><strong>Cost per unit:</strong> ₱{{ number_format($historyCost, 2) }}</div>
                                        @if($selectedHistory->action === 'Material Release Approved' && $approval->reviewer)
                                            <div><strong>Approved by:</strong> {{ $approval->reviewer->name }}</div>
                                        @elseif($selectedHistory->action === 'Approval Request Declined' && $approval->reviewer)
                                            <div><strong>Declined by:</strong> {{ $approval->reviewer->name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Inbound Stock Details --}}
                        @if($selectedHistory->action === 'Inbound Stock Added')
                            @php
                                $changes = is_array($selectedHistory->changes) ? $selectedHistory->changes : (is_string($selectedHistory->changes) ? json_decode($selectedHistory->changes, true) ?? [] : []);
                                $inventory = \App\Models\Inventory::find($selectedHistory->model_id);
                                $stockMovement = $changes['stock_movement_id'] ?? null ?
                                    \App\Models\StockMovement::find($changes['stock_movement_id']) : null;
                            @endphp
                            @if($inventory)
                                <div class="mb-6">
                                    <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 rounded-lg p-6">
                                        <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-4">📦 Inbound Stock Details</h3>

                                        {{-- Checklist Format --}}
                                        <div class="space-y-3 text-sm">
                                            <div class="flex items-center gap-3">
                                                <span class="text-green-600 text-lg">✅</span>
                                                <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Date:</strong>
                                                <span class="text-green-900 dark:text-green-100">{{ $selectedHistory->created_at->format('M d, Y - h:i A') }}</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-green-600 text-lg">✅</span>
                                                <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Type:</strong>
                                                <span class="text-green-900 dark:text-green-100">Inbound</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-green-600 text-lg">✅</span>
                                                <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Change:</strong>
                                                <span class="text-green-600 font-semibold">+{{ number_format($changes['quantity'] ?? 0) }} (quantity added)</span>
                                            </div>
                                            @if($stockMovement)
                                                <div class="flex items-center gap-3">
                                                    <span class="text-green-600 text-lg">✅</span>
                                                    <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Before:</strong>
                                                    <span class="text-green-900 dark:text-green-100">{{ number_format($stockMovement->previous_quantity) }} (previous stock level)</span>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span class="text-green-600 text-lg">✅</span>
                                                    <strong class="text-green-900 dark:text-green-100 min-w-[100px]">After:</strong>
                                                    <span class="text-green-900 dark:text-green-100">{{ number_format($stockMovement->new_quantity) }} (new stock level)</span>
                                                </div>
                                            @endif
                                            <div class="flex items-center gap-3">
                                                <span class="text-green-600 text-lg">✅</span>
                                                <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Cost/Unit:</strong>
                                                <span class="text-green-900 dark:text-green-100">{{ ($changes['cost_per_unit'] ?? 0) > 0 ? '₱' . number_format($changes['cost_per_unit'], 2) : '-' }}</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-green-600 text-lg">✅</span>
                                                <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Total Cost:</strong>
                                                <span class="text-green-900 dark:text-green-100">{{ ($changes['total_cost'] ?? 0) > 0 ? '₱' . number_format($changes['total_cost'], 2) . ' (calculated total)' : '- (calculated total)' }}</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-green-600 text-lg">✅</span>
                                                <strong class="text-green-900 dark:text-green-100 min-w-[100px]">User:</strong>
                                                <span class="text-green-900 dark:text-green-100">{{ $selectedHistory->user->name }}</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-green-600 text-lg">✅</span>
                                                <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Notes:</strong>
                                                <span class="text-green-900 dark:text-green-100">{{ $changes['notes'] ?? 'Test inbound stock addition from History page' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- Stock Movement History --}}
                        @if(in_array($selectedHistory->action, ['Outbound Stock Removed']))
                            @php
                                $inventory = \App\Models\Inventory::find($selectedHistory->model_id);
                                $stockMovements = $inventory ? \App\Models\StockMovement::where('inventory_id', $inventory->id)
                                    ->with('user')
                                    ->orderBy('created_at', 'desc')
                                    ->get() : collect();
                            @endphp
                            @if($stockMovements->isNotEmpty())
                                <div class="mb-6">
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Stock Movement History</h3>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead class="bg-gray-50 dark:bg-gray-700">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Change</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Before</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">After</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cost/Unit</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total Cost</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">User</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach($stockMovements as $movement)
                                                    <tr>
                                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">{{ $movement->created_at->format('M d, Y H:i') }}</td>
                                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">{{ $movement->movement_type_display }}</td>
                                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">
                                                            <span class="{{ $movement->quantity_change > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                                {{ number_format($movement->quantity_change) }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-red-600 dark:text-red-400">{{ number_format($movement->previous_quantity) }}</td>
                                                        <td class="px-4 py-2 text-sm text-green-600 dark:text-green-400">{{ number_format($movement->new_quantity) }}</td>
                                                        <td class="px-4 py-2 text-sm text-blue-600 dark:text-blue-400">
                                                            @if($movement->cost_per_unit)
                                                                ₱{{ number_format($movement->cost_per_unit, 2) }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-purple-600 dark:text-purple-400">
                                                            @if($movement->total_cost)
                                                                ₱{{ number_format($movement->total_cost, 2) }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">{{ $movement->user ? $movement->user->name : 'Unknown' }}</td>
                                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">
                                                            @if($movement->notes)
                                                                {!! nl2br(str_replace(' - ', '<br>', $movement->notes)) !!}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- Stock Movements Log for Material Release --}}
                        @if($selectedHistory->model === 'MaterialReleaseApproval')
                            @php
                                $approval = App\Models\MaterialReleaseApproval::with('expense')->find($selectedHistory->model_id);
                                $stockMovements = collect();
                                if ($approval) {
                                    // Try by reference first
                                    if ($approval->expense) {
                                        $stockMovements = App\Models\StockMovement::where('reference', 'expense_' . $approval->expense->id)
                                            ->with('user')
                                            ->orderBy('created_at', 'desc')
                                            ->get();
                                    }

                                    // If no movements found by reference, try by inventory and time
                                    if ($stockMovements->isEmpty()) {
                                        $historyTime = $selectedHistory->created_at;
                                        $stockMovements = App\Models\StockMovement::where('inventory_id', $approval->inventory_id)
                                            ->where('movement_type', 'outbound')
                                            ->where('quantity', -$approval->quantity_requested)
                                            ->where('user_id', $approval->requested_by)
                                            ->where('created_at', '>=', $historyTime->copy()->subMinutes(5))
                                            ->where('created_at', '<=', $historyTime->copy()->addMinutes(5))
                                            ->with('user')
                                            ->orderBy('created_at', 'desc')
                                            ->get();
                                    }
                                }
                            @endphp
                            @if($stockMovements->isNotEmpty())
                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mt-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100">
                                            <x-heroicon-o-queue-list class="w-5 h-5 inline mr-2" />
                                            Stock Movements Log
                                        </h3>
                                        @if($approval && $approval->expense && ($approval->expense->project || $approval->expense->client))
                                            <button wire:click="openRelatedMovementsModal({{ $approval->id }})"
                                                class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                <x-heroicon-o-eye class="w-4 h-4 inline mr-1" />
                                                View Related Logs
                                            </button>
                                        @endif
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead class="bg-gray-50 dark:bg-gray-700">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Change</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Before</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">After</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cost/Unit</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total Cost</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">User</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach($stockMovements as $movement)
                                                    <tr>
                                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">{{ $movement->created_at->format('M d, Y H:i') }}</td>
                                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">{{ $movement->movement_type_display }}</td>
                                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">
                                                            <span class="{{ $movement->quantity_change > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                                {{ number_format($movement->quantity_change) }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-red-600 dark:text-red-400">{{ number_format($movement->previous_quantity) }}</td>
                                                        <td class="px-4 py-2 text-sm text-green-600 dark:text-green-400">{{ number_format($movement->new_quantity) }}</td>
                                                        <td class="px-4 py-2 text-sm text-blue-600 dark:text-blue-400">
                                                            @if($movement->cost_per_unit)
                                                                ₱{{ number_format($movement->cost_per_unit, 2) }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-purple-600 dark:text-purple-400">
                                                            @if($movement->total_cost)
                                                                ₱{{ number_format($movement->total_cost, 2) }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">{{ $movement->user ? $movement->user->name : 'Unknown' }}</td>
                                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">
                                                            @if($movement->notes)
                                                                {!! nl2br(str_replace(' - ', '<br>', $movement->notes)) !!}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        @endif
                    @else
                        {{-- Field Changes Comparison --}}
                        @if($selectedHistory->changes && $selectedHistory->action !== 'Inbound Stock Added')
                            <div class="mb-6">
                                @if($selectedHistory->action === 'delete')
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Deleted Item Details</h3>
                                    <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-lg p-6">
                                        <div class="flex items-center gap-2 mb-4">
                                            <x-heroicon-o-trash class="w-6 h-6 text-red-500" />
                                            <span class="text-lg font-medium text-red-900 dark:text-red-100">This {{ $selectedHistory->model_name }} was permanently deleted</span>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @if(is_array($selectedHistory->changes))
                                                @foreach($selectedHistory->changes as $field => $value)
                                                    @if($field !== 'deleted' && $value !== null && $value !== '')
                                                        <div class="bg-white dark:bg-gray-800 p-3 rounded border border-red-200 dark:border-red-700">
                                                            <div class="text-xs font-semibold text-red-700 dark:text-red-300 uppercase tracking-wide">{{ ucfirst(str_replace('_', ' ', $field)) }}</div>
                                                            <div class="text-sm text-red-900 dark:text-red-100 mt-1">
                                                                @if(is_bool($value))
                                                                    @if($value)
                                                                        Yes
                                                                    @else
                                                                        <span class="text-gray-500 dark:text-gray-400 italic">No changes</span>
                                                                    @endif
                                                                @elseif(is_array($value) || is_object($value))
                                                                    <pre class="whitespace-pre-wrap text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                                @else
                                                                    @if(is_numeric($value))
                                                                        {{ number_format($value) }}
                                                                    @else
                                                                        {{ $value }}
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Field Changes</h3>
                                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead class="bg-gray-50 dark:bg-gray-700">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Field</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Before</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">After</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                @php
                                                    $oldValues = is_array($selectedHistory->old_values) ? $selectedHistory->old_values : (is_string($selectedHistory->old_values) ? json_decode($selectedHistory->old_values, true) ?? [] : []);
                                                    $changes = is_array($selectedHistory->changes) ? $selectedHistory->changes : (is_string($selectedHistory->changes) ? json_decode($selectedHistory->changes, true) ?? [] : []);

                                                    if ($selectedHistory->model === 'client' && $selectedHistory->action === 'update') {
                                                        // Handle structured client changes
                                                        $allFields = array_keys($changes);
                                                        $changedCount = count($changes);
                                                    } elseif ($selectedHistory->model === 'tool' && $selectedHistory->action === 'update') {
                                                        // For tool updates, calculate actual changed fields
                                                        $allFields = array_unique(array_merge(array_keys($oldValues), array_keys($changes)));
                                                        // Exclude has_image since it's automatically calculated
                                                        $allFields = array_filter($allFields, fn($field) => $field !== 'has_image');
                                                        $changedCount = 0;
                                                        foreach($allFields as $field) {
                                                            $oldValue = $oldValues[$field] ?? null;
                                                            $newValue = $changes[$field] ?? null;
                                                            if (isset($newValue) && $newValue !== $oldValue) {
                                                                $changedCount++;
                                                            }
                                                        }
                                                    } else {
                                                        // Handle other models with flat changes
                                                        $allFields = array_unique(array_merge(array_keys($oldValues), array_keys($changes)));

                                                        // For expense and MaterialReleaseApproval updates, exclude total_cost since it's automatically calculated
                                                        if (($selectedHistory->model === 'expense' || $selectedHistory->model === 'MaterialReleaseApproval') && $selectedHistory->action === 'update') {
                                                            $allFields = array_filter($allFields, fn($field) => $field !== 'total_cost');
                                                        }

                                                        $changedCount = 0;
                                                        foreach($allFields as $field) {
                                                            $oldValue = $oldValues[$field] ?? null;
                                                            $newValue = $changes[$field] ?? null;
                                                            if (isset($newValue) && $newValue !== $oldValue) {
                                                                $changedCount++;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                @if($selectedHistory->model === 'client' && $selectedHistory->action === 'update')
                                                    @foreach($changes as $field => $changeData)
                                                        <tr>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $changeData['field_name'] ?? ucfirst(str_replace('_', ' ', $field)) }}</td>
                                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                                {{ $changeData['old'] ?? 'N/A' }}
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                                {{ $changeData['new'] ?? 'N/A' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    @foreach($allFields as $field)
                                                        @php
                                                            $oldValue = $oldValues[$field] ?? null;
                                                            $newValue = $changes[$field] ?? null;
                                                            $hasChanged = isset($newValue) && $newValue !== $oldValue;
                                                        @endphp
                                                        <tr>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                                @if(isset($oldValues[$field]))
                                                                    @if(is_array($oldValues[$field]) || is_object($oldValues[$field]))
                                                                        <pre class="whitespace-pre-wrap text-xs">{{ json_encode($oldValues[$field], JSON_PRETTY_PRINT) }}</pre>
                                                                    @elseif(is_bool($oldValues[$field]))
                                                                        @if($oldValues[$field])
                                                                            Yes
                                                                        @else
                                                                            <span class="text-gray-500 dark:text-gray-400 italic">No changes</span>
                                                                        @endif
                                                                    @else
                                                                        @if(is_numeric($oldValues[$field]))
                                                                            @if($oldValues[$field] < 0)
                                                                                <span class="text-red-600 dark:text-red-400">{{ number_format($oldValues[$field]) }}</span>
                                                                            @elseif($oldValues[$field] > 0)
                                                                                <span class="text-green-600 dark:text-green-400">{{ number_format($oldValues[$field]) }}</span>
                                                                            @else
                                                                                {{ number_format($oldValues[$field]) }}
                                                                            @endif
                                                                        @else
                                                                            @if(str_starts_with($oldValues[$field], '+'))
                                                                                <span class="text-green-600 dark:text-green-400">{{ $oldValues[$field] }}</span>
                                                                            @elseif(str_starts_with($oldValues[$field], '-'))
                                                                                <span class="text-red-600 dark:text-red-400">{{ $oldValues[$field] }}</span>
                                                                            @else
                                                                                {{ $oldValues[$field] }}
                                                                            @endif
                                                                        @endif
                                                                    @endif
                                                                @else
                                                                    N/A
                                                                @endif
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                                @if($hasChanged)
                                                                    @if(is_array($newValue) || is_object($newValue))
                                                                        <pre class="whitespace-pre-wrap text-xs">{{ json_encode($newValue, JSON_PRETTY_PRINT) }}</pre>
                                                                    @elseif(is_bool($newValue))
                                                                        @if($newValue)
                                                                            Yes
                                                                        @else
                                                                            <span class="text-gray-500 dark:text-gray-400 italic">No changes</span>
                                                                        @endif
                                                                    @else
                                                                        @if(is_numeric($newValue))
                                                                            @if($newValue < 0)
                                                                                <span class="text-red-600 dark:text-red-400">{{ number_format($newValue) }}</span>
                                                                            @elseif($newValue > 0)
                                                                                <span class="text-green-600 dark:text-green-400">{{ number_format($newValue) }}</span>
                                                                            @else
                                                                                {{ number_format($newValue) }}
                                                                            @endif
                                                                        @else
                                                                            @if(str_starts_with($newValue, '+'))
                                                                                <span class="text-green-600 dark:text-green-400">{{ $newValue }}</span>
                                                                            @elseif(str_starts_with($newValue, '-'))
                                                                                <span class="text-red-600 dark:text-red-400">{{ $newValue }}</span>
                                                                            @else
                                                                                {{ $newValue }}
                                                                            @endif
                                                                        @endif
                                                                    @endif
                                                                @else
                                                                    <span class="text-gray-500 dark:text-gray-400 italic">No changes</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Change Summary --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                            @if($selectedHistory->action === 'Inbound Stock Added')
                                @php
                                    $changes = is_array($selectedHistory->changes) ? $selectedHistory->changes : (is_string($selectedHistory->changes) ? json_decode($selectedHistory->changes, true) ?? [] : []);
                                    $inventory = \App\Models\Inventory::find($selectedHistory->model_id);
                                    $stockMovement = $changes['stock_movement_id'] ?? null ?
                                        \App\Models\StockMovement::find($changes['stock_movement_id']) : null;
                                @endphp
                                @if($inventory)
                                    <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-4">📦 Inbound Stock Details</h3>
                                    <div class="space-y-3 text-sm">
                                        <div class="flex items-center gap-3">
                                            <span class="text-green-600 text-lg">✅</span>
                                            <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Date:</strong>
                                            <span class="text-green-900 dark:text-green-100">{{ $selectedHistory->created_at->format('M d, Y - h:i A') }}</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-green-600 text-lg">✅</span>
                                            <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Type:</strong>
                                            <span class="text-green-900 dark:text-green-100">Inbound</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-green-600 text-lg">✅</span>
                                            <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Change:</strong>
                                            <span class="text-green-600 font-semibold">+{{ number_format($changes['quantity'] ?? 0) }} (quantity added)</span>
                                        </div>
                                        @if($stockMovement)
                                            <div class="flex items-center gap-3">
                                                <span class="text-green-600 text-lg">✅</span>
                                                <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Before:</strong>
                                                <span class="text-green-900 dark:text-green-100">{{ number_format($stockMovement->previous_quantity) }} (previous stock level)</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-green-600 text-lg">✅</span>
                                                <strong class="text-green-900 dark:text-green-100 min-w-[100px]">After:</strong>
                                                <span class="text-green-900 dark:text-green-100">{{ number_format($stockMovement->new_quantity) }} (new stock level)</span>
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-3">
                                            <span class="text-green-600 text-lg">✅</span>
                                            <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Cost/Unit:</strong>
                                            <span class="text-green-900 dark:text-green-100">₱{{ number_format($changes['cost_per_unit'] ?? 0, 2) }}</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-green-600 text-lg">✅</span>
                                            <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Total Cost:</strong>
                                            <span class="text-green-900 dark:text-green-100">₱{{ number_format($changes['total_cost'] ?? 0, 2) }} (calculated total)</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-green-600 text-lg">✅</span>
                                            <strong class="text-green-900 dark:text-green-100 min-w-[100px]">User:</strong>
                                            <span class="text-green-900 dark:text-green-100">{{ $selectedHistory->user->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-green-600 text-lg">✅</span>
                                            <strong class="text-green-900 dark:text-green-100 min-w-[100px]">Notes:</strong>
                                            <span class="text-green-900 dark:text-green-100">{{ $changes['notes'] ?? 'Test inbound stock addition from History page' }}</span>
                                        </div>
                                    </div>
                                @endif
                            @else
                                @php
                                    $changes = $selectedHistory->changes ?? [];
                                    if (!is_array($changes)) {
                                        $changes = [];
                                    }
                                    $oldValues = $selectedHistory->old_values ?? [];
                                    if (!is_array($oldValues)) {
                                        $oldValues = [];
                                    }

                                    if ($selectedHistory->model === 'client' && $selectedHistory->action === 'update') {
                                        // For structured client changes
                                        $changedCount = count($changes);
                                        $detailsCaptured = $changedCount;
                                    } elseif ($selectedHistory->model === 'expense' && $selectedHistory->action === 'update') {
                                        // For expense updates, changes contains only user-editable fields
                                        $changedCount = count($changes);
                                        $detailsCaptured = $changeCount; // All changes are captured
                                    } elseif ($selectedHistory->model === 'tool' && $selectedHistory->action === 'update') {
                                        // For tool updates, there are 7 user-editable fields always submitted
                                        $changedCount = count($changes);
                                        $detailsCaptured = 7; // quantity, brand, model, description, ownership_type, released_to, release_date
                                    } else {
                                        // For other models, changes contains only actual changes
                                        $changedCount = count($changes);
                                        $detailsCaptured = $changeCount;
                                    }
                                @endphp
                                @if($selectedHistory->action === 'delete')
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Deletion Summary</h3>
                                @elseif($selectedHistory->action === 'edit')
                                    @php
                                        if ($selectedHistory->model === 'client') {
                                            $actualChanges = $changedCount;
                                            $detailsCaptured = $changedCount;
                                        } elseif ($selectedHistory->model === 'expense') {
                                            // For expense edits, calculate details captured and changed
                                            $oldValues = is_array($selectedHistory->old_values) ? $selectedHistory->old_values : [];
                                            $changes = is_array($selectedHistory->changes) ? $selectedHistory->changes : [];
                                            $allFields = array_unique(array_merge(array_keys($oldValues), array_keys($changes)));
                                            // Exclude total_cost since it's automatically calculated
                                            $allFields = array_filter($allFields, fn($field) => $field !== 'total_cost');
                                            $actualChanges = 0;
                                            foreach($allFields as $field) {
                                                $oldValue = $oldValues[$field] ?? null;
                                                $newValue = $changes[$field] ?? null;
                                                if (isset($newValue) && $newValue !== $oldValue) {
                                                    $actualChanges++;
                                                }
                                            }
                                            $detailsCaptured = count($changes) - (isset($changes['total_cost']) ? 1 : 0);
                                        } else {
                                            // Calculate actual changes for edit actions
                                            $oldValues = is_array($selectedHistory->old_values) ? $selectedHistory->old_values : [];
                                            $changes = is_array($selectedHistory->changes) ? $selectedHistory->changes : [];
                                            $actualChanges = 0;
                                            foreach ($changes as $field => $newValue) {
                                                $oldValue = $oldValues[$field] ?? null;
                                                if ($newValue != $oldValue) {
                                                    $actualChanges++;
                                                }
                                            }
                                            $detailsCaptured = count($changes);
                                        }
                                    @endphp
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Edit Summary</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($detailsCaptured) }}</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">Details Captured</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($actualChanges) }}</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">Details Changed</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $selectedHistory->model_name }}</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">Item Type</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $selectedHistory->created_at->diffForHumans() }}</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">Time Edited</div>
                                        </div>
                                    </div>
                                @else
                                    @if($selectedHistory->action === 'delete')
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Deletion Summary</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($detailsCaptured) }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">Details Captured</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $selectedHistory->model_name }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">Item Type</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $selectedHistory->created_at->diffForHumans() }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">Time of Deletion</div>
                                            </div>
                                        </div>
                                    @else
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Change Summary</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($detailsCaptured) }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">Details Captured</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($changedCount) }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">Details Changed</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $selectedHistory->model_name }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">Item Type</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $selectedHistory->created_at->diffForHumans() }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">Time Updated</div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            @endif
                        </div>

                        {{-- Stock Movement Creation --}}
                        @if(in_array($selectedHistory->action, ['Outbound Stock Removed']))
                            @php
                                $existingMovement = \App\Models\StockMovement::where('inventory_id', $selectedHistory->model_id)
                                    ->where('user_id', $selectedHistory->user_id)
                                    ->where('created_at', '>=', $selectedHistory->created_at->subSeconds(5))
                                    ->where('created_at', '<=', $selectedHistory->created_at->addSeconds(5))
                                    ->first();
                            @endphp
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 mt-6">
                                <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-4">
                                    <x-heroicon-o-plus-circle class="w-5 h-5 inline mr-2" />
                                    Stock Movement
                                </h3>
                                <div class="text-sm text-green-800 dark:text-green-200 mb-4">
                                    @if($existingMovement)
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-600" />
                                            <span class="font-medium">Stock movement already exists for this history entry</span>
                                        </div>
                                        <div class="mt-2 text-xs text-green-700 dark:text-green-300">
                                            Created: {{ $existingMovement->created_at->format('M d, Y - h:i A') }} |
                                            Type: {{ $existingMovement->movement_type_display }} |
                                            Quantity: {{ number_format(abs($existingMovement->quantity)) }}
                                        </div>
                                    @else
                                        <p>Create a stock movement record from this history entry to maintain accurate inventory tracking.</p>
                                        <div class="mt-3 p-3 bg-green-100 dark:bg-green-800/50 rounded border border-green-300 dark:border-green-600">
                                            <div class="text-xs text-green-800 dark:text-green-200 space-y-1">
                                                <div><strong>Type:</strong> {{ $selectedHistory->action === 'Inbound Stock Added' ? 'Inbound' : 'Outbound' }}</div>
                                                @php
                                                    $changes = is_array($selectedHistory->changes) ? $selectedHistory->changes : json_decode($selectedHistory->changes ?? '[]', true);
                                                    $oldValues = is_array($selectedHistory->old_values) ? $selectedHistory->old_values : json_decode($selectedHistory->old_values ?? '[]', true);
                                                @endphp
                                                <div><strong>Quantity:</strong> {{ number_format(abs($changes['quantity'] ?? 0)) }}</div>
                                                <div><strong>Previous Stock:</strong> {{ number_format($oldValues['quantity'] ?? 0) }}</div>
                                                <div><strong>New Stock:</strong> {{ number_format($changes['quantity'] ?? 0) }}</div>
                                                @if(!empty($changes['supplier']))
                                                    <div><strong>Supplier:</strong> {{ $changes['supplier'] }}</div>
                                                @endif
                                                @if(!empty($changes['date_received']))
                                                    <div><strong>Date Received:</strong> {{ \Carbon\Carbon::parse($changes['date_received'])->format('M d, Y') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @if(!$existingMovement)
                                    <button wire:click="createStockMovementFromHistory({{ $selectedHistory->id }})"
                                        wire:confirm="Are you sure you want to create a stock movement record from this history entry? This will create an audit trail entry for inventory tracking."
                                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                        <x-heroicon-o-plus-circle class="w-4 h-4 inline mr-2" />
                                        Create Stock Movement
                                    </button>
                                @endif
                            </div>
                        @endif

                        {{-- Approval Summary (if this is an approval action) --}}
                        @if(in_array($selectedHistory->action, ['Approval Request Approved', 'Approval Request Declined', 'Material Release Request Approved', 'Material Release Request Declined']))
                            @php
                                $changes = is_array($selectedHistory->changes ?? []) ? $selectedHistory->changes : [];
                            @endphp
                            <div class="bg-gradient-to-r {{ str_contains($selectedHistory->action, 'Approved') ? 'from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-green-200 dark:border-green-800' : 'from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 border-red-200 dark:border-red-800' }} border rounded-lg p-6 mt-6">
                                <h3 class="text-lg font-semibold {{ str_contains($selectedHistory->action, 'Approved') ? 'text-green-900 dark:text-green-100' : 'text-red-900 dark:text-red-100' }} mb-4">
                                    {{ str_contains($selectedHistory->action, 'Approved') ? '✅ Approval Summary' : '❌ Decline Summary' }}
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm {{ str_contains($selectedHistory->action, 'Approved') ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }}">
                                    @if(!empty($changes['client']))
                                        <div>
                                            <span class="font-semibold">Client:</span>
                                            <p class="mt-1">{{ $changes['client'] }}</p>
                                        </div>
                                    @endif
                                    @if(!empty($changes['project']) && $changes['project'] !== 'N/A')
                                        <div>
                                            <span class="font-semibold">Project:</span>
                                            <p class="mt-1">{{ $changes['project'] }}</p>
                                        </div>
                                    @endif
                                    @if(!empty($changes['material']))
                                        <div>
                                            <span class="font-semibold">Material:</span>
                                            <p class="mt-1">{{ $changes['material'] }}</p>
                                        </div>
                                    @endif
                                    @if(!empty($changes['quantity']))
                                        <div>
                                            <span class="font-semibold">Quantity:</span>
                                            <p class="mt-1">{{ number_format($changes['quantity']) }} units</p>
                                        </div>
                                    @endif
                                    @if(!empty($changes['reviewed_by']))
                                        <div>
                                            <span class="font-semibold">Reviewed by:</span>
                                            <p class="mt-1">{{ $changes['reviewed_by'] }}</p>
                                        </div>
                                    @elseif(!empty($changes['approved_by']))
                                        <div>
                                            <span class="font-semibold">Approved by:</span>
                                            <p class="mt-1">{{ $changes['approved_by'] }}</p>
                                        </div>
                                    @elseif(!empty($changes['declined_by']))
                                        <div>
                                            <span class="font-semibold">Declined by:</span>
                                            <p class="mt-1">{{ $changes['declined_by'] }}</p>
                                        </div>
                                    @endif
                                    @if(!empty($changes['reason']))
                                        <div class="md:col-span-2">
                                            <span class="font-semibold">Reason:</span>
                                            <p class="mt-1">{{ $changes['reason'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

