<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BARU — Tambah kolom notif_read_at di tabel users
 * Digunakan untuk menyimpan timestamp terakhir user membuka halaman notifikasi.
 * Badge dot merah akan hilang jika semua notifikasi sudah lebih lama dari notif_read_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('notif_read_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notif_read_at');
        });
    }
};
