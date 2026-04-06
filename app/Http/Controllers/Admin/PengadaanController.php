<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Pengadaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ┌─────────────────────────────────────────────────────────────┐
 * │  Admin\PengadaanController.php                               │
 * │  Controller Level 2 untuk mengelola permintaan pengadaan    │
 * │  dari karyawan. Fitur:                                      │
 * │   - index()  : daftar semua permintaan pengadaan            │
 * │   - show()   : detail permintaan                            │
 * │   - approve(): menyetujui → diteruskan ke PBJ              │
 * │   - reject() : menolak permintaan                           │
 * │   - verify() : verifikasi barang dari PBJ → stok masuk     │
 * │   - rejectVerification(): tolak verifikasi                  │
 * └─────────────────────────────────────────────────────────────┘
 */
class PengadaanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengadaan::with(['user', 'details.barang'])->latest();

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'pending':
                    $query->where('status_level2', 'pending');
                    break;
                case 'diproses_pbj':
                    $query->where('status_level2', 'approved')
                          ->where(function($q) {
                              $q->where('status_level3', 'pending')
                                ->orWhere('status_level3', 'processing');
                          });
                    break;
                case 'menunggu_verifikasi':
                    $query->where('status_level2', 'approved')
                          ->where('status_level3', 'completed')
                          ->where('status_admin_verifikasi', 'belum');
                    break;
                case 'approved':
                    $query->where('status_level2', 'approved');
                    break;
                case 'ditolak_pbj':
                    $query->where('status_level2', 'approved')
                          ->where('status_level3', 'rejected');
                    break;
                case 'rejected':
                    $query->where('status_level2', 'rejected');
                    break;
            }
        }

        if ($request->filled('q')) {
            $query->whereHas('user', fn($u) => $u->where('name', 'like', '%' . $request->q . '%'));
        }

        $pengadaans = $query->paginate(10)->withQueryString();

        return view('admin.permintaan-pengadaan', compact('pengadaans'));
    }

    public function show(Pengadaan $pengadaan)
    {
        $pengadaan->load(['user', 'details.barang', 'approvedLevel2By', 'processedByLevel3', 'verifiedBy']);
        return view('admin.detail-pengadaan', compact('pengadaan'));
    }

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

    /**
     * Verifikasi pengadaan yang sudah di-complete PBJ.
     * DI SINILAH stok barang benar-benar ditambahkan ke sistem.
     */
    public function verify(Request $request, Pengadaan $pengadaan)
    {
        if ($pengadaan->status_level3 !== 'completed') {
            return back()->with('error', 'Pengadaan belum diselesaikan oleh PBJ.');
        }

        if ($pengadaan->status_admin_verifikasi === 'verified') {
            return back()->with('error', 'Pengadaan ini sudah diverifikasi sebelumnya.');
        }

        DB::transaction(function () use ($pengadaan) {
            foreach ($pengadaan->details as $detail) {
                $jumlahRealisasi = (int) ($detail->jumlah_realisasi ?? 0);

                if ($jumlahRealisasi <= 0) {
                    continue;
                }

                if ($detail->tipe_item === 'baru') {
                    // Barang baru: buat record di tabel barang
                    $barang = Barang::create([
                        'kode_barang' => Barang::generateKode(),
                        'nama_barang' => $detail->nama_barang_baru,
                        'satuan'      => $detail->satuan_baru,
                        'stok'        => $jumlahRealisasi,
                    ]);
                    $detail->update(['barang_id' => $barang->id]);
                } else {
                    // Restock: tambah stok barang yang sudah ada
                    $detail->barang->increment('stok', $jumlahRealisasi);
                }
            }

            $pengadaan->update([
                'status_admin_verifikasi' => 'verified',
                'verified_by'             => Auth::id(),
                'verified_at'             => now(),
            ]);
        });

        return redirect()->route('admin.pengadaan.show', $pengadaan->id)
            ->with('success', 'Pengadaan telah diverifikasi. Stok barang berhasil diperbarui.');
    }

    /**
     * Tolak verifikasi pengadaan dari PBJ.
     */
    public function rejectVerification(Request $request, Pengadaan $pengadaan)
    {
        if ($pengadaan->status_level3 !== 'completed') {
            return back()->with('error', 'Pengadaan belum diselesaikan oleh PBJ.');
        }

        if ($pengadaan->status_admin_verifikasi !== 'belum') {
            return back()->with('error', 'Pengadaan ini sudah diverifikasi/ditolak.');
        }

        $pengadaan->update([
            'status_admin_verifikasi' => 'rejected',
            'verified_by'             => Auth::id(),
            'verified_at'             => now(),
        ]);

        return redirect()->route('admin.pengadaan.show', $pengadaan->id)
            ->with('success', 'Verifikasi pengadaan ditolak.');
    }
}
