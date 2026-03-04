<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanPengadaan extends Model
{
    use HasFactory;

    protected $table = 'permintaan_pengadaan';

    protected $fillable = [
        'nomor_pengadaan',
        'user_id',
        'bagian',
        'jabatan',
        'nama_barang',
        'kategori',
        'jumlah',
        'satuan',
        'estimasi_harga',
        'spesifikasi',
        'alasan_type',
        'alasan_detail',
        'urgensi',
        'tanggal_dibutuhkan',
        'status',
        'catatan_divisi',
        'catatan_pengadaan',
        'diteruskan_oleh',
        'diteruskan_pada',
        'diselesaikan_oleh',
        'diselesaikan_pada',
    ];

    protected $casts = [
        'estimasi_harga'     => 'decimal:2',
        'tanggal_dibutuhkan' => 'date',
        'diteruskan_pada'    => 'datetime',
        'diselesaikan_pada'  => 'datetime',
    ];

    const STATUS_MENUNGGU   = 'menunggu';
    const STATUS_DITERUSKAN = 'diteruskan';
    const STATUS_DIPROSES   = 'diproses';
    const STATUS_SELESAI    = 'selesai';
    const STATUS_DITOLAK    = 'ditolak';

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->nomor_pengadaan)) {
                $model->nomor_pengadaan = self::generateNomor();
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
        return 'PGD-' . $tahun . '-' . str_pad($urutan, 3, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU   => 'Menunggu Persetujuan',
            self::STATUS_DITERUSKAN => 'Diteruskan ke Pengadaan',
            self::STATUS_DIPROSES   => 'Sedang Diproses',
            self::STATUS_SELESAI    => 'Selesai',
            self::STATUS_DITOLAK    => 'Ditolak',
            default                 => '-',
        };
    }

    public function getAlasanLabelAttribute(): string
    {
        return match ($this->alasan_type) {
            'stok_habis'         => 'Stok Barang Habis',
            'barang_baru'        => 'Barang Baru',
            'kerusakan'          => 'Penggantian Kerusakan',
            'kebutuhan_mendadak' => 'Kebutuhan Mendadak',
            default              => 'Lainnya',
        };
    }

    public function scopeMenunggu($query)         { return $query->where('status', self::STATUS_MENUNGGU); }
    public function scopeByUser($query, int $uid) { return $query->where('user_id', $uid); }

    public function user()             { return $this->belongsTo(User::class, 'user_id'); }
    public function diteruskanOleh()   { return $this->belongsTo(User::class, 'diteruskan_oleh'); }
    public function diselesaikanOleh() { return $this->belongsTo(User::class, 'diselesaikan_oleh'); }
}
