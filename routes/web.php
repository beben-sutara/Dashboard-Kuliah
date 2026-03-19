<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LiveDashboardController;
use App\Http\Controllers\Baak\DashboardController as BaakDashboard;
use App\Http\Controllers\Baak\JadwalController as BaakJadwal;
use App\Http\Controllers\Baak\MasterDosenController;
use App\Http\Controllers\Baak\MasterRuanganController;
use App\Http\Controllers\Baak\MasterMatakuliahController;
use App\Http\Controllers\Baak\MasterKelasController;
use App\Http\Controllers\Baak\KrsMahasiswaController;
use App\Http\Controllers\Baak\PengajuanJadwalDosenController as BaakPengajuanJadwalDosenController;
use App\Http\Controllers\Dosen\PortalController as DosenPortal;
use App\Http\Controllers\Dosen\PengajuanJadwalController as DosenPengajuanJadwalController;
use App\Http\Controllers\Mahasiswa\PortalController as MahasiswaPortal;
use Illuminate\Support\Facades\Route;

// Root: redirect based on role
Route::get('/', function () {
    if (!auth()->check()) return redirect()->route('login');
    return match(auth()->user()->role) {
        'baak'      => redirect()->route('baak.dashboard'),
        'dosen'     => redirect()->route('dosen.portal'),
        'mahasiswa' => redirect()->route('mahasiswa.portal'),
        default     => redirect()->route('login'),
    };
});

// ─── Live Dashboard (Public — no login required) ──────────────────────────────
Route::get('/live', [LiveDashboardController::class, 'index'])->name('live.dashboard');
Route::get('/live/data', [LiveDashboardController::class, 'data'])->name('live.data');

// ─── INSUN AI Chatbot (Public) ────────────────────────────────────────────────
Route::post('/insun/chat', [\App\Http\Controllers\InsunController::class, 'chat'])->name('insun.chat');

// ─── BAAK Routes ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:baak'])->prefix('baak')->name('baak.')->group(function () {
    Route::get('/dashboard', [BaakDashboard::class, 'index'])->name('dashboard');

    // Jadwal CRUD (form on dashboard, no index/show/create/edit views needed)
    Route::post('/jadwal',           [BaakJadwal::class, 'store'])->name('jadwal.store');
    Route::put('/jadwal/{jadwal}',   [BaakJadwal::class, 'update'])->name('jadwal.update');
    Route::delete('/jadwal/{jadwal}',[BaakJadwal::class, 'destroy'])->name('jadwal.destroy');

    // Master Data
    Route::get('/master/dosen',                           [MasterDosenController::class, 'index'])->name('master.dosen');
    Route::post('/master/dosen',                          [MasterDosenController::class, 'store'])->name('master.dosen.store');
    Route::put('/master/dosen/{masterDosen}',             [MasterDosenController::class, 'update'])->name('master.dosen.update');
    Route::delete('/master/dosen/{masterDosen}',          [MasterDosenController::class, 'destroy'])->name('master.dosen.destroy');

    Route::get('/master/ruangan',                         [MasterRuanganController::class, 'index'])->name('master.ruangan');
    Route::post('/master/ruangan',                        [MasterRuanganController::class, 'store'])->name('master.ruangan.store');
    Route::put('/master/ruangan/{masterRuangan}',         [MasterRuanganController::class, 'update'])->name('master.ruangan.update');
    Route::delete('/master/ruangan/{masterRuangan}',      [MasterRuanganController::class, 'destroy'])->name('master.ruangan.destroy');

    Route::get('/master/matakuliah',                      [MasterMatakuliahController::class, 'index'])->name('master.matakuliah');
    Route::post('/master/matakuliah',                     [MasterMatakuliahController::class, 'store'])->name('master.matakuliah.store');
    Route::put('/master/matakuliah/{masterMatakuliah}',   [MasterMatakuliahController::class, 'update'])->name('master.matakuliah.update');
    Route::delete('/master/matakuliah/{masterMatakuliah}',[MasterMatakuliahController::class, 'destroy'])->name('master.matakuliah.destroy');

    Route::get('/master/kelas',                           [MasterKelasController::class, 'index'])->name('master.kelas');
    Route::post('/master/kelas',                          [MasterKelasController::class, 'store'])->name('master.kelas.store');
    Route::put('/master/kelas/{masterKelas}',             [MasterKelasController::class, 'update'])->name('master.kelas.update');
    Route::delete('/master/kelas/{masterKelas}',          [MasterKelasController::class, 'destroy'])->name('master.kelas.destroy');

    Route::get('/krs', [KrsMahasiswaController::class, 'index'])->name('krs.index');
    Route::post('/krs', [KrsMahasiswaController::class, 'store'])->name('krs.store');
    Route::put('/krs/{krsMahasiswa}', [KrsMahasiswaController::class, 'update'])->name('krs.update');
    Route::delete('/krs/{krsMahasiswa}', [KrsMahasiswaController::class, 'destroy'])->name('krs.destroy');

    Route::get('/pengajuan-dosen', [BaakPengajuanJadwalDosenController::class, 'index'])->name('pengajuan-dosen.index');
    Route::patch('/pengajuan-dosen/{pengajuanJadwalDosen}', [BaakPengajuanJadwalDosenController::class, 'update'])->name('pengajuan-dosen.update');
    Route::patch('/pengajuan-dosen/laporan-mahasiswa/{laporanKehadiran}', [BaakPengajuanJadwalDosenController::class, 'updateLaporan'])->name('pengajuan-dosen.laporan.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ─── Dosen Routes ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/portal', [DosenPortal::class, 'index'])->name('portal');
    Route::post('/pengajuan', [DosenPengajuanJadwalController::class, 'store'])->name('pengajuan.store');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ─── Mahasiswa Routes ─────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/portal', [MahasiswaPortal::class, 'index'])->name('portal');
    Route::post('/laporan', [MahasiswaPortal::class, 'laporan'])->name('laporan');
    Route::post('/insun', [MahasiswaPortal::class, 'insun'])->name('insun');
    Route::post('/krs/enroll',   [MahasiswaPortal::class, 'enrollKrs'])->name('krs.enroll');
    Route::post('/krs/unenroll', [MahasiswaPortal::class, 'unenrollKrs'])->name('krs.unenroll');
    Route::patch('/profil',      [MahasiswaPortal::class, 'updateProfil'])->name('profil.update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
