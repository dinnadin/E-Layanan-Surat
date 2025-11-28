<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratIjin extends Model
{
    protected $table = 'surat_ijin';
    protected $primaryKey = 'id_surat';

    protected $fillable = [
        'id_permintaan',
        'penandatangan_id',
        'penerima_id',
        'keterangan',
        'status',
        'file_surat',
    ];

    // relasi
    public function penandatangan()
    {
        return $this->belongsTo(Pengguna::class, 'penandatangan_id', 'id_pengguna');
    }

    public function penerima()
    {
        return $this->belongsTo(Pengguna::class, 'penerima_id', 'id_pengguna');
    }

   public function permintaan()
{
    return $this->belongsTo(PermintaanSuratIjin::class, 'id_permintaan', 'id');
}

public function riwayat()
{
    return $this->hasOne(RiwayatSurat::class, 'id_surat_ijin', 'id_surat');
}
// App\Models\SuratIjin.php
public function pengguna()
{
    return $this->belongsTo(Pengguna::class, 'id_pengguna');
}
}