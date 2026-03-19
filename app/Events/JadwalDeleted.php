<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JadwalDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $jadwalId) {}

    public function broadcastOn(): Channel
    {
        return new Channel('jadwal');
    }

    public function broadcastWith(): array
    {
        return [
            'action'    => 'deleted',
            'jadwal_id' => $this->jadwalId,
        ];
    }
}
