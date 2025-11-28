@php
    // Ambil role dari session, normalisasi ke lowercase
    $role = strtolower(trim(session('role') ?? ''));
    $layout = (strpos($role, 'kepala') !== false) ? 'layouts.appkepala' : 'layouts.apppegawai';
@endphp

@extends($layout)

@section('content')

{{-- Panggil CSS eksternal --}}
<link rel="stylesheet" href="{{ asset('css/pengajuansurat.css') }}">

<div class="container {{ (strpos($role, 'kepala') !== false) ? 'role-kepala' : 'role-pegawai' }}">
    <h1 style="text-align:center; color:#5b21b6; margin-bottom:30px; font-size:24px;"></h1>

    <div class="grid">
        <!-- Surat Cuti -->
        <div class="card">
            <img src="{{ asset('download/detail cuti.PNG') }}" alt="Icon Cuti">
            <div class="card-content">
                <h2>Surat Pengajuan Cuti</h2>
                <a href="{{ route('pengajuan_cuti.create') }}" class="btn">Buat Surat</a>
            </div>
        </div>
        <!-- Surat Aktif -->
        <div class="card">
            <img src="{{ asset('download/pelayanan surat aktif.PNG') }}" alt="Icon Aktif">
            <div class="card-content">
                <h2>Surat Keterangan Aktif</h2>
                <a href="{{ route('surataktif.create') }}" class="btn">Buat Surat</a>
            </div>
        </div>
        <!-- Surat Ijin -->
        <div class="card">
            <img src="{{ asset('download/pelayanan surat ijin.PNG') }}" alt="Icon Ijin">
            <div class="card-content">
                <h2>Surat Ijin Keluar</h2>
                <a href="{{ route('suratijin.create') }}" class="btn">Buat Surat</a>
            </div>
        </div>
    </div>
</div>
@endsection