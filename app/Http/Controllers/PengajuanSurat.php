<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengajuanSurat extends Controller
{
    // Halaman pengajuan surat
    public function pengajuanSurat()
    {
        if (!session('pengguna') || session('pengguna')->role !== 'pegawai') {
            return redirect()->route('login.form');
        }

        // Ambil data pengguna dari session
        $pengguna = session('pengguna');

        return view('pengajuansurat', compact('pegawai'));
    }

    // Proses simpan pengajuan
    public function store(Request $request)
    {
        if (!session('pengguna') || session('pengguna')->role !== 'pegawai') {
            return redirect()->route('login.form');
        }

        $pengguna = session('pengguna'); // ambil dari session

        $request->validate([
            'jenis_surat' => 'required|string|max:100',
            'keterangan'  => 'required|string|max:255',
        ]);

        // Simpan ke tabel pengajuan_surat
        DB::table('pengajuan_surat')->insert([
            'id_pengguna' => $pegawai->id_pengguna,
            'jenis_surat' => $request->jenis_surat,
            'keterangan'  => $request->keterangan,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Opsional: kalau ada tabel riwayat_surat, tambahkan juga
        DB::table('riwayat_surat')->insert([
            'id_pengguna' => $pegawai->id_pengguna,
            'jenis_surat' => $request->jenis_surat,
            'keterangan'  => $request->keterangan,
            'created_at'  => now(),
        ]);

        return redirect()->route('riwayat_surat')
                         ->with('success', 'Pengajuan surat berhasil diajukan!');
    }
}
