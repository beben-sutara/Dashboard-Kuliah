<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jadwal_perkuliahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('master_dosen')->onDelete('cascade');
            $table->foreignId('matakuliah_id')->constrained('master_matakuliah')->onDelete('cascade');
            $table->foreignId('ruangan_id')->constrained('master_ruangan')->onDelete('cascade');
            $table->string('prodi');
            $table->string('semester', 20);
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jadwal_perkuliahan');
    }
};
