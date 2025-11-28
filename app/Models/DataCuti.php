<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataCuti extends Model
{
    use HasFactory;

    protected $table = 'data_cuti';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_pengguna',
        'n_2',
        'n_1',
        'n',
        'jumlah',
        'diambil',
        'sisa'
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }
}
