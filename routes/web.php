<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Dashboard (Login Required)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return "Ini Dashboard Umum (Semua Role Bisa Masuk)";
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Role Test Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin-area', function () {
        return "HALAMAN KHUSUS ADMIN";
    });
});

Route::middleware(['auth', 'role:karyawan'])->group(function () {
    Route::get('/karyawan-area', function () {
        return "HALAMAN KHUSUS KARYAWAN";
    });
});

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';