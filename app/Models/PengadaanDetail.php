<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengadaanDetail extends Model
{
    protected $table = 'pengadaan_detail';

    protected $fillable = [
        'pengadaan_id',
        'barang_id',
        'jumlah',
    ];

    // ── RELATIONSHIPS ──

    public function pengadaan()
    {
        return $this->belongsTo(Pengadaan::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
