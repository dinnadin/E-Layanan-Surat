<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Models\Pimpinan;
use App\Models\PermintaanSurat;
use App\Models\PermintaanSuratIjin;
use App\Models\RiwayatSurat;
use App\Models\SuratIjin;
use App\Models\SuratAktif;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AuthController extends Controller
{
    // Menampilkan form login
    public function loginForm()
    {
        return view('login');
    }

   // Proses login
public function login(Request $request)
{
    $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);
    
    // ✅ LOAD RELASI JABATAN, PIMPINAN untuk pengecekan pensiun dan nama_pimpinan
    $user = Pengguna::with('jabatan', 'pangkatGolongan', 'pimpinan')->where('username', $request->username)->first();

    if (!$user) {
        return back()->withErrors(['username' => 'Username tidak ditemukan'])->withInput();
    }

    // ✅ CEK PASSWORD DULU
    if (!Hash::check($request->password, $user->password)) {
        return back()->withErrors(['username' => 'Username atau password salah'])->withInput();
    }

    // ✅ CEK STATUS AKTIF (cek apakah sudah pensiun atau non-aktif)
    if (isset($user->status_aktif) && $user->status_aktif !== 'aktif') {
        $pesan = '';
        
        if ($user->status_aktif === 'pensiun') {
            $tanggalPensiun = $user->tanggal_pensiun ? \Carbon\Carbon::parse($user->tanggal_pensiun)->format('d-m-Y') : '-';
            $pesan = 'Akun Anda sudah non-aktif dan tidak dapat login lagi. Tanggal pensiun: ' . $tanggalPensiun;
        } else {
            $pesan = 'Akun Anda sedang non-aktif. Silakan hubungi administrator.';
        }
        
        return back()->withErrors(['username' => $pesan])->withInput();
    }

    // ✅ CEK APAKAH SUDAH WAKTUNYA PENSIUN (real-time check)
    if (method_exists($user, 'sudahPensiun') && $user->sudahPensiun()) {
        // Update status menjadi pensiun
        if (method_exists($user, 'cekDanUpdateStatusPensiun')) {
            $user->cekDanUpdateStatusPensiun();
        }
        
        $umur = method_exists($user, 'getUmur') ? $user->getUmur() : 'melebihi batas';
        return back()->withErrors([
            'username' => 'Anda sudah memasuki usia pensiun (' . $umur . ' tahun) dan tidak dapat login lagi.'
        ])->withInput();
    }

    // ✅ LOGIN SUKSES - Aktifkan auth Laravel
    Auth::login($user);
    
    // ✅ AMBIL NAMA PIMPINAN JIKA ADA
    $namaPimpinan = null;
    if ($user->id_pimpinan) {
        $pimpinan = $user->pimpinan; // sudah di-load via with()
        if ($pimpinan) {
            $namaPimpinan = $pimpinan->nama_pimpinan;
        }
    }
    
    // ✅ AMBIL NAMA JABATAN JIKA ADA
    $namaJabatan = null;
    if ($user->id_jabatan) {
        $jabatan = $user->jabatan; // sudah di-load via with()
        if ($jabatan) {
            $namaJabatan = $jabatan->nama_jabatan;
        }
    }
    
    session([
        'login' => true,
        'id_pengguna' => $user->id_pengguna,
        'nama_lengkap' => $user->nama_lengkap,
        'nip' => $user->nip,
        'jabatan' => $user->jabatan->nama_jabatan ?? '-', 
        'pangkat_golongan_ruang' => $user->pangkatGolongan->pangkat ?? '-',
        'tanggal_dibuat' => $user->tanggal_dibuat, 
        'role' => $user->role,
        'id_pimpinan' => $user->id_pimpinan,
        'nama_pimpinan' => $namaPimpinan, // ✅ PRIORITAS 1
        'id_jabatan' => $user->id_jabatan,
        'nama_jabatan' => $namaJabatan, // ✅ PRIORITAS 2
        'unit_kerja' => $user->unitKerja->nama_unit_kerja ?? '-',
        'masa_kerja' => $user->masa_kerja ?? '-',
        
        'foto' => $user->foto && $user->foto !== 'images/default-avatar.png'
            ? (str_contains($user->foto, 'storage/') ? $user->foto : 'storage/' . ltrim($user->foto, '/'))
            : 'images/default-avatar.png',
    ]);

    $roleRaw = strtolower(trim($user->role ?? ''));

    if ($roleRaw === 'admin') {
        return redirect()->route('dashboard.admin');
    } elseif (strpos($roleRaw, 'kepala') !== false) {
        return redirect()->route('dashboard.kepala');
    } else {
        return redirect()->route('dashboard.pegawai');
    }
}

    // Logout
    public function logout()
{
    Auth::logout();
    session()->flush();
    return redirect()->route('login.form');
}

public function dashboardAdmin()
{
    if (session('login') && strtolower(session('role')) === 'admin') {

        // 🔹 Jumlah permintaan hari ini (pending)
        $permintaanHariIni = PermintaanSurat::whereDate('tanggal_pengajuan', Carbon::today('Asia/Jakarta'))
            ->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
            ->count();

        // 🔹 Permintaan kemarin yang masih pending
        $permintaanKemarin = PermintaanSurat::whereDate('tanggal_pengajuan', Carbon::yesterday('Asia/Jakarta'))
            ->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
            ->count();

        // 🔹 Total permintaan hari ini + kemarin
        $jumlahPermintaanHariIni = $permintaanHariIni + $permintaanKemarin;

        // 🔹 Jumlah surat aktif selesai hari ini
        $jumlahSelesaiHariIni = SuratAktif::whereDate('tanggal_terbit', Carbon::today('Asia/Jakarta'))
            ->whereRaw('LOWER(TRIM(status)) = ?', ['aktif'])
            ->count();

        // 🔹 Total semua pengguna tanpa filter role
        $totalPegawai = Pengguna::count();

        return view('admin', compact(
            'jumlahPermintaanHariIni',
            'jumlahSelesaiHariIni',
            'totalPegawai'
        ));
    }

    return redirect()->route('login.form');
}


public function dashboardPegawai()
{
    if (session('login') && strtolower(session('role')) === 'pegawai') {
        $id_pengguna = session('id_pengguna');

        // Riwayat Pengajuan
        $jumlahRiwayat = RiwayatSurat::where('id_pengguna', $id_pengguna)->count();

        // Pengajuan Selesai
$jumlahSelesai = SuratIjin::where('penerima_id', $id_pengguna)
                        ->where('status', 'aktif')
                        ->count()
             + SuratAktif::where('penerima_id', $id_pengguna)
                        ->where('status', 'aktif')
                        ->count();

        // Pengajuan Awal (hari ini)
$today = Carbon::today()->toDateString();
$jumlahPengajuanAwal = PermintaanSurat::whereDate('created_at', $today)->count()
                        + PermintaanSuratIjin::whereDate('created_at', $today)->count();

        // semua variabel dikirim ke view
        return view('pegawai', compact(
            'jumlahRiwayat',
            'jumlahSelesai',
            'jumlahPengajuanAwal'
        ));
    }

    return redirect()->route('login.form');
}

// Dashboard kepala
public function dashboardKepala()
{
    if (!session('login')) {
        return redirect()->route('login.form');
    }

    $role = strtolower(trim(session('role') ?? ''));

    if (strpos($role, 'kepala') !== false) {
        $idUser = session('id_pengguna');

        // ✅ Jumlah permintaan surat ijin yang status pending dan sesuai penandatangan login
        $jumlahPermintaanIjin = \App\Models\PermintaanSuratIjin::where('status', 'pending')
            ->where('penandatangan_id', $idUser)
            ->count();

        // Jumlah pengajuan selesai milik user yang login
        $jumlahPengajuanSelesai = \App\Models\RiwayatSurat::where('id_pengguna', $idUser)->count();

        return view('kepala', compact('jumlahPermintaanIjin', 'jumlahPengajuanSelesai'));
    }

    return redirect()->route('login.form');
}
}