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
            $table->string('waktu', 20)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('master_matakuliah', function (Blueprint $table) {
            $table->unsignedSmallInteger('waktu')->nullable()->change();
        });
    }
};
