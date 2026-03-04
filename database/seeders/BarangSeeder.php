<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\KategoriBarang;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        $atk        = KategoriBarang::where('nama', 'ATK')->value('id');
        $elektronik = KategoriBarang::where('nama', 'Elektronik')->value('id');
        $printer    = KategoriBarang::where('nama', 'Printer')->value('id');

        $barang = [
            ['kode_barang'=>'BRG-ATK-001','nama'=>'Kertas HVS A4 80gsm',      'kategori_id'=>$atk,       'satuan'=>'Rim',   'stok'=>150,'stok_minimum'=>10],
            ['kode_barang'=>'BRG-ATK-002','nama'=>'Pulpen Pilot G2',           'kategori_id'=>$atk,       'satuan'=>'Pcs',   'stok'=>45, 'stok_minimum'=>10],
            ['kode_barang'=>'BRG-ATK-003','nama'=>'Stapler Besar',             'kategori_id'=>$atk,       'satuan'=>'Pcs',   'stok'=>8,  'stok_minimum'=>5],
            ['kode_barang'=>'BRG-ATK-004','nama'=>'Amplop Coklat Besar',       'kategori_id'=>$atk,       'satuan'=>'Pcs',   'stok'=>0,  'stok_minimum'=>20],
            ['kode_barang'=>'BRG-ATK-005','nama'=>'Map Snelhecter',            'kategori_id'=>$atk,       'satuan'=>'Pcs',   'stok'=>60, 'stok_minimum'=>10],
            ['kode_barang'=>'BRG-ATK-006','nama'=>'Spidol Whiteboard',         'kategori_id'=>$atk,       'satuan'=>'Pcs',   'stok'=>12, 'stok_minimum'=>5],
            ['kode_barang'=>'BRG-ATK-007','nama'=>'Kertas F4 80gsm',           'kategori_id'=>$atk,       'satuan'=>'Rim',   'stok'=>5,  'stok_minimum'=>10],
            ['kode_barang'=>'BRG-ATK-008','nama'=>'Penggaris 30cm',            'kategori_id'=>$atk,       'satuan'=>'Pcs',   'stok'=>30, 'stok_minimum'=>5],
            ['kode_barang'=>'BRG-ATK-009','nama'=>'Isi Staples No.10',         'kategori_id'=>$atk,       'satuan'=>'Kotak', 'stok'=>25, 'stok_minimum'=>5],
            ['kode_barang'=>'BRG-ATK-010','nama'=>'Gunting Besar',             'kategori_id'=>$atk,       'satuan'=>'Pcs',   'stok'=>10, 'stok_minimum'=>3],
            ['kode_barang'=>'BRG-ELK-001','nama'=>'Mouse Wireless',            'kategori_id'=>$elektronik,'satuan'=>'Pcs',   'stok'=>0,  'stok_minimum'=>5],
            ['kode_barang'=>'BRG-ELK-002','nama'=>'Flash Disk 32GB',           'kategori_id'=>$elektronik,'satuan'=>'Pcs',   'stok'=>20, 'stok_minimum'=>5],
            ['kode_barang'=>'BRG-ELK-003','nama'=>'Kabel USB Type-C',          'kategori_id'=>$elektronik,'satuan'=>'Pcs',   'stok'=>3,  'stok_minimum'=>5],
            ['kode_barang'=>'BRG-PRT-001','nama'=>'Tinta Printer Canon BK',    'kategori_id'=>$printer,   'satuan'=>'Botol', 'stok'=>3,  'stok_minimum'=>5],
            ['kode_barang'=>'BRG-PRT-002','nama'=>'Toner Printer HP LaserJet', 'kategori_id'=>$printer,   'satuan'=>'Pcs',   'stok'=>4,  'stok_minimum'=>2],
        ];

        foreach ($barang as $b) {
            Barang::firstOrCreate(
                ['kode_barang' => $b['kode_barang']],
                array_merge($b, ['is_active' => true])
            );
        }
    }
}
