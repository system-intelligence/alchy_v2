<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Alchy Smart Inventory</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-100 antialiased bg-[#101828]">
        <div class="min-h-screen flex">
            <!-- Left Side - Branding -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary-500 via-primary-600 to-primary-800 relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 left-0 w-full h-full bg-white/5 transform rotate-12 translate-x-1/4 -translate-y-1/4"></div>
                    <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full transform translate-x-1/4 translate-y-1/4"></div>
                </div>

                <!-- Content -->
                <div class="relative z-10 flex flex-col justify-center items-center w-full p-12 text-white">
                    <!-- Logo -->
                    <div class="mb-8">
                        <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center shadow-2xl p-2">
                            <img src="{{ asset('images/logos/alchy_logo.png') }}" alt="Alchy Smart Inventory Logo" class="w-16 h-10 object-contain">
                        </div>
                    </div>

                    <!-- Title -->
                    <h1 class="text-4xl font-bold mb-4 text-center">
                        Alchy Smart Inventory
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-xl text-primary-100 text-center mb-8 max-w-md">
                        Streamline your inventory management with intelligent tracking and real-time insights.
                    </p>

                    <!-- Features -->
                    <div class="space-y-4 max-w-sm">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-primary-50">Real-time inventory tracking</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-primary-50">Advanced expense management</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-primary-50">Comprehensive reporting</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
                <div class="w-full max-w-md">
                    <!-- Mobile Logo (shown only on mobile) -->
                    <div class="lg:hidden text-center mb-8">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg p-1">
                            <img src="{{ asset('images/logos/alchy_logo.png') }}" alt="Alchy Smart Inventory Logo" class="w-16 h-10 object-contain">
                        </div>
                        <h1 class="text-2xl font-bold text-gray-100">Alchy Smart Inventory</h1>
                    </div>

                    <!-- Login Card -->
                    <div class="bg-[#0d1829] rounded-2xl shadow-xl border border-[#1B2537] p-8">
                        @if(request()->routeIs('login'))
                            <div class="text-center mb-8">
                                <h2 class="text-2xl font-bold text-gray-100">Welcome Back</h2>
                                <p class="text-gray-400 mt-2">Sign in to your account</p>
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
