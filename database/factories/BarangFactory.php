<?php

namespace Database\Factories;

use App\Models\KategoriBarang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Barang>
 */
class BarangFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_barang' => fake()->unique()->bothify('BRG-#####'),
            'nama_barang' => fake()->words(3, true),
            'deskripsi' => fake()->sentence(),
            'id_kategori' => KategoriBarang::factory(),
            'satuan' => fake()->randomElement(['pcs', 'box', 'unit', 'kg']),
            'harga_satuan' => fake()->numberBetween(1000, 1000000),
            'id_user_input' => User::factory(),
            'id_user_update' => null,
        ];
    }
}
