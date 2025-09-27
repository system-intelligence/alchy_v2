@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Profile Overview -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Profile Overview</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</h4>
                <p class="text-lg text-gray-900 dark:text-white">{{ $user->name }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</h4>
                <p class="text-lg text-gray-900 dark:text-white">{{ $user->email }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Role</h4>
                <p class="text-lg text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $user->role) }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Member Since</h4>
                <p class="text-lg text-gray-900 dark:text-white">{{ $user->created_at->format('M d, Y') }}</p>
            </div>
        </div>
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                <div class="flex items-center">
                    <x-heroicon-o-cube class="w-8 h-8 text-blue-600 dark:text-blue-400 mr-3" />
                    <div>
                        <p class="text-sm text-blue-600 dark:text-blue-400">Items Managed</p>
                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ \App\Models\Inventory::count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                <div class="flex items-center">
                    <x-heroicon-o-clock class="w-8 h-8 text-green-600 dark:text-green-400 mr-3" />
                    <div>
                        <p class="text-sm text-green-600 dark:text-green-400">Activities Logged</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ \App\Models\History::where('user_id', $user->id)->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                <div class="flex items-center">
                    <x-heroicon-o-envelope class="w-8 h-8 text-purple-600 dark:text-purple-400 mr-3" />
                    <div>
                        <p class="text-sm text-purple-600 dark:text-purple-400">Expenses Tracked</p>
                        <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ \App\Models\Expense::count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Avatar Upload -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <livewire:profile.avatar-upload />
    </div>

    <!-- Update Profile Information and Password -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Update Profile Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <!-- Update Password -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>

    @if(auth()->user()->isDeveloper())
    <!-- Delete Account -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="max-w-xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
    @endif
</div>
@endsection
