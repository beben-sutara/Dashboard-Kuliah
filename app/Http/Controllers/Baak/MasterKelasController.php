<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Models\MasterKelas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterKelasController extends Controller
{
    public function index()
    {
        $kelas = MasterKelas::orderBy('semester')
            ->orderBy('nama')
            ->paginate(15);

        return view('baak.master.kelas', compact('kelas'));
    }

    public function store(Request $request)
    {
        MasterKelas::create($this->validatePayload($request));

        return redirect()->route('baak.master.kelas')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, MasterKelas $masterKelas)
    {
        $masterKelas->update($this->validatePayload($request, $masterKelas));

        return redirect()->route('baak.master.kelas')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(MasterKelas $masterKelas)
    {
        $masterKelas->delete();

        return redirect()->route('baak.master.kelas')->with('success', 'Kelas berhasil dihapus.');
    }

    private function validatePayload(Request $request, ?MasterKelas $masterKelas = null): array
    {
        $namaRule = Rule::unique('master_kelas', 'nama');

        if ($masterKelas) {
            $namaRule->ignore($masterKelas->id);
        }

        return $request->validate([
            'nama' => ['required', 'string', 'max:100', $namaRule],
            'semester' => ['required', 'string', 'max:20'],
        ]);
    }
}
