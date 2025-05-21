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

Broadcast::channel('news.{newsId}', function ($user, $newsId) {
    // Здесь можно добавить дополнительную логику авторизации
    // Например, проверку прав доступа к новости
    return true; // Пока разрешаем всем авторизованным пользователям
});