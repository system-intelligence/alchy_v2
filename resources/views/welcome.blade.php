<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Welcome to Alchy Smart Inventory</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#0b0f1a]">
        <div class="min-h-screen relative overflow-hidden">
            <!-- Animated Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-[#0b0f1a] via-[#1a1f2e] to-[#0b0f1a]">
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-red-500/30 rounded-full blur-3xl animate-pulse"></div>
                    <div class="absolute bottom-0 left-0 w-96 h-96 bg-primary-500/30 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-red-600/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
                </div>
            </div>

            <!-- Content -->
            <div class="relative z-10 min-h-screen flex flex-col items-center justify-center px-6 py-8">
                <!-- Logo -->
                <div class="mb-6 sm:mb-8 animate-fade-in">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-red-500 to-red-700 rounded-2xl flex items-center justify-center shadow-2xl shadow-red-500/50 p-3 transform hover:scale-105 transition-transform duration-300">
                        <img src="{{ asset('images/logos/alchy_logo.png') }}" alt="Alchy Logo" class="w-full h-full object-contain filter brightness-0 invert">
                    </div>
                </div>

                <!-- Main Heading -->
                <div class="text-center mb-8 sm:mb-10 max-w-5xl animate-fade-in-up">
                    <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black text-white mb-4 leading-tight">
                        <span class="bg-gradient-to-r from-red-400 via-red-500 to-red-600 bg-clip-text text-transparent">
                            Alchy Enterprise Inc.
                        </span>
                        <br>
                        <span class="text-gray-100">
                            Smart Inventory
                        </span>
                    </h1>
                    <p class="text-base sm:text-lg md:text-xl text-gray-400 font-medium max-w-3xl mx-auto leading-relaxed">
                        Track inventory with ease using Alchy's intelligent tracking, 
                        <span class="">monitor expenses</span>, and 
                        <span class="">control stock levels</span>
                    </p>
                </div>

                <!-- Features Grid -->
                <div class="max-w-5xl w-full mb-8 sm:mb-10 animate-fade-in-up" style="animation-delay: 0.2s;">
                    <h2 class="text-2xl sm:text-3xl font-bold text-white mb-6 text-center">
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="group bg-[#1a1f2e]/60 backdrop-blur border border-[#2a2f3e] rounded-xl p-5 hover:border-red-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-red-500/10">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-500/20 to-red-600/20 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-white mb-2">Intelligent Tracking</h3>
                        <p class="text-sm text-gray-400 leading-relaxed">Track inventory with ease using advanced intelligent tracking system</p>
                    </div>

                    <div class="group bg-[#1a1f2e]/60 backdrop-blur border border-[#2a2f3e] rounded-xl p-5 hover:border-red-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-red-500/10">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-500/20 to-red-600/20 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-white mb-2">Monitor Expenses</h3>
                        <p class="text-sm text-gray-400 leading-relaxed">Keep track of all expenses with comprehensive monitoring tools</p>
                    </div>

                    <div class="group bg-[#1a1f2e]/60 backdrop-blur border border-[#2a2f3e] rounded-xl p-5 hover:border-red-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-red-500/10">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-500/20 to-red-600/20 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-white mb-2">Control Stock Levels</h3>
                        <p class="text-sm text-gray-400 leading-relaxed">Maintain optimal stock levels with automated control systems</p>
                    </div>
                    </div>
                </div>

                <!-- CTA Button -->
                <div class="animate-fade-in-up" style="animation-delay: 0.4s;">
                    <a href="{{ route('login') }}" 
                       class="group relative inline-flex items-center gap-2 sm:gap-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold text-base sm:text-lg px-8 sm:px-10 py-3 sm:py-4 rounded-2xl shadow-2xl shadow-red-500/50 transition-all duration-300 hover:scale-105 hover:shadow-red-500/70">
                        <span>Get Started</span>
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>

                <!-- Footer -->
                <div class="absolute bottom-3 left-0 right-0 text-center text-gray-600 text-xs sm:text-sm">
                    <p>&copy; {{ date('Y') }} Alchy Smart Inventory. All rights reserved.</p>
                </div>
            </div>
        </div>

        <style>
            @keyframes fade-in {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }

            @keyframes fade-in-up {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fade-in {
                animation: fade-in 1s ease-out;
            }

            .animate-fade-in-up {
                animation: fade-in-up 1s ease-out;
                animation-fill-mode: both;
            }
        </style>
    </body>
</html>
