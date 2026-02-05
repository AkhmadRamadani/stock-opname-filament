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
        Schema::table('barang', function (Blueprint $table) {
            // id_user_input might already exist in Model but not in DB.
            if (!Schema::hasColumn('barang', 'id_user_input')) {
                $table->foreignId('id_user_input')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('barang', 'id_user_update')) {
                $table->foreignId('id_user_update')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('kategori_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('kategori_barang', 'id_user_input')) {
                $table->foreignId('id_user_input')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('kategori_barang', 'id_user_update')) {
                $table->foreignId('id_user_update')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            if (Schema::hasColumn('barang', 'id_user_input')) {
                $table->dropForeign(['id_user_input']);
                $table->dropColumn('id_user_input');
            }
            if (Schema::hasColumn('barang', 'id_user_update')) {
                 $table->dropForeign(['id_user_update']);
                 $table->dropColumn('id_user_update');
            }
        });

        Schema::table('kategori_barang', function (Blueprint $table) {
            if (Schema::hasColumn('kategori_barang', 'id_user_input')) {
                 $table->dropForeign(['id_user_input']);
                 $table->dropColumn('id_user_input');
            }
            if (Schema::hasColumn('kategori_barang', 'id_user_update')) {
                 $table->dropForeign(['id_user_update']);
                 $table->dropColumn('id_user_update');
            }
        });
    }
};
