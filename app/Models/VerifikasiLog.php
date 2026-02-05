<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifikasiLog extends Model
{
    use HasFactory;

    protected $table = 'verifikasi_log';

    protected $fillable = [
        'tipe_transaksi',
        'id_referensi',
        'id_user_verifikator',
        'status_sebelum',
        'status_sesudah',
        'catatan_verifikasi',
        'tanggal_verifikasi'
    ];

    protected $casts = [
        'tanggal_verifikasi' => 'datetime',
    ];

    public function userVerifikator()
    {
        return $this->belongsTo(User::class, 'id_user_verifikator');
    }

    // Dynamic relationship based on transaction type
    public function transaksi()
    {
        if ($this->tipe_transaksi === 'masuk') {
            return $this->belongsTo(TransaksiMasuk::class, 'id_referensi');
        } elseif ($this->tipe_transaksi === 'keluar') {
            return $this->belongsTo(TransaksiKeluar::class, 'id_referensi');
        } elseif ($this->tipe_transaksi === 'laporan') {
            return $this->belongsTo(LaporanStok::class, 'id_referensi');
        }
        return null;
    }
}
