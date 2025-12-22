<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MaterialReleaseApproval;
use App\Models\User;
use App\Models\Chat;
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
        if ($user->role !== 'system_admin' && !$user->isDeveloper()) {
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

    public function render()
    {
        return view('livewire.approval-management');
    }
}
