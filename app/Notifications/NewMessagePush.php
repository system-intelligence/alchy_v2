<?php

namespace App\Notifications;

use App\Models\Chat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewMessagePush extends Notification implements ShouldQueue
{
    use Queueable;

    public Chat $message;

    public function __construct(Chat $message)
    {
        // Ensure relationships are available for the notification
        $this->message = $message->loadMissing(['user', 'recipient']);
    }

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, object $notification): WebPushMessage
    {
        $senderName = optional($this->message->user)->name ?? 'Someone';
        $icon = asset('images/logos/alchy_logo.png');

        return (new WebPushMessage)
            ->title("New message from {$senderName}")
            ->icon($icon)
            ->body($this->message->message)
            // Tag helps collapse multiple notifications into latest
            ->tag('chat-' . $this->message->id)
            ->data([
                'url' => url('/dashboard'),
                'sender_id' => $this->message->user_id,
                'recipient_id' => $this->message->recipient_id,
                'message_id' => $this->message->id,
            ]);
    }
}