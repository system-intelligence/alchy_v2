<?php

namespace App\Livewire;

use App\Models\Chat;
use App\Models\User;
use App\Models\MaterialReleaseApproval;
use App\Models\Inventory;
use App\Models\Expense;
use App\Models\History;
use App\Notifications\NewMessagePush;
use App\Events\ApprovalActionTaken;
use App\Events\HistoryEntryCreated;
use Illuminate\Support\Facades\Log;                                                                            
use Livewire\Component;

class ChatWidget extends Component
{
    public const GROUP_CHAT_ID = 'all-users';
    
    public $messages = [];
    public $newMessage = '';
    public $isOpen = false;
    public $selectedUser = null;
    public $isGroupChat = false;
    public $users = [];
    public $search = '';
    public $unreadCounts = [];
    public $groupUnreadCount = 0;
    private $lastMessageCheck = null;
    private $processedMessageIds = [];

    protected $listeners = [
        'messageReceived' => 'loadMessages',
        'refreshMessages' => 'loadMessages',
        'incomingMessage' => 'handleIncoming',
        'approvalActionTaken' => 'handleApprovalAction',
    ];

    public function mount()
    {
        $this->loadUsers();
        $this->loadUnreadCounts();
    }

    public function loadUnreadCounts()
    {
        $user = auth()->user();

        // Load persisted unread counts from database
        $this->unreadCounts = $user->unread_private_counts ?? [];
        $this->groupUnreadCount = $user->unread_group_count ?? 0;

        // Ensure all current users have entries in the array
        foreach ($this->users as $u) {
            if (!isset($this->unreadCounts[$u->id])) {
                $this->unreadCounts[$u->id] = 0;
            }
        }
    }

    public function saveUnreadCounts()
    {
        $user = auth()->user();
        $user->update([
            'unread_private_counts' => $this->unreadCounts,
            'unread_group_count' => $this->groupUnreadCount,
        ]);
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
        $this->isGroupChat = false;

        // Reset unread count for this conversation when opening it
        if ($this->selectedUser) {
            $this->unreadCounts[$this->selectedUser->id] = 0;
            $this->saveUnreadCounts();
        }

        $this->lastMessageCheck = now();
        $this->loadMessages();
        $this->dispatch('userSelected', $userId);
    }

    public function selectGroupChat()
    {
        $this->selectedUser = null;
        $this->isGroupChat = true;
        $this->groupUnreadCount = 0;
        $this->saveUnreadCounts();
        $this->lastMessageCheck = now();
        $this->loadMessages();
        $this->dispatch('groupChatSelected');
    }

    public function loadMessages()
    {
        if ($this->isGroupChat) {
            // Load group chat messages
            $currentMessages = Chat::where('group_id', self::GROUP_CHAT_ID)
                ->with(['user', 'approvalRequest.inventory'])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->reverse()
                ->values();
            
            $this->messages = $currentMessages;
        } elseif ($this->selectedUser) {
            $currentMessages = Chat::where(function ($query) {
                $query->where('user_id', auth()->id())
                      ->where('recipient_id', $this->selectedUser->id);
            })->orWhere(function ($query) {
                $query->where('user_id', $this->selectedUser->id)
                      ->where('recipient_id', auth()->id());
            })->with(['user', 'recipient', 'approvalRequest.inventory'])->latest()->take(50)->get()->reverse()->values();

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
                        'duration' => 5000
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
        if (empty(trim($this->newMessage))) {
            return;
        }

        try {
            if ($this->isGroupChat) {
                // Send to group chat
                $chat = Chat::create([
                    'user_id' => auth()->id(),
                    'recipient_id' => null,
                    'group_id' => self::GROUP_CHAT_ID,
                    'message' => $this->newMessage,
                ]);

                // Load the user relationship for broadcasting
                $chat->loadMissing('user');

                // Broadcast to all users (MessageSent expects a Chat model)
                event(new \App\Events\MessageSent($chat));
            } else {
                if (!$this->selectedUser) {
                    return;
                }

                $chat = Chat::create([
                    'user_id' => auth()->id(),
                    'recipient_id' => $this->selectedUser->id,
                    'group_id' => null,
                    'message' => $this->newMessage,
                ]);

                // Broadcast the message
                event(new \App\Events\MessageSent($chat));

                // Push notification (Web Push) to recipient if subscribed
                try {
                    if ($this->selectedUser) {
                        $this->selectedUser->notify(new NewMessagePush($chat));
                    }
                } catch (\Throwable $e) {
                    \Log::warning('NewMessagePush notify failed: ' . $e->getMessage());
                }
            }

            $this->newMessage = '';
            $this->loadMessages();

            // Emit events for UI updates
            $this->dispatch('messagesLoaded');
            $this->dispatch('messages-updated');
            $this->dispatch('message-sent-notification', [
                'message' => 'Message sent successfully!',
                'type' => 'success',
                'duration' => 5000
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to send message: ' . $e->getMessage());
            $this->dispatch('message-sent-notification', [
                'message' => 'Failed to send message. Please try again.',
                'type' => 'error',
                'duration' => 3000
            ]);
        }
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
        $messageId = $payload['message_id'] ?? null;

        // Prevent processing the same message multiple times
        if ($messageId && in_array($messageId, $this->processedMessageIds)) {
            return;
        }

        if ($messageId) {
            $this->processedMessageIds[] = $messageId;
            // Keep only the last 100 processed message IDs to prevent memory issues
            if (count($this->processedMessageIds) > 100) {
                array_shift($this->processedMessageIds);
            }
        }

        $senderId = $payload['user_id'] ?? null;
        $recipientId = $payload['recipient_id'] ?? null;
        $groupId = $payload['group_id'] ?? null;

        // If this is a group message for the main group, handle it here
        if ($groupId && $groupId === self::GROUP_CHAT_ID) {
            // If the user currently has the group chat open, reload messages
            if ($this->isOpen && $this->isGroupChat) {
                $this->loadMessages();
                $this->dispatch('messagesLoaded');
                $this->dispatch('messages-updated');
            } else {
                // Increment unread count for group
                $this->groupUnreadCount = ($this->groupUnreadCount ?? 0) + 1;
                $this->saveUnreadCounts();
                // Force UI update
                $this->dispatch('$refresh');

                $senderName = $payload['user_name'] ?? 'Someone';
                $messageText = $payload['message'] ?? '';
                $this->dispatch('new-message-notification', [
                    'message' => $senderName . ': ' . $messageText,
                    'type' => 'info',
                    'duration' => 5000
                ]);

                $this->dispatch('browserNotification', [
                    'message' => $messageText,
                    'senderName' => $senderName
                ]);
            }

            return;
        }

        // Only proceed if current user is the intended recipient for private messages
        if ($recipientId !== auth()->id()) {
            return;
        }

        // Ensure sender exists in users list
        if ($senderId) {
            $senderExists = $this->users->contains('id', $senderId);
            if (!$senderExists) {
                $this->loadUsers();
            }
        }

        // If chat is open with the sender, refresh messages; otherwise increment unread count
        if ($this->isOpen && $this->selectedUser && $this->selectedUser->id == $senderId) {
            // Reload messages to show the new message
            $this->loadMessages();
            $this->dispatch('messagesLoaded');
            $this->dispatch('messages-updated');
        } else {
            if ($senderId) {
                // Initialize if not exists
                if (!isset($this->unreadCounts[$senderId])) {
                    $this->unreadCounts[$senderId] = 0;
                }
                $this->unreadCounts[$senderId]++;
                $this->saveUnreadCounts();

                // Force UI update
                $this->dispatch('$refresh');
            }

            $senderName = $payload['user_name'] ?? 'Someone';
            $messageText = $payload['message'] ?? '';
            // Toast notification with message preview
            $this->dispatch('new-message-notification', [
                'message' => $senderName . ': ' . $messageText,
                'type' => 'info',
                'duration' => 5000
            ]);

            // Browser notification hook
            $this->dispatch('browserNotification', [
                'message' => $payload['message'] ?? '',
                'senderName' => $senderName
            ]);
        }
    }

    public function approveFromChat($approvalId, $chatId)
    {
        try {
            Log::info('=== APPROVE FROM CHAT STARTED ===', ['approval_id' => $approvalId, 'admin' => auth()->user()->name]);
            
            $approval = MaterialReleaseApproval::with(['requester', 'inventory', 'expense', 'expense.client', 'expense.project'])->findOrFail($approvalId);
            
            Log::info('Approval loaded', [
                'id' => $approval->id,
                'status' => $approval->status,
                'material' => $approval->inventory->material_name,
                'quantity' => $approval->quantity_requested
            ]);
            
            // Check if user is authorized (system admin only)
            if (auth()->user()->role !== 'system_admin') {
                Log::warning('User is not authorized', ['user_role' => auth()->user()->role]);
                $this->dispatch('notification', [
                    'message' => 'You do not have permission to approve requests.',
                    'type' => 'error'
                ]);
                return;
            }

            // Check if already processed
            if (!$approval->isPending()) {
                Log::warning('Approval already processed', ['status' => $approval->status]);
                $this->dispatch('notification', [
                    'message' => 'This request has already been processed.',
                    'type' => 'warning'
                ]);
                $this->loadMessages();
                return;
            }

            Log::info('Approving request...');
            // Approve the request
            $approval->approve(auth()->id(), 'Approved from chat');
            $approval->refresh(); // Reload to get updated status
            
            Log::info('Approval updated', ['new_status' => $approval->status]);

            // Actually release the materials
            $inventory = $approval->inventory;
            $inventory->refresh(); // Get fresh data
            
            Log::info('Inventory before release', [
                'material' => $inventory->material_name,
                'current_quantity' => $inventory->quantity,
                'requested_quantity' => $approval->quantity_requested
            ]);
            
            // Check if inventory has enough quantity
            if ($inventory->quantity < $approval->quantity_requested) {
                Log::error('Insufficient inventory', [
                    'available' => $inventory->quantity,
                    'requested' => $approval->quantity_requested
                ]);
                $this->dispatch('notification', [
                    'message' => 'Insufficient inventory quantity available.',
                    'type' => 'error'
                ]);
                return;
            }

            // Deduct from inventory
            $oldQuantity = $inventory->quantity;
            $inventory->quantity -= $approval->quantity_requested;
            
            // Update inventory status if quantity is 0
            if ($inventory->quantity == 0) {
                $inventory->inventory_status = \App\Enums\InventoryStatus::OUT_OF_STOCK;
            }
            
            $saved = $inventory->save();
            Log::info('Inventory saved', [
                'success' => $saved,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $inventory->quantity
            ]);

            // Create expense record if expense_id exists
            if ($approval->expense_id) {
                $expense = $approval->expense;
                if ($expense) {
                    Log::info('Updating expense record', ['expense_id' => $expense->id]);
                    $expense->quantity_released = ($expense->quantity_released ?? 0) + $approval->quantity_requested;
                    $expense->save();
                    Log::info('Expense updated', ['new_quantity_released' => $expense->quantity_released]);
                }
            }

            // Create history entry for inventory change - REMOVED to avoid duplication
            // The approval history already shows all necessary details
            Log::info('Skipping inventory history creation to avoid duplication');

            // Update existing history entry or create new
            $existingHistory = History::where('model', 'MaterialReleaseApproval')
                ->where('model_id', $approval->id)
                ->first();

            if ($existingHistory) {
                // Update existing history with completion details
                $existingHistory->update([
                    'action' => 'Approval Request Completed',
                    'changes' => json_encode([
                        'status' => 'approved',
                        'project' => $approval->project ?? 'N/A',
                        'client' => $approval->client ?? 'N/A',
                        'material' => $inventory->material_name,
                        'quantity' => $approval->quantity_requested,
                        'reviewer' => auth()->user()->name,
                        'completed_at' => now()->toDateTimeString(),
                    ]),
                ]);
                $approvalHistory = $existingHistory;
                Log::info('Approval history updated', ['history_id' => $approvalHistory->id]);
            } else {
                // Create new if not found
                $approvalHistory = History::create([
                    'user_id' => $approval->requested_by,
                    'action' => 'Approval Request Completed',
                    'model' => 'MaterialReleaseApproval',
                    'model_id' => $approval->id,
                    'changes' => json_encode([
                        'status' => 'approved',
                        'project' => $approval->project ?? 'N/A',
                        'client' => $approval->client ?? 'N/A',
                        'material' => $inventory->material_name,
                        'quantity' => $approval->quantity_requested,
                        'reviewer' => auth()->user()->name,
                        'completed_at' => now()->toDateTimeString(),
                    ]),
                    'old_values' => json_encode([
                        'status' => 'pending'
                    ])
                ]);
                Log::info('Approval history created', ['history_id' => $approvalHistory->id]);
            }
            
            // Broadcast history event
            Log::info('Broadcasting approval history event...');
            try {
                event(new HistoryEntryCreated($approvalHistory));
                Log::info('✅ Approval history event broadcasted successfully!');
            } catch (\Exception $e) {
                Log::error('❌ Error broadcasting approval history: ' . $e->getMessage());
            }

            // Broadcast real-time event to all relevant users
            Log::info('Broadcasting approval action event...');
            try {
                event(new ApprovalActionTaken($approval, 'approved'));
                Log::info('Approval action event broadcasted successfully');
            } catch (\Exception $e) {
                Log::error('Error broadcasting approval action: ' . $e->getMessage());
            }

            // Send approval notification to requester
            Log::info('Sending chat notification to requester...');
            $notificationChat = Chat::create([
                'user_id' => auth()->id(),
                'recipient_id' => $approval->requested_by,
                'message' => "✅ Your material release request has been APPROVED!\n\n" .
                             "Material: {$inventory->material_name}\n" .
                             "Quantity: {$approval->quantity_requested}\n" .
                             "Released by: " . auth()->user()->name,
            ]);
            Log::info('Chat notification sent', ['chat_id' => $notificationChat->id]);

            // Broadcast the chat message event
            try {
                event(new \App\Events\MessageSent($notificationChat));
                Log::info('Chat message broadcasted');
            } catch (\Exception $e) {
                Log::error('Error broadcasting chat message: ' . $e->getMessage());
            }

            Log::info('=== APPROVAL COMPLETED SUCCESSFULLY ===');
            
            $this->dispatch('notification', [
                'message' => 'Request approved and materials released successfully!',
                'type' => 'success'
            ]);

            // Refresh messages to show updated status
            $this->loadMessages();

        } catch (\Exception $e) {
            Log::error('Error approving from chat: ' . $e->getMessage());
            $this->dispatch('notification', [
                'message' => 'Error processing approval: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function declineFromChat($approvalId, $chatId, $reason = 'No reason provided')
    {
        try {
            $approval = MaterialReleaseApproval::with(['requester', 'inventory', 'expense', 'expense.client', 'expense.project'])->findOrFail($approvalId);
            
            // Check if user is authorized (system admin only)
            if (auth()->user()->role !== 'system_admin') {
                $this->dispatch('notification', [
                    'message' => 'You do not have permission to decline requests.',
                    'type' => 'error'
                ]);
                return;
            }

            // Check if already processed
            if (!$approval->isPending()) {
                $this->dispatch('notification', [
                    'message' => 'This request has already been processed.',
                    'type' => 'warning'
                ]);
                $this->loadMessages();
                return;
            }

            // Decline the request
            $approval->decline(auth()->id(), $reason);
            $approval->refresh();
            
            Log::info('Approval declined', ['new_status' => $approval->status]);

            // Update existing history entry or create new
            $existingHistory = History::where('model', 'MaterialReleaseApproval')
                ->where('model_id', $approval->id)
                ->first();

            if ($existingHistory) {
                // Update existing history with completion details
                $existingHistory->update([
                    'action' => 'Approval Request Completed',
                    'changes' => json_encode([
                        'status' => 'declined',
                        'project' => $approval->project ?? 'N/A',
                        'client' => $approval->client ?? 'N/A',
                        'material' => $approval->inventory->material_name,
                        'quantity' => $approval->quantity_requested,
                        'reviewer' => auth()->user()->name,
                        'reason' => $reason,
                        'completed_at' => now()->toDateTimeString(),
                    ]),
                ]);
                $approvalHistory = $existingHistory;
                Log::info('Decline history updated', ['history_id' => $approvalHistory->id]);
            } else {
                // Create new if not found
                $approvalHistory = History::create([
                    'user_id' => $approval->requested_by,
                    'action' => 'Approval Request Completed',
                    'model' => 'MaterialReleaseApproval',
                    'model_id' => $approval->id,
                    'changes' => json_encode([
                        'status' => 'declined',
                        'project' => $approval->project ?? 'N/A',
                        'client' => $approval->client ?? 'N/A',
                        'material' => $approval->inventory->material_name,
                        'quantity' => $approval->quantity_requested,
                        'reviewer' => auth()->user()->name,
                        'reason' => $reason,
                        'completed_at' => now()->toDateTimeString(),
                    ]),
                    'old_values' => json_encode([
                        'status' => 'pending'
                    ])
                ]);
                Log::info('Decline history created', ['history_id' => $approvalHistory->id]);
            }

            // Broadcast history event
            Log::info('Broadcasting decline history event...');
            try {
                event(new HistoryEntryCreated($approvalHistory));
                Log::info('✅ Decline history event broadcasted successfully!');
            } catch (\Exception $e) {
                Log::error('❌ Error broadcasting decline history: ' . $e->getMessage());
            }

            // Broadcast real-time event to all relevant users
            Log::info('Broadcasting decline action event...');
            try {
                event(new ApprovalActionTaken($approval, 'declined'));
                Log::info('Decline action event broadcasted successfully');
            } catch (\Exception $e) {
                Log::error('Error broadcasting decline action: ' . $e->getMessage());
            }

            // Send decline notification to requester
            Log::info('Sending decline notification to requester...');
            $notificationChat = Chat::create([
                'user_id' => auth()->id(),
                'recipient_id' => $approval->requested_by,
                'message' => "❌ Your material release request has been DECLINED.\n\n" .
                             "Material: {$approval->inventory->material_name}\n" .
                             "Quantity: {$approval->quantity_requested}\n" .
                             "Reason: {$reason}\n" .
                             "Declined by: " . auth()->user()->name,
            ]);
            Log::info('Decline chat notification sent', ['chat_id' => $notificationChat->id]);

            // Broadcast the chat message event
            try {
                event(new \App\Events\MessageSent($notificationChat));
                Log::info('Decline chat message broadcasted');
            } catch (\Exception $e) {
                Log::error('Error broadcasting decline chat message: ' . $e->getMessage());
            }

            Log::info('=== DECLINE COMPLETED SUCCESSFULLY ===');

            $this->dispatch('notification', [
                'message' => 'Request declined successfully.',
                'type' => 'success'
            ]);

            // Refresh messages to show updated status
            $this->loadMessages();

        } catch (\Exception $e) {
            Log::error('Error declining from chat: ' . $e->getMessage());
            $this->dispatch('notification', [
                'message' => 'Error processing decline: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function handleApprovalAction($payload)
    {
        // Handle real-time approval action updates
        try {
            Log::info('Approval action received in ChatWidget', ['payload' => $payload]);
            
            // Refresh messages to show updated button states
            $this->loadMessages();
            
            // Show notification to the user
            $action = $payload['action'] ?? 'processed';
            $reviewerName = $payload['reviewer_name'] ?? 'System Admin';
            $message = $payload['message'] ?? "Approval request has been {$action}.";
            
            $this->dispatch('new-message-notification', [
                'message' => $message,
                'type' => $action === 'approved' ? 'success' : 'warning',
                'duration' => 5000
            ]);

            // Browser notification hook
            $this->dispatch('browserNotification', [
                'message' => $message,
                'senderName' => $reviewerName
            ]);
        } catch (\Exception $e) {
            Log::error('Error handling approval action in ChatWidget: ' . $e->getMessage());
        }
    }

    public function shouldShowApprovalButtons($message)
    {
        if (!auth()->user() || !auth()->user()->isSystemAdmin()) {
            return false;
        }

        // Check if message has approvalRequest relationship
        if ($message->approvalRequest && $message->approvalRequest->isPending()) {
            return true;
        }

        // Check if message contains "Approval ID: " and extract the ID
        if (preg_match('/Approval ID:\s*(\d+)/', $message->message, $matches)) {
            $approvalId = $matches[1];
            $approval = MaterialReleaseApproval::find($approvalId);
            return $approval && $approval->isPending();
        }

        return false;
    }

    public function getApprovalId($message)
    {
        // Check if message has approvalRequest relationship
        if ($message->approvalRequest) {
            return $message->approvalRequest->id;
        }

        // Check if message contains "Approval ID: " and extract the ID
        if (preg_match('/Approval ID:\s*(\d+)/', $message->message, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function render()
    {
        return view('livewire.chat-widget');
    }
}
