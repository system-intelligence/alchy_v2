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
            // Approve the request
            $approval->approve(auth()->id(), $notes ?: $this->reviewNotes);

            // Update existing history entry or create new
            $existingHistory = History::where('model', 'MaterialReleaseApproval')
                ->where('model_id', $approval->id)
                ->first();

            if ($existingHistory) {
                // Preserve existing changes and add completion details
                $existingChanges = json_decode($existingHistory->changes, true) ?? [];
                $completionData = [
                    'status' => 'approved',
                    'project' => $approval->project ?? 'N/A',
                    'client' => $approval->client ?? 'N/A',
                    'material' => $approval->inventory->material_name,
                    'quantity' => $approval->quantity_requested,
                    'reviewer' => auth()->user()->name,
                    'completed_at' => now()->toDateTimeString(),
                ];

                // Merge existing data with completion data
                $updatedChanges = array_merge($existingChanges, $completionData);

                $existingHistory->update([
                    'action' => 'Approval Request Approved',
                    'changes' => json_encode($updatedChanges),
                ]);
                $approvalHistory = $existingHistory;
            } else {
                // Create new if not found
                $approvalHistory = History::create([
                    'user_id' => $approval->requested_by,
                    'action' => 'Approval Request Approved',
                    'model' => 'MaterialReleaseApproval',
                    'model_id' => $approval->id,
                    'changes' => json_encode([
                        'status' => 'approved',
                        'project' => $approval->project ?? 'N/A',
                        'client' => $approval->client ?? 'N/A',
                        'material' => $approval->inventory->material_name,
                        'quantity' => $approval->quantity_requested,
                        'reviewer' => auth()->user()->name,
                        'completed_at' => now()->toDateTimeString(),
                    ]),
                    'old_values' => json_encode([
                        'status' => 'pending'
                    ])
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
            // Decline the request
            $approval->decline(auth()->id(), $notes ?: $this->reviewNotes);

            // Update existing history entry or create new
            $existingHistory = History::where('model', 'MaterialReleaseApproval')
                ->where('model_id', $approval->id)
                ->first();

            if ($existingHistory) {
                // Preserve existing changes and add completion details
                $existingChanges = json_decode($existingHistory->changes, true) ?? [];
                $completionData = [
                    'status' => 'declined',
                    'project' => $approval->project ?? 'N/A',
                    'client' => $approval->client ?? 'N/A',
                    'material' => $approval->inventory->material_name,
                    'quantity' => $approval->quantity_requested,
                    'reviewer' => auth()->user()->name,
                    'reason' => $notes ?: $this->reviewNotes,
                    'completed_at' => now()->toDateTimeString(),
                ];

                // Merge existing data with completion data
                $updatedChanges = array_merge($existingChanges, $completionData);

                $existingHistory->update([
                    'action' => 'Approval Request Declined',
                    'changes' => json_encode($updatedChanges),
                ]);
                $approvalHistory = $existingHistory;
            } else {
                // Create new if not found
                $approvalHistory = History::create([
                    'user_id' => $approval->requested_by,
                    'action' => 'Approval Request Declined',
                    'model' => 'MaterialReleaseApproval',
                    'model_id' => $approval->id,
                    'changes' => json_encode([
                        'status' => 'declined',
                        'project' => $approval->project ?? 'N/A',
                        'client' => $approval->client ?? 'N/A',
                        'material' => $approval->inventory->material_name,
                        'quantity' => $approval->quantity_requested,
                        'reviewer' => auth()->user()->name,
                        'reason' => $notes ?: $this->reviewNotes,
                        'completed_at' => now()->toDateTimeString(),
                    ]),
                    'old_values' => json_encode([
                        'status' => 'pending'
                    ])
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
            // Get the cost per unit from the approval request data
            // Since we don't have it stored, we'll use a default or try to get it from the request
            $costPerUnit = 0.00; // Default, could be enhanced to store this in approval

            // Create expense record
            $totalCost = round($approval->quantity_requested * $costPerUnit, 2);
            $expense = Expense::create([
                'client_id' => null, // We don't have client_id in approval, could be enhanced
                'project_id' => null, // We don't have project_id in approval, could be enhanced
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
            $notesText = "Approved material release - {$approval->reason}";
            $inventory->recordStockMovement('outbound', $approval->quantity_requested, $approval->requested_by, [
                'reference' => 'expense_' . $expense->id,
                'notes' => $notesText,
            ], $previousQuantity);

            // Create history entry for the completed material release
            $changes = [
                'status' => 'approved',
                'material' => $inventory->material_name,
                'material_details' => $inventory->brand . ' ' . $inventory->description,
                'quantity' => $approval->quantity_requested,
                'cost_per_unit' => $costPerUnit,
                'total_cost' => $totalCost,
                'reason' => $approval->reason,
                'approved_by' => auth()->user()->name,
                'completed_at' => now()->toDateTimeString(),
                'auto_approved' => false, // Regular approval, not auto-approved
                'approved_by_self' => false,
            ];

            $history = History::create([
                'user_id' => $approval->requested_by,
                'action' => 'Material Release Completed',
                'model' => 'MaterialReleaseApproval',
                'model_id' => $approval->id,
                'changes' => json_encode($changes),
                'old_values' => json_encode([
                    'status' => 'pending'
                ]),
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
