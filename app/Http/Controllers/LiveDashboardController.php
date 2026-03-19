<?php

namespace App\Http\Controllers;

use App\Models\JadwalPerkuliahan;
use Carbon\Carbon;

class LiveDashboardController extends Controller
{
    private array $hariMap = [
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu',
        'Sunday'    => 'Minggu',
    ];

    public function index()
    {
        [$jadwalHariIni, $berlangsung, $akanDatang, $selesai, $hariIni] = $this->buildDashboardData();

        return view('live.dashboard', compact('jadwalHariIni', 'berlangsung', 'akanDatang', 'selesai', 'hariIni'));
    }

    /** JSON endpoint untuk polling AJAX */
    public function data()
    {
        $now = Carbon::now();
        [$jadwalHariIni, $berlangsung, $akanDatang, $selesai, $hariIni] = $this->buildDashboardData($now);

        return response()->json([
            'hari_ini'    => $hariIni,
            'tanggal'     => $now->locale('id')->isoFormat('dddd, D MMMM Y'),
            'server_time' => $now->format('H:i:s'),
            'jadwal_hari_ini' => $jadwalHariIni->values(),
            'berlangsung' => $berlangsung->values(),
            'akan_datang' => $akanDatang->values(),
            'selesai'     => $selesai->values(),
        ]);
    }

    private function buildDashboardData(?Carbon $now = null): array
    {
        $now ??= Carbon::now();
        $hariIni = $this->hariMap[$now->format('l')] ?? '';

        $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
            ->forHari($hariIni)
            ->orderBy('waktu_mulai')
            ->get()
            ->values()
            ->map(fn ($item, $index) => $this->serializeJadwal($item, $now, $index + 1));

        $berlangsung = $jadwal->where('status', 'berlangsung')->values();
        $akanDatang = $jadwal->where('status', 'akan_datang')->values();
        $selesai = $jadwal->where('status', 'selesai')->values();

        return [$jadwal, $berlangsung, $akanDatang, $selesai, $hariIni];
    }

    private function serializeJadwal(JadwalPerkuliahan $jadwal, Carbon $now, int $nomor): array
    {
        $status = 'akan_datang';
        $statusLabel = 'Akan Datang';
        $rowClass = 'bg-white';

        if ($this->isBerlangsung($jadwal, $now)) {
            $status = 'berlangsung';
            $statusLabel = 'Berlangsung';
            $rowClass = 'bg-emerald-50';
        } elseif ($this->isSelesai($jadwal, $now)) {
            $status = 'selesai';
            $statusLabel = 'Selesai';
            $rowClass = 'bg-slate-50';
        }

        return [
            'id' => $jadwal->id,
            'no' => $nomor,
            'smt' => $jadwal->kelas?->semester ?: ($jadwal->semester ?: '—'),
            'hari' => $jadwal->hari,
            'waktu' => substr((string) $jadwal->waktu_mulai, 0, 5) . ' - ' . substr((string) $jadwal->waktu_selesai, 0, 5),
            'ruang' => $jadwal->ruangan?->kode ?? '—',
            'kode' => $jadwal->matakuliah?->kode ?? '—',
            'matakuliah' => $jadwal->matakuliah?->nama ?? '—',
            'kelas' => $jadwal->kelas?->nama,
            'sks' => $jadwal->matakuliah?->sks ?? '—',
            'dosen' => $jadwal->dosen?->nama ?? '—',
            'status' => $status,
            'status_label' => $statusLabel,
            'row_class' => $rowClass,
        ];
    }

    private function isBerlangsung($j, Carbon $now): bool
    {
        $start = Carbon::createFromTimeString($j->waktu_mulai);
        $end   = Carbon::createFromTimeString($j->waktu_selesai);
        return $now->between($start, $end);
    }

    private function isAkanDatang($j, Carbon $now): bool
    {
        return Carbon::createFromTimeString($j->waktu_mulai)->gt($now);
    }

    private function isSelesai($j, Carbon $now): bool
    {
        return Carbon::createFromTimeString($j->waktu_selesai)->lt($now);
    }
}
