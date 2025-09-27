@extends('layouts.app')

@section('content')
<div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Developer Dashboard</h1>
                    <p class="text-blue-100 mt-1">Monitor system logs and manage users</p>
                </div>
                <div class="hidden md:block">
                    <x-heroicon-o-shield-check class="w-16 h-16 text-blue-200" />
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <x-heroicon-o-users class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\User::count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                        <x-heroicon-o-queue-list class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Inventory Items</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\Inventory::count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <x-heroicon-o-clock class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">History Logs</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\History::count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <x-heroicon-o-building-office class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Clients</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\Client::count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- System Logs -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent System Logs</h3>
                        <a href="{{ route('history') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                            View All →
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        @forelse(\App\Models\History::with('user')->latest()->take(10)->get() as $log)
                        <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-xs font-medium">{{ strtoupper(substr($log->user->name, 0, 1)) }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $log->user->name }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ ucfirst($log->action) }} {{ $log->model }} • {{ $log->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">No recent activity</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">System Health</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Database</span>
                            <span class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                Connected
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Storage</span>
                            <span class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                {{ number_format((disk_total_space('/') - disk_free_space('/')) / 1024 / 1024 / 1024, 2) }} GB used / {{ number_format(disk_total_space('/') / 1024 / 1024 / 1024, 2) }} GB total
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Active Sessions</span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ \App\Models\User::count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Total Transactions</span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ \App\Models\Expense::count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Low Stock Items</span>
                            <span class="text-sm text-red-600 dark:text-red-400">{{ \App\Models\Inventory::whereIn('status', ['critical', 'out_of_stock'])->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Management -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <livewire:developer.user-management />
            </div>
        </div>
    </div>
@endsection