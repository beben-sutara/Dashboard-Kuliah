<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterMatakuliah extends Model
{
    use HasFactory;

    protected $table = 'master_matakuliah';

    protected $fillable = ['kode', 'nama', 'sks', 'waktu'];

    public function jadwalPerkuliahan()
    {
        return $this->hasMany(JadwalPerkuliahan::class, 'matakuliah_id');
    }
}
