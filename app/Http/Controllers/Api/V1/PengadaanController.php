<?php

namespace App\Http\Controllers\Api\V1;

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
     * Logic identik dengan Karyawan\PengadaanController@index web.
     */
    public function index(Request $request)
    {
        $pengadaans = Pengadaan::with(['details.barang'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate($request->input('per_page', 10));

        // Transform data agar lebih mudah dikonsumsi Android
        $pengadaans->getCollection()->transform(function ($pengadaan) {
            return [
                'id'              => $pengadaan->id,
                'status_level2'   => $pengadaan->status_level2,
                'status_level3'   => $pengadaan->status_level3,
                'completed_at'    => $pengadaan->completed_at?->toIso8601String(),
                'created_at'      => $pengadaan->created_at->toIso8601String(),
                'updated_at'      => $pengadaan->updated_at->toIso8601String(),
                'details'         => $pengadaan->details->map(function ($detail) {
                    return [
                        'id'              => $detail->id,
                        'tipe_item'       => $detail->tipe_item,
                        'barang_id'       => $detail->barang_id,
                        'nama_barang'     => $detail->barang->nama_barang ?? $detail->nama_barang_baru ?? 'Barang Baru',
                        'kode_barang'     => $detail->barang->kode_barang ?? '-',
                        'satuan'          => $detail->barang->satuan ?? $detail->satuan_baru ?? '-',
                        'jumlah'          => $detail->jumlah,
                        'jumlah_realisasi' => $detail->jumlah_realisasi,
                        'alasan'          => $detail->alasan,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $pengadaans,
        ]);
    }

    /**
     * Simpan permintaan pengadaan baru.
     * Logic identik dengan Karyawan\PengadaanController@store web.
     *
     * Expected JSON body untuk RESTOCK:
     * {
     *   "tipe_pengadaan": "restock",
     *   "barang_id": 1,
     *   "jumlah": 10,
     *   "alasan": "Stok hampir habis"
     * }
     *
     * Expected JSON body untuk BARANG BARU:
     * {
     *   "tipe_pengadaan": "baru",
     *   "nama_barang_baru": "Kertas A4",
     *   "jumlah": 50,
     *   "satuan_baru": "rim",
     *   "alasan": "Belum tersedia di gudang"
     * }
     */
    public function store(Request $request)
    {
        $tipe = $request->input('tipe_pengadaan', 'restock');

        if ($tipe === 'restock') {
            // ── RESTOCK: tambah stok barang yang sudah ada ──
            $request->validate([
                'barang_id' => ['required', 'exists:barang,id'],
                'jumlah'    => ['required', 'integer', 'min:1'],
                'alasan'    => ['required', 'string', 'max:1000'],
            ], [
                'barang_id.required' => 'Pilih barang yang akan di-restock.',
                'barang_id.exists'   => 'Barang tidak valid.',
                'jumlah.required'    => 'Jumlah wajib diisi.',
                'jumlah.min'         => 'Jumlah minimal 1.',
                'alasan.required'    => 'Alasan pengadaan wajib diisi.',
            ]);

            $pengadaan = DB::transaction(function () use ($request) {
                $pengadaan = Pengadaan::create([
                    'user_id'       => Auth::id(),
                    'status_level2' => 'pending',
                    'status_level3' => 'pending',
                ]);

                PengadaanDetail::create([
                    'pengadaan_id' => $pengadaan->id,
                    'tipe_item'    => 'restock',
                    'barang_id'    => $request->barang_id,
                    'jumlah'       => (int) $request->jumlah,
                    'alasan'       => $request->alasan,
                ]);

                return $pengadaan->load('details.barang');
            });

            return response()->json([
                'success' => true,
                'message' => 'Permintaan restock berhasil dikirim! Divisi Umum akan menindaklanjutinya.',
                'data'    => $this->formatPengadaan($pengadaan),
            ], 201);

        } else {
            // ── BARANG BARU: pengadaan jenis barang yang belum ada ──
            $request->validate([
                'nama_barang_baru' => ['required', 'string', 'max:100'],
                'jumlah'           => ['required', 'integer', 'min:1'],
                'satuan_baru'      => ['required', 'string', 'max:30'],
                'alasan'           => ['required', 'string', 'max:1000'],
            ], [
                'nama_barang_baru.required' => 'Nama barang wajib diisi.',
                'jumlah.required'           => 'Jumlah wajib diisi.',
                'satuan_baru.required'      => 'Satuan wajib dipilih.',
                'alasan.required'           => 'Alasan pengadaan wajib diisi.',
            ]);

            $pengadaan = DB::transaction(function () use ($request) {
                $pengadaan = Pengadaan::create([
                    'user_id'       => Auth::id(),
                    'status_level2' => 'pending',
                    'status_level3' => 'pending',
                ]);

                PengadaanDetail::create([
                    'pengadaan_id'     => $pengadaan->id,
                    'tipe_item'        => 'baru',
                    'barang_id'        => null,
                    'nama_barang_baru' => $request->nama_barang_baru,
                    'satuan_baru'      => $request->satuan_baru,
                    'jumlah'           => (int) $request->jumlah,
                    'alasan'           => $request->alasan,
                ]);

                return $pengadaan->load('details.barang');
            });

            return response()->json([
                'success' => true,
                'message' => 'Permintaan pengadaan barang baru berhasil dikirim! Divisi Umum akan menindaklanjutinya.',
                'data'    => $this->formatPengadaan($pengadaan),
            ], 201);
        }
    }

    /**
     * Format pengadaan untuk response JSON.
     */
    private function formatPengadaan(Pengadaan $pengadaan): array
    {
        return [
            'id'            => $pengadaan->id,
            'status_level2' => $pengadaan->status_level2,
            'status_level3' => $pengadaan->status_level3,
            'created_at'    => $pengadaan->created_at->toIso8601String(),
            'details'       => $pengadaan->details->map(function ($d) {
                return [
                    'tipe_item'       => $d->tipe_item,
                    'barang_id'       => $d->barang_id,
                    'nama_barang'     => $d->barang->nama_barang ?? $d->nama_barang_baru ?? 'Barang Baru',
                    'satuan'          => $d->barang->satuan ?? $d->satuan_baru ?? '-',
                    'jumlah'          => $d->jumlah,
                    'alasan'          => $d->alasan,
                ];
            }),
        ];
    }
}
