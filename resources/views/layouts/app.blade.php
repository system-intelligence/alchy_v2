<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Alchy Smart Inventory</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-white dark:bg-gray-800 shadow-xl w-64 min-h-screen fixed lg:static lg:translate-x-0 transform -translate-x-full transition-transform duration-300 ease-in-out z-30 border-r border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-center h-16 bg-gradient-to-r from-blue-600 to-blue-800 shadow-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-md">
                        <x-heroicon-o-cube class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-white text-lg font-bold tracking-wide">Alchy</h1>
                        <p class="text-blue-100 text-xs">Smart Inventory</p>
                    </div>
                </div>
            </div>
            <nav class="mt-8 px-4">
                <div class="mb-6">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Navigation</p>
                    <div class="space-y-1">
                        <a href="{{ route('dashboard') }}" class="flex items-center py-3 px-3 rounded-lg transition duration-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 {{ request()->routeIs('dashboard') ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 dark:text-gray-300' }}">
                            <x-heroicon-o-home class="w-5 h-5 mr-3" />
                            Dashboard
                        </a>
                        <a href="{{ route('masterlist') }}" class="flex items-center py-3 px-3 rounded-lg transition duration-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 {{ request()->routeIs('masterlist') ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 dark:text-gray-300' }}">
                            <x-heroicon-o-queue-list class="w-5 h-5 mr-3" />
                            Masterlist
                        </a>
                        <a href="{{ route('history') }}" class="flex items-center py-3 px-3 rounded-lg transition duration-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 {{ request()->routeIs('history') ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 dark:text-gray-300' }}">
                            <x-heroicon-o-clock class="w-5 h-5 mr-3" />
                            History
                        </a>
                        <a href="{{ route('expenses') }}" class="flex items-center py-3 px-3 rounded-lg transition duration-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 {{ request()->routeIs('expenses') ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 dark:text-gray-300' }}">
                            <x-heroicon-o-currency-dollar class="w-5 h-5 mr-3" />
                            Expenses
                        </a>
                        @if(auth()->user()->isDeveloper())
                        <a href="{{ route('developer.user-management') }}" class="flex items-center py-3 px-3 rounded-lg transition duration-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 {{ request()->routeIs('developer.user-management') ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 dark:text-gray-300' }}">
                            <x-heroicon-o-users class="w-5 h-5 mr-3" />
                            User Management
                        </a>
                        @endif
                    </div>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Account</p>
                    <div class="space-y-1">
                        <a href="{{ route('profile.edit') }}" class="flex items-center py-3 px-3 rounded-lg transition duration-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 {{ request()->routeIs('profile.edit') ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 dark:text-gray-300' }}">
                            <x-heroicon-o-user class="w-5 h-5 mr-3" />
                            Profile
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <x-heroicon-o-bars-3 class="w-6 h-6" />
                        </button>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">{{ $title ?? 'Dashboard' }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $subtitle ?? 'Welcome back, ' . auth()->user()->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            @if(auth()->user()->avatar_url && !str_contains(auth()->user()->avatar_url, 'ui-avatars.com'))
                                <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                </div>
                            @endif
                            <div class="hidden md:block">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                                <span class="hidden sm:inline">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-6 bg-gray-50 dark:bg-gray-900">
                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
    <script>
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
        });
    </script>
</body>
</html>
