<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('laporan_kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwal_perkuliahan')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('status_validasi', ['pending', 'valid', 'ditolak'])->default('pending');
            $table->timestamps();

            // Anti-spam composite unique key
            $table->unique(['mahasiswa_id', 'jadwal_id', 'tanggal']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('laporan_kehadiran');
    }
};
