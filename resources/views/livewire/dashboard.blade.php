<div class="space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">Dashboard</h1>
        <p class="text-gray-400">Welcome back, {{ auth()->user()->name }}</p>
    </div>

    @if(auth()->user()->isDeveloper())
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-white">Developer Dashboard</h2>
            <p class="text-gray-400 mt-1">Monitor system logs and manage users</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- System Logs Card -->
            <div class="bg-[#0d1829] border border-[#1B2537] p-6 rounded-2xl shadow-xl hover:shadow-2xl transition-shadow duration-300">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500/20 to-red-600/20 rounded-lg flex items-center justify-center mr-3">
                        <x-heroicon-o-document-text class="w-5 h-5 text-red-400" />
                    </div>
                    <h3 class="text-xl font-semibold text-white">System Logs</h3>
                </div>
                <ul class="space-y-2 max-h-64 overflow-y-auto">
                    @forelse($data['logs'] as $log)
                        <li class="text-sm bg-[#121f33] border border-[#1B2537] p-3 rounded-lg text-gray-300 hover:bg-[#172033] transition-colors">
                            <span class="text-gray-400">{{ $log->created_at->format('Y-m-d H:i') }}</span> - 
                            <span class="text-white">{{ $log->action }}</span> on 
                            <span class="text-primary-400">{{ $log->model }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 text-center py-8">No recent logs</li>
                    @endforelse
                </ul>
            </div>

            <!-- All Users Card -->
            <div class="bg-[#0d1829] border border-[#1B2537] p-6 rounded-2xl shadow-xl hover:shadow-2xl transition-shadow duration-300">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 rounded-lg flex items-center justify-center mr-3">
                        <x-heroicon-o-users class="w-5 h-5 text-emerald-400" />
                    </div>
                    <h3 class="text-xl font-semibold text-white">All Users</h3>
                </div>
                <ul class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach($data['users'] as $user)
                        <li class="text-sm bg-[#121f33] border border-[#1B2537] p-3 rounded-lg flex justify-between items-center hover:bg-[#172033] transition-colors">
                            <span class="text-gray-300">{{ $user->name }}</span>
                            <span class="text-xs bg-primary-500/20 border border-primary-500/40 text-primary-200 px-3 py-1 rounded-full font-medium uppercase tracking-wide">{{ $user->role }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @elseif(auth()->user()->isSystemAdmin())
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-white">System Admin Dashboard</h2>
            <p class="text-gray-400 mt-1">Manage inventory and monitor system status</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Inventory Card -->
            <div class="bg-gradient-to-br from-[#0d1829] to-[#121f33] border border-[#1B2537] p-6 rounded-2xl shadow-xl hover:shadow-2xl hover:border-primary-500/50 transition-all duration-300 group">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary-500/20 to-primary-600/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <x-heroicon-o-queue-list class="w-8 h-8 text-primary-400" />
                    </div>
                </div>
                <h3 class="text-4xl font-bold text-white text-center mb-2">{{ $data['inventory_count'] }}</h3>
                <p class="text-gray-400 text-center text-sm">Total Inventory Items</p>
            </div>

            <!-- Low Stock Card -->
            <div class="bg-gradient-to-br from-[#0d1829] to-[#121f33] border border-[#1B2537] p-6 rounded-2xl shadow-xl hover:shadow-2xl hover:border-red-500/50 transition-all duration-300 group">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-500/20 to-red-600/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-red-400" />
                    </div>
                </div>
                <h3 class="text-4xl font-bold text-white text-center mb-2">{{ $data['low_stock'] }}</h3>
                <p class="text-gray-400 text-center text-sm">Low Stock Items</p>
            </div>

            <!-- Pending Orders Card -->
            <div class="bg-gradient-to-br from-[#0d1829] to-[#121f33] border border-[#1B2537] p-6 rounded-2xl shadow-xl hover:shadow-2xl hover:border-emerald-500/50 transition-all duration-300 group">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <x-heroicon-o-shopping-bag class="w-8 h-8 text-emerald-400" />
                    </div>
                </div>
                <h3 class="text-4xl font-bold text-white text-center mb-2">0</h3>
                <p class="text-gray-400 text-center text-sm">Pending Orders</p>
            </div>
        </div>
    @else
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-white">User Dashboard</h2>
            <p class="text-gray-400 mt-1">View expenses and system information</p>
        </div>
        <div class="bg-gradient-to-br from-[#0d1829] to-[#121f33] border border-[#1B2537] p-6 rounded-2xl shadow-xl hover:shadow-2xl transition-shadow duration-300">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500/20 to-red-600/20 rounded-lg flex items-center justify-center mr-3">
                    <x-heroicon-o-currency-dollar class="w-5 h-5 text-red-400" />
                </div>
                <h3 class="text-xl font-semibold text-white">Recent Expenses</h3>
            </div>
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-[#172033] border border-[#1B2537] rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-inbox class="w-8 h-8 text-gray-500" />
                </div>
                <p class="text-gray-400">No recent expenses to display.</p>
            </div>
        </div>
    @endif
</div>
