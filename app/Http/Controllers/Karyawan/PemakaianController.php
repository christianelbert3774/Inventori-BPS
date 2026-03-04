<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PemakaianController extends Controller
{
    /**
     * Daftar riwayat permintaan pemakaian milik user yang login.
     */
    public function index()
    {
        $pemakaians = Pemakaian::with(['details.barang', 'approvedBy'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('karyawan.riwayat-pemakaian', compact('pemakaians'));
    }

    /**
     * Tampilkan form permintaan pemakaian.
     */
    public function create()
    {
        // Hanya tampilkan barang yang stoknya > 0
        $barangs = Barang::where('stok', '>', 0)
            ->orderBy('nama_barang')
            ->get();

        return view('karyawan.form-pemakaian', compact('barangs'));
    }

    /**
     * Simpan permintaan pemakaian baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'barang_id'   => ['required', 'array', 'min:1'],
            'barang_id.*' => ['required', 'exists:barang,id'],
            'jumlah'      => ['required', 'array', 'min:1'],
            'jumlah.*'    => ['required', 'integer', 'min:1'],
        ], [
            'barang_id.required'   => 'Pilih minimal satu barang.',
            'barang_id.*.required' => 'Setiap baris harus memilih barang.',
            'barang_id.*.exists'   => 'Barang yang dipilih tidak valid.',
            'jumlah.*.required'    => 'Jumlah wajib diisi.',
            'jumlah.*.min'         => 'Jumlah minimal adalah 1.',
        ]);

        // Validasi duplikat barang
        $ids = $request->barang_id;
        if (count($ids) !== count(array_unique($ids))) {
            return back()
                ->withErrors(['barang_id' => 'Terdapat barang yang sama dalam satu permintaan. Gabungkan menjadi satu baris.'])
                ->withInput();
        }

        // Validasi stok cukup
        foreach ($ids as $i => $barangId) {
            $barang = Barang::find($barangId);
            $jumlah = (int) $request->jumlah[$i];

            if ($barang->stok < $jumlah) {
                return back()
                    ->withErrors(['jumlah' => "Stok {$barang->nama_barang} tidak mencukupi. Stok tersedia: {$barang->stok} {$barang->satuan}."])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($request, $ids) {
            // Buat header pemakaian
            $pemakaian = Pemakaian::create([
                'user_id' => Auth::id(),
                'status'  => 'pending',
            ]);

            // Buat detail
            foreach ($ids as $i => $barangId) {
                PemakaianDetail::create([
                    'pemakaian_id' => $pemakaian->id,
                    'barang_id'    => $barangId,
                    'jumlah'       => (int) $request->jumlah[$i],
                ]);
            }
        });

        return redirect()->route('karyawan.pemakaian.index')
            ->with('success', 'Permintaan pemakaian berhasil dikirim! Admin gudang akan segera memprosesnya.');
    }
}
