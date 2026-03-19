<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('krs_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('jadwal_id')->constrained('jadwal_perkuliahan')->onDelete('cascade');
            $table->string('semester_akademik', 50);
            $table->enum('status', ['aktif', 'nonaktif', 'selesai'])->default('aktif');
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'jadwal_id', 'semester_akademik'], 'krs_mahasiswa_unique_per_term');
        });
    }

    public function down()
    {
        Schema::dropIfExists('krs_mahasiswa');
    }
};
