<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanSurat extends Model
{
    use HasFactory;

    protected $table = 'permintaan_surat';
    protected $primaryKey = 'id_permintaan';

    protected $fillable = [
        'id_pengguna',
        'penandatangan_id',
        'nama',
        'nip',
        'pangkat_golongan_ruang',
        'jabatan',
        'status',
        'tanggal_pengajuan',
    ];
        public $timestamps = false; // nonaktifkan timestamps Laravel


    // kasih tau Laravel kalau created_at pakai 'tanggal_pengajuan'
    const CREATED_AT = 'tanggal_pengajuan';
    const UPDATED_AT = 'updated_at';

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

   public function penandatangan()
{
    return $this->belongsTo(Pengguna::class, 'penandatangan_id');
}
public function pimpinan()
{
    return $this->belongsTo(DataPimpinan::class, 'id_pimpinan', 'id_pimpinan');
}
}
