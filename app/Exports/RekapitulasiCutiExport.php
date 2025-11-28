<?php

namespace App\Exports;

use App\Models\PengajuanCuti;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class RekapitulasiCutiExport implements FromCollection, WithHeadings
{

public function collection()
{
    return PengajuanCuti::with('pengguna')->get()->map(function ($c) {
        return [
            'Nama' => $c->pengguna->nama_lengkap ?? '-',
            'NIP' => $c->pengguna->nip ?? '-',
            'Jenis Cuti' => $c->jenis_permohonan ?? '-',
            'Tanggal Mulai' => $c->tanggal_mulai ? Carbon::parse($c->tanggal_mulai)->translatedFormat('d F Y') : '-',
            'Tanggal Selesai' => $c->tanggal_selesai ? Carbon::parse($c->tanggal_selesai)->translatedFormat('d F Y') : '-',
            'Lama (Hari)' => $c->lama ?? '-',
        ];
    });
}

    public function headings(): array
    {
        return ['Nama', 'NIP', 'Jenis Cuti', 'Tanggal Mulai', 'Tanggal Selesai', 'Lama (Hari)'];
    }
}
