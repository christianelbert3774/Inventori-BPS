<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengadaanDetail extends Model
{
    protected $table = 'pengadaan_detail';

    protected $fillable = [
        'pengadaan_id',
        'tipe_item',        // 'restock' atau 'baru'
        'barang_id',        // nullable jika tipe_item = 'baru' (belum ada barang)
        'nama_barang_baru', // hanya diisi jika tipe_item = 'baru'
        'satuan_baru',      // hanya diisi jika tipe_item = 'baru'
        'kategori_baru',    // hanya diisi jika tipe_item = 'baru'
        'jumlah',
        'alasan',
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
