<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBarang extends Model
{
    use HasFactory;

    protected $table = 'kategori_barang';
    protected $fillable = ['nama', 'deskripsi', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function barang()
    {
        return $this->hasMany(Barang::class, 'kategori_id');
    }
}
