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
            </div>
        </div>
        @if(auth()->user()->isSystemAdmin() || auth()->user()->isUser())
        <div class="flex gap-2">
            @if(count($selectedItems) > 0 && auth()->user()->isSystemAdmin())
            <button wire:click="bulkDelete"
                    onclick="return confirm('Are you sure you want to delete {{ count($selectedItems) }} selected items?')"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                <x-heroicon-o-trash class="w-4 h-4" />
                Delete Selected ({{ count($selectedItems) }})
            </button>
            @endif
            <button wire:click="openReleaseModal" class="{{ $clients->count() > 0 ? 'bg-red-500 hover:bg-red-600 cursor-pointer' : 'bg-red-400 cursor-not-allowed opacity-60' }} text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 shadow-md border-2 border-red-500" {{ $clients->count() === 0 ? 'disabled' : '' }}>
                <x-heroicon-o-plus class="w-4 h-4" />
                Record Release
            </button>
            @if(auth()->user()->isSystemAdmin())
            <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                <x-heroicon-o-plus class="w-4 h-4" />
                Add Inventory
            </button>
            @endif
        </div>
        @endif
    </div>

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
                        @if(auth()->user()->isSystemAdmin())
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
                            @if(auth()->user()->isSystemAdmin())
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $inventory->quantity }}</td>
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
                            <select wire:model="client_id" class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline">
                                <option value="">Select client...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }} — {{ $client->branch }}</option>
                                @endforeach
                            </select>
                            @error('client_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Add Material Section -->
                        <div class="border-t pt-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Add Materials</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Material</label>
                                    <select wire:model="selectedInventoryId" class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none focus:shadow-outline">
                                        <option value="">Select material...</option>
                                        @foreach($inventoryOptions as $item)
                                            <option value="{{ $item->id }}">{{ $item->brand }} — {{ \Illuminate\Support\Str::limit($item->description, 30) }} (Stock: {{ $item->quantity }})</option>
                                        @endforeach
                                    </select>
                                    @error('selectedInventoryId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <button type="button" wire:click="addReleaseItem" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded flex items-center gap-2">
                                        <x-heroicon-o-plus class="w-4 h-4" />
                                        Add Material
                                    </button>
                                </div>
                            </div>
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
                                            <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">{{ $item['inventory']->brand }} - {{ $item['inventory']->description }}</td>
                                            <td class="px-4 py-2">
                                                <input type="number" min="1" wire:model="releaseItems.{{ $index }}.quantity_used" class="w-20 shadow appearance-none border rounded py-1 px-2 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none" />
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" min="0" step="0.01" wire:model="releaseItems.{{ $index }}.cost_per_unit" class="w-24 shadow appearance-none border rounded py-1 px-2 text-gray-700 dark:text-gray-300 dark:bg-gray-700 focus:outline-none" />
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">
                                                ₱{{ number_format(($item['quantity_used'] ?? 0) * ($item['cost_per_unit'] ?? 0), 2) }}
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
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">Save Release</button>
                            <button type="button" wire:click="closeModal" class="bg-gray-500 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded">Cancel</button>
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

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="my-modal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ $editing ? 'Edit Inventory' : 'Add Inventory' }}</h3>
                    <form wire:submit.prevent="save">
                        <!-- Image Upload Section -->
                        <div class="flex flex-col items-center justify-center mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Inventory Image</label>

                            <!-- Upload Area -->
                            <div class="w-full max-w-sm">
                                <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-blue-400 dark:hover:border-blue-500 transition-colors cursor-pointer bg-gray-50 dark:bg-gray-800/50 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                    @if($image)
                                        <img src="{{ $image->temporaryUrl() }}" alt="Image preview" class="w-16 h-16 rounded-lg object-cover mx-auto mb-3 border border-gray-200 dark:border-gray-700">
                                    @elseif($editing && $inventoryId)
                                        @php
                                            $inventory = \App\Models\Inventory::find($inventoryId);
                                        @endphp
                                        @if($inventory && $inventory->hasImageBlob())
                                            <img src="{{ $inventory->image_url }}" alt="Current image" class="w-16 h-16 rounded-lg object-cover mx-auto mb-3 border border-gray-200 dark:border-gray-700">
                                        @else
                                            <div class="w-16 h-16 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                                                <x-heroicon-o-photo class="w-8 h-8 text-gray-500 dark:text-gray-400" />
                                            </div>
                                        @endif
                                    @else
                                        <div class="w-16 h-16 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                                            <x-heroicon-o-photo class="w-8 h-8 text-gray-500 dark:text-gray-400" />
                                        </div>
                                    @endif

                                    <div class="text-center">
                                        <x-heroicon-o-cloud-arrow-up class="w-8 h-8 text-gray-400 dark:text-gray-500 mx-auto mb-2" />
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload Image</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG up to 5MB</p>
                                    </div>

                                    <input type="file"
                                           wire:model="image"
                                           accept="image/png,image/jpeg,image/jpg"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                </div>

                                @error('image') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

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
                                <option value="Bodega Room">Bodega Room</option>
                                <option value="Alchy Room">Alchy Room</option>
                            </select>
                            @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="quantity">Quantity</label>
                            <input wire:model="quantity" type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="quantity">
                            @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="min_stock_level">Minimum Stock Level</label>
                            <input wire:model="min_stock_level" type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="min_stock_level">
                            @error('min_stock_level') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @if($editing)
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="editPassword">Enter your password to confirm</label>
                            <input wire:model="editPassword" type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="editPassword">
                            @error('editPassword') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                {{ $editing ? 'Update' : 'Create' }}
                            </button>
                            <button type="button" wire:click="closeModal" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
