<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Models\DataCuti;
use Illuminate\Support\Facades\Auth;

class DetailCutiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // ✅ Ambil daftar tahun unik dari data cuti user (terurut dari terbaru)
        $daftarTahun = PengajuanCuti::where('id_pengguna', $user->id_pengguna)
            ->selectRaw('YEAR(tanggal_mulai) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        // Ambil input bulan dan tahun dari form filter
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');

        // Query dasar: hanya data milik user
        $query = PengajuanCuti::where('id_pengguna', $user->id_pengguna);

        // Jika ada filter bulan/tahun, tambahkan ke query
        if ($bulan) {
            $query->whereMonth('tanggal_mulai', $bulan);
        }
        if ($tahun) {
            $query->whereYear('tanggal_mulai', $tahun);
        }

        $cuti = $query->orderBy('tanggal_pengajuan', 'desc')->paginate(5)->appends($request->except('page'));


        // Ambil saldo cuti dari tabel data_cuti
        $dataCuti = DataCuti::where('id_pengguna', $user->id_pengguna)->first();

        $n2 = $n1 = $n = $jumlah = $diambil = $sisa = 0;
        if ($dataCuti) {
            $n2 = $dataCuti->n_2 ?? 0;
            $n1 = $dataCuti->n_1 ?? 0;
            $n  = $dataCuti->n ?? 0;
            $jumlah = $dataCuti->jumlah ?? ($n2 + $n1 + $n);
            $diambil = $dataCuti->diambil ?? 0;
            $sisa = $dataCuti->sisa ?? ($jumlah - $diambil);
        }

        return view('detail_cuti.index', compact(
            'cuti', 
            'dataCuti', 
            'n2', 
            'n1', 
            'n', 
            'jumlah', 
            'diambil', 
            'sisa', 
            'bulan', 
            'tahun',
            'daftarTahun' // ✅ TAMBAHAN: kirim daftar tahun ke view
        ));
    }
}