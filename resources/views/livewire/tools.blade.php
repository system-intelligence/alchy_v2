<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-100">Tools & Equipment</h1>
            <p class="text-sm text-gray-400">Manage and track your tools and equipment inventory</p>
        </div>
        @if(auth()->user()->isSystemAdmin())
            <button wire:click="openModal" class="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-primary-500/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600">
                <x-heroicon-o-plus class="h-4 w-4" />
                Add Tool
            </button>
        @endif
    </div>

    @if (session()->has('message'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    <!-- Search Bar -->
    <div>
        <div class="relative">
            <x-heroicon-o-magnifying-glass class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search brand, model, description, released to..."
                class="w-full rounded-xl border border-[#1B2537] bg-[#0d1829] py-3 pl-12 pr-4 text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
            />
        </div>
    </div>

    <!-- Tools Table -->
    <div class="overflow-hidden rounded-2xl border border-[#1B2537] bg-[#0d1829] shadow-lg shadow-black/20">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#1B2537]">
                <thead class="bg-[#121f33]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Image</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">QTY</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Brand</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Model</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Description</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Ownership</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Released To</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Date</th>
                        @if(auth()->user()->isSystemAdmin())
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1B2537] bg-[#0d1829]">
                    @forelse($tools as $tool)
                        <tr class="transition-colors hover:bg-[#121f33]">
                            <td class="whitespace-nowrap px-6 py-4">
                                @if($tool->hasImageBlob())
                                    <img wire:click="viewImage({{ $tool->id }})" src="{{ $tool->image_url }}" alt="{{ $tool->brand }}" class="h-12 w-12 rounded-lg object-cover border border-[#1B2537] cursor-pointer hover:opacity-80 transition-opacity">
                                @else
                                    <span class="text-gray-400">no changes for all uniform approach</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-100">{{ $tool->quantity }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-300">{{ $tool->brand }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-300">{{ $tool->model ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-300">{{ $tool->description }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if($tool->ownership_type === 'company')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-500/15 border border-blue-500/40 px-2.5 py-1 text-xs font-semibold text-blue-100">
                                        <x-heroicon-o-building-office class="h-3 w-3" />
                                        Company-Owned
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/15 border border-amber-500/40 px-2.5 py-1 text-xs font-semibold text-amber-100">
                                        <x-heroicon-o-user class="h-3 w-3" />
                                        Employee-Owned
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-300">{{ $tool->released_to ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-400">
                                {{ $tool->release_date ? $tool->release_date->format('M d, Y') : '—' }}
                            </td>
                            @if(auth()->user()->isSystemAdmin())
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <button 
                                            wire:click="openModal({{ $tool->id }})"
                                            class="inline-flex items-center gap-1 rounded-lg border border-primary-500/40 bg-primary-500/15 px-3 py-1.5 text-xs font-semibold text-primary-100 transition-colors hover:bg-primary-500/25"
                                        >
                                            <x-heroicon-o-pencil class="h-3.5 w-3.5" />
                                            Edit
                                        </button>
                                        <button 
                                            wire:click="openDeleteModal({{ $tool->id }})"
                                            class="inline-flex items-center gap-1 rounded-lg border border-red-500/40 bg-red-500/15 px-3 py-1.5 text-xs font-semibold text-red-100 transition-colors hover:bg-red-500/25"
                                        >
                                            <x-heroicon-o-trash class="h-3.5 w-3.5" />
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isSystemAdmin() ? '9' : '8' }}" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-wrench-screwdriver class="h-12 w-12 text-gray-600" />
                                    <p class="text-sm font-semibold text-gray-200">No tools found</p>
                                    <p class="text-xs text-gray-500">{{ $search ? 'Try adjusting your search' : 'Add your first tool to get started' }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $tools->links() }}
    </div>

    <!-- Add/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-[99999] min-h-screen bg-black/80 backdrop-blur-md" style="margin: 0; padding: 0; overflow: hidden;" wire:click.self="closeModal">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto">
                <div class="w-full rounded-2xl border border-[#1B2537] bg-[#101828] p-8 shadow-2xl" wire:click.stop>
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-white">{{ $editingToolId ? 'Edit Tool' : 'Add New Tool' }}</h3>
                        <button wire:click="closeModal" class="text-gray-400 transition-colors hover:text-gray-200">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">Tool Image</label>
                        <div class="flex justify-center">
                            <label for="image-upload" class="relative flex h-32 w-32 cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-[#1B2537] bg-[#0d1829] transition-colors hover:border-primary-500 hover:bg-[#121f33]">
                                @if($image)
                                    <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="absolute inset-0 h-full w-full rounded-xl object-cover">
                                @elseif($existingImage)
                                    <img src="{{ $existingImage }}" alt="Current image" class="absolute inset-0 h-full w-full rounded-xl object-cover">
                                    <div class="absolute inset-0 flex items-center justify-center rounded-xl bg-black/50 opacity-0 transition-opacity hover:opacity-100">
                                        <span class="text-xs font-medium text-white">Change Image</span>
                                    </div>
                                @else
                                    <x-heroicon-o-photo class="h-8 w-8 text-gray-500" />
                                    <span class="text-xs font-medium text-gray-400">Upload Image</span>
                                    <span class="text-[10px] text-gray-600">PNG, JPG up to 2MB</span>
                                @endif
                                <input 
                                    id="image-upload"
                                    type="file" 
                                    wire:model="image" 
                                    accept="image/*"
                                    class="hidden"
                                />
                            </label>
                        </div>
                        @error('image') <span class="mt-1 block text-center text-xs text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Quantity *</label>
                            <input 
                                type="number" 
                                wire:model="quantity" 
                                min="1"
                                class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-4 py-2.5 text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                                placeholder="Enter quantity"
                            />
                            @error('quantity') <span class="mt-1 text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Brand *</label>
                            <input 
                                type="text" 
                                wire:model="brand" 
                                class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-4 py-2.5 text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                                placeholder="Enter brand"
                            />
                            @error('brand') <span class="mt-1 text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">Model</label>
                        <input 
                            type="text" 
                            wire:model="model" 
                            class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-4 py-2.5 text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                            placeholder="Enter model"
                        />
                        @error('model') <span class="mt-1 text-xs text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">Description *</label>
                        <input 
                            type="text" 
                            wire:model="description" 
                            class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-4 py-2.5 text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                            placeholder="Enter description"
                        />
                        @error('description') <span class="mt-1 text-xs text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">Ownership Type *</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="group relative flex cursor-pointer items-center gap-3 rounded-xl border-2 bg-[#0d1829] p-4 transition-all duration-200 active:scale-95 {{ $ownership_type === 'company' ? 'border-blue-500 bg-blue-500/10 shadow-lg shadow-blue-500/20' : 'border-[#1B2537] hover:border-blue-400/50 hover:bg-[#121f33]' }}">
                                <input type="radio" wire:model.live="ownership_type" value="company" class="peer sr-only" />
                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl transition-all duration-200 {{ $ownership_type === 'company' ? 'bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-md scale-110' : 'bg-blue-500/15 text-blue-400 group-hover:bg-blue-500/25 group-active:scale-95' }}">
                                    <x-heroicon-o-building-office class="h-6 w-6" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold transition-colors {{ $ownership_type === 'company' ? 'text-blue-100' : 'text-gray-100 group-hover:text-blue-200' }}">Company-Owned</p>
                                    <p class="text-xs text-gray-500">Owned by employer</p>
                                </div>
                                @if($ownership_type === 'company')
                                    <div class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-blue-500 shadow-lg animate-in zoom-in duration-200">
                                        <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @endif
                            </label>
                            <label class="group relative flex cursor-pointer items-center gap-3 rounded-xl border-2 bg-[#0d1829] p-4 transition-all duration-200 active:scale-95 {{ $ownership_type === 'employee' ? 'border-amber-500 bg-amber-500/10 shadow-lg shadow-amber-500/20' : 'border-[#1B2537] hover:border-amber-400/50 hover:bg-[#121f33]' }}">
                                <input type="radio" wire:model.live="ownership_type" value="employee" class="peer sr-only" />
                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl transition-all duration-200 {{ $ownership_type === 'employee' ? 'bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-md scale-110' : 'bg-amber-500/15 text-amber-400 group-hover:bg-amber-500/25 group-active:scale-95' }}">
                                    <x-heroicon-o-user class="h-6 w-6" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold transition-colors {{ $ownership_type === 'employee' ? 'text-amber-100' : 'text-gray-100 group-hover:text-amber-200' }}">Employee-Owned</p>
                                    <p class="text-xs text-gray-500">Personal property</p>
                                </div>
                                @if($ownership_type === 'employee')
                                    <div class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 shadow-lg animate-in zoom-in duration-200">
                                        <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @endif
                            </label>
                        </div>
                        @error('ownership_type') <span class="mt-1 text-xs text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Released To</label>
                            <input 
                                type="text" 
                                wire:model="released_to" 
                                class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-4 py-2.5 text-sm text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                                placeholder="Enter recipient"
                            />
                            @error('released_to') <span class="mt-1 text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Release Date</label>
                            <input 
                                type="date" 
                                wire:model="release_date" 
                                class="w-full rounded-lg border border-[#1B2537] bg-[#0d1829] px-4 py-2.5 text-sm text-gray-100 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                            />
                            @error('release_date') <span class="mt-1 text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button 
                            type="button" 
                            wire:click="closeModal" 
                            class="rounded-lg border border-gray-600 px-5 py-2.5 text-sm font-semibold text-gray-300 transition-colors hover:bg-gray-700/40"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="rounded-lg bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/20 transition-colors hover:bg-primary-600"
                        >
                            {{ $editingToolId ? 'Update Tool' : 'Add Tool' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[99999] min-h-screen bg-black/80 backdrop-blur-md" style="margin: 0; padding: 0; overflow: hidden;" wire:click.self="closeDeleteModal">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="w-full max-w-md rounded-2xl border border-[#1B2537] bg-[#101828] p-6 shadow-2xl" wire:click.stop>
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/10 text-red-300">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
                    </span>
                    <h3 class="text-lg font-semibold text-white">Delete Tool</h3>
                </div>
                <p class="mb-6 text-sm text-gray-400">Are you sure you want to delete this tool? This action cannot be undone.</p>
                <div class="flex items-center justify-end gap-3">
                    <button 
                        wire:click="closeDeleteModal" 
                        class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-semibold text-gray-300 transition-colors hover:bg-gray-700/40"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="deleteTool" 
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-red-600/30 transition-colors hover:bg-red-700"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
        </div>
    @endif

    <!-- Image Viewer Modal -->
    @if($showImageViewer)
        <div class="fixed inset-0 z-[99999] min-h-screen bg-black/90 backdrop-blur-md" style="margin: 0; padding: 0; overflow: hidden;" wire:click="closeImageViewer">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative max-h-[90vh] max-w-5xl" wire:click.stop>
                    <!-- Close button -->
                    <button wire:click="closeImageViewer" class="absolute -right-4 -top-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-red-500 text-white shadow-lg transition-colors hover:bg-red-600">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                    <!-- Expanded Image -->
                    <img src="{{ $viewingImage }}" alt="Tool Image" class="max-h-[90vh] w-auto rounded-2xl shadow-2xl">
                </div>
            </div>
        </div>
    @endif
</div>
