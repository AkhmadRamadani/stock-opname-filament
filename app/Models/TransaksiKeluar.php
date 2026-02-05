<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiKeluar extends Model
{
    use HasFactory;

    protected $table = 'transaksi_keluar';

    protected $fillable = [
        'tanggal_keluar',
        'kode_barang',
        'jumlah_keluar',
        'tujuan',
        'id_user_input',
        'status',
        'id_user_verifikator',
        'tanggal_verifikasi'
    ];

    protected $casts = [
        'tanggal_keluar' => 'date',
        'tanggal_verifikasi' => 'datetime',
        'jumlah_keluar' => 'integer',
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
            ->where('tipe_transaksi', 'keluar');
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
                'tipe_transaksi' => 'keluar',
                'id_referensi' => $model->id,
                'id_user_verifikator' => auth()->id() ?? $model->id_user_input,
                'status_sebelum' => null,
                'status_sesudah' => 'pending',
                'catatan_verifikasi' => 'Transaksi keluar dibuat',
                'tanggal_verifikasi' => now(),
            ]);
        });
    }
}
