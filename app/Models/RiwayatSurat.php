<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\pengajuancuti;


class RiwayatSurat extends Model
{
    use HasFactory;

    protected $table = 'riwayat_surat';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_pengguna',
        'id_surat_aktif',
        'id_surat_ijin',
        'id_cuti',
        'keterangan',
    ];

    public $timestamps = false;

    // Relasi ke pengguna
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    // Relasi ke surat aktif
    public function suratAktif()
    {
        return $this->belongsTo(SuratAktif::class, 'id_surat_aktif', 'id_surat');
    }

    // Relasi ke surat ijin
    public function suratIjin()
    {
        return $this->belongsTo(SuratIjin::class, 'id_surat_ijin', 'id_surat');
    }
    public function cuti()
{
    return $this->belongsTo(pengajuancuti::class, 'id_cuti', 'id_cuti');
}
}
