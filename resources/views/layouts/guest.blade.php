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
    <body class="font-sans text-gray-100 antialiased bg-[#0b0f1a]">
        <div class="min-h-screen flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-2xl p-2">
                        <img src="{{ asset('images/logos/alchy_logo.png') }}" alt="Alchy Smart Inventory Logo" class="w-14 h-12 object-contain">
                    </div>
                    <h1 class="text-3xl font-bold text-gray-100">Alchy Smart Inventory</h1>
                </div>

                <!-- Login Card -->
                <div class="bg-[#0d1829] rounded-2xl shadow-2xl border border-[#1B2537] p-8">
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
    </body>
</html>
