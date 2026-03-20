<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemesterAkademik extends Model
{
    use HasFactory;

    protected $table = 'semester_akademik';

    protected $fillable = [
        'nama',
        'tipe',
        'tahun_mulai',
        'tahun_akhir',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_aktif',
    ];

    protected $casts = [
        'tanggal_mulai'  => 'date',
        'tanggal_selesai' => 'date',
        'is_aktif'       => 'boolean',
    ];

    // Get the currently active semester
    public static function aktif(): ?self
    {
        return static::where('is_aktif', true)->first();
    }

    // Get the active semester ID (or null)
    public static function aktifId(): ?int
    {
        return static::where('is_aktif', true)->value('id');
    }

    // Scope: only active
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    // Set this semester as active (deactivate others)
    public function setAktif(): void
    {
        static::where('is_aktif', true)->update(['is_aktif' => false]);
        $this->update(['is_aktif' => true]);
    }

    public function jadwalPerkuliahan()
    {
        return $this->hasMany(JadwalPerkuliahan::class, 'semester_akademik_id');
    }
}
