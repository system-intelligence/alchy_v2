<div>
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

    @if(auth()->user()->isDeveloper())
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Developer Dashboard</h2>
            <p class="text-gray-600 dark:text-gray-400">Monitor system logs and manage users</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg border-l-4 border-blue-500">
                <div class="flex items-center mb-4">
                    <x-heroicon-o-document-text class="w-6 h-6 text-blue-500 mr-2" />
                    <h3 class="text-xl font-semibold">System Logs</h3>
                </div>
                <ul class="space-y-2 max-h-64 overflow-y-auto">
                    @forelse($data['logs'] as $log)
                        <li class="text-sm bg-gray-50 dark:bg-gray-700 p-2 rounded">{{ $log->created_at->format('Y-m-d H:i') }} - {{ $log->action }} on {{ $log->model }}</li>
                    @empty
                        <li class="text-sm text-gray-500">No recent logs</li>
                    @endforelse
                </ul>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg border-l-4 border-green-500">
                <div class="flex items-center mb-4">
                    <x-heroicon-o-users class="w-6 h-6 text-green-500 mr-2" />
                    <h3 class="text-xl font-semibold">All Users</h3>
                </div>
                <ul class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach($data['users'] as $user)
                        <li class="text-sm bg-gray-50 dark:bg-gray-700 p-2 rounded flex justify-between">
                            <span>{{ $user->name }}</span>
                            <span class="text-xs bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200 px-2 py-1 rounded">{{ $user->role }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @elseif(auth()->user()->isSystemAdmin())
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">System Admin Dashboard</h2>
            <p class="text-gray-600 dark:text-gray-400">Manage inventory and monitor system status</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg text-center border-l-4 border-blue-500">
                <x-heroicon-o-queue-list class="w-12 h-12 text-blue-500 mx-auto mb-4" />
                <h3 class="text-3xl font-bold text-blue-600">{{ $data['inventory_count'] }}</h3>
                <p class="text-gray-600 dark:text-gray-400">Total Inventory Items</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg text-center border-l-4 border-red-500">
                <x-heroicon-o-exclamation-triangle class="w-12 h-12 text-red-500 mx-auto mb-4" />
                <h3 class="text-3xl font-bold text-red-600">{{ $data['low_stock'] }}</h3>
                <p class="text-gray-600 dark:text-gray-400">Low Stock Items</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg text-center border-l-4 border-green-500">
                <x-heroicon-o-shopping-bag class="w-12 h-12 text-green-500 mx-auto mb-4" />
                <h3 class="text-3xl font-bold text-green-600">0</h3>
                <p class="text-gray-600 dark:text-gray-400">Pending Orders</p>
            </div>
        </div>
    @else
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">User Dashboard</h2>
            <p class="text-gray-600 dark:text-gray-400">View expenses and system information</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg border-l-4 border-purple-500">
            <div class="flex items-center mb-4">
                <x-heroicon-o-currency-dollar class="w-6 h-6 text-purple-500 mr-2" />
                <h3 class="text-xl font-semibold">Recent Expenses</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-400">No recent expenses to display.</p>
        </div>
    @endif
</div>
