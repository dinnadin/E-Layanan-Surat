@extends('layouts.appKepala')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pelayanansuratijin.css') }}">

<div class="report-page">
    <h3 class="report-title">Daftar Permintaan Surat Ijin Keluar</h3>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 12px 20px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            {{ session('error') }}
        </div>
    @endif

 <div class="report-page">
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="search-wrapper">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" placeholder="Search">
        </div>
    </div>

    <!-- BUNGKUS TABEL DI SINI -->
    <div class="table-container">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Pangkat</th>
                    <th>Jabatan</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Aksi</th>
                </tr>
        </thead>
        <tbody>
            @forelse($permintaan as $surat)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $surat->pengguna->nama_lengkap ?? '-' }}</td>
                    <td>{{ $surat->pengguna->nip ?? '-' }}</td>
                    <td>
                        {{ $surat->pengguna->pangkatGolongan->pangkat ?? '-' }} /
                        {{ $surat->pengguna->pangkatGolongan->golongan ?? '-' }}
                        {{ $surat->pengguna->pangkatGolongan->ruang ?? '' }}
                    </td>
                    <td>{{ $surat->pengguna->jabatan->nama_jabatan ?? '-' }}</td>
                    {{-- ✅ Kolom Tanggal Pengajuan --}}
                        <td>{{ \Carbon\Carbon::parse($surat->mulai_tanggal)->format('d-m-Y') }}</td>
                                            <td>
                        <a href="{{ route('kepala.permintaan.edit', $surat->id) }}" class="btn-detail">Lihat Detail</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:20px 10px;">Belum ada pengajuan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ✅ Pagination --}}
    <div style="margin-top: 15px;">
        {{ $permintaan->links() }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('table tbody tr');

    searchInput.addEventListener('keyup', function () {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            row.style.display = rowText.includes(searchTerm) ? '' : 'none';
        });
    });
});
</script>

@endsection
