<?php

namespace App\Http\Controllers;

use App\Models\PermintaanSuratIjin;
use App\Models\Pengguna;
use App\Models\SuratIjin;
use App\Models\RiwayatSurat;
use App\Models\DataPimpinan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermintaanSuratIjinController extends Controller
{
    /**
     * Form create permintaan surat ijin (pegawai)
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
              ->where('nama_pimpinan', 'not like', '%Bagian%'); // hindari kepala bagian lain
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
     * Simpan permintaan surat ijin ke database
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_pengguna' => 'required',
            'nip' => 'required',
            'nama_lengkap' => 'nullable',
            'jabatan' => 'required',
            'unit_kerja' => 'required',
            'mulai_tanggal' => 'required|date',
            'mulai_jam' => 'required',
            'selesai_jam' => 'required',
            'jenis_alasan' => 'required',
            'deskripsi_alasan' => 'nullable',
            'penandatangan_id' => 'required|exists:pengguna,id_pengguna',
        ]);

        /**
         * Cari penandatangan berdasarkan id_pimpinan
         * (otomatis bisa kepala atau ketua tim kerja)
         */
        // ✅ Cek apakah sudah ada pengajuan di tanggal yang sama oleh pengguna yang sama
$existing = PermintaanSuratIjin::where('id_pengguna', $request->id_pengguna)
    ->where('mulai_tanggal', $request->mulai_tanggal)
    ->whereIn('status', ['pending', 'Disetujui']) // masih menunggu atau sudah disetujui
    ->exists();

if ($existing) {
return redirect()->back()->with('error', 'Tanggal pengajuan Anda beririsan dengan pengajuan sebelumnya.');
}
        $data['penandatangan_id'] = $request->penandatangan_id; // langsung ambil dari dropdown
        $data['status'] = 'pending';
        
        PermintaanSuratIjin::create($data);

return redirect()->route('riwayat')->with('success', 'Permintaan surat ijin berhasil dikirim!');
    }

    public function riwayat(Request $request)
    {
        $data = RiwayatSurat::orderBy('id', 'desc')->paginate(10);
        $tahunList = range(date('Y'), date('Y') - 5);

        return view('riwayat', compact('data', 'tahunList'));
    }

    public function kepalaEdit($id)
    {
        $permintaan = PermintaanSuratIjin::findOrFail($id);
        return view('suratijin.edit', compact('permintaan'));
    }

    public function kepalaUpdate(Request $request, $id)
    {
        $permintaan = PermintaanSuratIjin::findOrFail($id);

        $request->validate([
            'mulai_tanggal' => 'required|date',
            'mulai_jam' => 'nullable',
            'selesai_jam' => 'nullable',
            'jenis_alasan' => 'required|string',
            'deskripsi_alasan' => 'nullable|string',
            'status' => 'nullable|in:pending,disetujui,ditolak',
        ]);

        $permintaan->update([
            'mulai_tanggal' => $request->mulai_tanggal,
            'mulai_jam' => $request->mulai_jam,
            'selesai_jam' => $request->selesai_jam,
            'jenis_alasan' => $request->jenis_alasan,
            'deskripsi_alasan' => $request->deskripsi_alasan,
            'status' => $request->status ? trim($request->status) : $permintaan->status,
        ]);

        return redirect()->route('kepala.permintaan.index')->with('success', 'Permintaan berhasil diperbarui.');
    }

    public function simpan(Request $request)
    {
        return $this->store($request);
    }

    /**
     * PERBAIKAN: Ganti status 'menunggu' menjadi 'pending'
     */
    public function kepalaIndex(Request $request)
{
    $loginUser = Auth::user();
    $search = $request->input('search');

    $permintaan = PermintaanSuratIjin::with(['pengguna.pangkatGolongan', 'pengguna.jabatan'])
        ->where('penandatangan_id', $loginUser->id_pengguna)
        ->where('status', 'pending')
        ->when($search, function ($query, $search) {
            $query->whereHas('pengguna', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('unit_kerja', 'like', "%{$search}%");
            });
        })
        ->latest()
        ->paginate(10);

    return view('suratijin.index', compact('permintaan', 'search'));
}
    

    public function index()
{
    $permintaan = PermintaanSuratIjin::whereNull('penandatangan_id')
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc')
        ->paginate(10); // ✅ ubah get() jadi paginate(10)

    return view('suratijin.index', compact('permintaan'));
}
}