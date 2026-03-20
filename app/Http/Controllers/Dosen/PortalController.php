<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\JadwalPerkuliahan;
use App\Models\MasterRuangan;
use App\Models\PengajuanJadwalDosen;

class PortalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $masterDosen = $user->masterDosen;
        $hariOrder = JadwalPerkuliahan::HARI_ORDER;
        $hariIni = JadwalPerkuliahan::hariSekarang();
        $jadwalMingguanKosong = collect($hariOrder)->mapWithKeys(fn ($hari) => [$hari => collect()]);

        if (!$masterDosen) {
            return view('dosen.portal', [
                'jadwal'      => collect(),
                'kelasHariIni' => collect(),
                'jadwalMingguan' => $jadwalMingguanKosong,
                'masterDosen' => null,
                'hariIni' => $hariIni,
                'pengajuanTerbaru' => collect(),
                'permintaanPending' => 0,
                'ruangan' => collect(),
            ]);
        }

        $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan'])
            ->forDosen($masterDosen->id)
            ->forSemesterAktif()
            ->orderedWeekly()
            ->get();

        $jadwalMingguan = collect($hariOrder)
            ->mapWithKeys(fn ($hari) => [$hari => $jadwal->where('hari', $hari)->values()]);

        $kelasHariIni = $jadwalMingguan->get($hariIni, collect());

        $pengajuanTerbaru = PengajuanJadwalDosen::with(['jadwal.matakuliah', 'jadwal.ruangan', 'ruanganPengganti'])
            ->where('dosen_id', $masterDosen->id)
            ->latest()
            ->take(5)
            ->get();

        $permintaanPending = PengajuanJadwalDosen::where('dosen_id', $masterDosen->id)
            ->where('status', PengajuanJadwalDosen::STATUS_PENDING)
            ->count();

        $ruangan = MasterRuangan::orderBy('kode')->get();

        return view('dosen.portal', compact(
            'jadwal',
            'kelasHariIni',
            'jadwalMingguan',
            'masterDosen',
            'hariIni',
            'pengajuanTerbaru',
            'permintaanPending',
            'ruangan'
        ));
    }
}
