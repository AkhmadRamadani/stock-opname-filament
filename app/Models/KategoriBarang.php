<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBarang extends Model
{
    use HasFactory;

    protected $table = 'kategori_barang';
    protected $fillable = ['kode_kategori', 'nama_kategori', 'deskripsi_kategori', 'is_active'];

    // Relasi
    public function barang()
    {
        return $this->hasMany(Barang::class, 'id_kategori', 'id');
    }
}
