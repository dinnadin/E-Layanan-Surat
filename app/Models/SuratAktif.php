<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratAktif extends Model
{
    use HasFactory;

    protected $table = 'surat_aktif';
    protected $primaryKey = 'id_surat';

    protected $fillable = [
        'id_permintaan',
        'nomor_surat',
        'tanggal_terbit',
        'penandatangan_id',
        'penerima_id',
        'keterangan',
        'status',
        'file_surat',
    ];

    public $timestamps = true;

    public function penerima()
    {
        return $this->belongsTo(Pengguna::class, 'penerima_id', 'id_pengguna');
    }

    public function penandatangan()
    {
        return $this->belongsTo(Pengguna::class, 'penandatangan_id', 'id_pengguna');
    }
    public function permintaan()
{
    return $this->belongsTo(PermintaanSurat::class, 'id_permintaan', 'id_permintaan');
}

}
