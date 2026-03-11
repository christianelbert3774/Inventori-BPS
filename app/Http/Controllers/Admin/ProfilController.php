<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemakaian;
use App\Models\Pengadaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * BARU — Admin\ProfilController.php
 * Profil untuk Level 2 (Divisi Umum). Fitur sama dengan Level 1:
 *  - Lihat & edit informasi pribadi
 *  - Ubah password
 *  - Aktivitas terbaru (permintaan yang diproses oleh admin ini)
 */
class ProfilController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Statistik: berapa permintaan yang sudah diproses oleh admin ini
        $totalApproved = Pemakaian::where('approved_by', $user->id)
            ->whereIn('status', ['approved', 'rejected'])->count();

        $totalForwarded = Pengadaan::where('approved_level2_by', $user->id)
            ->where('status_level2', '!=', 'pending')->count();

        $pendingPemakaian = Pemakaian::where('status', 'pending')->count();
        $pendingPengadaan = Pengadaan::where('status_level2', 'pending')->count();

        // Aktivitas terbaru yang diproses admin ini
        $recentPemakaian = Pemakaian::with(['user', 'details.barang'])
            ->where('approved_by', $user->id)
            ->latest('updated_at')->limit(3)->get();

        $recentPengadaan = Pengadaan::with(['user', 'details.barang'])
            ->where('approved_level2_by', $user->id)
            ->latest('updated_at')->limit(3)->get();

        return view('admin.profil', compact(
            'user',
            'totalApproved',
            'totalForwarded',
            'pendingPemakaian',
            'pendingPengadaan',
            'recentPemakaian',
            'recentPengadaan'
        ));
    }

    public function updateProfil(Request $request)
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

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

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
            return back()
                ->withErrors(['current_password' => 'Password lama tidak sesuai.'])
                ->with('tab', 'password');
        }

        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password berhasil diperbarui.')->with('tab', 'password');
    }
}
