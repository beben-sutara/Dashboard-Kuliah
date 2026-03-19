<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_matakuliah', function (Blueprint $table) {
            $table->unsignedSmallInteger('waktu')->nullable()->after('sks')
                ->comment('Durasi per pertemuan dalam menit');
        });
    }

    public function down()
    {
        Schema::table('master_matakuliah', function (Blueprint $table) {
            $table->dropColumn('waktu');
        });
    }
};
