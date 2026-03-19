<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Models\JadwalPerkuliahan;
use App\Models\MasterDosen;
use App\Models\MasterKelas;
use App\Models\MasterMatakuliah;
use App\Models\MasterRuangan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
            ->orderedWeekly()
            ->get();

        $dosen = MasterDosen::orderBy('nama')->get();
        $matakuliah = MasterMatakuliah::orderByRaw("CASE WHEN kode IS NULL OR kode = '' THEN 1 ELSE 0 END")
            ->orderBy('kode')
            ->orderBy('nama')
            ->get();
        $kelas = MasterKelas::orderBy('semester')
            ->orderBy('nama')
            ->get();
        $ruangan = MasterRuangan::orderBy('kode')->get();

        $stats = [
            'jadwal' => JadwalPerkuliahan::count(),
            'dosen' => MasterDosen::count(),
            'matakuliah' => MasterMatakuliah::count(),
            'ruangan' => MasterRuangan::count(),
        ];

        return view('baak.dashboard', compact('jadwal', 'dosen', 'matakuliah', 'kelas', 'ruangan', 'stats'));
    }
}
