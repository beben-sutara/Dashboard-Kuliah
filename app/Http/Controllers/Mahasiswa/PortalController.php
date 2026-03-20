<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\JadwalPerkuliahan;
use App\Models\KrsMahasiswa;
use App\Models\LaporanKehadiran;
use App\Models\Mahasiswa;
use App\Models\SemesterAkademik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class PortalController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $mahasiswa = $user->mahasiswa ?? $this->autoCreateMahasiswa($user);

        $hariIni              = JadwalPerkuliahan::hariSekarang();
        $jadwalMingguanKosong = collect(JadwalPerkuliahan::HARI_ORDER)->mapWithKeys(fn ($hari) => [$hari => collect()]);

        $hasKrs = $mahasiswa->krsMahasiswa()->where('status', KrsMahasiswa::STATUS_AKTIF)->exists();

        if ($hasKrs) {
            $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                ->forMahasiswaAktif($mahasiswa)
                ->orderedWeekly()
                ->get();

            $jadwalMingguan  = collect(JadwalPerkuliahan::HARI_ORDER)
                ->mapWithKeys(fn ($hari) => [$hari => $jadwal->where('hari', $hari)->values()]);
            $jadwalHariIni   = $jadwalMingguan->get($hariIni, collect());
            $mataKuliahAktif = $jadwal->unique('matakuliah_id')->values();
            $semesterAktif   = $mahasiswa->krsMahasiswa()
                ->where('status', KrsMahasiswa::STATUS_AKTIF)
                ->orderBy('semester_akademik')
                ->pluck('semester_akademik')
                ->unique()
                ->values();
            $jadwalTersedia  = collect();
        } else {
            // No KRS yet — load all available schedules so student can self-enroll
            $jadwal          = collect();
            $jadwalMingguan  = $jadwalMingguanKosong;
            $jadwalHariIni   = collect();
            $mataKuliahAktif = collect();
            $semesterAktif   = collect();
            $jadwalTersedia  = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                ->forSemesterAktif()
                ->orderedWeekly()
                ->get();
        }

        $profilBelumLengkap = str_starts_with((string) $mahasiswa->nim, 'TEMP-');

        return view('mahasiswa.portal', compact(
            'jadwal',
            'jadwalHariIni',
            'jadwalMingguan',
            'mataKuliahAktif',
            'semesterAktif',
            'mahasiswa',
            'hariIni',
            'hasKrs',
            'jadwalTersedia',
            'profilBelumLengkap'
        ));
    }

    /** Auto-enroll a schedule into the student's KRS. */
    public function enrollKrs(Request $request)
    {
        $validated = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwal_perkuliahan,id'],
        ]);

        $mahasiswa = $request->user()->mahasiswa;
        if (!$mahasiswa) {
            return response()->json(['success' => false, 'message' => 'Data mahasiswa tidak ditemukan.'], 404);
        }

        KrsMahasiswa::firstOrCreate(
            [
                'mahasiswa_id'     => $mahasiswa->id,
                'jadwal_id'        => $validated['jadwal_id'],
                'semester_akademik' => $this->semesterAkademikAktif(),
            ],
            ['status' => KrsMahasiswa::STATUS_AKTIF]
        );

        return response()->json(['success' => true, 'message' => 'Jadwal berhasil ditambahkan ke KRS Anda.']);
    }

    /** Remove a schedule from the student's active KRS. */
    public function unenrollKrs(Request $request)
    {
        $validated = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwal_perkuliahan,id'],
        ]);

        $mahasiswa = $request->user()->mahasiswa;
        if (!$mahasiswa) {
            return response()->json(['success' => false, 'message' => 'Data mahasiswa tidak ditemukan.'], 404);
        }

        KrsMahasiswa::where('mahasiswa_id', $mahasiswa->id)
            ->where('jadwal_id', $validated['jadwal_id'])
            ->where('status', KrsMahasiswa::STATUS_AKTIF)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Jadwal dihapus dari KRS Anda.']);
    }

    /** Student self-completes their profile (NIM, program studi, angkatan). */
    public function updateProfil(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa;
        if (!$mahasiswa) {
            return back()->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        $validated = $request->validate([
            'nim'           => ['required', 'string', 'max:30', Rule::unique('mahasiswa', 'nim')->ignore($mahasiswa->id)],
            'program_studi' => ['required', 'string', 'max:100'],
            'angkatan'      => ['required', 'digits:4', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
        ]);

        $mahasiswa->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function laporan(Request $request)
    {
        $validated = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwal_perkuliahan,id'],
            'jenis_laporan' => ['nullable', Rule::in([
                LaporanKehadiran::JENIS_DOSEN_TIDAK_HADIR,
                LaporanKehadiran::JENIS_HANYA_MEMBERI_TUGAS,
            ])],
            'catatan_mahasiswa' => ['nullable', 'string', 'max:1000'],
        ]);

        $mahasiswa = $request->user()->mahasiswa;

        if (!$mahasiswa) {
            return response()->json(['success' => false, 'message' => 'Data mahasiswa tidak ditemukan.'], 404);
        }

        $jadwal = JadwalPerkuliahan::query()
            ->forMahasiswaAktif($mahasiswa)
            ->where('jadwal_perkuliahan.id', $validated['jadwal_id'])
            ->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak termasuk dalam KRS aktif Anda.',
            ], 403);
        }

        $tanggal = now()->toDateString();

        try {
            LaporanKehadiran::create([
                'jadwal_id'       => $validated['jadwal_id'],
                'mahasiswa_id'    => $mahasiswa->id,
                'tanggal'         => $tanggal,
                'jenis_laporan'   => $validated['jenis_laporan'] ?? LaporanKehadiran::JENIS_DOSEN_TIDAK_HADIR,
                'catatan_mahasiswa' => $validated['catatan_mahasiswa'] ?? null,
                'status_validasi' => LaporanKehadiran::STATUS_PENDING,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Composite unique key violation = sudah lapor hari ini
            if ($e->getCode() === '23000') {
                return response()->json(['success' => false, 'message' => 'Anda sudah mengirim laporan untuk kelas ini hari ini.'], 422);
            }
            throw $e;
        }

        // Call Python threshold check
        $this->checkThreshold($validated['jadwal_id'], $tanggal);

        return response()->json(['success' => true, 'message' => 'Laporan berhasil dikirim. Terima kasih atas partisipasinya.']);
    }

    public function insun(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $msg = strtolower(trim($request->message));
        $user = $request->user();
        $mahasiswa = $user->mahasiswa;
        $hariIni = JadwalPerkuliahan::hariSekarang();
        $now = now();

        // Jadwal hari ini (milik mahasiswa jika ada KRS, semua jika belum)
        if (str_contains($msg, 'jadwal hari ini') || str_contains($msg, 'hari ini') || str_contains($msg, 'jadwal sekarang')) {
            $hasKrs = $mahasiswa && $mahasiswa->krsMahasiswa()->where('status', KrsMahasiswa::STATUS_AKTIF)->exists();
            $jadwal = collect();
            $isPersonal = false;

            if ($hasKrs) {
                $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                    ->forMahasiswaAktif($mahasiswa)
                    ->forHari($hariIni)
                    ->orderedWeekly()
                    ->get();
                $isPersonal = true;
            }

            // Fallback: tampilkan semua jadwal hari ini jika KRS kosong untuk hari ini
            if ($jadwal->isEmpty()) {
                $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                    ->forHari($hariIni)
                    ->orderedWeekly()
                    ->get();
                $isPersonal = false;
            }

            if ($jadwal->isEmpty()) {
                return response()->json(['reply' => "📭 Tidak ada jadwal kuliah untuk hari ini ($hariIni)."]);
            }

            $label = $isPersonal ? "📋 **Jadwal Anda Hari Ini ($hariIni):**\n" : "📋 **Semua Jadwal Hari Ini ($hariIni):**\n";
            $lines = [$label];
            foreach ($jadwal as $j) {
                $waktu = substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_selesai, 0, 5);
                $status = $j->isAktif() ? '🟢 Berlangsung' : (now()->gt(\Carbon\Carbon::createFromTimeString($j->waktu_selesai)) ? '✅ Selesai' : '⏳ Akan Datang');
                $lines[] = "• **{$j->matakuliah->nama}** ({$j->matakuliah->kode})";
                $lines[] = "  {$waktu} · Ruang {$j->ruangan->kode} · {$j->dosen->nama} · {$status}";
            }

            return response()->json(['reply' => implode("\n", $lines)]);
        }

        // Kelas yang sedang berlangsung
        if (str_contains($msg, 'berlangsung') || str_contains($msg, 'sedang') || str_contains($msg, 'sekarang aktif')) {
            $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                ->forHari($hariIni)
                ->get()
                ->filter(fn($j) => $j->isAktif());

            if ($jadwal->isEmpty()) {
                return response()->json(['reply' => "⏳ Tidak ada kelas yang sedang berlangsung saat ini."]);
            }

            $lines = ["🟢 **Kelas Sedang Berlangsung:**\n"];
            foreach ($jadwal as $j) {
                $waktu = substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_selesai, 0, 5);
                $lines[] = "• **{$j->matakuliah->nama}** — {$j->dosen->nama}";
                $lines[] = "  {$waktu} · Ruang {$j->ruangan->kode}";
            }

            return response()->json(['reply' => implode("\n", $lines)]);
        }

        // Ruangan kosong
        if (str_contains($msg, 'ruangan kosong') || str_contains($msg, 'ruang kosong') || str_contains($msg, 'ruangan tersedia')) {
            $ruanganTerpakai = JadwalPerkuliahan::forHari($hariIni)
                ->get()
                ->filter(fn($j) => $j->isAktif())
                ->pluck('ruangan_id')
                ->unique();

            $ruanganKosong = \App\Models\MasterRuangan::whereNotIn('id', $ruanganTerpakai)->orderBy('kode')->get();

            if ($ruanganKosong->isEmpty()) {
                return response()->json(['reply' => "🏫 Semua ruangan sedang terpakai saat ini."]);
            }

            $lines = ["🏫 **Ruangan Kosong Saat Ini:**\n"];
            foreach ($ruanganKosong as $r) {
                $lines[] = "• **{$r->kode}** — {$r->nama}" . ($r->kapasitas ? " (Kapasitas: {$r->kapasitas})" : '');
            }

            return response()->json(['reply' => implode("\n", $lines)]);
        }

        // Jadwal dosen tertentu
        if (str_contains($msg, 'dosen') || str_contains($msg, 'mengajar')) {
            $keyword = preg_replace('/(jadwal|dosen|mengajar|siapa|pak|bu|kapan)\s*/i', '', $msg);
            $keyword = trim($keyword, '? ');

            if (strlen($keyword) >= 2) {
                $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                    ->whereHas('dosen', fn($q) => $q->where('nama', 'like', "%{$keyword}%"))
                    ->orderedWeekly()
                    ->get();

                if ($jadwal->isNotEmpty()) {
                    $dosenNama = $jadwal->first()->dosen->nama;
                    $lines = ["👨‍🏫 **Jadwal {$dosenNama}:**\n"];
                    foreach ($jadwal as $j) {
                        $waktu = substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_selesai, 0, 5);
                        $lines[] = "• {$j->hari}, {$waktu} — {$j->matakuliah->nama} · Ruang {$j->ruangan->kode}";
                    }
                    return response()->json(['reply' => implode("\n", $lines)]);
                }

                return response()->json(['reply' => "🔎 Tidak ditemukan jadwal untuk dosen dengan nama \"{$keyword}\"."]);
            }
        }

        // Jadwal mata kuliah tertentu
        if (str_contains($msg, 'mata kuliah') || str_contains($msg, 'matkul') || str_contains($msg, 'kuliah')) {
            $keyword = preg_replace('/(jadwal|mata\s*kuliah|matkul|kuliah|kapan)\s*/i', '', $msg);
            $keyword = trim($keyword, '? ');

            if (strlen($keyword) >= 2) {
                $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                    ->whereHas('matakuliah', fn($q) => $q->where('nama', 'like', "%{$keyword}%")->orWhere('kode', 'like', "%{$keyword}%"))
                    ->orderedWeekly()
                    ->get();

                if ($jadwal->isNotEmpty()) {
                    $mkNama = $jadwal->first()->matakuliah->nama;
                    $lines = ["📚 **Jadwal {$mkNama}:**\n"];
                    foreach ($jadwal as $j) {
                        $waktu = substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_selesai, 0, 5);
                        $lines[] = "• {$j->hari}, {$waktu} — {$j->dosen->nama} · Ruang {$j->ruangan->kode} · Kelas {$j->kelas?->nama}";
                    }
                    return response()->json(['reply' => implode("\n", $lines)]);
                }

                return response()->json(['reply' => "🔎 Tidak ditemukan jadwal untuk mata kuliah \"{$keyword}\"."]);
            }
        }

        // Jadwal besok
        if (str_contains($msg, 'besok')) {
            $hariBesok = $this->hariIndonesia($now->copy()->addDay());
            $hasKrs = $mahasiswa && $mahasiswa->krsMahasiswa()->where('status', KrsMahasiswa::STATUS_AKTIF)->exists();
            $jadwal = collect();

            if ($hasKrs) {
                $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                    ->forMahasiswaAktif($mahasiswa)
                    ->forHari($hariBesok)
                    ->orderedWeekly()
                    ->get();
            }

            if ($jadwal->isEmpty()) {
                $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                    ->forHari($hariBesok)
                    ->orderedWeekly()
                    ->get();
            }

            if ($jadwal->isEmpty()) {
                return response()->json(['reply' => "📭 Tidak ada jadwal kuliah untuk besok ($hariBesok)."]);
            }

            $lines = ["📋 **Jadwal Besok ($hariBesok):**\n"];
            foreach ($jadwal as $j) {
                $waktu = substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_selesai, 0, 5);
                $lines[] = "• **{$j->matakuliah->nama}** — {$waktu} · Ruang {$j->ruangan->kode} · {$j->dosen->nama}";
            }

            return response()->json(['reply' => implode("\n", $lines)]);
        }

        // Info KRS mahasiswa
        if (str_contains($msg, 'krs') || str_contains($msg, 'mata kuliah saya') || str_contains($msg, 'jadwal saya')) {
            if (!$mahasiswa) {
                return response()->json(['reply' => '⚠️ Data mahasiswa Anda belum terdaftar.']);
            }

            $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                ->forMahasiswaAktif($mahasiswa)
                ->orderedWeekly()
                ->get();

            if ($jadwal->isEmpty()) {
                return response()->json(['reply' => '📭 Anda belum memiliki KRS aktif. Silakan daftarkan jadwal Anda terlebih dahulu.']);
            }

            $totalSks = $jadwal->sum(fn($j) => $j->matakuliah->sks ?? 0);
            $lines = ["📚 **KRS Aktif Anda ({$jadwal->count()} mata kuliah, {$totalSks} SKS):**\n"];
            foreach ($jadwal as $j) {
                $waktu = substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_selesai, 0, 5);
                $lines[] = "• {$j->hari}, {$waktu} — **{$j->matakuliah->nama}** · {$j->dosen->nama}";
            }

            return response()->json(['reply' => implode("\n", $lines)]);
        }

        // Default: tampilkan bantuan
        return response()->json(['reply' => "🤖 Halo! Saya **INSUN AI**, asisten akademik Anda.\n\nSaya bisa membantu:\n• 📋 \"Jadwal hari ini\" — lihat jadwal kuliah hari ini\n• 🟢 \"Kelas yang berlangsung\" — kelas yang sedang aktif\n• 🏫 \"Ruangan kosong\" — cek ketersediaan ruangan\n• 👨‍🏫 \"Dosen [nama]\" — cari jadwal dosen\n• 📚 \"Matkul [nama]\" — cari jadwal mata kuliah\n• 📋 \"Jadwal besok\" — lihat jadwal besok\n• 📚 \"KRS saya\" — lihat KRS aktif Anda\n\nSilakan tanyakan! 😊"]);
    }

    private function hariIndonesia(\Carbon\Carbon $date): string
    {
        $map = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
        return $map[$date->format('l')] ?? '';
    }

    private function checkThreshold(int $jadwalId, string $tanggal): void
    {
        try {
            $pythonUrl = config('services.python_api.url', 'http://127.0.0.1:8001');
            Http::timeout(5)->post("{$pythonUrl}/api/laporan/threshold", [
                'jadwal_id' => $jadwalId,
                'tanggal'   => $tanggal,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Python threshold check unavailable: ' . $e->getMessage());
        }
    }

    /** Auto-create a placeholder mahasiswa record for a newly registered user. */
    private function autoCreateMahasiswa(User $user): Mahasiswa
    {
        // Try to find an unlinked mahasiswa with the same email first (BAAK pre-entry)
        $existing = Mahasiswa::where('email', $user->email)->whereNull('user_id')->first();
        if ($existing) {
            $existing->update(['user_id' => $user->id]);
            return $existing;
        }

        return Mahasiswa::create([
            'nim'           => 'TEMP-' . $user->id,
            'nama'          => $user->name,
            'email'         => $user->email,
            'angkatan'      => date('Y'),
            'program_studi' => 'Belum Diisi',
            'user_id'       => $user->id,
        ]);
    }

    /** Get the active semester name from SemesterAkademik, with fallback. */
    private function semesterAkademikAktif(): string
    {
        $aktif = SemesterAkademik::aktif();
        if ($aktif) {
            return $aktif->nama;
        }

        // Fallback: compute from current date
        $month = (int) date('n');
        $year  = (int) date('Y');
        return $month >= 8
            ? "Ganjil {$year}/" . ($year + 1)
            : "Genap " . ($year - 1) . "/{$year}";
    }
}
