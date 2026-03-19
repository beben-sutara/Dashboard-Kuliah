<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanJadwalDosen extends Model
{
    use HasFactory;

    public const JENIS_LAPOR_ABSEN = 'lapor_absen';
    public const JENIS_RESCHEDULE = 'ajukan_jadwal_ulang';

    public const STATUS_PENDING = 'pending';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    protected $table = 'pengajuan_jadwal_dosen';

    protected $fillable = [
        'jadwal_id',
        'dosen_id',
        'jenis',
        'tanggal_kelas',
        'alasan',
        'status',
        'tanggal_pengganti',
        'waktu_mulai_pengganti',
        'waktu_selesai_pengganti',
        'ruangan_id_pengganti',
        'reviewed_by',
        'catatan_baak',
        'reviewed_at',
    ];

    protected $casts = [
        'tanggal_kelas' => 'date',
        'tanggal_pengganti' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function jadwal()
    {
        return $this->belongsTo(JadwalPerkuliahan::class, 'jadwal_id');
    }

    public function dosen()
    {
        return $this->belongsTo(MasterDosen::class, 'dosen_id');
    }

    public function ruanganPengganti()
    {
        return $this->belongsTo(MasterRuangan::class, 'ruangan_id_pengganti');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
