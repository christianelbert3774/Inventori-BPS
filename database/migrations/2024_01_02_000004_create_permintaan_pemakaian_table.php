<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permintaan_pemakaian')) {
            return;
        }

        Schema::create('permintaan_pemakaian', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_permintaan', 30)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('bagian', 100);
            $table->string('keperluan', 255);
            $table->text('catatan')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_pemakaian');
    }
};
