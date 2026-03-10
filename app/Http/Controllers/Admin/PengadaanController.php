<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengadaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ┌─────────────────────────────────────────────────────────────┐
 * │  BARU — Admin\PengadaanController.php                       │
 * │  Controller Level 2 untuk mengelola permintaan pengadaan    │
 * │  dari karyawan. Memiliki fitur:                             │
 * │   - index()  : daftar semua permintaan pengadaan            │
 * │   - show()   : detail permintaan                            │
 * │   - approve(): menyetujui → status menjadi 'approved'       │
 * │                (diteruskan ke Level 3 / PBJ)                │
 * │   - reject() : menolak permintaan                           │
 * └─────────────────────────────────────────────────────────────┘
 */
class PengadaanController extends Controller
{
    /**
     * Daftar semua permintaan pengadaan dari karyawan.
     */
    public function index(Request $request)
    {
        $query = Pengadaan::with(['user', 'details.barang'])->latest();

        // Filter status level2
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status_level2', $request->status);
        }

        // Search nama pemohon
        if ($request->filled('q')) {
            $query->whereHas('user', fn($u) => $u->where('name', 'like', '%' . $request->q . '%'));
        }

        $pengadaans = $query->paginate(10)->withQueryString();

        return view('admin.permintaan-pengadaan', compact('pengadaans'));
    }

    /**
     * Detail permintaan pengadaan.
     */
    public function show(Pengadaan $pengadaan)
    {
        $pengadaan->load(['user', 'details.barang', 'approvedLevel2By']);
        return view('admin.detail-pengadaan', compact('pengadaan'));
    }

    /**
     * Setujui permintaan pengadaan → diteruskan ke Level 3 (PBJ).
     * Logika: status_level2 → 'approved', status_level3 tetap 'pending'
     * (PBJ yang akan menindaklanjuti).
     */
    public function approve(Request $request, Pengadaan $pengadaan)
    {
        if ($pengadaan->status_level2 !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $pengadaan->update([
            'status_level2'      => 'approved',
            'approved_level2_by' => Auth::id(),
        ]);

        return redirect()->route('admin.pengadaan.index')
            ->with('success', 'Permintaan pengadaan disetujui dan diteruskan ke PBJ.');
    }

    /**
     * Tolak permintaan pengadaan.
     */
    public function reject(Request $request, Pengadaan $pengadaan)
    {
        if ($pengadaan->status_level2 !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $pengadaan->update([
            'status_level2'      => 'rejected',
            'approved_level2_by' => Auth::id(),
        ]);

        return redirect()->route('admin.pengadaan.index')
            ->with('success', 'Permintaan pengadaan telah ditolak.');
    }
}
