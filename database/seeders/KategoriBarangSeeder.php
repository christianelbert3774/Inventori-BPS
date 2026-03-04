<?php

namespace Database\Seeders;

use App\Models\KategoriBarang;
use Illuminate\Database\Seeder;

class KategoriBarangSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'ATK',        'deskripsi' => 'Alat Tulis Kantor'],
            ['nama' => 'Elektronik', 'deskripsi' => 'Perangkat elektronik dan aksesoris'],
            ['nama' => 'Printer',    'deskripsi' => 'Tinta, toner, dan kebutuhan printer'],
            ['nama' => 'Komputer',   'deskripsi' => 'Komputer, laptop, dan aksesori'],
            ['nama' => 'Furnitur',   'deskripsi' => 'Meja, kursi, dan perabotan kantor'],
            ['nama' => 'Kebersihan', 'deskripsi' => 'Perlengkapan kebersihan kantor'],
            ['nama' => 'Lainnya',    'deskripsi' => 'Barang lainnya'],
        ];
        foreach ($data as $d) {
            KategoriBarang::firstOrCreate(['nama' => $d['nama']], array_merge($d, ['is_active' => true]));
        }
    }
}
