<div>
    @if($showUpload)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Upload Image</h3>

                @if (session()->has('message'))
                    <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('message') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <form wire:submit.prevent="saveImage">
                    <div class="mb-4">
                        <input type="file" wire:model="image" accept="image/*" class="w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('image') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                        @if($image)
                            <div class="mt-2">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Selected: {{ $image->getClientOriginalName() }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Size: {{ number_format($image->getSize() / 1024, 2) }} KB</p>
                            </div>
                        @endif
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" wire:click="$set('showUpload', false)"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <button wire:click="$set('showUpload', true)"
            class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded flex items-center gap-2">
        <x-heroicon-o-camera class="w-4 h-4" />
        {{ $hasImage ? 'Change Image' : 'Upload Image' }}
    </button>
</div>
