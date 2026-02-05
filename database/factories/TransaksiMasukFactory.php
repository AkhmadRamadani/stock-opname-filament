<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransaksiMasuk>
 */
class TransaksiMasukFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'verified', 'rejected']);
        $isVerifiedOrRejected = in_array($status, ['verified', 'rejected']);

        return [
            'tanggal_masuk' => fake()->date(),
            'kode_barang' => Barang::factory(),
            'jumlah_masuk' => fake()->numberBetween(1, 100),
            'keterangan' => fake()->sentence(),
            'harga_beli' => fake()->numberBetween(1000, 1000000),
            'supplier' => fake()->company(),
            'id_user_input' => User::factory(),
            'status' => $status,
            'id_user_verifikator' => $isVerifiedOrRejected ? User::factory() : null,
            'tanggal_verifikasi' => $isVerifiedOrRejected ? fake()->dateTime() : null,
        ];
    }
}
