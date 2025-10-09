<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Chat $message)
    {
        // Ensure relations are available to the frontend listener when broadcasting
        $this->message = $message->loadMissing(['user', 'recipient']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Broadcast to the conversation channel and to each user's private channel
        $channels = [];

        // Consistent conversation channel (private)
        $userIds = [$this->message->user_id, $this->message->recipient_id];
        sort($userIds);
        $channelName = 'chat.' . implode('.', $userIds);
        $channels[] = new PrivateChannel($channelName);

        // Private, per-user channels for badges/notifications
        $channels[] = new PrivateChannel('App.Models.User.' . $this->message->user_id);
        $channels[] = new PrivateChannel('App.Models.User.' . $this->message->recipient_id);

        return $channels;
    }

    /**
     * Minify payload for the frontend and include sender metadata
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'user_id' => $this->message->user_id,
                'recipient_id' => $this->message->recipient_id,
                'message' => $this->message->message,
                'created_at' => optional($this->message->created_at)->toISOString(),
                'user_name' => optional($this->message->user)->name,
            ],
        ];
    }

    /**
     * Explicit event name so frontend can listen('MessageSent')
     */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}
