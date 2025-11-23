<?php
// database/migrations/xxxx_create_barang_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->string('kode_barang')->unique()->primary();
            $table->string('nama_barang');
            $table->text('deskripsi')->nullable();
            $table->foreignId('id_kategori')->constrained('kategori_barang')->onDelete('cascade');
            $table->string('satuan');
            $table->integer('harga_satuan')->default(0);
            $table->datetime('created_at');
            $table->datetime('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('barang');
    }
};