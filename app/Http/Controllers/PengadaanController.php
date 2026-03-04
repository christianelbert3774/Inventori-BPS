<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pengadaan;
use App\Models\PengadaanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengadaanController extends Controller
{
    /**
     * Tampilkan form permintaan pengadaan
     */
    public function create()
    {
        // Semua barang (untuk opsi restock, termasuk yang habis)
        $barangs = Barang::orderBy('nama_barang')->get();

        return view('karyawan.form-pengadaan', compact('barangs'));
    }

    /**
     * Simpan permintaan pengadaan
     * Dua mode:
     *   - restock : barang sudah ada, tambah stok (pakai barang_id existing)
     *   - baru    : barang belum ada, buat record barang baru
     */
    public function store(Request $request)
    {
        $tipe = $request->input('tipe_pengadaan', 'restock');

        if ($tipe === 'restock') {
            $request->validate([
                'restock_barang_id'   => ['required', 'array', 'min:1'],
                'restock_barang_id.*' => ['required', 'exists:barang,id'],
                'restock_jumlah'      => ['required', 'array', 'min:1'],
                'restock_jumlah.*'    => ['required', 'integer', 'min:1'],
                'restock_alasan'      => ['required', 'array', 'min:1'],
                'restock_alasan.*'    => ['required', 'string', 'max:1000'],
            ], [
                'restock_barang_id.required'   => 'Pilih minimal 1 barang untuk direstock.',
                'restock_barang_id.*.exists'   => 'Barang tidak ditemukan.',
                'restock_jumlah.*.required'    => 'Jumlah wajib diisi.',
                'restock_jumlah.*.min'         => 'Jumlah minimal 1.',
                'restock_alasan.*.required'    => 'Alasan pengadaan wajib diisi.',
            ]);

            DB::transaction(function () use ($request) {
                $pengadaan = Pengadaan::create([
                    'user_id'       => Auth::id(),
                    'status_level2' => 'pending',
                    'status_level3' => 'pending',
                ]);

                foreach ($request->restock_barang_id as $i => $barangId) {
                    PengadaanDetail::create([
                        'pengadaan_id' => $pengadaan->id,
                        'barang_id'    => $barangId,
                        'jumlah'       => $request->restock_jumlah[$i],
                    ]);
                }
            });

        } else {
            // Barang baru
            $request->validate([
                'baru_nama'    => ['required', 'array', 'min:1'],
                'baru_nama.*'  => ['required', 'string', 'max:100'],
                'baru_satuan'  => ['required', 'array', 'min:1'],
                'baru_satuan.*'=> ['required', 'string'],
                'baru_jumlah'  => ['required', 'array', 'min:1'],
                'baru_jumlah.*'=> ['required', 'integer', 'min:1'],
                'baru_alasan'  => ['required', 'array', 'min:1'],
                'baru_alasan.*'=> ['required', 'string', 'max:1000'],
            ], [
                'baru_nama.*.required'   => 'Nama barang wajib diisi.',
                'baru_satuan.*.required' => 'Satuan barang wajib dipilih.',
                'baru_jumlah.*.required' => 'Jumlah wajib diisi.',
                'baru_jumlah.*.min'      => 'Jumlah minimal 1.',
                'baru_alasan.*.required' => 'Alasan pengadaan wajib diisi.',
            ]);

            DB::transaction(function () use ($request) {
                $pengadaan = Pengadaan::create([
                    'user_id'       => Auth::id(),
                    'status_level2' => 'pending',
                    'status_level3' => 'pending',
                ]);

                foreach ($request->baru_nama as $i => $namaBaru) {
                    // Buat record barang baru dengan stok 0 (akan diisi setelah PBJ beli)
                    $barang = Barang::create([
                        'kode_barang' => Barang::generateKode(),
                        'nama_barang' => $namaBaru,
                        'satuan'      => $request->baru_satuan[$i],
                        'stok'        => 0,
                    ]);

                    PengadaanDetail::create([
                        'pengadaan_id' => $pengadaan->id,
                        'barang_id'    => $barang->id,
                        'jumlah'       => $request->baru_jumlah[$i],
                    ]);
                }
            });
        }

        return redirect()->route('karyawan.dashboard')
            ->with('success', 'Permintaan pengadaan berhasil diajukan! Divisi Umum akan menindaklanjuti.');
    }
}
