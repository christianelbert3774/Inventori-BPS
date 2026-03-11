<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ┌─────────────────────────────────────────────────────────────┐
 * │  DIMODIFIKASI — RoleMiddleware.php                          │
 * │  Middleware baru yang menggantikan CheckRole & EnsureRole.  │
 * │  Digunakan untuk memproteksi route berdasarkan kolom        │
 * │  'role' (string) di tabel users.                           │
 * │  Contoh pemakaian di route:                                 │
 * │    ->middleware('role:admin_gudang')                        │
 * │    ->middleware('role:karyawan,admin_gudang')               │
 * └─────────────────────────────────────────────────────────────┘
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->role ?? '';

        if (!in_array($userRole, $roles)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}
