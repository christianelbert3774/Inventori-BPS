<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Pemakaian;
use App\Models\Pengadaan;
use Illuminate\Http\Request;

/**
 * ┌─────────────────────────────────────────────────────────────┐
 * │  BARU — Admin\DashboardController.php                       │
 * │  Controller untuk Dashboard Level 2 (Divisi Umum /         │
 * │  Admin Gudang). Menampilkan statistik stok dan ringkasan    │
 * │  permintaan yang masuk dari Level 1.                        │
 * └─────────────────────────────────────────────────────────────┘
 */
class DashboardController extends Controller
{
    public function index()
    {
        // ── Statistik Barang ──
        $totalBarang        = Barang::count();
        $barangTersedia     = Barang::where('stok', '>', 10)->count();
        $barangHampirHabis  = Barang::where('stok', '>', 0)->where('stok', '<=', 10)->count();
        $barangHabis        = Barang::where('stok', 0)->count();

        // ── Statistik Permintaan ──
        $pemakaianMenunggu  = Pemakaian::where('status', 'pending')->count();
        $pengadaanMenunggu  = Pengadaan::where('status_level2', 'pending')->count();

        // ── Aktivitas Terbaru (gabungan pemakaian + pengadaan) ──
        // Ambil 5 pemakaian terbaru
        $pemakaianTerbaru = Pemakaian::with(['user', 'details.barang'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $namaBarang = $item->details->map(fn($d) => $d->barang->nama_barang ?? '-')->implode(', ');
                return [
                    'jenis'       => 'Pemakaian',
                    'pemohon'     => $item->user->name ?? '-',
                    'barang'      => $namaBarang ?: '-',
                    'status'      => $item->status,
                    'tanggal'     => $item->created_at,
                    'id'          => $item->id,
                    'route_detail'=> 'admin.pemakaian.show',
                ];
            });

        // Ambil 5 pengadaan terbaru
        $pengadaanTerbaru = Pengadaan::with(['user', 'details.barang'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $namaBarang = $item->details->map(fn($d) => $d->barang->nama_barang ?? '-')->implode(', ');
                return [
                    'jenis'       => 'Pengadaan',
                    'pemohon'     => $item->user->name ?? '-',
                    'barang'      => $namaBarang ?: '-',
                    'status'      => $item->status_level2,
                    'tanggal'     => $item->created_at,
                    'id'          => $item->id,
                    'route_detail'=> 'admin.pengadaan.show',
                ];
            });

        // Gabung dan urutkan berdasarkan tanggal terbaru
        $aktivitasTerbaru = $pemakaianTerbaru
            ->concat($pengadaanTerbaru)
            ->sortByDesc('tanggal')
            ->take(8)
            ->values();

        return view('admin.dashboard', compact(
            'totalBarang',
            'barangTersedia',
            'barangHampirHabis',
            'barangHabis',
            'pemakaianMenunggu',
            'pengadaanMenunggu',
            'aktivitasTerbaru'
        ));
    }
}
