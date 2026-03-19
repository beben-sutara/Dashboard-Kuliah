<?php

namespace Tests\Feature;

use App\Models\MasterDosen;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BaakMasterDosenManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_master_dosen_page_uses_nuptk_label(): void
    {
        $user = User::factory()->create(['role' => 'baak']);

        $response = $this->actingAs($user)->get(route('baak.master.dosen'));

        $response->assertOk();
        $response->assertSee('NUPTK');
    }

    public function test_baak_can_store_dosen_without_nuptk(): void
    {
        $user = User::factory()->create(['role' => 'baak']);

        $response = $this->actingAs($user)->post(route('baak.master.dosen.store'), [
            'nidn' => '',
            'nama' => 'Dosen Tanpa NUPTK',
            'prodi' => 'Teknik Informatika',
        ]);

        $response->assertRedirect(route('baak.master.dosen'));

        $this->assertDatabaseHas('master_dosen', [
            'nama' => 'Dosen Tanpa NUPTK',
            'prodi' => 'Teknik Informatika',
            'nidn' => null,
        ]);
    }

    public function test_baak_can_update_dosen_and_clear_nuptk(): void
    {
        $user = User::factory()->create(['role' => 'baak']);
        $dosen = MasterDosen::create([
            'nidn' => 'D-500',
            'nama' => 'Dosen Update',
            'prodi' => 'Sistem Informasi',
        ]);

        $response = $this->actingAs($user)->put(route('baak.master.dosen.update', $dosen), [
            'nidn' => '',
            'nama' => 'Dosen Update',
            'prodi' => 'Sistem Informasi',
        ]);

        $response->assertRedirect(route('baak.master.dosen'));

        $this->assertDatabaseHas('master_dosen', [
            'id' => $dosen->id,
            'nidn' => null,
        ]);
    }
}
