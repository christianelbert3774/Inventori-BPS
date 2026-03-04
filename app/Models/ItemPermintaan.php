<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPermintaan extends Model
{
    use HasFactory;

    protected $table = 'item_permintaan';

    protected $fillable = [
        'permintaan_id',
        'barang_id',
        'jumlah',
        'keterangan',
    ];

    protected $casts = ['jumlah' => 'integer'];

    public function permintaan() { return $this->belongsTo(PermintaanPemakaian::class, 'permintaan_id'); }
    public function barang()     { return $this->belongsTo(Barang::class, 'barang_id'); }
}
