<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataLibur extends Model
{
    use HasFactory;

    protected $table = 'data_libur'; // nama tabel sesuai di database

    protected $primaryKey = 'id_tanggal'; // primary key

    public $timestamps = false; // karena tabel tidak punya created_at / updated_at

    protected $fillable = [
        'tanggal',
        'deskripsi',
    ];
}
