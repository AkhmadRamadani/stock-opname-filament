<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi_keluar', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_keluar');
            $table->string('kode_barang');
            $table->integer('jumlah_keluar');
            $table->string('tujuan');
            $table->unsignedBigInteger('id_user_input');
            $table->enum('status', ['pending', 'verified', 'rejected']);
            $table->unsignedBigInteger('id_user_verifikator')->nullable();
            $table->dateTime('tanggal_verifikasi')->nullable();
            $table->timestamps();

            $table->foreign('kode_barang')->references('kode_barang')->on('barang');
            $table->foreign('id_user_input')->references('id')->on('users');
            $table->foreign('id_user_verifikator')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_keluar');
    }
};
