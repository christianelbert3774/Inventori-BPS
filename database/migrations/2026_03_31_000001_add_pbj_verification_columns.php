<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom untuk fitur PBJ: foto bukti, catatan PBJ,
 * verifikasi admin, dan jumlah realisasi per detail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengadaan', function (Blueprint $table) {
            if (!Schema::hasColumn('pengadaan', 'foto_bukti')) {
                $table->string('foto_bukti', 500)->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('pengadaan', 'catatan_pbj')) {
                $table->text('catatan_pbj')->nullable()->after('foto_bukti');
            }
            if (!Schema::hasColumn('pengadaan', 'status_admin_verifikasi')) {
                $table->string('status_admin_verifikasi', 20)->default('belum')->after('catatan_pbj');
            }
            if (!Schema::hasColumn('pengadaan', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('status_admin_verifikasi');
                $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('pengadaan', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
        });

        // Tambah kolom jumlah_realisasi di pengadaan_detail
        Schema::table('pengadaan_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('pengadaan_detail', 'jumlah_realisasi')) {
                $table->unsignedInteger('jumlah_realisasi')->nullable()->after('jumlah');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengadaan', function (Blueprint $table) {
            $cols = ['foto_bukti', 'catatan_pbj', 'status_admin_verifikasi', 'verified_at'];
            $toDrop = array_values(array_filter($cols, fn($c) => Schema::hasColumn('pengadaan', $c)));

            if (Schema::hasColumn('pengadaan', 'verified_by')) {
                $table->dropForeign(['verified_by']);
                $toDrop[] = 'verified_by';
            }

            if ($toDrop) {
                $table->dropColumn($toDrop);
            }
        });

        Schema::table('pengadaan_detail', function (Blueprint $table) {
            if (Schema::hasColumn('pengadaan_detail', 'jumlah_realisasi')) {
                $table->dropColumn('jumlah_realisasi');
            }
        });
    }
};
