<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Pemakaian;
use App\Models\Pengadaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $totalPemakaian   = Pemakaian::where('user_id', $user->id)->count();
        $totalPengadaan   = Pengadaan::where('user_id', $user->id)->count();
        $pendingPemakaian = Pemakaian::where('user_id', $user->id)->where('status', 'pending')->count();
        $pendingPengadaan = Pengadaan::where('user_id', $user->id)->where('status_level2', 'pending')->count();

        $recentPemakaian = Pemakaian::with('details.barang')
            ->where('user_id', $user->id)->latest()->limit(3)->get();

        $recentPengadaan = Pengadaan::with('details.barang')
            ->where('user_id', $user->id)->latest()->limit(3)->get();

        return view('karyawan.profil', compact(
            'user', 'totalPemakaian', 'totalPengadaan',
            'pendingPemakaian', 'pendingPengadaan',
            'recentPemakaian', 'recentPengadaan'
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

    /**
     * Halaman print aktivitas bulanan (tampil langsung di browser, user tinggal Ctrl+P).
     */
    public function printAktivitas(Request $request)
    {
        $user  = Auth::user();
        $bulan = $request->input('bulan', now()->format('Y-m'));  // default bulan ini

        // Parse bulan
        $start = \Carbon\Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $pemakaians = Pemakaian::with(['details.barang', 'approvedBy'])
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->get();

        $pengadaans = Pengadaan::with(['details.barang'])
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->get();

        $bulanLabel = $start->locale('id')->isoFormat('MMMM Y');

        return view('karyawan.print-aktivitas', compact(
            'user', 'pemakaians', 'pengadaans', 'bulanLabel', 'bulan'
        ));
    }
}
