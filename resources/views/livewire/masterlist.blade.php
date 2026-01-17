 @php
    use App\Enums\InventoryStatus;
@endphp

<div>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-2">
            <h1 class="text-2xl font-bold">Masterlist</h1>
        </div>
        <div class="flex-1 md:max-w-2xl">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex flex-1 gap-2">     
                    <input
                        type="text"
                        wire:model.debounce.400ms="search"
                        wire:keydown.enter="performSearch"
                        placeholder="Search brand, description, location..."
                        class="flex-1 shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    />
                    <button
                        wire:click="performSearch"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded flex items-center gap-2 shadow-md border-2 border-blue-500 whitespace-nowrap"
                    >
                        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        <span class="hidden sm:inline">Search</span>
                    </button>
                </div>
                <select
                    wire:change="filterByStatus($event.target.value)"
                    class="shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                >
                    <option value="">All Status</option>
                    <option value="normal">Normal</option>
                    <option value="critical">Critical</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
                <select
                    wire:change="filterByCategory($event.target.value)"
                    class="shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                >
                    <option value="">All Locations</option>
                    @foreach(\App\Models\Inventory::CATEGORIES as $categoryOption)
                        <option value="{{ $categoryOption }}">{{ $categoryOption }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @if(auth()->user()->isSystemAdmin() || auth()->user()->isUser())
        <div class="flex gap-2">
            @if(count($selectedItems) > 0)
            <button wire:click="addSelectedToRelease"
                    class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 shadow-md border-2 border-green-500">
                <x-heroicon-o-plus class="w-4 h-4" />
                Record Release ({{ count($selectedItems) }} items)
            </button>
            @if(auth()->user()->isSystemAdmin())
            <button wire:click="bulkDelete"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                <x-heroicon-o-trash class="w-4 h-4" />
                Delete Selected ({{ count($selectedItems) }})
            </button>
            @endif
            @else
            <button class="bg-gray-400 cursor-not-allowed opacity-60 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 shadow-md border-2 border-gray-400" disabled title="Select materials from the list first">
                <x-heroicon-o-plus class="w-4 h-4" />
                Select Materials to Release
            </button>
            @endif
            @if(auth()->user()->isSystemAdmin())
            <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                <x-heroicon-o-plus class="w-4 h-4" />
                Add Inventory
            </button>
            @endif
        </div>
    @endif
</div>

<script>
function formatNumberInput(input) {
    // Remove all non-digit and non-comma characters first
    let value = input.value.replace(/[^\d,]/g, '');

    // Remove existing commas to get clean number
    let cleanValue = value.replace(/,/g, '');

    // Format with commas using regex
    if (cleanValue) {
        cleanValue = cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Update the input value
    input.value = cleanValue;
}

function formatDecimalInput(input) {
    // Remove all non-digit, non-comma, and non-decimal characters except first dot
    let value = input.value.replace(/[^\d,.\s]/g, '');
    let parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }

    // Format the integer part with commas
    if (value) {
        let [integerPart, decimalPart] = value.split('.');
        // Remove commas from integer part first
        integerPart = integerPart.replace(/,/g, '');
        // Add commas back
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        value = decimalPart !== undefined ? integerPart + '.' + decimalPart : integerPart;
    }

    // Update the input value
    input.value = value;
}
</script>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        @if(auth()->user()->isSystemAdmin() || auth()->user()->isUser())
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Brand</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        @if(auth()->user()->isSystemAdmin())
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($inventories as $index => $inventory)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            @if(auth()->user()->isSystemAdmin() || auth()->user()->isUser())
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox"
                                       wire:model.live="selectedItems"
                                       value="{{ $inventory->id }}"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $inventory->brand }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $inventory->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $inventory->category }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ number_format($inventory->quantity, 0, '.', ',') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusEnum = $inventory->status instanceof InventoryStatus
                                        ? $inventory->status
                                        : InventoryStatus::tryFrom($inventory->status) ?? InventoryStatus::NORMAL;

                                    $statusClasses = match ($statusEnum) {
                                        InventoryStatus::NORMAL => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        InventoryStatus::CRITICAL => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        InventoryStatus::OUT_OF_STOCK => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                    };
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses }}">
                                    {{ ucfirst(str_replace('_', ' ', $statusEnum->value)) }}
                                </span>
                            </td>
                            @if(auth()->user()->isSystemAdmin())
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button wire:click="openModal({{ $inventory->id }})"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 p-1 rounded-md hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                            title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                    </button>
                                    <button wire:click="openDeleteModal({{ $inventory->id }})"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 p-1 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                            title="Delete">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isSystemAdmin() ? 8 : 7 }}" class="px-6 py-6 text-center text-sm text-gray-500 dark:text-gray-300">
                                No inventory found. @if(auth()->user()->isSystemAdmin()) Click "Add Inventory" to create a new item. @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Record Release Modal -->
    @if($showReleaseModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="release-modal">
            <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Record Release</h3>
                    <form wire:submit.prevent="saveRelease" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client</label>
                            <select wire:model.live="client_id" class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline">
                                <option value="">Select client...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }} — {{ $client->branch }}</option>
                                @endforeach
                            </select>
                            @error('client_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Project</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                @if(empty($client_id))
                                    💡 Select a client first to see available projects
                                @elseif(empty($projects))
                                    ⚠️ No projects found for this client
                                @else
                                    ✅ Projects loaded for selected client
                                @endif
                            </p>
                            <select wire:model="project_id" class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline" @if(empty($projects)) disabled @endif>
                                <option value="">
                                    @if(empty($client_id))
                                        Select a client first...
                                    @elseif(empty($projects))
                                        No projects available
                                    @else
                                        Select project...
                                    @endif
                                </option>
                                @forelse($projects as $project)
                                    <option value="{{ $project['id'] }}">{{ $project['name'] }} — {{ $project['reference_code'] }}</option>
                                @empty
                                    <option disabled>No projects available</option>
                                @endforelse
                            </select>
                            @error('project_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>


                        <!-- Selected Materials Table -->
                        @if(count($releaseItems) > 0)
                        <div class="border-t pt-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Selected Materials</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Material</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Quantity</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cost per Unit</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($releaseItems as $index => $item)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">
                                                @if(isset($item['inventory']) && $item['inventory'])
                                                    {{ $item['inventory']->brand }} - {{ $item['inventory']->description }}
                                                @else
                                                    Unknown Material (ID: {{ $item['inventory_id'] ?? 'N/A' }})
                                                @endif
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" inputmode="numeric" pattern="[0-9,]*" wire:model="releaseItems.{{ $index }}.quantity_used" class="w-24 shadow appearance-none border rounded py-1 px-2 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none" onblur="formatNumberInput(this)" />
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" inputmode="decimal" pattern="[0-9,]*\.?[0-9,]*" wire:model="releaseItems.{{ $index }}.cost_per_unit" class="w-24 shadow appearance-none border rounded py-1 px-2 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none" onblur="formatDecimalInput(this)" />
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">
                                                ₱{{ number_format((float)str_replace(',', '', $item['quantity_used'] ?? '0') * (float)str_replace(',', '', $item['cost_per_unit'] ?? '0'), 2) }}
                                            </td>
                                            <td class="px-4 py-2">
                                                <button type="button" wire:click="removeReleaseItem({{ $index }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    <x-heroicon-o-trash class="w-4 h-4" />
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @error('releaseItems') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="flex items-center justify-between border-t pt-4">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                @if(count($releaseItems) === 0)
                                    <span class="text-gray-500">No materials added yet</span>
                                @else
                                    <span class="text-green-600">✓ {{ count($releaseItems) }} material(s) ready for release</span>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">Save Release</button>
                                <button type="button" wire:click="closeModal" class="bg-gray-500 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Duplicate Material Modal -->
    @if($showDuplicateModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-md w-full mx-4">
                <div class="text-center">
                    <x-heroicon-o-exclamation-triangle class="w-12 h-12 text-yellow-500 mx-auto mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Duplicate Material</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $duplicateMessage }}</p>
                    <button wire:click="closeDuplicateModal" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                        OK
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Inventory Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" id="delete-inventory-modal">
            <div class="p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delete Inventory Item</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Are you sure you want to delete this inventory item? This action cannot be undone.
                    </p>
                    <form wire:submit.prevent="confirmDelete" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Enter your password to confirm</label>
                            <input type="password" wire:model="deletePassword" class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline" />
                            @error('deletePassword') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded">Delete Item</button>
                            <button type="button" wire:click="closeModal" class="bg-gray-500 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Delete Inventory Modal -->
    @if($showBulkDeleteModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" id="bulk-delete-inventory-modal">
            <div class="p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delete Inventory Items</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Are you sure you want to delete {{ count($selectedItems) }} selected inventory items? This action cannot be undone.
                    </p>
                    <form wire:submit.prevent="confirmBulkDelete" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Enter your password to confirm</label>
                            <input type="password" wire:model="bulkDeletePassword" class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline" />
                            @error('bulkDeletePassword') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded">Delete Items</button>
                            <button type="button" wire:click="closeModal" class="bg-gray-500 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" id="my-modal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-[90vw] h-[80vh] mx-auto overflow-hidden">
                <div class="p-6 h-full flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $editing ? 'Edit Inventory Item' : 'Add Inventory' }}</h3>
                        <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                    @if(!$editing)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">This is a physical count to ensure inventory accuracy.</p>
                    @endif

                    @if($editing)
                        <!-- Tabs -->
                        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                            <nav class="-mb-px flex space-x-8">
                                <button
                                    wire:click="switchTab('edit')"
                                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'edit' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                                >
                                    Edit Details
                                </button>
                                <button
                                    wire:click="switchTab('inbound')"
                                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'inbound' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                                >
                                    Inbound Inventory
                                </button>
                                <button
                                    wire:click="switchTab('movements')"
                                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'movements' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                                >
                                    Stock Movements
                                </button>
                            </nav>
                        </div>
                    @endif

                    <div class="flex-1 overflow-y-auto">

                    <!-- Edit Details Tab -->
                    @if($activeTab === 'edit')
                        <form wire:submit.prevent="save" class="space-y-6">
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="brand">Brand</label>
                            <input wire:model="brand" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="brand">
                            @error('brand') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="description">Description</label>
                            <textarea wire:model="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="description"></textarea>
                            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="category">Location</label>
                            <select wire:model="category" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="category">
                                <option value="">Select location...</option>
                                @foreach(\App\Models\Inventory::CATEGORIES as $categoryOption)
                                    <option value="{{ $categoryOption }}">{{ $categoryOption }}</option>
                                @endforeach
                            </select>
                            @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="quantity">Quantity</label>
                            @if($editing)
                                <input wire:model="quantity" type="text" inputmode="numeric" pattern="[0-9,]*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 dark:bg-gray-600 cursor-not-allowed" id="quantity" oninput="formatNumberInput(this)" disabled readonly title="Quantity changes should be made through the Inbound Inventory tab">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">⚠️ Quantity can only be adjusted through the Inbound Inventory tab</p>
                            @else
                                <input wire:model="quantity" type="text" inputmode="numeric" pattern="[0-9,]*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="quantity" oninput="formatNumberInput(this)">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Enter the initial physical count for this inventory item.</p>
                            @endif
                            @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="min_stock_level">Minimum Stock Level</label>
                            <input wire:model="min_stock_level" type="text" inputmode="numeric" pattern="[0-9,]*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="min_stock_level" oninput="formatNumberInput(this)">
                            @error('min_stock_level') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @if($editing)
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="editPassword">Enter your password to confirm</label>
                            <input wire:model="editPassword" type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="editPassword">
                            @error('editPassword') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endif
                       <div class="flex items-center justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                           <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                               {{ $editing ? 'Update Inventory' : 'Create Inventory' }}
                           </button>
                       </div>
                       </form>
                   @endif

                   <!-- Inbound Inventory Tab -->
                   @if($activeTab === 'inbound')
                       <form wire:submit.prevent="addInboundStock" class="space-y-6">
                           <div class="mb-4">
                               <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="inboundQuantity">Quantity to Add</label>
                               <input wire:model="inboundQuantity" type="text" inputmode="numeric" pattern="[0-9,]*" placeholder="Enter quantity..." class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="inboundQuantity" oninput="formatNumberInput(this)">
                               @error('inboundQuantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                           </div>
                           <div class="mb-4">
                               <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="supplier">Supplier</label>
                               <input wire:model="supplier" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="supplier">
                               @error('supplier') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                           </div>
                           <div class="mb-4">
                               <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="dateReceived">Date Received</label>
                               <input wire:model="dateReceived" type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="dateReceived">
                               @error('dateReceived') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                           </div>
                           <div class="mb-4">
                               <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="inboundNotes">Notes</label>
                               <textarea wire:model="inboundNotes" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="inboundNotes" rows="3"></textarea>
                               @error('inboundNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                           </div>
                           <div class="flex items-center justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                               <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                   Add Stock
                               </button>
                           </div>
                       </form>
                   @endif

                   <!-- Stock Movements Tab -->
                   @if($activeTab === 'movements')
                       <div class="space-y-6">
                           <!-- Filters -->
                           <div class="flex flex-col sm:flex-row gap-4">
                               <div class="flex-1">
                                   <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="movementTypeFilter">Movement Type</label>
                                   <select wire:model.live="movementTypeFilter" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="movementTypeFilter">
                                       <option value="">All Types</option>
                                       <option value="inbound">Inbound</option>
                                       <option value="outbound">Outbound</option>
                                   </select>
                               </div>
                               <div class="flex-1">
                                   <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="movementDateFilter">Date</label>
                                   <input wire:model.live="movementDateFilter" type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="movementDateFilter">
                               </div>
                           </div>

                           <!-- Movements Table -->
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
                                       @forelse($this->getFilteredStockMovements() as $movement)
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
                                           <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">{{ $movement->notes ?: '-' }}</td>
                                       </tr>
                                       @empty
                                       <tr>
                                           <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-300">
                                               No stock movements found.
                                           </td>
                                       </tr>
                                       @endforelse
                                   </tbody>
                               </table>
                           </div>

                       </div>
                   @endif
                   </div>
               </div>
           </div>
       </div>
   @endif
</div>
