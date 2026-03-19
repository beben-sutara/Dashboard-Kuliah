<?php

namespace Tests\Feature;

use App\Models\MasterDosen;
use App\Models\MasterKelas;
use App\Models\MasterMatakuliah;
use App\Models\MasterRuangan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BaakMasterKelasManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_master_kelas_page_can_be_opened(): void
    {
        $user = User::factory()->create(['role' => 'baak']);

        $response = $this->actingAs($user)->get(route('baak.master.kelas'));

        $response->assertOk();
        $response->assertSee('Master Kelas');
        $response->assertSee('Semester');
    }

    public function test_baak_can_store_master_kelas(): void
    {
        $user = User::factory()->create(['role' => 'baak']);

        $response = $this->actingAs($user)->post(route('baak.master.kelas.store'), [
            'nama' => 'TI-3A',
            'semester' => '3',
        ]);

        $response->assertRedirect(route('baak.master.kelas'));

        $this->assertDatabaseHas('master_kelas', [
            'nama' => 'TI-3A',
            'semester' => '3',
        ]);
    }

    public function test_jadwal_store_uses_selected_kelas_semester(): void
    {
        $user = User::factory()->create(['role' => 'baak']);
        $kelas = MasterKelas::create([
            'nama' => 'SI-5A',
            'semester' => '5',
        ]);
        $dosen = MasterDosen::create([
            'nidn' => 'D-601',
            'nama' => 'Dosen Sinkron',
            'prodi' => 'Sistem Informasi',
        ]);
        $ruangan = MasterRuangan::create([
            'kode' => 'A201',
            'nama' => 'Ruang A201',
            'kapasitas' => 40,
            'jenis' => 'Teori',
        ]);
        $matakuliah = MasterMatakuliah::create([
            'kode' => 'SI501',
            'nama' => 'Sistem Pendukung Keputusan',
            'sks' => 3,
        ]);

        $response = $this->actingAs($user)->post(route('baak.jadwal.store'), [
            'kelas_id' => $kelas->id,
            'dosen_id' => $dosen->id,
            'matakuliah_id' => $matakuliah->id,
            'ruangan_id' => $ruangan->id,
            'prodi' => 'Sistem Informasi',
            'semester' => '999',
            'hari' => 'Senin',
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
        ]);

        $response->assertRedirect(route('baak.dashboard'));

        $this->assertDatabaseHas('jadwal_perkuliahan', [
            'kelas_id' => $kelas->id,
            'dosen_id' => $dosen->id,
            'matakuliah_id' => $matakuliah->id,
            'semester' => '5',
        ]);
    }
}
