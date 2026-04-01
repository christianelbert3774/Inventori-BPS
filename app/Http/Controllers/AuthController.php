<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

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

            $user = Auth::user();

            if ($user->is_active != 1) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tidak dapat melakukan login, akun anda telah dinonaktifkan.'
                ]);
            }

            $request->session()->regenerate();
            return $this->redirectByRole($user);
        }

        return back()
            ->withErrors(['email' => 'Email atau password yang Anda masukkan salah.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Redirect berdasarkan role user.
     */
    private function redirectByRole($user)
    {
        return match ($user->role ?? '') {
            'divisi_umum'       => redirect()->route('admin.dashboard'),
            'pejabat_pengadaan' => redirect()->route('pbj.dashboard'),
            'karyawan'          => redirect()->route('karyawan.dashboard'),
            default             => redirect()->route('login'),
        };
    }
}
