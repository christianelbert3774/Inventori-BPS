<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kategori_barang')) {
            return;
        }

        Schema::create('kategori_barang', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->string('deskripsi', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_barang');
    }
};
