@extends('layouts.app')
@section('content')
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('css/datapegawai.css') }}">

<div class="form-wrapper">
    <h4>Data Pegawai</h4>
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="mb-3">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <form action="{{ route('pengguna.import') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-1 align-items-center">
                @csrf
                <input type="file" name="file" required style="max-width: 180px; font-size: 0.85rem;">
                <button type="submit" class="btn btn-sm">Import</button>
            </form>
            <a href="{{ route('pegawai.export') }}" class="btn btn-sm">Ekspor</a>
            <a href="{{ route('data.pegawai.create') }}" class="btn btn-sm">+ Add</a>

            <form id="bulkDeleteForm" action="{{ route('data.pegawai.bulkDelete') }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <input type="hidden" name="ids" id="selectedIds">
                <input type="hidden" name="select_all" id="selectAllInput" value="false">
                <button type="button" id="deleteSelectedBtn" class="btn btn-danger btn-sm" disabled data-bs-toggle="modal" data-bs-target="#bulkDeleteModal">
                    Hapus Terpilih
                </button>
            </form>

            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAllDataModal">
                Hapus Semua
            </button>
        </div>
    </div>

    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <!-- Search Box di Kiri -->
        <div class="search-wrapper">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" placeholder="Search">
        </div>

        <!-- Filter Tampilkan di Kanan -->
        <form method="GET" action="{{ route('data.pegawai') }}" style="display: flex; align-items: center; gap: 10px;">
            <label for="filter" style="font-weight: bold;">Tampilkan:</label>
            <select name="filter" id="filter" onchange="this.form.submit()" class="form-select" style="width: 200px;">
                <option value="">Semua Pegawai</option>
                <option value="aktif" {{ request('filter') == 'aktif' ? 'selected' : '' }}>Pegawai Aktif</option>
                <option value="pensiun" {{ request('filter') == 'pensiun' ? 'selected' : '' }}>Pegawai Pensiun</option>
            </select>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th style="width: 100px;" class="text-center">
                        <input type="checkbox" id="selectAll" class="form-check-input" style="cursor: pointer;">
                        <br>
                        <small id="selectLabel" class="text-white">Pilih</small>
                    </th>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Umur</th>
                    <th>Role</th>
                    <th>Pimpinan</th>
                    <th>Jabatan</th>
                    <th>Pangkat / Golongan</th>
                    <th>Unit Kerja</th>
                    <th>Masa Kerja</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($pengguna as $index => $item)
                <tr>
                    <td class="text-center"><input type="checkbox" class="selectItem form-check-input" value="{{ $item->id_pengguna }}"></td>
                    <td>{{ $pengguna->firstItem() + $index }}</td>
                    <td>{{ $item->nama_lengkap }}</td>
                    <td>{{ $item->nip }}</td>
                    <td>{{ $item->umur }}</td>
                    <td>{{ $item->role }}</td>
                    <td>{{ $item->pimpinan?->nama_pimpinan ?? '-' }}</td>
                    <td>{{ $item->jabatan?->nama_jabatan ?? '-' }}</td>
                    <td>
                        {{ $item->pangkatGolongan 
                            ? $item->pangkatGolongan->pangkat . ' (' . $item->pangkatGolongan->golongan . '/' . $item->pangkatGolongan->ruang . ')' 
                            : '-' }}
                    </td>
                    <td>{{ $item->unitKerja?->nama_unit_kerja ?? '-' }}</td>
                    <td>{{ $item->masa_kerja }}</td>
                    
                    <td class="text-center">
                        @if($item->status_aktif === 'pensiun' || $item->status_aktif === 'nonaktif')
                            <button class="btn btn-secondary btn-sm" disabled style="opacity: 0.7; cursor: not-allowed;">
                                Sudah Pensiun
                            </button>
                        @else
                            <div class="action-buttons-inline">
                                <a href="{{ route('data.pegawai.show', $item->id_pengguna) }}" class="btn-icon-edit" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('data.pegawai.edit', $item->id_pengguna) }}" class="btn-icon-edit" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn-icon-delete" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id_pengguna }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        @endif

                        <!-- Modal Hapus -->
                        <div class="modal fade modal-top-center modal-delete-wide" id="deleteModal{{ $item->id_pengguna }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow-sm rounded-3">
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title fw-bold">Hapus Pegawai</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-start">
                                        Apakah Anda yakin ingin menghapus data ini?
                                    </div>
                                    <div class="modal-footer border-0 pt-0">
                                        <button type="button" class="btn btn-cancel px-4" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('data.pegawai.destroy', $item->id_pengguna) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-confirm-delete px-4">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="12" class="text-center">Data tidak ditemukan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="d-flex justify-content-end mt-3" id="paginationContainer">
        {{ $pengguna->appends(['filter' => request('filter'), 'search' => request('search')])->links('pagination::bootstrap-4') }}
    </div>
</div>

<!-- Modal Hapus Terpilih -->
<div class="modal fade modal-top-center" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-sm rounded-3">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Hapus Data Pegawai</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-start py-3">
        <p id="deleteMessage">Apakah Anda yakin ingin menghapus semua data pegawai yang dipilih?</p>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-cancel px-4" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-confirm-delete px-4" id="confirmBulkDelete">Hapus</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Hapus Semua Data -->
<div class="modal fade modal-top-center" id="deleteAllDataModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-sm rounded-3">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-danger">⚠️ Hapus SEMUA Data Pegawai</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-start py-3">
        <p class="text-danger fw-bold">PERHATIAN! Tindakan ini akan menghapus SEMUA data pegawai di seluruh halaman dan tidak dapat dibatalkan!</p>
        <p>Apakah Anda benar-benar yakin ingin melanjutkan?</p>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-cancel px-4" data-bs-dismiss="modal">Batal</button>
        <form action="{{ route('data.pegawai.bulkDelete') }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <input type="hidden" name="select_all" value="true">
            <button type="submit" class="btn btn-danger px-4">Ya, Hapus Semua</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const selectAll = document.getElementById('selectAll');
    const deleteButton = document.getElementById('deleteSelectedBtn');
    const selectLabel = document.getElementById('selectLabel');
    const selectedIdsInput = document.getElementById('selectedIds');
    const confirmBulkDelete = document.getElementById('confirmBulkDelete');
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const paginationContainer = document.getElementById('paginationContainer');

    // ✅ LIVE SEARCH FUNCTION
    let searchTimeout;
    if (searchInput && tableBody) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(() => {
                const searchValue = this.value.trim();
                const filterValue = document.getElementById('filter') ? document.getElementById('filter').value : '';
                
                // Build URL
                const url = new URL(window.location.origin + '/data-pegawai');
                url.searchParams.set('search', searchValue);
                url.searchParams.set('filter', filterValue);
                
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
                            
                            // Re-attach checkbox listeners
                            attachCheckboxListeners();
                            
                            // Reset selections
                            if (selectAll) selectAll.checked = false;
                            updateSelectAllLabel();
                            updateDeleteButton();
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

    // ✅ Function untuk attach event listeners ke checkbox
    function attachCheckboxListeners() {
        const newCheckboxes = document.querySelectorAll('.selectItem');
        
        newCheckboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                const allChecked = [...newCheckboxes].every(c => c.checked);
                if (selectAll) selectAll.checked = allChecked;
                updateSelectAllLabel();
                updateDeleteButton();
            });
        });
    }

    // ✅ Select All checkbox
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            const currentCheckboxes = document.querySelectorAll('.selectItem');
            currentCheckboxes.forEach(cb => cb.checked = this.checked);
            updateSelectAllLabel();
            updateDeleteButton();
        });
    }

    // ✅ Initial checkbox listeners
    attachCheckboxListeners();

    // ✅ Update label
    function updateSelectAllLabel() {
        if (!selectLabel) return;
        const currentCheckboxes = document.querySelectorAll('.selectItem');
        const checkedCount = [...currentCheckboxes].filter(c => c.checked).length;
        if (checkedCount > 0) {
            selectLabel.textContent = checkedCount + " dipilih";
        } else {
            selectLabel.textContent = "Pilih Semua";
        }
    }

    // ✅ Enable/disable delete button
    function updateDeleteButton() {
        if (!deleteButton) return;
        const currentCheckboxes = document.querySelectorAll('.selectItem');
        const checkedCount = [...currentCheckboxes].filter(c => c.checked).length;
        deleteButton.disabled = checkedCount === 0;
    }

    // ✅ Bulk delete confirmation
    if (confirmBulkDelete) {
        confirmBulkDelete.addEventListener('click', function() {
            const currentCheckboxes = document.querySelectorAll('.selectItem');
            const selectedIds = [...currentCheckboxes]
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            
            if (selectedIdsInput) {
                selectedIdsInput.value = selectedIds.join(',');
            }
            
            document.getElementById('bulkDeleteForm').submit();
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

    // ✅ Highlight search results (HANYA Nama, NIP, Unit Kerja)
    function highlightSearchResults(searchTerm) {
        if (!searchTerm || searchTerm.length < 1) return;
        
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            // Kolom: 2=Nama, 3=NIP, 9=Unit Kerja
            [2, 3, 9].forEach(index => {
                const cell = cells[index];
                if (!cell) return;
                
                // Skip jika ada button/checkbox
                if (cell.querySelector('.action-buttons-inline, .btn, input[type="checkbox"]')) {
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
});
</script>
@endsection