<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Karyawan\DashboardController;
use App\Http\Controllers\Karyawan\NotifikasiController;
use App\Http\Controllers\Karyawan\PemakaianController;
use App\Http\Controllers\Karyawan\PengadaanController;
use App\Http\Controllers\Karyawan\ProfilController;

// ── Controller Admin (Level 2) — BARU ──
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\KaryawanController as AdminKaryawanController;
use App\Http\Controllers\Admin\NotifikasiController as AdminNotifikasiController;
use App\Http\Controllers\Admin\PemakaianController as AdminPemakaianController;
use App\Http\Controllers\Admin\PengadaanController as AdminPengadaanController;
use App\Http\Controllers\Admin\ProfilController as AdminProfilController;

// ── Controller PBJ (Level 3) ──
use App\Http\Controllers\Pbj\DashboardController as PbjDashboardController;
use App\Http\Controllers\Pbj\NotifikasiController as PbjNotifikasiController;
use App\Http\Controllers\Pbj\PengadaanController as PbjPengadaanController;
use App\Http\Controllers\Pbj\ProfilController as PbjProfilController;

use Illuminate\Support\Facades\Route;

// ── REDIRECT ROOT ──
Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role ?? '';
        if ($role === 'divisi_umum') {
            return redirect()->route('admin.dashboard');
        }
        if ($role === 'pejabat_pengadaan') {
            return redirect()->route('pbj.dashboard');
        }
        return redirect()->route('karyawan.dashboard');
    }
    return redirect()->route('login');
});

// ── AUTH ──
Route::get('/login', [AuthController::class , 'showLogin'])->name('login');
Route::post('/login', [AuthController::class , 'login'])->name('login.post');

Route::post('/logout', [AuthController::class , 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── KARYAWAN PORTAL (Level 1) — TIDAK DIUBAH ──
Route::middleware(['auth'])
    ->prefix('karyawan')
    ->name('karyawan.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');

        // Permintaan Pemakaian
        Route::get('/pemakaian', [PemakaianController::class , 'index'])->name('pemakaian.index');
        Route::get('/pemakaian/create', [PemakaianController::class , 'create'])->name('pemakaian.create');
        Route::post('/pemakaian', [PemakaianController::class , 'store'])->name('pemakaian.store');

        // Permintaan Pengadaan
        Route::get('/pengadaan', [PengadaanController::class , 'index'])->name('pengadaan.index');
        Route::get('/pengadaan/create', [PengadaanController::class , 'create'])->name('pengadaan.create');
        Route::post('/pengadaan', [PengadaanController::class , 'store'])->name('pengadaan.store');

        // Profil
        Route::get('/profil', [ProfilController::class , 'index'])->name('profil');
        Route::patch('/profil', [ProfilController::class , 'updateProfil'])->name('profil.update');
        Route::patch('/profil/password', [ProfilController::class , 'updatePassword'])->name('profil.password');
        Route::get('/profil/print', [ProfilController::class , 'printAktivitas'])->name('profil.print');

        // Notifikasi
        Route::get('/notifikasi', [NotifikasiController::class , 'index'])->name('notifikasi');
    });

// ── ADMIN PORTAL (Level 2 — Divisi Umum) ──
Route::middleware(['auth', 'role:divisi_umum'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class , 'index'])->name('dashboard');

        // Permintaan Pemakaian
        Route::get('/pemakaian', [AdminPemakaianController::class , 'index'])->name('pemakaian.index');
        Route::get('/pemakaian/{pemakaian}', [AdminPemakaianController::class , 'show'])->name('pemakaian.show');
        Route::patch('/pemakaian/{pemakaian}/approve', [AdminPemakaianController::class , 'approve'])->name('pemakaian.approve');
        Route::patch('/pemakaian/{pemakaian}/reject', [AdminPemakaianController::class , 'reject'])->name('pemakaian.reject');

        // Permintaan Pengadaan
        Route::get('/pengadaan', [AdminPengadaanController::class , 'index'])->name('pengadaan.index');
        Route::get('/pengadaan/{pengadaan}', [AdminPengadaanController::class , 'show'])->name('pengadaan.show');
        Route::patch('/pengadaan/{pengadaan}/approve', [AdminPengadaanController::class , 'approve'])->name('pengadaan.approve');
        Route::patch('/pengadaan/{pengadaan}/reject', [AdminPengadaanController::class , 'reject'])->name('pengadaan.reject');

        // Verifikasi Pengadaan dari PBJ
        Route::patch('/pengadaan/{pengadaan}/verify', [AdminPengadaanController::class , 'verify'])->name('pengadaan.verify');
        Route::patch('/pengadaan/{pengadaan}/reject-verification', [AdminPengadaanController::class , 'rejectVerification'])->name('pengadaan.rejectVerification');

        // Manajemen Karyawan
        Route::get('/karyawan', [AdminKaryawanController::class , 'index'])->name('karyawan.index');
        Route::get('/karyawan/create', [AdminKaryawanController::class , 'create'])->name('karyawan.create');
        Route::post('/karyawan', [AdminKaryawanController::class , 'store'])->name('karyawan.store');
        Route::get('/karyawan/{karyawan}/history', [AdminKaryawanController::class , 'history'])->name('karyawan.history');
        Route::patch('/karyawan/{karyawan}/toggle-active', [AdminKaryawanController::class , 'toggleActive'])->name('karyawan.toggleActive');

        // Notifikasi
        Route::get('/notifikasi', [AdminNotifikasiController::class , 'index'])->name('notifikasi');

        // Profil
        Route::get('/profil', [AdminProfilController::class , 'index'])->name('profil');
        Route::patch('/profil', [AdminProfilController::class , 'updateProfil'])->name('profil.update');
        Route::patch('/profil/password', [AdminProfilController::class , 'updatePassword'])->name('profil.password');
    });

// ── PBJ PORTAL (Level 3 — Pengadaan Barang & Jasa) ──
Route::middleware(['auth', 'role:pejabat_pengadaan'])
    ->prefix('pbj')
    ->name('pbj.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [PbjDashboardController::class , 'index'])->name('dashboard');

        // Pengadaan
        Route::get('/pengadaan', [PbjPengadaanController::class , 'index'])->name('pengadaan.index');
        Route::get('/pengadaan/{pengadaan}', [PbjPengadaanController::class , 'show'])->name('pengadaan.show');
        Route::patch('/pengadaan/{pengadaan}/complete', [PbjPengadaanController::class , 'complete'])->name('pengadaan.complete');
        Route::patch('/pengadaan/{pengadaan}/reject', [PbjPengadaanController::class , 'reject'])->name('pengadaan.reject');

        // Notifikasi
        Route::get('/notifikasi', [PbjNotifikasiController::class , 'index'])->name('notifikasi');

        // Profil
        Route::get('/profil', [PbjProfilController::class , 'index'])->name('profil');
        Route::patch('/profil', [PbjProfilController::class , 'updateProfil'])->name('profil.update');
        Route::patch('/profil/password', [PbjProfilController::class , 'updatePassword'])->name('profil.password');
    });
