<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    protected $table = 'data_unit_kerja';
    protected $primaryKey = 'id_unit_kerja';
    public $timestamps = false;

    protected $fillable = [
        'nama_unit_kerja',
        'sub_unit_kerja',
    ];
}
