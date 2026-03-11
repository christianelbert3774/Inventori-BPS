<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Pemakaian;
use App\Models\Pengadaan;
use Illuminate\Http\Request;

/**
 * DIMODIFIKASI — Admin\DashboardController.php
 * Perubahan: Tambah query barang dengan filter dan pagination
 * agar tabel stok di dashboard mendukung filter tersedia/hampir_habis/habis
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ── Statistik Barang ──
        $totalBarang       = Barang::count();
        $barangTersedia    = Barang::where('stok', '>', 10)->count();
        $barangHampirHabis = Barang::where('stok', '>', 0)->where('stok', '<=', 10)->count();
        $barangHabis       = Barang::where('stok', 0)->count();

        // ── Tabel Stok dengan filter ──
        $query = Barang::query()->orderBy('nama_barang');
        if ($request->filter === 'tersedia')     $query->where('stok', '>', 10);
        elseif ($request->filter === 'hampir_habis') $query->where('stok', '>', 0)->where('stok', '<=', 10);
        elseif ($request->filter === 'habis')    $query->where('stok', 0);
        $barangs = $query->paginate(8)->withQueryString();

        // ── Statistik Permintaan ──
        $pemakaianMenunggu = Pemakaian::where('status', 'pending')->count();
        $pengadaanMenunggu = Pengadaan::where('status_level2', 'pending')->count();

        // ── Aktivitas Terbaru ──
        $pemakaianTerbaru = Pemakaian::with(['user', 'details.barang'])->latest()->limit(5)->get()
            ->map(fn($item) => [
                'jenis'        => 'Pemakaian',
                'pemohon'      => $item->user->name ?? '-',
                'barang'       => $item->details->map(fn($d) => $d->barang->nama_barang ?? '-')->implode(', '),
                'status'       => $item->status,
                'tanggal'      => $item->created_at,
                'id'           => $item->id,
                'route_detail' => 'admin.pemakaian.show',
            ]);

        $pengadaanTerbaru = Pengadaan::with(['user', 'details.barang'])->latest()->limit(5)->get()
            ->map(fn($item) => [
                'jenis'        => 'Pengadaan',
                'pemohon'      => $item->user->name ?? '-',
                'barang'       => $item->details->map(fn($d) => $d->barang->nama_barang ?? '-')->implode(', '),
                'status'       => $item->status_level2,
                'tanggal'      => $item->created_at,
                'id'           => $item->id,
                'route_detail' => 'admin.pengadaan.show',
            ]);

        $aktivitasTerbaru = $pemakaianTerbaru->concat($pengadaanTerbaru)
            ->sortByDesc('tanggal')->take(8)->values();

        return view('admin.dashboard', compact(
            'totalBarang', 'barangTersedia', 'barangHampirHabis', 'barangHabis',
            'barangs', 'pemakaianMenunggu', 'pengadaanMenunggu', 'aktivitasTerbaru'
        ));
    }
}
