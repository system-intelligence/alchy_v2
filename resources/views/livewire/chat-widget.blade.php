<div class="fixed bottom-4 right-4 z-50"
     x-data="{
        toasts: [],
        maxToasts: 5,
        nextId: 1,
        subscribed: false,
        convName: null,
        convSub: null,
        groupSub: null,
        groupChannel: null,
        userChannel: null,
        showToast(msg, type = 'info', ms = 5000) {
            console.log('[Toast] Showing notification:', msg, 'Type:', type, 'Duration:', ms);
            const id = this.nextId++;
            const toast = { id, message: msg, type, visible: false };
            
            // Add to beginning of array (top of stack)
            this.toasts.unshift(toast);
            
            // Remove oldest if exceeds max
            if (this.toasts.length > this.maxToasts) {
                this.toasts.pop();
            }
            
            // Trigger animation on next tick
            this.$nextTick(() => {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) {
                    this.toasts[index].visible = true;
                }
            });
            
            // Auto-remove after duration
            setTimeout(() => {
                console.log('[Toast] Hiding notification:', id);
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) {
                    this.toasts[index].visible = false;
                    // Remove from array after animation completes
                    setTimeout(() => {
                        const idx = this.toasts.findIndex(t => t.id === id);
                        if (idx !== -1) {
                            this.toasts.splice(idx, 1);
                        }
                    }, 400);
                }
            }, ms);
        },
        removeToast(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index !== -1) {
                this.toasts[index].visible = false;
                // Remove from array after animation completes
                setTimeout(() => {
                    const idx = this.toasts.findIndex(t => t.id === id);
                    if (idx !== -1) {
                        this.toasts.splice(idx, 1);
                    }
                }, 400);
            }
        },
        notifyBrowser(detail) {
            if (!('Notification' in window)) return;
            const title = detail?.senderName || 'New message';
            const body = detail?.message || '';
            if (Notification.permission === 'granted') {
                new Notification(title, { body, icon: '/images/logo.png' });
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(p => {
                    if (p === 'granted') new Notification(title, { body, icon: '/images/logo.png' });
                });
            }
        },
        setupEcho(userId) {
            if (this.subscribed || !window.Echo || !userId) {
                if (!window.Echo) {
                    console.error('[Chat] ❌ Echo not initialized - Make sure to run: npm run dev');
                }
                return;
            }
            
            try {
                console.info('[Chat] 🔌 Setting up user channel for user:', userId);
                const channelName = `App.Models.User.${userId}`;
                
                const onUserEvt = (data) => {
                    console.info('[Chat] 📨 Received message event:', data);
                    // Data comes directly from broadcastWith()
                    if (data && data.user_id) {
                        console.info('[Chat] 📬 Processing incoming message from user:', data.user_id);
                        $wire.call('handleIncoming', data);
                    } else {
                        console.warn('[Chat] ⚠️ Invalid message data:', data);
                    }
                };

                this.userChannel = window.Echo.private(channelName)
                    .listen('.MessageSent', onUserEvt)
                    .listen('.approval.action', (data) => {
                        console.info('[Chat] 🔔 Approval action received:', data);
                        $wire.call('handleApprovalAction', data);
                    })
                    .listen('.ApprovalRequestCreated', (data) => {
                        console.info('[Chat] 🔔 New approval request received:', data);
                        
                        // Show toast notification
                        window.dispatchEvent(new CustomEvent('new-message-notification', {
                            detail: {
                                message: data.message || 'New material release request',
                                type: 'info',
                                duration: 8000
                            }
                        }));
                        
                        // Browser notification
                        window.dispatchEvent(new CustomEvent('browserNotification', {
                            detail: {
                                message: data.message || 'New material release request',
                                senderName: data.requester_name || 'User'
                            }
                        }));
                        
                        // Refresh messages if viewing that conversation
                        $wire.call('loadMessages');
                    })
                    .subscribed(() => {
                        console.info('[Chat] ✅ Successfully subscribed to user channel:', channelName);
                    })
                    .error((err) => {
                        console.error('[Chat] ❌ User channel error:', err);
                    });
                    
                this.subscribed = true;
                console.info('[Chat] ✓ User channel setup complete:', channelName);
            } catch (err) {
                console.error('[Chat] ❌ Echo subscribe error:', err);
            }
        },
        setupConversation(otherUserId) {
            if (!window.Echo || !otherUserId) return;
            
            const me = {{ auth()->id() }};
            const a = Math.min(me, otherUserId);
            const b = Math.max(me, otherUserId);
            const name = `chat.${a}.${b}`;
            
            if (this.convName === name && this.convSub) {
                console.info('[Chat] ℹ️ Already subscribed to conversation:', name);
                return;
            }

            try {
                // Leave previous conversation channel if any
                if (this.convSub && this.convName) {
                    console.info('[Chat] 👋 Leaving previous conversation:', this.convName);
                    window.Echo.leave(this.convName);
                    this.convSub = null;
                }
                
                console.info('[Chat] 🔌 Setting up conversation channel:', name);
                this.convName = name;
                
                const onConvEvt = (data) => {
                    console.info('[Chat] 💬 Conversation message received:', data);
                    
                    if (data && data.message_id) {
                        console.info('[Chat] 🔄 Refreshing messages...');
                        // Refresh messages and scroll
                        $wire.call('loadMessages').then(() => {
                            setTimeout(() => {
                                window.dispatchEvent(new CustomEvent('messagesLoaded'));
                                window.dispatchEvent(new CustomEvent('messages-updated'));
                            }, 50);
                        });
                    } else {
                        console.warn('[Chat] ⚠️ Invalid conversation data:', data);
                    }
                };

                this.convSub = window.Echo.private(name)
                    .listen('.MessageSent', onConvEvt)
                    .subscribed(() => {
                        console.info('[Chat] ✅ Successfully subscribed to conversation:', name);
                    })
                    .error((err) => {
                        console.error('[Chat] ❌ Conversation channel error:', err);
                    });
                    
                console.info('[Chat] ✓ Conversation channel setup complete:', name);
            } catch (err) {
                console.error('[Chat] ❌ Echo conversation subscribe error:', err);
            }
        },
        setupGroupChat() {
            if (!window.Echo) return;
            
            const groupId = 'all-users';
            const channelName = `group.${groupId}`;
            
            if (this.groupChannel === channelName && this.groupSub) {
                console.info('[Chat] ℹ️ Already subscribed to group chat:', channelName);
                return;
            }

            try {
                // Leave previous group channel if any
                if (this.groupSub && this.groupChannel) {
                    console.info('[Chat] 👋 Leaving previous group chat:', this.groupChannel);
                    window.Echo.leave(this.groupChannel);
                    this.groupSub = null;
                }
                
                console.info('[Chat] 🔌 Setting up group chat channel:', channelName);
                this.groupChannel = channelName;
                
                const onGroupEvt = (data) => {
                    console.info('[Chat] 👥 Group message received:', data);
                    
                    if (data && data.message_id) {
                        console.info('[Chat] 🔄 Refreshing group messages...');
                        $wire.call('loadMessages').then(() => {
                            setTimeout(() => {
                                window.dispatchEvent(new CustomEvent('messagesLoaded'));
                                window.dispatchEvent(new CustomEvent('messages-updated'));
                            }, 50);
                        });
                    } else {
                        console.warn('[Chat] ⚠️ Invalid group data:', data);
                    }
                };

                this.groupSub = window.Echo.private(channelName)
                    .listen('.MessageSent', onGroupEvt)
                    .subscribed(() => {
                        console.info('[Chat] ✅ Successfully subscribed to group chat:', channelName);
                    })
                    .error((err) => {
                        console.error('[Chat] ❌ Group chat channel error:', err);
                    });
                    
                console.info('[Chat] ✓ Group chat channel setup complete:', channelName);
            } catch (err) {
                console.error('[Chat] ❌ Echo group chat subscribe error:', err);
            }
        },
        cleanup() {
            if (this.convSub && this.convName) {
                window.Echo.leave(this.convName);
                this.convSub = null;
                this.convName = null;
            }
            if (this.groupSub && this.groupChannel) {
                window.Echo.leave(this.groupChannel);
                this.groupSub = null;
                this.groupChannel = null;
            }
        }
     }"
     x-init="setupEcho({{ auth()->id() }})"
     x-effect="
        if ($wire.selectedUser) { setupConversation($wire.selectedUser.id) }
        if ($wire.isGroupChat) { setupGroupChat() }
        if (!$wire.isOpen && convName) {
            if (window.Echo) { window.Echo.leave(convName) }
            convName = null; convSub = null;
        }
        if (!$wire.isGroupChat && groupSub) {
            if (window.Echo && groupChannel) { window.Echo.leave(groupChannel) }
            groupSub = null; groupChannel = null;
        }
     "
     @new-message-notification.window="console.log('[Event] new-message-notification received:', $event.detail); let data = $event.detail[0] || $event.detail; showToast(data.message, data.type, data.duration)"
     @message-sent-notification.window="console.log('[Event] message-sent-notification received:', $event.detail); let data = $event.detail[0] || $event.detail; showToast(data.message, data.type, data.duration)"
     @browserNotification.window="notifyBrowser($event.detail)"
     @userSelected.window="setupConversation($event.detail)">
    @if($isOpen)
        <!-- Backdrop Blur Overlay -->
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40" wire:click="toggleChat"></div>
        
        <!-- Centered Chat Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4">
            <div class="bg-gradient-to-br from-[#0a0f1a] to-[#1a1f2e] border-2 border-red-500/30 rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] sm:h-[90vh] max-h-[850px] flex overflow-hidden backdrop-blur-xl">
                <!-- Sidebar -->
                <div class="w-2/5 sm:w-1/3 min-w-[180px] max-w-[350px] border-r border-red-500/20 flex flex-col bg-[#0d1117]/50 overflow-hidden">
                <div class="p-3 sm:p-4 border-b border-red-500/20 bg-gradient-to-r from-red-900/20 to-red-800/10 flex-shrink-0">
                    <input type="text" wire:model.live="search" placeholder="Search..." class="w-full px-3 sm:px-4 py-2 sm:py-2.5 bg-[#1a1f2e] border border-red-500/30 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-xs sm:text-sm text-gray-200 placeholder-gray-500 transition-all">
                </div>
                <div class="flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-red-500/50 scrollbar-track-transparent">
                    <!-- Group Chat Option -->
                    <div wire:click="selectGroupChat" class="p-2.5 sm:p-3 hover:bg-red-500/10 cursor-pointer border-b-2 border-red-500/30 transition-all duration-200 {{ $isGroupChat ? 'bg-gradient-to-r from-red-500/20 to-red-600/10 border-l-4 border-l-red-500' : '' }}">
                        <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-full flex items-center justify-center border-2 border-yellow-400/50 shadow-lg shadow-yellow-500/30 flex-shrink-0">
                                <x-heroicon-o-user-group class="w-5 h-5 text-white" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-100 truncate">IT Department</p>
                                <p class="text-xs text-gray-400">Everyone in the system</p>
                            </div>
                            @if($groupUnreadCount > 0 && !$isGroupChat)
                                <sup class="ml-auto inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-gradient-to-r from-yellow-500 to-orange-600 text-white text-[10px] font-bold shadow-lg shadow-yellow-500/50 animate-pulse">
                                    {{ $groupUnreadCount }}
                                </sup>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Individual Users -->
                    @foreach($users as $user)
                        <div wire:click="selectUser({{ $user->id }})" class="p-2.5 sm:p-3 hover:bg-red-500/10 cursor-pointer border-b border-red-500/10 transition-all duration-200 {{ $selectedUser && $selectedUser->id == $user->id ? 'bg-gradient-to-r from-red-500/20 to-red-600/10 border-l-4 border-l-red-500' : '' }}">
                            <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
                                @if($user->hasAvatarBlob())
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover border-2 border-red-500/50 ring-2 ring-red-500/20 flex-shrink-0">
                                @else
                                    <div class="w-9 h-9 sm:w-10 sm:h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center border-2 border-red-400/50 shadow-lg shadow-red-500/30 flex-shrink-0">
                                        <span class="text-white text-sm font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-100 truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-400 capitalize">{{ str_replace('_', ' ', $user->role) }}</p>
                                </div>
                                @php($count = $unreadCounts[$user->id] ?? 0)
                                @if($count > 0 && (! $selectedUser || $selectedUser->id != $user->id))
                                    <sup class="ml-auto inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-gradient-to-r from-red-500 to-red-600 text-white text-[10px] font-bold shadow-lg shadow-red-500/50 animate-pulse">
                                        {{ $count }}
                                    </sup>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
                <div class="bg-gradient-to-r from-red-600 via-red-500 to-red-600 text-white p-3 sm:p-4 rounded-tr-2xl flex justify-between items-center shadow-lg flex-shrink-0">
                    <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse shadow-lg shadow-green-400/50 flex-shrink-0"></div>
                        <span class="font-bold text-base sm:text-lg truncate">
                            @if($isGroupChat)
                                IT Department
                            @elseif($selectedUser)
                                {{ $selectedUser->name }}
                            @else
                                Select a chat
                            @endif
                        </span>
                    </div>
                    <button wire:click="toggleChat" class="text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                @if($isGroupChat || $selectedUser)
                    <div
                        class="flex-1 p-3 sm:p-4 overflow-y-auto overflow-x-hidden bg-[#0d1117] scrollbar-thin scrollbar-thumb-red-500/50 scrollbar-track-transparent"
                        id="messages-container"
                        wire:loading.class="opacity-50"
                        x-ref="mc"
                        x-init="$nextTick(() => { $refs.mc.scrollTop = $refs.mc.scrollHeight })"
                        @messages-updated.window="$nextTick(() => { $refs.mc && ($refs.mc.scrollTop = $refs.mc.scrollHeight) })"
                        @messagesLoaded.window="$nextTick(() => { $refs.mc && ($refs.mc.scrollTop = $refs.mc.scrollHeight) })"
                        @messageReceived.window="$nextTick(() => { $refs.mc && ($refs.mc.scrollTop = $refs.mc.scrollHeight) })"
                    >
                        @foreach($messages as $message)
                            <div class="flex {{ $message->user_id == auth()->id() ? 'justify-end' : 'justify-start' }} mb-2.5">
                                <div class="p-2.5 sm:p-3 rounded-2xl min-w-[60px] max-w-[85%] sm:max-w-[80%] lg:max-w-[75%] shadow-lg {{ $message->user_id == auth()->id() ? 'bg-gradient-to-br from-red-500 to-red-600 text-white shadow-red-500/30' : 'bg-gradient-to-br from-gray-700 to-gray-800 text-gray-100 shadow-gray-900/50' }}">
                                    <div class="text-[10px] opacity-75 mb-1 font-medium truncate">{{ $message->user->name }}</div>
                                    <div class="text-sm leading-relaxed break-words whitespace-pre-wrap overflow-wrap-anywhere">{{ $message->message }}</div>
                                    <div class="text-[10px] opacity-70 mt-1.5 text-right">{{ $message->created_at->diffForHumans() }}</div>

                                    @if($this->shouldShowApprovalButtons($message))
                                        <div class="mt-3 pt-3 border-t border-white/20 flex gap-2">
                                            <button wire:click="approveFromChat({{ $this->getApprovalId($message) }}, {{ $message->id }})"
                                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-semibold rounded-lg shadow-lg shadow-green-500/30 hover:shadow-green-500/50 transition-all duration-200 transform hover:scale-105 active:scale-95">
                                                <span class="flex items-center justify-center gap-1">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Approve & Release
                                                </span>
                                            </button>
                                            <button wire:click="declineFromChat({{ $this->getApprovalId($message) }}, {{ $message->id }}, 'Declined from chat')"
                                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white text-sm font-semibold rounded-lg shadow-lg shadow-red-500/30 hover:shadow-red-500/50 transition-all duration-200 transform hover:scale-105 active:scale-95">
                                                <span class="flex items-center justify-center gap-1">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Decline
                                                </span>
                                            </button>
                                        </div>
                                    @endif
                                    
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="p-3 sm:p-4 border-t border-red-500/20 bg-[#0d1117]/80 backdrop-blur-sm flex-shrink-0">
                        <form wire:submit.prevent="sendMessage" class="flex items-end gap-2">
                            <textarea 
                                wire:model="newMessage" 
                                placeholder="Message..."
                                rows="1"
                                @keydown.enter.prevent="if (!$event.shiftKey) { $wire.sendMessage(); $el.style.height = 'auto'; $el.style.height = '44px'; } else { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'; }"
                                @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 200) + 'px'"
                                x-init="$el.style.height = '44px'"
                                class="flex-1 bg-[#1a1f2e] border border-red-500/30 rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm text-gray-200 placeholder-gray-500 transition-all resize-none overflow-y-auto min-h-[44px] max-h-[200px]"
                                style="height: 44px;"></textarea>
                            <button type="submit" class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 active:scale-95 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl font-semibold shadow-lg shadow-red-500/30 hover:shadow-red-500/50 transition-all duration-200 flex-shrink-0">
                                <x-heroicon-o-paper-airplane class="w-5 h-5" />
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex-1 flex items-center justify-center bg-[#0d1117]">
                        <div class="text-center">
                            <div class="relative inline-block mb-4">
                                <x-heroicon-o-chat-bubble-left-right class="w-20 h-20 mx-auto text-red-500/30" />
                                <div class="absolute inset-0 bg-red-500/10 blur-xl rounded-full"></div>
                            </div>
                            <p class="text-gray-400 px-4 text-sm">Select a chat to start messaging</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        @php($__totalUnread = array_sum($unreadCounts ?? []) + ($groupUnreadCount ?? 0))
        <button wire:click="toggleChat" class="relative bg-red-500 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg hover:bg-red-600 transition-all duration-200">
            <x-heroicon-o-chat-bubble-left-right class="w-7 h-7" />
            @if($__totalUnread > 0)
                <sup class="absolute -top-2 -right-2 inline-flex items-center justify-center min-w-[1.5rem] h-6 px-2 rounded-full bg-yellow-400 text-gray-900 text-xs font-bold shadow-lg border-2 border-white animate-pulse">
                    {{ $__totalUnread > 99 ? '99+' : $__totalUnread }}
                </sup>
            @endif
        </button>
    @endif

    <!-- Stacked Toast Notifications (Top Right) -->
    <div class="fixed top-4 right-4 z-[9999] space-y-3 max-w-sm">
        <template x-for="(toast, index) in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 x-transition:enter="transform transition ease-out duration-500"
                 x-transition:enter-start="-translate-x-full opacity-0 scale-95"
                 x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                 x-transition:leave="transform transition ease-in duration-400"
                 x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                 x-transition:leave-end="translate-x-full opacity-0 scale-95">
                <div class="relative rounded-2xl px-5 py-4 shadow-2xl border-2 backdrop-blur-xl"
                     :class="{
                        'bg-gradient-to-r from-green-900/90 to-green-800/90 border-green-500/50': toast.type==='success',
                        'bg-gradient-to-r from-red-900/90 to-red-800/90 border-red-500/50': toast.type==='error',
                        'bg-gradient-to-r from-blue-900/90 to-blue-800/90 border-blue-500/50': toast.type==='info'
                     }">
                    <!-- Glow effect -->
                    <div class="absolute inset-0 rounded-2xl blur-xl opacity-50"
                         :class="{
                            'bg-green-500': toast.type==='success',
                            'bg-red-500': toast.type==='error',
                            'bg-blue-500': toast.type==='info'
                         }"></div>
                    
                    <div class="relative flex items-start space-x-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <template x-if="toast.type==='success'">
                                <svg class="w-6 h-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </template>
                            <template x-if="toast.type==='error'">
                                <svg class="w-6 h-6 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </template>
                            <template x-if="toast.type==='info'">
                                <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </template>
                        </div>
                        
                        <!-- Content with sender on top and message on bottom -->
                        <div class="flex-1 min-w-0 space-y-2">
                            <!-- Sender name at top -->
                            <p class="text-xs font-bold text-white/90 uppercase tracking-wide truncate" x-text="toast.message.split(':')[0]"></p>
                            <!-- Message at bottom -->
                            <p class="text-sm font-medium text-white/80 leading-relaxed break-words line-clamp-3" x-text="toast.message.split(':').slice(1).join(':').trim()"></p>
                        </div>
                        
                        <!-- Close button -->
                        <button @click="removeToast(toast.id)" class="flex-shrink-0 text-white/70 hover:text-white transition-colors">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
        </template>
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
