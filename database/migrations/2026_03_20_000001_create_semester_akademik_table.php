<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('semester_akademik', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();       // "Ganjil 2024/2025"
            $table->enum('tipe', ['Ganjil', 'Genap']);
            $table->year('tahun_mulai');                  // 2024
            $table->year('tahun_akhir');                  // 2025
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        // Add semester_akademik_id to jadwal_perkuliahan
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            $table->foreignId('semester_akademik_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('semester_akademik')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            $table->dropForeign(['semester_akademik_id']);
            $table->dropColumn('semester_akademik_id');
        });

        Schema::dropIfExists('semester_akademik');
    }
};
