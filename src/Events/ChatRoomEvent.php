<?php

namespace JaceApp\Jace\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatRoomEvent implements ShouldBroadcast
{
    public $type;
    public $data;

    /**
     * Create a new event instance.
     */
    public function __construct(string $type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('chat-room'),
        ];
    }
}
