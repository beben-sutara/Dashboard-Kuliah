<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('master_matakuliah', function (Blueprint $table) {
            $table->string('kode', 30)->nullable()->after('id');
            $table->unsignedTinyInteger('sks')->default(3)->after('nama');
        });

        $defaultMatakuliah = [
            'Algoritma & Pemrograman' => ['kode' => 'IF101', 'sks' => 3],
            'Basis Data' => ['kode' => 'SI201', 'sks' => 3],
            'Jaringan Komputer' => ['kode' => 'IF203', 'sks' => 3],
            'Rekayasa Perangkat Lunak' => ['kode' => 'IF205', 'sks' => 3],
            'Kecerdasan Buatan' => ['kode' => 'IF307', 'sks' => 3],
            'Sistem Operasi' => ['kode' => 'IF204', 'sks' => 3],
            'Pemrograman Web' => ['kode' => 'SI204', 'sks' => 3],
            'Struktur Data' => ['kode' => 'IF102', 'sks' => 3],
            'Kalkulus' => ['kode' => 'MT101', 'sks' => 3],
            'Statistika' => ['kode' => 'MT201', 'sks' => 2],
        ];

        DB::table('master_matakuliah')
            ->orderBy('id')
            ->get()
            ->each(function ($matakuliah) use ($defaultMatakuliah) {
                $fallbackCode = 'MK' . str_pad((string) $matakuliah->id, 3, '0', STR_PAD_LEFT);
                $defaults = $defaultMatakuliah[$matakuliah->nama] ?? ['kode' => $fallbackCode, 'sks' => 3];

                DB::table('master_matakuliah')
                    ->where('id', $matakuliah->id)
                    ->update([
                        'kode' => $matakuliah->kode ?: $defaults['kode'],
                        'sks' => $defaults['sks'],
                    ]);
            });

        Schema::table('master_matakuliah', function (Blueprint $table) {
            $table->unique('kode');
        });
    }

    public function down()
    {
        Schema::table('master_matakuliah', function (Blueprint $table) {
            $table->dropUnique(['kode']);
            $table->dropColumn(['kode', 'sks']);
        });
    }
};
