<?php

namespace App\Events;

use App\Models\MaterialReleaseApproval;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalActionTaken implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $approval;
    public $action; // 'approved' or 'declined'
    public $reviewerId;
    public $reviewerName;
    public $requesterId;

    /**
     * Create a new event instance.
     */
    public function __construct(MaterialReleaseApproval $approval, string $action)
    {
        $this->approval = $approval;
        $this->action = $action;
        $this->reviewerId = $approval->reviewed_by;
        $this->reviewerName = $approval->reviewer->name ?? 'System Admin';
        $this->requesterId = $approval->requested_by;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        // Broadcast to the requester and all admins
        $channels = [
            new PrivateChannel('App.Models.User.' . $this->requesterId),
        ];

        // Also broadcast to all system admins and developers
        $admins = User::whereIn('role', ['system_admin', 'developer'])->get();
        foreach ($admins as $admin) {
            $channels[] = new PrivateChannel('App.Models.User.' . $admin->id);
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'approval_id' => $this->approval->id,
            'action' => $this->action,
            'reviewer_id' => $this->reviewerId,
            'reviewer_name' => $this->reviewerName,
            'requester_id' => $this->requesterId,
            'material_name' => $this->approval->inventory->material_name ?? 'Unknown',
            'quantity' => $this->approval->quantity_requested,
            'status' => $this->approval->status,
            'message' => $this->action === 'approved' 
                ? "Your material release request has been approved by {$this->reviewerName}!"
                : "Your material release request has been declined by {$this->reviewerName}.",
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'approval.action';
    }
}
