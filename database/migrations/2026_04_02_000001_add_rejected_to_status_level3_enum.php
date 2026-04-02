<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menambahkan nilai 'rejected' ke enum status_level3 pada tabel pengadaan,
 * agar PBJ bisa menolak pengadaan.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pengadaan MODIFY COLUMN status_level3 ENUM('pending','completed','rejected') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pengadaan MODIFY COLUMN status_level3 ENUM('pending','completed') DEFAULT 'pending'");
    }
};
