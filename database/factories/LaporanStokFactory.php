<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LaporanStok>
 */
class LaporanStokFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stokAwal = fake()->numberBetween(0, 100);
        $totalMasuk = fake()->numberBetween(0, 50);
        $totalKeluar = fake()->numberBetween(0, 50);
        $stokAkhir = $stokAwal + $totalMasuk - $totalKeluar;

        $status = fake()->randomElement(['draft', 'verified', 'published']);
        $isVerifiedOrPublished = in_array($status, ['verified', 'published']);

        return [
            'kode_barang' => Barang::factory(),
            'tanggal' => fake()->date(),
            'stok_awal' => $stokAwal,
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'stok_akhir' => $stokAkhir,
            'status' => $status,
            'id_user_verifikator' => $isVerifiedOrPublished ? User::factory() : null,
            'tanggal_verifikasi' => $isVerifiedOrPublished ? fake()->dateTime() : null,
        ];
    }
}
