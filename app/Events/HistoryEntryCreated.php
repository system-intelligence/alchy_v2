<?php

namespace App\Events;

use App\Models\History;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HistoryEntryCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $history;

    /**
     * Create a new event instance.
     */
    public function __construct(History $history)
    {
        $this->history = $history;
        \Log::info('HistoryEntryCreated event constructed', [
            'history_id' => $history->id,
            'user_id' => $history->user_id,
            'action' => $history->action,
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        // Broadcast to all authenticated users to update history page
        \Log::info('HistoryEntryCreated broadcasting on channel: history-updates');
        return [
            new Channel('history-updates'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'history_id' => $this->history->id,
            'user_id' => $this->history->user_id,
            'action' => $this->history->action,
            'model' => $this->history->model,
            'model_id' => $this->history->model_id,
            'created_at' => $this->history->created_at->toDateTimeString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'history.created';
    }
}
