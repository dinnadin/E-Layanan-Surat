@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/rekapitulasidatacuti.css') }}">

<div class="container-fluid p-4">

    {{-- Judul --}}
    <h3 class="mb-3">Rekapitulasi Data Cuti</h3>

    {{-- Search --}}
    <div class="mb-3">
        <form>
            <div class="search-box d-flex align-items-center">
                <div class="search-input-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Search nama atau NIP...">
                </div>
            </div>
        </form>
    </div>

    {{-- ✅ Filter + Tombol Export (di bawah Search) --}}
    <div class="filter-row mb-3">
        {{-- Form Filter (kiri) --}}
        <form method="GET" action="{{ route('rekapitulasi_cuti.index') }}" id="filterForm" class="form-filter mb-0">
            <select name="bulan" id="bulanFilter" class="filter-select" aria-label="Bulan">
                <option value="">-- Bulan --</option>
                @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>

            <select name="tahun" id="tahunFilter" class="filter-select" aria-label="Tahun">
                <option value="">-- Tahun --</option>
                @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>
                        {{ $t }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn-filter">Filter</button>
        </form>

        {{-- Tombol Export (kanan) --}}
        <div class="btn-wrapper">
            <a href="{{ route('rekapitulasi_cuti.export', request()->only(['bulan','tahun'])) }}" class="btn-export">
                Export Data
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- ✅ WRAPPER TABEL DENGAN DIV RESPONSIVE --}}
    <div class="table-wrapper">
        <div class="table-responsive">
            <table class="table-custom mt-2">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Jenis Cuti</th>
                        <th>Tgl Mulai</th>
                        <th>Tgl Selesai</th>
                        <th>Lama (Hari)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($cuti as $index => $c)
                    <tr>
                        <td>{{ $cuti->firstItem() + $index }}</td>
                        <td>{{ $c->pengguna->nama_lengkap ?? '-' }}</td>
                        <td>{{ $c->pengguna->nip ?? '-' }}</td>
                        <td>{{ $c->jenis_permohonan }}</td>
                        <td>{{ $c->tanggal_mulai }}</td>
                        <td>{{ $c->tanggal_selesai }}</td>
                        <td>{{ $c->lama }}</td>
                        <td>
                            @if(\Carbon\Carbon::parse($c->tanggal_mulai)->isFuture())
                                <button type="button" 
                                        class="btn-batalkan" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#hapusModal"
                                        data-id="{{ $c->id_cuti }}">
                                    Batalkan
                                </button>
                            @else
                                <button class="btn-batalkan" style="background-color: #ccc; cursor: not-allowed;" disabled>
                                    Sudah Dijalankan
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center">Data tidak ditemukan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrapper mt-3" id="paginationContainer">
            <ul class="pagination">
                @if ($cuti->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">&lt;</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $cuti->appends(request()->except('page'))->previousPageUrl() }}" rel="prev">&lt;</a>
                    </li>
                @endif

                @for ($i = 1; $i <= $cuti->lastPage(); $i++)
                    <li class="page-item {{ $i == $cuti->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $cuti->appends(request()->except('page'))->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor

                @if ($cuti->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $cuti->appends(request()->except('page'))->nextPageUrl() }}" rel="next">&gt;</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">&gt;</span></li>
                @endif
            </ul>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi --}}
<div class="modal fade" id="hapusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-top-custom">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Batalkan Cuti</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Apakah Anda yakin ingin membatalkan cuti ini?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <form id="formHapus" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-danger">Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const bulanFilter = document.getElementById('bulanFilter');
    const tahunFilter = document.getElementById('tahunFilter');
    
    let searchTimeout;

    // ✅ LIVE SEARCH FUNCTION
    if (searchInput && tableBody) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(() => {
                const searchValue = this.value.trim();
                const bulanValue = bulanFilter ? bulanFilter.value : '';
                const tahunValue = tahunFilter ? tahunFilter.value : '';
                
                // Build URL
                const url = new URL(window.location.origin + '/rekapitulasi-cuti');
                url.searchParams.set('search', searchValue);
                if (bulanValue) url.searchParams.set('bulan', bulanValue);
                if (tahunValue) url.searchParams.set('tahun', tahunValue);
                
                // Fetch data
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Update table body dengan smooth transition
                    const newTableBody = doc.getElementById('tableBody');
                    if (newTableBody) {
                        tableBody.style.opacity = '0.5';
                        setTimeout(() => {
                            tableBody.innerHTML = newTableBody.innerHTML;
                            tableBody.style.opacity = '1';
                            
                            // Highlight search results
                            if (searchValue) {
                                highlightSearchResults(searchValue);
                            }
                        }, 100);
                    }
                    
                    // Update pagination
                    const newPagination = doc.getElementById('paginationContainer');
                    if (newPagination && paginationContainer) {
                        paginationContainer.innerHTML = newPagination.innerHTML;
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                });
            }, 100);
        });

        // Visual feedback
        searchInput.addEventListener('keydown', function() {
            this.style.borderColor = '#0d6efd';
        });

        searchInput.addEventListener('blur', function() {
            this.style.borderColor = '';
        });
    }

    // ✅ Handle pagination links
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const url = e.target.closest('.pagination a').href;
            const searchValue = searchInput ? searchInput.value.trim() : '';
            
            if (searchValue) {
                const separator = url.includes('?') ? '&' : '?';
                window.location.href = url + separator + 'search=' + encodeURIComponent(searchValue);
            } else {
                window.location.href = url;
            }
        }
    });

    // ✅ Highlight search results (Nama dan NIP)
    function highlightSearchResults(searchTerm) {
        if (!searchTerm || searchTerm.length < 1) return;
        
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            // Kolom: 1=Nama, 2=NIP
            [1, 2].forEach(index => {
                const cell = cells[index];
                if (!cell) return;
                
                // Skip jika ada button
                if (cell.querySelector('button')) {
                    return;
                }
                
                const originalText = cell.textContent;
                // Escape special characters untuk regex
                const escapedTerm = searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const regex = new RegExp(`(${escapedTerm})`, 'gi');
                
                if (regex.test(originalText)) {
                    const highlightedText = originalText.replace(regex, '<mark style="background-color: #fff3cd; padding: 2px 4px; border-radius: 3px; font-weight: 500;">$1</mark>');
                    cell.innerHTML = highlightedText;
                }
            });
        });
    }

    // ✅ Modal handler untuk batalkan cuti
    const hapusModal = document.getElementById('hapusModal');
    if (hapusModal) {
        hapusModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const form = document.getElementById('formHapus');
            form.action = `/rekapitulasi-cuti/${id}/batalkan`;
        });
    }
});
</script>

@endsection