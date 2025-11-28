<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanSuratIjin extends Model
{
    protected $table = 'permintaan_surat_ijin';
    protected $primaryKey = 'id'; // PK sebenarnya id
    public $timestamps = false;

    protected $fillable = [
        'id_pengguna','nip','nama_lengkap','jabatan','unit_kerja',
        'penandatangan_id',
        'mulai_tanggal','mulai_jam','selesai_jam','jenis_alasan','deskripsi_alasan','status','alasan_penolakan'
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(pengguna::class,'id_pengguna','id_pengguna');
    }

    public function penandatangan(): BelongsTo
    {
        return $this->belongsTo(pengguna::class,'penandatangan_id','id_pengguna');
    }

    public function suratIjin()
{
    return $this->hasOne(SuratIjin::class, 'id_permintaan', 'id');
}
public function jabatan()
{
    return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
}
public function unitKerja()
{
    return $this->belongsTo(UnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
}
public function pimpinan()
{
    return $this->belongsTo(DataPimpinan::class, 'id_pimpinan', 'id_pimpinan');
}
}

