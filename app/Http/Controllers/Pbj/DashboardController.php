<?php

namespace App\Http\Controllers\Pbj;

use App\Http\Controllers\Controller;
use App\Models\Pengadaan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Hanya pengadaan yang sudah disetujui Divisi Umum
        $base = Pengadaan::where('status_level2', 'approved');

        $totalMasuk     = (clone $base)->count();
        $pending        = (clone $base)->where('status_level3', 'pending')->count();
        $selesai        = (clone $base)->where('status_level3', 'completed')->count();
        $ditolak        = (clone $base)->where('status_level3', 'rejected')->count();

        // Pengadaan terbaru
        $terbaru = Pengadaan::with(['user', 'details.barang'])
            ->where('status_level2', 'approved')
            ->latest()
            ->limit(8)
            ->get();

        return view('pbj.dashboard', compact(
            'totalMasuk', 'pending', 'selesai', 'ditolak', 'terbaru'
        ));
    }
}
