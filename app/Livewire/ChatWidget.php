<?php

namespace App\Livewire;

use App\Models\Chat;
use App\Models\User;
use App\Notifications\NewMessagePush;
use Livewire\Component;

class ChatWidget extends Component
{
    public $messages = [];
    public $newMessage = '';
    public $isOpen = false;
    public $selectedUser = null;
    public $users = [];
    public $search = '';
    public $unreadCounts = [];
    private $lastMessageCheck = null;

    protected $listeners = [
        'messageReceived' => 'loadMessages',
        'refreshMessages' => 'loadMessages',
        'incomingMessage' => 'handleIncoming',
    ];

    public function mount()
    {
        $this->loadUsers();

        // Initialize unread counters for currently available users
        foreach ($this->users as $u) {
            $this->unreadCounts[$u->id] = $this->unreadCounts[$u->id] ?? 0;
        }
    }

    public function loadUsers()
    {
        $this->users = User::where('id', '!=', auth()->id())
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->get();

        // Ensure unread counts map has keys for all users
        foreach ($this->users as $u) {
            if (!isset($this->unreadCounts[$u->id])) {
                $this->unreadCounts[$u->id] = 0;
            }
        }
    }

    public function selectUser($userId)
    {
        $this->selectedUser = User::find($userId);

        // Reset unread count for this conversation when opening it
        if ($this->selectedUser) {
            $this->unreadCounts[$this->selectedUser->id] = 0;
        }

        $this->lastMessageCheck = now(); // Set initial timestamp for new conversation
        $this->loadMessages();
        $this->dispatch('userSelected', $userId);
    }

    public function loadMessages()
    {
        if ($this->selectedUser) {
            $currentMessages = Chat::where(function ($query) {
                $query->where('user_id', auth()->id())
                      ->where('recipient_id', $this->selectedUser->id);
            })->orWhere(function ($query) {
                $query->where('user_id', $this->selectedUser->id)
                      ->where('recipient_id', auth()->id());
            })->with(['user', 'recipient'])->latest()->take(50)->get()->reverse()->values();

            // Check for new messages from the other user since last check
            if ($this->lastMessageCheck) {
                $newMessages = $currentMessages->filter(function ($message) {
                    return $message->created_at > $this->lastMessageCheck && $message->user_id !== auth()->id();
                });

                foreach ($newMessages as $message) {
                    // This is a new message from the other user
                    $this->dispatch('new-message-notification', [
                        'message' => "New message from {$message->user->name}",
                        'type' => 'info',
                        'duration' => 4000
                    ]);

                    // Also emit browser notification if supported
                    $this->dispatch('browserNotification', [
                        'message' => $message->message,
                        'senderName' => $message->user->name
                    ]);
                }
            }

            $this->messages = $currentMessages;
            $this->lastMessageCheck = now();

            // Emit events to trigger scrolling (Livewire + Browser)
            $this->dispatch('messagesLoaded');
            $this->dispatch('messages-updated');
        } else {
            $this->messages = [];
            $this->lastMessageCheck = null;
        }
    }

    public function sendMessage()
    {
        if (trim($this->newMessage) === '' || !$this->selectedUser) {
            return;
        }

        $message = Chat::create([
            'user_id' => auth()->id(),
            'recipient_id' => $this->selectedUser->id,
            'message' => $this->newMessage,
        ]);

        $this->newMessage = '';

        // Broadcast the message
        broadcast(new \App\Events\MessageSent($message))->toOthers();

        // Push notification (Web Push) to recipient if subscribed
        try {
            if ($this->selectedUser) {
                $this->selectedUser->notify(new NewMessagePush($message));
            }
        } catch (\Throwable $e) {
            \Log::warning('NewMessagePush notify failed: ' . $e->getMessage());
        }

        $this->loadMessages();

        // Emit events for UI updates
        $this->dispatch('messagesLoaded');
        $this->dispatch('messages-updated');
        $this->dispatch('message-sent-notification', [
            'message' => 'Message sent successfully!',
            'type' => 'success',
            'duration' => 2000
        ]);
    }

    public function updatedSearch()
    {
        $this->loadUsers();
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
        if (!$this->isOpen) {
            $this->selectedUser = null;
            $this->messages = [];
        }
    }

    public function handleIncoming($payload)
    {
        // Payload from Echo broadcastWith()
        $senderId = $payload['user_id'] ?? null;
        $recipientId = $payload['recipient_id'] ?? null;

        // Only proceed if current user is the intended recipient
        if ($recipientId !== auth()->id()) {
            return;
        }

        // If chat is open with the sender, refresh messages; otherwise increment unread count
        if ($this->isOpen && $this->selectedUser && $this->selectedUser->id == $senderId) {
            $this->loadMessages();
            $this->dispatch('messagesLoaded');
            $this->dispatch('messages-updated');
        } else {
            if ($senderId) {
                $this->unreadCounts[$senderId] = ($this->unreadCounts[$senderId] ?? 0) + 1;
            }

            $senderName = $payload['user_name'] ?? 'Someone';
            // Toast notification
            $this->dispatch('new-message-notification', [
                'message' => "New message from {$senderName}",
                'type' => 'info',
                'duration' => 4000
            ]);

            // Browser notification hook
            $this->dispatch('browserNotification', [
                'message' => $payload['message'] ?? '',
                'senderName' => $senderName
            ]);
        }
    }

    public function render()
    {
        return view('livewire.chat-widget');
    }
}
