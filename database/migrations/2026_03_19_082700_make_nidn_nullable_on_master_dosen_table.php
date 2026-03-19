<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('master_dosen') || ! Schema::hasColumn('master_dosen', 'nidn')) {
            return;
        }

        DB::statement('ALTER TABLE master_dosen MODIFY nidn VARCHAR(255) NULL');
    }

    public function down()
    {
        if (! Schema::hasTable('master_dosen') || ! Schema::hasColumn('master_dosen', 'nidn')) {
            return;
        }

        DB::table('master_dosen')
            ->select('id')
            ->whereNull('nidn')
            ->orderBy('id')
            ->get()
            ->each(function ($dosen) {
                DB::table('master_dosen')
                    ->where('id', $dosen->id)
                    ->update([
                        'nidn' => 'NUPTK-' . str_pad((string) $dosen->id, 6, '0', STR_PAD_LEFT),
                    ]);
            });

        DB::statement('ALTER TABLE master_dosen MODIFY nidn VARCHAR(255) NOT NULL');
    }
};
