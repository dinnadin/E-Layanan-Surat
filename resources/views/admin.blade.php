@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <h2>Halo, {{ Auth::user()->nama_lengkap }}</h2>
    <p>Selamat datang di Web Aplikasi Pelayanan Surat Kepegawaian Online</p>

<div class="card-container">
    <div class="card">
        <div class="icon-text">
            <i>🔔</i><span>Permintaan Hari Ini ({{ $jumlahPermintaanHariIni }})</span>
        </div>
    </div>
    <div class="card">
        <div class="icon-text">
            <i>✅</i><span>Selesai Hari Ini ({{ $jumlahSelesaiHariIni }})</span>
        </div>
    </div>
    <div class="card">
        <div class="icon-text">
            <i>📊</i><span>Total Pegawai ({{ $totalPegawai }})</span>
        </div>
    </div>
</div>
@endsection
