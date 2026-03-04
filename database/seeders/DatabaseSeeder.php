<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── ROLES (sudah ada di SQL, tapi aman di-seed ulang) ──
        $roleKaryawan   = Role::firstOrCreate(['name' => 'karyawan']);
        $roleDivisi     = Role::firstOrCreate(['name' => 'divisi_umum']);
        $rolePbj        = Role::firstOrCreate(['name' => 'pbj']);

        // ── USERS ──
        User::firstOrCreate(
            ['email' => 'karyawan@bps.go.id'],
            [
                'role_id'  => $roleKaryawan->id,
                'name'     => 'Rizky Saputra',
                'password' => Hash::make('password'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@bps.go.id'],
            [
                'role_id'  => $roleDivisi->id,
                'name'     => 'Siti Aminah',
                'password' => Hash::make('password'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'pbj@bps.go.id'],
            [
                'role_id'  => $rolePbj->id,
                'name'     => 'Budi Santoso',
                'password' => Hash::make('password'),
            ]
        );

        // ── BARANG DUMMY ──
        $barangs = [
            ['kode_barang' => 'BRG-0001', 'nama_barang' => 'Kertas HVS A4 80gsm',       'satuan' => 'Rim',   'stok' => 82],
            ['kode_barang' => 'BRG-0002', 'nama_barang' => 'Tinta Printer Canon 740',   'satuan' => 'Pcs',   'stok' => 9],
            ['kode_barang' => 'BRG-0003', 'nama_barang' => 'Stapler Max HD-10',          'satuan' => 'Pcs',   'stok' => 24],
            ['kode_barang' => 'BRG-0004', 'nama_barang' => 'Hand Sanitizer 500ml',      'satuan' => 'Botol', 'stok' => 0],
            ['kode_barang' => 'BRG-0005', 'nama_barang' => 'Mouse Wireless Logitech',   'satuan' => 'Unit',  'stok' => 18],
            ['kode_barang' => 'BRG-0006', 'nama_barang' => 'Buku Agenda Tahunan',       'satuan' => 'Pcs',   'stok' => 6],
            ['kode_barang' => 'BRG-0007', 'nama_barang' => 'Amplop Coklat Besar',       'satuan' => 'Pcs',   'stok' => 190],
            ['kode_barang' => 'BRG-0008', 'nama_barang' => 'Spidol Whiteboard',         'satuan' => 'Pcs',   'stok' => 45],
            ['kode_barang' => 'BRG-0009', 'nama_barang' => 'Binder Clip No.155',        'satuan' => 'Pak',   'stok' => 30],
            ['kode_barang' => 'BRG-0010', 'nama_barang' => 'Kertas Karton Warna',       'satuan' => 'Lembar','stok' => 100],
            ['kode_barang' => 'BRG-0011', 'nama_barang' => 'Tipe-X Cair Kenko',         'satuan' => 'Pcs',   'stok' => 5],
            ['kode_barang' => 'BRG-0012', 'nama_barang' => 'Flashdisk SanDisk 32GB',    'satuan' => 'Unit',  'stok' => 0],
            ['kode_barang' => 'BRG-0013', 'nama_barang' => 'Ballpoint Pilot G2',        'satuan' => 'Lusin', 'stok' => 12],
            ['kode_barang' => 'BRG-0014', 'nama_barang' => 'Penggaris Besi 30cm',       'satuan' => 'Pcs',   'stok' => 20],
            ['kode_barang' => 'BRG-0015', 'nama_barang' => 'Tissue Kotak 200 Lembar',   'satuan' => 'Pak',   'stok' => 8],
        ];

        foreach ($barangs as $b) {
            Barang::firstOrCreate(
                ['kode_barang' => $b['kode_barang']],
                $b
            );
        }

        $this->command->info('✅ Seeder selesai! Akun:');
        $this->command->info('   karyawan@bps.go.id  / password');
        $this->command->info('   admin@bps.go.id     / password');
        $this->command->info('   pbj@bps.go.id       / password');
    }
}
