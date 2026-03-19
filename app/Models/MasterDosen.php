<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDosen extends Model
{
    use HasFactory;

    protected $table = 'master_dosen';

    protected $fillable = ['nidn', 'nama', 'prodi', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jadwalPerkuliahan()
    {
        return $this->hasMany(JadwalPerkuliahan::class, 'dosen_id');
    }

    public function pengajuanJadwalDosen()
    {
        return $this->hasMany(PengajuanJadwalDosen::class, 'dosen_id');
    }
}
