<?php

namespace App\Http\Controllers\Pbj;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Pengadaan;
use App\Models\PengadaanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ┌──────────────────────────────────────────────────────────────┐
 * │  PBJ\PengadaanController.php                                 │
 * │  Controller Level 3 (PBJ) untuk menyelesaikan pengadaan.    │
 * │                                                              │
 * │  Alur yang benar:                                            │
 * │   1. Karyawan submit form pengadaan → status pending         │
 * │   2. Divisi Umum approve → status_level2 = approved          │
 * │   3. PBJ melihat daftar, lalu klik "Selesaikan"             │
 * │      → BARU DI SINI Barang dibuat / stok ditambah            │
 * └──────────────────────────────────────────────────────────────┘
 */
class PengadaanController extends Controller
{
    /**
     * Daftar permintaan pengadaan yang sudah di-approve Divisi Umum
     * dan menunggu ditindaklanjuti PBJ.
     */
    public function index(Request $request)
    {
        $query = Pengadaan::with(['user', 'details.barang'])
            ->where('status_level2', 'approved') // hanya yang sudah disetujui
            ->latest();

        if ($request->filled('status')) {
            $query->where('status_level3', $request->status);
        }

        $pengadaans = $query->paginate(10)->withQueryString();

        return view('pbj.pengadaan-index', compact('pengadaans'));
    }

    /**
     * Detail permintaan pengadaan untuk PBJ.
     */
    public function show(Pengadaan $pengadaan)
    {
        $pengadaan->load(['user', 'details.barang']);
        return view('pbj.pengadaan-show', compact('pengadaan'));
    }

    /**
     * Selesaikan pengadaan:
     *   - Untuk item 'restock': tambahkan stok barang yang sudah ada
     *   - Untuk item 'baru'   : buat record Barang baru di tabel barang, lalu set stoknya
     *
     * INI adalah satu-satunya tempat di mana Barang baru dibuat ke tabel barang.
     */
    public function complete(Request $request, Pengadaan $pengadaan)
    {
        if ($pengadaan->status_level2 !== 'approved') {
            return back()->with('error', 'Pengadaan ini belum disetujui Divisi Umum.');
        }

        if ($pengadaan->status_level3 === 'completed') {
            return back()->with('error', 'Pengadaan ini sudah diselesaikan.');
        }

        // Validasi jumlah realisasi untuk setiap detail
        $rules = [];
        foreach ($pengadaan->details as $detail) {
            $rules["jumlah_realisasi.{$detail->id}"] = ['required', 'integer', 'min:0'];
        }
        $request->validate($rules, [
            'jumlah_realisasi.*.required' => 'Jumlah realisasi wajib diisi.',
            'jumlah_realisasi.*.min'      => 'Jumlah tidak boleh negatif.',
        ]);

        DB::transaction(function () use ($request, $pengadaan) {
            foreach ($pengadaan->details as $detail) {
                $jumlahRealisasi = (int) $request->input("jumlah_realisasi.{$detail->id}", 0);

                if ($jumlahRealisasi <= 0) {
                    continue; // skip jika tidak ada yang dibeli
                }

                if ($detail->tipe_item === 'baru') {
                    // ── BARANG BARU: baru sekarang dibuat di tabel barang ──
                    $barang = Barang::create([
                        'kode_barang' => Barang::generateKode(),
                        'nama_barang' => $detail->nama_barang_baru,
                        'satuan'      => $detail->satuan_baru,
                        'stok'        => $jumlahRealisasi,
                    ]);

                    // Update detail: isi barang_id sekarang barang sudah ada
                    $detail->update(['barang_id' => $barang->id]);

                } else {
                    // ── RESTOCK: tambah stok barang yang sudah ada ──
                    $detail->barang->increment('stok', $jumlahRealisasi);
                }
            }

            // Update status pengadaan selesai
            $pengadaan->update([
                'status_level3'       => 'completed',
                'processed_by_level3' => Auth::id(),
                'completed_at'        => now(),
            ]);
        });

        return redirect()->route('pbj.pengadaan.index')
            ->with('success', 'Pengadaan berhasil diselesaikan. Stok barang telah diperbarui.');
    }

    /**
     * Tolak pengadaan (PBJ tidak bisa membelikan).
     */
    public function reject(Request $request, Pengadaan $pengadaan)
    {
        if ($pengadaan->status_level3 !== 'pending') {
            return back()->with('error', 'Status pengadaan tidak valid untuk ditolak.');
        }

        $pengadaan->update([
            'status_level3'       => 'rejected',
            'processed_by_level3' => Auth::id(),
        ]);

        return redirect()->route('pbj.pengadaan.index')
            ->with('success', 'Pengadaan telah ditolak.');
    }
}
