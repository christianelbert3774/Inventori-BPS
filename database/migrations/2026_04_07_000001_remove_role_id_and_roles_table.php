<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menghapus kolom role_id dari tabel users dan menghapus tabel roles.
 * Semua pengecekan role kini menggunakan kolom enum 'role' di tabel users.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Hapus foreign key lalu kolom role_id dari tabel users (jika ada)
        if (Schema::hasColumn('users', 'role_id')) {
            // FK dibuat manual di MySQL, constraint name = 'users_ibfk_1'
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign('users_ibfk_1');
            });
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role_id');
            });
        }

        // Hapus tabel roles (jika ada)
        Schema::dropIfExists('roles');
    }

    public function down(): void
    {
        // Buat ulang tabel roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Tambahkan kembali kolom role_id ke tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('id');
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });
    }
};
