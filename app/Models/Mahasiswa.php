<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = ['nim', 'nama', 'email', 'angkatan', 'program_studi', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function laporanKehadiran()
    {
        return $this->hasMany(LaporanKehadiran::class, 'mahasiswa_id');
    }

    public function krsMahasiswa()
    {
        return $this->hasMany(KrsMahasiswa::class, 'mahasiswa_id');
    }

    public function jadwalPerkuliahan()
    {
        return $this->belongsToMany(JadwalPerkuliahan::class, 'krs_mahasiswa', 'mahasiswa_id', 'jadwal_id')
            ->withPivot(['semester_akademik', 'status'])
            ->withTimestamps();
    }
}
