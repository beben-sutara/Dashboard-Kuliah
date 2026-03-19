<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Models\MasterDosen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterDosenController extends Controller
{
    public function index()
    {
        $dosen = MasterDosen::orderBy('nama')->paginate(15);
        return view('baak.master.dosen', compact('dosen'));
    }

    public function store(Request $request)
    {
        MasterDosen::create($this->validatePayload($request));
        return redirect()->route('baak.master.dosen')->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function update(Request $request, MasterDosen $masterDosen)
    {
        $masterDosen->update($this->validatePayload($request, $masterDosen));
        return redirect()->route('baak.master.dosen')->with('success', 'Dosen berhasil diperbarui.');
    }

    public function destroy(MasterDosen $masterDosen)
    {
        $masterDosen->delete();
        return redirect()->route('baak.master.dosen')->with('success', 'Dosen berhasil dihapus.');
    }

    private function validatePayload(Request $request, ?MasterDosen $masterDosen = null): array
    {
        $normalizedNidn = trim((string) $request->input('nidn', ''));
        $request->merge([
            'nidn' => $normalizedNidn !== '' ? $normalizedNidn : null,
        ]);

        $nidnRule = Rule::unique('master_dosen', 'nidn');

        if ($masterDosen) {
            $nidnRule->ignore($masterDosen->id);
        }

        return $request->validate([
            'nidn' => ['nullable', 'string', 'max:50', $nidnRule],
            'nama' => ['required', 'string', 'max:100'],
            'prodi' => ['required', 'string', 'max:100'],
        ]);
    }
}
