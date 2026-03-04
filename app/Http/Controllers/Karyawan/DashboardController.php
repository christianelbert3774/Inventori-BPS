<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Filter stok
        $query = Barang::query()->orderBy('nama_barang');

        if ($request->filter === 'tersedia') {
            $query->where('stok', '>', 10);
        } elseif ($request->filter === 'hampir_habis') {
            $query->where('stok', '>', 0)->where('stok', '<=', 10);
        } elseif ($request->filter === 'habis') {
            $query->where('stok', 0);
        }

        // Search
        if ($request->filled('q')) {
            $query->where('nama_barang', 'like', '%' . $request->q . '%')
                  ->orWhere('kode_barang', 'like', '%' . $request->q . '%');
        }

        $barangs = $query->paginate(10)->withQueryString();

        // Statistik
        $totalBarang       = Barang::count();
        $barangTersedia    = Barang::where('stok', '>', 10)->count();
        $barangHampirHabis = Barang::where('stok', '>', 0)->where('stok', '<=', 10)->count();
        $barangHabis       = Barang::where('stok', 0)->count();

        return view('karyawan.dashboard', compact(
            'barangs',
            'totalBarang',
            'barangTersedia',
            'barangHampirHabis',
            'barangHabis'
        ));
    }
}
