<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Forgot Password</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                No problem. Just let us know your email address and we will email you a password reset link.
            </p>
        </div>


        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Email Address
            </label>
            <input id="email"
                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-colors"
                   type="email"
                   name="email"
                   :value="old('email')"
                   required
                   autofocus
                   autocomplete="email"
                   placeholder="Enter your email address" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Send Reset Link Button -->
        <button type="submit"
                class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
            Send Password Reset Link
        </button>

        <!-- Back to Login Link -->
        <div class="text-center">
            <a href="{{ route('login') }}"
               class="text-sm text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300 font-medium transition-colors">
                ← Back to Login
            </a>
        </div>

        <!-- Help Notice -->
        <div class="mt-6 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
            <div class="flex items-start space-x-3">
                <div class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-200">Need Help?</h3>
                    <p class="text-xs text-red-700 dark:text-red-300 mt-1">
                        If you don't receive the email within a few minutes, check your spam folder or contact support.
                    </p>
                </div>
            </div>
        </div>
    </form>
</x-guest-layout>
