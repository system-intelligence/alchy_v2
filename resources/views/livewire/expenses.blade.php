<div>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold">Expenses Tracking</h1>
        @if(auth()->user()->isSystemAdmin())
            <div class="flex gap-3">
                <button wire:click="openCreateModal" class="{{ $clients->count() > 0 ? 'bg-red-500 hover:bg-red-600 cursor-pointer' : 'bg-red-400 cursor-not-allowed opacity-60' }} text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 shadow-md border-2 border-red-500" {{ $clients->count() === 0 ? 'disabled' : '' }}>
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Record Release
                </button>
                <button wire:click="openClientModal" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 shadow-md border-2 border-green-600">
                    <x-heroicon-o-user-plus class="w-4 h-4" />
                    Add Client
                </button>
            </div>
        @endif
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if($clients->count() === 0)
        <div class="text-center py-12">
            <x-heroicon-o-building-office class="w-16 h-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Clients Yet</h3>
            @if(auth()->user()->isSystemAdmin())
                <p class="text-gray-500 dark:text-gray-400 mb-6">Get started by adding your first client to begin tracking expenses.</p>
                <button wire:click="openClientModal" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg flex items-center gap-2 mx-auto">
                    <x-heroicon-o-user-plus class="w-5 h-5" />
                    Add Your First Client
                </button>
            @else
                <p class="text-gray-500 dark:text-gray-400 mb-6">Please contact your system administrator to add clients before you can record expenses.</p>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($clients as $client)
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center space-x-3">
                        @if($client->logo_url)
                            <img src="{{ $client->logo_url }}" alt="{{ $client->name }} logo" class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                        @else
                            <div class="w-12 h-12 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <x-heroicon-o-building-office class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                            </div>
                        @endif
                        <div>
                            <h3 class="text-lg font-semibold">{{ $client->name }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ $client->branch }}</p>
                        </div>
                    </div>
                    @if(auth()->user()->isSystemAdmin())
                        <div class="flex gap-2">
                            <button wire:click="openClientModal({{ $client->id }})"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 p-1 rounded-md hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                    title="Edit Client">
                                <x-heroicon-o-pencil class="w-4 h-4" />
                            </button>
                            <button onclick="if(!confirm('Are you sure you want to delete this client? This action cannot be undone.')) { event.stopImmediatePropagation(); return false; }"
                                    wire:click="deleteClient({{ $client->id }})"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 p-1 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                    title="Delete Client">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        </div>
                    @endif
                </div>
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Expenses</p>
                        <p class="text-2xl font-bold text-green-600">₱{{ number_format($client->expenses->sum('total_cost'), 2) }}</p>
                    </div>
                    <button wire:click="viewExpenses({{ $client->id }})" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                        <x-heroicon-o-eye class="w-4 h-4" />
                        View Expenses
                    </button>
                </div>
            </div>
        @endforeach
        </div>
    @endif

    <!-- Create Release Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="create-expense-modal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-xl shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Record Release</h3>
                    <form wire:submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client</label>
                            <select wire:model="client_id" class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline">
                                <option value="">Select client...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }} — {{ $client->branch }}</option>
                                @endforeach
                            </select>
                            @error('client_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Material</label>
                            <select wire:model="inventory_id" class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline">
                                <option value="">Select material...</option>
                                @foreach($inventoryOptions as $item)
                                    <option value="{{ $item->id }}">{{ $item->brand }} — {{ \Illuminate\Support\Str::limit($item->description, 40) }} (Stock: {{ $item->quantity }})</option>
                                @endforeach
                            </select>
                            @error('inventory_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                                <input type="number" min="1" wire:model="quantity_used" class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline" />
                                @error('quantity_used') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cost per Unit</label>
                                <input type="number" min="0" step="0.01" wire:model="cost_per_unit" class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline" />
                                @error('cost_per_unit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">Save</button>
                            <button type="button" wire:click="closeModal" class="bg-gray-500 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal -->
    @if($selectedClient)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="expenses-modal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Expenses for {{ $selectedClient->name }} - {{ $selectedClient->branch }}</h3>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Material</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cost per Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($clientExpenses as $expense)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $expense->inventory->brand }} - {{ $expense->inventory->description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $expense->quantity_used }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">₱{{ number_format($expense->cost_per_unit, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">₱{{ number_format($expense->total_cost, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $expense->released_at->setTimezone('Asia/Manila')->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="flex justify-end mt-4">
                        <button wire:click="closeModal" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Client Management Modal -->
    @if($showClientModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="client-modal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-lg shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">
                        {{ $editingClient ? 'Edit Client' : 'Add New Client' }}
                    </h3>
                    <form wire:submit.prevent="saveClient" class="space-y-6">
                        <!-- Logo Upload Section -->
                        <div class="flex flex-col items-center justify-center">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Client Logo</label>

                            <!-- Upload Area -->
                            <div class="w-full max-w-sm">
                                <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-blue-400 dark:hover:border-blue-500 transition-colors cursor-pointer bg-gray-50 dark:bg-gray-800/50 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                    @if($clientLogo)
                                        <img src="{{ $clientLogo->temporaryUrl() }}" alt="Logo preview" class="w-16 h-16 rounded-lg object-cover mx-auto mb-3 border border-gray-200 dark:border-gray-700">
                                    @elseif($editingClient && $clientId)
                                        @php
                                            $client = \App\Models\Client::find($clientId);
                                        @endphp
                                        @if($client && $client->logo_url)
                                            <img src="{{ $client->logo_url }}" alt="Current logo" class="w-16 h-16 rounded-lg object-cover mx-auto mb-3 border border-gray-200 dark:border-gray-700">
                                        @else
                                            <div class="w-16 h-16 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                                                <x-heroicon-o-building-office class="w-8 h-8 text-gray-500 dark:text-gray-400" />
                                            </div>
                                        @endif
                                    @else
                                        <div class="w-16 h-16 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                                            <x-heroicon-o-building-office class="w-8 h-8 text-gray-500 dark:text-gray-400" />
                                        </div>
                                    @endif

                                    <div class="text-center">
                                        <x-heroicon-o-cloud-arrow-up class="w-8 h-8 text-gray-400 dark:text-gray-500 mx-auto mb-2" />
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload Logo</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF, SVG up to 2MB</p>
                                    </div>

                                    <input type="file"
                                           wire:model="clientLogo"
                                           accept="image/*"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                </div>

                                @error('clientLogo') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Client Details -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client Name</label>
                            <input wire:model="clientName"
                                   type="text"
                                   class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline"
                                   placeholder="Enter client name" />
                            @error('clientName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch/Location</label>
                            <input wire:model="clientBranch"
                                   type="text"
                                   class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline"
                                   placeholder="Enter branch or location" />
                            @error('clientBranch') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded flex items-center gap-2">
                                <x-heroicon-o-check class="w-4 h-4" />
                                {{ $editingClient ? 'Update' : 'Create' }}
                            </button>
                            <button type="button" wire:click="closeModal" class="bg-gray-500 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded flex items-center gap-2">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
