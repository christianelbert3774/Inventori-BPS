# SIBAS — Sistem Inventori Barang BPS

## Struktur File yang Disertakan

```
sibas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   └── Karyawan/
│   │   │       ├── DashboardController.php
│   │   │       ├── PemakaianController.php
│   │   │       └── PengadaanController.php
│   │   └── Middleware/
│   │       └── CheckRole.php
│   └── Models/
│       ├── User.php
│       ├── Role.php
│       ├── Barang.php
│       ├── Pemakaian.php
│       ├── PemakaianDetail.php
│       ├── Pengadaan.php
│       └── PengadaanDetail.php
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php          ← Layout utama (sidebar + topbar)
│   ├── auth/
│   │   └── login.blade.php        ← Halaman login
│   └── karyawan/
│       ├── dashboard.blade.php        ← Dashboard stok barang
│       ├── form-pemakaian.blade.php   ← Form permintaan pemakaian
│       ├── form-pengadaan.blade.php   ← Form permintaan pengadaan
│       ├── riwayat-pemakaian.blade.php
│       └── riwayat-pengadaan.blade.php
├── public/
│   ├── css/app.css
│   ├── js/app.js
│   └── images/                    ← Letakkan logo-bps.png di sini
├── routes/web.php
├── database/seeders/DatabaseSeeder.php
└── bootstrap/app.php              ← Registrasi middleware 'role'
```

---

## Cara Setup di Project Laravel yang Ada

### 1. Salin File

Salin semua file ke project Laravel Anda sesuai path-nya masing-masing.

### 2. Konfigurasi .env

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventori-bps
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Import Database

```bash
mysql -u root inventori-bps < inventori-bps.sql
```

### 4. Jalankan Seeder (data dummy)

```bash
php artisan db:seed
```

### 5. Pasang Logo BPS

Salin file logo BPS ke:
```
public/images/logo-bps.png
```

### 6. Daftarkan Middleware (Laravel 11)

File `bootstrap/app.php` sudah disertakan dengan registrasi middleware `role`.
Jika project Anda masih Laravel 10, tambahkan di `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ... existing
    'role' => \App\Http\Middleware\CheckRole::class,
];
```

### 7. Pastikan Auth Config

Di `config/auth.php`, pastikan model User mengarah ke:
```php
'model' => App\Models\User::class,
```

### 8. Jalankan Aplikasi

```bash
php artisan serve
```

---

## Akun Login (Setelah Seeder)

| Email                  | Password | Role         |
|------------------------|----------|--------------|
| karyawan@bps.go.id     | password | Karyawan     |
| admin@bps.go.id        | password | Divisi Umum  |
| pbj@bps.go.id          | password | PBJ          |

---

## Fitur yang Sudah Ada

- ✅ Login dengan email + password (redirect by role)
- ✅ Dashboard karyawan: statistik stok, tabel barang dengan filter
- ✅ Form permintaan pemakaian (multi-barang, tambah/hapus baris dinamis)
- ✅ Form permintaan pengadaan:
  - **Restock**: pilih barang yang ada + jumlah
  - **Barang Baru**: nama, satuan, kategori, alasan
- ✅ Riwayat pemakaian & pengadaan
- ✅ Middleware `CheckRole` untuk proteksi halaman
- ✅ Validasi server-side di semua form
- ✅ Desain responsif, full-screen, nuansa BPS

---

## Catatan Developer

- Kolom `keterangan` tidak ada di tabel `pemakaian` dan `pengadaan_detail` (sesuai SQL).
  Jika dibutuhkan, tambahkan migrasi: `php artisan make:migration add_keterangan_to_pemakaian`
- Stok "hampir habis" didefinisikan: stok ≤ 10 (bisa diubah di `Barang::getStatusAttribute`)
- Pengadaan barang baru otomatis membuat record di tabel `barang` dengan stok 0
