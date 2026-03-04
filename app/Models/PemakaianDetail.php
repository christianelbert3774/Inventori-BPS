<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemakaianDetail extends Model
{
    protected $table = 'pemakaian_detail';

    protected $fillable = [
        'pemakaian_id',
        'barang_id',
        'jumlah',
    ];

    // ── RELATIONSHIPS ──

    public function pemakaian()
    {
        return $this->belongsTo(Pemakaian::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
