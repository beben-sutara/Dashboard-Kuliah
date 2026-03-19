<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LaporanThresholdReached implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $jadwalId,
        public string $matakuliah,
        public string $dosen,
        public string $tanggal,
        public int $jumlahLaporan
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('baak-notifications');
    }

    public function broadcastWith(): array
    {
        return [
            'jadwal_id'      => $this->jadwalId,
            'matakuliah'     => $this->matakuliah,
            'dosen'          => $this->dosen,
            'tanggal'        => $this->tanggal,
            'jumlah_laporan' => $this->jumlahLaporan,
            'message'        => "⚠️ Dosen {$this->dosen} dilaporkan tidak hadir pada mata kuliah {$this->matakuliah} ({$this->jumlahLaporan} laporan masuk).",
        ];
    }
}
