<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
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
<body class="font-sans antialiased bg-[#101828] text-gray-100 text-xs sm:text-sm md:text-base">
    <div class="h-screen w-full flex overflow-hidden">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-[#101828] shadow-xl w-64 h-full fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-30 border-r border-[#1B2537]">
            <div class="flex flex-col h-full">
                <div class="flex items-center justify-center h-20 bg-gradient-to-r from-primary-500 to-primary-700 shadow-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-14 h-14 bg-white rounded-lg flex items-center justify-center shadow-md">
                            <img src="http://192.168.1.201:8000/images/logos/alchy_logo.png" alt="Alchy Logo" class="w-10 h-10 object-contain">
                        </div>
                        <div>
                            <h1 class="text-white text-lg font-bold tracking-wide">Alchy</h1>
                            <p class="text-primary-100 text-xs">Smart Inventory</p>
                        </div>
                    </div>
                </div>
                <nav class="flex-1 mt-6 px-3 pb-20 overflow-y-auto">
                    <div class="mb-4">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Navigation</p>
                        <div class="space-y-1">
                            <a href="{{ route('dashboard') }}" @class([
                                'flex items-center py-2 px-2 md:py-3 md:px-3 rounded-lg transition duration-200 text-gray-300 hover:text-primary-400 hover:bg-[#172033]',
                                'bg-[#172033] text-primary-400 border-r-2 border-primary-500' => request()->routeIs('dashboard'),
                            ])>
                                <x-heroicon-o-home class="w-4 h-4 md:w-5 md:h-5 mr-2 md:mr-3" />
                                <span class="text-xs md:text-sm">Dashboard</span>
                            </a>
                            <a href="{{ route('masterlist') }}" @class([
                                'flex items-center py-2 px-2 md:py-3 md:px-3 rounded-lg transition duration-200 text-gray-300 hover:text-primary-400 hover:bg-[#172033]',
                                'bg-[#172033] text-primary-400 border-r-2 border-primary-500' => request()->routeIs('masterlist'),
                            ])>
                                <x-heroicon-o-queue-list class="w-4 h-4 md:w-5 md:h-5 mr-2 md:mr-3" />
                                <span class="text-xs md:text-sm">Masterlist</span>
                            </a>
                            <a href="{{ route('history') }}" @class([
                                'flex items-center py-2 px-2 md:py-3 md:px-3 rounded-lg transition duration-200 text-gray-300 hover:text-primary-400 hover:bg-[#172033]',
                                'bg-[#172033] text-primary-400 border-r-2 border-primary-500' => request()->routeIs('history'),
                            ])>
                                <x-heroicon-o-clock class="w-4 h-4 md:w-5 md:h-5 mr-2 md:mr-3" />
                                <span class="text-xs md:text-sm">History</span>
                            </a>
                            <a href="{{ route('tools') }}" @class([
                                'flex items-center py-2 px-2 md:py-3 md:px-3 rounded-lg transition duration-200 text-gray-300 hover:text-primary-400 hover:bg-[#172033]',
                                'bg-[#172033] text-primary-400 border-r-2 border-primary-500' => request()->routeIs('tools'),
                            ])>
                                <x-heroicon-o-wrench-screwdriver class="w-4 h-4 md:w-5 md:h-5 mr-2 md:mr-3" />
                                <span class="text-xs md:text-sm">Tools</span>
                            </a>
                            @if(auth()->user()->isSystemAdmin())
                            <a href="{{ route('expenses') }}" @class([
                                'flex items-center py-2 px-2 md:py-3 md:px-3 rounded-lg transition duration-200 text-gray-300 hover:text-primary-400 hover:bg-[#172033]',
                                'bg-[#172033] text-primary-400 border-r-2 border-primary-500' => request()->routeIs('expenses'),
                            ])>
                                <x-heroicon-o-currency-dollar class="w-4 h-4 md:w-5 md:h-5 mr-2 md:mr-3" />
                                <span class="text-xs md:text-sm">Expenses</span>
                            </a>
                            @endif
                            @if(auth()->user()->isDeveloper())
                            <a href="{{ route('developer.user-management') }}" @class([
                                'flex items-center py-2 px-2 md:py-3 md:px-3 rounded-lg transition duration-200 text-gray-300 hover:text-primary-400 hover:bg-[#172033]',
                                'bg-[#172033] text-primary-400 border-r-2 border-primary-500' => request()->routeIs('developer.user-management'),
                            ])>
                                <x-heroicon-o-users class="w-4 h-4 md:w-5 md:h-5 mr-2 md:mr-3" />
                                <span class="text-xs md:text-sm">User Management</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="border-t border-[#1B2537] pt-6">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Account</p>
                        <div class="space-y-1">
                            <a href="{{ route('profile.edit') }}" @class([
                                'flex items-center py-3 px-3 rounded-lg transition duration-200 text-gray-300 hover:text-primary-400 hover:bg-[#172033]',
                                'bg-[#172033] text-primary-400 border-r-2 border-primary-500' => request()->routeIs('profile.edit'),
                            ])>
                                <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"></path>
                                </svg>
                                Profile
                            </a>
                            <button onclick="toggleAppFullscreen()" class="w-full flex items-center py-3 px-3 rounded-lg transition duration-200 text-gray-300 hover:text-primary-400 hover:bg-[#172033]">
                                <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Full Screen
                            </button>
                        </div>
                    </div>
                </nav>
                <div class="px-4 pb-6 mt-auto">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center space-x-2 px-4 py-3 bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white rounded-xl shadow-md transition-colors">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                            <span class="text-sm font-semibold">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col bg-[#0d1829] lg:ml-64 h-full overflow-hidden">
            <!-- Header -->
            <header class="bg-[#0d1829] shadow-sm border-b border-[#1B2537]">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-300 hover:text-gray-100 hover:bg-[#172033] transition-colors">
                            <x-heroicon-o-bars-3 class="w-6 h-6" />
                        </button>
                        <div>
                            <h2 class="text-sm md:text-lg font-semibold text-gray-100">{{ $title ?? 'Dashboard' }}</h2>
                            <p class="text-[10px] md:text-sm text-gray-400">{{ $subtitle ?? 'Welcome back, ' . auth()->user()->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if(auth()->user()->hasAvatarBlob())
                            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                        @else
                            <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        <div class="hidden md:block">
                            <p class="text-sm font-medium text-gray-100">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-400 capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-3 md:p-6 bg-[#101828] overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
    <livewire:chat-widget />
    <livewire:notification-toast />
    <script>
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
        });

        // Fullscreen toggle function
        function toggleAppFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }

        // Update button based on fullscreen state
        function updateFullscreenButton() {
            var btn = document.querySelector('button[onclick="toggleAppFullscreen()"]');
            if (!btn) return;
            
            if (document.fullscreenElement) {
                btn.innerHTML = '<svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25" /></svg> Exit Full Screen';
            } else {
                btn.innerHTML = '<svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg> Full Screen';
            }
        }

        // Listen for fullscreen changes
        document.addEventListener('fullscreenchange', updateFullscreenButton);

        // Initialize on load
        document.addEventListener('DOMContentLoaded', updateFullscreenButton);
        if (window.Livewire) {
            Livewire.hook('message.processed', updateFullscreenButton);
        }

        // Auto-join presence channel for all authenticated users
        document.addEventListener('livewire:initialized', () => {
            if (window.Echo) {
                console.log('[Presence] Auto-joining online presence channel...');
                window.Echo.join('online')
                    .here((users) => {
                        console.log('[Presence] Online users:', users.length);
                    })
                    .joining((user) => {
                        console.log('[Presence] User came online:', user.name);
                    })
                    .leaving((user) => {
                        console.log('[Presence] User went offline:', user.name);
                    })
                    .error((error) => {
                        console.error('[Presence] Channel error:', error);
                    });
            } else {
                console.warn('[Presence] Echo not available. Make sure to run: npm run dev');
            }
        });

        // History page listeners
        if (window.location.pathname === '/history') {
            document.addEventListener('DOMContentLoaded', function() {
                console.log('[History] 🚀 Page loaded, setting up listeners...');
                
                // Listen for Livewire refresh confirmation
                window.addEventListener('history-refreshed', (event) => {
                    console.log('[History] ✅ History refreshed!', event.detail);
                });
                
                if (window.Echo) {
                    console.log('[History] 🔌 Echo available, setting up channel listener...');
                    
                    const channel = window.Echo.channel('history-updates');
                    console.log('[History] 📡 Subscribing to channel: history-updates');
                    
                    channel.listen('.history.created', (data) => {
                            console.log('[History] 🔔 NEW HISTORY EVENT RECEIVED!', data);
                            console.log('[History] 📊 Event data:', JSON.stringify(data, null, 2));
                            
                            // Dispatch to Livewire component
                            console.log('[History] 🔄 Dispatching refreshHistory to Livewire...');
                            Livewire.dispatch('refreshHistory');
                            
                            // Show toast notification
                            console.log('[History] 🍞 Showing toast notification...');
                            window.dispatchEvent(new CustomEvent('new-message-notification', {
                                detail: {
                                    message: 'New history entry: ' + (data.action || 'Update'),
                                    type: 'info',
                                    duration: 3000
                                }
                            }));
                        })
                        .subscribed(() => {
                            console.log('[History] ✅ SUCCESSFULLY SUBSCRIBED to history-updates channel!');
                            console.log('[History] 🎧 Listening for .history.created events...');
                        })
                        .error((error) => {
                            console.error('[History] ❌ Echo channel error:', error);
                        });
                        
                    console.log('[History] ✓ Echo listener setup complete');
                } else {
                    console.warn('[History] ⚠️ Echo is NOT available! Pusher may not be loaded.');
                    console.log('[History] 🔍 Check if window.Echo exists:', typeof window.Echo);
                    console.log('[History] 🔍 Check if window.Pusher exists:', typeof window.Pusher);
                }
            });
        }
    </script>
</body>
</html>
