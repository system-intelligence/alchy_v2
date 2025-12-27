<div>
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                        Notifications
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        @if($unseenCount > 0)
                            You have {{ $unseenCount }} unseen notification{{ $unseenCount > 1 ? 's' : '' }}
                        @elseif($unreadCount > 0)
                            You have {{ $unreadCount }} unread notification{{ $unreadCount > 1 ? 's' : '' }}
                        @else
                            All caught up!
                        @endif
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    @if($unseenCount > 0)
                        <button
                            wire:click="markAllAsSeen"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >c
                            <x-heroicon-o-eye class="w-4 h-4 mr-2" />
                            Mark All Seen
                        </button>
                    @endif
                    @if($unreadCount > 0)
                        <button
                            wire:click="markAllAsRead"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <x-heroicon-o-check class="w-4 h-4 mr-2" />
                            Mark All Read
                        </button>
                    @endif
                </div>
            </div>

            <!-- Filters -->
            <div class="mt-4 flex items-center space-x-4">
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        wire:model.live="showUnreadOnly"
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700"
                    >
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show unread only</span>
                </label>

                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-700 dark:text-gray-300">Filter by type:</label>
                    <select
                        wire:model.live="selectedType"
                        class="rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 text-sm"
                    >
                        <option value="">All types</option>
                        @foreach($notificationTypes as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($notifications as $notification)
                <div class="p-6 {{ $notification->is_read ? 'bg-gray-50 dark:bg-gray-900' : 'bg-white dark:bg-gray-800' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-3">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $notification->title }}
                                </h3>
                                @if(!$notification->seen)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        Unseen
                                    </span>
                                @elseif(!$notification->is_read)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                        New
                                    </span>
                                @endif
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    {{ ucfirst($notification->type) }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ $notification->message }}
                            </p>
                            @if($notification->data)
                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    @foreach($notification->data as $key => $value)
                                        <span class="inline-block mr-4">
                                            <strong>{{ ucfirst($key) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-2 ml-4">
                            @if(!$notification->seen)
                                <button
                                    wire:click="markAsSeen({{ $notification->id }})"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium"
                                >
                                    Mark Seen
                                </button>
                            @endif
                            @if(!$notification->is_read)
                                <button
                                    wire:click="markAsRead({{ $notification->id }})"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium"
                                >
                                    Mark Read
                                </button>
                            @endif
                            <button
                                wire:click="deleteNotification({{ $notification->id }})"
                                wire:confirm="Are you sure you want to delete this notification?"
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center">
                    <x-heroicon-o-bell class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No notifications</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if($showUnreadOnly)
                            You don't have any unread notifications.
                        @else
                            You're all caught up! Check back later for new notifications.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>