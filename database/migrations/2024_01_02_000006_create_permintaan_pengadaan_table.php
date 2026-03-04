<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permintaan_pengadaan')) {
            return;
        }

        Schema::create('permintaan_pengadaan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengadaan', 30)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('bagian', 100);
            $table->string('jabatan', 100);
            $table->string('nama_barang', 200);
            $table->string('kategori', 100);
            $table->unsignedInteger('jumlah');
            $table->string('satuan', 50);
            $table->decimal('estimasi_harga', 15, 2)->nullable();
            $table->string('spesifikasi', 500)->nullable();
            $table->enum('alasan_type', ['stok_habis','barang_baru','kerusakan','kebutuhan_mendadak','lainnya']);
            $table->text('alasan_detail');
            $table->enum('urgensi', ['Rendah','Sedang','Tinggi'])->default('Sedang');
            $table->date('tanggal_dibutuhkan');
            $table->enum('status', ['menunggu','diteruskan','diproses','selesai','ditolak'])->default('menunggu');
            $table->text('catatan_divisi')->nullable();
            $table->text('catatan_pengadaan')->nullable();
            $table->foreignId('diteruskan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diteruskan_pada')->nullable();
            $table->foreignId('diselesaikan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diselesaikan_pada')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('urgensi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_pengadaan');
    }
};
