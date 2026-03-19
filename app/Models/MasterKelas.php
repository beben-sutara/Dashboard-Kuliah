<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKelas extends Model
{
    use HasFactory;

    protected $table = 'master_kelas';

    protected $fillable = ['nama', 'semester'];

    public function jadwalPerkuliahan()
    {
        return $this->hasMany(JadwalPerkuliahan::class, 'kelas_id');
    }
}
