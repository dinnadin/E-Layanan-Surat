<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermintaanSuratIjin;
use App\Models\SuratIjin;
use App\Models\Pengguna;
use App\Models\RiwayatSurat;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuratIjinController extends Controller
{
    /**
     * Kepala: Lihat daftar permintaan surat ijin yang masih pending
     */
  public function index()
{
    $loginUser = Auth::user();

    $permintaan = PermintaanSuratIjin::with('pengguna')
        ->where('penandatangan_id', $loginUser->id_pengguna)
        ->where('status', 'pending')
        ->latest()
        ->paginate(10); // ✅ ubah dari get() ke paginate(10)

    return view('suratijin.index', compact('permintaan'));
}

    /**
     * Form untuk membuat surat ijin (pegawai)
     */
public function create()
{
    $loginUser = Auth::user();
    $pimpinanList = collect();

    // ✅ Pegawai tanpa id_pimpinan → tanda tangan Ketua Tim Kerja, Ketua Kelompok, atau Kepala Bagian
    if (is_null($loginUser->id_pimpinan)) {
        $pimpinanList = Pengguna::whereHas('pimpinan', function ($q) {
            $q->where(function($query) {
                $query->where('nama_pimpinan', 'like', '%Ketua Tim Kerja%')
                      ->orWhere('nama_pimpinan', 'like', '%Ketua Kelompok%')
                      ->orWhere('nama_pimpinan', 'like', '%Kepala Bagian%');
            });
        })->get();
    }
    // ✅ Jika dia Ketua Tim Kerja / Ketua Kelompok → tanda tangan Kepala
    elseif (stripos($loginUser->pimpinan->nama_pimpinan ?? '', 'Ketua') !== false) {
        $pimpinanList = Pengguna::whereHas('pimpinan', function ($q) {
            $q->where('nama_pimpinan', 'like', '%Kepala%')
              ->where('nama_pimpinan', 'not like', '%Ketua%');
        })->get();
    }
    // ✅ Jika dia Kepala Bagian → tanda tangan Kepala (level lebih tinggi)
    elseif (stripos($loginUser->pimpinan->nama_pimpinan ?? '', 'Kepala Bagian') !== false) {
        $pimpinanList = Pengguna::whereHas('pimpinan', function ($q) {
            $q->where('nama_pimpinan', 'like', '%Kepala%')
              ->where('nama_pimpinan', 'not like', '%Bagian%');
        })->get();
    }
    // ✅ Jika dia Kepala → tanda tangan Kepala Balai
    elseif (stripos($loginUser->pimpinan->nama_pimpinan ?? '', 'Kepala') !== false) {
        $pimpinanList = Pengguna::whereHas('pimpinan', function ($q) {
            $q->where('nama_pimpinan', 'like', '%Kepala Balai%');
        })->get();
    }

    return view('suratijin.create', [
        'loginUser' => $loginUser,
        'pimpinanList' => $pimpinanList,
    ]);
}
    /**
     * Riwayat surat ijin yang diterima pengguna login
     */
    public function riwayat()
    {
        $data = SuratIjin::with(['penerima', 'penandatangan'])
            ->where('penerima_id', auth()->user()->id_pengguna)
            ->latest()
            ->get();

        return view('riwayat', compact('data'));
    }

    /**
     * Simpan pengajuan surat ijin ke tabel permintaan (pegawai)
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_pengguna'      => ['required', 'integer', Rule::exists((new Pengguna)->getTable(), (new Pengguna)->getKeyName())],
            'nip'              => 'required|string|max:50',
            'jabatan'          => 'required|string|max:100',
            'unit_kerja'       => 'required|string|max:100',
            'mulai_tanggal'    => 'required|date',
            'mulai_jam'        => 'required|date_format:H:i',
            'selesai_jam'      => 'required|date_format:H:i|after:mulai_jam',
            'jenis_alasan'     => 'required|string|max:50',
            'deskripsi_alasan' => 'required|string|max:255',
            'penandatangan_id' => 'required|exists:pengguna,id_pengguna',
        ]);

        $pengguna = Pengguna::findOrFail($request->id_pengguna);

        PermintaanSuratIjin::create([
            'id_pengguna'      => $request->id_pengguna,
            'nip'              => $request->nip,
            'nama_lengkap'     => $pengguna->nama_lengkap,
            'jabatan'          => $request->jabatan,
            'pangkat_golongan' => $pengguna->pangkat_golongan,
            'unit_kerja'       => $request->unit_kerja,
            'penandatangan_id' => $request->penandatangan_id,
            'mulai_tanggal'    => $request->mulai_tanggal,
            'mulai_jam'        => $request->mulai_jam,
            'selesai_jam'      => $request->selesai_jam,
            'jenis_alasan'     => $request->jenis_alasan,
            'deskripsi_alasan' => $request->deskripsi_alasan,
            'status'           => 'pending',
        ]);

        return redirect()->route('riwayat')
            ->with('success', 'Pengajuan surat ijin berhasil, menunggu persetujuan kepala.');
    }

    /**
     * Kepala: Form edit/review permintaan surat ijin
     */
    public function edit($id)
    {
        $permintaan = PermintaanSuratIjin::with(['pengguna', 'penandatangan'])->findOrFail($id);
        return view('suratijin.edit', compact('permintaan'));
    }

    /**
     * SETUJU: Kepala menyetujui dan membuat surat ijin resmi
     */
/**
 * Setujui permintaan surat ijin
 */
public function setuju(Request $request, $id)
{
    $permintaan = PermintaanSuratIjin::findOrFail($id);
    
    // Cek apakah TTD kepala sudah ada
    if (!Auth::user()->tanda_tangan) {
        return redirect()->back()->with('error', 'TTD Anda belum tersedia. Silakan upload TTD terlebih dahulu.');
    }
    
    // Validasi catatan (opsional, max 500 karakter)
    $request->validate([
        'catatan' => 'nullable|string|max:500'
    ], [
        'catatan.max' => 'Catatan maksimal 500 karakter'
    ]);
    
    // Update status menjadi Disetujui
    $permintaan->update([
        'status' => 'Disetujui'
    ]);
    
    // Buat surat ijin dengan catatan
    $suratIjin = SuratIjin::create([
        'id_permintaan' => $permintaan->id,
        'penandatangan_id' => Auth::user()->id_pengguna,
        'penerima_id' => $permintaan->id_pengguna,
        'keterangan' => $request->catatan, // ✅ Simpan catatan dari form
        'status' => 'Aktif',
    ]);
    
    // Simpan ke riwayat surat
    $keteranganRiwayat = 'Surat Ijin Disetujui';
    if ($request->catatan) {
        $keteranganRiwayat .= ' - Catatan: ' . $request->catatan;
    }
    
    RiwayatSurat::create([
        'id_pengguna' => $permintaan->id_pengguna,
        'id_surat_ijin' => $suratIjin->id_surat,
        'keterangan' => $keteranganRiwayat
    ]);
    
    return redirect()->route('kepala.permintaan.index')
        ->with('success', 'Permintaan surat ijin berhasil disetujui!');
}

/**
 * Tolak permintaan surat ijin dengan alasan
 */
public function tolak(Request $request, $id)
{
    $request->validate([
        'alasan_penolakan' => 'required|string|min:10'
    ], [
        'alasan_penolakan.required' => 'Alasan penolakan wajib diisi',
        'alasan_penolakan.min' => 'Alasan penolakan minimal 10 karakter'
    ]);

    $permintaan = PermintaanSuratIjin::findOrFail($id);

    // Update status dan simpan alasan penolakan
    $permintaan->update([
        'status' => 'Ditolak',
        'alasan_penolakan' => $request->alasan_penolakan
    ]);

    // Format tanggal pengajuan
    $tanggalPengajuan = \Carbon\Carbon::parse($permintaan->created_at)->format('d-m-Y');

    // Simpan ke tabel riwayat_surat dengan tambahan tanggal & jenis surat
    RiwayatSurat::create([
        'id_pengguna'   => $permintaan->id_pengguna,
        'id_surat_ijin' => null,
        'keterangan'    => "Pengajuan Surat Ijin tanggal {$tanggalPengajuan} ditolak. Alasan: {$request->alasan_penolakan}"
    ]);

    return redirect()->route('kepala.permintaan.index')
        ->with('success', 'Permintaan surat ijin ditolak dan tercatat di riwayat.');
}

}