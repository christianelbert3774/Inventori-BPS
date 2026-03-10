<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Pemakaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ┌─────────────────────────────────────────────────────────────┐
 * │  BARU — Admin\PemakaianController.php                       │
 * │  Controller Level 2 untuk mengelola permintaan pemakaian    │
 * │  dari karyawan. Memiliki fitur:                             │
 * │   - index()  : daftar semua permintaan pemakaian            │
 * │   - show()   : detail permintaan beserta item barang        │
 * │   - approve(): menyetujui → stok barang otomatis berkurang  │
 * │   - reject() : menolak permintaan                           │
 * └─────────────────────────────────────────────────────────────┘
 */
class PemakaianController extends Controller
{
    /**
     * Daftar semua permintaan pemakaian dari karyawan.
     */
    public function index(Request $request)
    {
        $query = Pemakaian::with(['user', 'details.barang'])->latest();

        // Filter status
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // Search nama pemohon
        if ($request->filled('q')) {
            $query->whereHas('user', fn($u) => $u->where('name', 'like', '%' . $request->q . '%'));
        }

        $pemakaians = $query->paginate(10)->withQueryString();

        return view('admin.permintaan-pemakaian', compact('pemakaians'));
    }

    /**
     * Detail permintaan pemakaian.
     */
    public function show(Pemakaian $pemakaian)
    {
        $pemakaian->load(['user', 'details.barang', 'approvedBy']);
        return view('admin.detail-pemakaian', compact('pemakaian'));
    }

    /**
     * Setujui permintaan pemakaian.
     * Logika: status → approved, stok setiap barang dikurangi sesuai jumlah.
     */
    public function approve(Request $request, Pemakaian $pemakaian)
    {
        // Hanya bisa approve jika masih pending
        if ($pemakaian->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($request, $pemakaian) {
            $pemakaian->load('details.barang');

            // Validasi stok mencukupi untuk semua item sebelum proses
            foreach ($pemakaian->details as $detail) {
                $barang = $detail->barang;
                if (!$barang || $barang->stok < $detail->jumlah) {
                    throw new \Exception(
                        "Stok {$barang->nama_barang} tidak mencukupi. " .
                        "Tersedia: {$barang->stok}, Diminta: {$detail->jumlah}."
                    );
                }
            }

            // Kurangi stok setiap barang
            foreach ($pemakaian->details as $detail) {
                Barang::where('id', $detail->barang_id)
                    ->decrement('stok', $detail->jumlah);
            }

            // Update status permintaan
            $pemakaian->update([
                'status'      => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('admin.pemakaian.index')
            ->with('success', 'Permintaan pemakaian disetujui. Stok barang telah dikurangi.');
    }

    /**
     * Tolak permintaan pemakaian.
     */
    public function reject(Request $request, Pemakaian $pemakaian)
    {
        if ($pemakaian->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $pemakaian->update([
            'status'      => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.pemakaian.index')
            ->with('success', 'Permintaan pemakaian telah ditolak.');
    }
}
