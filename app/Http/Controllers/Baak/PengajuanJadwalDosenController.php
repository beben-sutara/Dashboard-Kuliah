<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Models\LaporanKehadiran;
use App\Models\PengajuanJadwalDosen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengajuanJadwalDosenController extends Controller
{
    public function index()
    {
        $pengajuan = PengajuanJadwalDosen::with([
            'dosen',
            'jadwal.matakuliah',
            'jadwal.ruangan',
            'ruanganPengganti',
            'reviewer',
        ])
            ->latest()
            ->paginate(15, ['*'], 'pengajuan_page');

        $laporanMahasiswa = LaporanKehadiran::with([
            'mahasiswa',
            'jadwal.dosen',
            'jadwal.matakuliah',
            'jadwal.ruangan',
            'reviewer',
        ])
            ->latest('tanggal')
            ->latest()
            ->paginate(15, ['*'], 'laporan_page');

        $stats = [
            'pending' => PengajuanJadwalDosen::where('status', PengajuanJadwalDosen::STATUS_PENDING)->count(),
            'disetujui' => PengajuanJadwalDosen::where('status', PengajuanJadwalDosen::STATUS_DISETUJUI)->count(),
            'ditolak' => PengajuanJadwalDosen::where('status', PengajuanJadwalDosen::STATUS_DITOLAK)->count(),
        ];

        $laporanStats = [
            'pending' => LaporanKehadiran::where('status_validasi', LaporanKehadiran::STATUS_PENDING)->count(),
            'valid' => LaporanKehadiran::where('status_validasi', LaporanKehadiran::STATUS_VALID)->count(),
            'ditolak' => LaporanKehadiran::where('status_validasi', LaporanKehadiran::STATUS_DITOLAK)->count(),
        ];

        return view('baak.pengajuan-dosen.index', compact('pengajuan', 'stats', 'laporanMahasiswa', 'laporanStats'));
    }

    public function update(Request $request, PengajuanJadwalDosen $pengajuanJadwalDosen)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                PengajuanJadwalDosen::STATUS_DISETUJUI,
                PengajuanJadwalDosen::STATUS_DITOLAK,
            ])],
            'catatan_baak' => ['nullable', 'string', 'max:1000'],
        ]);

        $pengajuanJadwalDosen->update([
            'status' => $validated['status'],
            'catatan_baak' => $validated['catatan_baak'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('baak.pengajuan-dosen.index')->with('success', 'Status pengajuan dosen berhasil diperbarui.');
    }

    public function updateLaporan(Request $request, LaporanKehadiran $laporanKehadiran)
    {
        $validated = $request->validate([
            'status_validasi' => ['required', Rule::in([
                LaporanKehadiran::STATUS_VALID,
                LaporanKehadiran::STATUS_DITOLAK,
            ])],
            'catatan_baak' => ['nullable', 'string', 'max:1000'],
        ]);

        $laporanKehadiran->update([
            'status_validasi' => $validated['status_validasi'],
            'catatan_baak' => $validated['catatan_baak'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('baak.pengajuan-dosen.index')->with('success', 'Laporan mahasiswa berhasil ditinjau.');
    }
}
