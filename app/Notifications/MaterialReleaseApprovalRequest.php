<?php

namespace App\Notifications;

use App\Models\MaterialReleaseApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class MaterialReleaseApprovalRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public MaterialReleaseApproval $approval;

    /**
     * Create a new notification instance.
     */
    public function __construct(MaterialReleaseApproval $approval)
    {
        $this->approval = $approval;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'material_release_approval',
            'approval_id' => $this->approval->id,
            'requester_name' => $this->approval->requester->name,
            'inventory_name' => $this->approval->inventory->brand . ' - ' . $this->approval->inventory->description,
            'quantity' => $this->approval->quantity_requested,
            'reason' => $this->approval->reason,
            'chat_id' => $this->approval->chat_id,
            'message' => "{$this->approval->requester->name} is requesting approval to release {$this->approval->quantity_requested} units of {$this->approval->inventory->brand}",
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'material_release_approval',
            'approval_id' => $this->approval->id,
            'requester_name' => $this->approval->requester->name,
            'inventory_name' => $this->approval->inventory->brand . ' - ' . $this->approval->inventory->description,
            'quantity' => $this->approval->quantity_requested,
            'reason' => $this->approval->reason,
            'chat_id' => $this->approval->chat_id,
            'message' => "{$this->approval->requester->name} is requesting approval to release {$this->approval->quantity_requested} units of {$this->approval->inventory->brand}",
        ]);
    }
}