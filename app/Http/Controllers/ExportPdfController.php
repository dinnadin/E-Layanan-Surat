<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SuratIjin;
use App\Models\RiwayatSurat;
use Illuminate\Support\Facades\File;

class ExportPdfController extends Controller
{
    public function download($tipe, $id)
    {
        // 🔍 CEK DULU: Apakah ini ID dari riwayat?
        $riwayat = RiwayatSurat::with(['suratIjin', 'suratAktif', 'cuti'])->find($id);
        
        if ($riwayat) {
            // Deteksi jenis surat dari riwayat
            if ($riwayat->id_surat_ijin) {
                $id = $riwayat->id_surat_ijin;
                $tipe = 'ijin';
                // Lanjut ke logic surat ijin di bawah
            } elseif ($riwayat->id_surat_aktif) {
                return $this->downloadSuratAktif($riwayat->id_surat_aktif);
            } elseif ($riwayat->id_cuti) {
                return $this->downloadSuratCuti($riwayat->id_cuti);
            }
        }

        if ($tipe !== 'ijin') {
            abort(404, 'Tipe surat tidak ditemukan');
        }

        // 🔍 Cek apakah $id adalah ID riwayat atau ID surat_ijin
        $surat = null;
        
        // Coba cari sebagai ID surat_ijin langsung
        $surat = SuratIjin::with([
            'permintaan.pengguna.jabatan', 
            'penandatangan',
            'penandatangan.pimpinan'
        ])->find($id);
        
        // Jika tidak ketemu, coba cari lewat riwayat
        if (!$surat) {
            $riwayat = RiwayatSurat::find($id);
            
            if ($riwayat && $riwayat->id_surat_ijin) {
                $surat = SuratIjin::with([
                    'permintaan.pengguna.jabatan', 
                    'penandatangan',
                    'penandatangan.pimpinan'
                ])->find($riwayat->id_surat_ijin);
            }
        }
        
        // Jika masih tidak ketemu, throw 404
        if (!$surat) {
            abort(404, 'Surat tidak ditemukan');
        }

        // 🔥 Proses TTD - Copy ke folder public agar DomPDF bisa akses
        $surat->penandatangan->ttd_path = null;
        
        if ($surat->penandatangan) {
            // Ambil path TTD
            $ttdPath = $surat->penandatangan->tanda_tangan;
            
            if (empty($ttdPath)) {
                \Log::warning("❌ TTD tidak ada di penandatangan", [
                    'penandatangan_id' => $surat->penandatangan->id_pengguna ?? null
                ]);
            }

           if (!empty($ttdPath)) {
    // Cek apakah path sudah dalam format 'storage/...' atau 'tanda_tangan/...'
    if (strpos($ttdPath, 'storage/') === 0) {
        // Jika path = 'storage/tanda_tangan/xxx.png'
        // Maka file ada di public/storage/tanda_tangan/xxx.png
        $sourcePath = public_path($ttdPath);
    } else {
        // Jika path = 'tanda_tangan/xxx.png' 
        // Maka file ada di storage/app/public/tanda_tangan/xxx.png
        $sourcePath = storage_path('app/public/' . $ttdPath);
    }
    
    if (file_exists($sourcePath)) {
                    $tempDir = public_path('temp_ttd');
                    if (!File::exists($tempDir)) {
                        File::makeDirectory($tempDir, 0755, true);
                    }

                    // Copy file TTD ke folder public
                    $filename = 'ttd_' . $surat->id_surat . '_' . time() . '.png';
                    $tempPath = $tempDir . '/' . $filename;
                    File::copy($sourcePath, $tempPath);
                    
                    // Simpan path absolut untuk DomPDF
                    $surat->penandatangan->ttd_path = $tempPath;
                    
                    \Log::info("✅ TTD berhasil di-copy", [
                        'source' => $sourcePath,
                        'destination' => $tempPath
                    ]);
                } else {
                    \Log::warning("❌ File TTD tidak ditemukan", [
                        'path' => $sourcePath
                    ]);
                }
            }
        }

        // Generate PDF
        $pdf = Pdf::setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => public_path(),
            ])
            ->loadView('pdf.surat_ijin', compact('surat'))
            ->setPaper('A4', 'portrait');

        $output = $pdf->output();

        // Hapus file temporary setelah PDF di-generate
        if (isset($surat->penandatangan->ttd_path) && file_exists($surat->penandatangan->ttd_path)) {
            @unlink($surat->penandatangan->ttd_path);
            \Log::info("✅ File temporary TTD dihapus");
        }

        // Return PDF
        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="surat-ijin-' . $surat->id_surat . '.pdf"'
        ]);
    }

    // ✅ TAMBAHAN: Method untuk Surat Aktif
    private function downloadSuratAktif($id)
{
    $surat = \App\Models\SuratAktif::with(['permintaan.pengguna', 'penandatangan', 'penandatangan.pimpinan'])->findOrFail($id);

    $kepalaUmum = \DB::table('pengguna')
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

    // 🔥 Proses TTD
    if ($surat->penandatangan) {
        $surat->penandatangan->ttd_path = null;
        $ttdPath = $surat->penandatangan->tanda_tangan;
        
        if (!empty($ttdPath)) {
            if (strpos($ttdPath, 'storage/') === 0) {
                $sourcePath = public_path($ttdPath);
            } else {
                $sourcePath = storage_path('app/public/' . $ttdPath);
            }
            
            if (file_exists($sourcePath)) {
                $tempDir = public_path('temp_ttd');
                if (!File::exists($tempDir)) {
                    File::makeDirectory($tempDir, 0755, true);
                }
                
                $filename = 'ttd_aktif_' . $surat->id_surat . '_' . time() . '.png';
                $tempPath = $tempDir . '/' . $filename;
                File::copy($sourcePath, $tempPath);
                
                $surat->penandatangan->ttd_path = $tempPath;
                \Log::info("✅ TTD Surat Aktif berhasil di-copy", ['source' => $sourcePath, 'destination' => $tempPath]);
            } else {
                \Log::warning("❌ File TTD Surat Aktif tidak ditemukan", ['path' => $sourcePath]);
            }
        }
    }

    $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => public_path(),
        ])
        ->loadView('pdf.surat_aktif', compact('surat', 'kepalaUmum'))
        ->setPaper('A4', 'portrait');

    $output = $pdf->output();

    // Hapus file temporary
    if (isset($surat->penandatangan->ttd_path) && file_exists($surat->penandatangan->ttd_path)) {
        @unlink($surat->penandatangan->ttd_path);
        \Log::info("✅ File temporary TTD Surat Aktif dihapus");
    }

    return response($output, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="surat-aktif-' . $surat->id_surat . '.pdf"'
    ]);
}

    // ✅ TAMBAHAN: Method untuk Surat Cuti
    private function downloadSuratCuti($id)
    {
        $surat = \App\Models\PengajuanCuti::with(['pengguna'])->findOrFail($id);

        $kepalaUmum = \DB::table('pengguna')
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

        $dataCuti = \App\Models\DataCuti::where('id_pengguna', $surat->id_pengguna)->first();

        $pdf = Pdf::loadView('pdf.permintaan_cuti', compact('surat', 'kepalaUmum', 'dataCuti'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream('surat-cuti-' . $surat->id_cuti . '.pdf');
    }
}