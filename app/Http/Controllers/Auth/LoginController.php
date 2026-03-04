<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user && !$user->is_active) {
            return back()->withInput($request->only('email'))
                         ->withErrors(['email' => 'Akun Anda tidak aktif. Hubungi administrator.']);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return $this->redirectByRole(Auth::user());
        }

        return back()->withInput($request->only('email'))
                     ->withErrors(['email' => 'Email atau password salah.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('status', 'Anda berhasil keluar dari sistem.');
    }

    private function redirectByRole(User $user)
    {
        return match ($user->role) {
            User::ROLE_KARYAWAN  => redirect()->route('karyawan.dashboard'),
            User::ROLE_ADMIN     => redirect()->route('admin.dashboard'),
            User::ROLE_PENGADAAN => redirect()->route('pengadaan.dashboard'),
            default              => redirect()->route('login'),
        };
    }
}
