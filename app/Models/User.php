<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'google_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isBaak(): bool { return $this->role === 'baak'; }
    public function isDosen(): bool { return $this->role === 'dosen'; }
    public function isMahasiswa(): bool { return $this->role === 'mahasiswa'; }

    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class);
    }

    public function masterDosen()
    {
        return $this->hasOne(MasterDosen::class);
    }

    public function pengajuanJadwalDitinjau()
    {
        return $this->hasMany(PengajuanJadwalDosen::class, 'reviewed_by');
    }

    public function laporanKehadiranDitinjau()
    {
        return $this->hasMany(LaporanKehadiran::class, 'reviewed_by');
    }
}
