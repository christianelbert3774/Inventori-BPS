<?php

namespace App\Http\Controllers\Api\V1;

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
     * Logic identik dengan Karyawan\PemakaianController@index web.
     */
    public function index(Request $request)
    {
        $pemakaians = Pemakaian::with(['details.barang', 'approvedBy'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate($request->input('per_page', 10));

        // Transform data agar lebih mudah dikonsumsi Android
        $pemakaians->getCollection()->transform(function ($pemakaian) {
            return [
                'id'          => $pemakaian->id,
                'status'      => $pemakaian->status,
                'approved_by' => $pemakaian->approvedBy ? [
                    'id'   => $pemakaian->approvedBy->id,
                    'name' => $pemakaian->approvedBy->name,
                ] : null,
                'approved_at' => $pemakaian->approved_at?->toIso8601String(),
                'created_at'  => $pemakaian->created_at->toIso8601String(),
                'updated_at'  => $pemakaian->updated_at->toIso8601String(),
                'details'     => $pemakaian->details->map(function ($detail) {
                    return [
                        'id'          => $detail->id,
                        'barang_id'   => $detail->barang_id,
                        'nama_barang' => $detail->barang->nama_barang ?? 'Barang',
                        'kode_barang' => $detail->barang->kode_barang ?? '-',
                        'satuan'      => $detail->barang->satuan ?? '-',
                        'jumlah'      => $detail->jumlah,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $pemakaians,
        ]);
    }

    /**
     * Simpan permintaan pemakaian baru.
     * Logic identik dengan Karyawan\PemakaianController@store web.
     *
     * Expected JSON body:
     * {
     *   "items": [
     *     { "barang_id": 1, "jumlah": 5 },
     *     { "barang_id": 3, "jumlah": 2 }
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'items'            => ['required', 'array', 'min:1'],
            'items.*.barang_id' => ['required', 'exists:barang,id'],
            'items.*.jumlah'    => ['required', 'integer', 'min:1'],
        ], [
            'items.required'            => 'Pilih minimal satu barang.',
            'items.*.barang_id.required' => 'Setiap baris harus memilih barang.',
            'items.*.barang_id.exists'   => 'Barang yang dipilih tidak valid.',
            'items.*.jumlah.required'    => 'Jumlah wajib diisi.',
            'items.*.jumlah.min'         => 'Jumlah minimal adalah 1.',
        ]);

        $items = $request->items;

        // Validasi duplikat barang
        $ids = collect($items)->pluck('barang_id')->toArray();
        if (count($ids) !== count(array_unique($ids))) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => [
                    'items' => ['Terdapat barang yang sama dalam satu permintaan. Gabungkan menjadi satu baris.'],
                ],
            ], 422);
        }

        // Validasi stok cukup
        foreach ($items as $item) {
            $barang = Barang::find($item['barang_id']);
            $jumlah = (int) $item['jumlah'];

            if ($barang->stok < $jumlah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors'  => [
                        'items' => ["Stok {$barang->nama_barang} tidak mencukupi. Stok tersedia: {$barang->stok} {$barang->satuan}."],
                    ],
                ], 422);
            }
        }

        $pemakaian = DB::transaction(function () use ($items) {
            // Buat header pemakaian
            $pemakaian = Pemakaian::create([
                'user_id' => Auth::id(),
                'status'  => 'pending',
            ]);

            // Buat detail
            foreach ($items as $item) {
                PemakaianDetail::create([
                    'pemakaian_id' => $pemakaian->id,
                    'barang_id'    => $item['barang_id'],
                    'jumlah'       => (int) $item['jumlah'],
                ]);
            }

            return $pemakaian->load('details.barang');
        });

        return response()->json([
            'success' => true,
            'message' => 'Permintaan pemakaian berhasil dikirim! Admin gudang akan segera memprosesnya.',
            'data'    => [
                'id'         => $pemakaian->id,
                'status'     => $pemakaian->status,
                'created_at' => $pemakaian->created_at->toIso8601String(),
                'details'    => $pemakaian->details->map(function ($d) {
                    return [
                        'barang_id'   => $d->barang_id,
                        'nama_barang' => $d->barang->nama_barang,
                        'jumlah'      => $d->jumlah,
                    ];
                }),
            ],
        ], 201);
    }

    /**
     * Daftar barang yang stok > 0 (untuk dropdown di form pemakaian).
     * Logic identik dengan Karyawan\PemakaianController@create web.
     */
    public function barangTersedia()
    {
        $barangs = Barang::where('stok', '>', 0)
            ->orderBy('nama_barang')
            ->get()
            ->map(function ($b) {
                return [
                    'id'          => $b->id,
                    'kode_barang' => $b->kode_barang,
                    'nama_barang' => $b->nama_barang,
                    'satuan'      => $b->satuan,
                    'stok'        => $b->stok,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $barangs,
        ]);
    }
}
