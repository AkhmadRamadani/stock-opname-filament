<?php
// database/migrations/xxxx_create_kategori_barang_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kategori_barang', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kategori')->unique();
            $table->string('nama_kategori');
            $table->text('deskripsi_kategori')->nullable();
            $table->boolean('is_active')->default(true);
            $table->datetime('created_at');
            $table->datetime('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kategori_barang');
    }
};