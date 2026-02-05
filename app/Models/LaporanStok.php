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
        'status'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    public function verifikasiLogs()
    {
        return $this->hasMany(VerifikasiLog::class, 'id_referensi')->where('tipe_transaksi', 'laporan');
    }
}
