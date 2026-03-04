<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('item_permintaan')) {
            return;
        }

        Schema::create('item_permintaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_id')->constrained('permintaan_pemakaian')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barang')->restrictOnDelete();
            $table->unsignedInteger('jumlah');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_permintaan');
    }
};
