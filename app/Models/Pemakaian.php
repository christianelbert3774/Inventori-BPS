<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemakaian extends Model
{
    protected $table = 'pemakaian';

    protected $fillable = [
        'user_id',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // ── RELATIONSHIPS ──

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function details()
    {
        return $this->hasMany(PemakaianDetail::class);
    }
}
