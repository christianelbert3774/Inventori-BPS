<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * BUGFIX: Sebelumnya, saat karyawan submit form pengadaan barang baru,
 * record Barang langsung dibuat ke tabel 'barang' padahal seharusnya
 * baru dibuat setelah Divisi Umum approve dan PBJ menyelesaikan pengadaan.
 *
 * CATATAN: Tabel barang menggunakan INT (bukan BIGINT), jadi FK harus
 * menggunakan unsignedInteger agar kompatibel.
 *
 * Migration ini idempotent — aman dijalankan ulang meski sebelumnya gagal.
 */
return new class extends Migration
{
    private function fkExists(string $table, string $fkName): bool
    {
        return collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$table, $fkName]))->isNotEmpty();
    }

    public function up(): void
    {
        // Step 1: Drop FK lama jika masih ada (nama asli MySQL)
        if ($this->fkExists('pengadaan_detail', 'pengadaan_detail_ibfk_2')) {
            DB::statement('ALTER TABLE pengadaan_detail DROP FOREIGN KEY pengadaan_detail_ibfk_2');
        }

        // Step 2: Modify barang_id jadi nullable
        // Pakai INT (bukan BIGINT) agar cocok dengan tipe kolom id di tabel barang
        DB::statement('ALTER TABLE pengadaan_detail MODIFY barang_id INT NULL');

        // Step 3: Buat ulang FK nullable (skip jika sudah ada)
        if (!$this->fkExists('pengadaan_detail', 'pengadaan_detail_ibfk_2')) {
            DB::statement('ALTER TABLE pengadaan_detail ADD CONSTRAINT pengadaan_detail_ibfk_2 FOREIGN KEY (barang_id) REFERENCES barang(id) ON DELETE SET NULL');
        }

        // Step 4: Tambah kolom baru (skip jika sudah ada)
        Schema::table('pengadaan_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('pengadaan_detail', 'tipe_item')) {
                $table->string('tipe_item', 10)->default('restock')->after('pengadaan_id');
            }
            if (!Schema::hasColumn('pengadaan_detail', 'nama_barang_baru')) {
                $table->string('nama_barang_baru', 100)->nullable()->after('barang_id');
            }
            if (!Schema::hasColumn('pengadaan_detail', 'satuan_baru')) {
                $table->string('satuan_baru', 30)->nullable()->after('nama_barang_baru');
            }
            if (!Schema::hasColumn('pengadaan_detail', 'kategori_baru')) {
                $table->string('kategori_baru', 100)->nullable()->after('satuan_baru');
            }
            if (!Schema::hasColumn('pengadaan_detail', 'alasan')) {
                $table->text('alasan')->nullable()->after('jumlah');
            }
        });
    }

    public function down(): void
    {
        // Hapus kolom baru yang ada
        $cols   = ['tipe_item', 'nama_barang_baru', 'satuan_baru', 'kategori_baru', 'alasan'];
        $toDrop = array_values(array_filter($cols, fn($c) => Schema::hasColumn('pengadaan_detail', $c)));
        if ($toDrop) {
            Schema::table('pengadaan_detail', fn(Blueprint $t) => $t->dropColumn($toDrop));
        }

        // Drop FK dan kembalikan ke kondisi semula
        if ($this->fkExists('pengadaan_detail', 'pengadaan_detail_ibfk_2')) {
            DB::statement('ALTER TABLE pengadaan_detail DROP FOREIGN KEY pengadaan_detail_ibfk_2');
        }
        DB::statement('ALTER TABLE pengadaan_detail MODIFY barang_id INT NOT NULL');
        if (!$this->fkExists('pengadaan_detail', 'pengadaan_detail_ibfk_2')) {
            DB::statement('ALTER TABLE pengadaan_detail ADD CONSTRAINT pengadaan_detail_ibfk_2 FOREIGN KEY (barang_id) REFERENCES barang(id) ON DELETE CASCADE');
        }
    }
};
