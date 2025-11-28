@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/datacuti.css') }}">

<div class="container-fluid p-4 page-container">
    <div class="form-wrapper d-flex flex-column">

        {{-- Header Judul --}}
        <div class="form-card mb-3 d-flex flex-column">
            <div class="form-card-header d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Data Cuti</h3>
            </div>

<div class="d-flex justify-content-start mb-3 flex-wrap align-items-center gap-1">

    {{-- 🔹 KIRI: Choose File + Import --}}
    <div class="d-flex align-items-center gap-2 flex-wrap">

        {{-- Tombol Import --}}
        <form action="{{ route('data_cuti.import') }}" method="POST" enctype="multipart/form-data"
              id="importForm" class="m-0 d-flex align-items-center gap-2">
            @csrf
            <input type="file" name="file" id="fileInput">
            <button type="submit" class="btn-action btn-import">
                Import Data
            </button>
        </form>
    </div>

        <a href="{{ route('data-cuti.export') }}" class="btn-action btn-export">
            Eksport Data
        </a>

        <button id="deleteSelected" class="btn-action btn-delete-selected">
            Hapus Terpilih
        </button>

        {{-- Tambah Data Cuti (style disamakan) --}}
    <a href="{{ route('data_cuti.create') }}" class="btn-action" style="text-decoration:none;">
    + Add
</a>

                {{-- ✅ BUTTON UPDATE CUTI TAHUNAN (DENGAN SWEETALERT2) --}}
                <button type="button" 
                        id="btnUpdateCuti"
                        class="btn-action" 
                        style="margin-right:4px; background-color:{{ $canUpdateCuti ? '#28a745' : '#6c757d' }}; color:white; padding:8px 13px; border-radius:6px; border:none; cursor:{{ $canUpdateCuti ? 'pointer' : 'not-allowed' }}; opacity:{{ $canUpdateCuti ? '1' : '0.6' }};"
                        {{ $canUpdateCuti ? '' : 'disabled' }}
                        title="{{ $updateMessage }}"
                        data-can-update="{{ $canUpdateCuti ? 'true' : 'false' }}">
                    <i class="bi bi-arrow-repeat"></i> Update Cuti
                </button>

                {{-- Form Hidden untuk Submit --}}
                <form id="formUpdateCuti" action="{{ route('admin.trigger.update.cuti') }}" method="POST" style="display:none;">
                    @csrf
                </form>
                </div>
            </div>

            <div class="search-container mb-3">
    <div class="search-box d-flex align-items-center">
        <div class="search-input-wrapper">
            <i class="bi bi-search" style="color:black;"></i>
            <input type="text" id="searchInput" placeholder="Search data cuti...">
        </div>
    </div>

</div>

            {{-- Tabel Data --}}
            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width:80px;" class="text-center">
                                <input type="checkbox" id="selectAll"> Pilih
                            </th>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIP</th>
                            <th>N-2</th>
                            <th>N-1</th>
                            <th>N</th>
                            <th>Jumlah</th>
                            <th>Diambil</th>
                            <th>Sisa</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($data as $index => $row)
                            <tr>
                                <td><input type="checkbox" class="rowCheckbox" value="{{ $row->id }}"></td>
                                <td>{{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                                <td>{{ $row->pengguna->nama_lengkap ?? '-' }}</td>
                                <td>{{ $row->pengguna->nip ?? '-' }}</td>
                                <td>{{ $row->n_2 }}</td>
                                <td>{{ $row->n_1 }}</td>
                                <td>{{ $row->n }}</td>
                                <td>{{ $row->jumlah }}</td>
                                <td>{{ $row->diambil }}</td>
                                <td>{{ $row->sisa }}</td>
                                <td>
                                    <div class="aksi-wrapper">
                                        <a href="{{ route('data_cuti.edit', $row->id) }}" class="btn-icon-edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form action="{{ route('data_cuti.destroy', $row->id) }}" method="POST" class="form-delete m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn-icon-delete btn-delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">Data belum ada</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-end mt-3 w-100">
                <div class="pagination-wrapper">
                    <ul class="pagination mb-0">
                        @if ($data->onFirstPage())
                            <li class="page-item disabled"><span class="page-link">&lt;</span></li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $data->previousPageUrl() }}" rel="prev">&lt;</a>
                            </li>
                        @endif

                        @for ($i = 1; $i <= $data->lastPage(); $i++)
                            <li class="page-item {{ $i == $data->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $data->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        @if ($data->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $data->nextPageUrl() }}" rel="next">&gt;</a>
                            </li>
                        @else
                            <li class="page-item disabled"><span class="page-link">&gt;</span></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmLabel">Hapus Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="deleteModalBody">
                Apakah Anda yakin ingin menghapus data ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ✅ LOAD SWEETALERT2 & ANIMATE.CSS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

{{-- ✅ SCRIPT MODAL KONFIRMASI UPDATE CUTI --}}
<script>
document.getElementById('btnUpdateCuti')?.addEventListener('click', function() {
    const canUpdate = this.getAttribute('data-can-update') === 'true';
    
    if (!canUpdate) {
        Swal.fire({
            icon: 'warning',
            title: '<strong style="color: #ff9800;">🔒 Akses Ditolak</strong>',
            html: `
                <div style="text-align: center; padding: 20px;">
                    <p style="font-size: 1.1rem; color: #856404; margin-bottom: 15px;">
                        Update cuti tahunan hanya dapat dilakukan pada <strong>1 Januari</strong>
                    </p>
                    <div style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); padding: 15px; border-radius: 10px; margin-top: 15px;">
                        <small style="color: #856404;">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Silakan coba lagi pada tanggal yang ditentukan.
                        </small>
                    </div>
                </div>
            `,
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#ffc107',
            customClass: {
                popup: 'animate__animated animate__shakeX'
            }
        });
        return;
    }
    
    // Modal Konfirmasi untuk Update
    Swal.fire({
        title: '<strong style="color: #ff9800;">⚠️ KONFIRMASI UPDATE CUTI TAHUNAN</strong>',
        html: `
            <div style="text-align: left; padding: 15px;">
                <div style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); padding: 20px; border-radius: 15px; margin-bottom: 20px; border-left: 5px solid #ffc107;">
                    <h6 style="color: #856404; font-weight: bold; margin-bottom: 10px;">
                        <i class="bi bi-info-circle-fill me-2"></i>PERHATIAN PENTING!
                    </h6>
                    <p style="color: #856404; margin: 0;">
                        Proses ini akan <strong>mengupdate cuti SEMUA pegawai</strong> secara otomatis.
                    </p>
                </div>
                
                
                <div style="background: linear-gradient(135deg, #f8d7da 0%, #f5c2c7 100%); padding: 15px; border-radius: 10px; border-left: 5px solid #dc3545;">
                    <h6 style="color: #721c24; font-weight: bold; margin-bottom: 8px;">
                        <i class="bi bi-shield-fill-exclamation me-2"></i>PENTING!
                    </h6>
                    <small style="color: #721c24;">
                        Proses ini <strong>HANYA DAPAT DILAKUKAN 1 KALI</strong> sampai 1 Januari tahun depan.
                    </small>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-arrow-repeat me-2"></i>Ya, Lanjutkan Update',
        cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Batal',
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        width: '700px',
        customClass: {
            popup: 'animate__animated animate__fadeInDown',
            confirmButton: 'btn-confirm-update',
            cancelButton: 'btn-cancel-update'
        },
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit form
            document.getElementById('formUpdateCuti').submit();
        }
    });
});
</script>

{{-- Custom CSS untuk Buttons --}}
<style>
.btn-confirm-update,
.btn-cancel-update {
    padding: 12px 30px !important;
    font-weight: 600 !important;
    border-radius: 10px !important;
    transition: all 0.3s ease !important;
}

.btn-confirm-update {
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3) !important;
}

.btn-confirm-update:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4) !important;
}

.btn-cancel-update:hover {
    transform: translateY(-2px) !important;
}
</style>

{{-- Script untuk Delete & Search tetap sama --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    let modalBody = document.getElementById('deleteModalBody');
    const deleteSelectedBtn = document.getElementById('deleteSelected');
    const selectAllCheckbox = document.getElementById('selectAll');

    deleteSelectedBtn.disabled = true;
    deleteSelectedBtn.style.opacity = "0.6";
    deleteSelectedBtn.style.cursor = "not-allowed";

    function updateDeleteButtonState() {
        const checkedCount = document.querySelectorAll('.rowCheckbox:checked').length;
        if (checkedCount > 0) {
            deleteSelectedBtn.disabled = false;
            deleteSelectedBtn.style.opacity = "1";
            deleteSelectedBtn.style.cursor = "pointer";
        } else {
            deleteSelectedBtn.disabled = true;
            deleteSelectedBtn.style.opacity = "0.6";
            deleteSelectedBtn.style.cursor = "not-allowed";
        }
    }

    document.getElementById("searchInput").addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#tableBody tr");
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });

    selectAllCheckbox.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.rowCheckbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateDeleteButtonState();
    });

    document.querySelectorAll('.rowCheckbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const allChecked = document.querySelectorAll('.rowCheckbox:checked').length === document.querySelectorAll('.rowCheckbox').length;
            selectAllCheckbox.checked = allChecked;
            updateDeleteButtonState();
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            let form = this.closest('form');
            let action = form.getAttribute('action');

            let modalForm = document.getElementById('deleteForm');
            modalForm.setAttribute('action', action);

            modalBody.innerText = "Apakah Anda yakin ingin menghapus data ini?";
            modalForm.innerHTML = `
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Hapus</button>
            `;

            let modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            modal.show();
        });
    });

    document.getElementById('deleteSelected').addEventListener('click', function (e) {
        e.preventDefault();
        let selected = Array.from(document.querySelectorAll('.rowCheckbox:checked')).map(cb => cb.value);

        if (selected.length === 0) {
            alert('Pilih minimal 1 data untuk dihapus!');
            return;
        }

        let modalForm = document.getElementById('deleteForm');
        modalForm.setAttribute('action', "{{ route('data_cuti.bulkDelete') }}");

        modalBody.innerText = "Apakah Anda yakin ingin menghapus data yang dipilih?";
        modalForm.innerHTML = `
            @csrf
            ${selected.map(id => `<input type="hidden" name="ids[]" value="${id}">`).join('')}
            <button type="submit" class="btn btn-danger">Hapus</button>
        `;

        let modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    });
});

 // Menampilkan nama file setelah dipilih
   document.getElementById('fileInput').addEventListener('change', function () {
        let file = this.files[0];
        document.getElementById('fileName').textContent = file ? file.name : '';
    });
</script>
@endsection