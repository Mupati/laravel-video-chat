<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('presence-video-channel', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('wossop-channel', function ($user) {
    // When a user subscribes to this channel it means they've logged in
    // us get their last login time and maybe device details
    return ['id' => $user->id, 'name' => $user->name, 'last_online' => $user->last_login_at];
});

Broadcast::channel('agora-online-channel', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('private-chat-channel.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
