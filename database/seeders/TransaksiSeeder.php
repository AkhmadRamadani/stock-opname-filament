<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiMasuk;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barangs = Barang::all();
        $users = User::all();

        if ($barangs->isEmpty()) {
            return;
        }

        if ($users->isEmpty()) {
            $users = User::factory()->count(3)->create();
        }

        // Transaksi Masuk
        for ($i = 0; $i < 10; $i++) {
            $status = fake()->randomElement(['pending', 'verified', 'rejected']);
            $isVerifiedOrRejected = in_array($status, ['verified', 'rejected']);

            $data = [
                'kode_barang' => $barangs->random()->kode_barang,
                'id_user_input' => $users->random()->id,
                'status' => $status,
                'id_user_verifikator' => $isVerifiedOrRejected ? $users->random()->id : null,
                'tanggal_verifikasi' => $isVerifiedOrRejected ? fake()->dateTime() : null,
            ];

            TransaksiMasuk::factory()->create($data);
        }

        // Transaksi Keluar
        for ($i = 0; $i < 10; $i++) {
            $status = fake()->randomElement(['pending', 'verified', 'rejected']);
            $isVerifiedOrRejected = in_array($status, ['verified', 'rejected']);

            $data = [
                'kode_barang' => $barangs->random()->kode_barang,
                'id_user_input' => $users->random()->id,
                'status' => $status,
                'id_user_verifikator' => $isVerifiedOrRejected ? $users->random()->id : null,
                'tanggal_verifikasi' => $isVerifiedOrRejected ? fake()->dateTime() : null,
            ];

            TransaksiKeluar::factory()->create($data);
        }
    }
}
