<!-- livewire:multiple-root-elements -->
<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Activity History</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">View all user actions and changes in the system</p>
    </div>

    {{-- View Mode Toggle --}}
    <div class="mb-6 flex gap-2">
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

    {{-- History Entries --}}
    @if($histories->isEmpty())
        <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <x-heroicon-o-clock class="w-16 h-16 mx-auto text-gray-400 mb-4" />
            <p class="text-gray-600 dark:text-gray-400">No history entries found.</p>
        </div>
    @else
        @if($viewMode === 'grid')
            {{-- Grid View --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                                @endphp
                                @if($approval)
                                    <div class="bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Request by:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $approval->requester->name }}</span>
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
                                        $userRole = auth()->user()?->role ?? auth()->user()?->getRoleNames()?->first() ?? '';
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
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $approval->inventory->brand }} {{ $approval->inventory->description }} ({{ $approval->quantity_requested }})</span>
                                    </div>
                                    @if($approval->expense)
                                        <div class="bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Cost:</span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">₱{{ number_format($approval->expense->total_cost, 2) }}</span>
                                        </div>
                                    @endif
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
                                            <div><strong>Quantity:</strong> {{ $approval->quantity_requested }}</div>
                                            <div><strong>Cost per unit:</strong> ₱{{ number_format($approval->expense->cost_per_unit, 2) }}</div>
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
                        @elseif(($history->action === 'update' || $history->action === 'delete') && ($history->changes || $history->old_values))
                            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded">
                                <div class="text-xs text-blue-700 dark:text-blue-300 mb-1">Details:</div>
                                <div class="text-sm text-blue-900 dark:text-blue-100">
                                    @if($history->action === 'update')
                                        {{ is_array($history->changes ?? []) && is_array($history->old_values ?? []) ? count(array_diff_assoc($history->changes ?? [], $history->old_values ?? [])) : 0 }} field(s) updated
                                    @else
                                        Delete action details
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Action Button --}}
                        @if(($history->action === 'create' && $history->model !== 'MaterialReleaseApproval') || (($history->action === 'update' || $history->action === 'delete') && ($history->changes || $history->old_values)) || $history->action === 'Approval Request Approved' || $history->action === 'Approval Request Declined')
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
                                    @if(($history->action === 'create' && $history->model !== 'MaterialReleaseApproval') || (($history->action === 'update' || $history->action === 'delete') && ($history->changes || $history->old_values)) || $history->action === 'Approval Request Approved' || $history->action === 'Approval Request Declined')
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
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $approval->inventory->brand }} {{ $approval->inventory->description }} ({{ $approval->quantity_requested }})</div>
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
                                    @if($approval->expense)
                                        <div>
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Cost:</span>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">₱{{ number_format($approval->expense->total_cost, 2) }}</div>
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
                                </div>
                            @endif
                        @endif
                    </div>


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
                                            $userRole = auth()->user()?->role ?? auth()->user()?->getRoleNames()?->first();
                                        @endphp
                                        @if(in_array($userRole, ['user', 'system_admin']) && $clientName)
                                            <div><strong>Client:</strong> {{ $clientName }}</div>
                                        @endif
                                        @if(in_array($userRole, ['user', 'system_admin']) && $projectName)
                                            <div><strong>Project:</strong> {{ $projectName }}</div>
                                        @endif
                                        <div><strong>Item:</strong> {{ $approval->inventory->brand }} - {{ $approval->inventory->description }}</div>
                                        <div><strong>Quantity:</strong> {{ $approval->quantity_requested }}</div>
                                        <div><strong>Cost per unit:</strong> ₱{{ number_format($approval->expense->cost_per_unit, 2) }}</div>
                                        @if($selectedHistory->action === 'Material Release Approved' && $approval->reviewer)
                                            <div><strong>Approved by:</strong> {{ $approval->reviewer->name }}</div>
                                        @elseif($selectedHistory->action === 'Approval Request Declined' && $approval->reviewer)
                                            <div><strong>Declined by:</strong> {{ $approval->reviewer->name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- Field Changes Comparison --}}
                        @if($selectedHistory->changes)
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
                                                                    {{ $value ? 'Yes' : 'No' }}
                                                                @elseif(is_array($value) || is_object($value))
                                                                    <pre class="whitespace-pre-wrap text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                                @else
                                                                    {{ $value }}
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
                                    <div class="space-y-4">
                                        @php
                                            $oldValues = is_array($selectedHistory->old_values) ? $selectedHistory->old_values : [];
                                            $changes = is_array($selectedHistory->changes) ? $selectedHistory->changes : [];
                                            $allFields = array_unique(array_merge(array_keys($oldValues), array_keys($changes)));
                                        @endphp
                                        @foreach($allFields as $field)
                                            @php
                                                $oldValue = $oldValues[$field] ?? null;
                                                $newValue = $changes[$field] ?? null;
                                                $hasChanged = isset($newValue) && $newValue !== $oldValue;
                                            @endphp
                                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ ucfirst(str_replace('_', ' ', $field)) }}</label>
                                                @if($hasChanged)
                                                    @if(is_array($newValue) || is_object($newValue))
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                            {{-- Before --}}
                                                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded p-3">
                                                                <div class="text-xs font-semibold text-red-700 dark:text-red-300 mb-1">Before</div>
                                                                <div class="text-sm text-red-900 dark:text-red-100 bg-white dark:bg-gray-700 px-2 py-1 rounded">
                                                                    <pre class="whitespace-pre-wrap">{{ json_encode($oldValue, JSON_PRETTY_PRINT) }}</pre>
                                                                </div>
                                                            </div>
                                                            {{-- After --}}
                                                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded p-3">
                                                                <div class="text-xs font-semibold text-green-700 dark:text-green-300 mb-1">After</div>
                                                                <div class="text-sm text-green-900 dark:text-green-100 bg-white dark:bg-gray-700 px-2 py-1 rounded">
                                                                    <pre class="whitespace-pre-wrap">{{ json_encode($newValue, JSON_PRETTY_PRINT) }}</pre>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded p-3">
                                                            <div class="text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-700 px-2 py-1 rounded">
                                                                {!! $this->getHighlightedDiff($oldValue, $newValue) !!}
                                                            </div>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded p-3">
                                                        <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                                                            No changes
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Change Summary --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                            @php
                                $changes = is_array($selectedHistory->changes ?? []) ? $selectedHistory->changes : [];
                                $oldValues = is_array($selectedHistory->old_values ?? []) ? $selectedHistory->old_values : [];
                            @endphp
                            @if($selectedHistory->action === 'delete')
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Deletion Summary</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ count($changes) - 1 }}</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Details Captured</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $selectedHistory->model_name }}</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Item Type</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $selectedHistory->created_at->diffForHumans() }}</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Time of Deletion</div>
                                    </div>
                                </div>
                            @else
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Change Summary</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ count($changes) }}</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Fields Logged</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ is_array($changes) && is_array($oldValues) ? count(array_diff_assoc($changes, $oldValues)) : 0 }}</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Fields Changed</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $selectedHistory->created_at->diffForHumans() }}</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Time of Change</div>
                                    </div>
                                </div>
                            @endif
                        </div>

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
                                            <p class="mt-1">{{ $changes['quantity'] }} units</p>
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

