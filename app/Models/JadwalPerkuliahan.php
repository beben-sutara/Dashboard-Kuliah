<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPerkuliahan extends Model
{
    use HasFactory;

    public const HARI_ORDER = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    protected $table = 'jadwal_perkuliahan';

    protected $fillable = [
        'dosen_id', 'matakuliah_id', 'ruangan_id', 'kelas_id',
        'prodi', 'semester', 'hari', 'waktu_mulai', 'waktu_selesai'
    ];

    public function dosen()
    {
        return $this->belongsTo(MasterDosen::class, 'dosen_id');
    }

    public function matakuliah()
    {
        return $this->belongsTo(MasterMatakuliah::class, 'matakuliah_id');
    }

    public function ruangan()
    {
        return $this->belongsTo(MasterRuangan::class, 'ruangan_id');
    }

    public function kelas()
    {
        return $this->belongsTo(MasterKelas::class, 'kelas_id');
    }

    public function laporanKehadiran()
    {
        return $this->hasMany(LaporanKehadiran::class, 'jadwal_id');
    }

    public function krsMahasiswa()
    {
        return $this->hasMany(KrsMahasiswa::class, 'jadwal_id');
    }

    public function mahasiswa()
    {
        return $this->belongsToMany(Mahasiswa::class, 'krs_mahasiswa', 'jadwal_id', 'mahasiswa_id')
            ->withPivot(['semester_akademik', 'status'])
            ->withTimestamps();
    }

    public function pengajuanJadwalDosen()
    {
        return $this->hasMany(PengajuanJadwalDosen::class, 'jadwal_id');
    }

    public function scopeForDosen(Builder $query, int $dosenId): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('dosen_id'), $dosenId);
    }

    public function scopeForMahasiswaAktif(Builder $query, Mahasiswa $mahasiswa): Builder
    {
        $model = $query->getModel();

        return $query
            ->select($model->qualifyColumn('*'))
            ->join('krs_mahasiswa', 'krs_mahasiswa.jadwal_id', '=', $model->qualifyColumn('id'))
            ->where('krs_mahasiswa.mahasiswa_id', $mahasiswa->id)
            ->where('krs_mahasiswa.status', KrsMahasiswa::STATUS_AKTIF)
            ->distinct();
    }

    public function scopeForHari(Builder $query, string $hari): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('hari'), $hari);
    }

    public function scopeOrderedWeekly(Builder $query): Builder
    {
        $hariColumn = $query->getModel()->qualifyColumn('hari');
        $waktuColumn = $query->getModel()->qualifyColumn('waktu_mulai');

        return $query
            ->orderByRaw(
                "CASE {$hariColumn}
                    WHEN 'Senin' THEN 1
                    WHEN 'Selasa' THEN 2
                    WHEN 'Rabu' THEN 3
                    WHEN 'Kamis' THEN 4
                    WHEN 'Jumat' THEN 5
                    WHEN 'Sabtu' THEN 6
                    ELSE 7
                END"
            )
            ->orderBy($waktuColumn);
    }

    public static function hariSekarang(): string
    {
        return [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ][now()->format('l')] ?? 'Senin';
    }

    public function isAktif(): bool
    {
        $now = now();

        if ($this->hari !== static::hariSekarang()) {
            return false;
        }

        $start = Carbon::createFromTimeString((string) $this->waktu_mulai);
        $end = Carbon::createFromTimeString((string) $this->waktu_selesai);

        return $now->between($start, $end);
    }
}
