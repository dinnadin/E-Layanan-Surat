<?php

namespace App\Http\Controllers;

use App\Models\RiwayatSurat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiwayatSuratController extends Controller
{
    public function index(Request $request)
    {
        $idPengguna = session('id_pengguna');

        // 🔹 Ambil daftar tahun dari tiga tabel
        $tahunList = DB::table('pengajuan_cuti')->selectRaw('YEAR(created_at) as tahun')
            ->union(DB::table('surat_ijin')->selectRaw('YEAR(created_at) as tahun'))
            ->union(DB::table('surat_aktif')->selectRaw('YEAR(created_at) as tahun'))
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        $jenisSurat = $request->input('jenis_surat');

        // 🔹 Query utama
        $query = DB::table('riwayat_surat as rs')
            ->leftJoin('surat_aktif as sa', 'sa.id_surat', '=', 'rs.id_surat_aktif')
            ->leftJoin('permintaan_surat as psa', 'psa.id_permintaan', '=', 'sa.id_permintaan')
            ->leftJoin('surat_ijin as si', 'si.id_surat', '=', 'rs.id_surat_ijin')
            ->leftJoin('permintaan_surat_ijin as psi', 'psi.id', '=', 'si.id_permintaan')
            ->leftJoin('pengajuan_cuti as pc', 'pc.id_cuti', '=', 'rs.id_cuti')
            ->where('rs.id_pengguna', $idPengguna)
            ->select(
                'rs.id',
                'rs.keterangan',
                'rs.id_surat_ijin',
                'rs.id_surat_aktif',
                'rs.id_cuti',
                'psi.status as status_ijin',
                'psi.alasan_penolakan as alasan_penolakan_ijin',
                'psa.status as status_aktif',
                'pc.id_cuti as status_cuti',
                'si.keterangan as keterangan_ijin',
                DB::raw("
                    COALESCE(
                        si.created_at,
                        sa.created_at,
                        pc.tanggal_pengajuan
                    ) as tanggal_pengajuan
                "),
                DB::raw("
                    CASE 
                        WHEN rs.id_surat_aktif IS NOT NULL THEN sa.updated_at
                        WHEN rs.id_surat_ijin IS NOT NULL THEN si.updated_at
                        ELSE NULL
                    END as tanggal_disetujui
                "),
                DB::raw("
                    CASE 
                        WHEN rs.id_surat_ijin IS NOT NULL THEN 'Surat Ijin'
                        WHEN rs.id_surat_aktif IS NOT NULL THEN 'Surat Aktif'
                        WHEN rs.id_cuti IS NOT NULL THEN 'Surat Cuti'
                        ELSE '-' 
                    END as jenis_surat
                "),
                DB::raw("sa.nomor_surat as nomor_surat")
            )
            ->orderByDesc('rs.id');

        // 🔹 Filter bulan - DIPERBAIKI
        if (!empty($bulan)) {
            $query->where(function($q) use ($bulan) {
                $q->whereMonth('si.created_at', $bulan)
                  ->orWhereMonth('sa.created_at', $bulan)
                  ->orWhereMonth('pc.tanggal_pengajuan', $bulan);
            });
        }

        // 🔹 Filter tahun - DIPERBAIKI
        if (!empty($tahun)) {
            $query->where(function($q) use ($tahun) {
                $q->whereYear('si.created_at', $tahun)
                  ->orWhereYear('sa.created_at', $tahun)
                  ->orWhereYear('pc.tanggal_pengajuan', $tahun);
            });
        }

        // 🔹 Filter jenis surat
        if (!empty($jenisSurat)) {
            if ($jenisSurat === 'ijin') {
                $query->whereNotNull('rs.id_surat_ijin');
            } elseif ($jenisSurat === 'aktif') {
                $query->whereNotNull('rs.id_surat_aktif');
            } elseif ($jenisSurat === 'cuti') {
                $query->whereNotNull('rs.id_cuti');
            }
        }

        // ✅ Filter status surat
        if ($request->filled('status')) {
            if ($request->status === 'disetujui') {
                $query->where(function ($q) {
                    $q->whereNotNull('sa.nomor_surat')
                      ->orWhereNotNull('sa.updated_at')
                      ->orWhereNotNull('si.updated_at');
                });
            } elseif ($request->status === 'ditolak') {
                $query->where(function ($q) {
                    $q->where('rs.keterangan', 'like', '%ditolak%')
                      ->orWhere('psi.status', 'ditolak')
                      ->orWhere('psa.status', 'ditolak');
                });
            }
        }

        // 🔹 Pastikan surat yang ditolak tetap muncul (dari keterangan)
        $query->where(function ($q) {
            $q->whereNotNull('rs.id_surat_ijin')
              ->orWhereNotNull('rs.id_surat_aktif')
              ->orWhereNotNull('rs.id_cuti')
              ->orWhere('rs.keterangan', 'like', '%ditolak%')
              ->orWhere('rs.keterangan', 'like', '%DITOLAK%');
        });

$data = $query->paginate(5)->appends($request->except('page'));

        // 🔹 Tambahkan parsing untuk menampilkan tanggal & jenis surat pada keterangan "ditolak"
        foreach ($data as $item) {
            if ($item->jenis_surat === '-' && stripos($item->keterangan, 'Surat') !== false) {
                if (preg_match('/Surat\s([A-Za-z]+)/', $item->keterangan, $match)) {
                    $item->jenis_surat = 'Surat ' . ucfirst(strtolower($match[1]));
                }
            }

            if (preg_match('/\d{2}-\d{2}-\d{4}/', $item->keterangan, $match)) {
                $item->tanggal_pengajuan = $match[0];
            }
        }

        return view('riwayat', compact('data', 'bulan', 'tahun', 'tahunList', 'jenisSurat'));
    }

    // DOWNLOAD PDF semua riwayat
    public function downloadPdf()
    {
        $id_pengguna = session('id_pengguna');
        $role = strtolower(trim(session('role') ?? ''));

        if (!$id_pengguna) {
            return redirect()->route('login.form')->with('error', 'Silakan login dulu');
        }

        $data = RiwayatSurat::with(['suratIjin', 'suratAktif', 'cuti'])
            ->when($role !== 'admin', fn($q) => $q->where('id_pengguna', $id_pengguna))
            ->orderBy('id', 'desc')
            ->get();

        $pdf = Pdf::loadView('riwayatsurat.pdf', compact('data'));
        return $pdf->download('riwayat_surat.pdf');
    }

    // TAMPILKAN SATU SURAT DALAM BENTUK PDF
    public function showPdf($id)
    {
        $id_pengguna = session('id_pengguna');
        $role = strtolower(trim(session('role') ?? ''));

        if (!$id_pengguna) {
            return redirect()->route('login.form')->with('error', 'Silakan login dulu');
        }

        $riwayat = RiwayatSurat::with(['suratIjin', 'suratAktif', 'cuti'])
            ->when($role !== 'admin', fn($q) => $q->where('id_pengguna', $id_pengguna))
            ->findOrFail($id);

        $surat = $riwayat->suratIjin ?? $riwayat->suratAktif ?? $riwayat->cuti;

        $kepalaUmum = DB::table('pengguna')
            ->join('data_jabatan', 'pengguna.id_jabatan', '=', 'data_jabatan.id_jabatan')
            ->leftJoin('data_pangkat', 'pengguna.id_pangkat_golongan_ruang', '=', 'data_pangkat.id_pangkat')
            ->leftJoin('data_unit_kerja', 'pengguna.id_unit_kerja', '=', 'data_unit_kerja.id_unit_kerja')
            ->where('data_jabatan.nama_jabatan', 'like', '%kepala%')
            ->select(
                'pengguna.nama_lengkap',
                'pengguna.nip',
                'data_jabatan.nama_jabatan as jabatan',
                'data_pangkat.pangkat',
                'data_pangkat.golongan',
                'data_pangkat.ruang',
                'data_unit_kerja.nama_unit_kerja',
                'data_unit_kerja.sub_unit_kerja'
            )
            ->first();

        if ($riwayat->cuti) {
            $dataCuti = \App\Models\DataCuti::where('id_pengguna', $riwayat->id_pengguna)->first();
            $view = 'pdf.permintaan_cuti';
            $pdf = \PDF::loadView($view, compact('surat', 'kepalaUmum', 'dataCuti'))->setPaper('a4', 'portrait');
        } elseif ($riwayat->suratIjin) {
            $view = 'pdf.surat_ijin';
            $pdf = \PDF::loadView($view, compact('surat', 'kepalaUmum'))->setPaper('a4', 'portrait');
        } else {
            $view = 'pdf.surat_aktif';
            $pdf = \PDF::loadView($view, compact('surat', 'kepalaUmum'))->setPaper('a4', 'portrait');
        }

        return $pdf->stream('surat_' . $riwayat->id . '.pdf');
    }
}