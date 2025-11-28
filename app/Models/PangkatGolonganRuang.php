<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PangkatGolonganRuang extends Model
{
    // Nama tabel sesuai migration yang kamu pakai
    protected $table = 'data_pangkat';

    // Primary key sesuai migration
    protected $primaryKey = 'id_pangkat';

    // Jika PK adalah integer auto-increment default sudah true
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'pangkat',
        'golongan',
        'ruang',
    ];

    public function pengguna()
    {
        return $this->hasMany(Pengguna::class, 'id_pangkat_golongan_ruang', 'id_pangkat');
    }
}
