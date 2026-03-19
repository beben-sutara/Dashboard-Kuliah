<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Events\JadwalCreated;
use App\Events\JadwalUpdated;
use App\Events\JadwalDeleted;
use App\Models\JadwalPerkuliahan;
use App\Models\MasterDosen;
use App\Models\MasterKelas;
use App\Models\MasterMatakuliah;
use App\Models\MasterRuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class JadwalController extends Controller
{
    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        // Call Python FastAPI clash detection
        $clashResult = $this->checkClash($validated);
        if ($clashResult['clash']) {
            return back()->withInput()->withErrors([
                'clash' => 'Bentrok jadwal terdeteksi! ' . $clashResult['message']
            ]);
        }

        $jadwal = JadwalPerkuliahan::create($validated);
        $jadwal->load(['dosen', 'matakuliah', 'ruangan']);

        try {
            broadcast(new JadwalCreated($jadwal))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Broadcast JadwalCreated gagal: ' . $e->getMessage());
        }

        return redirect()->route('baak.dashboard')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(Request $request, JadwalPerkuliahan $jadwal)
    {
        $validated = $this->validatePayload($request);

        $clashResult = $this->checkClash($validated, $jadwal->id);
        if ($clashResult['clash']) {
            return back()->withInput()->withErrors([
                'clash' => 'Bentrok jadwal terdeteksi! ' . $clashResult['message']
            ]);
        }

        $jadwal->update($validated);
        $jadwal->load(['dosen', 'matakuliah', 'ruangan']);

        try {
            broadcast(new JadwalUpdated($jadwal))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Broadcast JadwalUpdated gagal: ' . $e->getMessage());
        }

        return redirect()->route('baak.dashboard')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(JadwalPerkuliahan $jadwal)
    {
        $jadwalId = $jadwal->id;
        $jadwal->delete();

        try {
            broadcast(new JadwalDeleted($jadwalId))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Broadcast JadwalDeleted gagal: ' . $e->getMessage());
        }

        return redirect()->route('baak.dashboard')->with('success', 'Jadwal berhasil dihapus.');
    }

    private function checkClash(array $data, ?int $excludeId = null): array
    {
        try {
            $pythonUrl = config('services.python_api.url', 'http://127.0.0.1:8001');
            $response = Http::timeout(5)->post("{$pythonUrl}/api/clash-check", array_merge(
                $data,
                ['exclude_id' => $excludeId]
            ));

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // If Python service is unavailable, log and allow save
            \Log::warning('Python FastAPI clash-check unavailable: ' . $e->getMessage());
        }

        return ['clash' => false, 'message' => ''];
    }

    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'kelas_id'       => 'required|exists:master_kelas,id',
            'dosen_id'       => 'required|exists:master_dosen,id',
            'matakuliah_id'  => 'required|exists:master_matakuliah,id',
            'ruangan_id'     => 'required|exists:master_ruangan,id',
            'prodi'          => 'required|string|max:100',
            'hari'           => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'waktu_mulai'    => 'required|date_format:H:i',
            'waktu_selesai'  => 'required|date_format:H:i|after:waktu_mulai',
        ]);

        $validated['semester'] = MasterKelas::query()
            ->whereKey($validated['kelas_id'])
            ->value('semester');

        return $validated;
    }
}
