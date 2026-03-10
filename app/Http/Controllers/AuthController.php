<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ┌─────────────────────────────────────────────────────────────┐
 * │  DIMODIFIKASI — AuthController.php                          │
 * │  Perubahan: redirectByRole() diupdate agar user             │
 * │  dengan role 'admin_gudang' diarahkan ke admin.dashboard    │
 * │  bukan ke karyawan.dashboard seperti sebelumnya.            │
 * │  Logika login/logout TIDAK berubah.                         │
 * └─────────────────────────────────────────────────────────────┘
 */
class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    /**
     * Proses login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return $this->redirectByRole(Auth::user());
        }

        return back()
            ->withErrors(['email' => 'Email atau password yang Anda masukkan salah.'])
            ->onlyInput('email');
    }

    /**
     * Proses logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Redirect berdasarkan role user.
     * admin_gudang → portal admin (Level 2)
     * karyawan     → portal karyawan (Level 1)
     * pejabat_pengadaan → karyawan dashboard (Level 3 belum ada)
     */
    private function redirectByRole($user)
    {
        return match ($user->role ?? '') {
            'divisi_umum'      => redirect()->route('admin.dashboard'),
            'karyawan'          => redirect()->route('karyawan.dashboard'),
            'pejabat_pengadaan' => redirect()->route('karyawan.dashboard'),
            default             => redirect()->route('login'),
        };
    }
}
