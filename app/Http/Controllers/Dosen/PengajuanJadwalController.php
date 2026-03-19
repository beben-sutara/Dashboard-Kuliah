<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\JadwalPerkuliahan;
use App\Models\PengajuanJadwalDosen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengajuanJadwalController extends Controller
{
    public function store(Request $request)
    {
        $masterDosen = $request->user()->masterDosen;

        if (!$masterDosen) {
            return redirect()
                ->route('dosen.portal')
                ->with('error', 'Akun Anda belum terhubung ke data dosen. Hubungi BAAK terlebih dahulu.');
        }

        $validated = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwal_perkuliahan,id'],
            'jenis' => ['required', Rule::in([
                PengajuanJadwalDosen::JENIS_LAPOR_ABSEN,
                PengajuanJadwalDosen::JENIS_RESCHEDULE,
            ])],
            'tanggal_kelas' => ['required', 'date', 'after_or_equal:today'],
            'alasan' => ['required', 'string', 'max:1000'],
            'tanggal_pengganti' => [
                Rule::requiredIf(fn () => $request->input('jenis') === PengajuanJadwalDosen::JENIS_RESCHEDULE),
                'nullable',
                'date',
                'after_or_equal:tanggal_kelas',
            ],
            'waktu_mulai_pengganti' => [
                Rule::requiredIf(fn () => $request->input('jenis') === PengajuanJadwalDosen::JENIS_RESCHEDULE),
                'nullable',
                'date_format:H:i',
            ],
            'waktu_selesai_pengganti' => [
                Rule::requiredIf(fn () => $request->input('jenis') === PengajuanJadwalDosen::JENIS_RESCHEDULE),
                'nullable',
                'date_format:H:i',
                'after:waktu_mulai_pengganti',
            ],
            'ruangan_id_pengganti' => [
                Rule::requiredIf(fn () => $request->input('jenis') === PengajuanJadwalDosen::JENIS_RESCHEDULE),
                'nullable',
                'exists:master_ruangan,id',
            ],
        ]);

        $jadwal = JadwalPerkuliahan::query()
            ->forDosen($masterDosen->id)
            ->find($validated['jadwal_id']);

        if (!$jadwal) {
            abort(403, 'Anda hanya dapat mengajukan permintaan untuk kelas Anda sendiri.');
        }

        $sudahAdaPending = PengajuanJadwalDosen::where('dosen_id', $masterDosen->id)
            ->where('jadwal_id', $jadwal->id)
            ->where('jenis', $validated['jenis'])
            ->where('tanggal_kelas', $validated['tanggal_kelas'])
            ->where('status', PengajuanJadwalDosen::STATUS_PENDING)
            ->exists();

        if ($sudahAdaPending) {
            return back()
                ->withInput()
                ->withErrors([
                    'pengajuan' => 'Masih ada pengajuan pending untuk kelas, jenis, dan tanggal tersebut.',
                ]);
        }

        PengajuanJadwalDosen::create([
            'jadwal_id' => $jadwal->id,
            'dosen_id' => $masterDosen->id,
            'jenis' => $validated['jenis'],
            'tanggal_kelas' => $validated['tanggal_kelas'],
            'alasan' => $validated['alasan'],
            'status' => PengajuanJadwalDosen::STATUS_PENDING,
            'tanggal_pengganti' => $validated['tanggal_pengganti'] ?? null,
            'waktu_mulai_pengganti' => $validated['waktu_mulai_pengganti'] ?? null,
            'waktu_selesai_pengganti' => $validated['waktu_selesai_pengganti'] ?? null,
            'ruangan_id_pengganti' => $validated['ruangan_id_pengganti'] ?? null,
        ]);

        $pesan = $validated['jenis'] === PengajuanJadwalDosen::JENIS_LAPOR_ABSEN
            ? 'Laporan berhalangan mengajar berhasil dikirim ke BAAK.'
            : 'Permintaan reschedule berhasil dikirim ke BAAK.';

        return redirect()->route('dosen.portal')->with('success', $pesan);
    }
}
