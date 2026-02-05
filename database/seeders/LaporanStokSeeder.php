<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\LaporanStok;
use App\Models\User;
use Illuminate\Database\Seeder;

class LaporanStokSeeder extends Seeder
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

        for ($i = 0; $i < 10; $i++) {
            $status = fake()->randomElement(['draft', 'verified', 'published']);
            $isVerifiedOrPublished = in_array($status, ['verified', 'published']);

            $data = [
                'kode_barang' => $barangs->random()->kode_barang,
                'status' => $status,
                'id_user_verifikator' => $isVerifiedOrPublished ? $users->random()->id : null,
                'tanggal_verifikasi' => $isVerifiedOrPublished ? fake()->dateTime() : null,
            ];

            LaporanStok::factory()->create($data);
        }
    }
}
