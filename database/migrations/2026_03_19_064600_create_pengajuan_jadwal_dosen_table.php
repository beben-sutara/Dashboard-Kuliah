<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengajuan_jadwal_dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwal_perkuliahan')->onDelete('cascade');
            $table->foreignId('dosen_id')->constrained('master_dosen')->onDelete('cascade');
            $table->enum('jenis', ['lapor_absen', 'ajukan_jadwal_ulang']);
            $table->date('tanggal_kelas');
            $table->text('alasan');
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->date('tanggal_pengganti')->nullable();
            $table->time('waktu_mulai_pengganti')->nullable();
            $table->time('waktu_selesai_pengganti')->nullable();
            $table->foreignId('ruangan_id_pengganti')->nullable()->constrained('master_ruangan')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_baak')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['dosen_id', 'status']);
            $table->index(['jadwal_id', 'tanggal_kelas']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengajuan_jadwal_dosen');
    }
};
