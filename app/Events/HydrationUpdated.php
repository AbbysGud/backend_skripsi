<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HydrationUpdated
{
    use InteractsWithSockets, SerializesModels;

    public $hydrationData;

    public function __construct($hydrationData)
    {
        $this->hydrationData = $hydrationData;
    }

    public function broadcastOn()
    {
        return new Channel('hydration');
    }

    public function broadcastAs()
    {
        return 'hydration.updated';
    }
}
