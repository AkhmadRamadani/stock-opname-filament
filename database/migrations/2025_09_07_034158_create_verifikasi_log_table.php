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
        Schema::create('verifikasi_log', function (Blueprint $table) {
            $table->id();
            $table->string('tipe_transaksi'); // masuk, keluar, laporan
            $table->unsignedBigInteger('id_referensi');
            $table->unsignedBigInteger('id_user_verifikator');
            $table->enum('status_sebelum', ['pending', 'draft']);
            $table->enum('status_sesudah', ['verified', 'rejected', 'published']);
            $table->string('catatan_verifikasi')->nullable();
            $table->dateTime('tanggal_verifikasi');
            $table->timestamps();

            $table->foreign('id_user_verifikator')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifikasi_log');
    }
};
