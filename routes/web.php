<?php
/*
  ┌──────────────────────────────────────────────────────────────┐
  │  DIMODIFIKASI — routes/web.php                               │
  │  Perubahan:                                                  │
  │   1. Tambah import controller Admin (3 controller baru)      │
  │   2. Tambah route group prefix 'admin' untuk Level 2         │
  │      dengan middleware 'role:admin_gudang'                   │
  │   3. Route karyawan yang sudah ada TIDAK diubah sama sekali  │
  └──────────────────────────────────────────────────────────────┘
*/

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

use Illuminate\Support\Facades\Route;

// ── REDIRECT ROOT ──
Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role ?? '';
        if ($role === 'divisi_umum') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('karyawan.dashboard');
    }
    return redirect()->route('login');
});

// ── AUTH ──
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── KARYAWAN PORTAL (Level 1) — TIDAK DIUBAH ──
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

        // Profil
        Route::get('/profil',              [ProfilController::class, 'index'])->name('profil');
        Route::patch('/profil',            [ProfilController::class, 'updateProfil'])->name('profil.update');
        Route::patch('/profil/password',   [ProfilController::class, 'updatePassword'])->name('profil.password');
        Route::get('/profil/print',        [ProfilController::class, 'printAktivitas'])->name('profil.print');

        // Notifikasi
        Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    });

// ── ADMIN PORTAL (Level 2 — Divisi Umum) — BARU ──
Route::middleware(['auth', 'role:divisi_umum'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Permintaan Pemakaian
        Route::get('/pemakaian',                    [AdminPemakaianController::class, 'index'])->name('pemakaian.index');
        Route::get('/pemakaian/{pemakaian}',         [AdminPemakaianController::class, 'show'])->name('pemakaian.show');
        Route::patch('/pemakaian/{pemakaian}/approve',[AdminPemakaianController::class, 'approve'])->name('pemakaian.approve');
        Route::patch('/pemakaian/{pemakaian}/reject', [AdminPemakaianController::class, 'reject'])->name('pemakaian.reject');

        // Permintaan Pengadaan
        Route::get('/pengadaan',                    [AdminPengadaanController::class, 'index'])->name('pengadaan.index');
        Route::get('/pengadaan/{pengadaan}',         [AdminPengadaanController::class, 'show'])->name('pengadaan.show');
        Route::patch('/pengadaan/{pengadaan}/approve',[AdminPengadaanController::class, 'approve'])->name('pengadaan.approve');
        Route::patch('/pengadaan/{pengadaan}/reject', [AdminPengadaanController::class, 'reject'])->name('pengadaan.reject');

        // Manajemen Karyawan
        Route::get('/karyawan',                         [AdminKaryawanController::class, 'index'])->name('karyawan.index');
        Route::get('/karyawan/create',                  [AdminKaryawanController::class, 'create'])->name('karyawan.create');
        Route::post('/karyawan',                        [AdminKaryawanController::class, 'store'])->name('karyawan.store');
        Route::get('/karyawan/{karyawan}/history',      [AdminKaryawanController::class, 'history'])->name('karyawan.history');
        Route::patch('/karyawan/{karyawan}/toggle-active',[AdminKaryawanController::class, 'toggleActive'])->name('karyawan.toggleActive');

        // Notifikasi
        Route::get('/notifikasi', [AdminNotifikasiController::class, 'index'])->name('notifikasi');

        // Profil
        Route::get('/profil',            [AdminProfilController::class, 'index'])->name('profil');
        Route::patch('/profil',          [AdminProfilController::class, 'updateProfil'])->name('profil.update');
        Route::patch('/profil/password', [AdminProfilController::class, 'updatePassword'])->name('profil.password');
    });
