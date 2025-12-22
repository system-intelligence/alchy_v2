<div>
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-100">Expenses Tracking</h1>
            <p class="text-sm text-gray-400">Monitor client spend, group every release by project, and keep warranties within reach.</p>
        </div>
        @if(auth()->user()->isSystemAdmin())
            <div class="flex flex-wrap gap-3">
                <button wire:click="openProjectModal" class="inline-flex items-center gap-2 rounded-xl bg-[#172033] px-5 py-3 text-sm font-semibold text-primary-100 transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#1f2c43]">
                    <x-heroicon-o-folder-plus class="h-4 w-4" />
                    Add Project
                </button>
                <button wire:click="openClientModal" class="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-primary-500/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600">
                    <x-heroicon-o-user-plus class="h-4 w-4" />
                    Add New Client
                </button>
            </div>
        @endif
    </div>

    <!-- Main Tabs Navigation -->
    <div class="mb-6 border-b border-[#1B2537]">
        <nav class="flex gap-1 overflow-x-auto">
            <button 
                wire:click="$set('activeMainTab', 'clients')"
                class="relative px-6 py-3 text-sm font-semibold transition-all whitespace-nowrap {{ $activeMainTab === 'clients' ? 'text-primary-400' : 'text-gray-400 hover:text-gray-200' }}"
            >
                Our Clients
                @if($activeMainTab === 'clients')
                    <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-primary-500 to-red-500"></span>
                @endif
            </button>
            <button 
                wire:click="$set('activeMainTab', 'projects')"
                class="relative px-6 py-3 text-sm font-semibold transition-all whitespace-nowrap {{ $activeMainTab === 'projects' ? 'text-primary-400' : 'text-gray-400 hover:text-gray-200' }}"
            >
                Project Management
                @if($activeMainTab === 'projects')
                    <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-primary-500 to-red-500"></span>
                @endif
            </button>
            <button 
                wire:click="$set('activeMainTab', 'receipts')"
                class="relative px-6 py-3 text-sm font-semibold transition-all whitespace-nowrap {{ $activeMainTab === 'receipts' ? 'text-primary-400' : 'text-gray-400 hover:text-gray-200' }}"
            >
                Receipt Rollup
                @if($activeMainTab === 'receipts')
                    <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-primary-500 to-red-500"></span>
                @endif
            </button>
        </nav>
    </div>

    @if($showDeleteClientModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur" id="delete-client-modal">
            <div class="w-11/12 max-w-md rounded-2xl border border-[#1B2537] bg-[#121f33] p-6 shadow-2xl">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/10 text-red-300">
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
                        </span>
                        <h3 class="text-lg font-semibold text-white">Delete Client</h3>
                    </div>
                    <p class="text-sm text-gray-400">Deleting this client will permanently remove all associated records. This action cannot be undone.</p>
                    <form wire:submit.prevent="confirmDeleteClient" class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-300">Enter your password to confirm</label>
                            <input type="password" wire:model="deletePassword" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40" />
                            @error('deletePassword') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeModal" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-semibold text-gray-300 transition-colors hover:bg-gray-700/40">Cancel</button>
                            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-red-600/30 transition-colors hover:bg-red-700">Delete Client</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Project Modal -->
    @if($showDeleteProjectModal)
        <div class="fixed inset-0 z-60 flex items-center justify-center bg-black/60 backdrop-blur" id="delete-project-modal">
            <div class="w-11/12 max-w-md rounded-2xl border border-[#1B2537] bg-[#121f33] p-6 shadow-2xl">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/10 text-red-300">
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
                        </span>
                        <h3 class="text-lg font-semibold text-white">Delete Project</h3>
                    </div>
                    <p class="text-sm text-gray-400">Deleting this project will permanently remove all associated records. This action cannot be undone.</p>
                    @if($deleteProjectData)
                        <div class="mt-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                            <h4 class="text-sm font-semibold text-red-200 mb-3">Data to be deleted:</h4>
                            <div class="space-y-2 text-sm text-gray-300">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-400">Expenses:</span>
                                    <span class="font-semibold text-red-200">{{ $deleteProjectData['expense_count'] }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-400">Notes:</span>
                                    <span class="font-semibold text-red-200">{{ $deleteProjectData['note_count'] }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-red-500/20">
                                    <span class="text-gray-400">Total Value:</span>
                                    <span class="font-semibold text-red-200">₱{{ sprintf('%.2f', $deleteProjectData['total_expenses']) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    <form wire:submit.prevent="confirmDeleteProject" class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-300">Enter your password to confirm</label>
                            <input type="password" wire:model="deleteProjectPassword" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40" />
                            @error('deleteProjectPassword') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeModal" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-semibold text-gray-300 transition-colors hover:bg-gray-700/40">Cancel</button>
                            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-red-600/30 transition-colors hover:bg-red-700">Delete Project</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('message'))
        <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    <!-- Our Clients Tab -->
    @if($activeMainTab === 'clients')
        <!-- Summary Stats -->
        @php
            $clientCollection = $clients instanceof \Illuminate\Support\Collection ? $clients : collect($clients);
            $totalClients = $clientCollection->count();
            $activeClients = $clientCollection->filter(fn($client) => $client->status !== 'settled')->count();
            $totalExpenses = $clientCollection->sum(fn($client) => $client->expenses->sum('total_cost'));
        @endphp

        <div class="mb-10 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl border border-primary-500/40 bg-primary-500/10 p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-primary-200">Total Spend</p>
            <p class="mt-2 text-2xl font-semibold text-white">₱{{ number_format($summary['total'] ?? 0, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-500/40 bg-amber-500/10 p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-200">This Month</p>
            <p class="mt-2 text-2xl font-semibold text-white">₱{{ number_format($summary['month'] ?? 0, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-sky-500/40 bg-sky-500/10 p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-sky-200">Average / Expense</p>
            <p class="mt-2 text-2xl font-semibold text-white">₱{{ number_format($summary['average'] ?? 0, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-500/40 bg-emerald-500/10 p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-200">Expenses Logged</p>
            <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($summary['count'] ?? 0) }}</p>
        </div>
    </div>
    @endif

    <!-- Our Clients Tab -->
    @if($activeMainTab === 'clients')
        @if($clients->count() === 0)
            <div class="rounded-3xl border border-dashed border-gray-600/60 bg-[#172033] px-6 py-12 text-center">
                <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-2xl bg-primary-500/10 text-primary-300">
                    <x-heroicon-o-building-office class="h-10 w-10" />
                </div>
                <h3 class="mb-2 text-lg font-semibold text-white">No Clients Yet</h3>
                @if(auth()->user()->isSystemAdmin())
                    <p class="mb-6 text-sm text-gray-400">Add your first client to begin tracking releases, projects, and warranty coverage.</p>
                    <button wire:click="openClientModal" class="mx-auto inline-flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-primary-500/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600">
                        <x-heroicon-o-user-plus class="h-4 w-4" />
                        Add Your First Client
                    </button>
                @else
                    <p class="text-sm text-gray-400">Please contact your system administrator to add clients before you can record expenses.</p>
                @endif
            </div>
        @else
            <!-- Client Cards Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white">
                        Our <span class="bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">Clients</span>
                    </h2>
                    <p class="mt-1 text-sm text-gray-400">{{ $clients->count() }} {{ Str::plural('client', $clients->count()) }} · {{ $clients->sum(fn($c) => $c->projects->count()) }} total {{ Str::plural('project', $clients->sum(fn($c) => $c->projects->count())) }}</p>
                </div>
                @if(auth()->user()->isSystemAdmin())
                    <button wire:click="openClientModal" class="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-primary-500/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600">
                        <x-heroicon-o-user-plus class="h-4 w-4" />
                        Add Client
                    </button>
                @endif
            </div>

            <div class="mx-auto grid max-w-screen-2xl grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($clients as $client)
                @php
                    $projectCount = $client->projects->count();
                    $activeProjectCount = $client->projects->filter(fn($project) => $project->isActive())->count();
                    $recentProjects = $client->projects->sortByDesc('created_at')->take(2);
                    $upcomingWarranty = $client->projects
                        ->filter(fn($project) => $project->warranty_until)
                        ->sortBy('warranty_until')
                        ->first();
                    $projectStatusColors = [
                        'planning' => 'border-sky-500/40 bg-sky-500/10 text-sky-200',
                        'in_progress' => 'border-primary-500/40 bg-primary-500/15 text-primary-200',
                        'completed' => 'border-emerald-500/40 bg-emerald-500/15 text-emerald-200',
                        'warranty' => 'border-amber-500/40 bg-amber-500/15 text-amber-200',
                    ];
                @endphp
                <div class="flex min-h-48 flex-col rounded-2xl border border-[#1B2537] bg-[#121f33] p-5 shadow-lg shadow-black/20 transition-transform duration-200 hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-start justify-between">
                        <div class="flex flex-1 items-center">
                            @if($client->logo_url)
                                <img src="{{ $client->logo_url }}" alt="{{ $client->name }} logo" class="h-12 w-12 rounded-xl border border-[#1B2537] object-cover">
                            @else
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-dashed border-gray-600 bg-[#172033] text-gray-400">
                                    <x-heroicon-o-building-office class="h-6 w-6" />
                                </div>
                            @endif
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-white">{{ $client->name }}</h3>
                                <p class="text-sm text-gray-400">{{ $client->branch }}</p>
                            </div>
                        </div>
                        @if(auth()->user()->isSystemAdmin())
                            <div class="flex gap-2">
                                <button wire:click="openProjectModal(null, {{ $client->id }})" class="rounded-lg bg-[#172033] p-2 text-primary-200 transition-colors hover:bg-primary-500/10 hover:text-primary-100" title="Add Project">
                                    <x-heroicon-o-folder-plus class="h-5 w-5" />
                                </button>
                                <button wire:click="openClientModal({{ $client->id }})" class="rounded-lg bg-[#172033] p-2 text-gray-400 transition-colors hover:bg-primary-500/10 hover:text-primary-300" title="Edit Client">
                                    <x-heroicon-o-pencil class="h-5 w-5" />
                                </button>
                                <button wire:click="openDeleteClientModal({{ $client->id }})" class="rounded-lg bg-[#172033] p-2 text-red-400 transition-colors hover:bg-red-500/10 hover:text-red-300" title="Delete Client">
                                    <x-heroicon-o-trash class="h-5 w-5" />
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 space-y-3 text-sm text-gray-300">
                        <div class="flex flex-wrap items-center gap-2">
                            @if($client->job_type)
                                <span class="rounded-full bg-[#172033] px-3 py-1 text-xs font-medium capitalize text-gray-300">{{ $client->job_type }}</span>
                            @endif
                            <span class="rounded-full bg-[#172033] px-3 py-1 text-xs font-medium text-gray-300">{{ $client->expenses->count() }} expenses</span>
                            <span class="rounded-full bg-[#172033] px-3 py-1 text-xs font-medium text-gray-300">{{ $projectCount }} {{ \Illuminate\Support\Str::plural('project', $projectCount) }}</span>
                        </div>
                        @if($recentProjects->isNotEmpty())
                            <div class="flex flex-wrap gap-2 text-xs text-gray-400">
                                @foreach($recentProjects as $project)
                                    @php
                                        $projectBadge = $projectStatusColors[$project->status] ?? 'border-[#1B2537] bg-[#172033] text-gray-300';
                                    @endphp
                                    <span class="inline-flex items-center gap-2 rounded-lg border px-3 py-1 {{ $projectBadge }}">
                                        <span class="text-gray-200">{{ $project->name }}</span>
                                        @if($project->reference_code)
                                            <span class="text-[10px] uppercase tracking-wide text-gray-400">{{ $project->reference_code }}</span>
                                        @endif
                                    </span>
                                @endforeach
                                @if($projectCount > $recentProjects->count())
                                    <span class="text-gray-500">+{{ $projectCount - $recentProjects->count() }} more</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="mt-auto flex items-center justify-between border-t border-[#1B2537] pt-4">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">Total Expenses</p>
                            <p class="mt-1 text-2xl font-semibold text-emerald-300">₱{{ number_format($client->expenses->sum('total_cost'), 2) }}</p>
                        </div>
                        <button wire:click="viewExpenses({{ $client->id }})" class="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-primary-500/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600">
                            <x-heroicon-o-eye class="h-4 w-4" />
                            View Expenses
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    @endif

    <!-- Project Management Tab -->
        @if($activeMainTab === 'projects')
            @if($projectSummaries->count())
                <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-white">
                            Project <span class="bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">Management</span>
                        </h2>
                        <p class="mt-1 text-sm text-gray-400">Every reference code with its running total and latest releases</p>
                    </div>
                    <span class="inline-flex items-center rounded-full border border-[#1B2537] bg-[#121f33] px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        {{ number_format($projectSummaries->count()) }} {{ \Illuminate\Support\Str::plural('project', $projectSummaries->count()) }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-3">
                    @foreach($projectSummaries as $projectSummary)
                        @php
                            $statusPalette = [
                                'planning' => 'border-sky-500/40 bg-sky-500/10 text-sky-200',
                                'in_progress' => 'border-primary-500/40 bg-primary-500/15 text-primary-200',
                                'completed' => 'border-emerald-500/40 bg-emerald-500/15 text-emerald-200',
                                'warranty' => 'border-amber-500/40 bg-amber-500/15 text-amber-200',
                            ];
                            $statusClass = $statusPalette[$projectSummary->status] ?? 'border-[#1B2537] bg-[#121f33] text-gray-300';
                            $latestExpense = $projectSummary->expenses->first();
                            $lastReleasedAt = $latestExpense?->released_at?->setTimezone('Asia/Manila');
                        @endphp
                        <div class="flex h-full flex-col rounded-2xl border border-[#1B2537] bg-[#101828] p-5 shadow-lg shadow-black/20">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <span class="inline-block rounded-md border border-[#1B2537] bg-[#172033] px-2 py-0.5 text-[10px] uppercase tracking-widest text-gray-400 mb-2">
                                            {{ $projectSummary->reference_code ?? 'NO-REF' }}
                                        </span>
                                        <h3 class="text-xl font-bold text-white mb-1">{{ $projectSummary->name }}</h3>
                                        <p class="text-sm text-gray-400">{{ $projectSummary->client->name }} · {{ $projectSummary->client->branch }}</p>
                                        <span class="mt-2 inline-flex items-center rounded-full border border-[#1B2537] bg-[#172033] px-3 py-1 text-xs font-medium capitalize text-gray-300">
                                            <x-heroicon-o-wrench-screwdriver class="mr-1 h-3 w-3" />
                                            {{ $projectSummary->job_type ?? 'not specified' }}
                                        </span>
                                    </div>
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusClass }} flex-shrink-0">
                                        {{ ucfirst(str_replace('_', ' ', $projectSummary->status)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-xl border border-emerald-500/40 bg-emerald-500/10 p-3">
                                    <p class="text-xs uppercase tracking-wide text-emerald-200">Total Spend</p>
                                    <p class="mt-1 text-lg font-semibold text-white">₱{{ number_format($projectSummary->expenses_total ?? 0, 2) }}</p>
                                </div>
                                <div class="rounded-xl border border-sky-500/40 bg-sky-500/10 p-3">
                                    <p class="text-xs uppercase tracking-wide text-sky-200">Expenses</p>
                                    <p class="mt-1 text-lg font-semibold text-white">{{ number_format($projectSummary->expenses_count) }}</p>
                                </div>
                            </div>

                            <div class="mt-4 space-y-3 text-xs text-gray-400">
                                @if($projectSummary->start_date)
                                    <div class="flex items-center justify-between">
                                        <span>Start date</span>
                                        <span>{{ $projectSummary->start_date->format('M d, Y') }}</span>
                                    </div>
                                @endif
                                @if($projectSummary->target_date)
                                    <div class="flex items-center justify-between">
                                        <span>Target end date</span>
                                        <span>{{ $projectSummary->target_date->format('M d, Y') }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between">
                                    <span>Last release</span>
                                    <span>{{ $lastReleasedAt ? $lastReleasedAt->diffForHumans() : '—' }}</span>
                                </div>
                                @if($projectSummary->warranty_until)
                                    <div class="flex items-center justify-between text-emerald-200">
                                        <span>Warranty until</span>
                                        <span>{{ $projectSummary->warranty_until->format('M d, Y') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 space-y-2">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Latest expenses</p>

                                @if($projectSummary->expenses->isNotEmpty())
                                    <div class="space-y-2">
                                        @foreach($projectSummary->expenses->take(3) as $recentExpense)
                                            @php
                                                $released = $recentExpense->released_at?->setTimezone('Asia/Manila');
                                            @endphp
                                            <div class="flex items-start justify-between rounded-xl border border-[#1B2537] bg-[#172033] px-3 py-2 text-xs text-gray-300">
                                                <div>
                                                    <p class="font-medium text-gray-100">{{ $recentExpense->inventory->brand ?? 'Unknown Item' }}</p>
                                                    <p class="text-[11px] text-gray-400">{{ $recentExpense->inventory->description ?? '—' }}</p>
                                                    <p class="text-[11px] text-gray-500">{{ $released ? $released->format('M d, Y · h:i A') : '—' }}</p>
                                                </div>
                                                <span class="text-sm font-semibold text-emerald-300">₱{{ number_format($recentExpense->total_cost, 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-[#253248] bg-[#121f33] px-5 py-6 text-center text-xs text-gray-400">
                                        <x-heroicon-o-cube-transparent class="h-8 w-8 text-gray-500" />
                                        <div>
                                            <p class="text-sm font-semibold text-gray-200">No expenses yet</p>
                                            <p class="mt-1 text-[11px] text-gray-500">Releases recorded for this project will appear here.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-auto flex flex-col gap-2 pt-5 sm:flex-row sm:justify-end">
                                @if(auth()->user()->isSystemAdmin())
                                    <button wire:click="openProjectManager({{ $projectSummary->id }})" class="inline-flex items-center gap-2 rounded-lg border border-emerald-500/40 bg-emerald-500/15 px-4 py-2 text-sm font-semibold text-emerald-100 transition-colors hover:bg-emerald-500/25">
                                        <x-heroicon-o-wrench-screwdriver class="h-4 w-4" />
                                        Manage Project
                                    </button>
                                @endif
                                <button wire:click="viewProject({{ $projectSummary->id }})" class="inline-flex items-center gap-2 rounded-lg border border-primary-500/40 bg-primary-500/15 px-4 py-2 text-sm font-semibold text-primary-100 transition-colors hover:bg-primary-500/25">
                                    <x-heroicon-o-eye class="h-4 w-4" />
                                    View Project
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        <!-- Receipt Rollup Tab -->
        @if($activeMainTab === 'receipts')
            <div class="mb-6 space-y-6">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-white">
                            Receipt <span class="bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">Rollup</span>
                        </h2>
                        <p class="mt-1 text-sm text-gray-400">Grouped by client and project so you only see each location once.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full border border-[#1B2537] bg-[#121f33] px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        {{ number_format($filteredExpenses->count()) }} line items
                    </span>
                </div>

                @forelse($receiptGroups as $receipt)
                    @php
                        $client = $receipt['client'];
                        $projects = $receipt['projects'];
                        $latestExpense = $projects
                            ->flatMap(fn ($project) => $project['expenses'])
                            ->sortByDesc(fn ($expense) => $expense->released_at?->timestamp ?? 0)
                            ->first();
                    @endphp
                    <div class="rounded-3xl border border-[#1B2537] bg-[#101828] p-6 shadow-lg shadow-black/20">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Client</p>
                                <h3 class="text-2xl font-semibold text-white">{{ $client->name }}</h3>
                                @if($receipt['branch'])
                                    <p class="text-sm text-gray-400">{{ $receipt['branch'] }}</p>
                                @endif
                                <p class="mt-3 text-xs uppercase tracking-wide text-gray-500">Projects Covered</p>
                                <p class="text-sm text-gray-300">{{ $projects->count() }} {{ \Illuminate\Support\Str::plural('group', $projects->count()) }} · {{ number_format($receipt['count']) }} items</p>
                            </div>
                            <div class="flex flex-col items-start gap-2 text-right md:items-end">
                                <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-200">
                                    Total ₱{{ number_format($receipt['total'], 2) }}
                                </span>
                                <span class="text-xs text-gray-500">Last updated {{ $latestExpense?->released_at?->setTimezone('Asia/Manila')->diffForHumans() ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="mt-6 space-y-5">
                            @foreach($projects as $projectGroup)
                                @php
                                    $project = $projectGroup['project'];
                                    $badgeStyles = [
                                        'planning' => 'border-sky-500/40 bg-sky-500/10 text-sky-200',
                                        'in_progress' => 'border-primary-500/40 bg-primary-500/15 text-primary-200',
                                        'completed' => 'border-emerald-500/40 bg-emerald-500/15 text-emerald-200',
                                        'warranty' => 'border-amber-500/40 bg-amber-500/15 text-amber-200',
                                    ];
                                    $statusClass = $badgeStyles[$projectGroup['status'] ?? ''] ?? 'border-[#1B2537] bg-[#121f33] text-gray-300';
                                @endphp
                                <div class="rounded-2xl border border-[#1B2537] bg-[#0d1829] p-5">
                                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h4 class="text-lg font-semibold text-white">{{ $projectGroup['project_name'] }}</h4>
                                                @if($projectGroup['reference_code'])
                                                    <span class="rounded-md border border-[#1B2537] bg-[#172033] px-2 py-0.5 text-[11px] uppercase tracking-widest text-gray-400">{{ $projectGroup['reference_code'] }}</span>
                                                @endif
                                                @if($projectGroup['status'])
                                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusClass }}">
                                                        {{ ucfirst(str_replace('_', ' ', $projectGroup['status'])) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="mt-2 flex flex-wrap items-center gap-4 text-xs text-gray-400">
                                                @if($projectGroup['start_date'])
                                                    <span>Start: {{ $projectGroup['start_date']->format('M d, Y') }}</span>
                                                @endif
                                                @if($projectGroup['target_date'])
                                                    <span>Target: {{ $projectGroup['target_date']->format('M d, Y') }}</span>
                                                @endif
                                            </div>
                                            @if($projectGroup['warranty_until'])
                                                <p class="mt-2 inline-flex items-center gap-2 text-xs text-emerald-200">
                                                    <x-heroicon-o-shield-check class="h-4 w-4" />
                                                    Warranty until {{ $projectGroup['warranty_until']->format('M d, Y') }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs uppercase tracking-wide text-gray-500">Group Total</p>
                                            <p class="text-lg font-semibold text-emerald-300">₱{{ number_format($projectGroup['subtotal'], 2) }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($projectGroup['item_count']) }} line {{ \Illuminate\Support\Str::plural('item', $projectGroup['item_count']) }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 overflow-hidden rounded-xl border border-[#1B2537]">
                                        <table class="min-w-full divide-y divide-[#1B2537] text-sm text-gray-200">
                                            <thead class="bg-[#121f33] text-xs uppercase tracking-wide text-gray-400">
                                                <tr>
                                                    <th class="px-4 py-3 text-left">Released</th>
                                                    <th class="px-4 py-3 text-left">Client · Project</th>
                                                    <th class="px-4 py-3 text-left">Inventory</th>
                                                    <th class="px-4 py-3 text-left">Location</th>
                                                    <th class="px-4 py-3 text-left">Project Status</th>
                                                    <th class="px-4 py-3 text-left">Warranty</th>
                                                    <th class="px-4 py-3 text-right">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-[#1B2537] bg-[#0d1829]">
                                                @foreach($projectGroup['expenses'] as $expense)
                                                    @php
                                                        $inventory = optional($expense->inventory);
                                                        $releasedAt = $expense->released_at?->setTimezone('Asia/Manila');
                                                        $expenseProject = $expense->project;
                                                        $expenseClient = $expense->client;
                                                        
                                                        $projectStatusPalette = [
                                                            'planning' => 'border-sky-500/40 bg-sky-500/10 text-sky-200',
                                                            'in_progress' => 'border-primary-500/40 bg-primary-500/15 text-primary-200',
                                                            'completed' => 'border-emerald-500/40 bg-emerald-500/15 text-emerald-200',
                                                            'warranty' => 'border-amber-500/40 bg-amber-500/15 text-amber-200',
                                                        ];
                                                        $projectStatusClass = $projectStatusPalette[$expenseProject?->status ?? ''] ?? 'border-[#1B2537] bg-[#172033] text-gray-300';
                                                    @endphp
                                                    <tr class="transition-colors hover:bg-[#121f33]">
                                                        <td class="px-4 py-2 text-sm text-gray-400 align-middle">
                                                            {{ $releasedAt ? $releasedAt->format('M d, Y') : '—' }}
                                                        </td>
                                                        <td class="px-4 py-2 align-middle">
                                                            <div>
                                                                <p class="font-semibold text-gray-100">{{ $expenseClient?->name ?? '—' }}</p>
                                                                @if($expenseClient?->branch)
                                                                    <p class="text-xs text-gray-400">{{ $expenseClient->branch }}</p>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-2 align-middle">
                                                            <div>
                                                                <p class="font-medium text-gray-100">{{ $inventory->brand ?? '—' }}</p>
                                                                <p class="text-xs text-gray-400">{{ $inventory->description ?? '—' }}</p>
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-2 align-middle">
                                                            <p class="text-sm text-gray-300">{{ $inventory->category ?? '—' }}</p>
                                                        </td>
                                                        <td class="px-4 py-2 align-middle">
                                                            @if($expenseProject?->status)
                                                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $projectStatusClass }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $expenseProject->status)) }}
                                                                </span>
                                                            @else
                                                                <span class="text-xs text-gray-500">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-2 align-middle">
                                                            @if($expenseProject?->warranty_until)
                                                                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-500/40 bg-emerald-500/15 px-3 py-1 text-xs text-emerald-200">
                                                                    <x-heroicon-o-shield-check class="h-3 w-3" />
                                                                    {{ $expenseProject->warranty_until->format('M d, Y') }}
                                                                </span>
                                                            @else
                                                                <span class="text-xs text-gray-500">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-2 text-right font-semibold text-emerald-300 align-middle">
                                                            ₱{{ number_format($expense->total_cost, 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-gray-600/60 bg-[#172033] px-6 py-12 text-center text-sm text-gray-400">
                        No expenses match your current filters. Adjust the filters above to see receipt details.
                    </div>
                @endforelse
            </div>

            <div class="mt-10 rounded-2xl border border-[#1B2537] bg-[#0d1829]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#1B2537] text-sm text-gray-200">
                <thead class="bg-[#121f33] text-xs uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">Released</th>
                        <th class="px-4 py-3 text-left">Client · Project</th>
                        <th class="px-4 py-3 text-left">Inventory</th>
                        <th class="px-4 py-3 text-left">Location</th>
                        <th class="px-4 py-3 text-left">Project Status</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1B2537]">
                    @forelse($filteredExpenses as $expense)
                        @php
                            $project = $expense->project;
                            $projectStatus = $project->status ?? null;
                            $projectStatusColors = [
                                'planning' => 'border-sky-500/40 bg-sky-500/10 text-sky-200',
                                'in_progress' => 'border-primary-500/40 bg-primary-500/15 text-primary-200',
                                'completed' => 'border-emerald-500/40 bg-emerald-500/15 text-emerald-200',
                                'warranty' => 'border-amber-500/40 bg-amber-500/15 text-amber-200',
                            ];
                            $clientStatusColors = [
                                'in_progress' => 'border-primary-500/40 bg-primary-500/15 text-primary-200',
                                'settled' => 'border-emerald-500/40 bg-emerald-500/15 text-emerald-200',
                                'cancelled' => 'border-red-500/40 bg-red-500/15 text-red-200',
                            ];
                            $releasedAt = $expense->released_at?->setTimezone('Asia/Manila');
                        @endphp
                        <tr class="transition-colors hover:bg-[#121f33]">
                            <td class="whitespace-nowrap px-4 py-3">{{ $releasedAt ? $releasedAt->format('M d, Y · h:i A') : '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-100">{{ $expense->client->name }}</div>
                                <div class="text-xs text-gray-400">
                                    @if($project)
                                        {{ $project->name }}
                                        @if($project->reference_code)
                                            · {{ $project->reference_code }}
                                        @endif
                                    @else
                                        <span class="italic text-gray-500">No project linked</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-gray-100">{{ $expense->inventory->brand }}</div>
                                <div class="text-xs text-gray-400">{{ $expense->inventory->description }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-2 rounded-md border border-[#1B2537] bg-[#172033] px-3 py-1 text-xs text-gray-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-primary-400"></span>
                                    {{ $expense->inventory->category ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($project)
                                    @php
                                        $badge = $projectStatusColors[$projectStatus] ?? 'border-[#1B2537] bg-[#172033] text-gray-300';
                                    @endphp
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-medium uppercase tracking-wide {{ $badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $projectStatus)) }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-gray-100">₱{{ number_format($expense->total_cost, 2) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <button type="button" wire:click="viewExpenses({{ $expense->client_id }})" class="inline-flex items-center gap-2 rounded-lg border border-[#1B2537] px-3 py-1 text-xs font-semibold text-gray-300 transition-colors hover:bg-primary-500/15 hover:text-primary-200">
                                    <x-heroicon-o-eye class="h-4 w-4" /> View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-400">No expenses match your current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
        @endif

    @if($selectedClient)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur p-4" id="expenses-modal">
            <div class="w-full max-w-7xl rounded-3xl border border-[#1B2537] bg-[#101828] p-6 sm:p-8 shadow-2xl max-h-[95vh] overflow-y-auto">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-semibold text-white">Expenses for {{ $selectedClient->name }} · {{ $selectedClient->branch }}</h3>
                        <p class="text-sm text-gray-400">Review releases, adjust costs, and filter by period.</p>
                    </div>
                    <button wire:click="closeModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-600 px-4 py-2 text-sm font-semibold text-gray-300 transition-colors hover:bg-gray-700/40">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                        Close
                    </button>
                </div>

                <div class="mt-6 space-y-6">
                    <!-- Calendar Section - Full Width Centered -->
                    <div class="rounded-2xl border border-[#1B2537] bg-[#0d1829] p-6 mx-auto max-w-5xl">
                        <div class="flex items-center justify-between">
                            <button wire:click="changeMonth('prev')" class="rounded-full bg-[#172033] p-3 text-gray-300 transition-colors duration-200 hover:bg-primary-500/20">
                                <x-heroicon-o-chevron-left class="h-6 w-6" />
                            </button>
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <button wire:click="toggleMonth" class="flex h-11 w-32 items-center justify-between rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 text-sm text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        @if ($monthSelected !== null)
                                            {{ $monthItems[$monthSelected] }}
                                        @else
                                            Month…
                                        @endif
                                        <span class="text-gray-500">
                                            @if ($monthOpen)
                                                <x-heroicon-o-chevron-up class="h-5 w-5" />
                                            @else
                                                <x-heroicon-o-chevron-down class="h-5 w-5" />
                                            @endif
                                        </span>
                                    </button>
                                    @if ($monthOpen)
                                        <ul class="absolute z-10 mt-1 w-full rounded-lg border border-[#1B2537] bg-[#0d1829] shadow-xl">
                                            @foreach($monthItems as $index => $item)
                                                <li wire:click="selectMonth({{ $index }})"
                                                    @class([
                                                        'flex cursor-pointer items-center justify-between px-3 py-2 text-sm text-gray-200 hover:bg-primary-500/10',
                                                        'bg-primary-500 text-white' => $monthSelected === $index,
                                                    ])
                                                >
                                                    {{ $item }}
                                                    @if ($monthSelected === $index)
                                                        <x-heroicon-o-check class="h-4 w-4" />
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <div class="relative">
                                    <button wire:click="toggleYear" class="flex h-11 w-24 items-center justify-between rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 text-sm text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        @if ($yearSelected !== null)
                                            {{ $yearItems[$yearSelected] }}
                                        @else
                                            Year…
                                        @endif
                                        <span class="text-gray-500">
                                            @if ($yearOpen)
                                                <x-heroicon-o-chevron-up class="h-5 w-5" />
                                            @else
                                                <x-heroicon-o-chevron-down class="h-5 w-5" />
                                            @endif
                                        </span>
                                    </button>
                                    @if ($yearOpen)
                                        <ul class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-[#1B2537] bg-[#0d1829] shadow-xl">
                                            @foreach($yearItems as $index => $item)
                                                <li wire:click="selectYear({{ $index }})"
                                                    @class([
                                                        'flex cursor-pointer items-center justify-between px-3 py-2 text-sm text-gray-200 hover:bg-primary-500/10',
                                                        'bg-primary-500 text-white' => $yearSelected === $index,
                                                    ])
                                                >
                                                    {{ $item }}
                                                    @if ($yearSelected === $index)
                                                        <x-heroicon-o-check class="h-4 w-4" />
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <div class="relative">
                                    <button wire:click="toggleTheme" class="rounded-full bg-[#172033] p-3 text-gray-300 transition-colors duration-200 hover:bg-primary-500/20">
                                        <x-heroicon-o-adjustments-horizontal class="h-5 w-5" />
                                    </button>
                                    @if ($themeOpen)
                                        <ul class="absolute right-0 z-10 mt-1 w-52 rounded-xl border border-[#1B2537] bg-[#0d1829] shadow-xl">
                                            @foreach($themes as $key => $theme)
                                                <li wire:click="selectTheme('{{ $key }}')"
                                                    @class([
                                                        'flex cursor-pointer items-center justify-between px-3 py-2 text-sm text-gray-200 hover:bg-primary-500/10',
                                                        'bg-primary-500 text-white' => $calendarTheme === $key,
                                                    ])
                                                >
                                                    <div class="flex items-center gap-2">
                                                        <div class="h-4 w-4 rounded border border-[#1B2537] {{ $theme['class'] }}"></div>
                                                        <span>{{ $theme['name'] }}</span>
                                                    </div>
                                                    @if ($calendarTheme === $key)
                                                        <x-heroicon-o-check class="h-4 w-4" />
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                            <button wire:click="changeMonth('next')" class="rounded-full bg-[#172033] p-3 text-gray-300 transition-colors duration-200 hover:bg-primary-500/20">
                                <x-heroicon-o-chevron-right class="h-6 w-6" />
                            </button>
                        </div>

                        <div class="grid grid-cols-7 gap-2 text-center text-sm font-semibold uppercase tracking-wider text-gray-400">
                            <span class="flex h-12 items-center justify-center">Sun</span>
                            <span class="flex h-12 items-center justify-center">Mon</span>
                            <span class="flex h-12 items-center justify-center">Tue</span>
                            <span class="flex h-12 items-center justify-center">Wed</span>
                            <span class="flex h-12 items-center justify-center">Thu</span>
                            <span class="flex h-12 items-center justify-center">Fri</span>
                            <span class="flex h-12 items-center justify-center">Sat</span>
                        </div>

                        <div class="grid grid-cols-7 gap-2">
                            @php
                                $firstDayOfMonth = date('w', strtotime("$calendarYear-$calendarMonth-01"));
                                $totalCells = $firstDayOfMonth + $this->daysInMonth;
                                $rows = ceil($totalCells / 7);
                            @endphp

                            @for($cell = 0; $cell < $rows * 7; $cell++)
                                @if($cell < $firstDayOfMonth || $cell >= $firstDayOfMonth + $this->daysInMonth)
                                    <span class="h-14"></span>
                                @else
                                    @php $day = $cell - $firstDayOfMonth + 1; @endphp
                                    <div class="relative">
                                        <button wire:click="selectDate({{ $day }})"
                                            class="flex h-14 w-full items-center justify-center rounded-xl border border-[#1B2537] bg-[#172033] text-base font-semibold text-gray-300 transition-all duration-150 hover:bg-primary-500/20 active:scale-95
                                            {{ $filterDate === sprintf('%04d-%02d-%02d', $calendarYear, $calendarMonth, $day) ? 'bg-primary-500 text-white border-primary-500 shadow-lg shadow-primary-500/30 scale-105' : '' }}">
                                            {{ $day }}
                                        </button>
                                        @if(!empty($calendarEvents[$day]))
                                            <span class="absolute -right-1 -top-1 flex h-6 w-6 items-center justify-center rounded-full bg-primary-500 text-xs font-bold text-white shadow-lg shadow-primary-500/40">
                                                {{ count($calendarEvents[$day]) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            @endfor
                        </div>

                        <div class="flex flex-wrap items-center justify-center gap-3 mt-6">
                            <button wire:click="applyMonthFilter" class="rounded-full border border-primary-500/40 bg-primary-500/15 px-6 py-2 text-sm font-semibold text-primary-100 transition-colors duration-200 hover:bg-primary-500/25">
                                Filter by Month
                            </button>
                            <button wire:click="clearDateFilter" class="rounded-full border border-gray-600 bg-[#172033] px-6 py-2 text-sm font-semibold text-gray-300 transition-colors duration-200 hover:bg-gray-700/40">
                                Clear Filter
                            </button>
                        </div>

                        <div class="mt-6 space-y-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Month Activity</p>
                            @if(!empty($calendarEvents))
                                <div class="max-h-72 space-y-3 overflow-y-auto pr-1">
                                    @foreach($calendarEvents as $day => $events)
                                        @php
                                            $dayDate = \Illuminate\Support\Carbon::create($calendarYear, $calendarMonth, (int) $day);
                                        @endphp
                                        <div class="rounded-2xl border border-[#1B2537] bg-[#121f33] p-3">
                                            <div class="flex items-center justify-between text-xs text-gray-300">
                                                <span class="font-semibold text-white">{{ $dayDate->format('M d, Y') }}</span>
                                                <span>{{ count($events) }} {{ \Illuminate\Support\Str::plural('expense', count($events)) }}</span>
                                            </div>
                                            <div class="mt-3 space-y-2">
                                                @foreach($events as $event)
                                                    <div class="flex items-start justify-between gap-3 rounded-xl border border-[#1B2537] bg-[#172033] px-3 py-2 text-xs text-gray-300">
                                                        <div>
                                                            <p class="font-medium text-gray-100">{{ $event['time'] }} · {{ $event['project'] ?? 'No project' }}</p>
                                                            <p class="text-[11px] uppercase tracking-wide text-gray-400">Ref: {{ $event['reference'] ?? '—' }}</p>
                                                            <p class="text-[11px] text-gray-500">{{ $event['inventory'] }}</p>
                                                        </div>
                                                        <span class="text-sm font-semibold text-emerald-300">₱{{ number_format($event['total'], 2) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-gray-500">No expenses recorded for this month yet.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Expenses List - Full Width Below Calendar -->
                    <div class="space-y-6">
                        @php
                            $groupedClientExpenses = collect($clientExpenses)
                                ->groupBy(fn ($expense) => $expense->project_id ?: 'unassigned')
                                ->sortByDesc(fn ($group) => $group->sum('total_cost'));
                        @endphp

                        @forelse($groupedClientExpenses as $projectId => $expensesGroup)
                        @php
                            $project = optional($expensesGroup->first()->project);
                            $subtotal = $expensesGroup->sum('total_cost');
                            $statusPalette = [
                                'planning' => 'border-sky-500/40 bg-sky-500/10 text-sky-200',
                                'in_progress' => 'border-primary-500/40 bg-primary-500/15 text-primary-200',
                                'completed' => 'border-emerald-500/40 bg-emerald-500/15 text-emerald-200',
                                'warranty' => 'border-amber-500/40 bg-amber-500/15 text-amber-200',
                            ];
                            $statusClass = $statusPalette[$project->status ?? ''] ?? 'border-[#1B2537] bg-[#172033] text-gray-300';
                        @endphp
                        <div class="mb-6 rounded-2xl border border-[#1B2537] bg-[#0d1829] p-5">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="text-lg font-semibold text-white">{{ $project?->name ?? 'General Expenses' }}</h4>
                                        @if($project?->reference_code)
                                            <span class="rounded-md border border-[#1B2537] bg-[#172033] px-2 py-0.5 text-[11px] uppercase tracking-widest text-gray-400">{{ $project->reference_code }}</span>
                                        @endif
                                        @if($project?->status)
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusClass }}">
                                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($project?->warranty_until)
                                        <p class="mt-2 inline-flex items-center gap-2 text-xs text-emerald-200">
                                            <x-heroicon-o-shield-check class="h-4 w-4" />
                                            Warranty until {{ $project->warranty_until->format('M d, Y') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">Subtotal</p>
                                    <p class="text-lg font-semibold text-emerald-300">₱{{ number_format($subtotal, 2) }}</p>
                                    <p class="text-xs text-gray-500">{{ number_format($expensesGroup->count()) }} line {{ \Illuminate\Support\Str::plural('item', $expensesGroup->count()) }}</p>
                                </div>
                            </div>

                            <div class="mt-4 overflow-hidden rounded-xl border border-[#1B2537]">
                                <table class="min-w-full divide-y divide-[#1B2537] text-sm text-gray-300">
                                    <thead class="bg-[#121f33] text-xs uppercase tracking-wide text-gray-400">
                                        <tr>
                                            <th class="px-4 py-3 text-left">Released</th>
                                            <th class="px-4 py-3 text-left">Item</th>
                                            <th class="px-4 py-3 text-left">Qty</th>
                                            <th class="px-4 py-3 text-left">Cost / Unit</th>
                                            <th class="px-4 py-3 text-right">Total</th>
                                            @if(auth()->user()->isSystemAdmin())
                                                <th class="px-4 py-3 text-right">Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#1B2537] bg-[#0d1829]">
                                        @foreach($expensesGroup as $expense)
                                            @php
                                                $inventory = optional($expense->inventory);
                                                $releasedAt = $expense->released_at?->setTimezone('Asia/Manila');
                                            @endphp
                                            <tr class="transition-colors hover:bg-[#121f33]">
                                                <td class="px-4 py-2 text-sm text-gray-400 align-middle">
                                                    <div class="whitespace-nowrap">{{ $releasedAt ? $releasedAt->format('M d, Y · h:i A') : '—' }}</div>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="font-medium text-gray-100 whitespace-nowrap">{{ $inventory->brand ?? 'Unknown Item' }}</span>
                                                        <span class="text-gray-500">·</span>
                                                        <span class="text-sm text-gray-400">{{ $inventory->description ?? '—' }}</span>
                                                        @if($expense->notes)
                                                            <span class="text-gray-500">·</span>
                                                            <span class="text-xs text-gray-500 italic">{{ $expense->notes }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-200 align-middle">
                                                    <div class="whitespace-nowrap">{{ number_format($expense->quantity_used, 2) }}</div>
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-200 align-middle">
                                                    <div class="whitespace-nowrap">
                                                        @if($editingExpenseId == $expense->id)
                                                            <input type="number" min="0" step="0.01" wire:model="editCostPerUnit" class="w-24 rounded-lg border border-[#1B2537] bg-[#101828] px-2 py-1 text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" />
                                                            @error('editCostPerUnit') <span class="mt-1 block text-xs text-red-400">{{ $message }}</span> @enderror
                                                        @else
                                                            ₱{{ number_format($expense->cost_per_unit, 2) }}
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2 text-right font-semibold text-emerald-300 align-middle">
                                                    <div class="whitespace-nowrap">₱{{ number_format($expense->total_cost, 2) }}</div>
                                                </td>
                                                @if(auth()->user()->isSystemAdmin())
                                                    <td class="px-4 py-2 text-right text-xs font-semibold align-middle">
                                                        @if($editingExpenseId == $expense->id)
                                                            <div class="flex justify-end gap-2">
                                                                <button wire:click="saveEditExpense" class="rounded-lg bg-emerald-500/20 px-3 py-1 text-emerald-200 transition-colors hover:bg-emerald-500/30">Save</button>
                                                                <button wire:click="cancelEditExpense" class="rounded-lg border border-gray-600 px-3 py-1 text-gray-300 transition-colors hover:bg-gray-700/40">Cancel</button>
                                                            </div>
                                                        @else
                                                            <button wire:click="editExpense({{ $expense->id }})" class="rounded-lg bg-primary-500/15 px-3 py-1 text-primary-200 transition-colors hover:bg-primary-500/25">Edit</button>
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="border-t border-[#1B2537] bg-[#121f33]">
                                        <tr>
                                            <td colspan="4" class="px-4 py-3 text-right text-xs uppercase tracking-wide text-gray-400">Group Total</td>
                                            <td class="px-4 py-3 text-right font-semibold text-emerald-300">₱{{ number_format($subtotal, 2) }}</td>
                                            @if(auth()->user()->isSystemAdmin())
                                                <td class="px-4 py-3"></td>
                                            @endif
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-600/60 bg-[#172033] px-6 py-12 text-center text-sm text-gray-400">
                                No expenses recorded for this client yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showProjectManageModal && $managingProject)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur" id="project-manage-modal">
            <div class="flex w-11/12 max-w-7xl flex-col overflow-hidden rounded-3xl border border-[#1B2537] bg-[#0b1524] p-4 sm:p-6 text-sm shadow-2xl max-h-[90vh]">
                @php
                    $manageStatusPalette = [
                        'planning' => 'border-sky-500/40 bg-sky-500/10 text-sky-200',
                        'in_progress' => 'border-primary-500/40 bg-primary-500/15 text-primary-200',
                        'completed' => 'border-emerald-500/40 bg-emerald-500/15 text-emerald-200',
                        'warranty' => 'border-amber-500/40 bg-amber-500/15 text-amber-200',
                    ];
                    $manageStatusClass = $manageStatusPalette[$managingProject['status'] ?? ''] ?? 'border-[#1B2537] bg-[#121f33] text-gray-300';
                    $firstRelease = !empty($manageProjectMetrics['first_release']) ? \Illuminate\Support\Carbon::parse($manageProjectMetrics['first_release'])->setTimezone('Asia/Manila') : null;
                    $lastRelease = !empty($manageProjectMetrics['last_release']) ? \Illuminate\Support\Carbon::parse($manageProjectMetrics['last_release'])->setTimezone('Asia/Manila') : null;
                @endphp
                <div class="flex flex-col gap-3 sm:gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] uppercase tracking-wide text-gray-500">Managing</p>
                        <h3 class="truncate text-lg sm:text-xl font-semibold text-white">{{ $managingProject['name'] }}</h3>
                        <p class="truncate text-xs text-gray-400">{{ $managingProject['reference_code'] ?? 'No reference' }} · {{ $managingProject['client']['name'] }} — {{ $managingProject['client']['branch'] }}</p>
                    </div>
                    <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center sm:flex-shrink-0">
                        <span class="inline-flex items-center justify-center rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-wide {{ $manageStatusClass }}">
                            {{ ucfirst(str_replace('_', ' ', $managingProject['status'])) }}
                        </span>
                        <button wire:click="downloadProjectReceipt({{ $managingProject['id'] }})" class="inline-flex items-center justify-center gap-2 rounded-xl border border-sky-500/40 bg-sky-500/15 px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-sky-100 transition-colors hover:bg-sky-500/25">
                            <x-heroicon-o-document-text class="h-4 w-4 flex-shrink-0" />
                            <span class="hidden sm:inline">Download PDF</span>
                            <span class="sm:hidden">PDF</span>
                        </button>
                        <button wire:click="closeProjectManageModal" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-600 px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-gray-300 transition-colors hover:bg-gray-700/40">
                            <x-heroicon-o-x-mark class="h-4 w-4 flex-shrink-0" />
                            Close
                        </button>
                    </div>
                </div>

                <div class="mt-3 sm:mt-4 grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 text-xs text-gray-300">
                    <div class="rounded-xl sm:rounded-2xl border border-emerald-500/40 bg-emerald-500/10 p-3 sm:p-4">
                        <p class="text-[10px] sm:text-[11px] uppercase tracking-wide text-emerald-200">Total Spend</p>
                        <p class="mt-1 text-base sm:text-lg font-semibold text-white truncate">₱{{ number_format($manageProjectMetrics['total'] ?? 0, 2) }}</p>
                    </div>
                    <div class="rounded-xl sm:rounded-2xl border border-primary-500/40 bg-primary-500/15 p-3 sm:p-4">
                        <p class="text-[10px] sm:text-[11px] uppercase tracking-wide text-primary-200">Expenses Logged</p>
                        <p class="mt-1 text-base sm:text-lg font-semibold text-white">{{ number_format($manageProjectMetrics['count'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-xl sm:rounded-2xl border border-sky-500/40 bg-sky-500/10 p-3 sm:p-4">
                        <p class="text-[10px] sm:text-[11px] uppercase tracking-wide text-sky-200">Average Spend</p>
                        <p class="mt-1 text-base sm:text-lg font-semibold text-white truncate">₱{{ number_format($manageProjectMetrics['average'] ?? 0, 2) }}</p>
                    </div>
                    <div class="rounded-xl sm:rounded-2xl border border-amber-500/40 bg-amber-500/10 p-3 sm:p-4">
                        <p class="text-[10px] sm:text-[11px] uppercase tracking-wide text-amber-200">Recent Release</p>
                        <p class="mt-1 text-[10px] sm:text-xs font-semibold text-white truncate">{{ $lastRelease ? $lastRelease->format('M d, Y · h:i A') : '—' }}</p>
                    </div>
                </div>

                <div class="mt-3 sm:mt-4 flex flex-wrap gap-2">
                    @php
                        $tabs = [
                            'release' => ['label' => 'Release Materials', 'icon' => 'cube-transparent'],
                            'update' => ['label' => 'Update Project', 'icon' => 'pencil-square'],
                            'notes' => ['label' => 'Notes', 'icon' => 'document-duplicate'],
                            'receipts' => ['label' => 'Printable Receipt', 'icon' => 'document-text'],
                        ];
                    @endphp
                    @foreach($tabs as $tabKey => $tabMeta)
                        @php
                            $isActive = $manageActiveTab === $tabKey;
                            $tabClasses = $isActive
                                ? 'border-primary-500/60 bg-primary-500/20 text-primary-100'
                                : 'border-[#1B2537] bg-[#121f33] text-gray-400 hover:border-primary-500/40 hover:text-primary-100';
                        @endphp
                        <button type="button" wire:click="$set('manageActiveTab','{{ $tabKey }}')" class="inline-flex items-center gap-2 rounded-lg sm:rounded-xl border px-3 sm:px-4 py-1.5 sm:py-2 text-[11px] sm:text-xs font-semibold transition-colors {{ $tabClasses }}">
                            @switch($tabMeta['icon'])
                                @case('cube-transparent')
                                    <x-heroicon-o-cube-transparent class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                    @break
                                @case('pencil-square')
                                    <x-heroicon-o-pencil-square class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                    @break
                                @case('document-duplicate')
                                    <x-heroicon-o-document-duplicate class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                    @break
                                @case('document-text')
                                    <x-heroicon-o-document-text class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                    @break
                            @endswitch
                            <span class="hidden sm:inline">{{ $tabMeta['label'] }}</span>
                            <span class="sm:hidden">{{ explode(' ', $tabMeta['label'])[0] }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="mt-4 sm:mt-6 flex-1 overflow-y-auto pr-1">
                @if($manageActiveTab === 'release')
                    <!-- Two Column Flex Layout: Record Material Release | Recent Releases -->
                    <div class="flex flex-col lg:flex-row gap-5 sm:gap-6">
                        
                        <!-- Column 1: Record Material Release -->
                        <div class="lg:w-1/2 flex min-w-0 flex-col rounded-xl sm:rounded-2xl border border-[#1B2537] bg-[#0f1829] p-4 sm:p-6 shadow-2xl shadow-black/30">
                            <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-between gap-3 sm:gap-4">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-gradient-to-br from-primary-500/30 to-sky-500/40 text-lg text-primary-100">
                                        <x-heroicon-o-cog-6-tooth class="h-5 w-5 sm:h-6 sm:w-6" />
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-base sm:text-lg font-semibold text-white">Record Material Release</h4>
                                        <p class="text-[11px] sm:text-xs text-gray-400">Select an item, set the quantity, and we will track stock adjustments automatically.</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-2 rounded-full border border-primary-500/40 bg-primary-500/15 px-2.5 sm:px-3 py-1 text-[10px] sm:text-xs font-semibold uppercase tracking-wide text-primary-100 self-start sm:self-auto">
                                    <x-heroicon-o-queue-list class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                    <span class="whitespace-nowrap">{{ count($manageReleaseItems) }} {{ Str::plural('material', count($manageReleaseItems)) }}</span>
                                </span>
                            </div>

                            <form wire:submit.prevent="recordProjectRelease" class="mt-4 sm:mt-6 flex flex-1 flex-col">
                                @error('manageReleaseItems') <span class="block text-xs text-red-400">{{ $message }}</span> @enderror

                                @php $inventoryOptionsCollection = collect($manageInventoryOptions); @endphp

                                <div class="flex flex-1 flex-col gap-4 sm:gap-5">
                                    <div class="flex-1 space-y-3 sm:space-y-4">
                                    @if($manageReleaseDuplicateNotice)
                                        <div class="rounded-lg sm:rounded-xl border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-xs text-amber-200">
                                            <div class="flex items-start justify-between gap-3">
                                                <span>{{ $manageReleaseDuplicateNotice }}</span>
                                                <button type="button" wire:click="clearManageReleaseNotice" class="rounded-md px-2 py-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-amber-100 transition-colors hover:bg-amber-500/20 flex-shrink-0">Dismiss</button>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="space-y-3 sm:space-y-4 overflow-y-auto pr-1 lg:max-h-[30rem]">
                                        @foreach($manageReleaseItems as $index => $releaseItem)
                                            @php
                                                $selectedInventoryOption = $inventoryOptionsCollection->firstWhere('id', (int)($releaseItem['inventory_id'] ?? 0));
                                                $selectedElsewhere = collect($manageReleaseItems)
                                                    ->except([$index])
                                                    ->pluck('inventory_id')
                                                    ->filter()
                                                    ->map(fn($id) => (int) $id)
                                                    ->values()
                                                    ->all();
                                                $quantityValue = (int) ($releaseItem['quantity'] ?? 1);
                                                $quantityValue = $quantityValue > 0 ? $quantityValue : 1;
                                                $costValue = $releaseItem['cost_per_unit'] ?? '';
                                                $lineTotal = ($costValue !== '' && is_numeric($costValue))
                                                    ? number_format($quantityValue * (float) $costValue, 2)
                                                    : null;
                                            @endphp
                                            <div wire:key="release-item-{{ $index }}" class="relative overflow-hidden rounded-xl sm:rounded-2xl border border-[#1f2b40] bg-gradient-to-br from-[#1a2539] via-[#0f1829] to-[#0a1220] p-4 sm:p-5 shadow-lg shadow-black/25">
                                                <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(circle at top right, rgba(91,180,255,0.08), transparent 55%);"></div>
                                                <div class="relative grid gap-3 sm:gap-4 md:grid-cols-[auto_minmax(0,1fr)_auto] md:items-center">
                                                    <div class="flex h-full items-center justify-center rounded-lg sm:rounded-xl border border-primary-500/40 bg-primary-500/15 px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-primary-100">
                                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-xs sm:text-sm font-semibold text-white">Material {{ $index + 1 }}</p>
                                                        <p class="text-[10px] sm:text-[11px] text-gray-400 truncate">Choose an item and set usage details below.</p>
                                                    </div>
                                                    <div class="flex justify-end md:justify-start">
                                                        @if(count($manageReleaseItems) > 1)
                                                            <button type="button" wire:click="removeManageReleaseItem({{ $index }})" class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg border border-red-500/40 bg-red-500/15 px-2.5 sm:px-3 py-1 sm:py-1.5 text-[10px] sm:text-xs font-semibold uppercase tracking-wide text-red-200 transition-colors hover:bg-red-500/25">
                                                                <x-heroicon-o-trash class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                                                <span class="hidden sm:inline">Remove</span>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="relative mt-4 sm:mt-5 grid gap-3 sm:gap-4 md:grid-cols-[minmax(0,2.4fr)_repeat(2,minmax(0,1fr))]">
                                                    <div>
                                                        <label class="mb-1.5 sm:mb-2 block text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-gray-500">Inventory Item</label>
                                                        <select wire:model="manageReleaseItems.{{ $index }}.inventory_id" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40">
                                                            <option value="">Select an item...</option>
                                                            @foreach($manageInventoryOptions as $inventoryOption)
                                                                @php $optionDisabled = in_array($inventoryOption['id'], $selectedElsewhere, true); @endphp
                                                                <option value="{{ $inventoryOption['id'] }}" @if($optionDisabled) disabled class="bg-[#121f33]/40 text-gray-500" @endif>
                                                                    {{ $inventoryOption['label'] }} ({{ $inventoryOption['quantity'] }} in stock)
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('manageReleaseItems.' . $index . '.inventory_id') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                                        @if($selectedInventoryOption)
                                                            <p class="mt-1.5 sm:mt-2 text-[10px] sm:text-xs text-gray-500 truncate">Available: {{ number_format($selectedInventoryOption['quantity']) }} units · Status: {{ ucfirst(str_replace('_', ' ', $selectedInventoryOption['status'])) }}</p>
                                                        @elseif(!empty($releaseItem['inventory_id']) && ! $selectedInventoryOption)
                                                            <p class="mt-1.5 sm:mt-2 text-[10px] sm:text-xs text-amber-400">This item is no longer listed in inventory.</p>
                                                        @endif
                                                    </div>

                                                    <div>
                                                        <label class="mb-1.5 sm:mb-2 block text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-gray-500">Quantity</label>
                                                        <div class="relative flex items-center">
                                                            <input type="number" min="1" wire:model.defer="manageReleaseItems.{{ $index }}.quantity" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" />
                                                        </div>
                                                        @error('manageReleaseItems.' . $index . '.quantity') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                                    </div>

                                                    <div>
                                                        <label class="mb-1.5 sm:mb-2 block text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-gray-500">Cost per unit</label>
                                                        <div class="relative flex items-center">
                                                            <span class="pointer-events-none absolute left-2.5 sm:left-3 text-xs text-gray-500">₱</span>
                                                            <input type="number" min="0" step="0.01" wire:model.defer="manageReleaseItems.{{ $index }}.cost_per_unit" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-2.5 sm:px-3 py-1.5 sm:py-2 pl-6 sm:pl-7 text-xs sm:text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" />
                                                        </div>
                                                        @error('manageReleaseItems.' . $index . '.cost_per_unit') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>

                                                <div class="relative mt-4 sm:mt-5 flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-between gap-2 sm:gap-3">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        @if($selectedInventoryOption)
                                                            <div class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg border border-sky-500/30 bg-sky-500/10 px-2.5 sm:px-3 py-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-sky-200">
                                                                <x-heroicon-o-information-circle class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                                                {{ strtoupper(str_replace('_', ' ', $selectedInventoryOption['status'])) }} STOCK
                                                            </div>
                                                        @endif

                                                        @if($lineTotal)
                                                            <div class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-2.5 sm:px-3 py-1 text-[10px] sm:text-xs font-semibold text-emerald-200">
                                                                <x-heroicon-o-banknotes class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                                                <span class="whitespace-nowrap">Line Total: ₱{{ $lineTotal }}</span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    @if(count($manageReleaseItems) > 1)
                                                        <button type="button" wire:click="removeManageReleaseItem({{ $index }})" class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg border border-red-500/40 bg-red-500/10 px-2.5 sm:px-3 py-1.5 sm:py-2 text-[10px] sm:text-xs font-semibold uppercase tracking-wide text-red-200 transition-colors hover:bg-red-500/20 sm:hidden">
                                                            <x-heroicon-o-trash class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                                            Remove Material
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-5 sm:mt-6 space-y-4 sm:space-y-5 rounded-xl sm:rounded-2xl border border-[#1B2537] bg-[#111c2c] p-4 sm:p-5 shadow-lg shadow-black/10">
                                        <div class="space-y-3 sm:space-y-4">
                                            <div>
                                                <label class="mb-1.5 sm:mb-2 block text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-gray-500">Release Date</label>
                                                <input type="date" wire:model.defer="manageReleaseDate" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" />
                                                @error('manageReleaseDate') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="mb-1.5 sm:mb-2 block text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-gray-500">Release Time</label>
                                                <input type="time" wire:model.defer="manageReleaseTime" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" />
                                                @error('manageReleaseTime') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="mb-1.5 sm:mb-2 block text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-gray-500">Internal Notes (optional)</label>
                                                <textarea rows="4" wire:model.defer="manageReleaseNotes" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" placeholder="Document install notes, serials, or handover details."></textarea>
                                                @error('manageReleaseNotes') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                                @if($manageReleaseNotes && !$manageExpenseNotesSupported)
                                                    <p class="mt-1.5 sm:mt-2 text-[10px] sm:text-xs text-amber-400">Notes will be saved to the project history log only because the expenses table currently has no notes column.</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="space-y-2 rounded-lg sm:rounded-xl border border-primary-500/30 bg-primary-500/10 px-3 sm:px-4 py-2.5 sm:py-3 text-[10px] sm:text-[11px] text-primary-100">
                                            <div class="flex items-start gap-2">
                                                <x-heroicon-o-light-bulb class="mt-0.5 h-3.5 w-3.5 sm:h-4 sm:w-4 text-primary-300 flex-shrink-0" />
                                                <p>Tip: batching multiple materials together will sync inventory adjustments in one go.</p>
                                            </div>
                                            <div class="flex items-start gap-2">
                                                <x-heroicon-o-clipboard-document-list class="mt-0.5 h-3.5 w-3.5 sm:h-4 sm:w-4 text-primary-300 flex-shrink-0" />
                                                <p>Need to log delivery paperwork? Add internal notes so the audit trail stays complete.</p>
                                            </div>
                                        </div>

                                        <div class="flex flex-col gap-2 sm:gap-3 border-t border-[#1B2537] pt-3 sm:pt-4">
                                            <button type="button" wire:click="addManageReleaseItem" class="inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-lg border border-sky-500/50 bg-sky-500/15 px-3 py-2 text-[10px] sm:text-xs font-semibold uppercase tracking-wide text-sky-100 transition-colors hover:bg-sky-500/30">
                                                <x-heroicon-o-plus class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                                Add Another Material
                                            </button>
                                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-lg sm:rounded-xl bg-gradient-to-r from-primary-500 to-sky-500 px-4 sm:px-5 py-2.5 sm:py-3 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-primary-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:from-primary-600 hover:to-sky-500">
                                                <x-heroicon-o-check class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                                Record Release
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                      <!-- Column 2: Recent Releases -->
                        <div class="lg:w-1/2 flex min-w-0 flex-col rounded-xl sm:rounded-2xl border border-[#1B2537] bg-[#0f1829] p-4 sm:p-5 shadow-2xl shadow-black/30">
                            <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-between gap-3 sm:gap-4 pb-4 border-b border-[#1B2537]">
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-base sm:text-lg font-semibold text-white">Recent Releases</h4>
                                    <p class="text-[11px] sm:text-xs text-gray-400">Audit-friendly timeline of the latest material drops for this project.</p>
                                </div>
                                <span class="inline-flex items-center gap-1.5 sm:gap-2 rounded-full border border-primary-500/40 bg-primary-500/15 px-2.5 sm:px-3 py-1 text-[10px] sm:text-xs font-semibold uppercase tracking-wide text-primary-100 self-start sm:self-auto">
                                    <x-heroicon-o-clock class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                    <span class="whitespace-nowrap">{{ number_format(count($manageRecentReleases)) }} logged</span>
                                </span>
                            </div>

                            <div class="mt-4 sm:mt-5 flex-1">
                                @if(count($manageRecentReleases))
                                    <div class="space-y-3 sm:space-y-4 overflow-y-auto pr-1 lg:max-h-[32rem]">
                                        @foreach($manageRecentReleases as $release)
                                            @php
                                                $releasedAt = !empty($release['released_at']) ? \Illuminate\Support\Carbon::parse($release['released_at'])->setTimezone('Asia/Manila') : null;
                                            @endphp
                                            <div class="relative flex flex-col sm:flex-row gap-3 sm:gap-4 rounded-xl sm:rounded-2xl border border-[#1f2b40] bg-gradient-to-br from-[#1a2539] via-[#0f1829] to-[#08111f] p-4 sm:p-5 shadow-lg shadow-black/25">
                                                <div class="flex sm:flex-col items-center sm:items-center justify-between sm:justify-start gap-2 sm:gap-0">
                                                    <span class="flex h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-lg sm:rounded-xl border border-emerald-500/40 bg-emerald-500/15 text-emerald-200">
                                                        <x-heroicon-o-truck class="h-4 w-4 sm:h-5 sm:w-5" />
                                                    </span>
                                                    <span class="text-[10px] sm:text-[11px] uppercase tracking-wide text-gray-500 sm:mt-2">{{ $releasedAt ? $releasedAt->diffForHumans() : '—' }}</span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-between gap-2 sm:gap-3">
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-xs sm:text-sm font-semibold text-white truncate">{{ $release['inventory']['brand'] ?? 'Unknown Item' }}</p>
                                                            <p class="text-[10px] sm:text-[11px] text-gray-400 truncate">{{ $release['inventory']['description'] ?? '—' }}</p>
                                                        </div>
                                                        <span class="inline-flex items-center gap-1.5 sm:gap-2 rounded-full border border-emerald-500/40 bg-emerald-500/10 px-2.5 sm:px-3 py-1 text-[10px] sm:text-xs font-semibold text-emerald-200 self-start sm:self-auto whitespace-nowrap">
                                                            <x-heroicon-o-banknotes class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                                            ₱{{ number_format($release['total'], 2) }}
                                                        </span>
                                                    </div>
                                                    <div class="mt-3 sm:mt-4 grid gap-2 sm:gap-3 text-[10px] sm:text-[11px] text-gray-400 sm:grid-cols-3">
                                                        <div class="flex items-center gap-1.5 sm:gap-2">
                                                            <x-heroicon-o-calendar-days class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-gray-500 flex-shrink-0" />
                                                            <span class="truncate">{{ $releasedAt ? $releasedAt->format('M d, Y · h:i A') : '—' }}</span>
                                                        </div>
                                                        <div class="flex items-center gap-1.5 sm:gap-2">
                                                            <x-heroicon-o-cube class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-gray-500 flex-shrink-0" />
                                                            <span class="truncate">{{ number_format($release['quantity'], 2) }} unit(s)</span>
                                                        </div>
                                                        <div class="flex items-center gap-1.5 sm:gap-2">
                                                            <x-heroicon-o-currency-dollar class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-gray-500 flex-shrink-0" />
                                                            <span class="truncate">₱{{ number_format($release['cost_per_unit'], 2) }} per unit</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center gap-2 sm:gap-3 rounded-xl sm:rounded-2xl border border-dashed border-gray-600/60 bg-[#121f33] p-6 sm:p-8 text-center">
                                        <x-heroicon-o-cube-transparent class="h-8 w-8 sm:h-10 sm:w-10 text-gray-500" />
                                        <div>
                                            <p class="text-xs sm:text-sm font-semibold text-gray-200">No releases yet</p>
                                            <p class="mt-1 text-[10px] sm:text-xs text-gray-400">Logged materials will appear here after you record them using the form on the left.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                @elseif($manageActiveTab === 'update')
                    <div class="mt-4 sm:mt-6 rounded-xl sm:rounded-2xl border border-[#1B2537] bg-[#101828] p-4 sm:p-5">
                        <h4 class="text-sm sm:text-base font-semibold text-white">Update Project Details</h4>
                        <p class="mt-1 text-[11px] sm:text-xs text-gray-400">Adjust lifecycle status, warranty timelines, or key notes. Changes are tracked in history.</p>

                        <form wire:submit.prevent="updateManageProjectDetails" class="mt-3 sm:mt-4 space-y-4 sm:space-y-5">
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <label class="mb-1.5 sm:mb-2 block text-xs sm:text-sm font-medium text-gray-300">Status</label>
                                    <select wire:model="manageProjectStatus" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40">
                                        @foreach(\App\Models\Project::STATUSES as $status)
                                            <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                    @error('manageProjectStatus') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="mb-1.5 sm:mb-2 block text-xs sm:text-sm font-medium text-gray-300">Start Date</label>
                                    <input type="date" wire:model.defer="manageProjectStartDate" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" />
                                    @error('manageProjectStartDate') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="mb-1.5 sm:mb-2 block text-xs sm:text-sm font-medium text-gray-300">Target Completion</label>
                                    <input type="date" wire:model.defer="manageProjectTargetDate" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" />
                                    @error('manageProjectTargetDate') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="mb-1.5 sm:mb-2 block text-xs sm:text-sm font-medium text-gray-300">Warranty Until</label>
                                    <input type="date" wire:model.defer="manageProjectWarrantyUntil" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" />
                                    @error('manageProjectWarrantyUntil') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 sm:mb-2 block text-xs sm:text-sm font-medium text-gray-300">Project Notes</label>
                                <textarea rows="3" wire:model.defer="manageProjectNotesInput" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" placeholder="Keep internal notes, punch-list items, or warranty coverage details here."></textarea>
                                @error('manageProjectNotesInput') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg sm:rounded-xl bg-primary-500 px-4 sm:px-5 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-primary-500/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600">
                                    <x-heroicon-o-check class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                    Save Changes
                                </button>
                            </div>
                        </form>

                        <!-- Danger Zone -->
                        <div class="mt-6 sm:mt-8 rounded-xl sm:rounded-2xl border border-red-500/40 bg-red-500/5 p-4 sm:p-5">
                            <div class="flex items-start gap-3 sm:gap-4">
                                <span class="flex h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-red-500/10 text-red-300">
                                    <x-heroicon-o-exclamation-triangle class="h-4 w-4 sm:h-5 sm:w-5" />
                                </span>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm sm:text-base font-semibold text-white">Danger Zone</h4>
                                    <p class="mt-1 text-[11px] sm:text-xs text-gray-400">Permanently delete this project and all associated data. This action cannot be undone.</p>
                                    <div class="mt-3 sm:mt-4">
                                        <button wire:click="openDeleteProjectModal({{ $managingProject['id'] }})" class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg border border-red-500/40 bg-red-500/15 px-3 sm:px-4 py-1.5 sm:py-2 text-[10px] sm:text-xs font-semibold uppercase tracking-wide text-red-200 transition-colors hover:bg-red-500/25">
                                            <x-heroicon-o-trash class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                            Delete Project
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($manageActiveTab === 'notes')
                    <div class="mt-4 sm:mt-6 grid gap-4 sm:gap-5 lg:grid-cols-2">
                        <!-- Column 1: Add New Note Form -->
                        <div class="rounded-xl sm:rounded-2xl border border-[#1B2537] bg-[#101828] p-4 sm:p-5">
                            <h4 class="text-sm sm:text-base font-semibold text-white">Add Project Note</h4>
                            <p class="mt-1 text-[11px] sm:text-xs text-gray-400">Document changes, updates, or important details about this project.</p>

                            <form wire:submit.prevent="saveProjectNote" class="mt-3 sm:mt-4 space-y-4">
                                <div>
                                    <label class="mb-1.5 sm:mb-2 block text-xs sm:text-sm font-medium text-gray-300">Note Content</label>
                                    <textarea rows="8" wire:model.defer="newNoteContent" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" placeholder="Enter your note here..."></textarea>
                                    @error('newNoteContent') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg sm:rounded-xl bg-primary-500 px-4 sm:px-5 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-primary-500/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600">
                                        <x-heroicon-o-plus class="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0" />
                                        Add Note
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Column 2: Notes List -->
                        <div class="rounded-xl sm:rounded-2xl border border-[#1B2537] bg-[#101828] p-4 sm:p-5">
                            <h4 class="text-sm sm:text-base font-semibold text-white mb-4">Project Notes ({{ count($manageProjectNotesList) }})</h4>

                            <div class="max-h-[600px] overflow-y-auto pr-2">
                                @forelse($manageProjectNotesList as $note)
                                    <div class="mb-4 rounded-lg border border-[#1B2537] bg-[#0d1829] p-4">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-primary-500/10 px-2 py-0.5 text-[10px] font-medium text-primary-200">
                                                        <x-heroicon-o-user class="h-3 w-3" />
                                                        {{ $note['user_name'] }}
                                                    </span>
                                                    <span class="text-[10px] text-gray-500">{{ $note['created_at_human'] }}</span>
                                                </div>
                                                <p class="mt-2 text-sm text-gray-200 whitespace-pre-wrap">{{ $note['content'] }}</p>
                                                
                                                @if(!empty($note['images']))
                                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                                        @foreach($note['images'] as $image)
                                                            <img src="{{ $image }}" alt="Note image" class="rounded-lg border border-[#1B2537] object-cover h-32 w-full">
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            <button wire:click="deleteProjectNote({{ $note['id'] }})" class="ml-3 rounded-lg bg-red-500/10 p-2 text-red-400 transition-colors hover:bg-red-500/20 hover:text-red-300">
                                                <x-heroicon-o-trash class="h-4 w-4" />
                                            </button>
                                        </div>
                                        <p class="mt-2 text-[10px] text-gray-500">{{ $note['created_at'] }}</p>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-[#253248] bg-[#121f33] px-5 py-8 text-center">
                                        <x-heroicon-o-document-duplicate class="h-10 w-10 text-gray-500" />
                                        <div>
                                            <p class="text-sm font-semibold text-gray-200">No notes yet</p>
                                            <p class="mt-1 text-xs text-gray-500">Add your first note to start documenting project updates.</p>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-4 sm:mt-6 grid gap-4 sm:gap-5 lg:grid-cols-[3fr,2fr]">
                        <div class="rounded-xl sm:rounded-2xl border border-[#1B2537] bg-[#101828] p-4 sm:p-5">
                            <h4 class="text-sm sm:text-base font-semibold text-white">Official PDF Receipt</h4>
                            <p class="mt-1 text-[11px] sm:text-xs text-gray-400">Download a professional PDF receipt with official watermark and security features. Opens in your browser for direct printing.</p>

                            <div class="mt-3 space-y-2 sm:space-y-3 text-xs text-gray-300">
                                <div class="rounded-lg sm:rounded-xl border border-[#1B2537] bg-[#121f33] p-3 sm:p-4">
                                    <p class="text-[10px] sm:text-xs uppercase tracking-wide text-gray-500">Project</p>
                                    <p class="text-sm sm:text-base font-semibold text-white truncate">{{ $managingProject['name'] }}</p>
                                    <p class="text-[11px] sm:text-xs text-gray-400 truncate">{{ $managingProject['reference_code'] ?? 'No reference code yet' }}</p>
                                </div>
                                <div class="rounded-lg sm:rounded-xl border border-[#1B2537] bg-[#121f33] p-3 sm:p-4">
                                    <p class="text-[10px] sm:text-xs uppercase tracking-wide text-gray-500">Client</p>
                                    <p class="text-sm sm:text-base font-semibold text-white truncate">{{ $managingProject['client']['name'] }}</p>
                                    <p class="text-[11px] sm:text-xs text-gray-400 truncate">{{ $managingProject['client']['branch'] }}</p>
                                </div>
                                <div class="rounded-lg sm:rounded-xl border border-[#1B2537] bg-[#121f33] p-3 sm:p-4">
                                    <p class="text-[10px] sm:text-xs uppercase tracking-wide text-gray-500">PDF Features</p>
                                    <ul class="mt-2 space-y-1 text-[10px] sm:text-xs text-gray-400">
                                        <li>✓ Official watermark for authenticity</li>
                                        <li>✓ Secure format prevents unauthorized editing</li>
                                        <li>✓ Professional layout ready for printing</li>
                                        <li>✓ All times displayed in Asia/Manila timezone</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl sm:rounded-2xl border border-[#1B2537] bg-[#101828] p-4 sm:p-5 text-xs text-gray-300">
                            <h5 class="text-xs sm:text-sm font-semibold text-white">Document Security</h5>
                            <p class="mt-2 text-[11px] sm:text-xs text-gray-400">PDF receipts include an official watermark and timestamp to prevent tampering. The document will open in your browser where you can print or save it locally.</p>
                            @if($manageRecentReleases)
                                <p class="mt-3 sm:mt-4 text-[10px] sm:text-xs uppercase tracking-wide text-gray-500">Sample rows</p>
                                <div class="mt-2 space-y-2">
                                    @foreach(collect($manageRecentReleases)->take(3) as $release)
                                        @php
                                            $releasedAt = !empty($release['released_at']) ? \Illuminate\Support\Carbon::parse($release['released_at'])->setTimezone('Asia/Manila') : null;
                                        @endphp
                                        <div class="rounded-lg sm:rounded-xl border border-[#1B2537] bg-[#121f33] p-2.5 sm:p-3">
                                            <p class="text-[10px] sm:text-xs text-gray-500 truncate">{{ $releasedAt ? $releasedAt->format('M d, Y · h:i A') : '—' }}</p>
                                            <p class="text-xs sm:text-sm font-semibold text-gray-100 truncate">{{ $release['inventory']['brand'] ?? 'Unknown Item' }}</p>
                                            <p class="text-[10px] sm:text-xs text-gray-400 truncate">{{ $release['inventory']['description'] ?? '—' }}</p>
                                            <p class="mt-1 text-[10px] sm:text-xs text-emerald-300">{{ number_format($release['quantity'], 2) }} × ₱{{ number_format($release['cost_per_unit'], 2) }} = ₱{{ number_format($release['total'], 2) }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </div>
    @endif

    @if($showProjectDetailModal && $selectedProject)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur" id="project-detail-modal">
            <div class="w-11/12 max-w-6xl rounded-3xl border border-[#1B2537] bg-[#101828] p-6 shadow-2xl">
                @php
                    $statusPalette = [
                        'planning' => 'border-sky-500/40 bg-sky-500/10 text-sky-200',
                        'in_progress' => 'border-primary-500/40 bg-primary-500/15 text-primary-200',
                        'completed' => 'border-emerald-500/40 bg-emerald-500/15 text-emerald-200',
                        'warranty' => 'border-amber-500/40 bg-amber-500/15 text-amber-200',
                    ];
                    $statusClass = $statusPalette[$selectedProject['status'] ?? ''] ?? 'border-[#1B2537] bg-[#121f33] text-gray-300';
                    $startDate = !empty($selectedProject['start_date']) ? \Illuminate\Support\Carbon::parse($selectedProject['start_date']) : null;
                    $targetDate = !empty($selectedProject['target_date']) ? \Illuminate\Support\Carbon::parse($selectedProject['target_date']) : null;
                    $warrantyUntil = !empty($selectedProject['warranty_until']) ? \Illuminate\Support\Carbon::parse($selectedProject['warranty_until']) : null;
                    $firstRelease = !empty($selectedProjectMetrics['first_release']) ? \Illuminate\Support\Carbon::parse($selectedProjectMetrics['first_release'])->setTimezone('Asia/Manila') : null;
                    $lastRelease = !empty($selectedProjectMetrics['last_release']) ? \Illuminate\Support\Carbon::parse($selectedProjectMetrics['last_release'])->setTimezone('Asia/Manila') : null;
                @endphp
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Project Reference</p>
                        <h3 class="text-2xl font-semibold text-white">{{ $selectedProject['reference_code'] ?? '—' }}</h3>
                        <p class="text-lg font-semibold text-gray-100">{{ $selectedProject['name'] }}</p>
                        <p class="text-sm text-gray-400">{{ $selectedProject['client']['name'] }} · {{ $selectedProject['client']['branch'] }}</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusClass }}">
                            {{ ucfirst(str_replace('_', ' ', $selectedProject['status'])) }}
                        </span>
                        <button wire:click="closeProjectDetailModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-600 px-4 py-2 text-sm font-semibold text-gray-300 transition-colors hover:bg-gray-700/40">
                            <x-heroicon-o-x-mark class="h-4 w-4" />
                            Close
                        </button>
                    </div>
                </div>

                @if(!empty($selectedProject['notes']))
                    <div class="mt-4 rounded-2xl border border-[#1B2537] bg-[#121f33] p-4 text-sm text-gray-300">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Project Notes</p>
                        <p class="mt-1 leading-relaxed">{{ $selectedProject['notes'] }}</p>
                    </div>
                @endif

                <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div class="rounded-2xl border border-emerald-500/40 bg-emerald-500/10 p-4">
                        <p class="text-xs uppercase tracking-wide text-emerald-200">Total Spend</p>
                        <p class="mt-2 text-xl font-semibold text-white">₱{{ number_format($selectedProjectMetrics['total'] ?? 0, 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-sky-500/40 bg-sky-500/10 p-4">
                        <p class="text-xs uppercase tracking-wide text-sky-200">Expenses Logged</p>
                        <p class="mt-2 text-xl font-semibold text-white">{{ number_format($selectedProjectMetrics['count'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-2xl border border-primary-500/40 bg-primary-500/15 p-4">
                        <p class="text-xs uppercase tracking-wide text-primary-200">Average Spend</p>
                        <p class="mt-2 text-xl font-semibold text-white">₱{{ number_format($selectedProjectMetrics['average'] ?? 0, 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-amber-500/40 bg-amber-500/10 p-4">
                        <p class="text-xs uppercase tracking-wide text-amber-200">Last Release</p>
                        <p class="mt-2 text-sm font-semibold text-white">{{ $lastRelease ? $lastRelease->format('M d, Y · h:i A') : '—' }}</p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 text-sm text-gray-300 md:grid-cols-3">
                    <div class="rounded-2xl border border-[#1B2537] bg-[#0d1829] p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Kickoff</p>
                        <p class="mt-1 font-semibold text-white">{{ $startDate ? $startDate->format('M d, Y') : '—' }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#1B2537] bg-[#0d1829] p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Target completion</p>
                        <p class="mt-1 font-semibold text-white">{{ $targetDate ? $targetDate->format('M d, Y') : '—' }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#1B2537] bg-[#0d1829] p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Warranty until</p>
                        <p class="mt-1 font-semibold text-white">{{ $warrantyUntil ? $warrantyUntil->format('M d, Y') : '—' }}</p>
                    </div>
                </div>

                @if($firstRelease || $lastRelease)
                    <div class="mt-4 text-xs text-gray-400">
                        <p>First release recorded {{ $firstRelease ? $firstRelease->format('M d, Y · h:i A') : '—' }}</p>
                        <p>Most recent release {{ $lastRelease ? $lastRelease->diffForHumans() : '—' }}</p>
                    </div>
                @endif

                @if(!empty($selectedProjectBreakdown))
                    <div class="mt-6">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Category Breakdown</p>
                        <div class="mt-3 grid gap-3 md:grid-cols-2">
                            @foreach($selectedProjectBreakdown as $row)
                                @php
                                    $categoryName = $row['category'] === 'Uncategorized'
                                        ? 'Uncategorized'
                                        : \Illuminate\Support\Str::title(str_replace('_', ' ', $row['category']));
                                @endphp
                                <div class="rounded-2xl border border-[#1B2537] bg-[#121f33] p-4 text-sm text-gray-300">
                                    <div class="flex items-center justify-between">
                                        <p class="font-semibold text-gray-100">{{ $categoryName }}</p>
                                        <span class="text-xs text-gray-500">{{ number_format($row['count']) }} {{ \Illuminate\Support\Str::plural('item', $row['count']) }}</span>
                                    </div>
                                    <p class="mt-2 text-lg font-semibold text-emerald-300">₱{{ number_format($row['total'], 2) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-6 overflow-hidden rounded-2xl border border-[#1B2537]">
                    <table class="min-w-full divide-y divide-[#1B2537] text-sm text-gray-300">
                        <thead class="bg-[#121f33] text-xs uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Released</th>
                                <th class="px-4 py-3 text-left">Item</th>
                                <th class="px-4 py-3 text-left">Qty × Cost</th>
                                <th class="px-4 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#1B2537] bg-[#0d1829]">
                            @forelse($selectedProjectExpenses as $expense)
                                @php
                                    $released = !empty($expense['released_at']) ? \Illuminate\Support\Carbon::parse($expense['released_at'])->setTimezone('Asia/Manila') : null;
                                @endphp
                                <tr class="transition-colors hover:bg-[#121f33]">
                                    <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-400">{{ $released ? $released->format('M d, Y · h:i A') : '—' }}</td>
                                    <td class="px-4 py-3 align-top">
                                        <p class="font-medium text-gray-100">{{ $expense['inventory']['brand'] ?? 'Unknown Item' }}</p>
                                        <p class="text-xs text-gray-400">{{ $expense['inventory']['description'] ?? '—' }}</p>
                                        @if(!empty($expense['notes']))
                                            <p class="mt-1 text-xs text-gray-500">Note: {{ $expense['notes'] }}</p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-200">
                                        {{ number_format($expense['quantity_used'], 2) }} × ₱{{ number_format($expense['cost_per_unit'], 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-emerald-300">₱{{ number_format($expense['total_cost'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">No expenses recorded for this project yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($showProjectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur" id="project-modal">
            <div class="w-11/12 max-w-3xl rounded-3xl border border-[#1B2537] bg-[#101828] p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-white">{{ $editingProject ? 'Edit Project' : 'Add New Project' }}</h3>
                        <p class="text-sm text-gray-400">Group expenses per client engagement and track warranty windows with confidence.</p>
                    </div>
                    <button wire:click="closeProjectModal" class="rounded-full border border-gray-600 p-2 text-gray-300 transition-colors hover:bg-gray-700/40">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>

                <form wire:submit.prevent="saveProject" class="mt-6 space-y-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        @php
                            $clientCollectionForModal = $clients instanceof \Illuminate\Support\Collection ? $clients : collect($clients);
                            $clientsByName = $clientCollectionForModal->groupBy('name');
                            $availableBranches = $projectClientName ? $clientsByName->get($projectClientName, collect()) : collect();
                        @endphp
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Client Name</label>
                            <select wire:model.live="projectClientName" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40">
                                <option value="">Select a client name...</option>
                                @foreach($clientsByName->keys()->sort() as $clientName)
                                    <option value="{{ $clientName }}">{{ $clientName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Branch / Location</label>
                            <select wire:model.live="projectClientId" @class(['w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40', 'opacity-60' => !$projectClientName]) @disabled(!$projectClientName)>
                                <option value="">@if($projectClientName) Select a branch... @else Choose a name first @endif</option>
                                @foreach($availableBranches as $clientOption)
                                    <option value="{{ $clientOption->id }}">{{ $clientOption->branch ?? 'Main Branch' }}</option>
                                @endforeach
                            </select>
                            @error('projectClientId') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-300">Reference Code</label>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <input wire:model.defer="projectReference" type="text" placeholder="Auto-generated or enter manually" class="flex-1 rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" />
                                <button type="button" wire:click="generateProjectReference" class="inline-flex items-center gap-2 rounded-lg border border-primary-500/40 bg-primary-500/15 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-primary-100 transition-colors hover:bg-primary-500/25">
                                    <x-heroicon-o-sparkles class="h-4 w-4" />
                                    Generate
                                </button>
                            </div>
                            @error('projectReference') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-300">Project Name</label>
                            <input wire:model.defer="projectName" type="text" placeholder="Enter project name" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" />
                            @error('projectName') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Start Date</label>
                            <input wire:model.defer="projectStartDate" type="date" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" />
                            @error('projectStartDate') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Target Completion</label>
                            <input wire:model.defer="projectTargetDate" type="date" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" />
                            @error('projectTargetDate') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Warranty Until</label>
                            <input wire:model.defer="projectWarrantyUntil" type="date" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" />
                            @error('projectWarrantyUntil') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Job Type</label>
                            <select wire:model="projectJobType" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40">
                                <option value="">Select job type...</option>
                                <option value="installation">Installation</option>
                                <option value="service">Service</option>
                            </select>
                            @error('projectJobType') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Status</label>
                            <select wire:model="projectStatus" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40">
                                @foreach(\App\Models\Project::STATUSES as $status)
                                    <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                            @error('projectStatus') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-300">Notes</label>
                            <textarea wire:model.defer="projectNotes" rows="3" placeholder="Add installation details, asset tags, or warranty coverage." class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"></textarea>
                            @error('projectNotes') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="closeProjectModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-600 px-5 py-2 text-sm font-semibold text-gray-300 transition-colors hover:bg-gray-700/40">
                            <x-heroicon-o-x-mark class="h-4 w-4" />
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-primary-500/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600">
                            <x-heroicon-o-check class="h-4 w-4" />
                            {{ $editingProject ? 'Update Project' : 'Create Project' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showClientModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur" id="client-modal">
            <div class="w-11/12 max-w-xl rounded-3xl border border-[#1B2537] bg-[#101828] p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-white">{{ $editingClient ? 'Edit Client' : 'Add New Client' }}</h3>
                        <p class="text-sm text-gray-400">Keep client details polished to streamline reporting across teams.</p>
                    </div>
                    <button wire:click="closeModal" class="rounded-full border border-gray-600 p-2 text-gray-300 transition-colors hover:bg-gray-700/40">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>

                <form wire:submit.prevent="saveClient" class="mt-6 space-y-6">
                    <div class="flex flex-col items-center">
                        <label class="mb-3 block text-sm font-medium text-gray-300">Client Logo</label>
                        <div class="w-full max-w-sm">
                            <div class="relative rounded-2xl border-2 border-dashed border-gray-600 bg-[#121f33] p-6 text-center transition-colors hover:border-primary-500 hover:bg-primary-500/10">
                                @if($clientLogo)
                                    <img src="{{ $clientLogo->temporaryUrl() }}" alt="Logo preview" class="mx-auto mb-3 h-16 w-16 rounded-xl border border-[#1B2537] object-cover">
                                @elseif($editingClient && $clientId)
                                    @php
                                        $client = \App\Models\Client::find($clientId);
                                    @endphp
                                    @if($client && $client->logo_url)
                                        <img src="{{ $client->logo_url }}" alt="Current logo" class="mx-auto mb-3 h-16 w-16 rounded-xl border border-[#1B2537] object-cover">
                                    @else
                                        <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-xl bg-[#172033] text-gray-400">
                                            <x-heroicon-o-building-office class="h-8 w-8" />
                                        </div>
                                    @endif
                                @else
                                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-xl bg-[#172033] text-gray-400">
                                        <x-heroicon-o-building-office class="h-8 w-8" />
                                    </div>
                                @endif

                                <div class="text-center text-sm text-gray-300">
                                    <x-heroicon-o-cloud-arrow-up class="mx-auto mb-2 h-8 w-8 text-gray-400" />
                                    <p class="font-medium">Upload Logo</p>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF, SVG up to 2MB</p>
                                </div>

                                <input type="file" wire:model="clientLogo" accept="image/*" class="absolute inset-0 h-full w-full cursor-pointer opacity-0">
                            </div>
                            @error('clientLogo') <span class="mt-2 block text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-300">Client Name</label>
                            <input wire:model="clientName" type="text" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" placeholder="Enter client name" />
                            @error('clientName') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-300">Branch/Location</label>
                            <input wire:model="clientBranch" type="text" class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40" placeholder="Enter branch or location" />
                            @error('clientBranch') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="closeModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-600 px-5 py-2 text-sm font-semibold text-gray-300 transition-colors hover:bg-gray-700/40">
                            <x-heroicon-o-x-mark class="h-4 w-4" />
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-primary-500/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600">
                            <x-heroicon-o-check class="h-4 w-4" />
                            {{ $editingClient ? 'Update Client' : 'Create Client' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
