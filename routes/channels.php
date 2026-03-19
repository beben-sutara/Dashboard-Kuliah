<?php

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

// Public channels (no auth needed for jadwal broadcast)
Broadcast::channel('jadwal', function () {
    return true;
});

Broadcast::channel('baak-notifications', function ($user) {
    return $user->role === 'baak';
});
