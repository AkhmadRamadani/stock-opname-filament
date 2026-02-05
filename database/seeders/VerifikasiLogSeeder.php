<?php

namespace Database\Seeders;

use App\Models\LaporanStok;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiMasuk;
use App\Models\User;
use App\Models\VerifikasiLog;
use Illuminate\Database\Seeder;

class VerifikasiLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        $masuk = TransaksiMasuk::whereIn('status', ['verified', 'rejected'])->limit(5)->get();
        foreach ($masuk as $t) {
            VerifikasiLog::factory()->create([
                'tipe_transaksi' => 'masuk',
                'id_referensi' => $t->id,
                'id_user_verifikator' => $t->id_user_verifikator ?? $users->random()->id,
                'status_sebelum' => 'pending',
                'status_sesudah' => $t->status,
                'tanggal_verifikasi' => $t->tanggal_verifikasi ?? now(),
            ]);
        }

        $keluar = TransaksiKeluar::whereIn('status', ['verified', 'rejected'])->limit(5)->get();
        foreach ($keluar as $t) {
            VerifikasiLog::factory()->create([
                'tipe_transaksi' => 'keluar',
                'id_referensi' => $t->id,
                'id_user_verifikator' => $t->id_user_verifikator ?? $users->random()->id,
                'status_sebelum' => 'pending',
                'status_sesudah' => $t->status,
                'tanggal_verifikasi' => $t->tanggal_verifikasi ?? now(),
            ]);
        }

        $laporan = LaporanStok::whereIn('status', ['verified', 'published'])->limit(5)->get();
        foreach ($laporan as $t) {
            VerifikasiLog::factory()->create([
                'tipe_transaksi' => 'laporan',
                'id_referensi' => $t->id,
                'id_user_verifikator' => $t->id_user_verifikator ?? $users->random()->id,
                'status_sebelum' => 'draft',
                'status_sesudah' => $t->status,
                'tanggal_verifikasi' => $t->tanggal_verifikasi ?? now(),
            ]);
        }
    }
}
