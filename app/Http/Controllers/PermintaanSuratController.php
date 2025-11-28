<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermintaanSurat;
use App\Models\RiwayatSurat;
use App\Models\Pengguna;
use App\Models\SuratAktif; 

class PermintaanSuratController extends Controller
{
    // daftar semua permintaan (hanya Pending)
    public function index()
    {
        $permintaan = PermintaanSurat::with('pengguna')
                        ->where('status', 'Pending')
                        ->latest()
                        ->paginate(10);

        return view('admin.surataktif.index', compact('permintaan'));
    }

    // detail permintaan
    public function show($id)
    {
        $permintaan = PermintaanSurat::with(['pengguna', 'pimpinan'])->findOrFail($id);
        return view('surataktif.show', compact('permintaan'));
    }

// ✅ Method untuk menampilkan form create
public function create()
{
    // ambil id dari session
    $id_pengguna = session('id_pengguna');

    if (!$id_pengguna) {
        return redirect()->route('login.form')->with('error', 'Silakan login dulu');
    }

    // ambil data user yang login beserta relasi
    $loginUser = Pengguna::with(['jabatan', 'unitKerja', 'pangkatGolongan'])
        ->where('id_pengguna', $id_pengguna)
        ->first();

    // fallback jika tidak ditemukan
    if (!$loginUser) {
        return redirect()->route('login.form')->with('error', 'User tidak ditemukan, silakan login ulang.');
    }

    // ✅ Ambil hanya kepala yang memiliki pimpinan mengandung kata "Kepala Balai"
$kepala = Pengguna::with(['pimpinan', 'jabatan', 'pangkatGolongan'])
    ->whereHas('pimpinan', function ($query) {
        $query->whereRaw('LOWER(nama_pimpinan) LIKE ?', ['%kepala balai%']);
    })
    ->where(function ($query) {
        $query->whereRaw('LOWER(role) = ?', ['kepala'])
              ->orWhereRaw('LOWER(role) = ?', ['ketua kelompok']);
    })
    ->get();

    return view('surataktif.create', compact('loginUser', 'kepala'));
}

    // simpan pengajuan oleh pegawai
    public function store(Request $request)
    {
        $request->validate([
            'id_pengguna'        => 'required|exists:pengguna,id_pengguna',
            'penandatangan_id'   => 'required|exists:data_pimpinan,id_pimpinan', // ✅ Ubah dari id_pimpinan
            'nip'                => 'required',
            'pangkat_golongan_ruang' => 'required',
            'jabatan'            => 'required',
            // ✅ TIDAK ada validasi status_kepegawaian (sudah diambil dari tabel pengguna)
        ]);

        $pengguna = Pengguna::findOrFail($request->id_pengguna);

        $permintaan = PermintaanSurat::create([
            'id_pengguna'        => $request->id_pengguna,
            'penandatangan_id'   => $request->penandatangan_id, // ✅ Sesuaikan dengan nama field di validasi
            'nama'               => $pengguna->nama_lengkap ?? null,
            'nip'                => $request->nip,
            'pangkat_golongan_ruang' => $request->pangkat_golongan_ruang,
            'jabatan'            => $request->jabatan,
            'status'             => 'Pending',
            // ✅ TIDAK simpan status_kepegawaian (sudah ada di tabel pengguna)
            'tanggal_pengajuan'  => now(),
        ]);

        RiwayatSurat::create([
            'id_permintaan' => $permintaan->id_permintaan,
            'aksi'          => 'Dibuat',
            'keterangan'    => 'Permintaan surat baru dibuat oleh pegawai',
        ]);

        return redirect()->back()->with('success', 'Permintaan berhasil dikirim, menunggu konfirmasi Admin');
    }

    // hapus permintaan
    public function destroy($id)
    {
        $permintaan = PermintaanSurat::findOrFail($id);
        $permintaan->delete();

        return redirect()->route('admin.permintaan_surat.index')->with('success', 'Permintaan berhasil dihapus');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nomor_surat' => 'required|string|max:255',
        ]);

        $permintaan = PermintaanSurat::findOrFail($id);

        // Simpan nomor surat ke tabel surat_aktif
        $suratAktif = SuratAktif::create([
            'id_permintaan' => $permintaan->id_permintaan,
            'nomor_surat'   => $request->nomor_surat,
            'id_pengguna'   => $permintaan->id_pengguna,
            'tanggal_surat' => now(),
        ]);

        // Simpan juga ke riwayat_surat supaya bisa dilihat pegawai
        RiwayatSurat::create([
            'id_pengguna' => $permintaan->id_pengguna,
            'jenis_surat' => 'Surat Aktif',
            'nomor_surat' => $request->nomor_surat,
            'file'        => null, 
        ]);

        // Update status permintaan menjadi disetujui
        $permintaan->update([
            'status' => 'Disetujui',
        ]);

        return redirect()->route('admin.permintaan_surat.show', $permintaan->id_permintaan)
            ->with('success', 'Nomor surat berhasil disimpan!');
    }
}