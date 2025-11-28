data_cuti/edit
@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/editdatacuti.css') }}">

<div class="cuti-page">
    <div class="cuti-card">
        <div class="cuti-box">

            <!-- HEADER -->
            <div class="cuti-header">
                <a href="{{ route('data_cuti.index') }}" class="back-link">&larr;</a>
                <h2>EDIT DATA CUTI</h2>
            </div>

            <hr class="cuti-divider">
@if(session('error'))
    <div class="alert alert-danger" style="background:#f8d7da;color:#721c24;padding:10px;border-radius:8px;margin-bottom:15px;">
        {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success" style="background:#d4edda;color:#155724;padding:10px;border-radius:8px;margin-bottom:15px;">
        {{ session('success') }}
    </div>
@endif

            <!-- FORM -->
            <form action="{{ route('data_cuti.update', $cuti->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- NAMA & NIP -->
                <div class="form-row">
                    <div class="form-field">
                        <label>Nama</label>
                        <input type="text" value="{{ $cuti->pengguna->nama_lengkap ?? '-' }}" readonly>
                    </div>

                    <div class="form-field">
                        <label>NIP</label>
                        <input type="text" value="{{ $cuti->pengguna->nip ?? '-' }}" readonly>
                    </div>
                </div>

              <!-- DATA CUTI -->
<div class="form-grid">
    <div class="field field-n2">
        <label>N-2</label>
        <input type="text" name="n_2" value="{{ $cuti->n_2 }}" readonly>
    </div>

    <div class="field field-n1">
        <label>N-1</label>
        <input type="text" name="n_1" value="{{ $cuti->n_1 }}" readonly>
    </div>

    <div class="field field-n">
        <label>N</label>
        <input type="number" name="n" value="{{ $cuti->n }}" required>
    </div>

    <div class="field field-jumlah">
        <label>Jumlah</label>
        <input type="text" name="jumlah" value="{{ $cuti->jumlah }}" readonly>
    </div>

    <div class="field field-diambil">
        <label>Diambil</label>
        <input type="text" name="diambil" value="{{ $cuti->diambil }}" readonly>
    </div>

    <div class="field field-sisa">
        <label>Sisa</label>
        <input type="text" name="sisa" value="{{ $cuti->sisa }}" readonly>
    </div>
</div>


                <!-- TOMBOL -->
                <div class="btn-wrapper">
                    <button type="submit" class="btn-simpan">SIMPAN</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection