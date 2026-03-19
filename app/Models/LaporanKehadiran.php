<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKehadiran extends Model
{
    use HasFactory;

    public const JENIS_DOSEN_TIDAK_HADIR = 'dosen_tidak_hadir';
    public const JENIS_HANYA_MEMBERI_TUGAS = 'hanya_memberi_tugas';

    public const STATUS_PENDING = 'pending';
    public const STATUS_VALID = 'valid';
    public const STATUS_DITOLAK = 'ditolak';

    protected $table = 'laporan_kehadiran';

    protected $fillable = [
        'jadwal_id',
        'mahasiswa_id',
        'tanggal',
        'jenis_laporan',
        'catatan_mahasiswa',
        'status_validasi',
        'reviewed_by',
        'catatan_baak',
        'reviewed_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function jadwal()
    {
        return $this->belongsTo(JadwalPerkuliahan::class, 'jadwal_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
