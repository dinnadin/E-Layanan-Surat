@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pelayanansurataktif.css') }}">

<div class="report-page">
    <h3 class="report-title">Daftar Permintaan Surat Aktif</h3>

    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="search-wrapper">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" placeholder="Search">
        </div>
    </div>

    <div class="table-wrapper">
        <table class="table-custom">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nama">Nama</th>
                    <th class="col-nip">NIP</th>
                    <th class="col-pangkat">Pangkat/Golongan</th>
                    <th class="col-jabatan">Jabatan</th>
                    <th class="col-tanggal">Tanggal Pengajuan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($listSurat as $surat)
                    <tr>
                        <td class="col-no text-center">{{ $loop->iteration }}</td>
                        <td class="col-nama">{{ $surat->pengguna->nama_lengkap ?? '-' }}</td>
                        <td class="col-nip">{{ $surat->pengguna->nip ?? '-' }}</td>
                        <td class="col-pangkat">
                            {{ $surat->pengguna->pangkatGolongan->pangkat ?? '-' }} 
                            {{ $surat->pengguna->pangkatGolongan->golongan ?? '-' }}/{{ $surat->pengguna->pangkatGolongan->ruang ?? '-' }}
                        </td>
                        <td class="col-jabatan">{{ $surat->pengguna->jabatan->nama_jabatan ?? '-' }}</td>
                        <td class="col-tanggal text-center">{{ \Carbon\Carbon::parse($surat->mulai_tanggal)->format('d-m-Y') }}</td>
                        <td class="col-aksi text-center">
                            <a href="{{ route('admin.surataktif.show', $surat->id_permintaan) }}" class="btn-detail">Lihat Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:20px 10px;">Belum ada pengajuan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-end mt-3">
        <nav aria-label="Pagination">
            <ul class="pagination mb-0">
                {{-- Previous --}}
                @if ($listSurat->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">&lt;</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link"
                           href="{{ $listSurat->appends(request()->query())->previousPageUrl() }}"
                           aria-label="Previous">
                            &lt;
                        </a>
                    </li>
                @endif

                {{-- Numbered links --}}
                @foreach ($listSurat->getUrlRange(1, $listSurat->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $listSurat->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach

                {{-- Next --}}
                @if ($listSurat->hasMorePages())
                    <li class="page-item">
                        <a class="page-link"
                           href="{{ $listSurat->appends(request()->query())->nextPageUrl() }}"
                           aria-label="Next">
                            &gt;
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">&gt;</span>
                    </li>
                @endif
            </ul>
        </nav>
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