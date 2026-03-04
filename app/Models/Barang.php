<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'satuan',
        'stok',
    ];

    // ── RELATIONSHIPS ──

    public function pemakaianDetails()
    {
        return $this->hasMany(PemakaianDetail::class);
    }

    public function pengadaanDetails()
    {
        return $this->hasMany(PengadaanDetail::class);
    }

    // ── HELPERS ──

    public function getStatusAttribute(): string
    {
        if ($this->stok === 0) return 'habis';
        if ($this->stok <= 10) return 'hampir_habis';
        return 'tersedia';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'habis'        => 'Habis',
            'hampir_habis' => 'Hampir Habis',
            default        => 'Tersedia',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'habis'        => 'badge-empty',
            'hampir_habis' => 'badge-low',
            default        => 'badge-available',
        };
    }

    /**
     * Generate kode barang otomatis.
     * Format: BRG-XXXX (e.g. BRG-0001)
     */
    public static function generateKode(): string
    {
        $last = self::orderBy('id', 'desc')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'BRG-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
