<?php

namespace App\Exports;

use App\Models\RiwayatSurat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Facades\DB;

class PengajuanExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    public function collection()
    {
        return DB::table('riwayat_surat')
            ->join('pengguna', 'riwayat_surat.id_pengguna', '=', 'pengguna.id_pengguna')
            ->leftJoin('surat_ijin', 'riwayat_surat.id_surat_ijin', '=', 'surat_ijin.id_surat')
            ->leftJoin('surat_aktif', 'riwayat_surat.id_surat_aktif', '=', 'surat_aktif.id_surat')
            ->leftJoin('pengajuan_cuti', 'riwayat_surat.id_cuti', '=', 'pengajuan_cuti.id_cuti')
            ->select(
                'pengguna.nama_lengkap as nama_pengguna',
                'pengguna.nip as nip',
                DB::raw("
                    CASE 
                        WHEN riwayat_surat.id_surat_ijin IS NOT NULL THEN 'Surat Ijin'
                        WHEN riwayat_surat.id_surat_aktif IS NOT NULL THEN 'Surat Aktif'
                        WHEN riwayat_surat.id_cuti IS NOT NULL THEN 'Cuti'
                        ELSE 'Tidak diketahui'
                    END as jenis_surat
                "),
                DB::raw("COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan) as tanggal_pengajuan")
            )
            ->orderBy('riwayat_surat.id', 'desc')
            ->get();
    }

  public function map($row): array
{
    return [
        $row->nama_pengguna,
        '="' . $row->nip . '"',   // ← Excel wajib simpan sebagai TEXT
        $row->jenis_surat,
        $row->tanggal_pengajuan,
    ];
}
    public function headings(): array
    {
        return [
            'Nama Pengguna',
            'NIP',
            'Jenis Surat',
            'Tanggal Pengajuan',
        ];
    }

    // FORMAT NIP sebagai TEXT
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
