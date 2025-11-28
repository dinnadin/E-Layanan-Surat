@extends('layouts.app')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('css/detailpegawai.css') }}">

<div class="form-wrapper">
    <h4 class="mb-4">Detail Data Pegawai</h4>
    <!-- Judul dengan panah kembali -->
    <div class="title-row">
        <a href="javascript:history.back()" class="back-link" aria-label="Kembali">&larr;</a>
    </div>

    <div class="text-center mb-4">
        @if($pegawai->foto && file_exists(public_path($pegawai->foto)))
            <img src="{{ asset($pegawai->foto) }}" 
                 alt="Foto {{ $pegawai->nama_lengkap }}" 
                 class="rounded-circle shadow-sm" 
                 style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #ddd;">
        @else
            <img src="{{ asset('images/default-profile.png') }}" 
                 alt="Default Profile" 
                 class="rounded-circle shadow-sm" 
                 style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #ddd;">
        @endif
        <h5 class="mt-3">{{ $pegawai->nama_lengkap }}</h5>
        <p class="text-muted">{{ $pegawai->jabatan?->nama_jabatan ?? '-' }}</p>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-bold">Nama Lengkap</label>
            <input type="text" class="form-control" value="{{ $pegawai->nama_lengkap }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">NIP</label>
            <input type="text" class="form-control" value="{{ $pegawai->nip }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">Umur</label>
            <input type="text" class="form-control" value="{{ $pegawai->umur }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">Role</label>
            <input type="text" class="form-control" value="{{ $pegawai->role }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">Pimpinan</label>
            <input type="text" class="form-control" value="{{ $pegawai->pimpinan?->nama_pimpinan ?? '-' }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">Jabatan</label>
            <input type="text" class="form-control" value="{{ $pegawai->jabatan?->nama_jabatan ?? '-' }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">Pangkat / Golongan</label>
            <input type="text" class="form-control" 
                   value="{{ $pegawai->pangkatGolongan 
                            ? $pegawai->pangkatGolongan->pangkat . ' (' . $pegawai->pangkatGolongan->golongan . '/' . $pegawai->pangkatGolongan->ruang . ')' 
                            : '-' }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">Unit Kerja</label>
            <input type="text" class="form-control" value="{{ $pegawai->unitKerja?->nama_unit_kerja ?? '-' }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">Masa Kerja</label>
            <input type="text" class="form-control" value="{{ $pegawai->masa_kerja }}" readonly>
        </div>
                <div class="col-md-6">
            <label class="form-label fw-bold">Status</label>
            <input type="text" class="form-control" value="{{ ucfirst($pegawai->status_aktif) }}" readonly>
        </div>
        
        <!-- Status Kepegawaian -->
        <div class="col-md-6">
            <label class="form-label fw-bold">Status Kepegawaian</label>
            <input type="text" class="form-control" value="{{ $pegawai->status_kepegawaian ?? '-' }}" readonly>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">Status</label>
            <input type="text" class="form-control" value="{{ ucfirst($pegawai->status_aktif) }}" readonly>
        </div>
        
        <!-- Tanda Tangan -->
        <div class="col-md-12">
            <label class="form-label fw-bold">Tanda Tangan</label>
            <div>
                @if($pegawai->tanda_tangan && \Storage::disk('public')->exists($pegawai->tanda_tangan))
                    <img src="{{ asset('storage/' . $pegawai->tanda_tangan) }}" 
                         alt="Tanda Tangan {{ $pegawai->nama_lengkap }}" 
                         class="signature-display">
                @else
                    <div class="alert alert-secondary" role="alert">
                        <i class="bi bi-exclamation-circle"></i> Tanda tangan belum tersedia
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection