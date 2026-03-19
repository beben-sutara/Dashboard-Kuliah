<?php

namespace Tests\Feature;

use App\Models\JadwalPerkuliahan;
use App\Models\KrsMahasiswa;
use App\Models\LaporanKehadiran;
use App\Models\Mahasiswa;
use App\Models\MasterDosen;
use App\Models\MasterKelas;
use App\Models\MasterMatakuliah;
use App\Models\MasterRuangan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BaakMahasiswaReportReviewTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mahasiswa_can_submit_report_with_reason_and_note(): void
    {
        Http::fake([
            '*' => Http::response(['threshold_reached' => false], 200),
        ]);

        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Mahasiswa::create([
            'nim' => 'M-120',
            'nama' => 'Mahasiswa Pelapor',
            'email' => 'pelapor@example.com',
            'angkatan' => '2023',
            'program_studi' => 'Sistem Informasi',
            'user_id' => $studentUser->id,
        ]);
        $lecturer = MasterDosen::create([
            'nidn' => 'D-710',
            'nama' => 'Dosen Terlapor',
            'prodi' => 'Sistem Informasi',
        ]);
        $kelas = MasterKelas::create([
            'nama' => 'SI-3A',
            'semester' => '3',
        ]);
        $room = MasterRuangan::create([
            'kode' => 'R301',
            'nama' => 'Ruang 301',
            'kapasitas' => 35,
            'jenis' => 'Teori',
        ]);
        $course = MasterMatakuliah::create([
            'kode' => 'SI330',
            'nama' => 'Analisis Sistem',
            'sks' => 3,
        ]);
        $schedule = JadwalPerkuliahan::create([
            'kelas_id' => $kelas->id,
            'dosen_id' => $lecturer->id,
            'matakuliah_id' => $course->id,
            'ruangan_id' => $room->id,
            'prodi' => 'Sistem Informasi',
            'semester' => '3',
            'hari' => 'Senin',
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
        ]);

        KrsMahasiswa::create([
            'mahasiswa_id' => $student->id,
            'jadwal_id' => $schedule->id,
            'semester_akademik' => 'Genap 2025/2026',
            'status' => KrsMahasiswa::STATUS_AKTIF,
        ]);

        $response = $this->actingAs($studentUser)->postJson(route('mahasiswa.laporan'), [
            'jadwal_id' => $schedule->id,
            'jenis_laporan' => LaporanKehadiran::JENIS_HANYA_MEMBERI_TUGAS,
            'catatan_mahasiswa' => 'Dosen hanya memberi tugas tanpa tatap muka.',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('laporan_kehadiran', [
            'jadwal_id' => $schedule->id,
            'mahasiswa_id' => $student->id,
            'jenis_laporan' => LaporanKehadiran::JENIS_HANYA_MEMBERI_TUGAS,
            'catatan_mahasiswa' => 'Dosen hanya memberi tugas tanpa tatap muka.',
            'status_validasi' => LaporanKehadiran::STATUS_PENDING,
        ]);
    }

    public function test_baak_pengajuan_page_shows_student_reports(): void
    {
        $baak = User::factory()->create(['role' => 'baak']);
        $student = Mahasiswa::create([
            'nim' => 'M-121',
            'nama' => 'Mahasiswa Review',
            'email' => 'review@example.com',
            'angkatan' => '2023',
            'program_studi' => 'Teknik Informatika',
        ]);
        $lecturer = MasterDosen::create([
            'nidn' => 'D-711',
            'nama' => 'Dosen Review',
            'prodi' => 'Teknik Informatika',
        ]);
        $kelas = MasterKelas::create([
            'nama' => 'TI-5A',
            'semester' => '5',
        ]);
        $room = MasterRuangan::create([
            'kode' => 'LAB03',
            'nama' => 'Lab 03',
            'kapasitas' => 30,
            'jenis' => 'Lab',
        ]);
        $course = MasterMatakuliah::create([
            'kode' => 'IF550',
            'nama' => 'Machine Learning',
            'sks' => 3,
        ]);
        $schedule = JadwalPerkuliahan::create([
            'kelas_id' => $kelas->id,
            'dosen_id' => $lecturer->id,
            'matakuliah_id' => $course->id,
            'ruangan_id' => $room->id,
            'prodi' => 'Teknik Informatika',
            'semester' => '5',
            'hari' => 'Selasa',
            'waktu_mulai' => '10:00',
            'waktu_selesai' => '12:00',
        ]);

        LaporanKehadiran::create([
            'jadwal_id' => $schedule->id,
            'mahasiswa_id' => $student->id,
            'tanggal' => now()->toDateString(),
            'jenis_laporan' => LaporanKehadiran::JENIS_HANYA_MEMBERI_TUGAS,
            'catatan_mahasiswa' => 'Mahasiswa hanya menerima tugas mandiri.',
            'status_validasi' => LaporanKehadiran::STATUS_PENDING,
        ]);

        $response = $this->actingAs($baak)->get(route('baak.pengajuan-dosen.index'));

        $response->assertOk();
        $response->assertSee('Laporan Mahasiswa');
        $response->assertSee('Hanya Memberi Tugas');
        $response->assertSee('Mahasiswa Review');
        $response->assertSee('Machine Learning');
    }

    public function test_baak_can_review_student_report(): void
    {
        $baak = User::factory()->create(['role' => 'baak']);
        $student = Mahasiswa::create([
            'nim' => 'M-122',
            'nama' => 'Mahasiswa Validasi',
            'email' => 'validasi@example.com',
            'angkatan' => '2023',
            'program_studi' => 'Sistem Informasi',
        ]);
        $lecturer = MasterDosen::create([
            'nidn' => 'D-712',
            'nama' => 'Dosen Validasi',
            'prodi' => 'Sistem Informasi',
        ]);
        $kelas = MasterKelas::create([
            'nama' => 'SI-1A',
            'semester' => '1',
        ]);
        $room = MasterRuangan::create([
            'kode' => 'A105',
            'nama' => 'Ruang A105',
            'kapasitas' => 40,
            'jenis' => 'Teori',
        ]);
        $course = MasterMatakuliah::create([
            'kode' => 'SI101',
            'nama' => 'Pengantar Sistem Informasi',
            'sks' => 3,
        ]);
        $schedule = JadwalPerkuliahan::create([
            'kelas_id' => $kelas->id,
            'dosen_id' => $lecturer->id,
            'matakuliah_id' => $course->id,
            'ruangan_id' => $room->id,
            'prodi' => 'Sistem Informasi',
            'semester' => '1',
            'hari' => 'Rabu',
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '09:40',
        ]);
        $report = LaporanKehadiran::create([
            'jadwal_id' => $schedule->id,
            'mahasiswa_id' => $student->id,
            'tanggal' => now()->toDateString(),
            'jenis_laporan' => LaporanKehadiran::JENIS_DOSEN_TIDAK_HADIR,
            'status_validasi' => LaporanKehadiran::STATUS_PENDING,
        ]);

        $response = $this->actingAs($baak)->patch(route('baak.pengajuan-dosen.laporan.update', $report), [
            'status_validasi' => LaporanKehadiran::STATUS_DITOLAK,
            'catatan_baak' => 'Perlu verifikasi ulang dengan dosen pengampu.',
        ]);

        $response->assertRedirect(route('baak.pengajuan-dosen.index'));

        $this->assertDatabaseHas('laporan_kehadiran', [
            'id' => $report->id,
            'status_validasi' => LaporanKehadiran::STATUS_DITOLAK,
            'catatan_baak' => 'Perlu verifikasi ulang dengan dosen pengampu.',
            'reviewed_by' => $baak->id,
        ]);
    }
}
