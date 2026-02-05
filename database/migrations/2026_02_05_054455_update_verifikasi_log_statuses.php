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
        Schema::table('verifikasi_log', function (Blueprint $table) {
            // Change enum to string to allow more flexible statuses like 'draft' or 'pending'
            // and make status_sebelum nullable for initial records
            $table->string('status_sebelum')->nullable()->change();
            $table->string('status_sesudah')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verifikasi_log', function (Blueprint $table) {
            // Revert changes (this is tricky with SQLite/Enum, but we can try to revert to non-nullable string)
            // Ideally we would revert to Enum but that might lose data if we have invalid values.
            // For now, we just ensure they are not nullable again if we want to strict revert,
            // but in practice down() for type changes is often just "do nothing" or best effort.
            // Let's try to revert to string not null, assuming data is clean.
            // $table->string('status_sebelum')->nullable(false)->change(); // This might fail if we have nulls
        });
    }
};
