<?php

namespace App\Http\Controllers;

use App\Models\JadwalPerkuliahan;
use App\Models\MasterRuangan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InsunController extends Controller
{
    private array $greetings = [
        'Tentu! Saya bantu carikan ya 😊',
        'Siap, saya cek dulu sebentar ya...',
        'Oke, berikut informasinya! ✨',
        'Baik, ini yang saya temukan 📋',
        'Dengan senang hati! Ini datanya 🎓',
    ];

    private array $emptyGreetings = [
        'Hmm, sepertinya',
        'Wah, ternyata',
        'Saya sudah cek, dan',
        'Setelah saya telusuri,',
    ];

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $msg = strtolower(trim($request->message));
        $hariIni = JadwalPerkuliahan::hariSekarang();
        $now = now();
        $jam = $now->format('H:i');

        // Sapaan
        if (preg_match('/^(halo|hai|hi|hey|helo|p|assalam|salam|selamat)\b/', $msg)) {
            $sapaan = ((int) $now->format('H')) < 11 ? 'Selamat pagi' : (((int) $now->format('H')) < 15 ? 'Selamat siang' : (((int) $now->format('H')) < 18 ? 'Selamat sore' : 'Selamat malam'));
            return $this->reply("{$sapaan}! 👋\n\nSaya INSUN, asisten akademik virtual kampus kamu. Saya bisa bantu kamu cek jadwal kuliah, info dosen, ruangan kosong, dan lainnya.\n\nCoba tanya, misalnya:\n• \"Jadwal hari ini apa?\"\n• \"Dosen Budi mengajar kapan?\"\n• \"Ada ruangan kosong nggak?\"\n\nApa yang ingin kamu ketahui? 😊");
        }

        // Terima kasih
        if (preg_match('/^(makasih|terima\s*kasih|thanks|thx|tq|nuhun)/', $msg)) {
            return $this->reply("Sama-sama! 😊 Senang bisa membantu. Kalau ada pertanyaan lain soal jadwal kuliah, jangan ragu tanya ya! 🎓");
        }

        // --- Detect context modifiers ---
        $filterHari = null;
        $filterHariLabel = null;
        if (str_contains($msg, 'hari ini')) {
            $filterHari = $hariIni;
            $filterHariLabel = "hari ini ({$hariIni})";
        } elseif (str_contains($msg, 'besok')) {
            $filterHari = $this->hariIndonesia($now->copy()->addDay());
            $filterHariLabel = "besok ({$filterHari})";
        } else {
            $hariList2 = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
            foreach ($hariList2 as $h2) {
                if (str_contains($msg, $h2)) {
                    $filterHari = ucfirst($h2);
                    $filterHariLabel = "hari {$filterHari}";
                    break;
                }
            }
        }

        // Jadwal dosen tertentu (BEFORE "jadwal hari ini" to catch "pak Agun hari ini")
        $isDosenQuery = str_contains($msg, 'dosen') || str_contains($msg, 'mengajar')
            || preg_match('/\b(pak|bu|bapak|ibu)\s+\w/i', $msg);

        if ($isDosenQuery) {
            $keyword = preg_replace('/(jadwal|dosen|mengajar|siapa|pak|bu|bapak|ibu|kapan|apa|nya|ada|hari ini|besok|senin|selasa|rabu|kamis|jumat|sabtu)\s*/i', '', $msg);
            $keyword = trim($keyword, '? .,!');

            // Also try extracting name after pak/bu if keyword is too short
            if (strlen($keyword) < 2 && preg_match('/(?:pak|bu|bapak|ibu)\s+(\w+)/i', $msg, $m)) {
                $keyword = $m[1];
            }

            if (strlen($keyword) >= 2) {
                $query = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                    ->whereHas('dosen', fn($q) => $q->where('nama', 'like', "%{$keyword}%"));

                if ($filterHari) {
                    $query->forHari($filterHari);
                }

                $jadwal = $query->orderedWeekly()->get();

                if ($jadwal->isNotEmpty()) {
                    $dosenNama = $jadwal->first()->dosen->nama;

                    if ($filterHari) {
                        $lines = ["{$this->greetRandom()}\n\nJadwal **{$dosenNama}** untuk {$filterHariLabel}:\n"];
                        foreach ($jadwal as $j) {
                            $waktu = substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_selesai, 0, 5);
                            $status = $j->isAktif() ? '🟢 Berlangsung' : ($now->gt(Carbon::createFromTimeString($j->waktu_selesai)) ? '✅ Selesai' : '⏳ Akan Datang');
                            $lines[] = "📖 **{$j->matakuliah->nama}**";
                            $lines[] = "   ⏰ {$waktu} · 🏫 Ruang {$j->ruangan->kode} · {$status}";
                            $lines[] = "";
                        }
                    } else {
                        $totalMk = $jadwal->unique('matakuliah_id')->count();
                        $lines = ["{$this->greetRandom()}\n\n**{$dosenNama}** mengajar **{$totalMk} mata kuliah** dengan total **{$jadwal->count()} sesi** per minggu:\n"];
                        foreach ($jadwal as $j) {
                            $waktu = substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_selesai, 0, 5);
                            $lines[] = "📖 {$j->hari}, {$waktu}";
                            $lines[] = "   {$j->matakuliah->nama} · Ruang {$j->ruangan->kode}";
                            $lines[] = "";
                        }
                    }
                    $lines[] = "Mau tahu info lain tentang dosen ini atau hal lainnya? 😊";
                    return $this->reply(implode("\n", $lines));
                }

                $notFoundMsg = $filterHari
                    ? "Hmm, **{$keyword}** sepertinya tidak ada jadwal untuk {$filterHariLabel}. 🤔\n\nCoba cek hari lain, atau ketik \"dosen {$keyword}\" untuk lihat semua jadwalnya."
                    : "Hmm, saya tidak menemukan dosen dengan nama \"{$keyword}\" di database. 🤔\n\nCoba periksa kembali ejaannya ya. Misalnya ketik \"dosen Budi\" atau \"Pak Siti\".";
                return $this->reply($notFoundMsg);
            }

            return $this->reply("Siapa nama dosennya yang ingin kamu cari? 🤔\n\nCoba ketik seperti ini: \"dosen Budi\", \"Pak Agun hari ini\", atau \"Bu Siti\".");
        }

        // Jadwal mata kuliah tertentu (also before "hari ini" generic)
        if (str_contains($msg, 'mata kuliah') || str_contains($msg, 'matkul')) {
            $keyword = preg_replace('/(jadwal|mata\s*kuliah|matkul|kuliah|kapan|apa|nya|hari ini|besok|senin|selasa|rabu|kamis|jumat|sabtu)\s*/i', '', $msg);
            $keyword = trim($keyword, '? .,!');

            if (strlen($keyword) >= 2) {
                $query = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                    ->whereHas('matakuliah', fn($q) => $q->where('nama', 'like', "%{$keyword}%")->orWhere('kode', 'like', "%{$keyword}%"));

                if ($filterHari) {
                    $query->forHari($filterHari);
                }

                $jadwal = $query->orderedWeekly()->get();

                if ($jadwal->isNotEmpty()) {
                    $mkNama = $jadwal->first()->matakuliah->nama;
                    $mkKode = $jadwal->first()->matakuliah->kode;
                    $sks = $jadwal->first()->matakuliah->sks;
                    $header = "**{$mkNama}**" . ($mkKode ? " ({$mkKode})" : '') . ($sks ? " · {$sks} SKS" : '');
                    $label = $filterHari ? " untuk {$filterHariLabel}" : '';
                    $lines = ["{$this->greetRandom()}\n\n{$header} dijadwalkan{$label} sebagai berikut:\n"];
                    foreach ($jadwal as $j) {
                        $waktu = substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_selesai, 0, 5);
                        $hariLabel = $filterHari ? '' : "{$j->hari}, ";
                        $lines[] = "📖 {$hariLabel}{$waktu}";
                        $lines[] = "   👨‍🏫 {$j->dosen->nama} · 🏫 Ruang {$j->ruangan->kode}" . ($j->kelas ? " · Kelas {$j->kelas->nama}" : '');
                        $lines[] = "";
                    }
                    $lines[] = "Butuh info lain? Silakan tanya! 📚";
                    return $this->reply(implode("\n", $lines));
                }

                return $this->reply("Saya tidak menemukan mata kuliah \"{$keyword}\" di database. 🤔\n\nCoba cek kembali nama atau kodenya ya. Misalnya: \"matkul basis data\" atau \"matkul SI4028\".");
            }

            return $this->reply("Mata kuliah apa yang ingin kamu cari? 🤔\n\nCoba ketik seperti ini: \"matkul basis data\" atau \"matkul SI4028\".");
        }

        // Jadwal hari ini
        if (str_contains($msg, 'jadwal hari ini') || str_contains($msg, 'hari ini') || str_contains($msg, 'jadwal sekarang')) {
            $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                ->forHari($hariIni)
                ->orderedWeekly()
                ->get();

            if ($jadwal->isEmpty()) {
                return $this->reply("{$this->emptyRandom()} tidak ada jadwal kuliah untuk hari ini ($hariIni). Mungkin hari libur atau belum ada jadwal yang diinput. 📭\n\nMau cek jadwal hari lain? Coba ketik \"jadwal besok\" atau \"jadwal senin\" 😊");
            }

            $berlangsung = $jadwal->filter(fn($j) => $j->isAktif())->count();
            $selesai = $jadwal->filter(fn($j) => $now->gt(Carbon::createFromTimeString($j->waktu_selesai)))->count();
            $intro = "{$this->greetRandom()}\n\nHari ini **{$hariIni}**, ada **{$jadwal->count()} kelas** yang terjadwal.";
            if ($berlangsung > 0) $intro .= " Saat ini ada **{$berlangsung} kelas** yang sedang berlangsung! 🟢";

            $lines = [$intro, ""];
            foreach ($jadwal as $j) {
                $waktu = substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_selesai, 0, 5);
                $status = $j->isAktif() ? '🟢 Berlangsung' : ($now->gt(Carbon::createFromTimeString($j->waktu_selesai)) ? '✅ Selesai' : '⏳ Akan Datang');
                $lines[] = "📖 **{$j->matakuliah->nama}**";
                $lines[] = "   ⏰ {$waktu} · 🏫 Ruang {$j->ruangan->kode}";
                $lines[] = "   👨‍🏫 {$j->dosen->nama} · {$status}";
                $lines[] = "";
            }

            $lines[] = "Ada yang ingin kamu tanyakan lagi? 😊";
            return $this->reply(implode("\n", $lines));
        }

        // Kelas yang sedang berlangsung
        if (str_contains($msg, 'berlangsung') || str_contains($msg, 'sedang') || str_contains($msg, 'sekarang aktif')) {
            $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                ->forHari($hariIni)
                ->get()
                ->filter(fn($j) => $j->isAktif());

            if ($jadwal->isEmpty()) {
                return $this->reply("Saat ini (pukul {$jam}) belum ada kelas yang sedang berlangsung nih. ⏳\n\nMungkin kelasnya sudah selesai atau belum dimulai. Coba tanya \"jadwal hari ini\" untuk lihat semua jadwal ya! 😊");
            }

            $lines = ["{$this->greetRandom()}\n\nSaat ini (pukul {$jam}) ada **{$jadwal->count()} kelas** yang sedang berlangsung:\n"];
            foreach ($jadwal as $j) {
                $waktu = substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_selesai, 0, 5);
                $lines[] = "🟢 **{$j->matakuliah->nama}**";
                $lines[] = "   ⏰ {$waktu} · 🏫 Ruang {$j->ruangan->kode}";
                $lines[] = "   👨‍🏫 {$j->dosen->nama}";
                $lines[] = "";
            }

            $lines[] = "Semoga membantu! 🎓";
            return $this->reply(implode("\n", $lines));
        }

        // Ruangan kosong
        if (str_contains($msg, 'ruangan kosong') || str_contains($msg, 'ruang kosong') || str_contains($msg, 'ruangan tersedia')) {
            $ruanganTerpakai = JadwalPerkuliahan::forHari($hariIni)
                ->get()
                ->filter(fn($j) => $j->isAktif())
                ->pluck('ruangan_id')
                ->unique();

            $ruanganKosong = MasterRuangan::whereNotIn('id', $ruanganTerpakai)->orderBy('kode')->get();

            if ($ruanganKosong->isEmpty()) {
                return $this->reply("Waduh, sepertinya semua ruangan sedang terpakai saat ini (pukul {$jam}). 😅\n\nCoba cek lagi nanti ya, atau tanya \"jadwal hari ini\" untuk lihat kapan ada ruangan yang kosong.");
            }

            $lines = ["{$this->greetRandom()}\n\nSaat ini (pukul {$jam}) ada **{$ruanganKosong->count()} ruangan** yang tersedia:\n"];
            foreach ($ruanganKosong as $r) {
                $kap = $r->kapasitas ? " · Kapasitas {$r->kapasitas} orang" : '';
                $lines[] = "🏫 **{$r->kode}** — {$r->nama}{$kap}";
            }
            $lines[] = "\nRuangan di atas sedang tidak digunakan, jadi bisa dipakai untuk belajar atau kegiatan lain! 📚";

            return $this->reply(implode("\n", $lines));
        }

        // Jadwal besok
        if (str_contains($msg, 'besok')) {
            $hariBesok = $this->hariIndonesia($now->copy()->addDay());
            $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                ->forHari($hariBesok)
                ->orderedWeekly()
                ->get();

            if ($jadwal->isEmpty()) {
                return $this->reply("{$this->emptyRandom()} tidak ada jadwal kuliah untuk besok ({$hariBesok}). Sepertinya bisa istirahat dulu ya! 😴📭");
            }

            $lines = ["{$this->greetRandom()}\n\nBesok **{$hariBesok}**, ada **{$jadwal->count()} kelas** yang terjadwal:\n"];
            foreach ($jadwal as $j) {
                $waktu = substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_selesai, 0, 5);
                $lines[] = "📖 **{$j->matakuliah->nama}**";
                $lines[] = "   ⏰ {$waktu} · 🏫 Ruang {$j->ruangan->kode} · 👨‍🏫 {$j->dosen->nama}";
                $lines[] = "";
            }
            $lines[] = "Jangan lupa siapkan materinya ya! 💪📚";
            return $this->reply(implode("\n", $lines));
        }

        // Jadwal hari tertentu
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        foreach ($hariList as $h) {
            if (str_contains($msg, $h)) {
                $hariCari = ucfirst($h);
                $jadwal = JadwalPerkuliahan::with(['dosen', 'matakuliah', 'ruangan', 'kelas'])
                    ->forHari($hariCari)
                    ->orderedWeekly()
                    ->get();

                $isHariIni = $hariCari === $hariIni;
                $label = $isHariIni ? "hari ini ({$hariCari})" : "hari {$hariCari}";

                if ($jadwal->isEmpty()) {
                    return $this->reply("{$this->emptyRandom()} tidak ada jadwal kuliah untuk {$label}. 📭\n\nMau cek hari lain? 😊");
                }

                $lines = ["{$this->greetRandom()}\n\nUntuk {$label}, ada **{$jadwal->count()} kelas** yang terjadwal:\n"];
                foreach ($jadwal as $j) {
                    $waktu = substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_selesai, 0, 5);
                    $lines[] = "📖 **{$j->matakuliah->nama}**";
                    $lines[] = "   ⏰ {$waktu} · 🏫 Ruang {$j->ruangan->kode} · 👨‍🏫 {$j->dosen->nama}";
                    $lines[] = "";
                }
                $lines[] = "Ada pertanyaan lain? 😊";
                return $this->reply(implode("\n", $lines));
            }
        }

        // Default: bantuan
        $sapaan = ((int) $now->format('H')) < 11 ? 'pagi' : (((int) $now->format('H')) < 15 ? 'siang' : (((int) $now->format('H')) < 18 ? 'sore' : 'malam'));
        return $this->reply("Halo, selamat {$sapaan}! 👋\n\nSaya INSUN, asisten akademik virtual kampus. Maaf, saya belum paham pertanyaan kamu. 😅\n\nTapi saya bisa bantu hal-hal berikut:\n\n📋 \"Jadwal hari ini\" — cek jadwal kuliah\n🟢 \"Kelas yang berlangsung\" — kelas aktif sekarang\n🏫 \"Ruangan kosong\" — cari ruangan tersedia\n👨‍🏫 \"Dosen [nama]\" — jadwal dosen tertentu\n📚 \"Matkul [nama]\" — jadwal mata kuliah\n📋 \"Jadwal besok\" atau \"Jadwal senin\"\n\nCoba ketik salah satu di atas ya! 😊");
    }

    private function reply(string $text): \Illuminate\Http\JsonResponse
    {
        return response()->json(['reply' => $text]);
    }

    private function greetRandom(): string
    {
        return $this->greetings[array_rand($this->greetings)];
    }

    private function emptyRandom(): string
    {
        return $this->emptyGreetings[array_rand($this->emptyGreetings)];
    }

    private function hariIndonesia(Carbon $date): string
    {
        $map = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
        return $map[$date->format('l')] ?? '';
    }
}
