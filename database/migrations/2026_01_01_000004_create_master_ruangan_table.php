<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('master_ruangan', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->unsignedSmallInteger('kapasitas');
            $table->enum('jenis', ['Teori', 'Lab', 'Aula', 'Seminar'])->default('Teori');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_ruangan');
    }
};
