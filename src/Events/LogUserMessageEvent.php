<?php

namespace JaceApp\Jace\Events;

use JaceApp\Jace\Requests\LogMessageRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LogUserMessageEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $type;
    public $uid;
    public $userId;
    public $guestId;
    public $message;
    public $blocks;

    /**
     * Create a new event instance.
     */
    public function __construct(LogMessageRequest $request)
    {
        $this->type = $request['type'];
        $this->uid = $request['uid'];
        $this->userId = $request['userId'];
        $this->guestId = $request['guestId'];
        $this->message = $request['message'];
        $this->blocks = $request['blocks'];
    }
}
