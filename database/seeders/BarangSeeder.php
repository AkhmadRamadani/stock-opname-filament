<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\User;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = KategoriBarang::all();
        $users = User::all();

        if ($categories->isEmpty()) {
            $categories = KategoriBarang::factory()->count(5)->create();
        }

        if ($users->isEmpty()) {
            $users = User::factory()->count(3)->create();
        }

        for ($i = 0; $i < 20; $i++) {
            Barang::factory()->create([
                'id_kategori' => $categories->random()->id,
                'id_user_input' => $users->random()->id,
            ]);
        }
    }
}
