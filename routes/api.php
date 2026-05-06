<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\NotifikasiController;
use App\Http\Controllers\Api\V1\PemakaianController;
use App\Http\Controllers\Api\V1\PengadaanController;
use App\Http\Controllers\Api\V1\PrintController;
use App\Http\Controllers\Api\V1\ProfilController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Inventori BPS Mobile (Karyawan Level)
|--------------------------------------------------------------------------
|
| Semua endpoint menggunakan prefix /api/v1/
| Autentikasi menggunakan Laravel Sanctum (token-based)
| Database yang digunakan SAMA dengan versi desktop
|
*/

// ── PUBLIC (tanpa token) ──
Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// ── PROTECTED (butuh token + role karyawan) ──
Route::prefix('v1')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // Dashboard — Statistik stok & daftar barang
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Barang — Daftar barang tersedia (untuk form pemakaian)
        Route::get('/barang', [PemakaianController::class, 'barangTersedia']);

        // Permintaan Pemakaian
        Route::get('/pemakaian', [PemakaianController::class, 'index']);
        Route::post('/pemakaian', [PemakaianController::class, 'store']);

        // Permintaan Pengadaan
        Route::get('/pengadaan', [PengadaanController::class, 'index']);
        Route::post('/pengadaan', [PengadaanController::class, 'store']);

        // Profil
        Route::get('/profil', [ProfilController::class, 'index']);
        Route::patch('/profil', [ProfilController::class, 'update']);
        Route::patch('/profil/password', [ProfilController::class, 'updatePassword']);

        // Notifikasi
        Route::get('/notifikasi', [NotifikasiController::class, 'index']);
        Route::get('/notifikasi/badge', [NotifikasiController::class, 'badgeCount']);

    });

// ── PRINT (autentikasi ditangani oleh PrintController via ?token=) ──
// Harus di luar auth:sanctum karena WebView tidak mengirim header Authorization
Route::prefix('v1')->group(function () {
    Route::get('/print/pemakaian/{pemakaian}', [PrintController::class, 'pemakaian']);
    Route::get('/print/pengadaan/{pengadaan}', [PrintController::class, 'pengadaan']);
    Route::get('/print/aktivitas', [PrintController::class, 'aktivitas']);
});
