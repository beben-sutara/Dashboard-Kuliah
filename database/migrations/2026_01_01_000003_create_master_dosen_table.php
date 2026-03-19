<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('master_dosen', function (Blueprint $table) {
            $table->id();
            $table->string('nidn')->unique();
            $table->string('nama');
            $table->string('prodi');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_dosen');
    }
};
