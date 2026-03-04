<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Karyawan\DashboardController;
use App\Http\Controllers\Karyawan\PemakaianController;
use App\Http\Controllers\Karyawan\PengadaanController;
use Illuminate\Support\Facades\Route;

// ── REDIRECT ROOT ──
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('login');
    }
    return redirect()->route('login');
});

// ── AUTH: Login (tanpa middleware guest agar tidak loop) ──
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── KARYAWAN PORTAL ──
Route::middleware(['auth'])
    ->prefix('karyawan')
    ->name('karyawan.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Permintaan Pemakaian
        Route::get('/pemakaian',        [PemakaianController::class, 'index'])->name('pemakaian.index');
        Route::get('/pemakaian/create', [PemakaianController::class, 'create'])->name('pemakaian.create');
        Route::post('/pemakaian',       [PemakaianController::class, 'store'])->name('pemakaian.store');

        // Permintaan Pengadaan
        Route::get('/pengadaan',        [PengadaanController::class, 'index'])->name('pengadaan.index');
        Route::get('/pengadaan/create', [PengadaanController::class, 'create'])->name('pengadaan.create');
        Route::post('/pengadaan',       [PengadaanController::class, 'store'])->name('pengadaan.store');
    });
