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
     *
     * BUGFIX: Sebelumnya untuk mode 'baru', Barang::create() langsung dipanggil
     * di sini sehingga barang muncul di tabel barang sebelum diapprove.
     * Sekarang data barang baru disimpan sementara di kolom pengadaan_detail
     * (nama_barang_baru, satuan_baru) dan barang_id dibiarkan NULL.
     * Record Barang baru dibuat oleh PBJ saat mereka menyelesaikan pengadaan.
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
                    'tipe_item'    => 'restock',
                    'barang_id'    => $request->barang_id,
                    'jumlah'       => (int) $request->jumlah_restock,
                    'alasan'       => $request->alasan_restock,
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
                'alasan_baru'      => ['required', 'string', 'max:1000'],
            ], [
                'nama_barang_baru.required' => 'Nama barang wajib diisi.',
                'jumlah_baru.required'      => 'Jumlah wajib diisi.',
                'satuan_baru.required'      => 'Satuan wajib dipilih.',
                'alasan_baru.required'      => 'Alasan pengadaan wajib diisi.',
            ]);

            DB::transaction(function () use ($request) {
                $pengadaan = Pengadaan::create([
                    'user_id'       => Auth::id(),
                    'status_level2' => 'pending',
                    'status_level3' => 'pending',
                ]);

                // BUGFIX: Tidak lagi membuat Barang di sini.
                // Data barang baru disimpan sebagai kolom sementara di pengadaan_detail.
                // barang_id sengaja NULL — akan diisi oleh PBJ saat menyelesaikan pengadaan.
                PengadaanDetail::create([
                    'pengadaan_id'    => $pengadaan->id,
                    'tipe_item'       => 'baru',
                    'barang_id'       => null, // belum ada, akan dibuat saat PBJ selesai
                    'nama_barang_baru'=> $request->nama_barang_baru,
                    'satuan_baru'     => $request->satuan_baru,
                    'jumlah'          => (int) $request->jumlah_baru,
                    'alasan'          => $request->alasan_baru,
                ]);
            });

            return redirect()->route('karyawan.pengadaan.index')
                ->with('success', 'Permintaan pengadaan barang baru berhasil dikirim! Divisi Umum akan menindaklanjutinya.');
        }
    }
}
