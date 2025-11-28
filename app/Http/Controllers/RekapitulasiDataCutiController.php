<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Models\DataCuti;
use App\Exports\RekapitulasiCutiExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class RekapitulasiDataCutiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');

        // ✅ PERBAIKAN: Ambil tahun dari tabel pengajuan_cuti, bukan data_cuti
        $tahunList = \App\Models\PengajuanCuti::selectRaw('YEAR(tanggal_mulai) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        // ✅ Query dari tabel pengajuan_cuti (yang punya tanggal_mulai)
        $query = \App\Models\PengajuanCuti::with('pengguna');

        // ✅ Filter berdasarkan search (Nama atau NIP)
        if ($search) {
            $query->whereHas('pengguna', function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan bulan
        if ($bulan) {
            $query->whereMonth('tanggal_mulai', $bulan);
        }

        // Filter berdasarkan tahun
        if ($tahun) {
            $query->whereYear('tanggal_mulai', $tahun);
        }

        // Paginate hasil
        $cuti = $query->orderBy('tanggal_mulai', 'desc')
                    ->paginate(10)
                    ->appends($request->except('page'));

        // ✅ AJAX Request - Return hanya content untuk live search
        if ($request->ajax()) {
            return view('rekapitulasi_cuti.index', compact('cuti', 'tahunList'))->render();
        }

        return view('rekapitulasi_cuti.index', compact('cuti', 'tahunList'));
    }

   public function batalkan($id_cuti)
{
    // 🔍 Ambil data cuti
    $pengajuan = PengajuanCuti::findOrFail($id_cuti);

    // Normalisasi nama jenis cuti
    $jenisCuti = strtolower(preg_replace('/\s+/', ' ', trim($pengajuan->jenis_permohonan)));

    // ❗ Jika BUKAN cuti tahunan → jangan ubah quota N
    if ($jenisCuti !== 'cuti tahunan') {
        $pengajuan->delete();
        return redirect()->route('rekapitulasi_cuti.index')
            ->with('success', 'Pengajuan cuti berhasil dibatalkan (kuota tidak berubah).');
    }

    // === khusus cuti tahunan ===
    $dataCuti = DataCuti::where('id_pengguna', $pengajuan->id_pengguna)->first();

    if (!$dataCuti) {
        throw new \Exception('Data cuti tidak ditemukan');
    }

    $lama = (int) $pengajuan->lama;

    DB::transaction(function () use ($pengajuan, $dataCuti, $lama) {

        // Kembalikan lama cuti ke slot N
        $dataCuti->n = ($dataCuti->n ?? 0) + $lama;

        // Hitung ulang
        $dataCuti->jumlah = ($dataCuti->n ?? 0) + ($dataCuti->n_1 ?? 0) + ($dataCuti->n_2 ?? 0);
        $dataCuti->diambil = max(0, ($dataCuti->diambil ?? 0) - $lama);
        $dataCuti->sisa = max(0, $dataCuti->jumlah - $dataCuti->diambil);

        $dataCuti->save();

        $pengajuan->delete();
    });

    return redirect()->route('rekapitulasi_cuti.index')
        ->with('success', 'Pengajuan cuti berhasil dibatalkan dan kuota tahunan dikembalikan.');
}

    public function export()
    {
        return Excel::download(new RekapitulasiCutiExport, 'rekapitulasi_cuti.xlsx');
    }
}