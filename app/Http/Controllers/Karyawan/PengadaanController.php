<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Pengadaan;
use App\Models\PengadaanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengadaanController extends Controller
{
    /**
     * Daftar riwayat permintaan pengadaan milik user yang login.
     */
    public function index()
    {
        $pengadaans = Pengadaan::with(['details.barang'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('karyawan.riwayat-pengadaan', compact('pengadaans'));
    }

    /**
     * Tampilkan form permintaan pengadaan.
     */
    public function create()
    {
        // Semua barang untuk opsi restock
        $barangs = Barang::orderBy('nama_barang')->get();

        return view('karyawan.form-pengadaan', compact('barangs'));
    }

    /**
     * Simpan permintaan pengadaan baru.
     */
    public function store(Request $request)
    {
        $tipe = $request->input('tipe_pengadaan', 'restock');

        if ($tipe === 'restock') {
            // ── RESTOCK: tambah stok barang yang sudah ada ──
            $request->validate([
                'barang_id'      => ['required', 'exists:barang,id'],
                'jumlah_restock' => ['required', 'integer', 'min:1'],
                'alasan_restock' => ['required', 'string', 'max:1000'],
            ], [
                'barang_id.required'      => 'Pilih barang yang akan di-restock.',
                'barang_id.exists'        => 'Barang tidak valid.',
                'jumlah_restock.required' => 'Jumlah wajib diisi.',
                'jumlah_restock.min'      => 'Jumlah minimal 1.',
                'alasan_restock.required' => 'Alasan pengadaan wajib diisi.',
            ]);

            DB::transaction(function () use ($request) {
                $pengadaan = Pengadaan::create([
                    'user_id'       => Auth::id(),
                    'status_level2' => 'pending',
                    'status_level3' => 'pending',
                ]);

                PengadaanDetail::create([
                    'pengadaan_id' => $pengadaan->id,
                    'barang_id'    => $request->barang_id,
                    'jumlah'       => (int) $request->jumlah_restock,
                ]);
            });

            return redirect()->route('karyawan.pengadaan.index')
                ->with('success', 'Permintaan restock berhasil dikirim! Divisi Umum akan menindaklanjutinya.');

        } else {
            // ── BARANG BARU: pengadaan jenis barang yang belum ada ──
            $request->validate([
                'nama_barang_baru' => ['required', 'string', 'max:100'],
                'jumlah_baru'      => ['required', 'integer', 'min:1'],
                'satuan_baru'      => ['required', 'string', 'max:30'],
                'kategori_baru'    => ['required', 'string', 'max:100'],
                'alasan_baru'      => ['required', 'string', 'max:1000'],
            ], [
                'nama_barang_baru.required' => 'Nama barang wajib diisi.',
                'jumlah_baru.required'      => 'Jumlah wajib diisi.',
                'satuan_baru.required'      => 'Satuan wajib dipilih.',
                'kategori_baru.required'    => 'Kategori wajib dipilih.',
                'alasan_baru.required'      => 'Alasan pengadaan wajib diisi.',
            ]);

            DB::transaction(function () use ($request) {
                // Buat barang baru dengan stok 0 (akan diupdate setelah PBJ selesai belanja)
                $barang = Barang::create([
                    'kode_barang' => Barang::generateKode(),
                    'nama_barang' => $request->nama_barang_baru,
                    'satuan'      => $request->satuan_baru,
                    'stok'        => 0,
                ]);

                $pengadaan = Pengadaan::create([
                    'user_id'       => Auth::id(),
                    'status_level2' => 'pending',
                    'status_level3' => 'pending',
                ]);

                PengadaanDetail::create([
                    'pengadaan_id' => $pengadaan->id,
                    'barang_id'    => $barang->id,
                    'jumlah'       => (int) $request->jumlah_baru,
                ]);
            });

            return redirect()->route('karyawan.pengadaan.index')
                ->with('success', 'Permintaan pengadaan barang baru berhasil dikirim! Divisi Umum akan menindaklanjutinya.');
        }
    }
}
