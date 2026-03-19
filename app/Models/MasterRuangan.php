<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterRuangan extends Model
{
    use HasFactory;

    protected $table = 'master_ruangan';

    protected $fillable = ['kode', 'nama', 'kapasitas', 'jenis'];

    public function jadwalPerkuliahan()
    {
        return $this->hasMany(JadwalPerkuliahan::class, 'ruangan_id');
    }
}
