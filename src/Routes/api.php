<?php

use Illuminate\Support\Facades\Route;
use JaceApp\Jace\Http\Controllers\Api\ChatController;

Route::middleware(['api'])->group(function () {
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/api/chat/start-chat',  [ChatController::class, 'startChat'])->name('yace.chat.start-chat');
        Route::middleware(['check.banned'])->group(function() {
            Route::post('/api/chat/send-message',  [ChatController::class, 'sendMessage'])->name('yace.chat.send-message');
        });
    });
});
