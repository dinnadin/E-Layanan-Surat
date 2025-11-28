@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/datacuti.css') }}">

<div class="container-fluid p-4 page-container">
    <div class="form-wrapper d-flex flex-column">

        <div class="form-card mb-3 d-flex flex-column">
            <div class="form-card-header d-flex justify-content-between align-items-center mb-3">

                {{-- 🔙 Tombol Kembali --}}
                <a href="{{ url()->previous() }}" 
                   class="btn btn-light"
                   style="border-radius:8px; padding:6px 12px; margin-right:10px;">
                    <i class="bi bi-arrow-left"></i>
                </a>

                <h3 class="mb-0">Tambah Data Cuti</h3>
            </div>

            <form action="{{ route('data_cuti.store') }}" method="POST">
                @csrf

                {{-- Baris Nama & NIP --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Nama</label>
                        <input type="text" class="form-control" value="{{ $pegawai->nama_lengkap }}" readonly>
                        <input type="hidden" name="id_pengguna" value="{{ $pegawai->id_pengguna }}">
                    </div>

                    <div class="col-md-6">
                        <label>NIP</label>
                        <input type="text" class="form-control" value="{{ $pegawai->nip }}" readonly>
                    </div>
                </div>

                {{-- Baris N-2, N-1 & N --}}
                <div class="row mb-3">

                    <div class="col-md-4">
                        <label>N-2</label>
                        <input type="number" name="n_2" class="form-control" value="0" readonly>
                    </div>

                    <div class="col-md-4">
                        <label>N-1</label>
                        <input type="number" name="n_1" id="n_1" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label>N</label>
                        <input type="number" name="n" id="n" class="form-control" required>
                    </div>
                </div>

                {{-- Jumlah, Diambil, Sisa --}}
                <div class="row mb-4">

                    <div class="col-md-4">
                        <label>Jumlah</label>
                        <input type="number" name="jumlah" id="jumlah" class="form-control" readonly>
                    </div>

                    <div class="col-md-4">
                        <label>Diambil</label>
                        <input type="number" name="diambil" value="0" id="diambil" class="form-control" readonly>
                    </div>

                    <div class="col-md-4">
                        <label>Sisa</label>
                        <input type="number" name="sisa" id="sisa" class="form-control" readonly>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary"
                        style="padding:10px 35px; border-radius:10px;
                               background: linear-gradient(135deg,#d63384,#84229b); border:none;">
                        SIMPAN
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    // Hitung otomatis
    function updateValues() {
        let n1 = parseInt(document.getElementById("n_1").value) || 0;
        let n  = parseInt(document.getElementById("n").value) || 0;

        let jumlah = n1 + n;
        let diambil = 0;
        let sisa = jumlah - diambil;

        document.getElementById("jumlah").value = jumlah;
        document.getElementById("sisa").value = sisa;
    }

    document.getElementById("n_1").addEventListener("input", updateValues);
    document.getElementById("n").addEventListener("input", updateValues);
</script>

@endsection