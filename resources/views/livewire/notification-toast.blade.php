<div class="fixed top-4 right-4 z-50 space-y-2">
    @foreach($notifications as $notification)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-full"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-full"
            class="max-w-sm w-full shadow-2xl rounded-xl border-2 p-4 flex items-start space-x-3
            @if($notification['type'] === 'success')
                bg-green-50 dark:bg-green-900/20 border-green-400 dark:border-green-600
            @elseif($notification['type'] === 'error')
                bg-red-50 dark:bg-red-900/20 border-red-400 dark:border-red-600
            @elseif($notification['type'] === 'warning')
                bg-yellow-50 dark:bg-yellow-900/20 border-yellow-400 dark:border-yellow-600
            @else
                bg-blue-50 dark:bg-blue-900/20 border-blue-400 dark:border-blue-600
            @endif"
            x-init="
                setTimeout(() => {
                    show = false;
                    setTimeout(() => $wire.call('removeNotification', '{{ $notification['id'] }}'), 300);
                }, {{ $notification['duration'] }});
            "
        >
            <!-- Icon -->
            <div class="flex-shrink-0">
                @if($notification['type'] === 'success')
                    <x-heroicon-o-check-circle class="w-6 h-6 text-green-500" />
                @elseif($notification['type'] === 'error')
                    <x-heroicon-o-exclamation-circle class="w-6 h-6 text-red-500" />
                @elseif($notification['type'] === 'warning')
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-yellow-500" />
                @else
                    <x-heroicon-o-information-circle class="w-6 h-6 text-blue-500" />
                @endif
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold
                    @if($notification['type'] === 'success')
                        text-green-800 dark:text-green-200
                    @elseif($notification['type'] === 'error')
                        text-red-800 dark:text-red-200
                    @elseif($notification['type'] === 'warning')
                        text-yellow-800 dark:text-yellow-200
                    @else
                        text-blue-800 dark:text-blue-200
                    @endif">
                    {{ $notification['message'] }}
                </p>
            </div>

            <!-- Close button -->
            <button
                @click="show = false; setTimeout(() => $wire.call('removeNotification', '{{ $notification['id'] }}'), 300);"
                class="flex-shrink-0 ml-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
            >
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>
        </div>
    @endforeach
</div>

<script>
document.addEventListener('livewire:loaded', () => {
    // Handle browser notifications
    $wire.on('triggerBrowserNotification', (message, senderName) => {
        showBrowserNotification(message, senderName);
    });

    function showBrowserNotification(message, senderName) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const notification = new Notification(`New message from ${senderName}`, {
                body: message.length > 50 ? message.substring(0, 50) + '...' : message,
                icon: '/images/logos/alchy_logo.png',
                badge: '/images/logos/alchy_logo.png',
                tag: 'chat-message',
                requireInteraction: false,
                silent: false
            });

            setTimeout(() => {
                notification.close();
            }, 5000);

            notification.onclick = function() {
                window.focus();
                notification.close();
            };
        }
    }
});
</script>
