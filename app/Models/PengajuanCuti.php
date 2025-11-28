<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanCuti extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_cuti';
    protected $primaryKey = 'id_cuti';

    protected $fillable = [
        'id_pengguna',
        'penandatangan_id',
        'tandatangan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'jenis_permohonan',
        'lama',
        'satuan_lama',
        'alamat_cuti',
        'tanggal_pengajuan',
    ];

    public function pengguna()
    {
    return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }
    public function tandatangan()
    {
        return $this->belongsTo(Pengguna::class, 'tandatangan_id', 'id_pengguna');
    }

    // Cari pengguna yang id_pimpinan-nya = penandatangan_id
    public function penandatangan()
    {
        return $this->belongsTo(Pengguna::class, 'penandatangan_id', 'id_pengguna');
    }
    public function dataCuti()
{
    return $this->hasOne(\App\Models\DataCuti::class, 'id_pengguna', 'id_pengguna');
}
}
