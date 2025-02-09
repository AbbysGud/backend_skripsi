<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WeightEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $weight;
    public $message;
    public $mode;
    public $rfid;

    public function __construct($weight, $message, $mode, $rfid)
    {
        $this->weight = $weight;
        $this->message = $message;
        $this->mode = $mode;
        $this->rfid = $rfid;
    }

    public function broadcastOn()
    {
        return new Channel('weight-channel');
    }

    public function broadcastAs()
    {
        return 'WeightEvent';
    }
}
