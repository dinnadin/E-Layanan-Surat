<?php

namespace App\Exports;

use App\Models\Pengguna;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class PenggunaExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * Ambil semua data pengguna dengan relasi
     */
    public function collection()
    {
        return Pengguna::with(['jabatan', 'pangkatGolongan', 'unitKerja', 'pimpinan'])->get();
    }

    /**
     * Header kolom Excel
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'NIP',
            'Tanggal Lahir',
            'Umur',
            'Jabatan',
            'Pangkat Golongan Ruang',
            'Pimpinan',
            'Unit Kerja',
            'Masa Kerja',
            'Role',
            'Tanggal Masuk',
        ];
    }

    /**
     * Mapping setiap baris data
     */
    public function map($row): array
    {
        static $no = 1;

        // Gabungkan pangkat + golongan + ruang
        $pangkatGol = '-';
        if ($row->pangkatGolongan) {
            $parts = [];
            if (!empty($row->pangkatGolongan->pangkat)) $parts[] = $row->pangkatGolongan->pangkat;
            if (!empty($row->pangkatGolongan->golongan)) $parts[] = $row->pangkatGolongan->golongan;
            if (!empty($row->pangkatGolongan->ruang)) $parts[] = $row->pangkatGolongan->ruang;

            if (!empty($parts)) {
                $pangkatGol = $parts[0];
                if (count($parts) > 1) {
                    $pangkatGol .= ' (' . implode('/', array_slice($parts, 1)) . ')';
                }
            }
        }

        // Gabungkan unit kerja + sub unit jika ada
        $unitKerja = '-';
        if ($row->unitKerja) {
            $unitKerja = $row->unitKerja->nama_unit_kerja ?? '-';
            if (!empty($row->unitKerja->sub_unit_kerja)) {
                $unitKerja .= ' (' . $row->unitKerja->sub_unit_kerja . ')';
            }
        }

        // Format tanggal masuk ke "07 Agustus 2020"
        $tanggalMasuk = '-';
        if (!empty($row->tanggal_masuk)) {
            $tanggalMasuk = Carbon::parse($row->tanggal_masuk)->translatedFormat('d F Y');
        }

        // Format tanggal lahir
        $tanggalLahir = '-';
        if (!empty($row->tanggal_lahir)) {
            $tanggalLahir = Carbon::parse($row->tanggal_lahir)->translatedFormat('d F Y');
        }

        // 🔥 Hitung umur DARI TANGGAL LAHIR SAMPAI SEKARANG
        $umur = '-';
        if (!empty($row->tanggal_lahir)) {
            $tglLahir = Carbon::parse($row->tanggal_lahir);
            $sekarang = Carbon::now(); // 🔥 Menggunakan now() untuk waktu saat ini
            $diff = $tglLahir->diff($sekarang);
            $umur = $diff->y . ' tahun';
        }

        // 🔥 Hitung masa kerja DARI TANGGAL MASUK SAMPAI SEKARANG
        $masaKerja = '-';
        if (!empty($row->tanggal_masuk)) {
            $tglMasuk = Carbon::parse($row->tanggal_masuk);
            $sekarang = Carbon::now(); // 🔥 Menggunakan now() untuk waktu saat ini
            $diff = $tglMasuk->diff($sekarang);
            $masaKerja = $diff->y . ' tahun, ' . $diff->m . ' bulan, ' . $diff->d . ' hari';
        }

        // Nama Lengkap (tanpa username)
        $namaLengkap = $row->nama_lengkap ?? '-';

        // NIP (tanpa password, dengan tanda petik agar nol depan tidak hilang)
        $nip = '-';
        if (!empty($row->nip)) {
            $nip = "'" . $row->nip;
        }

        // Pimpinan - ambil dari relasi
        $pimpinan = $row->pimpinan->nama_pimpinan ?? '-';

        return [
            $no++,
            $namaLengkap,
            $nip,
            $tanggalLahir,
            $umur,
            $row->jabatan->nama_jabatan ?? '-',
            $pangkatGol,
            $pimpinan,
            $unitKerja,
            $masaKerja,
            $row->role ?? '-',
            $tanggalMasuk,
        ];
    }

    /**
     * Styling Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // header tebal
        ];
    }
}