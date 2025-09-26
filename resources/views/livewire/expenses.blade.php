<div>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold">Expenses Tracking</h1>
        @if(auth()->user()->isSystemAdmin())
            <div class="flex gap-3">
                <button wire:click="openClientModal" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 shadow-lg border-2 border-green-500 transform hover:scale-105 transition-all duration-200">
                    <x-heroicon-o-user-plus class="w-5 h-5" />
                    Add New Client
                </button>
            </div>
        @endif
    
        <!-- Delete Client Modal -->
        @if($showDeleteClientModal)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" id="delete-client-modal">
                <div class="p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delete Client</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Are you sure you want to delete this client? This action cannot be undone.
                        </p>
                        <form wire:submit.prevent="confirmDeleteClient" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Enter your password to confirm</label>
                                <input type="password" wire:model="deletePassword" class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline" />
                                @error('deletePassword') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex items-center justify-between">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded">Delete Client</button>
                                <button type="button" wire:click="closeModal" class="bg-gray-500 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
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
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 max-w-screen-2xl mx-auto">
            @foreach($clients as $client)
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow hover:shadow-lg transition-all duration-200 border border-gray-200 dark:border-gray-700 min-h-48 flex flex-col">
                <!-- 1st Row: Logo, Client, Branch, Buttons -->
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center flex-1">
                        @if($client->logo_url)
                            <img src="{{ $client->logo_url }}" alt="{{ $client->name }} logo" class="w-12 h-12 rounded-lg object-cover border-2 border-gray-200 dark:border-gray-700">
                        @else
                            <div class="w-12 h-12 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center border-2 border-gray-300 dark:border-gray-600">
                                <x-heroicon-o-building-office class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                            </div>
                        @endif
                        <div class="flex flex-col ml-3">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $client->name }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ $client->branch }}</p>
                        </div>
                    </div>
                    @if(auth()->user()->isSystemAdmin())
                        <div class="flex gap-2">
                            <button wire:click="openClientModal({{ $client->id }})"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 p-2 rounded-md hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                    title="Edit Client">
                                <x-heroicon-o-pencil class="w-5 h-5" />
                            </button>
                            <button wire:click="openDeleteClientModal({{ $client->id }})"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 p-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                    title="Delete Client">
                                <x-heroicon-o-trash class="w-5 h-5" />
                            </button>
                        </div>
                    @endif
                </div>

                <!-- 2nd Row: Dates, Status, Job Type -->
                <div class="flex flex-col gap-1 text-sm mb-3">
                    @if($client->start_date)
                        <div class="text-gray-500 dark:text-gray-400">
                            <div class="flex">
                                <x-heroicon-o-calendar class="w-4 h-4 mr-1" />
                                Date Started: {{ $client->start_date->format('M d, Y') }}
                            </div>
                            @if($client->end_date)
                                <div class="flex mt-1">
                                    <x-heroicon-o-calendar class="w-4 h-4 mr-1" />
                                    Date Ended: {{ $client->end_date->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="flex gap-2">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            @if($client->status == 'settled') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                            {{ ucfirst(str_replace('_', ' ', $client->status)) }}
                        </span>
                        @if($client->job_type)
                            <span class="text-gray-500 dark:text-gray-400 capitalize">{{ $client->job_type }}</span>
                        @endif
                    </div>
                </div>

                <!-- 3rd Row: Total Expenses and View Button -->
                <div class="flex justify-between items-center pt-3 border-t border-gray-200 dark:border-gray-700 mt-auto">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Expenses</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">₱{{ number_format($client->expenses->sum('total_cost'), 2) }}</p>
                    </div>
                    <button wire:click="viewExpenses({{ $client->id }})" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 shadow-lg transform hover:scale-105 transition-all duration-200">
                        <x-heroicon-o-eye class="w-4 h-4" />
                        View Expenses
                    </button>
                </div>

            </div>
        @endforeach
        </div>
    @endif

    <!-- Modal -->
    @if($selectedClient)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" id="expenses-modal">
            <div class="p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white dark:bg-gray-800">
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
                                @if(auth()->user()->isSystemAdmin())
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($clientExpenses as $expense)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $expense->inventory->brand }} - {{ $expense->inventory->description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $expense->quantity_used }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        @if($editingExpenseId == $expense->id)
                                            <input type="number" min="0" step="0.01" wire:model="editCostPerUnit" class="w-20 shadow appearance-none border rounded py-1 px-2 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline" />
                                            @error('editCostPerUnit') <span class="text-red-500 text-xs block">{{ $message }}</span> @enderror
                                        @else
                                            ₱{{ number_format($expense->cost_per_unit, 2) }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">₱{{ number_format($expense->total_cost, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $expense->released_at->setTimezone('Asia/Manila')->format('M d, Y H:i') }}</td>
                                    @if(auth()->user()->isSystemAdmin())
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($editingExpenseId == $expense->id)
                                            <button wire:click="saveEditExpense" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-2">Save</button>
                                            <button wire:click="cancelEditExpense" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">Cancel</button>
                                        @else
                                            <button wire:click="editExpense({{ $expense->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</button>
                                        @endif
                                    </td>
                                    @endif
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
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" id="client-modal">
            <div class="p-5 border w-11/12 max-w-lg shadow-lg rounded-md bg-white dark:bg-gray-800">
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
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                                <input wire:model="startDate"
                                       type="date"
                                       class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline" />
                                @error('startDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                                <input wire:model="endDate"
                                       type="date"
                                       class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline" />
                                @error('endDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Job Type</label>
                            <select wire:model="jobType" class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline">
                                <option value="">Select job type...</option>
                                <option value="service">Service</option>
                                <option value="installation">Installation</option>
                            </select>
                            @error('jobType') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
