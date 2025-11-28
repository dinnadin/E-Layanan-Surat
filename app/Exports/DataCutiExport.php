<?php

namespace App\Exports;

use App\Models\DataCuti;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DataCutiExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return DataCuti::with('pengguna')
            ->get()
            ->map(function ($cuti) {
                return [
                    'Nama'    => $cuti->pengguna->nama_lengkap ?? '-',
                    'N-2'     => $cuti->n_2,
                    'N-1'     => $cuti->n_1,
                    'N'       => $cuti->n,
                    'Jumlah'  => $cuti->jumlah,
                    'Diambil' => $cuti->diambil,
                    'Sisa'    => $cuti->sisa,
                ];
            });
    }

    public function headings(): array
    {
        return ['Nama', 'N-2', 'N-1', 'N', 'Jumlah', 'Diambil', 'Sisa'];
    }
}
