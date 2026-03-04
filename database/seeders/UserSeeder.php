<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'      => 'Budi Santoso',
                'email'     => 'karyawan@bps.go.id',
                'password'  => Hash::make('password'),
                'role'      => User::ROLE_KARYAWAN,
                'nip'       => '199001012020011001',
                'bagian'    => 'Seksi Distribusi',
                'jabatan'   => 'Staf Statistisi',
                'is_active' => true,
            ],
            [
                'name'      => 'Siti Rahayu',
                'email'     => 'siti@bps.go.id',
                'password'  => Hash::make('password'),
                'role'      => User::ROLE_KARYAWAN,
                'nip'       => '199505152021012002',
                'bagian'    => 'Seksi Neraca',
                'jabatan'   => 'Pranata Komputer',
                'is_active' => true,
            ],
            [
                'name'      => 'Ahmad Fauzi',
                'email'     => 'admin@bps.go.id',
                'password'  => Hash::make('password'),
                'role'      => User::ROLE_ADMIN,
                'nip'       => '198803202015031003',
                'bagian'    => 'Divisi Umum',
                'jabatan'   => 'Pengelola Barang',
                'is_active' => true,
            ],
            [
                'name'      => 'Dr. Hendra Wijaya',
                'email'     => 'pengadaan@bps.go.id',
                'password'  => Hash::make('password'),
                'role'      => User::ROLE_PENGADAAN,
                'nip'       => '197702102010011004',
                'bagian'    => 'Divisi Umum',
                'jabatan'   => 'Pejabat Pengadaan Barang',
                'is_active' => true,
            ],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(['email' => $u['email']], $u);
        }
    }
}
