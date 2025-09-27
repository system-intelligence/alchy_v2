<section>
    <header class="flex items-center space-x-2 mb-6">
        <x-heroicon-o-lock-closed class="w-6 h-6 text-green-600 dark:text-green-400" />
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                {{ __('Update Password') }}
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Ensure your account is using a long, random password to stay secure.') }}
            </p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <div class="relative mt-1">
                <x-heroicon-o-lock-closed class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="pl-10 block w-full" autocomplete="current-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <div class="relative mt-1">
                <x-heroicon-o-lock-closed class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                <x-text-input id="update_password_password" name="password" type="password" class="pl-10 block w-full" autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <div class="relative mt-1">
                <x-heroicon-o-lock-closed class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="pl-10 block w-full" autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
