<?php

namespace App\Http\Controllers;

use App\Models\Barang;

class KaryawanDashboardController extends Controller
{
    public function index()
    {
        $barangs          = Barang::orderBy('nama_barang')->paginate(15);
        $totalBarang      = Barang::count();
        $barangTersedia   = Barang::where('stok', '>', Barang::STOK_HAMPIR_HABIS)->count();
        $barangHampirHabis = Barang::where('stok', '>', 0)
                                   ->where('stok', '<=', Barang::STOK_HAMPIR_HABIS)
                                   ->count();
        $barangHabis      = Barang::where('stok', 0)->count();

        return view('karyawan.dashboard', compact(
            'barangs',
            'totalBarang',
            'barangTersedia',
            'barangHampirHabis',
            'barangHabis'
        ));
    }
}
