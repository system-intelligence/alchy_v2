<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Chat channels for real-time messaging
Broadcast::channel('chat.{userId1}.{userId2}', function ($user, $userId1, $userId2) {
    return $user->id == $userId1 || $user->id == $userId2;
});

// Group chat channel - all authenticated users can subscribe
Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    // Allow all authenticated users to subscribe to the group chat
    return ['id' => $user->id, 'name' => $user->name];
});

// Presence channel for online users tracking
Broadcast::channel('online', function ($user) {
    if (auth()->check()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
        ];
    }
});
