<?php

namespace App\Http\Controllers\Pbj;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Pengadaan;
use App\Models\PengadaanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * ┌──────────────────────────────────────────────────────────────┐
 * │  PBJ\PengadaanController.php                                 │
 * │  Controller Level 3 (PBJ) untuk menyelesaikan pengadaan.    │
 * │                                                              │
 * │  Alur baru:                                                  │
 * │   1. Karyawan submit form pengadaan → status pending         │
 * │   2. Divisi Umum approve → status_level2 = approved          │
 * │   3. PBJ input realisasi + upload foto bukti                 │
 * │      → status_level3 = completed                             │
 * │   4. Admin verifikasi → baru di sini stok ditambahkan        │
 * └──────────────────────────────────────────────────────────────┘
 */
class PengadaanController extends Controller
{
    /**
     * Daftar permintaan pengadaan yang sudah di-approve Divisi Umum.
     */
    public function index(Request $request)
    {
        $query = Pengadaan::with(['user', 'details.barang'])
            ->where('status_level2', 'approved')
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
     *   - Simpan jumlah realisasi per item
     *   - Upload foto bukti
     *   - Simpan catatan PBJ
     *   - Set status_level3 = 'completed'
     *
     * STOK BELUM DITAMBAHKAN di sini — Admin yang akan verifikasi dan menambah stok.
     */
    public function complete(Request $request, Pengadaan $pengadaan)
    {
        if ($pengadaan->status_level2 !== 'approved') {
            return back()->with('error', 'Pengadaan ini belum disetujui Divisi Umum.');
        }

        if ($pengadaan->status_level3 === 'completed') {
            return back()->with('error', 'Pengadaan ini sudah diselesaikan.');
        }

        // Validasi
        $rules = [
            'foto_bukti'  => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'catatan_pbj' => ['nullable', 'string', 'max:1000'],
        ];
        foreach ($pengadaan->details as $detail) {
            $rules["jumlah_realisasi.{$detail->id}"] = ['required', 'integer', 'min:0'];
        }
        $request->validate($rules, [
            'foto_bukti.required'         => 'Foto bukti pembelian wajib diunggah.',
            'foto_bukti.image'            => 'File harus berupa gambar.',
            'foto_bukti.mimes'            => 'Format gambar: JPG, PNG, atau WebP.',
            'foto_bukti.max'              => 'Ukuran file maksimal 5 MB.',
            'jumlah_realisasi.*.required' => 'Jumlah realisasi wajib diisi.',
            'jumlah_realisasi.*.min'      => 'Jumlah tidak boleh negatif.',
        ]);

        DB::transaction(function () use ($request, $pengadaan) {
            // Upload foto bukti
            $path = $request->file('foto_bukti')->store('bukti-pengadaan', 'public');

            // Simpan jumlah realisasi per detail
            foreach ($pengadaan->details as $detail) {
                $jumlahRealisasi = (int) $request->input("jumlah_realisasi.{$detail->id}", 0);
                $detail->update(['jumlah_realisasi' => $jumlahRealisasi]);
            }

            // Update pengadaan
            $pengadaan->update([
                'status_level3'       => 'completed',
                'processed_by_level3' => Auth::id(),
                'completed_at'        => now(),
                'foto_bukti'          => $path,
                'catatan_pbj'         => $request->input('catatan_pbj'),
            ]);
        });

        return redirect()->route('pbj.pengadaan.index')
            ->with('success', 'Pengadaan berhasil diselesaikan. Menunggu verifikasi dari Admin.');
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
