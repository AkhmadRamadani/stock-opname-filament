<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';
    protected $primaryKey = 'kode_barang'; // PK dari ERD string
    public $incrementing = false;          // karena bukan integer
    protected $keyType = 'string';         // tipe string
    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'satuan',
        'harga_satuan',
        'id_kategori',
        'deskripsi',
        'id_user_input',
        'id_user_update'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->id_user_input && auth()->check()) {
                $model->id_user_input = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->id_user_update = auth()->id();
            }
        });
    }

    // Relasi
    public function kategori()
    {
        return $this->belongsTo(KategoriBarang::class, 'id_kategori', 'id');
    }

    public function userInput()
    {
        return $this->belongsTo(User::class, 'id_user_input', 'id');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'id_user_update', 'id');
    }

    public function transaksiMasuk()
    {
        return $this->hasMany(TransaksiMasuk::class, 'kode_barang', 'kode_barang');
    }

    public function transaksiKeluar()
    {
        return $this->hasMany(TransaksiKeluar::class, 'kode_barang', 'kode_barang');
    }

    public function laporanStok()
    {
        return $this->hasMany(LaporanStok::class, 'kode_barang', 'kode_barang');
    }
}
