@php
    // Ambil role dari session, hilangkan spasi berlebih dan ubah ke huruf kecil
    $role = strtolower(trim(session('role') ?? ''));

    // Jika role adalah 'kepala' atau 'ketua kelompok' (abaikan huruf besar-kecil)
    if (strcasecmp($role, 'kepala') === 0 || strcasecmp($role, 'ketua kelompok') === 0) {
        $layout = 'layouts.appkepala';
    } else {
        $layout = 'layouts.apppegawai';
    }
@endphp
@extends($layout)

@section('content')
{{-- Panggil file CSS eksternal --}}
<link rel="stylesheet" href="{{ asset('css/detailcuti.css') }}">

<div class="content-scroll role-{{ $role }}">
    <div class="container role-{{ $role }}">

        <h3 class="title-cuti" style="font-weight:bold; margin-bottom:15px;">Detail Cuti</h3>
{{-- Filter Bulan & Tahun --}}
<form method="GET" action="{{ route('detail_cuti.index') }}" class="mb-3 d-flex align-items-center" style="gap: 10px;">
    <select name="bulan" class="filter-select">
        <option value="">-- Bulan --</option>
        @for ($i = 1; $i <= 12; $i++)
            <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                {{ DateTime::createFromFormat('!m', $i)->format('F') }}
            </option>
        @endfor
    </select>

   <select name="tahun" class="filter-select">
    <option value="">-- Tahun --</option>
    @forelse($daftarTahun as $tahun)
        <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
            {{ $tahun }}
        </option>
    @empty
        <option value="" disabled>Belum ada data</option>
    @endforelse
</select>

    <button type="submit" class="btn-filter">Filter</button>
</form>

        {{-- Tabel Riwayat Pengajuan Cuti --}}
        <div class="table-wrapper">
        <table class="riwayat-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jenis Permohonan Cuti</th>
                    <th>Alasan</th>
                    <th>Lama Cuti</th>
                    <th>Tanggal Cuti</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cuti as $i => $c)
                <tr>
                    <td>{{ ($cuti->currentPage() - 1) * $cuti->perPage() + ($i + 1) }}</td>
                    <td>{{ $c->jenis_permohonan }}</td>
                    <td>{{ $c->alasan ?? '-' }}</td>
                    <td>{{ $c->lama }} {{ $c->satuan_lama }}</td>
                    <td>{{ $c->tanggal_mulai }} s/d {{ $c->tanggal_selesai }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;">Belum ada pengajuan cuti</td>
                </tr>
                @endforelse
            </tbody>
        </table>
</div>
        {{-- Pagination --}}
        @if ($cuti->lastPage() > 1)
        <div class="pagination-wrapper">
            <ul class="pagination">
                @if ($cuti->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">&lt;</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $cuti->previousPageUrl() }}">&lt;</a></li>
                @endif

                @for ($i = 1; $i <= $cuti->lastPage(); $i++)
                    <li class="page-item {{ $i == $cuti->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $cuti->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor

                @if ($cuti->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $cuti->nextPageUrl() }}">&gt;</a></li>
                @else
                    <li class="page-item disabled"><span class="page-link">&gt;</span></li>
                @endif
            </ul>
        </div>
        @endif

        {{-- Rekap Saldo Cuti --}}
        <div style="max-width:350px; margin-top:30px;">
            <table class="rekap-cuti-table">
                <tr>
                    <td>N-2 (Cuti 2 Tahun Lalu)</td>
                    <td style="text-align:center;">{{ $n2 }}</td>
                </tr>
                <tr>
                    <td>N-1 (Cuti Tahun Lalu)</td>
                    <td style="text-align:center;">{{ $n1 }}</td>
                </tr>
                <tr>
                    <td>N (Cuti Tahun Ini)</td>
                    <td style="text-align:center;">{{ $n }}</td>
                </tr>
                <tr>
                    <td>Jumlah</td>
                    <td style="text-align:center;"><strong>{{ $jumlah }}</strong></td>
                </tr>
                <tr>
                    <td>Diambil</td>
                    <td style="text-align:center;"><strong>{{ $diambil }}</strong></td>
                </tr>
                <tr>
                    <td>Sisa</td>
                    <td style="text-align:center;"><strong>{{ $sisa }}</strong></td>
                </tr>
            </table>
        </div>

    </div>
</div>
@endsection