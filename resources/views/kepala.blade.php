@extends('layouts.appkepala')

@section('title', 'Dashboard Kepala')
@section('page-title', '')

@section('content')
    <h2>Halo, {{ Auth::user()->nama_lengkap }}</h2>
    <p>Selamat datang di Web Aplikasi Pelayanan Surat Kepegawaian Online</p>
<div class="card-container">
    {{-- Permintaan Surat Ijin --}}
    <div class="card">
    <i>📄</i>
    <span>Permintaan Surat Ijin: {{ $jumlahPermintaanIjin }}</span>
</div>

    @php
        $id_pengguna = session('id_pengguna') ?? 0;
        // Hitung jumlah riwayat surat yang pernah dia ajukan
        $jumlahRiwayat = \App\Models\RiwayatSurat::where('id_pengguna', $id_pengguna)->count();
    @endphp

    {{-- Riwayat Pengajuan --}}
   <div class="card">
    <i>🔁</i>
    <span>Riwayat Pengajuan: <strong>{{ $jumlahRiwayat ?? 0 }}</strong></span>
</div>

    {{-- Pengajuan Selesai --}}
   <div class="card">
    <i>✅</i>
    <span>Pengajuan Selesai: {{ $jumlahPengajuanSelesai }}</span>
</div>

    </div>
@endsection