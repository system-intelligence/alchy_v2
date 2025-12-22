# Real-Time Approval Notifications & History Tracking - Implementation Guide

## Overview
Enhanced the material release approval system with real-time Pusher notifications and comprehensive history tracking for all approve/decline actions.

## New Features

### 1. **Real-Time Notifications via Pusher** 🔔
- When an admin approves/declines a request from chat, the action is broadcast in real-time to:
  - The requester (user who made the request)
  - All system admins and developers
- Users receive instant notifications without page refresh
- Chat messages automatically update to show approval status

### 2. **Comprehensive History Tracking** 📋
- Every approve/decline action is now logged in the history table
- History entries include:
  - Who performed the action (reviewer)
  - Who requested the approval (requester)
  - Material details (name, quantity)
  - Status change (pending → approved/declined)
  - Timestamp of action
  - Reason (for declines)

## Technical Implementation

### New Files Created

#### 1. **app/Events/ApprovalActionTaken.php**
Real-time event that broadcasts approval actions via Pusher.

**Features:**
- Implements `ShouldBroadcastNow` for immediate broadcasting
- Broadcasts to private channels of requester and all admins
- Includes comprehensive payload with approval details
- Custom broadcast name: `approval.action`

**Broadcast Channels:**
```php
// Requester's private channel
new PrivateChannel('App.Models.User.' . $requesterId)

// All system admins' and developers' channels
new PrivateChannel('App.Models.User.' . $adminId)
```

**Payload Data:**
```json
{
  "approval_id": 123,
  "action": "approved|declined",
  "reviewer_id": 5,
  "reviewer_name": "Admin John",
  "requester_id": 10,
  "material_name": "Cement",
  "quantity": 50,
  "status": "approved|declined",
  "message": "Your material release request has been approved!"
}
```

### Modified Files

#### 1. **app/Livewire/ChatWidget.php**

**New Import:**
```php
use App\Events\ApprovalActionTaken;
```

**New Listener:**
```php
protected $listeners = [
    // ... existing listeners
    'approvalActionTaken' => 'handleApprovalAction',
];
```

**Enhanced `approveFromChat()` method:**
- Now creates TWO history entries:
  1. **Inventory History**: Tracks material quantity change
  2. **Approval History**: Tracks approval status change
- Broadcasts `ApprovalActionTaken` event to all relevant users
- Includes comprehensive details in history entries

**History Entry for Inventory:**
```php
History::create([
    'user_id' => auth()->id(),
    'action' => 'Material Release Approved',
    'model' => 'Inventory',
    'model_id' => $inventory->id,
    'changes' => json_encode([
        'approval_id' => $approval->id,
        'quantity_released' => 50,
        'new_quantity' => 450,
        'material_name' => 'Cement',
    ]),
    'old_values' => json_encode([
        'quantity' => 500
    ])
]);
```

**History Entry for Approval:**
```php
History::create([
    'user_id' => auth()->id(),
    'action' => 'Approval Request Approved',
    'model' => 'MaterialReleaseApproval',
    'model_id' => $approval->id,
    'changes' => json_encode([
        'status' => 'approved',
        'requester' => 'Maria Santos',
        'material' => 'Cement',
        'quantity' => 50,
        'reviewer' => 'Admin John',
        'reviewed_at' => '2025-11-13 10:30:45',
    ]),
    'old_values' => json_encode([
        'status' => 'pending'
    ])
]);
```

**Enhanced `declineFromChat()` method:**
- Creates detailed history entry for decline action
- Broadcasts `ApprovalActionTaken` event
- Includes decline reason in history

**History Entry for Decline:**
```php
History::create([
    'user_id' => auth()->id(),
    'action' => 'Approval Request Declined',
    'model' => 'MaterialReleaseApproval',
    'model_id' => $approval->id,
    'changes' => json_encode([
        'status' => 'declined',
        'requester' => 'Maria Santos',
        'material' => 'Cement',
        'quantity' => 50,
        'reviewer' => 'Admin John',
        'reason' => 'Insufficient justification',
        'reviewed_at' => '2025-11-13 10:30:45',
    ]),
    'old_values' => json_encode([
        'status' => 'pending'
    ])
]);
```

**New Method: `handleApprovalAction()`**
Handles incoming real-time approval action events:
```php
public function handleApprovalAction($payload)
{
    // Refresh messages to show updated button states
    $this->loadMessages();
    
    // Show toast notification
    $this->dispatch('new-message-notification', [
        'message' => $payload['message'],
        'type' => $payload['action'] === 'approved' ? 'success' : 'warning',
        'duration' => 5000
    ]);

    // Trigger browser notification
    $this->dispatch('browserNotification', [
        'message' => $payload['message'],
        'senderName' => $payload['reviewer_name']
    ]);
}
```

#### 2. **resources/views/livewire/chat-widget.blade.php**

**Enhanced Echo Listener:**
Added listener for approval action events on the user's private channel:

```javascript
this.userChannel = window.Echo.private(channelName)
    .listen('.MessageSent', onUserEvt)
    .listen('.approval.action', (data) => {
        console.info('[Chat] 🔔 Approval action received:', data);
        $wire.call('handleApprovalAction', data);
    })
    // ... rest of the code
```

## Real-Time Flow

### Scenario: Admin Approves Request

1. **Admin clicks "Approve & Release" button in chat**
   - ChatWidget `approveFromChat()` method is called

2. **Approval is processed**
   - Approval status updated to "approved"
   - Materials released from inventory
   - Expense record updated

3. **History entries created**
   - Inventory history: tracks quantity change
   - Approval history: tracks status change

4. **Pusher event broadcasted**
   - `ApprovalActionTaken` event fired
   - Sent to requester's private channel
   - Sent to all admins' private channels

5. **Real-time updates received**
   - Requester receives notification instantly
   - Other admins see the status update
   - Chat messages refresh automatically
   - Buttons change to "Approved by [Name]" badge

6. **Notifications displayed**
   - Toast notification appears
   - Browser notification triggered
   - Success message shown

### Scenario: Admin Declines Request

1. **Admin clicks "Decline" button in chat**
   - ChatWidget `declineFromChat()` method is called

2. **Decline is processed**
   - Approval status updated to "declined"
   - No materials released

3. **History entry created**
   - Approval history: tracks status change with reason

4. **Pusher event broadcasted**
   - `ApprovalActionTaken` event fired
   - Sent to requester and all admins

5. **Real-time updates received**
   - Requester receives notification instantly
   - Buttons change to "Declined by [Name]" badge

6. **Notifications displayed**
   - Warning toast notification
   - Browser notification with decline message

## History Tracking Details

### What Gets Tracked

#### For Approvals:
- ✅ **Action**: "Approval Request Approved"
- ✅ **Model**: MaterialReleaseApproval
- ✅ **User**: Admin who approved
- ✅ **Changes**: 
  - Status change (pending → approved)
  - Requester name
  - Material name and quantity
  - Reviewer name
  - Review timestamp
- ✅ **Old Values**: Previous status (pending)

**Plus separate inventory history:**
- ✅ **Action**: "Material Release Approved"
- ✅ **Model**: Inventory
- ✅ **Changes**: Quantity deducted, approval ID
- ✅ **Old Values**: Original quantity

#### For Declines:
- ❌ **Action**: "Approval Request Declined"
- ❌ **Model**: MaterialReleaseApproval
- ❌ **User**: Admin who declined
- ❌ **Changes**:
  - Status change (pending → declined)
  - Requester name
  - Material name and quantity
  - Reviewer name
  - Decline reason
  - Review timestamp
- ❌ **Old Values**: Previous status (pending)

### Viewing History

History entries can be viewed in the History section of the app. Each entry shows:
- Old vs New values comparison (with toggle feature)
- Who made the change
- When it was changed
- Detailed change information

## Benefits

### 1. **Instant Notifications** 🚀
- No page refresh needed
- Users notified immediately
- Better user experience

### 2. **Complete Audit Trail** 📊
- Every action is logged
- Can track who approved/declined what
- Timestamps for all actions
- Reason tracking for declines

### 3. **Transparency** 👁️
- Everyone sees updates in real-time
- Clear accountability
- Easy to review approval history

### 4. **Better Communication** 💬
- Admins see when others take action
- Prevents duplicate approvals
- Requesters get instant feedback

## Testing Checklist

- [ ] Admin approves request → Real-time notification received by requester
- [ ] Admin declines request → Real-time notification received by requester
- [ ] Other admins see status update in real-time
- [ ] History entries created for approvals
- [ ] History entries created for declines
- [ ] History shows correct old vs new values
- [ ] Buttons change to status badges after action
- [ ] Toast notifications appear
- [ ] Browser notifications work
- [ ] Console logs show Pusher events

## Console Logs

When approval action is taken, you should see in browser console:
```
[Chat] 🔔 Approval action received: {approval_id: 123, action: "approved", ...}
```

## Notes

- History tracking works even if Pusher fails (wrapped in try-catch)
- Both inventory and approval changes are tracked separately
- Old values are stored in JSON format for comparison
- Real-time updates refresh chat messages automatically
- Multiple admins won't see buttons after first action (prevents duplicate processing)

## Error Handling

All real-time broadcasting is wrapped in try-catch blocks:
- If Pusher fails, approval still works
- Errors are logged for debugging
- User still sees success message
- History is still created

## Example History Entry (JSON)

**Approval:**
```json
{
  "action": "Approval Request Approved",
  "model": "MaterialReleaseApproval",
  "model_id": 123,
  "user_id": 5,
  "changes": {
    "status": "approved",
    "requester": "Maria Santos",
    "material": "Cement",
    "quantity": 50,
    "reviewer": "Admin John",
    "reviewed_at": "2025-11-13 10:30:45"
  },
  "old_values": {
    "status": "pending"
  }
}
```

**Decline:**
```json
{
  "action": "Approval Request Declined",
  "model": "MaterialReleaseApproval",
  "model_id": 123,
  "user_id": 5,
  "changes": {
    "status": "declined",
    "requester": "Maria Santos",
    "material": "Cement",
    "quantity": 50,
    "reviewer": "Admin John",
    "reason": "Declined from chat",
    "reviewed_at": "2025-11-13 10:30:45"
  },
  "old_values": {
    "status": "pending"
  }
}
```
