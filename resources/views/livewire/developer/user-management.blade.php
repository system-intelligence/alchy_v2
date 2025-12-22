<div x-data="{
    onlineUsers: @entangle('onlineUsers'),
    presenceChannel: null,
    init() {
        if (window.Echo) {
            this.presenceChannel = window.Echo.join('online')
                .here((users) => {
                    console.log('[Presence] Currently online:', users);
                    this.onlineUsers = users.map(u => u.id);
                })
                .joining((user) => {
                    console.log('[Presence] User joined:', user);
                    if (!this.onlineUsers.includes(user.id)) {
                        this.onlineUsers.push(user.id);
                    }
                    @this.call('updateUserStatus', user.id, true);
                })
                .leaving((user) => {
                    console.log('[Presence] User left:', user);
                    this.onlineUsers = this.onlineUsers.filter(id => id !== user.id);
                    @this.call('updateUserStatus', user.id, false);
                });
        }
    }
}">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">User Management ({{ $users->count() }} total)</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <span x-text="onlineUsers.length"></span> online now
                    </span>
                </p>
            </div>
            <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-1.5 px-3 rounded text-sm flex items-center gap-2">
                <x-heroicon-o-plus class="w-4 h-4" />
                Add User
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 mx-6 mt-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Login</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Logout</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if($user->hasAvatarBlob())
                                <img src="{{ $user->avatar_url }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                                @else
                                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                    <span class="text-gray-700 text-xs font-medium">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                </div>
                            @endif
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($user->role == 'developer') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                            @elseif($user->role == 'system_admin') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span 
                            x-data="{ isOnline: onlineUsers.includes({{ $user->id }}) }"
                            x-effect="isOnline = onlineUsers.includes({{ $user->id }})"
                            :class="isOnline ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'"
                            class="px-2 py-1 inline-flex items-center gap-1.5 text-xs leading-5 font-semibold rounded-full transition-all duration-300">
                            <span 
                                :class="isOnline ? 'bg-green-500' : 'bg-gray-400'"
                                class="w-1.5 h-1.5 rounded-full transition-all duration-300"
                                x-show="isOnline"
                                x-transition
                                style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                            </span>
                            <span x-text="isOnline ? 'Online' : 'Offline'"></span>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($user->last_login_at)
                            <div class="text-sm text-gray-900 dark:text-white">{{ $user->last_login_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->last_login_at->format('h:i A') }}</div>
                            @if($user->last_login_ip)
                                <div class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                    </svg>
                                    {{ $user->last_login_ip }}
                                </div>
                            @endif
                        @else
                            <span class="text-sm text-gray-400 dark:text-gray-500">Never</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($user->last_logout_at)
                            <div class="text-sm text-gray-900 dark:text-white">{{ $user->last_logout_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->last_logout_at->format('h:i A') }}</div>
                            @if($user->last_login_at && $user->last_logout_at)
                                @php
                                    $duration = $user->last_login_at->diff($user->last_logout_at);
                                    $hours = $duration->h + ($duration->days * 24);
                                    $minutes = $duration->i;
                                @endphp
                                <div class="text-xs text-blue-500 dark:text-blue-400 mt-0.5">
                                    Session: {{ $hours > 0 ? $hours . 'h ' : '' }}{{ $minutes }}m
                                </div>
                            @endif
                        @else
                            <span class="text-sm text-gray-400 dark:text-gray-500">Never</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $user->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center space-x-2">
                            <button wire:click="openModal({{ $user->id }})"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 p-1 rounded-md hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                    title="Edit">
                                <x-heroicon-o-pencil class="w-4 h-4" />
                            </button>
                            <button wire:click="openDeleteModal({{ $user->id }})"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 p-1 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                    title="Delete">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" id="user-modal">
            <div class="p-5 border w-11/12 max-w-lg shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">
                        {{ $editing ? 'Edit User' : 'Add New User' }}
                    </h3>
                    <form wire:submit.prevent="save">
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="name">Name</label>
                            <input wire:model="name" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="email">Email</label>
                            <input wire:model="email" type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="role">Role</label>
                            <select wire:model="role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="role">
                                <option value="user">User</option>
                                <option value="system_admin">System Admin</option>
                                <option value="developer">Developer</option>
                            </select>
                            @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="password">Password {{ $editing ? '(leave blank to keep current)' : '' }}</label>
                            <input wire:model="password" type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password">
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="password_confirmation">Confirm Password</label>
                            <input wire:model="password_confirmation" type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password_confirmation">
                            @error('password_confirmation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
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

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" id="delete-modal">
            <div class="p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Confirm User Deletion</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">This action cannot be undone. Please enter your password to confirm.</p>
                    <form wire:submit.prevent="delete">
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="deletePassword">Password</label>
                            <input wire:model="deletePassword" type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="deletePassword" required>
                            @error('deletePassword') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Delete User
                            </button>
                            <button type="button" wire:click="closeDeleteModal" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <style>
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
    </style>
</div>
