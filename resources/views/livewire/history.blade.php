<div>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold">History</h1>
        <div class="flex gap-2">
            <button wire:click="setViewMode('grid')" class="px-4 py-2 rounded-lg border {{ $viewMode === 'grid' ? 'bg-blue-500 text-white border-blue-500' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <x-heroicon-o-squares-2x2 class="w-4 h-4 inline mr-2" />
                Grid View
            </button>
            <button wire:click="setViewMode('table')" class="px-4 py-2 rounded-lg border {{ $viewMode === 'table' ? 'bg-blue-500 text-white border-blue-500' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <x-heroicon-o-table-cells class="w-4 h-4 inline mr-2" />
                Table View
            </button>
        </div>
    </div>

    @if($viewMode === 'grid')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($histories as $history)
                <div class="border-2 rounded-lg shadow-sm p-6
                    @if($history->action === 'delete') bg-red-50 dark:bg-red-900/20 border-red-500 dark:border-red-400
                    @elseif($history->action === 'create') bg-green-50 dark:bg-green-900/20 border-green-500 dark:border-green-400
                    @elseif($history->action === 'update') bg-orange-50 dark:bg-orange-900/20 border-orange-500 dark:border-orange-400
                    @else bg-white dark:bg-gray-800
                    @endif">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            @if($history->action === 'create')
                                <x-heroicon-o-plus-circle class="w-5 h-5 text-green-500" />
                            @elseif($history->action === 'update')
                                <x-heroicon-o-pencil class="w-5 h-5 text-orange-500" />
                            @elseif($history->action === 'delete')
                                <x-heroicon-o-trash class="w-5 h-5 text-red-500" />
                            @endif
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($history->action) }}</span>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $history->created_at->format('M d, H:i') }}</span>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">User:</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $history->user->name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Type:</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($history->model) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        @if($history->action === 'create')
                            <div class="text-sm font-medium text-gray-900 dark:text-white mb-2">Created with:</div>
                            <div class="space-y-1">
                                @foreach($history->changes as $field => $value)
                                    @if($field !== 'deleted')
                                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $field)) }}: <span class="font-medium">{{ is_array($value) ? implode(', ', $value) : $value }}</span></div>
                                    @endif
                                @endforeach
                            </div>
                        @elseif($history->action === 'update')
                            <div class="text-sm font-medium text-gray-900 dark:text-white mb-2">Updated:</div>
                            <div class="space-y-1">
                                @foreach($history->changes as $field => $value)
                                    @if($field !== 'deleted')
                                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $field)) }}: <span class="font-medium">{{ is_array($value) ? implode(', ', $value) : $value }}</span></div>
                                    @endif
                                @endforeach
                            </div>
                        @elseif($history->action === 'delete')
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Deleted: {{ $history->changes['name'] ?? 'Unknown' }}</div>
                        @else
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ json_encode($history->changes) }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" style="table-layout: fixed;">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/5">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/5">User</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/5">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/5">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/5">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($histories as $history)
                             <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors
                                 @if($history->action === 'delete') bg-red-100 dark:bg-red-900/30
                                 @elseif($history->action === 'create') bg-green-100 dark:bg-green-900/30
                                 @elseif($history->action === 'update') bg-orange-100 dark:bg-orange-900/30
                                 @endif">
                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $history->created_at->format('Y-m-d H:i:s') }}</td>
                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $history->user->name }}</td>
                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-center">
                                     {{ ucfirst($history->action) }}
                                 </td>
                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ ucfirst($history->model) }}</td>
                                 <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                     @if($history->action === 'create')
                                         <div class="font-medium mb-1">Created with:</div>
                                         <div class="space-y-1">
                                             @foreach($history->changes as $field => $value)
                                                 <div class="text-xs">{{ ucfirst(str_replace('_', ' ', $field)) }}: <span class="font-medium">{{ is_array($value) ? implode(', ', $value) : $value }}</span></div>
                                             @endforeach
                                         </div>
                                     @elseif($history->action === 'update')
                                         <div class="font-medium mb-1">Updated:</div>
                                         <div class="space-y-1">
                                             @foreach($history->changes as $field => $value)
                                                 <div class="text-xs">{{ ucfirst(str_replace('_', ' ', $field)) }}: <span class="font-medium">{{ is_array($value) ? implode(', ', $value) : $value }}</span></div>
                                             @endforeach
                                         </div>
                                     @elseif($history->action === 'delete')
                                         <div class="font-medium">Deleted: {{ $history->changes['name'] ?? 'Unknown' }}</div>
                                     @else
                                         {{ json_encode($history->changes) }}
                                     @endif
                                 </td>
                             </tr>
                         @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
