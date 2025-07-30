<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RaceStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lobbyKey;
    public $players;
    public $countdown;

    public function __construct($lobbyKey, $players, $countdown = null)
    {
        $this->lobbyKey = $lobbyKey;
        $this->players = $players;
        $this->countdown = $countdown;
    }

    public function broadcastOn()
    {
        return new Channel('lobby.' . $this->lobbyKey);
    }

    public function broadcastAs()
    {
        return 'race.started';
    }

    public function broadcastWith()
    {
        return [
            'lobby_key' => $this->lobbyKey,
            'players' => $this->players,
            'countdown' => $this->countdown,
            'timestamp' => now()->toISOString()
        ];
    }
}
