<?php

namespace Tests\Feature;

use App\Models\JadwalPerkuliahan;
use App\Models\MasterDosen;
use App\Models\MasterKelas;
use App\Models\MasterMatakuliah;
use App\Models\MasterRuangan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ScheduleDisplayFormatTest extends TestCase
{
    use DatabaseTransactions;

    public function test_baak_dashboard_shows_schedule_columns_with_kode_and_sks(): void
    {
        $user = User::factory()->create(['role' => 'baak']);
        $dosen = MasterDosen::create([
            'nidn' => 'D-401',
            'nama' => 'Dosen BAAK',
            'prodi' => 'Teknik Informatika',
        ]);
        $ruangan = MasterRuangan::create([
            'kode' => 'LAB01',
            'nama' => 'Lab 01',
            'kapasitas' => 30,
            'jenis' => 'Lab',
        ]);
        $matakuliah = MasterMatakuliah::create([
            'kode' => 'IF101',
            'nama' => 'Algoritma & Pemrograman',
            'sks' => 3,
        ]);
        $kelas = MasterKelas::create([
            'nama' => 'TI-1A',
            'semester' => '1',
        ]);

        JadwalPerkuliahan::create([
            'kelas_id' => $kelas->id,
            'dosen_id' => $dosen->id,
            'matakuliah_id' => $matakuliah->id,
            'ruangan_id' => $ruangan->id,
            'prodi' => 'Teknik Informatika',
            'semester' => '1',
            'hari' => 'Senin',
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
        ]);

        $response = $this->actingAs($user)->get(route('baak.dashboard'));

        $response->assertOk();
        $response->assertSee('KODE');
        $response->assertSee('SKS');
        $response->assertSee('IF101');
        $response->assertSee('Algoritma & Pemrograman');
        $response->assertSee('TI-1A');
    }

    public function test_live_data_returns_kode_and_sks_fields(): void
    {
        $dosen = MasterDosen::create([
            'nidn' => 'D-402',
            'nama' => 'Dosen Live',
            'prodi' => 'Sistem Informasi',
        ]);
        $ruangan = MasterRuangan::create([
            'kode' => 'A101',
            'nama' => 'Ruang A101',
            'kapasitas' => 40,
            'jenis' => 'Teori',
        ]);
        $matakuliah = MasterMatakuliah::create([
            'kode' => 'SI201',
            'nama' => 'Basis Data',
            'sks' => 3,
        ]);
        $kelas = MasterKelas::create([
            'nama' => 'SI-5A',
            'semester' => '5',
        ]);

        JadwalPerkuliahan::create([
            'kelas_id' => $kelas->id,
            'dosen_id' => $dosen->id,
            'matakuliah_id' => $matakuliah->id,
            'ruangan_id' => $ruangan->id,
            'prodi' => 'Sistem Informasi',
            'semester' => '999',
            'hari' => JadwalPerkuliahan::hariSekarang(),
            'waktu_mulai' => now()->subMinutes(10)->format('H:i'),
            'waktu_selesai' => now()->addMinutes(40)->format('H:i'),
        ]);

        $response = $this->get(route('live.data'));

        $response->assertOk();
        $response->assertJsonFragment([
            'smt' => '5',
            'kode' => 'SI201',
            'kelas' => 'SI-5A',
            'sks' => 3,
            'matakuliah' => 'Basis Data',
        ]);
    }
}
