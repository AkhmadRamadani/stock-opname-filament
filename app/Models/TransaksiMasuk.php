<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiMasuk extends Model
{
    use HasFactory;

    protected $table = 'transaksi_masuk';

    protected $fillable = [
        'tanggal_masuk',
        'kode_barang',
        'jumlah_masuk',
        'keterangan',
        'harga_beli',
        'supplier',
        'id_user_input',
        'status',
        'id_user_verifikator',
        'tanggal_verifikasi'
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_verifikasi' => 'datetime',
        'jumlah_masuk' => 'integer',
        'harga_beli' => 'decimal:2',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    public function userInput()
    {
        return $this->belongsTo(User::class, 'id_user_input');
    }

    public function userVerifikator()
    {
        return $this->belongsTo(User::class, 'id_user_verifikator');
    }

    public function verifikasiLogs()
    {
        return $this->hasMany(VerifikasiLog::class, 'id_referensi')
            ->where('tipe_transaksi', 'masuk');
    }

    public function getTotalHargaAttribute()
    {
        return $this->jumlah_masuk * $this->harga_beli;
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->id_user_input = auth()->id();
            }
        });

        static::created(function ($model) {
            \App\Models\VerifikasiLog::create([
                'tipe_transaksi' => 'masuk',
                'id_referensi' => $model->id,
                'id_user_verifikator' => auth()->id() ?? $model->id_user_input,
                'status_sebelum' => 'draft',
                'status_sesudah' => 'pending',
                'catatan_verifikasi' => 'Transaksi masuk dibuat',
                'tanggal_verifikasi' => now(),
            ]);
        });
    }
}
