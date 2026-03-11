<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemakaian;
use App\Models\Pengadaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * BARU — Admin\KaryawanController.php
 * Fitur manajemen karyawan oleh Level 2 (Divisi Umum):
 *  - index()  : daftar semua karyawan + filter
 *  - history(): riwayat permintaan per karyawan (pemakaian + pengadaan)
 *  - create() : form tambah akun karyawan
 *  - store()  : simpan akun karyawan baru
 *  - destroy(): nonaktifkan akun karyawan
 */
class KaryawanController extends Controller
{
    /**
     * Daftar semua karyawan (role = karyawan).
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'karyawan')->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('bagian', 'like', "%$q%")
                    ->orWhere('nip', 'like', "%$q%");
            });
        }

        $karyawans = $query->paginate(10)->withQueryString();

        // Hitung statistik per karyawan
        $stats = [];
        foreach ($karyawans as $k) {
            $stats[$k->id] = [
                'pemakaian' => Pemakaian::where('user_id', $k->id)->count(),
                'pengadaan' => Pengadaan::where('user_id', $k->id)->count(),
                'pending'   => Pemakaian::where('user_id', $k->id)->where('status', 'pending')->count()
                             + Pengadaan::where('user_id', $k->id)->where('status_level2', 'pending')->count(),
            ];
        }

        return view('admin.karyawan', compact('karyawans', 'stats'));
    }

    /**
     * Riwayat permintaan per karyawan (filter by jenis + status).
     */
    public function history(Request $request, User $karyawan)
    {
        $jenis  = $request->input('jenis', 'semua');
        $status = $request->input('status', '');

        $pemakaians = collect();
        $pengadaans = collect();

        if ($jenis === 'semua' || $jenis === 'pemakaian') {
            $q = Pemakaian::with(['details.barang', 'approvedBy'])
                ->where('user_id', $karyawan->id)->latest();
            if ($status && in_array($status, ['pending','approved','rejected'])) {
                $q->where('status', $status);
            }
            $pemakaians = $q->get();
        }

        if ($jenis === 'semua' || $jenis === 'pengadaan') {
            $q = Pengadaan::with(['details.barang'])
                ->where('user_id', $karyawan->id)->latest();
            if ($status && in_array($status, ['pending','approved','rejected'])) {
                $q->where('status_level2', $status);
            }
            $pengadaans = $q->get();
        }

        return view('admin.karyawan-history', compact('karyawan', 'pemakaians', 'pengadaans', 'jenis', 'status'));
    }

    /**
     * Form tambah akun karyawan baru.
     */
    public function create()
    {
        return view('admin.karyawan-create');
    }

    /**
     * Simpan akun karyawan baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nip'      => ['nullable', 'string', 'max:30', 'unique:users,nip'],
            'bagian'   => ['nullable', 'string', 'max:100'],
            'jabatan'  => ['nullable', 'string', 'max:100'],
            'no_telp'  => ['nullable', 'string', 'max:20'],
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
            'nip.unique'        => 'NIP sudah terdaftar.',
        ]);

        User::create([
            'role'     => 'karyawan',
            'role_id'  => 1,
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'nip'      => $request->nip,
            'bagian'   => $request->bagian,
            'jabatan'  => $request->jabatan,
            'no_telp'  => $request->no_telp,
            'is_active'=> true,
        ]);

        return redirect()->route('admin.karyawan.index')
            ->with('success', "Akun karyawan {$request->name} berhasil dibuat.");
    }

    /**
     * Toggle aktif/nonaktif akun karyawan.
     */
    public function toggleActive(User $karyawan)
    {
        $karyawan->update(['is_active' => !$karyawan->is_active]);
        $status = $karyawan->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun {$karyawan->name} berhasil {$status}.");
    }
}
