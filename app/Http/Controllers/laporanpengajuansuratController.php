<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PengajuanExport;
use Illuminate\Support\Facades\DB;

class LaporanPengajuanSuratController extends Controller
{
    public function index(Request $request)
    {
        $id_pengguna = session('id_pengguna');
        $role = strtolower(trim(session('role') ?? ''));

        if (!$id_pengguna) {
            return redirect()->route('login.form')->with('error', 'Silakan login dulu');
        }

        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');

        $pengajuan = DB::table('riwayat_surat')
            ->join('pengguna', 'riwayat_surat.id_pengguna', '=', 'pengguna.id_pengguna')
            ->leftJoin('surat_ijin', 'riwayat_surat.id_surat_ijin', '=', 'surat_ijin.id_surat')
            ->leftJoin('permintaan_surat_ijin', 'surat_ijin.id_permintaan', '=', 'permintaan_surat_ijin.id')
            ->leftJoin('surat_aktif', 'riwayat_surat.id_surat_aktif', '=', 'surat_aktif.id_surat')
            ->leftJoin('pengajuan_cuti', 'riwayat_surat.id_cuti', '=', 'pengajuan_cuti.id_cuti')
            ->select(
                'riwayat_surat.id',
                'riwayat_surat.keterangan',
                'pengguna.nama_lengkap',
                'pengguna.nip',
                DB::raw("COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan) as tanggal_pengajuan"),
                DB::raw("
                    CASE 
                        WHEN riwayat_surat.id_surat_ijin IS NOT NULL THEN 'Surat Ijin'
                        WHEN riwayat_surat.id_surat_aktif IS NOT NULL THEN 'Surat Aktif'
                        WHEN riwayat_surat.id_cuti IS NOT NULL THEN 'Cuti'
                        ELSE 'Tidak diketahui'
                    END as jenis_surat
                "),
                'permintaan_surat_ijin.jenis_alasan as jenis_alasan',
                'surat_aktif.nomor_surat as nomor_surat_aktif',
                'pengajuan_cuti.alasan as alasan_cuti'
            )
            // Filter untuk role PEGAWAI
            ->when($role === 'pegawai', function($q) use ($id_pengguna) {
                return $q->where('riwayat_surat.id_pengguna', $id_pengguna);
            })
            // ✅ Filter untuk role KEPALA (SUDAH DIPERBAIKI)
            ->when($role === 'kepala', function($q) use ($id_pengguna) {
                return $q->where(function($sub) use ($id_pengguna) {
                    // Surat izin & aktif → cek penandatangan_id
                    $sub->where('surat_ijin.penandatangan_id', $id_pengguna)
                        ->orWhere('surat_aktif.penandatangan_id', $id_pengguna)
                        
                        // ✅ Cuti: tampilkan yang dia ajukan ATAU yang butuh tanda tangan dia
                        ->orWhere('pengajuan_cuti.penandatangan_id', $id_pengguna)
                        ->orWhere('pengajuan_cuti.tandatangan_id', $id_pengguna);
                });
            })
            // Filter berdasarkan bulan dan tahun
            ->when($bulan && $tahun, function($q) use ($bulan, $tahun) {
                $q->whereMonth(DB::raw("COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)"), $bulan)
                  ->whereYear(DB::raw("COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)"), $tahun);
            })
            // Filter berdasarkan jenis surat
            ->when($request->jenis_surat, function($q) use ($request) {
                if ($request->jenis_surat === 'ijin') {
                    $q->whereNotNull('riwayat_surat.id_surat_ijin');
                } elseif ($request->jenis_surat === 'aktif') {
                    $q->whereNotNull('riwayat_surat.id_surat_aktif');
                } elseif ($request->jenis_surat === 'cuti') {
                    $q->whereNotNull('riwayat_surat.id_cuti');
                }
            })
            ->orderBy('riwayat_surat.id', 'desc')
            ->paginate(7)                      
            ->appends($request->except('page'));


        // Ambil daftar tahun untuk dropdown filter
        $tahunList = DB::table('riwayat_surat')
            ->leftJoin('surat_ijin', 'riwayat_surat.id_surat_ijin', '=', 'surat_ijin.id_surat')
            ->leftJoin('surat_aktif', 'riwayat_surat.id_surat_aktif', '=', 'surat_aktif.id_surat')
            ->leftJoin('pengajuan_cuti', 'riwayat_surat.id_cuti', '=', 'pengajuan_cuti.id_cuti')
            ->selectRaw('YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) as tahun')
            ->whereNotNull(DB::raw('COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)'))
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        // Jika tidak ada tahun, gunakan tahun sekarang
        if ($tahunList->isEmpty()) {
            $tahunList = collect([date('Y')]);
        }

        return view('laporanpengajuansurat', compact('pengajuan', 'bulan', 'tahun', 'tahunList'))
            ->with('jenisSurat', $request->jenis_surat);
    }

    public function exportExcel()
    {
        return Excel::download(new PengajuanExport, 'laporan_pengajuan.xlsx');
    }

    public function cetak(Request $request)
    {
        $id_pengguna = session('id_pengguna');
        $role = strtolower(trim(session('role') ?? ''));

        if (!$id_pengguna) {
            return redirect()->route('login.form')->with('error', 'Silakan login dulu');
        }

        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');

        $pengajuan = DB::table('riwayat_surat')
            ->join('pengguna', 'riwayat_surat.id_pengguna', '=', 'pengguna.id_pengguna')
            ->leftJoin('surat_ijin', 'riwayat_surat.id_surat_ijin', '=', 'surat_ijin.id_surat')
            ->leftJoin('permintaan_surat_ijin', 'surat_ijin.id_permintaan', '=', 'permintaan_surat_ijin.id')
            ->leftJoin('surat_aktif', 'riwayat_surat.id_surat_aktif', '=', 'surat_aktif.id_surat')
            ->leftJoin('pengajuan_cuti', 'riwayat_surat.id_cuti', '=', 'pengajuan_cuti.id_cuti')
            ->select(
                'riwayat_surat.id',
                'pengguna.nama_lengkap',
                'pengguna.nip',
                DB::raw("COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan) as tanggal_pengajuan"),
                DB::raw("
                    CASE 
                        WHEN riwayat_surat.id_surat_ijin IS NOT NULL THEN 'Surat Ijin'
                        WHEN riwayat_surat.id_surat_aktif IS NOT NULL THEN 'Surat Aktif'
                        WHEN riwayat_surat.id_cuti IS NOT NULL THEN 'Cuti'
                        ELSE 'Tidak diketahui'
                    END as jenis_surat
                "),
                'permintaan_surat_ijin.jenis_alasan as jenis_alasan',
                'surat_aktif.nomor_surat as nomor_surat_aktif',
                'pengajuan_cuti.alasan as alasan_cuti'
            )
            // ✅ Tambahkan filter role kepala di fungsi cetak juga
            ->when($role === 'kepala', function($q) use ($id_pengguna) {
                return $q->where(function($sub) use ($id_pengguna) {
                    $sub->where('surat_ijin.penandatangan_id', $id_pengguna)
                        ->orWhere('surat_aktif.penandatangan_id', $id_pengguna)
                        ->orWhere('pengajuan_cuti.penandatangan_id', $id_pengguna)
                        ->orWhere('pengajuan_cuti.tandatangan_id', $id_pengguna);
                });
            })
            ->when($role === 'pegawai', function($q) use ($id_pengguna) {
                return $q->where('riwayat_surat.id_pengguna', $id_pengguna);
            })
            ->when($bulan && $tahun, function($q) use ($bulan, $tahun) {
                $q->whereMonth(DB::raw("COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)"), $bulan)
                  ->whereYear(DB::raw("COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)"), $tahun);
            })
            ->when($request->jenis_surat, function($q) use ($request) {
                if ($request->jenis_surat === 'ijin') {
                    $q->whereNotNull('riwayat_surat.id_surat_ijin');
                } elseif ($request->jenis_surat === 'aktif') {
                    $q->whereNotNull('riwayat_surat.id_surat_aktif');
                } elseif ($request->jenis_surat === 'cuti') {
                    $q->whereNotNull('riwayat_surat.id_cuti');
                }
            })
            ->orderBy('riwayat_surat.id', 'desc')
            ->get();

        $periode = ($bulan && $tahun) 
            ? \Carbon\Carbon::create()->month((int)$bulan)->year((int)$tahun)->translatedFormat('F Y')
            : 'Semua Periode';

        $pdf = Pdf::loadView('pdf.laporanpengajuansurat', compact('pengajuan', 'periode'))
                ->setPaper('a4', 'portrait');

        return $pdf->download('laporan_pengajuan_surat.pdf');
    }
    // Tampilkan halaman rekap (view admin)
    public function rekapBulanan(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        // Select rekap per pegawai dengan agregasi per bulan
        $rows = DB::table('pengguna')
            ->leftJoin('riwayat_surat', 'pengguna.id_pengguna', '=', 'riwayat_surat.id_pengguna')
            ->leftJoin('surat_ijin', 'riwayat_surat.id_surat_ijin', '=', 'surat_ijin.id_surat')
            ->leftJoin('surat_aktif', 'riwayat_surat.id_surat_aktif', '=', 'surat_aktif.id_surat')
            ->leftJoin('pengajuan_cuti', 'riwayat_surat.id_cuti', '=', 'pengajuan_cuti.id_cuti')
            ->select(
                'pengguna.id_pengguna',
                'pengguna.nama_lengkap',
                'pengguna.nip',
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = 1 THEN 1 ELSE 0 END) as m1"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = 2 THEN 1 ELSE 0 END) as m2"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = 3 THEN 1 ELSE 0 END) as m3"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = 4 THEN 1 ELSE 0 END) as m4"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = 5 THEN 1 ELSE 0 END) as m5"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = 6 THEN 1 ELSE 0 END) as m6"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = 7 THEN 1 ELSE 0 END) as m7"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = 8 THEN 1 ELSE 0 END) as m8"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = 9 THEN 1 ELSE 0 END) as m9"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = 10 THEN 1 ELSE 0 END) as m10"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = 11 THEN 1 ELSE 0 END) as m11"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = 12 THEN 1 ELSE 0 END) as m12")
            )
            ->groupBy('pengguna.id_pengguna', 'pengguna.nama_lengkap', 'pengguna.nip')
            ->orderBy('pengguna.nama_lengkap')
            ->get();

        // Hitung total per baris
        $rekap = $rows->map(function($r){
            $months = [];
            for ($i=1;$i<=12;$i++){
                $months[$i] = (int) ($r->{'m'.$i} ?? 0);
            }
            $total = array_sum($months);
            return [
                'id' => $r->id_pengguna,
                'nama' => $r->nama_lengkap,
                'nip' => $r->nip,
                'bulan' => $months,
                'total' => $total
            ];
        });

        return view('admin.laporan.rekap', ['rekap' => $rekap, 'tahun' => $tahun]);
    }

    // Export Excel (menggunakan Maatwebsite)
    public function exportRekapExcel(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        return Excel::download(new RekapBulananExport($tahun), "rekap_pengajuan_{$tahun}.xlsx");
    }

    // Cetak PDF (landscape)
    public function cetakRekapPdf(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        // rebuild data (sama query seperti rekapBulanan)
        $rows = DB::table('pengguna')
            ->leftJoin('riwayat_surat', 'pengguna.id_pengguna', '=', 'riwayat_surat.id_pengguna')
            ->leftJoin('surat_ijin', 'riwayat_surat.id_surat_ijin', '=', 'surat_ijin.id_surat')
            ->leftJoin('surat_aktif', 'riwayat_surat.id_surat_aktif', '=', 'surat_aktif.id_surat')
            ->leftJoin('pengajuan_cuti', 'riwayat_surat.id_cuti', '=', 'pengajuan_cuti.id_cuti')
            ->select(
                'pengguna.id_pengguna',
                'pengguna.nama_lengkap',
                'pengguna.nip',
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = 1 THEN 1 ELSE 0 END) as m1"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(...)) = 2 THEN 1 ELSE 0 END) as m2"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(...)) = 3 THEN 1 ELSE 0 END) as m3"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(...)) = 4 THEN 1 ELSE 0 END) as m4"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(...)) = 5 THEN 1 ELSE 0 END) as m5"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(...)) = 6 THEN 1 ELSE 0 END) as m6"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(...)) = 7 THEN 1 ELSE 0 END) as m7"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(...)) = 8 THEN 1 ELSE 0 END) as m8"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(...)) = 9 THEN 1 ELSE 0 END) as m9"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(...)) = 10 THEN 1 ELSE 0 END) as m10"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(...)) = 11 THEN 1 ELSE 0 END) as m11"),
                DB::raw("SUM(CASE WHEN YEAR(COALESCE(surat_ijin.created_at, surat_aktif.created_at, pengajuan_cuti.tanggal_pengajuan)) = {$tahun} AND MONTH(COALESCE(...)) = 12 THEN 1 ELSE 0 END) as m12")
            )
            ->groupBy('pengguna.id_pengguna', 'pengguna.nama_lengkap', 'pengguna.nip')
            ->orderBy('pengguna.nama_lengkap')
            ->get();

        $rekap = $rows->map(function($r){
            $months = [];
            for ($i=1;$i<=12;$i++){
                $months[$i] = (int) ($r->{'m'.$i} ?? 0);
            }
            $total = array_sum($months);
            return [
                'nama' => $r->nama_lengkap,
                'nip' => $r->nip,
                'bulan' => $months,
                'total' => $total
            ];
        });

        $pdf = Pdf::loadView('admin.laporan.rekap-pdf', compact('rekap','tahun'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download("rekap_pengajuan_{$tahun}.pdf");
    }
}