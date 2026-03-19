<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\MasterDosen;
use App\Models\MasterKelas;
use App\Models\MasterMatakuliah;
use App\Models\MasterRuangan;
use App\Models\JadwalPerkuliahan;
use App\Models\KrsMahasiswa;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── User accounts (demo accounts per role) ──────────────────────────
        $baak = User::firstOrCreate(['email' => 'baak@jadwalkuliah.com'], [
            'name'     => 'Admin BAAK',
            'password' => Hash::make('password'),
            'role'     => 'baak',
            'email_verified_at' => now(),
        ]);

        User::firstOrCreate(['email' => 'baak2@jadwalkuliah.com'], [
            'name'     => 'Operator BAAK 2',
            'password' => Hash::make('password'),
            'role'     => 'baak',
            'email_verified_at' => now(),
        ]);

        $dosenUser = User::firstOrCreate(['email' => 'dosen@jadwalkuliah.com'], [
            'name'     => 'Dr. Budi Santoso',
            'password' => Hash::make('password'),
            'role'     => 'dosen',
            'email_verified_at' => now(),
        ]);

        $mhsUser = User::firstOrCreate(['email' => 'mahasiswa@jadwalkuliah.com'], [
            'name'     => 'Andi Mahasiswa',
            'password' => Hash::make('password'),
            'role'     => 'mahasiswa',
            'email_verified_at' => now(),
        ]);

        // ── Master Dosen ──────────────────────────────────────────────────────
        $dosenData = [
            ['nidn' => '0101018001', 'nama' => 'Dr. Budi Santoso, M.Kom',      'prodi' => 'Teknik Informatika'],
            ['nidn' => '0202028002', 'nama' => 'Prof. Siti Rahayu, Ph.D',       'prodi' => 'Sistem Informasi'],
            ['nidn' => '0303038003', 'nama' => 'Ir. Ahmad Fauzi, M.T',          'prodi' => 'Teknik Informatika'],
            ['nidn' => '0404048004', 'nama' => 'Dr. Rina Wulandari, M.Si',      'prodi' => 'Matematika'],
            ['nidn' => '0505058005', 'nama' => 'Drs. Hendra Kusuma, M.Pd',      'prodi' => 'Sistem Informasi'],
            ['nidn' => '0606068006', 'nama' => 'Dr. Dewi Lestari, S.Kom, M.Cs', 'prodi' => 'Teknik Informatika'],
        ];
        foreach ($dosenData as $d) {
            MasterDosen::firstOrCreate(['nidn' => $d['nidn']], $d);
        }

        // Link dosen user account to first dosen record
        MasterDosen::where('nidn', '0101018001')
            ->whereNull('user_id')
            ->update(['user_id' => $dosenUser->id]);

        // ── Master Matakuliah ─────────────────────────────────────────────────
        $mkData = [
            ['kode' => 'IF101', 'nama' => 'Algoritma & Pemrograman', 'sks' => 3],
            ['kode' => 'SI201', 'nama' => 'Basis Data', 'sks' => 3],
            ['kode' => 'IF203', 'nama' => 'Jaringan Komputer', 'sks' => 3],
            ['kode' => 'IF205', 'nama' => 'Rekayasa Perangkat Lunak', 'sks' => 3],
            ['kode' => 'IF307', 'nama' => 'Kecerdasan Buatan', 'sks' => 3],
            ['kode' => 'IF204', 'nama' => 'Sistem Operasi', 'sks' => 3],
            ['kode' => 'SI204', 'nama' => 'Pemrograman Web', 'sks' => 3],
            ['kode' => 'IF102', 'nama' => 'Struktur Data', 'sks' => 3],
            ['kode' => 'MT101', 'nama' => 'Kalkulus', 'sks' => 3],
            ['kode' => 'MT201', 'nama' => 'Statistika', 'sks' => 2],
        ];
        foreach ($mkData as $mk) {
            MasterMatakuliah::updateOrCreate(
                ['nama' => $mk['nama']],
                ['kode' => $mk['kode'], 'sks' => $mk['sks']]
            );
        }

        // ── Master Ruangan ────────────────────────────────────────────────────
        $ruangData = [
            ['kode' => 'A101', 'nama' => 'Ruang Kuliah A101', 'kapasitas' => 40, 'jenis' => 'Teori'],
            ['kode' => 'A102', 'nama' => 'Ruang Kuliah A102', 'kapasitas' => 40, 'jenis' => 'Teori'],
            ['kode' => 'B201', 'nama' => 'Lab Komputer B201',  'kapasitas' => 30, 'jenis' => 'Lab'],
            ['kode' => 'B202', 'nama' => 'Lab Jaringan B202',  'kapasitas' => 25, 'jenis' => 'Lab'],
            ['kode' => 'C301', 'nama' => 'Aula Besar C301',    'kapasitas' => 200,'jenis' => 'Aula'],
            ['kode' => 'D101', 'nama' => 'Ruang Seminar D101', 'kapasitas' => 60, 'jenis' => 'Seminar'],
        ];
        foreach ($ruangData as $r) {
            MasterRuangan::firstOrCreate(['kode' => $r['kode']], $r);
        }

        // ── Master Kelas ──────────────────────────────────────────────────────
        $kelasData = [
            ['nama' => 'TI-1A', 'semester' => '1'],
            ['nama' => 'TI-3A', 'semester' => '3'],
            ['nama' => 'TI-5A', 'semester' => '5'],
            ['nama' => 'SI-3A', 'semester' => '3'],
            ['nama' => 'MT-1A', 'semester' => '1'],
        ];
        foreach ($kelasData as $kelas) {
            MasterKelas::firstOrCreate(['nama' => $kelas['nama']], $kelas);
        }

        // ── Mahasiswa (linked to mahasiswa user) ──────────────────────────────
        Mahasiswa::firstOrCreate(['nim' => '21001001'], [
            'nama'          => 'Andi Mahasiswa',
            'email'         => 'mahasiswa@jadwalkuliah.com',
            'angkatan'      => '2021',
            'program_studi' => 'Teknik Informatika',
            'user_id'       => $mhsUser->id,
        ]);

        // Extra mahasiswa tanpa user account
        $mhsExtra = [
            ['nim' => '21001002', 'nama' => 'Budi Prasetya',    'email' => 'budi@example.com',    'angkatan' => '2021', 'program_studi' => 'Teknik Informatika'],
            ['nim' => '21001003', 'nama' => 'Citra Dewi',       'email' => 'citra@example.com',   'angkatan' => '2021', 'program_studi' => 'Sistem Informasi'],
            ['nim' => '22001001', 'nama' => 'Doni Kusuma',      'email' => 'doni@example.com',    'angkatan' => '2022', 'program_studi' => 'Teknik Informatika'],
            ['nim' => '22001002', 'nama' => 'Eka Putri',        'email' => 'eka@example.com',     'angkatan' => '2022', 'program_studi' => 'Matematika'],
        ];
        foreach ($mhsExtra as $m) {
            Mahasiswa::firstOrCreate(['nim' => $m['nim']], $m);
        }

        // ── Jadwal Perkuliahan ────────────────────────────────────────────────
        $d = MasterDosen::pluck('id', 'nidn');
        $k = MasterKelas::pluck('id', 'nama');
        $mk = MasterMatakuliah::pluck('id', 'nama');
        $r = MasterRuangan::pluck('id', 'kode');

        $jadwalData = [
            ['kelas_id'=>$k['TI-1A'],'dosen_id'=>$d['0101018001'],'matakuliah_id'=>$mk['Algoritma & Pemrograman'],'ruangan_id'=>$r['B201'],'prodi'=>'Teknik Informatika','semester'=>'1','hari'=>'Senin','waktu_mulai'=>'08:00','waktu_selesai'=>'10:30'],
            ['kelas_id'=>$k['SI-3A'],'dosen_id'=>$d['0202028002'],'matakuliah_id'=>$mk['Basis Data'],             'ruangan_id'=>$r['A101'],'prodi'=>'Sistem Informasi',  'semester'=>'3','hari'=>'Senin','waktu_mulai'=>'10:30','waktu_selesai'=>'13:00'],
            ['kelas_id'=>$k['TI-3A'],'dosen_id'=>$d['0303038003'],'matakuliah_id'=>$mk['Jaringan Komputer'],       'ruangan_id'=>$r['B202'],'prodi'=>'Teknik Informatika','semester'=>'3','hari'=>'Selasa','waktu_mulai'=>'08:00','waktu_selesai'=>'10:30'],
            ['kelas_id'=>$k['TI-5A'],'dosen_id'=>$d['0101018001'],'matakuliah_id'=>$mk['Rekayasa Perangkat Lunak'],'ruangan_id'=>$r['A102'],'prodi'=>'Teknik Informatika','semester'=>'5','hari'=>'Selasa','waktu_mulai'=>'13:00','waktu_selesai'=>'15:30'],
            ['kelas_id'=>$k['MT-1A'],'dosen_id'=>$d['0404048004'],'matakuliah_id'=>$mk['Kalkulus'],               'ruangan_id'=>$r['A101'],'prodi'=>'Matematika',        'semester'=>'1','hari'=>'Rabu','waktu_mulai'=>'08:00','waktu_selesai'=>'10:30'],
            ['kelas_id'=>$k['SI-3A'],'dosen_id'=>$d['0505058005'],'matakuliah_id'=>$mk['Pemrograman Web'],        'ruangan_id'=>$r['B201'],'prodi'=>'Sistem Informasi',  'semester'=>'3','hari'=>'Rabu','waktu_mulai'=>'10:30','waktu_selesai'=>'13:00'],
            ['kelas_id'=>$k['TI-5A'],'dosen_id'=>$d['0606068006'],'matakuliah_id'=>$mk['Kecerdasan Buatan'],      'ruangan_id'=>$r['A102'],'prodi'=>'Teknik Informatika','semester'=>'5','hari'=>'Kamis','waktu_mulai'=>'08:00','waktu_selesai'=>'10:30'],
            ['kelas_id'=>$k['TI-3A'],'dosen_id'=>$d['0202028002'],'matakuliah_id'=>$mk['Struktur Data'],          'ruangan_id'=>$r['A101'],'prodi'=>'Teknik Informatika','semester'=>'3','hari'=>'Kamis','waktu_mulai'=>'13:00','waktu_selesai'=>'15:30'],
            ['kelas_id'=>$k['TI-3A'],'dosen_id'=>$d['0303038003'],'matakuliah_id'=>$mk['Sistem Operasi'],         'ruangan_id'=>$r['B202'],'prodi'=>'Teknik Informatika','semester'=>'3','hari'=>'Jumat','waktu_mulai'=>'08:00','waktu_selesai'=>'10:30'],
            ['kelas_id'=>$k['MT-1A'],'dosen_id'=>$d['0404048004'],'matakuliah_id'=>$mk['Statistika'],             'ruangan_id'=>$r['A102'],'prodi'=>'Matematika',        'semester'=>'1','hari'=>'Jumat','waktu_mulai'=>'10:30','waktu_selesai'=>'13:00'],
        ];

        foreach ($jadwalData as $j) {
            JadwalPerkuliahan::firstOrCreate(
                ['dosen_id'=>$j['dosen_id'],'hari'=>$j['hari'],'waktu_mulai'=>$j['waktu_mulai']],
                $j
            );
        }

        $defaultMahasiswa = Mahasiswa::where('nim', '21001001')->first();

        if ($defaultMahasiswa) {
            $krsJadwal = JadwalPerkuliahan::whereIn('matakuliah_id', [
                $mk['Algoritma & Pemrograman'],
                $mk['Rekayasa Perangkat Lunak'],
            ])->get();

            foreach ($krsJadwal as $jadwal) {
                KrsMahasiswa::firstOrCreate([
                    'mahasiswa_id' => $defaultMahasiswa->id,
                    'jadwal_id' => $jadwal->id,
                    'semester_akademik' => 'Ganjil 2024/2025',
                ], [
                    'status' => KrsMahasiswa::STATUS_AKTIF,
                ]);
            }
        }
    }
}
