<?php

namespace JaceApp\Jace\Listeners;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use JaceApp\Jace\Models\JaceChatHistory;

class LogUserMessageListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     * TODO: Break down text into blocks (for bold, italic, etc)
     */
    public function handle(object $event): void
    {
        try {
            $chatHistory = new JaceChatHistory;
            $chatHistory->chat_history_uid = $event->uid;
            $chatHistory->user_id = $event->userId;
            $chatHistory->type = $event->type;
            $chatHistory->guest_id = $event->guestId;
            $chatHistory->message = $event->message;
            $chatHistory->blocks = $event->blocks;
            $chatHistory->save();
        } catch (Exception $exception) {
            Log::error('Failed to save chat history', ['exception' => $exception->getMessage()]);
        }
    }
}
