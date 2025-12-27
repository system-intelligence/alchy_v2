<?php

namespace App\Events;

use App\Models\MaterialReleaseApproval;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalRequestCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $approval;

    /**
     * Create a new event instance.
     */
    public function __construct(MaterialReleaseApproval $approval)
    {
        $this->approval = $approval->loadMissing(['requester', 'inventory']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
     public function broadcastOn(): array
     {
         $channels = [];

         // Broadcast to all system admins
         $systemAdmins = \App\Models\User::where('role', 'system_admin')->get();
         foreach ($systemAdmins as $admin) {
             $channels[] = new PrivateChannel('App.Models.User.' . $admin->id);
         }

         return $channels;
     }

    /**
     * Broadcast payload
     */
    public function broadcastWith(): array
    {
        return [
            'approval_id' => $this->approval->id,
            'requester_name' => $this->approval->requester->name,
            'requester_id' => $this->approval->requester->id,
            'inventory_name' => $this->approval->inventory->brand . ' - ' . $this->approval->inventory->description,
            'quantity' => $this->approval->quantity_requested,
            'reason' => $this->approval->reason,
            'chat_id' => $this->approval->chat_id,
            'created_at' => $this->approval->created_at->toISOString(),
            'message' => "{$this->approval->requester->name} is requesting approval to release {$this->approval->quantity_requested} units of {$this->approval->inventory->brand}",
        ];
    }

    /**
     * Event name for frontend
     */
    public function broadcastAs(): string
    {
        return 'ApprovalRequestCreated';
    }
}
