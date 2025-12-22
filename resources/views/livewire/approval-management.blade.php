<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Material Release Approvals</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Review and approve material release requests from users</p>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-500 text-green-700 dark:text-green-400 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-500 text-red-700 dark:text-red-400 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter Tabs --}}
    <div class="mb-6 flex gap-2 border-b border-gray-200 dark:border-gray-700">
        <button wire:click="setFilter('pending')" 
            class="px-4 py-2 font-medium transition-colors {{ $filter === 'pending' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-600 dark:text-gray-400 hover:text-orange-600' }}">
            Pending
            @if($pendingCount > 0)
                <span class="ml-2 px-2 py-1 text-xs bg-orange-500 text-white rounded-full">{{ $pendingCount }}</span>
            @endif
        </button>
        <button wire:click="setFilter('approved')" 
            class="px-4 py-2 font-medium transition-colors {{ $filter === 'approved' ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-600 dark:text-gray-400 hover:text-green-600' }}">
            Approved
            @if($approvedCount > 0)
                <span class="ml-2 px-2 py-1 text-xs bg-green-500 text-white rounded-full">{{ $approvedCount }}</span>
            @endif
        </button>
        <button wire:click="setFilter('declined')" 
            class="px-4 py-2 font-medium transition-colors {{ $filter === 'declined' ? 'text-red-600 border-b-2 border-red-600' : 'text-gray-600 dark:text-gray-400 hover:text-red-600' }}">
            Declined
            @if($declinedCount > 0)
                <span class="ml-2 px-2 py-1 text-xs bg-red-500 text-white rounded-full">{{ $declinedCount }}</span>
            @endif
        </button>
        <button wire:click="setFilter('all')" 
            class="px-4 py-2 font-medium transition-colors {{ $filter === 'all' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 dark:text-gray-400 hover:text-blue-600' }}">
            All Requests
        </button>
    </div>

    {{-- Approvals Grid --}}
    @if($approvals->isEmpty())
        <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <x-heroicon-o-clipboard-document-check class="w-16 h-16 mx-auto text-gray-400 mb-4" />
            <p class="text-gray-600 dark:text-gray-400">No {{ $filter === 'all' ? '' : $filter }} approval requests found.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($approvals as $approval)
                <div class="border-2 rounded-lg shadow-sm p-6
                    @if($approval->status === 'pending') bg-orange-50 dark:bg-orange-900/20 border-orange-500
                    @elseif($approval->status === 'approved') bg-green-50 dark:bg-green-900/20 border-green-500
                    @elseif($approval->status === 'declined') bg-red-50 dark:bg-red-900/20 border-red-500
                    @endif">
                    
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            @if($approval->status === 'pending')
                                <x-heroicon-o-clock class="w-5 h-5 text-orange-500" />
                            @elseif($approval->status === 'approved')
                                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                            @elseif($approval->status === 'declined')
                                <x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />
                            @endif
                            <span class="text-sm font-bold uppercase">{{ $approval->status }}</span>
                        </div>
                        <span class="text-xs text-gray-500">{{ $approval->created_at->diffForHumans() }}</span>
                    </div>

                    {{-- Requester Info --}}
                    <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 mb-2">
                            <x-heroicon-o-user class="w-4 h-4 text-gray-500" />
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $approval->requester->name }}</span>
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            {{ $approval->created_at->format('M d, Y - h:i A') }}
                        </div>
                    </div>

                    {{-- Material Details --}}
                    <div class="space-y-2 mb-4">
                        <div class="bg-white/40 dark:bg-gray-800/40 px-3 py-2 rounded">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Item:</span>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $approval->inventory->brand }} - {{ $approval->inventory->description }}
                            </div>
                        </div>
                        <div class="bg-white/40 dark:bg-gray-800/40 px-3 py-2 rounded">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Quantity:</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $approval->quantity_requested }}</span>
                        </div>
                        @if($approval->reason)
                            <div class="bg-white/40 dark:bg-gray-800/40 px-3 py-2 rounded">
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Reason:</span>
                                <div class="text-sm text-gray-900 dark:text-white">{{ $approval->reason }}</div>
                            </div>
                        @endif
                    </div>

                    {{-- Review Info (if reviewed) --}}
                    @if($approval->status !== 'pending' && $approval->reviewer)
                        <div class="mb-4 p-3 bg-white/60 dark:bg-gray-800/60 rounded">
                            <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Reviewed by:</div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $approval->reviewer->name }}</div>
                            <div class="text-xs text-gray-500">{{ $approval->reviewed_at->format('M d, Y - h:i A') }}</div>
                            @if($approval->review_notes)
                                <div class="mt-2 text-xs text-gray-700 dark:text-gray-300">{{ $approval->review_notes }}</div>
                            @endif
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="flex gap-2">
                        @if($approval->status === 'pending')
                            <button wire:click="openReviewModal({{ $approval->id }})" 
                                class="flex-1 px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors">
                                <x-heroicon-o-pencil class="w-4 h-4 inline mr-1" />
                                Review
                            </button>
                        @endif
                        
                        @if($approval->chat_id)
                            <button wire:click="openChat({{ $approval->chat_id }})" 
                                class="flex-1 px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">
                                <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 inline mr-1" />
                                Chat
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Review Modal --}}
    @if($showReviewModal && $selectedApproval)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click="closeReviewModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" 
                wire:click.stop>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Review Request</h2>
                        <button wire:click="closeReviewModal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    {{-- Request Details --}}
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Requested by:</label>
                            <div class="text-lg font-medium text-gray-900 dark:text-white">{{ $selectedApproval->requester->name }}</div>
                            <div class="text-xs text-gray-500">{{ $selectedApproval->created_at->format('F d, Y - h:i A') }}</div>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Material Details:</div>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Item:</span>
                                    <div class="text-base font-medium text-gray-900 dark:text-white">
                                        {{ $selectedApproval->inventory->brand }} - {{ $selectedApproval->inventory->description }}
                                    </div>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Quantity Requested:</span>
                                    <div class="text-base font-medium text-gray-900 dark:text-white">{{ $selectedApproval->quantity_requested }}</div>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Available Stock:</span>
                                    <div class="text-base font-medium text-gray-900 dark:text-white">{{ $selectedApproval->inventory->quantity }}</div>
                                </div>
                            </div>
                        </div>

                        @if($selectedApproval->reason)
                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Reason:</label>
                                <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded text-gray-900 dark:text-white">{{ $selectedApproval->reason }}</div>
                            </div>
                        @endif

                        <div>
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 block">Review Notes (Optional):</label>
                            <textarea wire:model="reviewNotes" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
                                rows="3"
                                placeholder="Add notes for the requester..."></textarea>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-3">
                        <button wire:click="approveRequest({{ $selectedApproval->id }})" 
                            class="flex-1 px-4 py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition-colors">
                            <x-heroicon-o-check-circle class="w-5 h-5 inline mr-2" />
                            Approve
                        </button>
                        <button wire:click="declineRequest({{ $selectedApproval->id }})" 
                            class="flex-1 px-4 py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded-lg transition-colors">
                            <x-heroicon-o-x-circle class="w-5 h-5 inline mr-2" />
                            Decline
                        </button>
                        <button wire:click="closeReviewModal" 
                            class="px-4 py-3 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
