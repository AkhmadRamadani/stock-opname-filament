<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanStok extends Model
{
    use HasFactory;

    protected $table = 'laporan_stok';

    protected $fillable = [
        'kode_barang',
        'tanggal',
        'stok_awal',
        'total_masuk',
        'total_keluar',
        'stok_akhir',
        'status',
        'id_user_verifikator',
        'tanggal_verifikasi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'stok_awal' => 'integer',
        'total_masuk' => 'integer',
        'total_keluar' => 'integer',
        'stok_akhir' => 'integer',
        'tanggal_verifikasi' => 'datetime',
    ];

    // Relasi ke Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    // Relasi ke User (verifikator)
    public function verifikator()
    {
        return $this->belongsTo(User::class, 'id_user_verifikator');
    }

    public function verifikasiLogs()
    {
        return $this->hasMany(VerifikasiLog::class, 'id_referensi')
            ->where('tipe_transaksi', 'laporan');
    }

    protected static function booted()
    {
        static::created(function ($model) {
            \App\Models\VerifikasiLog::create([
                'tipe_transaksi' => 'laporan',
                'id_referensi' => $model->id,
                'id_user_verifikator' => auth()->id() ?? 1, // Default to admin/first user if system generated
                'status_sebelum' => null,
                'status_sesudah' => 'draft',
                'catatan_verifikasi' => 'Laporan stok dibuat (Draft)',
                'tanggal_verifikasi' => now(),
            ]);
        });
    }
}
