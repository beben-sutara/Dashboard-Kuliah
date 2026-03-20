<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Models\JadwalPerkuliahan;
use App\Models\LaporanKehadiran;
use App\Models\MasterDosen;
use App\Models\MasterRuangan;
use App\Models\MasterMatakuliah;
use App\Models\MasterKelas;
use App\Models\KrsMahasiswa;
use Carbon\Carbon;

class StatistikController extends Controller
{
    public function index()
    {
        $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])->get();

        // ── Summary cards ──
        $summary = (object) [
            'totalJadwal'    => $jadwal->count(),
            'totalDosen'     => MasterDosen::count(),
            'totalMatakuliah'=> MasterMatakuliah::count(),
            'totalRuangan'   => MasterRuangan::count(),
            'totalKelas'     => MasterKelas::count(),
            'totalKrs'       => KrsMahasiswa::where('status', 'aktif')->count(),
            'totalSks'       => $jadwal->sum(fn($j) => $j->matakuliah->sks ?? 0),
        ];

        // ── 1. Distribusi Jadwal per Hari (Bar chart) ──
        $hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $jadwalPerHari = [];
        foreach ($hariOrder as $h) {
            $jadwalPerHari[$h] = $jadwal->where('hari', $h)->count();
        }

        // ── 2. Beban Mengajar Dosen — top 10 (Horizontal bar) ──
        $bebanDosen = $jadwal->groupBy('dosen_id')->map(function ($items) {
            $dosen = $items->first()->dosen;
            return [
                'nama'     => $dosen->nama ?? 'N/A',
                'jumlah'   => $items->count(),
                'totalJam' => $items->sum(function ($j) {
                    $mulai = Carbon::createFromTimeString($j->waktu_mulai);
                    $selesai = Carbon::createFromTimeString($j->waktu_selesai);
                    return $selesai->diffInMinutes($mulai) / 60;
                }),
                'totalSks' => $items->sum(fn($j) => $j->matakuliah->sks ?? 0),
            ];
        })->sortByDesc('jumlah')->take(10)->values();

        // ── 3. Utilisasi Ruangan (Doughnut) ──
        $ruanganSemua = MasterRuangan::count();
        $ruanganTerpakai = $jadwal->pluck('ruangan_id')->unique()->count();
        $ruanganKosong = $ruanganSemua - $ruanganTerpakai;

        // Slot utilization per room (count how many schedule slots each room has)
        $utilisasiRuangan = $jadwal->groupBy('ruangan_id')->map(function ($items) {
            $ruangan = $items->first()->ruangan;
            return [
                'kode'   => $ruangan->kode ?? 'N/A',
                'nama'   => $ruangan->nama ?? '',
                'jumlah' => $items->count(),
                'jamTotal' => $items->sum(function ($j) {
                    $mulai = Carbon::createFromTimeString($j->waktu_mulai);
                    $selesai = Carbon::createFromTimeString($j->waktu_selesai);
                    return $selesai->diffInMinutes($mulai) / 60;
                }),
            ];
        })->sortByDesc('jumlah')->values();

        // ── 4. Distribusi per Prodi (Pie) ──
        $jadwalPerProdi = $jadwal->groupBy('prodi')->map(fn($items, $key) => [
            'prodi'  => $key ?: 'Belum Diset',
            'jumlah' => $items->count(),
        ])->values();

        // ── 5. Distribusi Waktu / Time Slot Heatmap ──
        $timeSlots = [];
        foreach ($jadwal as $j) {
            $jamMulai = (int) substr($j->waktu_mulai, 0, 2);
            $key = sprintf('%02d:00', $jamMulai);
            $timeSlots[$j->hari][$key] = ($timeSlots[$j->hari][$key] ?? 0) + 1;
        }

        // ── 6. Distribusi SKS per Mata Kuliah ──
        $sksDistribusi = $jadwal->groupBy(fn($j) => $j->matakuliah->sks ?? 0)
            ->map(fn($items, $sks) => ['sks' => $sks . ' SKS', 'jumlah' => $items->count()])
            ->sortKeys()
            ->values();

        // ── 7. Laporan Kehadiran Stats ──
        $laporanStats = (object) [
            'total'    => LaporanKehadiran::count(),
            'pending'  => LaporanKehadiran::where('status_validasi', 'pending')->count(),
            'valid'    => LaporanKehadiran::where('status_validasi', 'valid')->count(),
            'ditolak'  => LaporanKehadiran::where('status_validasi', 'ditolak')->count(),
        ];

        return view('baak.statistik', compact(
            'summary',
            'jadwalPerHari',
            'bebanDosen',
            'ruanganSemua',
            'ruanganTerpakai',
            'ruanganKosong',
            'utilisasiRuangan',
            'jadwalPerProdi',
            'timeSlots',
            'hariOrder',
            'sksDistribusi',
            'laporanStats'
        ));
    }
}
