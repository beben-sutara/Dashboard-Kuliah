<?php

namespace App\Events;

use App\Models\JadwalPerkuliahan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JadwalUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public JadwalPerkuliahan $jadwal) {}

    public function broadcastOn(): Channel
    {
        return new Channel('jadwal');
    }

    public function broadcastWith(): array
    {
        return [
            'action' => 'updated',
            'jadwal' => [
                'id'           => $this->jadwal->id,
                'matakuliah'   => $this->jadwal->matakuliah?->nama,
                'dosen'        => $this->jadwal->dosen?->nama,
                'ruangan'      => $this->jadwal->ruangan?->kode . ' - ' . $this->jadwal->ruangan?->nama,
                'prodi'        => $this->jadwal->prodi,
                'semester'     => $this->jadwal->semester,
                'hari'         => $this->jadwal->hari,
                'waktu_mulai'  => $this->jadwal->waktu_mulai,
                'waktu_selesai'=> $this->jadwal->waktu_selesai,
            ],
        ];
    }
}
