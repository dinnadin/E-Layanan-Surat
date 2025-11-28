<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\RiwayatSurat;
use App\Models\PermintaanSurat;
use App\Models\SuratAktif;
use App\Models\Pengguna;

class SuratAktifController extends Controller
{
    public function create()
    {
        $pegawai = Pengguna::all();
        $kepala = Pengguna::with(['jabatan', 'pangkatGolongan'])
                    ->whereRaw('LOWER(role) LIKE ?', ['%kepala%'])
                    ->get();

        $loginUserId = session('id_pengguna');
        $loginUser = Pengguna::with(['jabatan', 'unitKerja', 'pangkatGolongan'])
                    ->find($loginUserId);

        return view('surataktif.create', compact('pegawai', 'kepala', 'loginUser'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pengguna'        => 'required|exists:pengguna,id_pengguna',
            'penandatangan_id'   => 'required|exists:pengguna,id_pengguna',
            'nip'                => 'required|string',
            'pangkat_golongan_ruang' => 'required|string',
            'jabatan'            => 'required|string',
            'status_kepegawaian' => 'required|string',
        ]);

        $id_pengguna = $request->id_pengguna;

        // ✅ Cek apakah masih ada pengajuan yang belum disetujui (Pending atau Ditolak)
        $pendingExists = PermintaanSurat::where('id_pengguna', $id_pengguna)
                    ->whereIn('status', ['Pending', 'Ditolak'])
                    ->exists();

        if ($pendingExists) {
            return redirect()->back()->with('error', 'Anda masih memiliki pengajuan yang belum disetujui. Silakan tunggu hingga pengajuan sebelumnya disetujui.');
        }

        $pengguna = Pengguna::findOrFail($id_pengguna);

        $permintaan = PermintaanSurat::create([
            'id_pengguna'            => $id_pengguna,
            'penandatangan_id'       => $request->penandatangan_id,
            'nama'                   => $pengguna->nama_lengkap ?? null,
            'nip'                    => $request->nip,
            'pangkat_golongan_ruang' => $request->pangkat_golongan_ruang,
            'jabatan'                => $request->jabatan,
            'status'                 => 'Pending',
            'status_kepegawaian'     => $request->status_kepegawaian,
            'tanggal_pengajuan'      => now(),
        ]);

        return redirect()->route('riwayat')->with('success', 'Permintaan surat berhasil dibuat');
    }

    public function checkNomorSurat(Request $request)
    {
        $nomorSurat = $request->input('nomor_surat');
        $idPermintaan = $request->input('id_permintaan');
        
        $exists = SuratAktif::where('nomor_surat', $nomorSurat)
                            ->where('id_permintaan', '!=', $idPermintaan)
                            ->exists();
        
        return response()->json(['exists' => $exists]);
    }

    public function approve(Request $request, $id)
    {
        \Log::info('🔍 START APPROVE TANPA UPLOAD TTD', [
            'id_permintaan' => $id
        ]);

        $permintaan = PermintaanSurat::with(['pengguna', 'penandatangan'])->findOrFail($id);

        $request->validate([
            'nomor_surat'     => [
                'required',
                Rule::unique('surat_aktif', 'nomor_surat')->ignore($id, 'id_permintaan')
            ],
            'tanggal_terbit'  => 'required|date',
        ], [
            'nomor_surat.unique' => 'Nomor surat telah digunakan. Tolong gunakan nomor surat lain.',
        ]);

        $penandatanganId = $request->penandatangan_id ?? $permintaan->penandatangan_id;
        if (!$penandatanganId) {
            return back()->with('error', 'Penandatangan tidak ditemukan.');
        }

        $penandatangan = Pengguna::find($penandatanganId);
        if (!$penandatangan) {
            return back()->with('error', 'Data penandatangan tidak valid.');
        }

        // ✅ Gunakan TTD yang sudah ada di database
        $ttdPath = $penandatangan->tanda_tangan;

        if (!$ttdPath || !file_exists(storage_path('app/public/' . $ttdPath))) {
            return back()->with('error', 'TTD penandatangan tidak ditemukan. Silakan pastikan sudah diunggah sebelumnya.');
        }

        // ✅ SIMPAN SURAT KE DATABASE
        try {
            $surat = SuratAktif::create([
                'id_permintaan'    => $permintaan->id_permintaan,
                'nomor_surat'      => $request->nomor_surat,
                'tanggal_terbit'   => $request->tanggal_terbit,
                'penerima_id'      => $permintaan->id_pengguna,
                'penandatangan_id' => $penandatanganId,
            ]);

            $permintaan->update(['status' => 'Disetujui']);

            RiwayatSurat::create([
                'id_pengguna'    => $surat->penerima_id,
                'id_surat_aktif' => $surat->id_surat,
                'keterangan'     => 'Surat Aktif',
            ]);

            return redirect()->route('surataktif.index')->with('success', 'Surat berhasil disetujui dan disimpan.');
        } catch (\Exception $e) {
            \Log::error('❌ ERROR SIMPAN SURAT', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ]);

            return back()->with('error', 'Gagal menyimpan surat: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $listSurat = PermintaanSurat::with('pengguna')
                        ->where('status', 'Pending')
                        ->latest()
                        ->paginate(10);

        return view('surataktif.index', compact('listSurat'));
    }

    public function edit($id)
    {
        $permintaan = PermintaanSurat::findOrFail($id);
        $kepala = Pengguna::whereRaw('LOWER(role) LIKE ?', ['%kepala%'])->get();
        return view('surataktif.edit', compact('permintaan', 'kepala'));
    }

    public function show($id)
{
    $permintaan = PermintaanSurat::with([
        'pengguna' => function($query) {
            $query->select('id_pengguna', 'nama_lengkap', 'nip', 'id_jabatan', 'id_pangkat_golongan_ruang', 'status_kepegawaian');
        },
        'pengguna.jabatan', 
        'pengguna.pangkatGolongan',
        'penandatangan' => function($query) {
            $query->select('id_pengguna', 'nama_lengkap', 'nip', 'id_pangkat_golongan_ruang', 'id_jabatan', 'tanda_tangan');
        },
        'penandatangan.jabatan', 
        'penandatangan.pangkatGolongan'
    ])->findOrFail($id);

    // ✅ Ambil path tanda tangan dari penandatangan (kalau ada)
    $tandaTangan = null;
    if ($permintaan->penandatangan && $permintaan->penandatangan->tanda_tangan) {
        $ttdPath = $permintaan->penandatangan->tanda_tangan;

        // Pastikan path valid dari storage/public
        $fullPath = storage_path('app/public/' . $ttdPath);
        if (file_exists($fullPath)) {
            $tandaTangan = asset('storage/' . $ttdPath);
        }
    }

    return view('surataktif.show', compact('permintaan', 'tandaTangan'));
}

    public function checkTanggal(Request $request)
    {
        $id_pengguna = $request->id_pengguna;

        // ✅ Cek apakah ada pengajuan yang belum disetujui
        $exists = PermintaanSurat::where('id_pengguna', $id_pengguna)
                    ->whereIn('status', ['Pending', 'Ditolak'])
                    ->exists();

        return response()->json(['exists' => $exists]);
    }
}