<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VerifikasiLog>
 */
class VerifikasiLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipe_transaksi' => fake()->randomElement(['masuk', 'keluar', 'laporan']),
            'id_referensi' => 1, // Placeholder
            'id_user_verifikator' => User::factory(),
            'status_sebelum' => fake()->randomElement(['pending', 'draft']),
            'status_sesudah' => fake()->randomElement(['verified', 'rejected', 'published']),
            'catatan_verifikasi' => fake()->sentence(),
            'tanggal_verifikasi' => now(),
        ];
    }
}
