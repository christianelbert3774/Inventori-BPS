<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/**
 * ┌─────────────────────────────────────────────────────────────┐
 * │  DIMODIFIKASI — bootstrap/app.php                           │
 * │  Perubahan:                                                 │
 * │   1. Ganti alias middleware 'role' dari CheckRole           │
 * │      ke RoleMiddleware (yang baru, berbasis kolom string)   │
 * │   2. redirectUsersTo diupdate: jika sudah login,            │
 * │      redirect berdasarkan role (admin → admin.dashboard)    │
 * │   3. Tidak ada perubahan lain pada konfigurasi aplikasi     │
 * └─────────────────────────────────────────────────────────────┘
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Daftarkan RoleMiddleware sebagai alias 'role'
        // Digunakan di routes: ->middleware('role:admin_gudang')
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        // Redirect jika belum login
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Redirect jika sudah login mencoba buka /login lagi
        // → arahkan ke halaman yang sesuai role masing-masing
        $middleware->redirectUsersTo(function () {
            if (auth()->check()) {
                $role = auth()->user()->role ?? '';
                if ($role === 'divisi_umum') {
                    return route('admin.dashboard');
                }
            }
            return route('karyawan.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
