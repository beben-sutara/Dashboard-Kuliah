<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Models\SemesterAkademik;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function index()
    {
        $semesters = SemesterAkademik::orderByDesc('tahun_mulai')
            ->orderByDesc('tipe')
            ->get();

        return view('baak.semester', compact('semesters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipe'             => 'required|in:Ganjil,Genap',
            'tahun_mulai'      => 'required|digits:4',
            'tahun_akhir'      => 'required|digits:4|gt:tahun_mulai',
            'tanggal_mulai'    => 'nullable|date',
            'tanggal_selesai'  => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $validated['nama'] = "{$validated['tipe']} {$validated['tahun_mulai']}/{$validated['tahun_akhir']}";

        if (SemesterAkademik::where('nama', $validated['nama'])->exists()) {
            return back()->withErrors(['nama' => 'Semester akademik ini sudah ada.'])->withInput();
        }

        SemesterAkademik::create($validated);

        return back()->with('success', "Semester {$validated['nama']} berhasil ditambahkan.");
    }

    public function update(Request $request, SemesterAkademik $semester)
    {
        $validated = $request->validate([
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $semester->update($validated);

        return back()->with('success', "Semester {$semester->nama} berhasil diperbarui.");
    }

    public function setAktif(SemesterAkademik $semester)
    {
        $semester->setAktif();

        return back()->with('success', "✅ {$semester->nama} telah diset sebagai semester aktif.");
    }

    public function destroy(SemesterAkademik $semester)
    {
        if ($semester->is_aktif) {
            return back()->with('error', 'Tidak bisa menghapus semester yang sedang aktif.');
        }

        if ($semester->jadwalPerkuliahan()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus semester yang memiliki jadwal.');
        }

        $nama = $semester->nama;
        $semester->delete();

        return back()->with('success', "Semester {$nama} berhasil dihapus.");
    }
}
