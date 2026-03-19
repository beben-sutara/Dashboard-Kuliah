<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('laporan_kehadiran', function (Blueprint $table) {
            $table->enum('jenis_laporan', ['dosen_tidak_hadir', 'hanya_memberi_tugas'])
                ->default('dosen_tidak_hadir')
                ->after('tanggal');
            $table->text('catatan_mahasiswa')->nullable()->after('jenis_laporan');
            $table->foreignId('reviewed_by')->nullable()->after('status_validasi')->constrained('users')->nullOnDelete();
            $table->text('catatan_baak')->nullable()->after('reviewed_by');
            $table->timestamp('reviewed_at')->nullable()->after('catatan_baak');
        });
    }

    public function down()
    {
        Schema::table('laporan_kehadiran', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'jenis_laporan',
                'catatan_mahasiswa',
                'reviewed_by',
                'catatan_baak',
                'reviewed_at',
            ]);
        });
    }
};
