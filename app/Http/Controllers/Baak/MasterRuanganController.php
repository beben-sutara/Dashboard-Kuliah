<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Models\MasterRuangan;
use Illuminate\Http\Request;

class MasterRuanganController extends Controller
{
    public function index()
    {
        $ruangan = MasterRuangan::orderBy('kode')->paginate(15);
        return view('baak.master.ruangan', compact('ruangan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'      => 'required|string|unique:master_ruangan,kode',
            'nama'      => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1',
            'jenis'     => 'required|in:Teori,Lab,Aula,Seminar',
        ]);
        MasterRuangan::create($validated);
        return redirect()->route('baak.master.ruangan')->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function update(Request $request, MasterRuangan $masterRuangan)
    {
        $validated = $request->validate([
            'kode'      => 'required|string|unique:master_ruangan,kode,' . $masterRuangan->id,
            'nama'      => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1',
            'jenis'     => 'required|in:Teori,Lab,Aula,Seminar',
        ]);
        $masterRuangan->update($validated);
        return redirect()->route('baak.master.ruangan')->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(MasterRuangan $masterRuangan)
    {
        $masterRuangan->delete();
        return redirect()->route('baak.master.ruangan')->with('success', 'Ruangan berhasil dihapus.');
    }
}
