<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MaterialReleaseApproval;
use App\Models\User;
use App\Models\Chat;
use App\Models\History;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Project;
use App\Enums\InventoryStatus;
use App\Events\HistoryEntryCreated;
use App\Events\ApprovalActionTaken;
use Illuminate\Support\Facades\DB;

class ApprovalManagement extends Component
{
    public $approvals;
    public $pendingCount = 0;
    public $approvedCount = 0;
    public $declinedCount = 0;      
    public $filter = 'pending'; // pending, approved, declined, all
    
    public $selectedApproval = null;
    public $reviewNotes = '';
    public $showReviewModal = false;

    protected $listeners = ['approvalUpdated' => 'loadApprovals'];

    public function mount()
    {
        $this->ensureSystemAdmin();
        $this->loadApprovals();
    }

    protected function ensureSystemAdmin()
    {
        $user = auth()->user();
        if ($user->role !== 'system_admin') {
            abort(403, 'Unauthorized access.');
        }
    }

    public function loadApprovals()
    {
        $query = MaterialReleaseApproval::with(['requester', 'inventory', 'reviewer', 'chat'])
            ->latest();

        if ($this->filter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($this->filter === 'approved') {
            $query->where('status', 'approved');
        } elseif ($this->filter === 'declined') {
            $query->where('status', 'declined');
        }

        $this->approvals = $query->get();

        // Update counts
        $this->pendingCount = MaterialReleaseApproval::where('status', 'pending')->count();
        $this->approvedCount = MaterialReleaseApproval::where('status', 'approved')->count();
        $this->declinedCount = MaterialReleaseApproval::where('status', 'declined')->count();
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->loadApprovals();
    }

    public function openReviewModal($approvalId)
    {
        $this->selectedApproval = MaterialReleaseApproval::with(['requester', 'inventory', 'chat'])
            ->findOrFail($approvalId);
        $this->reviewNotes = '';
        $this->showReviewModal = true;
    }

    public function closeReviewModal()
    {
        $this->showReviewModal = false;
        $this->selectedApproval = null;
        $this->reviewNotes = '';
    }

    public function approveRequest($approvalId, $notes = null)
    {
        $this->ensureSystemAdmin();
        
        $approval = MaterialReleaseApproval::with(['requester', 'inventory', 'chat'])->findOrFail($approvalId);
        
        if (!$approval->isPending()) {
            session()->flash('error', 'This request has already been reviewed.');
            return;
        }

        DB::beginTransaction();
        try {
            // IMPORTANT: Capture requester info BEFORE any modifications
            // This ensures we preserve the original requester's name
            $requesterName = $approval->requester?->name;
            $requesterId = $approval->requested_by;
            $approverName = auth()->user()->name;
            $approverId = auth()->id();

            // Approve the request
            $approval->approve($approverId, $notes ?: $this->reviewNotes);

            // Get inventory details before processing
            $inventory = $approval->inventory;
            $previousQuantity = $inventory->quantity;
            $previousStatus = $inventory->status;

            // Update existing history entry or create new
            $existingHistory = History::where('model', 'MaterialReleaseApproval')
                ->where('model_id', $approval->id)
                ->first();

            // Prepare comprehensive approval data with explicit requester/approver separation
            $approvalData = [
                // Release identification
                'release_type' => 'approval_based_release',
                'approval_id' => $approval->id,
                
                // Approval workflow information
                'status' => 'approved',
                'approval_status' => 'approved',
                
                // Client and Project information
                'client' => $approval->client ?? 'N/A',
                'client_name' => $approval->client ?? 'N/A',
                'project' => $approval->project ?? 'N/A',
                'project_name' => $approval->project ?? 'N/A',
                
                // Material/Inventory information
                'material' => $inventory->material_name,
                'material_brand' => $inventory->brand,
                'material_description' => $inventory->description,
                'material_category' => $inventory->category,
                
                // Quantity information
                'quantity' => $approval->quantity_requested,
                'quantity_requested' => $approval->quantity_requested,
                
                // User information - EXPLICIT separation of requester and approver
                'requester' => $requesterName,
                'requested_by' => $requesterName,
                'requested_by_id' => $requesterId,
                'reviewer' => $approverName,
                'approved_by' => $approverName,
                'approved_by_id' => $approverId,
                
                // Review details
                'review_notes' => $notes ?: $this->reviewNotes,
                'reason' => $approval->reason,
                
                // Timestamps
                'requested_at' => $approval->created_at?->toDateTimeString(),
                'completed_at' => now()->toDateTimeString(),
                'reviewed_at' => now()->toDateTimeString(),
            ];

            if ($existingHistory) {
                // Preserve existing changes and add completion details
                $existingChanges = is_array($existingHistory->changes) ? $existingHistory->changes : json_decode($existingHistory->changes ?? '[]', true);
                
                // Merge existing data with completion data
                $updatedChanges = array_merge($existingChanges, $approvalData);

                $existingHistory->update([
                    'action' => 'Approval Request Approved',
                    'changes' => $updatedChanges,
                    'old_values' => [
                        'status' => 'pending',
                        'inventory_quantity' => $previousQuantity,
                        'inventory_status' => $previousStatus,
                    ],
                ]);
                $approvalHistory = $existingHistory;
            } else {
                // Create new if not found
                $approvalHistory = History::create([
                    'user_id' => $approval->requested_by,
                    'action' => 'Approval Request Approved',
                    'model' => 'MaterialReleaseApproval',
                    'model_id' => $approval->id,
                    'changes' => $approvalData,
                    'old_values' => [
                        'status' => 'pending',
                        'inventory_quantity' => $previousQuantity,
                        'inventory_status' => $previousStatus,
                    ],
                ]);
            }

            // Process the material release
            $this->processApprovedRelease($approval, $notes ?: $this->reviewNotes);

            // Broadcast history event
            event(new HistoryEntryCreated($approvalHistory));

            // Notification removed - using message boxes instead

            // Send message to requester via chat
            if ($approval->chat) {
                Chat::create([
                    'user_id' => auth()->id(),
                    'recipient_id' => $approval->requested_by,
                    'message' => "✅ Your material release request has been APPROVED!\n\nItem: {$approval->inventory->brand} - {$approval->inventory->description}\nQuantity: {$approval->quantity_requested}" . (($notes ?: $this->reviewNotes) ? "\n\nNotes: " . ($notes ?: $this->reviewNotes) : ''),
                ]);
            }

            DB::commit();

            $this->closeReviewModal();
            $this->loadApprovals();
            $this->dispatch('approvalUpdated');
            
            session()->flash('message', 'Request approved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    public function declineRequest($approvalId, $notes = null)
    {
        $this->ensureSystemAdmin();
        
        $approval = MaterialReleaseApproval::with(['requester', 'inventory', 'chat'])->findOrFail($approvalId);
        
        if (!$approval->isPending()) {
            session()->flash('error', 'This request has already been reviewed.');
            return;
        }

        DB::beginTransaction();
        try {
            // IMPORTANT: Capture requester info BEFORE any modifications
            // This ensures we preserve the original requester's name
            $requesterName = $approval->requester?->name;
            $requesterId = $approval->requested_by;
            $declinerName = auth()->user()->name;
            $declinerId = auth()->id();

            // Decline the request
            $approval->decline($declinerId, $notes ?: $this->reviewNotes);

            // Get inventory details
            $inventory = $approval->inventory;

            // Update existing history entry or create new
            $existingHistory = History::where('model', 'MaterialReleaseApproval')
                ->where('model_id', $approval->id)
                ->first();

            // Prepare comprehensive decline data with explicit requester/decliner separation
            $declineData = [
                // Release identification
                'release_type' => 'approval_based_release',
                'approval_id' => $approval->id,
                
                // Approval workflow information
                'status' => 'declined',
                'approval_status' => 'declined',
                
                // Client and Project information
                'client' => $approval->client ?? 'N/A',
                'client_name' => $approval->client ?? 'N/A',
                'project' => $approval->project ?? 'N/A',
                'project_name' => $approval->project ?? 'N/A',
                
                // Material/Inventory information
                'material' => $inventory->material_name,
                'material_brand' => $inventory->brand,
                'material_description' => $inventory->description,
                'material_category' => $inventory->category,
                
                // Quantity information
                'quantity' => $approval->quantity_requested,
                'quantity_requested' => $approval->quantity_requested,
                
                // User information - EXPLICIT separation of requester and decliner
                'requester' => $requesterName,
                'requested_by' => $requesterName,
                'requested_by_id' => $requesterId,
                'reviewer' => $declinerName,
                'declined_by' => $declinerName,
                'declined_by_id' => $declinerId,
                
                // Review details
                'reason' => $notes ?: $this->reviewNotes,
                'decline_reason' => $notes ?: $this->reviewNotes,
                'request_reason' => $approval->reason,
                
                // Timestamps
                'requested_at' => $approval->created_at?->toDateTimeString(),
                'completed_at' => now()->toDateTimeString(),
                'reviewed_at' => now()->toDateTimeString(),
            ];

            if ($existingHistory) {
                // Preserve existing changes and add completion details
                $existingChanges = is_array($existingHistory->changes) ? $existingHistory->changes : json_decode($existingHistory->changes ?? '[]', true);
                
                // Merge existing data with completion data
                $updatedChanges = array_merge($existingChanges, $declineData);

                $existingHistory->update([
                    'action' => 'Approval Request Declined',
                    'changes' => $updatedChanges,
                    'old_values' => [
                        'status' => 'pending',
                    ],
                ]);
                $approvalHistory = $existingHistory;
            } else {
                // Create new if not found
                $approvalHistory = History::create([
                    'user_id' => $approval->requested_by,
                    'action' => 'Approval Request Declined',
                    'model' => 'MaterialReleaseApproval',
                    'model_id' => $approval->id,
                    'changes' => $declineData,
                    'old_values' => [
                        'status' => 'pending',
                    ],
                ]);
            }

            // Broadcast history event
            event(new HistoryEntryCreated($approvalHistory));

            // Notification removed - using message boxes instead

            // Send message to requester via chat
            if ($approval->chat) {
                Chat::create([
                    'user_id' => auth()->id(),
                    'recipient_id' => $approval->requested_by,
                    'message' => "❌ Your material release request has been DECLINED.\n\nItem: {$approval->inventory->brand} - {$approval->inventory->description}\nQuantity: {$approval->quantity_requested}" . (($notes ?: $this->reviewNotes) ? "\n\nReason: " . ($notes ?: $this->reviewNotes) : ''),
                ]);
            }

            DB::commit();

            $this->closeReviewModal();
            $this->loadApprovals();
            $this->dispatch('approvalUpdated');
            
            session()->flash('message', 'Request declined.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to decline request: ' . $e->getMessage());
        }
    }

    public function openChat($chatId)
    {
        // Redirect to chat with this conversation
        return redirect()->route('dashboard')->with(['openChatId' => $chatId]);
    }

    /**
     * Process approved material release for regular users
     */
    protected function processApprovedRelease($approval, $notes = null): void
    {
        try {
            // IMPORTANT: Capture requester info FIRST before any operations
            $requesterName = $approval->requester?->name;
            $requesterId = $approval->requested_by;
            $approverName = auth()->user()->name;
            $approverId = auth()->id();

            // Get the cost per unit from the approval request data
            $costPerUnit = 0.00;

            // Get client and project from approval's stored data or relationships
            $clientId = $approval->client_id ?? null;
            $projectId = $approval->project_id ?? null;
            $clientName = $approval->client ?? 'N/A';
            $projectName = $approval->project ?? 'N/A';
            
            // Create expense record
            $totalCost = round($approval->quantity_requested * $costPerUnit, 2);
            $expense = Expense::create([
                'client_id' => $clientId,
                'project_id' => $projectId,
                'inventory_id' => $approval->inventory_id,
                'quantity_used' => $approval->quantity_requested,
                'cost_per_unit' => $costPerUnit,
                'total_cost' => $totalCost,
                'released_at' => now(),
            ]);

            // Update inventory
            $inventory = $approval->inventory;
            $previousQuantity = $inventory->quantity;
            $newQuantity = $inventory->quantity - $approval->quantity_requested;
            $newStatus = $newQuantity <= 0
                ? InventoryStatus::OUT_OF_STOCK
                : ($newQuantity <= $inventory->min_stock_level
                    ? InventoryStatus::CRITICAL
                    : InventoryStatus::NORMAL);

            $inventory->update([
                'quantity' => max(0, $newQuantity),
                'status' => $newStatus->value,
            ]);

            // Record outbound stock movement
            $notesText = "Approved release - {$approval->reason}";
            $stockMovement = $inventory->recordStockMovement('outbound', $approval->quantity_requested, $requesterId, [
                'reference' => 'expense_' . $expense->id,
                'cost_per_unit' => $costPerUnit,
                'total_cost' => $totalCost,
                'notes' => $notesText,
                'location' => $inventory->category,
            ], $previousQuantity);

            // Create comprehensive Material Release history entry with complete stock movement info
            $history = History::create([
                'user_id' => $requesterId,
                'action' => 'Material Release Completed',
                'model' => 'MaterialReleaseApproval',
                'model_id' => $approval->id,
                'changes' => [
                    // Release type
                    'release_type' => 'approval_based_release',
                    
                    // Status (completed/approved)
                    'status' => 'approved',
                    'approval_status' => 'approved',
                    
                    // Client and Project
                    'client' => $clientName,
                    'client_name' => $clientName,
                    'project' => $projectName,
                    'project_name' => $projectName,
                    
                    // Material information
                    'inventory_id' => $inventory->id,
                    'material_brand' => $inventory->brand,
                    'material_description' => $inventory->description,
                    'material_category' => $inventory->category,
                    
                    // Quantity information
                    'quantity' => $approval->quantity_requested,
                    'quantity_released' => $approval->quantity_requested,
                    
                    // Stock movement details
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => max(0, $newQuantity),
                    'cost_per_unit' => $costPerUnit,
                    'total_cost' => $totalCost,
                    'stock_movement_id' => $stockMovement->id,
                    
                    // User information
                    'requested_by' => $requesterName,
                    'requested_by_id' => $requesterId,
                    'approved_by' => $approverName,
                    'approved_by_id' => $approverId,
                    
                    // Timestamps
                    'requested_at' => $approval->created_at?->toDateTimeString(),
                    'completed_at' => now()->toDateTimeString(),
                    'reviewed_at' => now()->toDateTimeString(),
                    
                    // Additional details
                    'reason' => $approval->reason,
                    'review_notes' => $notes,
                ],
                'old_values' => [
                    'quantity' => $previousQuantity,
                    'status' => 'pending',
                ],
            ]);

            // Update approval with expense_id
            $approval->update(['expense_id' => $expense->id]);

            // Broadcast history event
            event(new HistoryEntryCreated($history));

            // Broadcast real-time approval event
            event(new ApprovalActionTaken($approval, 'approved'));

            \Log::info('Regular user material release processed for approval ID: ' . $approval->id);

        } catch (\Exception $e) {
            \Log::error('Failed to process approved release: ' . $e->getMessage());
            throw $e; // Re-throw to trigger rollback
        }
    }

    public function render()
    {
        return view('livewire.approval-management');
    }
}
