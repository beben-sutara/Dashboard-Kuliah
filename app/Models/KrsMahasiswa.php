<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KrsMahasiswa extends Model
{
    use HasFactory;

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';
    public const STATUS_SELESAI = 'selesai';

    protected $table = 'krs_mahasiswa';

    protected $fillable = [
        'mahasiswa_id',
        'jadwal_id',
        'semester_akademik',
        'status',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalPerkuliahan::class, 'jadwal_id');
    }
}
