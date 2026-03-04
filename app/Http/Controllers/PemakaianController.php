<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PemakaianController extends Controller
{
    /**
     * Riwayat permintaan pemakaian milik user
     */
    public function index()
    {
        $pemakaians = Pemakaian::with(['details.barang', 'approvedBy'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('karyawan.riwayat', compact('pemakaians'));
    }

    /**
     * Tampilkan form permintaan pemakaian
     */
    public function create()
    {
        // Hanya tampilkan barang yang masih ada stok
        $barangs = Barang::where('stok', '>', 0)
                         ->orderBy('nama_barang')
                         ->get();

        return view('karyawan.form-pemakaian', compact('barangs'));
    }

    /**
     * Simpan permintaan pemakaian ke DB
     */
    public function store(Request $request)
    {
        $request->validate([
            'barang_id'   => ['required', 'array', 'min:1'],
            'barang_id.*' => ['required', 'exists:barang,id'],
            'jumlah'      => ['required', 'array', 'min:1'],
            'jumlah.*'    => ['required', 'integer', 'min:1'],
        ], [
            'barang_id.required'   => 'Minimal pilih 1 barang.',
            'barang_id.*.required' => 'Barang harus dipilih.',
            'barang_id.*.exists'   => 'Barang tidak ditemukan.',
            'jumlah.*.required'    => 'Jumlah wajib diisi.',
            'jumlah.*.min'         => 'Jumlah minimal 1.',
        ]);

        // Validasi stok mencukupi
        foreach ($request->barang_id as $i => $barangId) {
            $barang = Barang::find($barangId);
            $jumlah = $request->jumlah[$i];

            if ($barang->stok < $jumlah) {
                return back()
                    ->withErrors(["Stok {$barang->nama_barang} tidak mencukupi. Stok tersedia: {$barang->stok} {$barang->satuan}."])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($request) {
            $pemakaian = Pemakaian::create([
                'user_id' => Auth::id(),
                'status'  => 'pending',
            ]);

            foreach ($request->barang_id as $i => $barangId) {
                PemakaianDetail::create([
                    'pemakaian_id' => $pemakaian->id,
                    'barang_id'    => $barangId,
                    'jumlah'       => $request->jumlah[$i],
                ]);
            }
        });

        return redirect()->route('karyawan.pemakaian.index')
            ->with('success', 'Permintaan pemakaian berhasil dikirim! Admin gudang akan segera memprosesnya.');
    }
}
