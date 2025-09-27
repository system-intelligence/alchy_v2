<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Email Address
            </label>
            <div class="relative">
                <x-heroicon-o-envelope class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input id="email"
                       class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-colors"
                       type="email"
                       name="email"
                       :value="old('email')"
                       required
                       autofocus
                       autocomplete="username"
                       placeholder="Enter your email" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Password
            </label>
            <div class="relative">
                <x-heroicon-o-lock-closed class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input id="password"
                       class="w-full pl-10 pr-12 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-colors"
                       type="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="Enter your password" />
                <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer z-10">
                    <x-heroicon-o-eye id="eye-icon" class="w-5 h-5" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="flex items-center">
                <input id="remember_me"
                       type="checkbox"
                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                       name="remember">
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300 font-medium transition-colors"
                   href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <button type="submit"
                class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
            Sign In
        </button>

        <!-- Demo Credentials -->
        <div class="mt-6 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
            <h3 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-2">Demo Accounts</h3>
            <div class="space-y-1 text-xs text-red-700 dark:text-red-300">
                <div><strong>Developer:</strong> developer@example.com / password</div>
                <div><strong>System Admin:</strong> admin@example.com / password</div>
                <div><strong>User:</strong> test@example.com / password</div>
            </div>
        </div>
    </form>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3c-1.398 0-2.735.324-3.947.93L3.707 2.293zM5 10a5 5 0 018.258-3.162L7.162 12.338A5 5 0 015 10z" clip-rule="evenodd"/><path d="M12.338 7.162A5 5 0 006.838 12.338L12.338 7.162z"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>';
            }
        }
    </script>
</x-guest-layout>
