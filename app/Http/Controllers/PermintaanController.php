<?php

namespace App\Http\Controllers;
use App\Models\PermintaanSurat;
use App\Models\RiwayatSurat;
use App\Models\SuratAktif;
use Illuminate\Http\Request;

class PermintaanController extends Controller
{
    public function index()
    {
        $surat = SuratAktif::with(['penerima', 'penandatangan'])->latest()->get();
        return view('surataktif.index', compact('surat'));
    }

    public function show($id)
    {
        $surat = SuratAktif::with(['penerima', 'penandatangan'])->findOrFail($id);
        return view('surataktif.show', compact('surat'));
    }

    public function updateStatus($id, $status)
    {
        $permintaan = PermintaanSurat::with('pegawai')->findOrFail($id);
        $permintaan->status = $status;
        $permintaan->save();

        if ($status == 'Disetujui') {
            // generate nomor surat
            $last = SuratAktif::latest('id_surat')->first();
            $nomorUrut = $last ? $last->id_surat + 1 : 1;
            $nomorSurat = str_pad($nomorUrut, 3, '0', STR_PAD_LEFT) . "/SA/" . date('Y');

            $surat = SuratAktif::create([
                'nomor_surat'     => $nomorSurat,
                'tanggal_terbit'  => now(),
                'penandatangan_id'=> null, // admin bisa isi belakangan
                'penerima_id'     => $permintaan->pegawai_id,
                'keterangan'      => 'Surat aktif untuk ' . $permintaan->pegawai->nama,
                'status'          => 'Aktif',
            ]);

            // catat riwayat
            RiwayatSurat::create([
                'permintaan_id' => $permintaan->id,
                'surat_id'      => $surat->id_surat,
                'aksi'          => 'Disetujui',
                'keterangan'    => 'Permintaan disetujui & surat aktif dibuat'
            ]);

        } elseif ($status == 'Ditolak') {
            RiwayatSurat::create([
                'permintaan_id' => $permintaan->id,
                'aksi'          => 'Ditolak',
                'keterangan'    => 'Permintaan surat ditolak oleh admin'
            ]);
        }

        return redirect()->route('admin.permintaan.index')->with('success', 'Status berhasil diperbarui!');
    }
}
