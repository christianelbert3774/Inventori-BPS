<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Pemakaian;
use App\Models\Pengadaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    /**
     * Data profil user + statistik + recent activity.
     * Logic identik dengan Karyawan\ProfilController@index web.
     */
    public function index()
    {
        $user = Auth::user();

        $totalPemakaian   = Pemakaian::where('user_id', $user->id)->count();
        $totalPengadaan   = Pengadaan::where('user_id', $user->id)->count();
        $pendingPemakaian = Pemakaian::where('user_id', $user->id)->where('status', 'pending')->count();
        $pendingPengadaan = Pengadaan::where('user_id', $user->id)->where('status_level2', 'pending')->count();

        // Recent pemakaian (3 terbaru)
        $recentPemakaian = Pemakaian::with('details.barang')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($p) {
                return [
                    'id'         => $p->id,
                    'status'     => $p->status,
                    'created_at' => $p->created_at->toIso8601String(),
                    'items'      => $p->details->map(function ($d) {
                        return [
                            'nama_barang' => $d->barang->nama_barang ?? 'Barang',
                            'jumlah'      => $d->jumlah,
                            'satuan'      => $d->barang->satuan ?? '-',
                        ];
                    }),
                ];
            });

        // Recent pengadaan (3 terbaru)
        $recentPengadaan = Pengadaan::with('details.barang')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($p) {
                return [
                    'id'            => $p->id,
                    'status_level2' => $p->status_level2,
                    'status_level3' => $p->status_level3,
                    'created_at'    => $p->created_at->toIso8601String(),
                    'items'         => $p->details->map(function ($d) {
                        return [
                            'tipe_item'   => $d->tipe_item,
                            'nama_barang' => $d->barang->nama_barang ?? $d->nama_barang_baru ?? 'Barang Baru',
                            'jumlah'      => $d->jumlah,
                            'satuan'      => $d->barang->satuan ?? $d->satuan_baru ?? '-',
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'nip'     => $user->nip,
                    'bagian'  => $user->bagian,
                    'jabatan' => $user->jabatan,
                    'no_telp' => $user->no_telp,
                    'role'    => $user->role,
                ],
                'statistik' => [
                    'total_pemakaian'   => $totalPemakaian,
                    'total_pengadaan'   => $totalPengadaan,
                    'pending_pemakaian' => $pendingPemakaian,
                    'pending_pengadaan' => $pendingPengadaan,
                ],
                'recent_pemakaian' => $recentPemakaian,
                'recent_pengadaan' => $recentPengadaan,
            ],
        ]);
    }

    /**
     * Update profil user.
     * Logic identik dengan Karyawan\ProfilController@updateProfil web.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
            'nip'     => ['nullable', 'string', 'max:30', Rule::unique('users')->ignore($user->id)],
            'bagian'  => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'no_telp' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique'   => 'Email sudah digunakan akun lain.',
            'nip.unique'     => 'NIP sudah digunakan akun lain.',
        ]);

        $user->update([
            'name'    => $request->name,
            'email'   => $request->email,
            'nip'     => $request->nip,
            'bagian'  => $request->bagian,
            'jabatan' => $request->jabatan,
            'no_telp' => $request->no_telp,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data'    => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'nip'     => $user->nip,
                'bagian'  => $user->bagian,
                'jabatan' => $user->jabatan,
                'no_telp' => $user->no_telp,
            ],
        ]);
    }

    /**
     * Ganti password.
     * Logic identik dengan Karyawan\ProfilController@updatePassword web.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required'         => 'Password baru wajib diisi.',
            'password.min'              => 'Password minimal 8 karakter.',
            'password.confirmed'        => 'Konfirmasi password tidak cocok.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => [
                    'current_password' => ['Password lama tidak sesuai.'],
                ],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui.',
        ]);
    }
}
