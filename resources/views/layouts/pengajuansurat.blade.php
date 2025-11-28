@extends('layouts.apppegawai')

@section('content')
<style>
    .card {
        background: #facc15;
        border-radius: 20px;
        padding: 20px;
        width: 250px;
        height: 130px;
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        transition: transform 0.3s;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;   
        justify-content: center; 
        text-align: center;
    }
    .card:hover {
        transform: scale(1.08);
    }
    .card img {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 200px;
        opacity: 0.2;
        transform: translate(-50%, -50%);
        pointer-events: none;
    }
    .card h2 {
        position: relative;
        z-index: 1;
        margin-bottom: 15px;
        font-size: 18px;
    }
    .btn {
        background: #5b21b6;
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        text-decoration: none;
        z-index: 10;        /* ditambahkan */
        position: relative;  /* ditambahkan */
    }
    .btn:hover {
        background: #3b0764;
    }
    .grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr); /* 3 kolom sejajar */
        gap: 30px;
        justify-items: center;
        margin-top: 40px;
    }
</style>

<div class="container">
    <h1 style="text-align:center; color:#5b21b6; margin-bottom:30px; font-size:24px;">
        Pengajuan Surat
    </h1>

    <div class="grid">
        <!-- Surat Cuti -->
        <div class="card" style="background:#facc15;">
            <img src="{{ asset('download/surat cuti.PNG') }}" alt="Icon Cuti">
            <h2>Surat Pengajuan Cuti</h2>
            <a href="{{ route('surat.cuti') }}" class="btn">Buat Surat</a>
        </div>

        <!-- Surat Aktif -->
        <div class="card" style="background:#fde047;">
            <img src="{{ asset('download/surat keterangan aktif.PNG') }}" alt="Icon Aktif">
            <h2>Surat Keterangan Aktif</h2>
            <a href="{{ route('surataktif.create') }}" class="btn">Buat Surat</a>
        </div>

        <!-- Surat Ijin -->
        <div class="card" style="background:#fde047;">
            <img src="{{ asset('download/surat izin.PNG') }}" alt="Icon Ijin">
            <h2>Surat Ijin</h2>
            <a href="{{ route('suratijin.create') }}" class="btn btn-primary">Buat Surat</a>
        </div>
    </div>
</div>
@endsection
