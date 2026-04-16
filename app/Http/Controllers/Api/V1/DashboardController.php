<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard — statistik stok + daftar barang (dengan filter & search).
     * Logic identik dengan Karyawan\DashboardController web.
     */
    public function index(Request $request)
    {
        // ── Query barang dengan filter ──
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
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_barang', 'like', '%' . $q . '%')
                    ->orWhere('kode_barang', 'like', '%' . $q . '%');
            });
        }

        $barangs = $query->paginate($request->input('per_page', 10));

        // ── Statistik ──
        $totalBarang       = Barang::count();
        $barangTersedia    = Barang::where('stok', '>', 10)->count();
        $barangHampirHabis = Barang::where('stok', '>', 0)->where('stok', '<=', 10)->count();
        $barangHabis       = Barang::where('stok', 0)->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'statistik' => [
                    'total_barang'        => $totalBarang,
                    'barang_tersedia'     => $barangTersedia,
                    'barang_hampir_habis' => $barangHampirHabis,
                    'barang_habis'        => $barangHabis,
                ],
                'barangs' => $barangs,
            ],
        ]);
    }
}
