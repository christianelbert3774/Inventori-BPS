<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\ItemPermintaan;
use App\Models\PermintaanPemakaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermintaanController extends Controller
{
    public function create(Request $request)
    {
        $daftarBarang     = Barang::aktif()->tersedia()->with('kategori')->orderBy('nama')->get();
        $selectedBarangId = $request->get('barang_id');
        return view('karyawan.form-permintaan', compact('daftarBarang', 'selectedBarangId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bagian'             => ['required', 'string', 'max:100'],
            'keperluan'          => ['required', 'string', 'max:255'],
            'catatan'            => ['nullable', 'string', 'max:500'],
            'items'              => ['required', 'array', 'min:1', 'max:10'],
            'items.*.barang_id'  => ['required', 'exists:barang,id'],
            'items.*.jumlah'     => ['required', 'integer', 'min:1'],
            'items.*.keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required'             => 'Minimal harus ada 1 barang yang diminta.',
            'items.*.barang_id.required' => 'Pilih barang terlebih dahulu.',
            'items.*.barang_id.exists'   => 'Barang yang dipilih tidak valid.',
            'items.*.jumlah.required'    => 'Jumlah barang wajib diisi.',
            'items.*.jumlah.min'         => 'Jumlah minimal adalah 1.',
        ]);

        foreach ($request->items as $index => $item) {
            $barang = Barang::findOrFail($item['barang_id']);
            if (!$barang->isStokCukup((int) $item['jumlah'])) {
                return back()->withInput()->withErrors([
                    "items.{$index}.jumlah" =>
                        "Stok {$barang->nama} tidak mencukupi. Tersedia: {$barang->stok} {$barang->satuan}."
                ]);
            }
        }

        $barangIds = collect($request->items)->pluck('barang_id');
        if ($barangIds->count() !== $barangIds->unique()->count()) {
            return back()->withInput()
                         ->withErrors(['items' => 'Tidak boleh memilih barang yang sama lebih dari satu kali.']);
        }

        DB::transaction(function () use ($request) {
            $permintaan = PermintaanPemakaian::create([
                'user_id'   => Auth::id(),
                'bagian'    => $request->bagian,
                'keperluan' => $request->keperluan,
                'catatan'   => $request->catatan,
            ]);

            foreach ($request->items as $item) {
                ItemPermintaan::create([
                    'permintaan_id' => $permintaan->id,
                    'barang_id'     => $item['barang_id'],
                    'jumlah'        => (int) $item['jumlah'],
                    'keterangan'    => $item['keterangan'] ?? null,
                ]);
            }
        });

        return redirect()->route('karyawan.riwayat')
            ->with('success', 'Permintaan pemakaian berhasil dikirim dan sedang menunggu persetujuan.');
    }

    public function show(int $id)
    {
        $permintaan = PermintaanPemakaian::byUser(Auth::id())
            ->with(['items.barang.kategori', 'disetujuiOleh'])
            ->findOrFail($id);
        return view('karyawan.permintaan-detail', compact('permintaan'));
    }

    public function destroy(int $id)
    {
        $permintaan = PermintaanPemakaian::byUser(Auth::id())->findOrFail($id);

        if (!$permintaan->isMenunggu()) {
            return back()->withErrors(['error' => 'Permintaan yang sudah diproses tidak dapat dibatalkan.']);
        }

        DB::transaction(function () use ($permintaan) {
            $permintaan->items()->delete();
            $permintaan->delete();
        });

        return redirect()->route('karyawan.riwayat')->with('success', 'Permintaan berhasil dibatalkan.');
    }
}
