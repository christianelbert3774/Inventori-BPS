<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'role',
        'name',
        'email',
        'password',
        'nip',
        'bagian',
        'jabatan',
        'no_telp',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // ── RELATIONSHIPS ──

    public function pemakaians()
    {
        return $this->hasMany(Pemakaian::class);
    }

    public function pengadaans()
    {
        return $this->hasMany(Pengadaan::class);
    }

    // ── HELPERS ──

    public function isKaryawan(): bool
    {
        return $this->role === 'karyawan';
    }

    public function isDivisiUmum(): bool
    {
        return $this->role === 'divisi_umum';
    }

    public function isPbj(): bool
    {
        return $this->role === 'pejabat_pengadaan';
    }
}
