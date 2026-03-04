<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengadaan extends Model
{
    protected $table = 'pengadaan';

    protected $fillable = [
        'user_id',
        'status_level2',
        'status_level3',
        'approved_level2_by',
        'processed_by_level3',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // ── RELATIONSHIPS ──

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedLevel2By()
    {
        return $this->belongsTo(User::class, 'approved_level2_by');
    }

    public function processedByLevel3()
    {
        return $this->belongsTo(User::class, 'processed_by_level3');
    }

    public function details()
    {
        return $this->hasMany(PengadaanDetail::class);
    }
}
