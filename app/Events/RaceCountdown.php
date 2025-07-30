<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RaceCountdown implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lobbyKey;
    public $count;

    public function __construct($lobbyKey, $count)
    {
        $this->lobbyKey = $lobbyKey;
        $this->count = $count;
    }

    public function broadcastOn()
    {
        return new Channel('lobby.' . $this->lobbyKey);
    }

    public function broadcastAs()
    {
        return 'race.countdown';
    }

    public function broadcastWith()
    {
        return [
            'lobby_key' => $this->lobbyKey,
            'count' => $this->count,
            'timestamp' => now()->toISOString()
        ];
    }
}
