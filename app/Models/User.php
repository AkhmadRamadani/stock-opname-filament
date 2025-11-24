<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active'
    ];

    public function barang()
    {
        return $this->hasMany(Barang::class, 'id_user_input', 'id');
    }

    public function transaksiMasuk()
    {
        return $this->hasMany(TransaksiMasuk::class, 'id_user_input', 'id');
    }

    public function transaksiKeluar()
    {
        return $this->hasMany(TransaksiKeluar::class, 'id_user_input', 'id');
    }

    public function verifikasiLog()
    {
        return $this->hasMany(VerifikasiLog::class, 'id_user_verifikator', 'id');
    }

    public function laporanStok()
    {
        return $this->hasMany(LaporanStok::class, 'id_user_verifikator', 'id');
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }
}
