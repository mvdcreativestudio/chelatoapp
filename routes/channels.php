<?php

use App\Models\User;
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

Broadcast::channel('store.{storeId}', function ($user, $storeId) {
  return $user->store_id == $storeId;
});


/**
 * Obtiene los canales a través de los cuales se transmitirá el evento.
 *
 * @param User $user
 * @param string $phoneId
 * @return \Illuminate\Broadcasting\Channel|array
*/
Broadcast::channel('messages.{phoneId}', function (User $user, string $phoneId): bool {
  return $user->store->phoneNumber->phone_id === $phoneId;
});
