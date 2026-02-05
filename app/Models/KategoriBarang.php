<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBarang extends Model
{
    use HasFactory;

    protected $table = 'kategori_barang';
    protected $fillable = ['kode_kategori', 'nama_kategori', 'deskripsi_kategori', 'is_active', 'id_user_input', 'id_user_update'];

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
    public function barang()
    {
        return $this->hasMany(Barang::class, 'id_kategori', 'id');
    }

    public function userInput()
    {
        return $this->belongsTo(User::class, 'id_user_input', 'id');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'id_user_update', 'id');
    }
}
