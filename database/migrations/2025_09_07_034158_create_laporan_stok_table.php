<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_stok', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang');
            $table->date('tanggal');
            $table->integer('stok_awal')->default(0);
            $table->integer('total_masuk')->default(0);
            $table->integer('total_keluar')->default(0);
            $table->integer('stok_akhir')->default(0);
            $table->enum('status', ['draft', 'verified', 'published'])->default('draft');
            $table->unsignedBigInteger('id_user_verifikator')->nullable();
            $table->dateTime('tanggal_verifikasi')->nullable();
            $table->timestamps();

            $table->foreign('kode_barang')->references('kode_barang')->on('barang')->onDelete('cascade');
            $table->foreign('id_user_verifikator')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_stok');
    }
};
