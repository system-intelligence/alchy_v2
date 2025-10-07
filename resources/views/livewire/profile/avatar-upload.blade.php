<div>
    <!-- Current Avatar Display -->
    <div class="flex items-center justify-center mb-8">
        <div class="relative">
            <img class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-xl"
                  src="{{ $user->avatar_url }}"
                  alt="Current Avatar"
                  onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF'">
            @if($user->hasMedia('avatar'))
                <button wire:click="removeAvatar"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 transition-colors shadow-lg">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            @endif
        </div>
    </div>

    <!-- Drag & Drop Upload Area -->
    <div class="max-w-2xl mx-auto">
        <div class="relative">
            <div wire:loading.class="opacity-50 pointer-events-none"
                 class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-12 text-center hover:border-blue-400 dark:hover:border-blue-500 transition-colors duration-200
                        {{ $isDragOver ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : '' }}"
                 wire:drop.prevent="handleDrop"
                 wire:dragover.prevent="setDragOver(true)"
                 wire:dragleave.prevent="setDragOver(false)">

                @if($photo)
                    <!-- Preview Mode -->
                    <div class="space-y-4">
                        <div class="flex justify-center">
                            <img src="{{ $photo->temporaryUrl() }}" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg" alt="Preview">
                        </div>
                        <div class="space-y-3">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Ready to upload</p>
                            <div class="flex justify-center space-x-3">
                                <button wire:click="save"
                                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                    <x-heroicon-o-cloud-arrow-up class="w-4 h-4 mr-2" />
                                    Upload Avatar
                                </button>
                                <button wire:click="cancelUpload"
                                        class="inline-flex items-center px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors duration-200">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Upload Mode -->
                    <div class="space-y-4">
                        <div class="flex justify-center">
                            <x-heroicon-o-cloud-arrow-up class="w-16 h-16 text-gray-400 dark:text-gray-500" />
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload New Avatar</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Drag and drop your image here, or click to browse
                            </p>
                            <input type="file"
                                   wire:model="photo"
                                   accept="image/*"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                   id="avatar-upload">
                            <label for="avatar-upload"
                                   class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg cursor-pointer transition-colors duration-200">
                                <x-heroicon-o-photo class="w-4 h-4 mr-2" />
                                Choose New Avatar
                            </label>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            PNG, JPG, GIF up to 1MB • Square images work best
                        </p>
                    </div>
                @endif
            </div>

            <!-- Loading Overlay -->
            <div wire:loading class="absolute inset-0 bg-white dark:bg-gray-800 bg-opacity-75 dark:bg-opacity-75 rounded-xl flex items-center justify-center">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Uploading...</span>
                </div>
            </div>
        </div>

        @error('photo')
            <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            </div>
        @enderror
    </div>
</div>
