<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Chat channels for real-time messaging
Broadcast::channel('chat.{userId1}.{userId2}', function ($user, $userId1, $userId2) {
    return $user->id == $userId1 || $user->id == $userId2;
});
