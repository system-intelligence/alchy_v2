# Chat Approval Buttons - Implementation Guide

## Overview
System admins can now approve or decline material release requests directly from chat messages without navigating to the separate approvals page.

## Features

### 1. **Approve/Decline Buttons in Chat**
- When a regular user requests material release, private chat messages are sent to all system admins
- System admins and developers see **Approve & Release** and **Decline** buttons directly in their private chat messages
- Buttons only appear for:
  - System admins and developers
  - Messages containing approval requests
  - Requests with "pending" status

### 2. **Approval Workflow**
When an admin clicks **Approve & Release**:
1. ✅ Approval status is updated to "approved"
2. ✅ Materials are automatically released (inventory deducted)
3. ✅ Expense record is updated
4. ✅ History entry is created
5. ✅ Inventory status updates if quantity reaches 0
6. ✅ Requester receives a notification message
7. ✅ Button changes to show "Approved by [Admin Name]" status badge

When an admin clicks **Decline**:
1. ❌ Approval status is updated to "declined"
2. ❌ Materials are NOT released
3. ❌ Requester receives a notification with decline reason
4. ❌ Button changes to show "Declined by [Admin Name]" status badge

### 3. **Real-Time Updates**
- After approve/decline action, the chat messages automatically refresh
- Status badges replace the action buttons
- No page reload needed

## Technical Implementation

### Modified Files

#### 1. **app/Livewire/ChatWidget.php**
**New imports added:**
```php
use App\Models\MaterialReleaseApproval;
use App\Models\Inventory;
use App\Models\Expense;
use App\Models\History;
use Illuminate\Support\Facades\Log;
```

**New methods added:**
- `approveFromChat($approvalId, $chatId)` - Handles approval and material release
- `declineFromChat($approvalId, $chatId, $reason)` - Handles decline

**Modified method:**
- `loadMessages()` - Now loads approval request data with messages using `->with(['approvalRequest.inventory'])`

#### 2. **app/Models/Chat.php**
**New relationship added:**
```php
public function approvalRequest(): HasOne
{
    return $this->hasOne(MaterialReleaseApproval::class, 'chat_id');
}
```

#### 3. **resources/views/livewire/chat-widget.blade.php**
**Enhanced message display:**
- Added conditional rendering for approval request buttons
- Shows "Approve & Release" (green) and "Decline" (red) buttons
- Displays status badges for approved/declined requests
- Uses SVG icons for visual clarity

## Usage Flow

### For Regular Users:
1. Try to release materials from Expenses or Masterlist
2. System creates an approval request
3. Chat message is sent to all system admins
4. User sees "Request sent for approval" message
5. User waits for admin response

### For System Admins:
1. Receive chat notification from user
2. See the material release details in the message
3. Click **Approve & Release** to:
   - Release the materials immediately
   - Notify the user
4. OR click **Decline** to:
   - Reject the request
   - Notify the user with reason

## Key Features

### ✅ Permissions
- Only system_admin and developer roles can see buttons
- Requesters cannot approve their own requests
- Already processed requests show status badges instead of buttons

### ✅ Validation
- Checks inventory availability before release
- Prevents duplicate processing
- Verifies user permissions

### ✅ Inventory Management
- Automatically deducts quantity on approval
- Updates inventory status (OUT_OF_STOCK if quantity = 0)
- Creates expense records
- Logs history entries

### ✅ Notifications
- Sends chat message to requester after approval
- Sends chat message to requester after decline
- Messages include material details and admin name

### ✅ Error Handling
- Try-catch blocks prevent crashes
- Logs errors for debugging
- Shows user-friendly error messages

## Button Styling

**Approve Button:**
- Green background (bg-green-600)
- Hover effect (hover:bg-green-700)
- Checkmark icon
- Text: "Approve & Release"

**Decline Button:**
- Red background (bg-red-600)
- Hover effect (hover:bg-red-700)
- X icon
- Text: "Decline"

**Approved Badge:**
- Green background with transparency
- Green border
- Checkmark circle icon
- Shows reviewer name

**Declined Badge:**
- Red background with transparency
- Red border
- X circle icon
- Shows reviewer name

## Example Message Flow

### Request Message (from user):
```
📋 Material Release Request

Material: Cement
Quantity: 50 bags
Project: Building Construction
Client: John Doe

Reason: Need for foundation work

Requested by: Maria Santos
```

### Approval Response (to user):
```
✅ Your material release request has been APPROVED!

Material: Cement
Quantity: 50 bags
Released by: Admin John
```

### Decline Response (to user):
```
❌ Your material release request has been DECLINED.

Material: Cement
Quantity: 50 bags
Reason: Declined from chat
Declined by: Admin John
```

## Benefits

1. **Faster Approval** - No need to navigate to separate page
2. **Better Communication** - Admins can discuss with users before deciding
3. **Real-Time** - Instant notifications via Pusher
4. **Transparent** - Status badges show who approved/declined
5. **Efficient** - Approve and release in one click
6. **Audit Trail** - All actions logged in history

## Notes

- Buttons only appear for pending requests
- After action taken, buttons are replaced with status badges
- Both Expenses and Masterlist sections use this approval system
- System admins can still use the /approvals page for bulk management
