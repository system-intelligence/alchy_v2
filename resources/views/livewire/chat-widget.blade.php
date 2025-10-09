<div class="fixed bottom-4 right-4 z-50"
     x-data="{
        toast: null,
        toastType: 'info',
        subscribed: false,
        convName: null,
        convSub: null,
        showToast(msg, type = 'info', ms = 3000) {
            this.toast = msg;
            this.toastType = type;
            setTimeout(() => this.toast = null, ms);
        },
        notifyBrowser(detail) {
            if (!('Notification' in window)) return;
            const title = detail?.senderName || 'New message';
            const body = detail?.message || '';
            if (Notification.permission === 'granted') {
                new Notification(title, { body });
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(p => {
                    if (p === 'granted') new Notification(title, { body });
                });
            }
        },
        setupEcho(userId) {
            if (this.subscribed || !window.Echo || !userId) return;
            try {
                const onUserEvt = (e) => {
                    const m = e?.message ?? null;
                    if (!m) return;
                    $wire.handleIncoming(m);
                };

                window.Echo.private(`App.Models.User.${userId}`)
                    .listen('MessageSent', onUserEvt)
                    .listen('.MessageSent', onUserEvt)
                    .listen('App\\Events\\MessageSent', onUserEvt)
                    .listen('.App\\Events\\MessageSent', onUserEvt);
                this.subscribed = true;
            } catch (err) {
                console && console.warn && console.warn('Echo subscribe error', err);
            }
        },
        setupConversation(otherUserId) {
            if (!window.Echo || !otherUserId) return;
            const me = {{ auth()->id() }};
            const a = Math.min(me, otherUserId);
            const b = Math.max(me, otherUserId);
            const name = `chat.${a}.${b}`;
            if (this.convName === name) return;

            try {
                // Leave previous conversation channel if any
                if (this.convSub && this.convName) {
                    window.Echo.leave(this.convName);
                    this.convSub = null;
                }
                this.convName = name;
                const onConvEvt = (e) => {
                    const m = e?.message ?? null;
                    if (!m) return;
                    // Always refresh the conversation; Livewire will re-render and scroll
                    $wire.loadMessages();
                    window.dispatchEvent(new CustomEvent('messagesLoaded'));
                    window.dispatchEvent(new CustomEvent('messages-updated'));
                };

                this.convSub = window.Echo.private(name)
                    .listen('MessageSent', onConvEvt)
                    .listen('.MessageSent', onConvEvt)
                    .listen('App\\Events\\MessageSent', onConvEvt)
                    .listen('.App\\Events\\MessageSent', onConvEvt);
            } catch (err) {
                console && console.warn && console.warn('Echo conv subscribe error', err);
            }
        }
     }"
     x-init="setupEcho({{ auth()->id() }})"
     x-effect="
        if ($wire.selectedUser) { setupConversation($wire.selectedUser.id) }
        if (!$wire.isOpen && convName) {
            if (window.Echo) { window.Echo.leave(convName) }
            convName = null; convSub = null;
        }
     "
     @new-message-notification.window="showToast($event.detail.message, 'info', 4000)"
     @message-sent-notification.window="showToast($event.detail.message, 'success', 2000)"
     @browserNotification.window="notifyBrowser($event.detail)"
     @userSelected.window="setupConversation($event.detail)">
    @if($isOpen)
        <div class="bg-white border border-gray-300 rounded-lg shadow-lg w-[32rem] h-[600px] flex">
            <!-- Sidebar -->
            <div class="w-1/3 border-r border-gray-200 flex flex-col">
                <div class="p-4 border-b border-gray-200">
                    <input type="text" wire:model.live="search" placeholder="Search users..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div class="flex-1 overflow-y-auto">
                    @foreach($users as $user)
                        <div wire:click="selectUser({{ $user->id }})" class="p-4 hover:bg-gray-50 cursor-pointer border-b border-gray-100 {{ $selectedUser && $selectedUser->id == $user->id ? 'bg-blue-50' : '' }}">
                            <div class="flex items-center space-x-3">
                                @if($user->avatar_url && !str_contains($user->avatar_url, 'ui-avatars.com'))
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-gray-200">
                                @else
                                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center border-2 border-gray-200">
                                        <span class="text-white text-sm font-medium">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $user->role) }}</p>
                                </div>
                                @php($count = $unreadCounts[$user->id] ?? 0)
                                @if($count > 0 && (! $selectedUser || $selectedUser->id != $user->id))
                                    <span class="ml-auto inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-red-500 text-white text-xs font-semibold">
                                        {{ $count }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 flex flex-col">
                <div class="bg-blue-500 text-white p-4 rounded-tr-lg flex justify-between items-center">
                    <span class="font-semibold">
                        @if($selectedUser)
                            {{ $selectedUser->name }}
                        @else
                            Select a user to chat
                        @endif
                    </span>
                    <button wire:click="toggleChat" class="text-white hover:text-gray-200">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>

                @if($selectedUser)
                    <div
                        class="flex-1 p-4 overflow-y-auto"
                        id="messages-container"
                        wire:loading.class="opacity-50"
                        x-ref="mc"
                        x-init="$nextTick(() => { $refs.mc.scrollTop = $refs.mc.scrollHeight })"
                        @messages-updated.window="$nextTick(() => { $refs.mc && ($refs.mc.scrollTop = $refs.mc.scrollHeight) })"
                        @messagesLoaded.window="$nextTick(() => { $refs.mc && ($refs.mc.scrollTop = $refs.mc.scrollHeight) })"
                        @messageReceived.window="$nextTick(() => { $refs.mc && ($refs.mc.scrollTop = $refs.mc.scrollHeight) })"
                    >
                        @foreach($messages as $message)
                            <div class="mb-2 {{ $message->user_id == auth()->id() ? 'text-right' : 'text-left' }}">
                                <div class="inline-block p-3 rounded-lg max-w-xs {{ $message->user_id == auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }}">
                                    <div class="text-xs opacity-75 mb-1">{{ $message->user->name }}</div>
                                    <div class="text-sm">{{ $message->message }}</div>
                                    <div class="text-xs opacity-75 mt-1">{{ $message->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
</div>
                    <div class="p-4 border-t">
                        <form wire:submit.prevent="sendMessage" class="flex">
                            <input type="text" wire:model="newMessage" placeholder="Type a message..." class="flex-1 border border-gray-300 rounded-l px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-r hover:bg-blue-600 font-medium">Send</button>
                        </form>
                    </div>
                @else
                    <div class="flex-1 flex items-center justify-center text-gray-500">
                        <div class="text-center">
                            <x-heroicon-o-chat-bubble-left-right class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                            <p class="px-4 opacity-50">Select a user from the sidebar to start chatting</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        @php($__totalUnread = array_sum($unreadCounts ?? []))
        <button wire:click="toggleChat" class="relative bg-blue-500 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg hover:bg-blue-600">
            <x-heroicon-o-chat-bubble-left-right class="w-7 h-7" />
            @if($__totalUnread > 0)
                <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-red-500 text-white text-xs font-semibold shadow">
                    {{ $__totalUnread }}
                </span>
            @endif
        </button>
    @endif

    <!-- Toast (minimal, no external deps) -->
    <div x-cloak x-show="toast" class="fixed top-4 right-4 z-[9999]">
        <div class="rounded-xl px-4 py-3 shadow-lg border bg-white"
             :class="toastType==='success' ? 'border-green-200' : (toastType==='info' ? 'border-blue-200' : 'border-gray-200')">
            <div class="flex items-center space-x-2">
                <span class="text-sm font-medium" x-text="toast"></span>
                <button class="ml-2 text-gray-400 hover:text-gray-600" @click="toast=null">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('livewire:loaded', () => {
    // Minimal, reliable auto-scroll with simple fallbacks
    const scrollToBottom = () => {
        const c = document.getElementById('messages-container');
        if (!c) return;
        c.scrollTop = c.scrollHeight;
        // light fallback
        setTimeout(() => { if (c) c.scrollTop = c.scrollHeight; }, 80);
    };

    // 1) Scroll after any Livewire DOM patch
    document.addEventListener('livewire:updated', scrollToBottom);

    // 2) Scroll when browser events are dispatched by Livewire
        window.addEventListener('messagesLoaded', scrollToBottom);
        window.addEventListener('messages-updated', scrollToBottom);
        window.addEventListener('messageReceived', scrollToBottom);


    // 4) Initial scroll
    scrollToBottom();
});
</script>
