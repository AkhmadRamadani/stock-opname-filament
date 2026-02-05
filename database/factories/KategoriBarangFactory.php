<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KategoriBarang>
 */
class KategoriBarangFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_kategori' => fake()->unique()->bothify('KAT-###'),
            'nama_kategori' => fake()->word(),
            'deskripsi_kategori' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
