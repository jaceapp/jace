<?php

use App\Http\Controllers\Auth\Socialite\TwitchController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use JaceApp\Jace\Http\Controllers\ChatController;

Route::middleware(['web'])->group(function () {
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/chat/start-chat',  [ChatController::class, 'startChat'])->name('yace.chat.start-chat');
        Route::middleware(['check.banned', 'check.channel.visibility'])->group(function() {
            Route::post('/chat/send-message',  [ChatController::class, 'sendMessage'])->name('yace.chat.send-message');
        });
    });

    Route::get('/auth/twitch/redirect', [TwitchController::class, 'redirect']);
    Route::get('/auth/twitch/callback', [TwitchController::class, 'callback']);
});
