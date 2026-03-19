<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('master_kelas')) {
            Schema::create('master_kelas', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100)->unique();
                $table->string('semester', 20);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('jadwal_perkuliahan') && ! Schema::hasColumn('jadwal_perkuliahan', 'kelas_id')) {
            Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
                $table->foreignId('kelas_id')
                    ->nullable()
                    ->after('ruangan_id')
                    ->constrained('master_kelas')
                    ->nullOnDelete();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('jadwal_perkuliahan') && Schema::hasColumn('jadwal_perkuliahan', 'kelas_id')) {
            Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
                $table->dropConstrainedForeignId('kelas_id');
            });
        }

        Schema::dropIfExists('master_kelas');
    }
};
