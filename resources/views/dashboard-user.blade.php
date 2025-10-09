@extends('layouts.app')

@section('content')
<div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Welcome, {{ auth()->user()->name }}</h1>
                    <p class="text-purple-100 mt-1">Here is a quick overview of your recent activity</p>
                </div>
                <div class="hidden md:block">
                    @if(auth()->user()->avatar_url && !str_contains(auth()->user()->avatar_url, 'ui-avatars.com'))
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-16 h-16 rounded-full object-cover border-4 border-purple-300 shadow-lg">
                    @else
                        <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center border-4 border-purple-300 shadow-lg">
                            <span class="text-white text-2xl font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @if(!auth()->user()->isUser())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <x-heroicon-o-currency-dollar class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Expenses (All Clients)</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format(\App\Models\Expense::sum('total_cost'), 2) }}
                        </p>
                    </div>
                </div>
            </div>
            @endif
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                        <x-heroicon-o-building-office class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Clients</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\Client::count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <x-heroicon-o-queue-list class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Inventory Items</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\Inventory::count() }}</p>
                    </div>
                </div>
            </div>
            @if(!auth()->user()->isUser())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 dark:bg-red-900 rounded-lg">
                        <x-heroicon-o-clock class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Recent Releases</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\Expense::where('released_at', '>=', now()->subDays(7))->count() }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        @if(!auth()->user()->isUser())
        <!-- Expenses Cards -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Clients Expenses</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach(\App\Models\Client::with('expenses')->get()->map(function ($client) { $client->total_expenses = $client->expenses->sum('total_cost'); return $client; }) as $client)
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow hover:shadow-lg transition-shadow border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $client->name }}</h4>
                                    <p class="text-gray-600 dark:text-gray-400">Branch: {{ $client->branch }}</p>
                                </div>
                                <x-heroicon-o-building-office class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                            </div>
                            <div class="flex items-center justify-between mt-4">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Expenses</p>
                                    <p class="text-2xl font-bold text-green-600">${{ number_format($client->total_expenses, 2) }}</p>
                                </div>
                                <a href="{{ route('expenses') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                    View Expenses
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if(!auth()->user()->isUser())
        <!-- Recent Expenses -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Expenses</h3>
                    <a href="{{ route('expenses') }}" class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 text-sm font-medium">
                        View All →
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse(\App\Models\Expense::with(['client', 'inventory'])->latest()->take(5)->get() as $expense)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                    <x-heroicon-o-currency-dollar class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $expense->inventory->brand }} - {{ Str::limit($expense->inventory->description, 30) }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $expense->client->name }} • {{ $expense->released_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($expense->total_cost, 2) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $expense->quantity_used }} units</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">No recent expenses</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Inventory Items -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Inventory</h3>
                    <a href="{{ route('masterlist') }}" class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 text-sm font-medium">
                        View All →
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Brand</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach(\App\Models\Inventory::latest()->take(5)->get() as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $item->brand }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($item->description, 40) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($item->status == 'normal') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($item->status == 'critical') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection