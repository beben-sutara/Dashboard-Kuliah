<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Models\JadwalPerkuliahan;
use App\Models\KrsMahasiswa;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KrsMahasiswaController extends Controller
{
    public function index()
    {
        $krsMahasiswa = KrsMahasiswa::with(['mahasiswa', 'jadwal.matakuliah', 'jadwal.dosen', 'jadwal.ruangan'])
            ->latest()
            ->paginate(15);

        $mahasiswa = Mahasiswa::orderBy('nama')->get();
        $jadwal = JadwalPerkuliahan::with(['matakuliah', 'dosen', 'ruangan'])
            ->orderedWeekly()
            ->get();

        return view('baak.krs.index', compact('krsMahasiswa', 'mahasiswa', 'jadwal'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        KrsMahasiswa::create($validated);

        return redirect()->route('baak.krs.index')->with('success', 'Data KRS mahasiswa berhasil ditambahkan.');
    }

    public function update(Request $request, KrsMahasiswa $krsMahasiswa)
    {
        $validated = $this->validateRequest($request, $krsMahasiswa);

        $krsMahasiswa->update($validated);

        return redirect()->route('baak.krs.index')->with('success', 'Data KRS mahasiswa berhasil diperbarui.');
    }

    public function destroy(KrsMahasiswa $krsMahasiswa)
    {
        $krsMahasiswa->delete();

        return redirect()->route('baak.krs.index')->with('success', 'Data KRS mahasiswa berhasil dihapus.');
    }

    private function validateRequest(Request $request, ?KrsMahasiswa $krsMahasiswa = null): array
    {
        return $request->validate([
            'mahasiswa_id' => ['required', 'exists:mahasiswa,id'],
            'jadwal_id' => [
                'required',
                'exists:jadwal_perkuliahan,id',
                Rule::unique('krs_mahasiswa')
                    ->ignore($krsMahasiswa?->id)
                    ->where(function ($query) use ($request) {
                        return $query
                            ->where('mahasiswa_id', $request->input('mahasiswa_id'))
                            ->where('semester_akademik', $request->input('semester_akademik'));
                    }),
            ],
            'semester_akademik' => ['required', 'string', 'max:50'],
            'status' => ['required', Rule::in([
                KrsMahasiswa::STATUS_AKTIF,
                KrsMahasiswa::STATUS_NONAKTIF,
                KrsMahasiswa::STATUS_SELESAI,
            ])],
        ]);
    }
}
