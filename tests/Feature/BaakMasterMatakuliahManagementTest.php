<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BaakMasterMatakuliahManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_master_matakuliah_page_does_not_show_schedule_count_column(): void
    {
        $user = User::factory()->create(['role' => 'baak']);

        $response = $this->actingAs($user)->get(route('baak.master.matakuliah'));

        $response->assertOk();
        $response->assertSee('Kode');
        $response->assertSee('Nama Mata Kuliah');
        $response->assertSee('SKS');
        $response->assertDontSee('Jml Jadwal');
    }
}
