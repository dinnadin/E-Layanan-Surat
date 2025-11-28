@extends('layouts.apppegawai')

@section('title', 'Dashboard Pegawai')
@section('page-title', '')

@section('content')
    <h2>Halo, {{ Auth::user()->nama_lengkap }}</h2>
    <p>Selamat datang di Web Aplikasi Pelayanan Surat Kepegawaian Online</p>

    <div class="card-container">
        <div class="card">
            <i>📄</i> Pengajuan Awal: <strong>{{ $jumlahPengajuanAwal ?? 0 }}</strong></span>
        </div>
    <div class="card">
            <i>🔁</i>
            <span>Riwayat Pengajuan: <strong>{{ $jumlahRiwayat ?? 0 }}</strong></span>
    </div>

        <div class="card">
            <i>✅</i> Pengajuan Selesai: <strong>{{ $jumlahSelesai ?? 0 }}</strong>
        </div>
    </div>
@endsection
