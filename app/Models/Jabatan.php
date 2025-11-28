<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $table = 'data_jabatan';
    
    // ✅ TAMBAHKAN INI - beritahu Laravel primary key-nya adalah id_jabatan
    protected $primaryKey = 'id_jabatan';

    protected $fillable = [
        'nama_jabatan',
        'usia_pensiun',
    ];

    // Relasi ke pengguna
    public function pengguna()
    {
        return $this->hasMany(Pengguna::class, 'jabatan_id', 'id_jabatan');
    }
}