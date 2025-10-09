<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationToast extends Component
{
    public $notifications = [];

    protected $listeners = ['showNotification' => 'addNotification', 'browserNotification' => 'showBrowserNotification'];

    public function addNotification($message, $type = 'info', $duration = 5000)
    {
        $id = uniqid();
        $this->notifications[] = [
            'id' => $id,
            'message' => $message,
            'type' => $type,
            'duration' => $duration,
        ];
    }

    public function removeNotification($id)
    {
        $this->notifications = array_filter($this->notifications, function ($notification) use ($id) {
            return $notification['id'] !== $id;
        });
    }

    public function showBrowserNotification($data)
    {
        $this->dispatch('triggerBrowserNotification', $data['message'], $data['senderName']);
    }

    public function render()
    {
        return view('livewire.notification-toast');
    }
}
