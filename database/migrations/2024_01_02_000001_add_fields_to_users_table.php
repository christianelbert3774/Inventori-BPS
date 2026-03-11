<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['karyawan', 'divisi_umum', 'pejabat_pengadaan'])
                  ->default('karyawan')->after('email');
            $table->string('nip', 30)->nullable()->unique()->after('role');
            $table->string('bagian', 100)->nullable()->after('nip');
            $table->string('jabatan', 100)->nullable()->after('bagian');
            $table->string('no_telp', 20)->nullable()->after('jabatan');
            $table->boolean('is_active')->default(true)->after('no_telp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role','nip','bagian','jabatan','no_telp','is_active']);
        });
    }
};
