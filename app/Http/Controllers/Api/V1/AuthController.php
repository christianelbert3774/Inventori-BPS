<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login — hanya untuk karyawan.
     * Return Sanctum token + data user.
     */
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

        // Cek credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password yang Anda masukkan salah.'],
            ]);
        }

        // Cek akun aktif
        if ($user->is_active != 1) {
            throw ValidationException::withMessages([
                'email' => ['Tidak dapat melakukan login, akun anda telah dinonaktifkan.'],
            ]);
        }

        // Hanya karyawan yang boleh login via mobile
        if ($user->role !== 'karyawan') {
            throw ValidationException::withMessages([
                'email' => ['Aplikasi mobile hanya tersedia untuk akun karyawan.'],
            ]);
        }

        // Hapus token lama (optional: biar satu device saja)
        // $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'user'  => [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'nip'     => $user->nip,
                    'bagian'  => $user->bagian,
                    'jabatan' => $user->jabatan,
                    'no_telp' => $user->no_telp,
                    'role'    => $user->role,
                ],
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout — hapus token saat ini.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * User — data user yang sedang login.
     */
    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'nip'     => $user->nip,
                'bagian'  => $user->bagian,
                'jabatan' => $user->jabatan,
                'no_telp' => $user->no_telp,
                'role'    => $user->role,
            ],
        ]);
    }
}
