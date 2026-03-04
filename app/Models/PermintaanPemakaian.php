<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanPemakaian extends Model
{
    use HasFactory;

    protected $table = 'permintaan_pemakaian';

    protected $fillable = [
        'nomor_permintaan',
        'user_id',
        'bagian',
        'keperluan',
        'catatan',
        'status',
        'catatan_admin',
        'disetujui_oleh',
        'disetujui_pada',
    ];

    protected $casts = ['disetujui_pada' => 'datetime'];

    const STATUS_MENUNGGU  = 'menunggu';
    const STATUS_DISETUJUI = 'disetujui';
    const STATUS_DITOLAK   = 'ditolak';

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->nomor_permintaan)) {
                $model->nomor_permintaan = self::generateNomor();
            }
            if (empty($model->status)) {
                $model->status = self::STATUS_MENUNGGU;
            }
        });
    }

    public static function generateNomor(): string
    {
        $tahun  = now()->format('Y');
        $urutan = static::whereYear('created_at', $tahun)->count() + 1;
        return 'PRM-' . $tahun . '-' . str_pad($urutan, 3, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU  => 'Menunggu Persetujuan',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_DITOLAK   => 'Ditolak',
            default                => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU  => 'warning',
            self::STATUS_DISETUJUI => 'success',
            self::STATUS_DITOLAK   => 'danger',
            default                => 'secondary',
        };
    }

    public function isMenunggu(): bool { return $this->status === self::STATUS_MENUNGGU; }

    public function scopeMenunggu($query)         { return $query->where('status', self::STATUS_MENUNGGU); }
    public function scopeByUser($query, int $uid) { return $query->where('user_id', $uid); }

    public function user()          { return $this->belongsTo(User::class, 'user_id'); }
    public function items()         { return $this->hasMany(ItemPermintaan::class, 'permintaan_id'); }
    public function disetujuiOleh() { return $this->belongsTo(User::class, 'disetujui_oleh'); }
}
