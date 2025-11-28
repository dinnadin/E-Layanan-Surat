<?php
namespace App\Imports;

use App\Models\DataCuti;
use App\Models\Pengguna;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class DataCutiImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    public function model(array $row)
    {
        $pengguna = Pengguna::where('nama_lengkap', trim($row['nama']))->first();

        if (!$pengguna) {
            // kalau nama tidak ditemukan di tabel pengguna → skip baris ini
            return null;
        }

        // Ambil nilai dari file (pastikan integer)
        $n_2 = (int) ($row['n_2'] ?? 0);
        $n_1 = (int) ($row['n_1'] ?? 0);
        $n   = (int) ($row['n'] ?? 0);
        $diambil = (int) ($row['diambil'] ?? 0);

        // 🔒 Batasi nilai n_1 maksimal 6
        if ($n_1 > 6) {
            $n_1 = 6;
        }

        // Hitung jumlah & sisa otomatis
        $jumlah = $n_2 + $n_1 + $n;
        $sisa   = $jumlah - $diambil;

        return new DataCuti([
            'id_pengguna' => $pengguna->id_pengguna,
            'n_2'    => $n_2,
            'n_1'    => $n_1,
            'n'      => $n,
            'jumlah' => $jumlah,
            'diambil'=> $diambil,
            'sisa'   => $sisa,
        ]);
    }
}
