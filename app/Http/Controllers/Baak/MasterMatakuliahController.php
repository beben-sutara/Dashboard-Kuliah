<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Models\MasterMatakuliah;
use Illuminate\Http\Request;

class MasterMatakuliahController extends Controller
{
    public function index()
    {
        $matakuliah = MasterMatakuliah::orderByRaw("CASE WHEN kode IS NULL OR kode = '' THEN 1 ELSE 0 END")
            ->orderBy('kode')
            ->orderBy('nama')
            ->paginate(15);

        return view('baak.master.matakuliah', compact('matakuliah'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'  => 'required|string|max:30|unique:master_matakuliah,kode',
            'nama'  => 'required|string|max:150|unique:master_matakuliah,nama',
            'sks'   => 'required|integer|min:1|max:6',
            'waktu' => ['nullable', 'string', 'max:20', 'regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/'],
        ]);

        MasterMatakuliah::create($validated);

        return redirect()->route('baak.master.matakuliah')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function update(Request $request, MasterMatakuliah $masterMatakuliah)
    {
        $validated = $request->validate([
            'kode'  => 'required|string|max:30|unique:master_matakuliah,kode,' . $masterMatakuliah->id,
            'nama'  => 'required|string|max:150|unique:master_matakuliah,nama,' . $masterMatakuliah->id,
            'sks'   => 'required|integer|min:1|max:6',
            'waktu' => ['nullable', 'string', 'max:20', 'regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/'],
        ]);

        $masterMatakuliah->update($validated);

        return redirect()->route('baak.master.matakuliah')->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(MasterMatakuliah $masterMatakuliah)
    {
        $masterMatakuliah->delete();
        return redirect()->route('baak.master.matakuliah')->with('success', 'Mata kuliah berhasil dihapus.');
    }
}
