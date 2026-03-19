<?php

namespace Tests\Feature;

use App\Models\JadwalPerkuliahan;
use App\Models\KrsMahasiswa;
use App\Models\Mahasiswa;
use App\Models\MasterDosen;
use App\Models\MasterMatakuliah;
use App\Models\MasterRuangan;
use App\Models\PengajuanJadwalDosen;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PortalAccessRulesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dosen_portal_only_shows_logged_in_lecturer_schedule(): void
    {
        $lecturerUser = User::factory()->create(['role' => 'dosen']);
        $lecturer = MasterDosen::create([
            'nidn' => 'D-001',
            'nama' => 'Dosen Login',
            'prodi' => 'Teknik Informatika',
            'user_id' => $lecturerUser->id,
        ]);

        $otherLecturer = MasterDosen::create([
            'nidn' => 'D-002',
            'nama' => 'Dosen Lain',
            'prodi' => 'Sistem Informasi',
        ]);

        $room = MasterRuangan::create([
            'kode' => 'R101',
            'nama' => 'Ruang 101',
            'kapasitas' => 30,
            'jenis' => 'Teori',
        ]);

        $ownCourse = MasterMatakuliah::create(['nama' => 'Pemrograman Web']);
        $otherCourse = MasterMatakuliah::create(['nama' => 'Jaringan Komputer']);

        JadwalPerkuliahan::create([
            'dosen_id' => $lecturer->id,
            'matakuliah_id' => $ownCourse->id,
            'ruangan_id' => $room->id,
            'prodi' => 'Teknik Informatika',
            'semester' => '3',
            'hari' => 'Senin',
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
        ]);

        JadwalPerkuliahan::create([
            'dosen_id' => $otherLecturer->id,
            'matakuliah_id' => $otherCourse->id,
            'ruangan_id' => $room->id,
            'prodi' => 'Sistem Informasi',
            'semester' => '5',
            'hari' => 'Selasa',
            'waktu_mulai' => '10:00',
            'waktu_selesai' => '12:00',
        ]);

        $response = $this->actingAs($lecturerUser)->get(route('dosen.portal'));

        $response->assertOk();
        $response->assertSee('Pemrograman Web');
        $response->assertDontSee('Jaringan Komputer');
    }

    public function test_mahasiswa_portal_only_shows_active_enrolled_schedules(): void
    {
        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Mahasiswa::create([
            'nim' => 'M-001',
            'nama' => 'Mahasiswa Login',
            'email' => 'student@example.com',
            'angkatan' => '2023',
            'program_studi' => 'Teknik Informatika',
            'user_id' => $studentUser->id,
        ]);

        $lecturer = MasterDosen::create([
            'nidn' => 'D-100',
            'nama' => 'Dosen Portal',
            'prodi' => 'Teknik Informatika',
        ]);

        $room = MasterRuangan::create([
            'kode' => 'R102',
            'nama' => 'Ruang 102',
            'kapasitas' => 35,
            'jenis' => 'Teori',
        ]);

        $enrolledCourse = MasterMatakuliah::create(['nama' => 'Basis Data']);
        $otherCourse = MasterMatakuliah::create(['nama' => 'Sistem Operasi']);
        $completedCourse = MasterMatakuliah::create(['nama' => 'Statistika']);

        $enrolledSchedule = JadwalPerkuliahan::create([
            'dosen_id' => $lecturer->id,
            'matakuliah_id' => $enrolledCourse->id,
            'ruangan_id' => $room->id,
            'prodi' => 'Teknik Informatika',
            'semester' => '4',
            'hari' => 'Rabu',
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '09:40',
        ]);

        $otherSchedule = JadwalPerkuliahan::create([
            'dosen_id' => $lecturer->id,
            'matakuliah_id' => $otherCourse->id,
            'ruangan_id' => $room->id,
            'prodi' => 'Teknik Informatika',
            'semester' => '4',
            'hari' => 'Kamis',
            'waktu_mulai' => '10:00',
            'waktu_selesai' => '11:40',
        ]);

        $completedSchedule = JadwalPerkuliahan::create([
            'dosen_id' => $lecturer->id,
            'matakuliah_id' => $completedCourse->id,
            'ruangan_id' => $room->id,
            'prodi' => 'Teknik Informatika',
            'semester' => '2',
            'hari' => 'Jumat',
            'waktu_mulai' => '13:00',
            'waktu_selesai' => '14:40',
        ]);

        KrsMahasiswa::create([
            'mahasiswa_id' => $student->id,
            'jadwal_id' => $enrolledSchedule->id,
            'semester_akademik' => 'Genap 2025/2026',
            'status' => KrsMahasiswa::STATUS_AKTIF,
        ]);

        KrsMahasiswa::create([
            'mahasiswa_id' => $student->id,
            'jadwal_id' => $completedSchedule->id,
            'semester_akademik' => 'Ganjil 2024/2025',
            'status' => KrsMahasiswa::STATUS_SELESAI,
        ]);

        $response = $this->actingAs($studentUser)->get(route('mahasiswa.portal'));

        $response->assertOk();
        $response->assertSee('Basis Data');
        $response->assertDontSee('Sistem Operasi');
        $response->assertDontSee('Statistika');
    }

    public function test_mahasiswa_absence_report_rejects_schedule_outside_active_enrollment(): void
    {
        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Mahasiswa::create([
            'nim' => 'M-002',
            'nama' => 'Mahasiswa Validasi',
            'email' => 'validation@example.com',
            'angkatan' => '2023',
            'program_studi' => 'Sistem Informasi',
            'user_id' => $studentUser->id,
        ]);

        $lecturer = MasterDosen::create([
            'nidn' => 'D-200',
            'nama' => 'Dosen Validasi',
            'prodi' => 'Sistem Informasi',
        ]);

        $room = MasterRuangan::create([
            'kode' => 'R103',
            'nama' => 'Ruang 103',
            'kapasitas' => 40,
            'jenis' => 'Teori',
        ]);

        $outsideCourse = MasterMatakuliah::create(['nama' => 'Keamanan Informasi']);
        $outsideSchedule = JadwalPerkuliahan::create([
            'dosen_id' => $lecturer->id,
            'matakuliah_id' => $outsideCourse->id,
            'ruangan_id' => $room->id,
            'prodi' => 'Sistem Informasi',
            'semester' => '5',
            'hari' => 'Senin',
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '10:40',
        ]);

        $response = $this->actingAs($studentUser)->postJson(route('mahasiswa.laporan'), [
            'jadwal_id' => $outsideSchedule->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('laporan_kehadiran', 0);
    }

    public function test_dosen_can_create_request_only_for_their_own_schedule(): void
    {
        $lecturerUser = User::factory()->create(['role' => 'dosen']);
        $lecturer = MasterDosen::create([
            'nidn' => 'D-300',
            'nama' => 'Dosen Pengajuan',
            'prodi' => 'Teknik Informatika',
            'user_id' => $lecturerUser->id,
        ]);

        $otherLecturer = MasterDosen::create([
            'nidn' => 'D-301',
            'nama' => 'Dosen Lain',
            'prodi' => 'Teknik Informatika',
        ]);

        $room = MasterRuangan::create([
            'kode' => 'R104',
            'nama' => 'Ruang 104',
            'kapasitas' => 25,
            'jenis' => 'Teori',
        ]);

        $course = MasterMatakuliah::create(['nama' => 'Algoritma']);
        $ownSchedule = JadwalPerkuliahan::create([
            'dosen_id' => $lecturer->id,
            'matakuliah_id' => $course->id,
            'ruangan_id' => $room->id,
            'prodi' => 'Teknik Informatika',
            'semester' => '1',
            'hari' => 'Selasa',
            'waktu_mulai' => '07:30',
            'waktu_selesai' => '09:10',
        ]);

        $foreignSchedule = JadwalPerkuliahan::create([
            'dosen_id' => $otherLecturer->id,
            'matakuliah_id' => $course->id,
            'ruangan_id' => $room->id,
            'prodi' => 'Teknik Informatika',
            'semester' => '1',
            'hari' => 'Rabu',
            'waktu_mulai' => '07:30',
            'waktu_selesai' => '09:10',
        ]);

        $this->actingAs($lecturerUser)->post(route('dosen.pengajuan.store'), [
            'jadwal_id' => $ownSchedule->id,
            'jenis' => PengajuanJadwalDosen::JENIS_LAPOR_ABSEN,
            'tanggal_kelas' => now()->addDay()->toDateString(),
            'alasan' => 'Ada tugas dinas luar kampus.',
        ])->assertRedirect(route('dosen.portal'));

        $this->assertDatabaseHas('pengajuan_jadwal_dosen', [
            'jadwal_id' => $ownSchedule->id,
            'dosen_id' => $lecturer->id,
            'jenis' => PengajuanJadwalDosen::JENIS_LAPOR_ABSEN,
        ]);

        $response = $this->actingAs($lecturerUser)->post(route('dosen.pengajuan.store'), [
            'jadwal_id' => $foreignSchedule->id,
            'jenis' => PengajuanJadwalDosen::JENIS_LAPOR_ABSEN,
            'tanggal_kelas' => now()->addDays(2)->toDateString(),
            'alasan' => 'Tidak boleh untuk jadwal dosen lain.',
        ]);

        $response->assertForbidden();
    }
}
