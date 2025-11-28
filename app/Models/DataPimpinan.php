<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPimpinan extends Model
{
    use HasFactory;

    protected $table = 'data_pimpinan';
    protected $primaryKey = 'id_pimpinan';
    
    protected $fillable = [
        'nama_pimpinan'
    ];

    /**
     * Relasi One to Many ke Pengguna
     * Satu pimpinan memiliki banyak pengguna/pegawai
     * 🔥 RELASI BARU
     */
    public function pengguna()
    {
        return $this->hasMany(Pengguna::class, 'id_pimpinan', 'id_pimpinan');
    }
}